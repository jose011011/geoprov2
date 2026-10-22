<?php
/**
 * FixGeo - Controlador base (web y API).
 * model(), view(), jsonResponse(), subirArchivoSeguro() y validarTokenApi() conservan su firma original
 * porque la API los usa.
 */
class Controller
{
    protected function model(string $model)
    {
        if (!preg_match('/^\w+$/', $model)) {
            throw new InvalidArgumentException('Modelo inválido.');
        }
        require_once __DIR__ . '/../app/models/' . $model . '.php';
        return new $model();
    }

    protected function view(string $view, array $data = [])
    {
        $__vista = __DIR__ . '/../app/views/' . $view . '.php';
        if (!preg_match('#^[\w/]+$#', $view) || !is_file($__vista)) {
            die('La vista [' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . '] no existe.');
        }
        extract($data);
        require $__vista;
    }

    protected function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ---------- Ayudas para los controladores web ----------
    protected function esPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function redirigir(string $ruta): void
    {
        header('Location: ' . BASE_URL . $ruta);
        exit;
    }

    protected function inicioPorRol(?int $rol = null): string
    {
        $rol ??= (int) ($_SESSION['role_id'] ?? 0);
        return match ($rol) {
            1, 2    => '/admin/dashboard',
            3       => '/profesional/dashboard',
            default => '/cliente/dashboard',
        };
    }

    /** Exige sesión (y opcionalmente uno de los roles indicados). */
    protected function requiereSesion(array $roles = []): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirigir('/auth/login');
        }
        if ($roles && !in_array((int) $_SESSION['role_id'], $roles, true)) {
            Flash::set('danger', 'No tienes permiso para entrar a esa sección.');
            $this->redirigir($this->inicioPorRol());
        }
    }

    // ---------- Subida de archivos (la usa la web y puede usarla la API) ----------
    public function subirArchivoSeguro($archivoInput, $carpetaDestino)
    {
        if (!isset($archivoInput['error']) || $archivoInput['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Error en la transmisión del archivo.'];
        }
        if ($archivoInput['size'] > 5 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'El archivo es demasiado pesado. Máximo 5 MB.'];
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivoInput['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeReal, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
            return ['ok' => false, 'error' => 'El formato del archivo fue alterado o no está permitido.'];
        }

        $extension = strtolower(pathinfo($archivoInput['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
            return ['ok' => false, 'error' => 'Extensión de archivo bloqueada por seguridad.'];
        }

        $nombreSeguro = 'fx_' . bin2hex(random_bytes(12)) . '.' . $extension;

        if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0755, true)) {
            return ['ok' => false, 'error' => 'No se pudo preparar la carpeta de destino.'];
        }

        if (move_uploaded_file($archivoInput['tmp_name'], rtrim($carpetaDestino, '/\\') . '/' . $nombreSeguro)) {
            return ['ok' => true, 'nombre_archivo' => $nombreSeguro];
        }
        return ['ok' => false, 'error' => 'Error de permisos de escritura en el servidor.'];
    }

    // ---------- Middleware de la API (token de Flutter) ----------
    protected function validarTokenApi()
    {
        $cabeceras = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $cabeceras = array_change_key_case($cabeceras, CASE_LOWER);
        $authHeader = $cabeceras['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'No autorizado. Token faltante en la petición.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT id_usuario, id_rol, nombre, correo, celular
            FROM usuarios
            WHERE api_token = :token AND estado = 'ACTIVO'
        ");
        $stmt->execute([':token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Sesión inválida o expirada. Vuelve a iniciar sesión.']);
            exit;
        }
        return $usuario;
    }




        /** Entrega un archivo de public/uploads/<subcarpeta>/ SOLO tras validar permisos en quien lo llama. Termina la petición. */
    protected function servirArchivoPrivado(string $subcarpeta, string $nombre): void
    {
        $base = realpath(__DIR__ . '/../public/uploads/' . $subcarpeta);
        $ruta = $base ? realpath($base . DIRECTORY_SEPARATOR . basename(str_replace('\\', '/', $nombre))) : false;

        if (!$ruta || !str_starts_with($ruta, $base) || !is_file($ruta)) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta) ?: 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
            http_response_code(415);
            exit('Tipo de archivo no permitido.');
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($ruta));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Disposition: inline; filename="' . basename($ruta) . '"');
        readfile($ruta);
        exit;
    }
}