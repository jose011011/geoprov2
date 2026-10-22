<?php
/**
 *   POST /api/solicitud/crear
 *   GET  /api/solicitud/mis_solicitudes
 *   GET  /api/solicitud/detalle/{id}
 *   GET  /api/solicitud/seguimiento/{id}
 *   POST /api/solicitud/cancelar/{id}
 *   POST /api/solicitud/calificar/{id}
 * Todas requieren token (Bearer).
 */
class ApiSolicitudController {
    private PDO $db;

    private const MACROS = [
        'ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES',
        'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO'
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       CREAR SOLICITUD
       Sin id_profesional  -> "Enviar a profesionales" (red abierta)
       Con id_profesional  -> "Elegir manualmente" (directa)
       Las notificaciones las generan los triggers de la BD.
       ========================================================== */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }

        $usuario = ApiAuth::exigir($this->db);
        $d = json_decode(file_get_contents('php://input'), true) ?? [];

        $idCategoria   = (int) ($d['id_categoria'] ?? 0);
        $idProfesional = (int) ($d['id_profesional'] ?? 0);
        $descripcion   = trim((string) ($d['descripcion_problema'] ?? ''));
        $direccion     = trim((string) ($d['direccion_servicio'] ?? ''));
        $macro         = strtoupper(trim((string) ($d['macrodistrito'] ?? '')));
        $zona          = trim((string) ($d['zona'] ?? ''));
        $lat           = $d['latitud_destino'] ?? null;
        $lng           = $d['longitud_destino'] ?? null;

        $errores = [];
        if ($idCategoria <= 0) {
            $errores[] = 'Elige una categoría.';
        }
        $lenD = mb_strlen($descripcion);
        if ($lenD < 10 || $lenD > 500) {
            $errores[] = 'Describe el problema (entre 10 y 500 letras).';
        }
        $lenDir = mb_strlen($direccion);
        if ($lenDir < 8 || $lenDir > 255) {
            $errores[] = 'Escribe una dirección más exacta (mínimo 8 letras).';
        }
        if (!in_array($macro, self::MACROS, true)) {
            $errores[] = 'Elige un macrodistrito válido.';
        }
        $lenZ = mb_strlen($zona);
        if ($lenZ < 3 || $lenZ > 100) {
            $errores[] = 'Escribe tu zona o barrio.';
        }
        if (!is_numeric($lat) || !is_numeric($lng)
            || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $errores[] = 'Marca el punto exacto del servicio en el mapa.';
        }
        if ($errores) {
            $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
        }

