<?php
/**
 *   GET  /api/perfil/ver
 *   POST /api/perfil/actualizar
 *   POST /api/perfil/foto        { imagen_base64 }
 *   POST /api/perfil/password    { actual, nueva }
 * Cliente (rol 4) y profesional (rol 3).
 */
class ApiPerfilController {
    private PDO $db;

    private const ROL_PROFESIONAL = 3;
    private const ROL_CLIENTE = 4;
    private const MACROS = [
        'ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES',
        'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO'
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       VER
       ========================================================== */
    public function ver() {
        try {
            $u = $this->usuarioActual();
            $id = (int) $u['id_usuario'];
            $resp = ['ok' => true, 'usuario' => $this->datosUsuario($id), 'cliente' => null, 'profesional' => null];

            if ((int) $u['id_rol'] === self::ROL_CLIENTE) {
                $stmt = $this->db->prepare("
                    SELECT direccion_referencia, zona, latitud_predeterminada, longitud_predeterminada
                      FROM clientes WHERE id_usuario = :u LIMIT 1
                ");
                $stmt->execute([':u' => $id]);
                $resp['cliente'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } else {
                $stmt = $this->db->prepare("
                    SELECT p.descripcion_servicio, p.experiencia_anios, p.macrodistrito_base,
                           p.zona_especifica, p.tarifa_base, p.tipo_prestador, p.estado_validacion,
                           c.nombre_categoria, pl.nombre_plan
                      FROM profesionales p
                      INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                      INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
                     WHERE p.id_usuario = :u LIMIT 1
                ");
                $stmt->execute([':u' => $id]);
                $resp['profesional'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            $this->responder($resp);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       ACTUALIZAR
       ========================================================== */
    public function actualizar() {
        $this->soloPost();
        try {
            $u  = $this->usuarioActual();
            $id = (int) $u['id_usuario'];
            $rol = (int) $u['id_rol'];
            $d = $this->leerJson();
            $errores = [];

            $celular = trim((string) ($d['celular'] ?? ''));
            if (!preg_match('/^[67]\d{7}$/', $celular)) {
                $errores[] = 'El celular debe tener 8 números y empezar con 6 o 7.';
            }

            if ($rol === self::ROL_CLIENTE) {
                $nombres  = Utilidades::limpiar($d['nombres'] ?? '');
                $apellido = Utilidades::limpiar($d['apellido'] ?? '');
                $dir  = Utilidades::limpiar($d['direccion_referencia'] ?? '');
                $zona = Utilidades::limpiar($d['zona'] ?? '');
                $lat = $d['latitud'] ?? null;
                $lng = $d['longitud'] ?? null;

                $partes = $nombres === '' ? [] : preg_split('/\s+/u', $nombres);
                if (!$partes || count($partes) > 2) {
                    $errores[] = 'Escribe 1 o 2 nombres.';
                } else {
                    foreach ($partes as $p) {
                        if (!preg_match('/^\p{L}{2,}$/u', $p)) {
                            $errores[] = 'El nombre solo puede tener letras (mínimo 2 por nombre).';
                            break;
                        }
                    }
                }
                if ($apellido === '' || !Utilidades::soloLetras($apellido, 2) || mb_strlen($apellido) > 100) {
                    $errores[] = 'Los apellidos deben ser máximo 2 palabras (paterno y materno), solo letras.';
                }
                if (mb_strlen($dir) < 8 || mb_strlen($dir) > 255) {
                    $errores[] = 'Escribe una dirección más exacta (mínimo 8 letras).';
                }
                if (mb_strlen($zona) < 3 || mb_strlen($zona) > 100) {
                    $errores[] = 'Escribe tu zona o barrio (mínimo 3 letras).';
                }
                if ($lat !== null || $lng !== null) {
                    if (!is_numeric($lat) || !is_numeric($lng)
                        || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        $errores[] = 'La ubicación del mapa no es válida.';
                    }
                }
            } else {
                $desc  = Utilidades::limpiar($d['descripcion_servicio'] ?? '');
                $exp   = $d['experiencia_anios'] ?? null;
                $macro = strtoupper(trim((string) ($d['macrodistrito_base'] ?? '')));
                $zona  = Utilidades::limpiar($d['zona_especifica'] ?? '');
                $tarifa = $d['tarifa_base'] ?? null;

                if (mb_strlen($desc) < 20 || mb_strlen($desc) > 600) {
                    $errores[] = 'Describe tu servicio (entre 20 y 600 letras).';
                }
                if (!is_numeric($exp) || $exp < 0 || $exp > 50) {
                    $errores[] = 'Los años de experiencia deben estar entre 0 y 50.';
                }
                if (!in_array($macro, self::MACROS, true)) {
                    $errores[] = 'Elige un macrodistrito válido.';
                }
                if (mb_strlen($zona) < 3 || mb_strlen($zona) > 150) {
                    $errores[] = 'Escribe tu zona de trabajo (mínimo 3 letras).';
                }
                if ($tarifa !== null && $tarifa !== '' && (!is_numeric($tarifa) || $tarifa < 0 || $tarifa > 10000)) {
                    $errores[] = 'La tarifa base no es válida.';
                }
                $tarifa = ($tarifa === null || $tarifa === '') ? 0 : (float) $tarifa;
            }

            if ($errores) {
                $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
            }

            // Celular no repetido
            $stmt = $this->db->prepare('SELECT 1 FROM usuarios WHERE celular = :c AND id_usuario <> :u LIMIT 1');
            $stmt->execute([':c' => $celular, ':u' => $id]);
            if ($stmt->fetchColumn()) {
                $this->responder(['ok' => false, 'error' => 'Ese número de celular ya está registrado en otra cuenta.'], 409);
            }

            $this->db->beginTransaction();

            if ($rol === self::ROL_CLIENTE) {
                $this->db->prepare('UPDATE usuarios SET nombre = :n, apellido = :a, celular = :c WHERE id_usuario = :u')
                    ->execute([
                        ':n' => Utilidades::titulo($nombres),
                        ':a' => Utilidades::titulo($apellido),
                        ':c' => $celular, ':u' => $id,
                    ]);

                $stmt = $this->db->prepare("
                    UPDATE clientes
                       SET direccion_referencia = :d, zona = :z,
                           latitud_predeterminada  = COALESCE(:lat, latitud_predeterminada),
                           longitud_predeterminada = COALESCE(:lng, longitud_predeterminada)
                     WHERE id_usuario = :u
                ");
                $stmt->bindValue(':d', $dir);
                $stmt->bindValue(':z', $zona);
                $stmt->bindValue(':lat', $lat === null ? null : (string) $lat, $lat === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':lng', $lng === null ? null : (string) $lng, $lng === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':u', $id, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Nombre y categoría NO se editan: están ligados a documentos ya verificados
                $this->db->prepare('UPDATE usuarios SET celular = :c WHERE id_usuario = :u')
                    ->execute([':c' => $celular, ':u' => $id]);

                $this->db->prepare("
                    UPDATE profesionales
                       SET descripcion_servicio = :d, experiencia_anios = :e,
                           macrodistrito_base = :m, zona_especifica = :z, tarifa_base = :t
                     WHERE id_usuario = :u
                ")->execute([
                    ':d' => $desc, ':e' => (int) $exp, ':m' => $macro,
                    ':z' => $zona, ':t' => $tarifa, ':u' => $id,
                ]);
            }

            Utilidades::auditar($this->db, $id, 'PERFIL_ACTUALIZADO_APP', 'usuarios', $id, ['rol' => $rol]);
            $this->db->commit();

            $this->responder(['ok' => true, 'usuario' => $this->datosUsuario($id), 'mensaje' => 'Perfil actualizado.']);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       FOTO DE PERFIL
       ========================================================== */
    public function foto() {
        $this->soloPost();
        $nuevoArchivo = null;
        try {
            $u  = $this->usuarioActual();
            $id = (int) $u['id_usuario'];
            $d  = $this->leerJson();

            $errores = [];
            $img = Utilidades::imagen($d['imagen_base64'] ?? null, 'perfil', $errores);
            if ($errores) {
                $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
            }

            $stmt = $this->db->prepare('SELECT foto_perfil FROM usuarios WHERE id_usuario = :u');
            $stmt->execute([':u' => $id]);
            $anterior = $stmt->fetchColumn();

            $dir = Utilidades::carpetaUploads('perfiles');
            $nombre = 'PERFIL_' . $id . '_' . bin2hex(random_bytes(8)) . '.' . $img['ext'];
            if (file_put_contents($dir . $nombre, $img['bin']) === false) {
                throw new RuntimeException('No se pudo guardar la foto.');
            }
            $nuevoArchivo = $dir . $nombre;

            $this->db->prepare('UPDATE usuarios SET foto_perfil = :f WHERE id_usuario = :u')
                ->execute([':f' => 'uploads/perfiles/' . $nombre, ':u' => $id]);

            // Borra la foto anterior solo si estaba en nuestra carpeta
            if ($anterior && strpos((string) $anterior, 'uploads/perfiles/') === 0) {
                $viejo = $dir . basename((string) $anterior);
                if (is_file($viejo)) {
                    @unlink($viejo);
                }
            }

            $this->responder(['ok' => true, 'usuario' => $this->datosUsuario($id), 'mensaje' => 'Foto actualizada.']);
        } catch (Throwable $e) {
            if ($nuevoArchivo && is_file($nuevoArchivo)) {
                @unlink($nuevoArchivo);
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       CAMBIAR CONTRASEÑA (renueva el token y cierra las demás sesiones)
       ========================================================== */
    public function password() {
        $this->soloPost();
        try {
            $u  = $this->usuarioActual();
            $id = (int) $u['id_usuario'];
            $d  = $this->leerJson();

            $actual = (string) ($d['actual'] ?? '');
            $nueva  = (string) ($d['nueva'] ?? '');

            if (!Utilidades::passwordValida($nueva)) {
                $this->responder(['ok' => false, 'error' =>
                    'La nueva contraseña debe tener entre 8 y 64 caracteres, sin espacios, con letras y números.'], 422);
            }

            $stmt = $this->db->prepare('SELECT password FROM usuarios WHERE id_usuario = :u');
            $stmt->execute([':u' => $id]);
            $hash = (string) $stmt->fetchColumn();

            if (!password_verify($actual, $hash)) {
                $this->responder(['ok' => false, 'error' => 'Tu contraseña actual no es correcta.'], 403);
            }
            if (password_verify($nueva, $hash)) {
                $this->responder(['ok' => false, 'error' => 'La nueva contraseña debe ser distinta a la actual.'], 422);
            }

            $token = bin2hex(random_bytes(32));
            $this->db->prepare('UPDATE usuarios SET password = :p, api_token = :t WHERE id_usuario = :u')
                ->execute([':p' => password_hash($nueva, PASSWORD_BCRYPT), ':t' => $token, ':u' => $id]);

            Utilidades::auditar($this->db, $id, 'PASSWORD_CAMBIADA_APP', 'usuarios', $id, ['origen' => 'app_movil']);

            $this->responder(['ok' => true, 'token' => $token, 'mensaje' => 'Contraseña actualizada.']);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       AUXILIARES
       ========================================================== */
    private function usuarioActual(): array {
        $u = ApiAuth::exigir($this->db);
        $rol = (int) $u['id_rol'];
        if ($rol !== self::ROL_CLIENTE && $rol !== self::ROL_PROFESIONAL) {
            $this->responder(['ok' => false, 'error' => 'Esta sección no está disponible para tu tipo de cuenta.'], 403);
        }
        return $u;
    }

    private function datosUsuario(int $id): array {
        $stmt = $this->db->prepare("
            SELECT id_usuario, id_rol, nombre, apellido, correo, celular, foto_perfil
              FROM usuarios WHERE id_usuario = :u
        ");
        $stmt->execute([':u' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function soloPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
    }

    private function leerJson(): array {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiPerfil] ' . $e->getMessage());
        $this->responder(['ok' => false, 'error' => 'No pudimos procesar tu solicitud. Intenta de nuevo.'], 500);
    }
}