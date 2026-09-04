<?php
class Solicitud {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear(int $idCliente, int $idProfesional, array $data): int {
        $codigo = 'GEO-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $this->db->prepare("
            INSERT INTO solicitudes_servicio (
                codigo_seguimiento, id_cliente, id_profesional, descripcion_problema,
                direccion_servicio, macrodistrito, zona, latitud_destino, longitud_destino
            ) VALUES (
                :codigo, :id_cliente, :id_profesional, :descripcion,
                :direccion, :macrodistrito, :zona, :lat, :lng
            )
        ");
        $stmt->execute([
            ':codigo'        => $codigo,
            ':id_cliente'    => $idCliente,
            ':id_profesional'=> $idProfesional,
            ':descripcion'   => trim($data['descripcion_problema']),
            ':direccion'     => trim($data['direccion_servicio']),
            ':macrodistrito' => $data['macrodistrito'],
            ':zona'          => trim($data['zona']),
            ':lat'           => (float) $data['latitud_destino'],
            ':lng'           => (float) $data['longitud_destino']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function obtenerPorId(int $idSolicitud): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   uc.nombre AS cliente_nombre, uc.apellido AS cliente_apellido, uc.celular AS cliente_celular,
                   up.nombre AS prof_nombre, up.apellido AS prof_apellido, up.celular AS prof_celular
            FROM solicitudes_servicio s
            INNER JOIN clientes cl ON s.id_cliente = cl.id_cliente
            INNER JOIN usuarios uc ON cl.id_usuario = uc.id_usuario
            INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
            INNER JOIN usuarios up ON p.id_usuario = up.id_usuario
            WHERE s.id_solicitud = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $idSolicitud]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function listarPorCliente(int $idCliente): array {
        $stmt = $this->db->prepare("
            SELECT s.id_solicitud, s.codigo_seguimiento, s.descripcion_problema, s.estado_servicio, s.fecha_solicitud,
                   u.nombre AS prof_nombre, u.apellido AS prof_apellido, c.nombre_categoria
            FROM solicitudes_servicio s
            INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE s.id_cliente = :id_cliente
            ORDER BY s.fecha_solicitud DESC
        ");
        $stmt->execute([':id_cliente' => $idCliente]);
        return $stmt->fetchAll();
    }

    public function listarPorProfesional(int $idProfesional, ?string $estado = null): array {
        $sql = "
            SELECT s.id_solicitud, s.codigo_seguimiento, s.descripcion_problema, s.direccion_servicio,
                   s.zona, s.estado_servicio, s.fecha_solicitud,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.celular AS cliente_celular
            FROM solicitudes_servicio s
            INNER JOIN clientes cl ON s.id_cliente = cl.id_cliente
            INNER JOIN usuarios u ON cl.id_usuario = u.id_usuario
            WHERE s.id_profesional = :id_prof
        ";
        $params = [':id_prof' => $idProfesional];
        if ($estado) {
            $sql .= " AND s.estado_servicio = :estado";
            $params[':estado'] = $estado;
        }
        $sql .= " ORDER BY s.fecha_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // SPRINT 1: LÓGICA DE TRANSACCIONES Y TOKENS (ACID)
    public function cambiarEstado(int $idSolicitud, string $nuevoEstado, int $idProfesionalSolicitante, ?int $tiempoEstimado = null, ?float $precioAcordado = null): void {
        $validos = ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO', 'FINALIZADA', 'CANCELADA'];
        if (!in_array($nuevoEstado, $validos, true)) {
            throw new Exception("Estado de servicio inválido.");
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT id_profesional, estado_servicio FROM solicitudes_servicio WHERE id_solicitud = :id LIMIT 1 FOR UPDATE");
            $stmt->execute([':id' => $idSolicitud]);
            $sol = $stmt->fetch();

            if (!$sol || (int) $sol['id_profesional'] !== $idProfesionalSolicitante) {
                throw new Exception("No tiene permiso para modificar esta solicitud.");
            }

            if ($nuevoEstado === 'ACEPTADA' && $sol['estado_servicio'] === 'PENDIENTE') {
                $stmtToken = $this->db->prepare("SELECT tokens_disponibles FROM profesionales WHERE id_profesional = :id_prof FOR UPDATE");
                $stmtToken->execute([':id_prof' => $idProfesionalSolicitante]);
                $prof = $stmtToken->fetch();

                if (!$prof || $prof['tokens_disponibles'] <= 0) {
                    throw new Exception("No tienes tokens suficientes. Por favor, recarga tu membresía.");
                }
                $stmtUpdateToken = $this->db->prepare("UPDATE profesionales SET tokens_disponibles = tokens_disponibles - 1 WHERE id_profesional = :id_prof");
                $stmtUpdateToken->execute([':id_prof' => $idProfesionalSolicitante]);
            }

            // AQUÍ INTEGRAMOS LOS NUEVOS CAMPOS DE LA BASE DE DATOS
            $campoExtra = '';
            $params = [':estado' => $nuevoEstado, ':id' => $idSolicitud];

            if ($nuevoEstado === 'ACEPTADA' && $tiempoEstimado !== null) {
                $campoExtra .= ", tiempo_estimado_llegada_min = :tiempo";
                $params[':tiempo'] = $tiempoEstimado;
            }
            if ($nuevoEstado === 'EN_PROCESO') {
                $campoExtra .= ", fecha_inicio_atencion = NOW()";
            }
            if ($nuevoEstado === 'FINALIZADA') {
                $campoExtra .= ", fecha_finalizacion = NOW()";
                if ($precioAcordado !== null) {
                    $campoExtra .= ", precio_acordado = :precio";
                    $params[':precio'] = $precioAcordado;
                }
            }

            $stmtUpdateSol = $this->db->prepare("
                UPDATE solicitudes_servicio SET estado_servicio = :estado {$campoExtra}
                WHERE id_solicitud = :id
            ");
            $stmtUpdateSol->execute($params);
            $this->db->commit();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}