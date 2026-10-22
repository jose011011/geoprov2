<?php
/**
 * FixGeo - Seguridad central: sesión, cabeceras, CSRF, límite de intentos y subida de imágenes.
 */
final class Security
{
    /** Llamar UNA vez, al inicio de public/index.php (antes de cualquier session_start). */
    public static function boot(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_name('FIXGEOSESSID');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => $https,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        // Rotar el id de sesión cada 30 minutos
        if (!isset($_SESSION['_nacida'])) {
            $_SESSION['_nacida'] = time();
        } elseif (time() - $_SESSION['_nacida'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_nacida'] = time();
        }

        self::cabeceras();
    }

    private static function cabeceras(): void
    {
        header_remove('X-Powered-By');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /** Llamar justo después de un login exitoso. */
    public static function renovarSesion(): void
    {
        session_regenerate_id(true);
        $_SESSION['_nacida'] = time();
    }

    // ---------- CSRF ----------
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /** Usar dentro de cada <form method="post">:  <?= Security::campo() ?> */
    public static function campo(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    /**
     * Verifica el token en POST/PUT/PATCH/DELETE. Acepta campo "_csrf" o cabecera X-CSRF-Token (fetch/AJAX).
     * Con CSRF_ENFORCE=false solo registra en el log (modo prueba); con true bloquea.
     */
    public static function verificar(): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }
        $enviado = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (hash_equals(self::token(), (string) $enviado)) {
            return;
        }
        if (defined('CSRF_ENFORCE') && CSRF_ENFORCE) {
            http_response_code(419);
            exit('Tu sesión expiró. Recarga la página e inténtalo de nuevo.');
        }
        error_log('[FixGeo] CSRF faltante/invalido en ' . ($_SERVER['REQUEST_URI'] ?? ''));
    }

    // ---------- Límite de intentos (login, recuperación, etc.) ----------
    private static function archivo(string $clave): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fixgeo_rl_' . hash('sha256', $clave) . '.json';
    }

    private static function leer(string $clave, int $ventana): array
    {
        $f = self::archivo($clave);
        $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
        if (!is_array($d) || (time() - ($d['t'] ?? 0)) > $ventana) {
            return ['n' => 0, 't' => time()];
        }
        return $d;
    }

    public static function bloqueado(string $clave, int $max = 5, int $ventana = 900): bool
    {
        return self::leer($clave, $ventana)['n'] >= $max;
    }

    public static function registrarFallo(string $clave, int $ventana = 900): void
    {
        $d = self::leer($clave, $ventana);
        $d['n']++;
        file_put_contents(self::archivo($clave), json_encode($d), LOCK_EX);
    }

    public static function limpiar(string $clave): void
    {
        @unlink(self::archivo($clave));
    }

    /** Clave recomendada para login: Security::claveIntentos($correo) */
    public static function claveIntentos(string $identificador): string
    {
        return 'login|' . strtolower(trim($identificador)) . '|' . ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    // ---------- Reglas de negocio ----------
    /** true si el cliente y el profesional son la misma persona (misma cuenta). */
    public static function esAutoSolicitud(PDO $db, int $idCliente, int $idProfesional): bool
    {
        $s = $db->prepare(
            'SELECT 1 FROM clientes c
               JOIN profesionales p ON p.id_usuario = c.id_usuario
              WHERE c.id_cliente = ? AND p.id_profesional = ? LIMIT 1'
        );
        $s->execute([$idCliente, $idProfesional]);
        return (bool) $s->fetchColumn();
    }

    // ---------- Subida de imágenes ----------
    /**
     * Valida y guarda una imagen. Devuelve el nombre de archivo guardado.
     * Ejemplo: $nombre = Security::guardarImagen($_FILES['ci_anverso'], $dir, 'DOC_' . $idProfesional);
     * @throws RuntimeException con mensaje listo para mostrar al usuario.
     */
    public static function guardarImagen(array $f, string $dirDestino, string $prefijo, int $maxMB = 3): string
    {
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            throw new RuntimeException('No se pudo subir el archivo.');
        }
        if ($f['size'] > $maxMB * 1024 * 1024) {
            throw new RuntimeException("El archivo supera {$maxMB} MB.");
        }
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset($permitidos[$mime]) || @getimagesize($f['tmp_name']) === false) {
            throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }
        if (!is_dir($dirDestino) && !mkdir($dirDestino, 0755, true)) {
            throw new RuntimeException('No se pudo preparar la carpeta de destino.');
        }
        $nombre = preg_replace('/[^A-Za-z0-9_]/', '', $prefijo) . '_' . bin2hex(random_bytes(8)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($f['tmp_name'], rtrim($dirDestino, '/\\') . DIRECTORY_SEPARATOR . $nombre)) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }
        return $nombre;
    }

    /** Escapar texto para HTML. */
    public static function e(?string $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}