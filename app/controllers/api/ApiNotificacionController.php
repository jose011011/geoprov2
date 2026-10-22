<?php
/**
 *   GET  /api/notificacion/lista
 *   GET  /api/notificacion/contador
 *   POST /api/notificacion/leer/{id}
 *   POST /api/notificacion/leer_todas
 * Cualquier usuario con sesión; cada uno ve solo las suyas.
 */
class ApiNotificacionController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function lista() {
        try {
            $u = ApiAuth::exigir($this->db);
            $id = (int) $u['id_usuario'];

            // Las alertas "nueva solicitud" ya leídas (tomadas por otro o canceladas) no se muestran
            $stmt = $this->db->prepare("
                SELECT id_notificacion, tipo, mensaje, url_destino, leida,
                       TIMESTAMPDIFF(SECOND, fecha_creacion, NOW()) AS hace_seg
                  FROM notificaciones
                 WHERE id_usuario = :u
                   AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                   AND NOT (tipo = 'NUEVA_SOLICITUD' AND leida = 1)
                 ORDER BY id_notificacion DESC
                 LIMIT 60
            ");
            $stmt->bindValue(':u', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->responder([
                'ok' => true,
                'no_leidas' => $this->contar($id),
                'notificaciones' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    public function contador() {
        try {
            $u = ApiAuth::exigir($this->db);
            $this->responder(['ok' => true, 'no_leidas' => $this->contar((int) $u['id_usuario'])]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    public function leer($id = 0) {
        $this->soloPost();
        try {
            $u = ApiAuth::exigir($this->db);
            $stmt = $this->db->prepare("
                UPDATE notificaciones SET leida = 1
                 WHERE id_notificacion = :n AND id_usuario = :u
            ");
            $stmt->bindValue(':n', (int) $id, PDO::PARAM_INT);
            $stmt->bindValue(':u', (int) $u['id_usuario'], PDO::PARAM_INT);
            $stmt->execute();
            $this->responder(['ok' => true]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    public function leer_todas() {
        $this->soloPost();
        try {
            $u = ApiAuth::exigir($this->db);
            $stmt = $this->db->prepare('UPDATE notificaciones SET leida = 1 WHERE id_usuario = :u AND leida = 0');
            $stmt->execute([':u' => $u['id_usuario']]);
            $this->responder(['ok' => true]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ---------------- Auxiliares ---------------- */

    private function contar(int $idUsuario): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notificaciones
             WHERE id_usuario = :u AND leida = 0
               AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 DAY)
        ");
        $stmt->execute([':u' => $idUsuario]);
        return (int) $stmt->fetchColumn();
    }

    private function soloPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiNotificacion] ' . $e->getMessage());
        $this->responder(['ok' => false, 'error' => 'No pudimos cargar tus notificaciones.'], 500);
    }
}