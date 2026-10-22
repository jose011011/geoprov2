<?php
/**
 * Autenticación por token para la API móvil.
 *   ApiAuth::exigir($db)   -> usuario, o 401 si no hay sesión válida
 *   ApiAuth::opcional($db) -> usuario o null (para rutas públicas)
 */
class ApiAuth {

    public static function exigir(PDO $db): array {
        $usuario = self::buscar($db);
        if ($usuario === null) {
            self::rechazar();
        }
        return $usuario;
    }

    public static function opcional(PDO $db): ?array {
        return self::buscar($db);
    }

    private static function buscar(PDO $db): ?array {
        $token = self::tokenActual();
        if ($token === '') {
            return null;
        }

        $stmt = $db->prepare("
            SELECT id_usuario, id_rol, nombre, apellido, correo, celular,
                   (api_token_expira IS NOT NULL AND api_token_expira < NOW()) AS expirado
              FROM usuarios
             WHERE api_token = :t AND estado = 'ACTIVO'
             LIMIT 1
        ");
        $stmt->execute([':t' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || (int) $usuario['expirado'] === 1) {
            return null;
        }
        unset($usuario['expirado']);
        return $usuario;
    }

    /** Token Bearer de la petición actual ('' si no hay). */
    public static function tokenActual(): string {
        $h = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if ($h === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $k => $v) {
                if (strtolower($k) === 'authorization') {
                    $h = $v;
                }
            }
        }
        return stripos($h, 'Bearer ') === 0 ? trim(substr($h, 7)) : '';
    }

    private static function rechazar(): void {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'ok' => false,
            'error' => 'Tu sesión expiró. Inicia sesión nuevamente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}