<?php
/**
 *   GET  /api/favorito/lista                 -> ids + profesionales favoritos
 *   POST /api/favorito/alternar/{id_prof}    -> marca o quita el favorito
 * Solo clientes (rol 4).
 */
class ApiFavoritoController {
    private PDO $db;

    private const ROL_CLIENTE = 4;
    private const DEBUG = true;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function lista() {
        try {
            $idCliente = $this->idCliente();

            $stmt = $this->db->prepare(ConsultasProfesional::base() . "
                 INNER JOIN clientes_favoritos f ON f.id_profesional = p.id_profesional
                 WHERE f.id_cliente = :c
                   AND p.estado_validacion = 'APROBADO'
                   AND u.estado = 'ACTIVO'
                 ORDER BY f.fecha_agregado DESC, f.id_favorito DESC
                 LIMIT 100
            ");
            $stmt->bindValue(':c', $idCliente, PDO::PARAM_INT);
            $stmt->execute();
            $profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare('SELECT id_profesional FROM clientes_favoritos WHERE id_cliente = :c');
            $stmt->execute([':c' => $idCliente]);
            $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

            $this->responder(['ok' => true, 'ids' => $ids, 'profesionales' => $profesionales]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    public function alternar($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
        try {
            $idCliente = $this->idCliente();
            $idPro = (int) $id;

            $stmt = $this->db->prepare("
                SELECT 1 FROM profesionales WHERE id_profesional = :p AND estado_validacion = 'APROBADO'
            ");
            $stmt->execute([':p' => $idPro]);
            if (!$stmt->fetchColumn()) {
                $this->responder(['ok' => false, 'error' => 'No encontramos a este profesional.'], 404);
            }

            $stmt = $this->db->prepare('SELECT id_favorito FROM clientes_favoritos WHERE id_cliente = :c AND id_profesional = :p');
            $stmt->execute([':c' => $idCliente, ':p' => $idPro]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $this->db->prepare('DELETE FROM clientes_favoritos WHERE id_favorito = :f')
                    ->execute([':f' => $existe]);
                $es = false;
            } else {
                $this->db->prepare('INSERT INTO clientes_favoritos (id_cliente, id_profesional) VALUES (:c, :p)')
                    ->execute([':c' => $idCliente, ':p' => $idPro]);
                $es = true;
            }

            $this->responder(['ok' => true, 'es_favorito' => $es]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

        private function idCliente(): int {
        $usuario = ApiAuth::exigir($this->db);
        $id = Utilidades::idClienteDe($this->db, $usuario, true);
        if ($id <= 0) {
            $this->responder(['ok' => false, 'error' => 'Los favoritos son solo para clientes.'], 403);
        }
        return $id;
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiFavorito] ' . $e->getMessage());
        $resp = ['ok' => false, 'error' => 'No pudimos procesar tus favoritos. Intenta de nuevo.'];
        if (Utilidades::debug()) {
            $resp['detalle'] = $e->getMessage();
        }
        $this->responder($resp, 500);
    }
}