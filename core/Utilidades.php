<?php
/** Funciones comunes de validación y auditoría para los controladores API. */
class Utilidades {
    public const MAX_IMAGEN = 5242880; // 5 MB

    public static function limpiar($valor): string {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $valor));
    }

    public static function titulo(string $t): string {
        return mb_convert_case(mb_strtolower($t, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /** Solo letras (con tildes y ñ), hasta $maxPalabras palabras. */
    public static function soloLetras(string $texto, int $maxPalabras): bool {
        $partes = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        if (!$partes || count($partes) > $maxPalabras) {
            return false;
        }
        foreach ($partes as $p) {
            if (!preg_match('/^\p{L}+$/u', $p)) {
                return false;
            }
        }
        return true;
    }

    /** Misma regla que auth-validation.js de la web. */
    public static function passwordValida(string $p): bool {
        return strlen($p) >= 8 && strlen($p) <= 64
            && !preg_match('/\s/', $p)
            && preg_match('/[A-Z]/', $p)
            && preg_match('/[0-9]/', $p)
            && preg_match('/[@$!%*?&\-_\.,]/', $p);
    }

    public static function mensajePassword(): string {
        return 'La contraseña debe tener mínimo 8 caracteres, sin espacios, con una mayúscula, '
             . 'un número y un símbolo (@ $ ! % * ? & - _ . ,).';
    }

    /**
     * Valida una imagen en base64 (jpg, png o webp, máx. 5 MB).
     * Devuelve ['bin' => ..., 'ext' => ...] o null (y agrega el error).
     */
    public static function imagen($b64, string $etiqueta, array &$errores): ?array {
        if (!is_string($b64) || $b64 === '') {
            $errores[] = "Falta la foto: $etiqueta.";
            return null;
        }
        if (stripos($b64, 'base64,') !== false) {
            $b64 = substr($b64, stripos($b64, 'base64,') + 7);
        }
        $bin = base64_decode($b64, true);
        if ($bin === false || $bin === '') {
            $errores[] = "La foto de $etiqueta no se pudo leer.";
            return null;
        }
        if (strlen($bin) > self::MAX_IMAGEN) {
            $errores[] = "La foto de $etiqueta pesa más de 5 MB.";
            return null;
        }
        $info = @getimagesizefromstring($bin);
        if (!$info) {
            $errores[] = "El archivo de $etiqueta no es una imagen válida.";
            return null;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG: $ext = 'jpg';  break;
            case IMAGETYPE_PNG:  $ext = 'png';  break;
            case IMAGETYPE_WEBP: $ext = 'webp'; break;
            default:
                $errores[] = "La foto de $etiqueta debe ser JPG, PNG o WEBP.";
                return null;
        }
        return ['bin' => $bin, 'ext' => $ext];
    }

    /** Carpeta pública de subidas (la crea si no existe). */
    public static function carpetaUploads(string $sub): string {
        $dir = dirname(__DIR__) . '/public/uploads/' . $sub . '/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new RuntimeException('No se pudo crear la carpeta de archivos.');
        }
        return $dir;
    }

    public static function auditar(PDO $db, ?int $idUsuario, string $accion,
                                   string $tabla, int $registroId, array $nuevos): void {
        try {
            $stmt = $db->prepare("
                INSERT INTO auditoria_logs
                    (id_usuario, accion, tabla_afectada, registro_id, valores_nuevos, ip_origen, user_agent)
                VALUES (:u, :a, :t, :r, :n, :ip, :ua)
            ");
            $stmt->execute([
                ':u'  => $idUsuario,
                ':a'  => $accion,
                ':t'  => $tabla,
                ':r'  => $registroId,
                ':n'  => json_encode($nuevos, JSON_UNESCAPED_UNICODE),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ':ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (Throwable $e) {
            error_log('[Utilidades] auditoria: ' . $e->getMessage());
        }
    }

    /**
     * Expresión SQL con los tokens que da un plan. Detecta cómo se llama la
     * columna en TU base real (tokens_otorgados, tokens_mensuales...).
     * Si no existe ninguna, el plan destacado (Premium) cuenta como ilimitado.
     */
    public static function sqlTokensPlan(PDO $db, string $alias = 'pl'): string {
        static $columna = false;

        if ($columna === false) {
            $columna = null;
            try {
                $cols = $db->query('SHOW COLUMNS FROM planes_suscripcion')
                           ->fetchAll(PDO::FETCH_COLUMN);

                $candidatas = ['tokens_otorgados', 'tokens_mensuales', 'tokens_incluidos',
                               'tokens_plan', 'cantidad_tokens', 'tokens_por_mes', 'tokens'];
                foreach ($candidatas as $c) {
                    if (in_array($c, $cols, true)) {
                        $columna = $c;
                        break;
                    }
                }
                if ($columna === null) {
                    foreach ($cols as $c) {
                        if (stripos($c, 'token') !== false) {
                            $columna = $c;
                            break;
                        }
                    }
                }
            } catch (Throwable $e) {
                error_log('[Utilidades] sqlTokensPlan: ' . $e->getMessage());
            }
        }

        if ($columna !== null) {
            return $alias . '.`' . str_replace('`', '', $columna) . '`';
        }
        return "(CASE WHEN {$alias}.posicionamiento_destacado = 1 THEN 999 ELSE 0 END)";
    }

    /** true solo en desarrollo (se define en app/config/config.php). */
    public static function debug(): bool {
        return defined('APP_DEBUG') && APP_DEBUG === true;
    }

    /**
     * id_cliente del usuario. Un PROFESIONAL (rol 3) también puede ser cliente:
     * con $crear = true se le crea su perfil de cliente al primer uso.
     * Devuelve 0 si no puede actuar como cliente.
     */
    public static function idClienteDe(PDO $db, array $usuario, bool $crear = false): int {
        $buscar = function () use ($db, $usuario): int {
            $stmt = $db->prepare('SELECT id_cliente FROM clientes WHERE id_usuario = :u LIMIT 1');
            $stmt->execute([':u' => $usuario['id_usuario']]);
            return (int) $stmt->fetchColumn();
        };

        $id = $buscar();
        if ($id > 0 || !$crear) {
            return $id;
        }
        if ((int) $usuario['id_rol'] !== 3) {
            return 0; // solo los profesionales tienen modo dual
        }

        $stmt = $db->prepare('SELECT zona_especifica, latitud_actual, longitud_actual FROM profesionales WHERE id_usuario = :u LIMIT 1');
        $stmt->execute([':u' => $usuario['id_usuario']]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            return 0;
        }

        $zona = mb_substr(trim((string) $p['zona_especifica']), 0, 100);
        if (mb_strlen($zona) < 3) {
            $zona = 'La Paz';
        }

        try {
            $ins = $db->prepare("
                INSERT INTO clientes (id_usuario, direccion_referencia, zona,
                                      latitud_predeterminada, longitud_predeterminada)
                VALUES (:u, :d, :z, :lat, :lng)
            ");
            $ins->bindValue(':u', (int) $usuario['id_usuario'], PDO::PARAM_INT);
            $ins->bindValue(':d', 'Zona ' . $zona . ' (la dirección exacta se indica en cada pedido)');
            $ins->bindValue(':z', $zona);
            $ins->bindValue(':lat', $p['latitud_actual'],
                $p['latitud_actual'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $ins->bindValue(':lng', $p['longitud_actual'],
                $p['longitud_actual'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $ins->execute();
            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            return $buscar(); // otro proceso lo creó al mismo tiempo
        }
    }
}