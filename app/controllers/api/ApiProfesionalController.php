<?php
/**
 * API del PROFESIONAL (requiere token y rol 3)
 *   GET  /api/profesional/panel
 *   POST /api/profesional/disponibilidad
 *   GET  /api/profesional/alertas
 *   POST /api/profesional/aceptar/{id}
 *   GET  /api/profesional/trabajo/{id}
 *   POST /api/profesional/en_camino/{id}
 *   POST /api/profesional/llegue/{id}
 *   POST /api/profesional/finalizar/{id}
 *   POST /api/profesional/ubicacion
 *   GET  /api/profesional/historial
 */
class ApiProfesionalController {
    private PDO $db;

    private const ROL_PROFESIONAL = 3;
    private const RADIO_KM = 10;

    /** true = las respuestas de error incluyen el detalle técnico. Pon false al subir al hosting. */
    private const DEBUG = true;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       PANEL (Centro de Mando)
       ========================================================== */
    public function panel() {
        try {
            $p  = $this->profesionalActual();
            $id = (int) $p['id_profesional'];
                        // Latido de presencia: la app abierta indica que el profesional sigue conectado
            if (in_array($p['estado_disponibilidad'], ['DISPONIBLE', 'OCUPADO'], true)) {
                $this->db->prepare('UPDATE profesionales SET ultimo_latido_app = NOW() WHERE id_profesional = :p')
                         ->execute([':p' => $id]);
            }

            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM solicitudes_servicio
                 WHERE id_profesional = :p AND estado_servicio = 'FINALIZADA'
            ");
            $stmt->execute([':p' => $id]);
            $completados = (int) $stmt->fetchColumn();

            $stmt = $this->db->prepare("
                SELECT COALESCE(ROUND(AVG(cal.puntuacion_general), 2), 5.00) AS promedio,
                       COUNT(cal.id_calificacion) AS total
                  FROM calificaciones cal
                  INNER JOIN solicitudes_servicio s ON s.id_solicitud = cal.id_solicitud
                 WHERE s.id_profesional = :p
            ");
            $stmt->execute([':p' => $id]);
            $rep = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare($this->sqlTrabajo() . "
                 WHERE s.id_profesional = :p
                   AND s.estado_servicio IN ('ACEPTADA', 'EN_CAMINO', 'EN_PROCESO')
                 ORDER BY s.id_solicitud DESC
                 LIMIT 1
            ");
            $stmt->execute([':p' => $id]);
            $activo = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            $this->responder([
                'ok' => true,
                'perfil' => [
                    'nombre'                => $p['nombre'],
                    'apellido'              => $p['apellido'],
                    'nombre_categoria'      => $p['nombre_categoria'],
                    'nombre_plan'           => $p['nombre_plan'],
                    'estado_disponibilidad' => $p['estado_disponibilidad'],
                    'estado_validacion'     => $p['estado_validacion'],
                    'macrodistrito_base'    => $p['macrodistrito_base'],
                    'tokens_disponibles'    => (int) $p['tokens_disponibles'],
                    'ilimitado'             => $this->esIlimitado($id),
                    'latitud_actual'        => $p['latitud_actual'],
                    'longitud_actual'       => $p['longitud_actual'],
                ],
                'stats' => [
                    'promedio'             => (float) $rep['promedio'],
                    'total_calificaciones' => (int) $rep['total'],
                    'completados'          => $completados,
                ],
                'trabajo_activo' => $activo,
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       DISPONIBILIDAD (toggle del radar)
       ========================================================== */
    public function disponibilidad() {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();

            $quiere = filter_var($d['disponible'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $lat = $d['latitud'] ?? null;
            $lng = $d['longitud'] ?? null;
            if ($lat !== null || $lng !== null) {
                if (!$this->coordValida($lat, $lng)) {
                    $this->responder(['ok' => false, 'error' => 'La ubicación no es válida.'], 422);
                }
            }

            if ($p['estado_validacion'] !== 'APROBADO') {
                $this->responder(['ok' => false,
                    'error' => 'Tu cuenta aún está en revisión. Podrás conectarte cuando sea aprobada.'], 403);
            }
            if ($this->tieneTrabajoEnCurso((int) $p['id_profesional'])) {
                $this->responder(['ok' => false,
                    'error' => 'Tienes un trabajo en curso. Finalízalo para cambiar tu disponibilidad.'], 409);
            }

            $stmt = $this->db->prepare("
                UPDATE profesionales
                   SET estado_disponibilidad = :e,
                       latitud_actual  = COALESCE(:lat, latitud_actual),
                       longitud_actual = COALESCE(:lng, longitud_actual),
                       ultima_conexion = NOW()
                 WHERE id_profesional = :id
            ");
            $stmt->bindValue(':e', $quiere ? 'DISPONIBLE' : 'NO_DISPONIBLE');
            $stmt->bindValue(':lat', $lat === null ? null : (string) $lat, $lat === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':lng', $lng === null ? null : (string) $lng, $lng === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':id', (int) $p['id_profesional'], PDO::PARAM_INT);
            $stmt->execute();

            $this->responder(['ok' => true, 'estado_disponibilidad' => $quiere ? 'DISPONIBLE' : 'NO_DISPONIBLE']);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       ALERTAS: solicitudes pendientes que le corresponden
       (directas a él, o abiertas de su rubro en su zona / a menos de 10 km)
       ========================================================== */
    public function alertas() {
        try {
            $p = $this->profesionalActual();

            if ($p['estado_validacion'] !== 'APROBADO' || $p['estado_disponibilidad'] !== 'DISPONIBLE') {
                $this->responder(['ok' => true, 'conectado' => false, 'alertas' => []]);
            }

            $lat = $p['latitud_actual'];
            $lng = $p['longitud_actual'];
            $conUbicacion = $lat !== null && $lng !== null;

            $dist = $conUbicacion
                ? "(6371 * ACOS(LEAST(1,
                        COS(RADIANS(:lat1)) * COS(RADIANS(s.latitud_destino))
                        * COS(RADIANS(s.longitud_destino) - RADIANS(:lng1))
                        + SIN(RADIANS(:lat2)) * SIN(RADIANS(s.latitud_destino))
                   )))"
                : 'NULL';

            $sql = "
                SELECT t.* FROM (
                    SELECT s.id_solicitud, s.codigo_seguimiento, s.descripcion_problema,
                           s.direccion_servicio, s.zona, s.macrodistrito,
                           s.latitud_destino, s.longitud_destino, s.fecha_solicitud,
                           s.id_profesional, s.id_categoria, c.nombre_categoria,
                           uc.id_usuario AS cli_user, uc.nombre AS cli_nombre,
                           $dist AS distancia_km
                      FROM solicitudes_servicio s
                      LEFT JOIN categorias c ON c.id_categoria = s.id_categoria
                      INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
                      INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
                     WHERE s.estado_servicio = 'PENDIENTE'
                ) t
                WHERE t.cli_user <> :yo
                  AND (
                        t.id_profesional = :me
                        OR (
                            t.id_profesional IS NULL
                            AND t.id_categoria = :cat
                            AND (
                                t.macrodistrito = :macro
                                OR (t.distancia_km IS NOT NULL AND t.distancia_km <= :radio)
                            )
                        )
                  )
                ORDER BY t.fecha_solicitud DESC
                LIMIT 30
            ";

            $params = [
                ':yo'    => $p['id_usuario'],
                ':me'    => $p['id_profesional'],
                ':cat'   => $p['id_categoria'],
                ':macro' => $p['macrodistrito_base'],
                ':radio' => self::RADIO_KM,
            ];
            if ($conUbicacion) {
                $params[':lat1'] = (string) $lat;
                $params[':lng1'] = (string) $lng;
                $params[':lat2'] = (string) $lat;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as &$f) {
                unset($f['cli_user'], $f['id_categoria']);
            }
            unset($f);

            $this->responder(['ok' => true, 'conectado' => true, 'alertas' => $filas]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       ACEPTAR (atómico: token + toma de la solicitud)
       ========================================================== */
    public function aceptar($id = 0) {
        $this->soloPost();
        try {
            $p     = $this->profesionalActual();
            $idPro = (int) $p['id_profesional'];
            $id    = (int) $id;

            if ($p['estado_validacion'] !== 'APROBADO') {
                $this->abortar(403, 'Tu cuenta aún está en revisión.');
            }
            if ($p['estado_disponibilidad'] !== 'DISPONIBLE') {
                $this->abortar(409, 'Conéctate (Disponible) para aceptar trabajos.');
            }
            if ($this->tieneTrabajoEnCurso($idPro)) {
                $this->abortar(409, 'Ya tienes un trabajo en curso. Finalízalo antes de aceptar otro.');
            }

            $this->db->beginTransaction();

            // Bloquea la fila para que dos técnicos no la tomen a la vez
            $stmt = $this->db->prepare("
                SELECT id_profesional, id_categoria, estado_servicio, id_cliente
                  FROM solicitudes_servicio
                 WHERE id_solicitud = :id
                 FOR UPDATE
            ");
            $stmt->execute([':id' => $id]);
            $sol = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sol) {
                $this->abortar(404, 'No encontramos esta solicitud.');
            }
            if ($sol['estado_servicio'] === 'CANCELADA') {
                $this->abortar(409, 'El cliente canceló esta solicitud.');
            }
            if ($sol['estado_servicio'] !== 'PENDIENTE'
                || ($sol['id_profesional'] !== null && (int) $sol['id_profesional'] !== $idPro)) {
                $this->abortar(409, 'Esta solicitud ya fue tomada por otro profesional.');
            }
            if ($sol['id_profesional'] === null && (int) $sol['id_categoria'] !== (int) $p['id_categoria']) {
                $this->abortar(403, 'Esta solicitud no es de tu especialidad.');
            }

            $stmt = $this->db->prepare('SELECT id_usuario FROM clientes WHERE id_cliente = :c');
            $stmt->execute([':c' => $sol['id_cliente']]);
            if ((int) $stmt->fetchColumn() === (int) $p['id_usuario']) {
                $this->abortar(422, 'No puedes aceptar tu propia solicitud.');
            }

            // 1 token por aceptar (los planes con propuestas ilimitadas no gastan tokens)
            if (!$this->esIlimitado($idPro)) {
                $stmt = $this->db->prepare("
                    UPDATE profesionales
                       SET tokens_disponibles = tokens_disponibles - 1
                     WHERE id_profesional = :p AND tokens_disponibles >= 1
                ");
                $stmt->execute([':p' => $idPro]);
                if ($stmt->rowCount() === 0) {
                    $this->abortar(402, 'No tienes tokens disponibles. Compra más para aceptar trabajos.');
                }
            }

            // Toma la solicitud
            $stmt = $this->db->prepare("
                UPDATE solicitudes_servicio
                   SET id_profesional = :p, estado_servicio = 'ACEPTADA'
                 WHERE id_solicitud = :id
                   AND estado_servicio = 'PENDIENTE'
                   AND (id_profesional IS NULL OR id_profesional = :p2)
            ");
            $stmt->execute([':p' => $idPro, ':id' => $id, ':p2' => $idPro]);
            if ($stmt->rowCount() === 0) {
                $this->abortar(409, 'Esta solicitud ya fue tomada por otro profesional.');
            }

            $stmt = $this->db->prepare("UPDATE profesionales SET estado_disponibilidad = 'OCUPADO' WHERE id_profesional = :p");
            $stmt->execute([':p' => $idPro]);

            $stmt = $this->db->prepare('SELECT tokens_disponibles FROM profesionales WHERE id_profesional = :p');
            $stmt->execute([':p' => $idPro]);
            $tokens = (int) $stmt->fetchColumn();

            $this->db->commit();

            $this->responder(['ok' => true, 'tokens_restantes' => $tokens, 'mensaje' => 'Trabajo aceptado.']);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       DETALLE DE UN TRABAJO PROPIO
       ========================================================== */
    public function trabajo($id = 0) {
        try {
            $p = $this->profesionalActual();
            $stmt = $this->db->prepare($this->sqlTrabajo() . "
                 WHERE s.id_solicitud = :id AND s.id_profesional = :p
                 LIMIT 1
            ");
            $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $stmt->bindValue(':p', (int) $p['id_profesional'], PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                $this->responder(['ok' => false, 'error' => 'No encontramos este trabajo.'], 404);
            }
            $this->responder(['ok' => true, 'trabajo' => $fila]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       CICLO DEL SERVICIO
       ========================================================== */
    public function en_camino($id = 0) {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();
            $eta = (int) ($d['eta_min'] ?? 0);
            if ($eta < 1 || $eta > 180) {
                $this->responder(['ok' => false, 'error' => 'Indica en cuántos minutos llegarás (de 1 a 180).'], 422);
            }

            $n = $this->cambiarEstado((int) $id, (int) $p['id_profesional'],
                'ACEPTADA', 'EN_CAMINO', ', tiempo_estimado_llegada_min = :eta', [':eta' => $eta]);
            if ($n === 0) {
                $this->responder(['ok' => false, 'error' => 'No se puede iniciar el viaje en el estado actual.'], 409);
            }
            $this->responder(['ok' => true, 'mensaje' => 'Viaje iniciado.']);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    public function llegue($id = 0) {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $n = $this->cambiarEstado((int) $id, (int) $p['id_profesional'],
                'EN_CAMINO', 'EN_PROCESO', ', fecha_inicio_atencion = NOW()');
            if ($n === 0) {
                $this->responder(['ok' => false, 'error' => 'Primero debes iniciar el viaje.'], 409);
            }
            $this->responder(['ok' => true, 'mensaje' => 'Llegada registrada.']);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    public function finalizar($id = 0) {
        $this->soloPost();
        try {
            $p     = $this->profesionalActual();
            $idPro = (int) $p['id_profesional'];
            $d     = $this->leerJson();

            $precio = $d['precio'] ?? null;
            if ($precio !== null && $precio !== '') {
                if (!is_numeric($precio) || $precio < 0 || $precio > 100000) {
                    $this->responder(['ok' => false, 'error' => 'El monto no es válido.'], 422);
                }
                $precio = (float) $precio;
            } else {
                $precio = null;
            }

            $this->db->beginTransaction();

            $n = $this->cambiarEstado((int) $id, $idPro, 'EN_PROCESO', 'FINALIZADA',
                ', fecha_finalizacion = NOW(), precio_acordado = COALESCE(:precio, precio_acordado)',
                [':precio' => $precio]);
            if ($n === 0) {
                $this->abortar(409, 'Primero debes registrar tu llegada al domicilio.');
            }

            $stmt = $this->db->prepare("
                UPDATE profesionales SET estado_disponibilidad = 'DISPONIBLE'
                 WHERE id_profesional = :p AND estado_disponibilidad = 'OCUPADO'
            ");
            $stmt->execute([':p' => $idPro]);

            $this->db->commit();
            $this->responder(['ok' => true, 'mensaje' => 'Servicio finalizado.']);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       UBICACIÓN EN VIVO
       Actualiza el perfil (lo que lee tu web) y guarda historial en tracking.
       ========================================================== */
    public function ubicacion() {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();

            $lat = $d['latitud'] ?? null;
            $lng = $d['longitud'] ?? null;
            if (!$this->coordValida($lat, $lng)) {
                $this->responder(['ok' => false, 'error' => 'La ubicación no es válida.'], 422);
            }
            $vel = isset($d['velocidad_kmh']) && is_numeric($d['velocidad_kmh'])
                ? max(0, min(300, (float) $d['velocidad_kmh'])) : 0;
            $idPro = (int) $p['id_profesional'];

            $stmt = $this->db->prepare("
                UPDATE profesionales
                   SET latitud_actual = :lat, longitud_actual = :lng, ultima_conexion = NOW()
                 WHERE id_profesional = :p
            ");
            $stmt->execute([':lat' => (string) $lat, ':lng' => (string) $lng, ':p' => $idPro]);

            // Si va en camino, también queda en el historial de seguimiento
            $stmt = $this->db->prepare("
                SELECT id_solicitud FROM solicitudes_servicio
                 WHERE id_profesional = :p AND estado_servicio = 'EN_CAMINO'
                 ORDER BY id_solicitud DESC LIMIT 1
            ");
            $stmt->execute([':p' => $idPro]);
            $idSolicitud = (int) $stmt->fetchColumn();

            if ($idSolicitud > 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO tracking_solicitud_gps
                        (id_solicitud, id_profesional, latitud, longitud, velocidad_kmh)
                    VALUES (:s, :p, :lat, :lng, :vel)
                ");
                $stmt->execute([
                    ':s' => $idSolicitud, ':p' => $idPro,
                    ':lat' => (string) $lat, ':lng' => (string) $lng, ':vel' => $vel,
                ]);
            }

            $this->responder(['ok' => true]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       HISTORIAL
       ========================================================== */
    public function historial() {
        try {
            $p = $this->profesionalActual();
            $stmt = $this->db->prepare("
                SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio,
                       s.precio_acordado,
                       COALESCE(s.fecha_finalizacion, s.fecha_solicitud) AS fecha,
                       c.nombre_categoria, uc.nombre AS cli_nombre,
                       cal.puntuacion_general AS estrellas
                  FROM solicitudes_servicio s
                  LEFT JOIN categorias c ON c.id_categoria = s.id_categoria
                  INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
                  INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
                  LEFT JOIN calificaciones cal ON cal.id_solicitud = s.id_solicitud
                 WHERE s.id_profesional = :p
                   AND s.estado_servicio IN ('FINALIZADA', 'CANCELADA')
                 ORDER BY fecha DESC
                 LIMIT 50
            ");
            $stmt->bindValue(':p', (int) $p['id_profesional'], PDO::PARAM_INT);
            $stmt->execute();
            $this->responder(['ok' => true, 'historial' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       AUXILIARES
       ========================================================== */
    private function profesionalActual(): array {
        $usuario = ApiAuth::exigir($this->db);
        if ((int) $usuario['id_rol'] !== self::ROL_PROFESIONAL) {
            $this->responder(['ok' => false, 'error' => 'Esta sección es solo para profesionales.'], 403);
        }

        $stmt = $this->db->prepare("
            SELECT p.id_profesional, p.id_usuario, p.id_categoria, p.tipo_prestador,
                   p.macrodistrito_base, p.tokens_disponibles, p.estado_validacion,
                   p.estado_disponibilidad, p.latitud_actual, p.longitud_actual,
                   pl.nombre_plan, c.nombre_categoria
              FROM profesionales p
              INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.id_usuario = :u
             LIMIT 1
        ");
        $stmt->execute([':u' => $usuario['id_usuario']]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            $this->responder(['ok' => false, 'error' => 'No encontramos tu perfil profesional.'], 403);
        }
        $fila['nombre']   = $usuario['nombre'];
        $fila['apellido'] = $usuario['apellido'];
        return $fila;
    }

    private function sqlTrabajo(): string {
        return "
            SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio,
                   s.descripcion_problema, s.direccion_servicio, s.macrodistrito, s.zona,
                   s.latitud_destino, s.longitud_destino, s.precio_acordado,
                   s.tiempo_estimado_llegada_min, s.motivo_cancelacion, s.fecha_solicitud,
                   c.nombre_categoria, c.icono_fa,
                   uc.nombre AS cli_nombre, uc.apellido AS cli_apellido,
                   uc.celular AS cli_celular
              FROM solicitudes_servicio s
              LEFT JOIN categorias c ON c.id_categoria = s.id_categoria
              INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
              INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
        ";
    }

    private function tieneTrabajoEnCurso(int $idPro): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM solicitudes_servicio
             WHERE id_profesional = :p
               AND estado_servicio IN ('ACEPTADA', 'EN_CAMINO', 'EN_PROCESO')
             LIMIT 1
        ");
        $stmt->execute([':p' => $idPro]);
        return (bool) $stmt->fetchColumn();
    }

     /** Plan con "propuestas ilimitadas" (999 o más) y vigente. */
    private function esIlimitado(int $idPro): bool {
        $tok = Utilidades::sqlTokensPlan($this->db, 'pl');
        $stmt = $this->db->prepare("
            SELECT 1
              FROM profesionales p
              INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
             WHERE p.id_profesional = :p
               AND $tok >= 999
               AND p.fin_suscripcion IS NOT NULL
               AND p.fin_suscripcion >= CURDATE()
             LIMIT 1
        ");
        $stmt->execute([':p' => $idPro]);
        return (bool) $stmt->fetchColumn();
    }

    /** Cambia el estado solo si está en el estado esperado. Devuelve filas afectadas. */
    private function cambiarEstado(int $id, int $idPro, string $desde, string $hasta,
                                   string $setExtra = '', array $extra = []): int {
        $sql = "UPDATE solicitudes_servicio
                   SET estado_servicio = :hasta $setExtra
                 WHERE id_solicitud = :id
                   AND id_profesional = :p
                   AND estado_servicio = :desde";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($extra, [
            ':hasta' => $hasta, ':id' => $id, ':p' => $idPro, ':desde' => $desde,
        ]));
        return $stmt->rowCount();
    }

    private function coordValida($lat, $lng): bool {
        return is_numeric($lat) && is_numeric($lng)
            && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
    }

    private function abortar(int $codigo, string $mensaje): void {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        $this->responder(['ok' => false, 'error' => $mensaje], $codigo);
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
        error_log('[ApiProfesional] ' . $e->getMessage());
        $resp = [
            'ok' => false,
            'error' => 'No pudimos procesar tu solicitud. Intenta de nuevo.',
        ];
        if (Utilidades::debug()) {
            $resp['detalle'] = $e->getMessage();
        }
        $this->responder($resp, 500);
    }
}   