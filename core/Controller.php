<?php
class Controller {
    protected function model(string $model) {
        require_once "../app/models/" . $model . ".php";
        return new $model();
    }

    protected function view(string $view, array $data = []) {
        extract($data);
        if (file_exists("../app/views/" . $view . ".php")) {
            require_once "../app/views/" . $view . ".php";
        } else {
            die("La vista [" . $view . "] no existe.");
        }
    }

    protected function jsonResponse(array $data, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ========================================================
       FUNCIÓN DE SEGURIDAD: SUBIDA DE ARCHIVOS BLINDADA
       ======================================================== */
    public function subirArchivoSeguro($archivoInput, $carpetaDestino) {
        // 1. Validar que el archivo subió sin errores HTTP
        if (!isset($archivoInput['error']) || $archivoInput['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Error en la transmisión del archivo.'];
        }

        // 2. Limitar el peso (Máximo 5 Megabytes)
        $pesoMaximo = 5 * 1024 * 1024;
        if ($archivoInput['size'] > $pesoMaximo) {
            return ['ok' => false, 'error' => 'El archivo es demasiado pesado. Máximo 5 MB.'];
        }

        // 3. LA MAGIA ANTI-HACKING: Leer el ADN (MIME-Type real) del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivoInput['tmp_name']);
        finfo_close($finfo);

        // Solo aceptamos imágenes reales o PDFs
        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($mimeReal, $mimesPermitidos, true)) {
            return ['ok' => false, 'error' => 'El formato del archivo fue alterado o no está permitido.'];
        }

        // 4. Renombrar el archivo para destruir cualquier nombre peligroso
        $extension = strtolower(pathinfo($archivoInput['name'], PATHINFO_EXTENSION));
        
        $extensionesSanas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($extension, $extensionesSanas, true)) {
            return ['ok' => false, 'error' => 'Extensión de archivo bloqueada por seguridad.'];
        }

        // Creamos un nombre encriptado: ej. "geo_doc_651a2b3c4d.jpg"
        $nombreSeguro = uniqid('geo_doc_', true) . '.' . $extension;
        
        // Asegurar que la carpeta exista, si no, crearla
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $rutaFinal = rtrim($carpetaDestino, '/') . '/' . $nombreSeguro;

        // 5. Mover el archivo a su carpeta final
        if (move_uploaded_file($archivoInput['tmp_name'], $rutaFinal)) {
            return ['ok' => true, 'nombre_archivo' => $nombreSeguro];
        } else {
            return ['ok' => false, 'error' => 'Error de permisos de escritura en el servidor.'];
        }
    }
/* ========================================================
       MIDDLEWARE API: VALIDAR TOKEN DE FLUTTER
       ======================================================== */
    protected function validarTokenApi() {
        // 1. Extraer las cabeceras de la petición enviada por Flutter
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';
        
        // Limpiamos la palabra "Bearer " para quedarnos solo con el código
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            http_response_code(401); // 401: No autorizado
            echo json_encode(['ok' => false, 'error' => 'No autorizado. Token faltante en la petición.']);
            exit;
        }

        // 2. Buscar al dueño del token en la base de datos
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT id_usuario, role_id, nombre, correo, celular 
            FROM usuarios 
            WHERE api_token = :token AND estado = 'ACTIVO'
        ");
        $stmt->execute([':token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Si el token es inventado, viejo, o el usuario está bloqueado, lo pateamos
        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Sesión inválida o expirada. Vuelve a iniciar sesión.']);
            exit;
        }

        // 4. Si todo está bien, devolvemos los datos del usuario al controlador
        return $usuario;
    }




}