        try {
            $idCliente = $this->idClienteOFallar($usuario);

            // Calificación obligatoria: no se puede pedir otro servicio sin calificar el anterior
            $stmtP = $this->db->prepare("
                SELECT s.codigo_seguimiento
                  FROM solicitudes_servicio s
                  LEFT JOIN calificaciones cal ON cal.id_solicitud = s.id_solicitud
                 WHERE s.id_cliente = :cli
                   AND s.estado_servicio = 'FINALIZADA'
                   AND cal.id_calificacion IS NULL
                 LIMIT 1
            ");
            $stmtP->bindValue(':cli', $idCliente, PDO::PARAM_INT);
            $stmtP->execute();
            $pendiente = $stmtP->fetchColumn();
            if ($pendiente) {
                $this->responder([
                    'ok' => false,
                    'error' => "Antes de pedir otro servicio, califica el anterior ($pendiente)."
                ], 409);
            }

            // Categoría
            $stmt = $this->db->prepare('SELECT 1 FROM categorias WHERE id_categoria = :c AND estado = 1');
            $stmt->execute([':c' => $idCategoria]);
            if (!$stmt->fetchColumn()) {
                $this->responder(['ok' => false, 'error' => 'La categoría no existe.'], 404);
            }

            // Profesional elegido (modo manual)
            $prof = null;
            if ($idProfesional > 0) {
                $stmt = $this->db->prepare("
                    SELECT p.id_profesional, p.id_usuario, p.id_categoria,
                           p.estado_validacion, p.estado_disponibilidad, u.nombre
                      FROM profesionales p
                      INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
                     WHERE p.id_profesional = :id
                     LIMIT 1
                ");
                $stmt->execute([':id' => $idProfesional]);
                $prof = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$prof) {
                    $this->responder(['ok' => false, 'error' => 'No encontramos a ese profesional.'], 404);
                }
                if ((int) $prof['id_usuario'] === (int) $usuario['id_usuario']) {
                    $this->responder(['ok' => false, 'error' => 'No puedes contratarte a ti mismo.'], 422);
                }
                if ($prof['estado_validacion'] !== 'APROBADO') {
                    $this->responder(['ok' => false, 'error' => 'Este profesional aún no está verificado.'], 422);
                }
                if ($prof['estado_disponibilidad'] !== 'DISPONIBLE') {
                    $this->responder(['ok' => false, 'error' => 'Este profesional no está disponible en este momento.'], 422);
                }
                if ((int) $prof['id_categoria'] !== $idCategoria) {
                    $this->responder(['ok' => false, 'error' => 'El profesional no atiende esta categoría.'], 422);
                }
            }

            $this->db->beginTransaction();

            $codigo = $this->generarCodigo();

            $stmt = $this->db->prepare("
                INSERT INTO solicitudes_servicio
                    (codigo_seguimiento, id_cliente, id_categoria, id_profesional,
                     descripcion_problema, direccion_servicio, macrodistrito, zona,
                     latitud_destino, longitud_destino)
                VALUES
                    (:cod, :cli, :cat, :prof, :desc, :dir, :macro, :zona, :lat, :lng)
            ");
            $stmt->bindValue(':cod', $codigo);
            $stmt->bindValue(':cli', $idCliente, PDO::PARAM_INT);
            $stmt->bindValue(':cat', $idCategoria, PDO::PARAM_INT);
            if ($prof) {
                $stmt->bindValue(':prof', (int) $prof['id_profesional'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':prof', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':desc', $descripcion);
            $stmt->bindValue(':dir', $direccion);
            $stmt->bindValue(':macro', $macro);
            $stmt->bindValue(':zona', $zona);
            $stmt->bindValue(':lat', (string) $lat);
            $stmt->bindValue(':lng', (string) $lng);
            $stmt->execute();

            $idSolicitud = (int) $this->db->lastInsertId();
            $this->db->commit();

            // Cuántos avisos generó el trigger para esta solicitud
            $stmtC = $this->db->prepare("
                SELECT COUNT(*) FROM notificaciones
                 WHERE tipo = 'NUEVA_SOLICITUD' AND url_destino = :u
            ");
            $stmtC->execute([':u' => 'profesional/solicitudes?id=' . $idSolicitud]);
            $notificados = (int) $stmtC->fetchColumn();

            if ($prof) {
                $mensaje = 'Enviamos tu solicitud a ' . $prof['nombre'] . '. Te avisaremos cuando la acepte.';
            } elseif ($notificados > 0) {
                $mensaje = "Avisamos a $notificados técnico(s) disponible(s) en tu zona. El primero en aceptar atenderá tu caso.";
            } else {
                $mensaje = 'Tu solicitud quedó registrada, pero ahora no hay técnicos disponibles en tu zona. Te avisaremos cuando alguien la acepte.';
            }

            $this->responder([
                'ok' => true,
                'id_solicitud' => $idSolicitud,
                'codigo_seguimiento' => $codigo,
                'notificados' => $notificados,
                'mensaje' => $mensaje,
            ], 201);

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            // Los mensajes de tus triggers (SQLSTATE 45000) son seguros de mostrar
            if ($e->getCode() === '45000') {
                $this->responder(['ok' => false, 'error' => $e->errorInfo[2] ?? 'No se pudo crear la solicitud.'], 409);
            }
            $this->fallo($e);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       LISTA DEL CLIENTE
       ========================================================== */
    public function mis_solicitudes() {
        try {
            $usuario   = ApiAuth::exigir($this->db);
            $idCliente = $this->idClienteOFallar($usuario);

            $stmt = $this->db->prepare("
                SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio,
                       s.descripcion_problema, s.direccion_servicio,
                       s.fecha_solicitud, s.precio_acordado,
                       c.nombre_categoria, c.icono_fa,
                       u.nombre AS prof_nombre, u.apellido AS prof_apellido,
                       (SELECT COUNT(*) FROM calificaciones cal
                         WHERE cal.id_solicitud = s.id_solicitud) AS ya_califico
                  FROM solicitudes_servicio s
                  LEFT JOIN categorias c ON c.id_categoria = s.id_categoria
                  LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
                  LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
                 WHERE s.id_cliente = :cli
                 ORDER BY s.fecha_solicitud DESC, s.id_solicitud DESC
                 LIMIT 50
            ");
            $stmt->bindValue(':cli', $idCliente, PDO::PARAM_INT);
            $stmt->execute();

            $this->responder(['ok' => true, 'solicitudes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       DETALLE
       ========================================================== */
    public function detalle($id = 0) {
        try {
            $usuario   = ApiAuth::exigir($this->db);
            $idCliente = $this->idClienteOFallar($usuario);
            $id = (int) $id;

            $stmt = $this->db->prepare("
                SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio,
                       s.descripcion_problema, s.direccion_servicio,
                       s.macrodistrito, s.zona,
                       s.latitud_destino, s.longitud_destino,
                       s.precio_acordado, s.tiempo_estimado_llegada_min,
                       s.motivo_cancelacion, s.fecha_solicitud,
                       s.fecha_inicio_atencion, s.fecha_finalizacion,
                       s.id_profesional,
                       c.nombre_categoria, c.icono_fa,
                       u.nombre AS prof_nombre, u.apellido AS prof_apellido,
                       u.celular AS prof_celular,
                       (SELECT COUNT(*) FROM calificaciones cal
                         WHERE cal.id_solicitud = s.id_solicitud) AS ya_califico
                  FROM solicitudes_servicio s
                  LEFT JOIN categorias c ON c.id_categoria = s.id_categoria
                  LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
                  LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
                 WHERE s.id_solicitud = :id AND s.id_cliente = :cli
                 LIMIT 1
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':cli', $idCliente, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                $this->responder(['ok' => false, 'error' => 'No encontramos esta solicitud.'], 404);
            }

            $fila['ultima_posicion'] = $this->ultimaPosicion(
                $id,
                $fila['estado_servicio'] === 'EN_CAMINO' ? (int) $fila['id_profesional'] : null
            );
            $this->responder(['ok' => true, 'solicitud' => $fila]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       SEGUIMIENTO LIGERO (el mapa lo consulta cada pocos segundos)
       ========================================================== */
    public function seguimiento($id = 0) {
        try {
            $usuario   = ApiAuth::exigir($this->db);
            $idCliente = $this->idClienteOFallar($usuario);
            $id = (int) $id;

            $stmt = $this->db->prepare("
                SELECT estado_servicio, tiempo_estimado_llegada_min, id_profesional
                  FROM solicitudes_servicio
                 WHERE id_solicitud = :id AND id_cliente = :cli
                 LIMIT 1
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':cli', $idCliente, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                $this->responder(['ok' => false, 'error' => 'No encontramos esta solicitud.'], 404);
            }

            $this->responder([
                'ok' => true,
                'estado_servicio' => $fila['estado_servicio'],
                'tiempo_estimado_llegada_min' => $fila['tiempo_estimado_llegada_min'],
                'ultima_posicion' => $this->ultimaPosicion(
                    $id,
                    $fila['estado_servicio'] === 'EN_CAMINO' ? (int) $fila['id_profesional'] : null
                ),
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       CANCELAR (solo si sigue PENDIENTE). El trigger avisa al profesional.
       ========================================================== */
    public function cancelar($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
        try {
            $usuario   = ApiAuth::exigir($this->db);
            $idCliente = $this->idClienteOFallar($usuario);
            $id = (int) $id;

            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            $motivo = trim((string) ($d['motivo'] ?? ''));
            if ($motivo === '') {
                $motivo = 'Cancelada por el cliente';
            }
            $motivo = mb_substr($motivo, 0, 255);

            $stmtE = $this->db->prepare('SELECT 1 FROM solicitudes_servicio WHERE id_solicitud = :id AND id_cliente = :cli');
            $stmtE->execute([':id' => $id, ':cli' => $idCliente]);
            if (!$stmtE->fetchColumn()) {
                $this->responder(['ok' => false, 'error' => 'No encontramos esta solicitud.'], 404);
            }

            $stmt = $this->db->prepare("
                UPDATE solicitudes_servicio
                   SET estado_servicio = 'CANCELADA',
                       motivo_cancelacion = :m,
                       fecha_finalizacion = NOW()
                 WHERE id_solicitud = :id
                   AND id_cliente = :cli
                   AND estado_servicio = 'PENDIENTE'
            ");
            $stmt->bindValue(':m', $motivo);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':cli', $idCliente, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->responder([
                    'ok' => false,
                    'error' => 'Esta solicitud ya no se puede cancelar porque el profesional ya la atendió.'
                ], 409);
            }

            $this->responder(['ok' => true, 'mensaje' => 'Solicitud cancelada.']);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       CALIFICAR (obligatorio al finalizar). Estrellas obligatorias, comentario opcional.
       puntualidad y calidad_trabajo son opcionales: si no vienen, usan la general.
       ========================================================== */
    public function calificar($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
        try {
            $usuario   = ApiAuth::exigir($this->db);
            $idCliente = $this->idClienteOFallar($usuario);
            $id = (int) $id;

            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            $general = (int) ($d['puntuacion_general'] ?? 0);
            $punt    = isset($d['puntualidad']) ? (int) $d['puntualidad'] : $general;
            $calidad = isset($d['calidad_trabajo']) ? (int) $d['calidad_trabajo'] : $general;

            foreach ([$general, $punt, $calidad] as $v) {
                if ($v < 1 || $v > 5) {
                    $this->responder(['ok' => false, 'error' => 'Elige una calificación de 1 a 5 estrellas.'], 422);
                }
            }

            $comentario = trim(strip_tags((string) ($d['comentario'] ?? '')));
            $comentario = mb_substr($comentario, 0, 500);

            $stmt = $this->db->prepare("
                SELECT estado_servicio FROM solicitudes_servicio
                 WHERE id_solicitud = :id AND id_cliente = :cli LIMIT 1
            ");
            $stmt->execute([':id' => $id, ':cli' => $idCliente]);
            $estado = $stmt->fetchColumn();

            if ($estado === false) {
                $this->responder(['ok' => false, 'error' => 'No encontramos esta solicitud.'], 404);
            }
            if ($estado !== 'FINALIZADA') {
                $this->responder(['ok' => false, 'error' => 'Solo puedes calificar servicios finalizados.'], 409);
            }

            $ins = $this->db->prepare("
                INSERT INTO calificaciones
                    (id_solicitud, puntuacion_general, puntualidad, calidad_trabajo, comentario)
                VALUES (:id, :g, :p, :c, :com)
            ");
            $ins->bindValue(':id', $id, PDO::PARAM_INT);
            $ins->bindValue(':g', $general, PDO::PARAM_INT);
            $ins->bindValue(':p', $punt, PDO::PARAM_INT);
            $ins->bindValue(':c', $calidad, PDO::PARAM_INT);
            if ($comentario === '') {
                $ins->bindValue(':com', null, PDO::PARAM_NULL);
            } else {
                $ins->bindValue(':com', $comentario);
            }
            $ins->execute();

            $this->responder(['ok' => true, 'mensaje' => '¡Gracias por tu opinión!'], 201);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->responder(['ok' => false, 'error' => 'Ya calificaste este servicio.'], 409);
            }
            $this->fallo($e);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       AUXILIARES
       ========================================================== */
    private function generarCodigo(): string {
        do {
            $codigo = 'GEO-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $this->db->prepare('SELECT 1 FROM solicitudes_servicio WHERE codigo_seguimiento = :c');
            $stmt->execute([':c' => $codigo]);
        } while ($stmt->fetchColumn());
        return $codigo;
    }

    private function idClienteOFallar(array $usuario): int {
        // Clientes (rol 4) y profesionales en modo cliente (rol 3, se crea su perfil al primer uso)
        $id = Utilidades::idClienteDe($this->db, $usuario, true);
        if ($id <= 0) {
            $this->responder(['ok' => false, 'error' => 'Tu cuenta no puede pedir servicios.'], 403);
        }
        return $id;
    }

    /**
     * Última posición conocida del profesional.
     * 1) tracking_solicitud_gps (lo que envía "Estoy en camino")
     * 2) Respaldo: ubicación del perfil, solo si se actualizó hace menos de 3 minutos
     */
    private function ultimaPosicion(int $idSolicitud, ?int $idProfesional = null): ?array {
        $stmt = $this->db->prepare("
            SELECT latitud, longitud, velocidad_kmh, timestamp_registro,
                   TIMESTAMPDIFF(SECOND, timestamp_registro, NOW()) AS hace_seg
              FROM tracking_solicitud_gps
             WHERE id_solicitud = :id
             ORDER BY timestamp_registro DESC, id_tracking DESC
             LIMIT 1
        ");
        $stmt->bindValue(':id', $idSolicitud, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            $fila['origen'] = 'tracking';
            return $fila;
        }

        if ($idProfesional !== null && $idProfesional > 0) {
            $stmt = $this->db->prepare("
                SELECT latitud_actual AS latitud, longitud_actual AS longitud,
                       0 AS velocidad_kmh, ultima_conexion AS timestamp_registro,
                       TIMESTAMPDIFF(SECOND, ultima_conexion, NOW()) AS hace_seg
                  FROM profesionales
                 WHERE id_profesional = :p
                   AND latitud_actual IS NOT NULL
                   AND longitud_actual IS NOT NULL
                   AND ultima_conexion IS NOT NULL
                   AND ultima_conexion >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                 LIMIT 1
            ");
            $stmt->bindValue(':p', $idProfesional, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fila) {
                $fila['origen'] = 'perfil';
                return $fila;
            }
        }
        return null;
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiSolicitud] ' . $e->getMessage());
        $this->responder([
            'ok' => false,
            'error' => 'No pudimos procesar tu solicitud. Intenta de nuevo.'
        ], 500);
    }
}