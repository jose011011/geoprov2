<?php
/**
 *   POST /api/trabajo/rechazar/{id}   { motivo? }  solicitud directa PENDIENTE para este profesional
 *   POST /api/trabajo/cancelar/{id}   { motivo }   trabajo ACEPTADA o EN_CAMINO
 * Solo profesionales (rol 3). Los avisos al cliente los genera el trigger de la BD.
 */
class ApiTrabajoController {
    private PDO $db;

    private const ROL_PROFESIONAL = 3;

    /** true = si cancela antes de salir (estado ACEPTADA) se le devuelve el token gastado */
    private const DEVOLVER_TOKEN_SI_CANCELA_ANTES_DE_SALIR = false;

    /** true = las respuestas de error incluyen el detalle técnico. Pon false al subir al hosting. */
    private const DEBUG = true;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       RECHAZAR una solicitud enviada directamente a este profesional
       ========================================================== */
    public function rechazar($id = 0) {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();
            $motivo = $this->motivo($d['motivo'] ?? '', 150);
            $texto = 'Rechazada por el profesional' . ($motivo !== '' ? ': ' . $motivo : '');

            $stmt = $this->db->prepare("
                UPDATE solicitudes_servicio
                   SET estado_servicio = 'CANCELADA',
                       motivo_cancelacion = :m,
                       fecha_finalizacion = NOW()
                 WHERE id_solicitud = :id
                   AND id_profesional = :p
                   AND estado_servicio = 'PENDIENTE'
            ");
            $stmt->bindValue(':m', $texto);
            $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $stmt->bindValue(':p', (int) $p['id_profesional'], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->responder(['ok' => false, 'error' =>
                    'Esta solicitud ya no está pendiente o no fue enviada a ti.'], 409);
            }

            Utilidades::auditar($this->db, (int) $p['id_usuario'], 'SOLICITUD_RECHAZADA_APP',
                'solicitudes_servicio', (int) $id, ['motivo' => $motivo]);

            $this->responder(['ok' => true, 'mensaje' => 'Solicitud rechazada. Avisamos al cliente.']);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       CANCELAR un trabajo ya aceptado (antes de llegar al domicilio)
       ========================================================== */
    public function cancelar($id = 0) {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $idPro = (int) $p['id_profesional'];
            $d = $this->leerJson();

            $motivo = $this->motivo($d['motivo'] ?? '', 150);
            if ($motivo === '') {
                $this->responder(['ok' => false, 'error' => 'Indica el motivo de la cancelación.'], 422);
            }
            $texto = 'Cancelada por el profesional: ' . $motivo;

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT estado_servicio FROM solicitudes_servicio
                 WHERE id_solicitud = :id AND id_profesional = :p
                 FOR UPDATE
            ");
            $stmt->execute([':id' => (int) $id, ':p' => $idPro]);
            $estado = $stmt->fetchColumn();

            if ($estado === false) {
                $this->abortar(404, 'No encontramos este trabajo.');
            }
            if ($estado === 'EN_PROCESO') {
                $this->abortar(409, 'Ya estás en el domicilio. Finaliza el servicio o contacta a soporte.');
            }
            if ($estado !== 'ACEPTADA' && $estado !== 'EN_CAMINO') {
                $this->abortar(409, 'Este trabajo ya no se puede cancelar.');
            }

            $stmt = $this->db->prepare("
                UPDATE solicitudes_servicio
                   SET estado_servicio = 'CANCELADA',
                       motivo_cancelacion = :m,
                       fecha_finalizacion = NOW()
                 WHERE id_solicitud = :id AND id_profesional = :p
                   AND estado_servicio IN ('ACEPTADA', 'EN_CAMINO')
            ");
            $stmt->execute([':m' => $texto, ':id' => (int) $id, ':p' => $idPro]);

            // El profesional vuelve a estar libre
            $stmt = $this->db->prepare("
                UPDATE profesionales SET estado_disponibilidad = 'DISPONIBLE'
                 WHERE id_profesional = :p AND estado_disponibilidad = 'OCUPADO'
            ");
            $stmt->execute([':p' => $idPro]);

            $devuelto = false;
            if (self::DEVOLVER_TOKEN_SI_CANCELA_ANTES_DE_SALIR
                && $estado === 'ACEPTADA' && !$this->esIlimitado($idPro)) {
                $stmt = $this->db->prepare('UPDATE profesionales SET tokens_disponibles = tokens_disponibles + 1 WHERE id_profesional = :p');
                $stmt->execute([':p' => $idPro]);
                $devuelto = true;
            }

            Utilidades::auditar($this->db, (int) $p['id_usuario'], 'TRABAJO_CANCELADO_APP',
                'solicitudes_servicio', (int) $id, ['motivo' => $motivo, 'token_devuelto' => $devuelto]);

            $this->db->commit();

            $this->responder([
                'ok' => true,
                'mensaje' => $devuelto
                    ? 'Trabajo cancelado. Te devolvimos el token.'
                    : 'Trabajo cancelado. Avisamos al cliente.',
            ]);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
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
        $stmt = $this->db->prepare('SELECT id_profesional, id_usuario FROM profesionales WHERE id_usuario = :u LIMIT 1');
        $stmt->execute([':u' => $usuario['id_usuario']]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            $this->responder(['ok' => false, 'error' => 'No encontramos tu perfil profesional.'], 403);
        }
        return $fila;
    }

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

    private function motivo($valor, int $max): string {
        $t = Utilidades::limpiar(strip_tags((string) $valor));
        return mb_substr($t, 0, $max);
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
        error_log('[ApiTrabajo] ' . $e->getMessage());
        $resp = ['ok' => false, 'error' => 'No pudimos procesar tu solicitud. Intenta de nuevo.'];
        if (Utilidades::debug()) {
            $resp['detalle'] = $e->getMessage();
        }
        $this->responder($resp, 500);
    }
}