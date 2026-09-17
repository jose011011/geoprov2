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

   public function listarPorProfesional($idProfesional, $estado = null) {
        $db = Database::getInstance()->getConnection();
        
        // Hacemos JOIN con clientes y usuarios para traer el nombre real
        $sql = "
            SELECT s.*, 
                   u.nombre AS cliente_nombre, 
                   u.apellido AS cliente_apellido
            FROM solicitudes_servicio s
            INNER JOIN clientes c ON s.id_cliente = c.id_cliente
            INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE s.id_profesional = :id_profesional
        ";
        
        $params = [':id_profesional' => $idProfesional];

        if ($estado && $estado !== 'TODAS') {
            $sql .= " AND s.estado_servicio = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY s.fecha_solicitud DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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



    /* ========================================================
       ASIGNACIÓN I.A. CASCADA - OBTENER TRABAJOS DISPONIBLES
       ======================================================== */
    public function obtenerOportunidadesCascada($idPlanProfesional, $macrodistritoProf, $idCategoriaProf, $idProfesional = null) {
        $db = Database::getInstance()->getConnection();
        
        // CANDADO: Evitar que vea trabajos si ya tiene uno activo (Si se pasó el ID del profesional)
        if ($idProfesional) {
            $stmtActivo = $db->prepare("SELECT id_solicitud FROM solicitudes_servicio WHERE id_profesional = :id_prof AND estado_servicio IN ('ACEPTADA', 'EN_CAMINO', 'EN_PROCESO') LIMIT 1");
            $stmtActivo->execute([':id_prof' => $idProfesional]);
            if ($stmtActivo->fetch()) return []; // Bandeja vacía
        }

        $minutosRetraso = 7; // Por defecto (Gratuitos)
        if ($idPlanProfesional == 3) {
            $minutosRetraso = 0; // PREMIUM: Lo ve al instante
        } elseif ($idPlanProfesional == 2) {
            $minutosRetraso = 3; // BÁSICO: 3 minutos de retraso
        }
        
        $sql = "
            SELECT s.*, c.zona AS zona_cliente, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
            FROM solicitudes_servicio s
            INNER JOIN clientes c ON s.id_cliente = c.id_cliente
            INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE s.estado_servicio = 'PENDIENTE' 
              AND s.id_profesional IS NULL 
              AND s.id_categoria = :id_categoria
              AND s.macrodistrito = :macrodistrito
              AND s.fecha_solicitud <= DATE_SUB(NOW(), INTERVAL :retraso MINUTE)
            ORDER BY s.fecha_solicitud DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':macrodistrito', $macrodistritoProf, PDO::PARAM_STR);
        $stmt->bindValue(':id_categoria', $idCategoriaProf, PDO::PARAM_INT);
        $stmt->bindValue(':retraso', $minutosRetraso, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ========================================================
       ACEPTAR TRABAJO (El primero que llega se lo queda)
       ======================================================== */
    public function reclamarTrabajo($idSolicitud, $idProfesional) {
        $db = Database::getInstance()->getConnection();

        // 1. Candado de seguridad: Verificar que NO tenga ya un trabajo activo
        $stmtActivo = $db->prepare("
            SELECT id_solicitud 
            FROM solicitudes_servicio 
            WHERE id_profesional = :id_prof 
              AND estado_servicio IN ('ACEPTADA', 'EN_CAMINO', 'EN_PROCESO')
            LIMIT 1
        ");
        $stmtActivo->execute([':id_prof' => $idProfesional]);
        if ($stmtActivo->fetch()) {
            throw new Exception("Ya tienes un trabajo activo. Finalízalo antes de aceptar otro.");
        }
        
        // 2. El UPDATE con 'IS NULL' es un candado de concurrencia. Si alguien más le ganó, esto fallará.
        $stmt = $db->prepare("
            UPDATE solicitudes_servicio 
            SET id_profesional = :id_prof, 
                estado_servicio = 'ACEPTADA' 
            WHERE id_solicitud = :id_sol 
              AND estado_servicio = 'PENDIENTE' 
              AND id_profesional IS NULL
        ");
        
        $stmt->execute([
            ':id_prof' => $idProfesional,
            ':id_sol' => $idSolicitud
        ]);
        
        // Si rowCount > 0, significa que él ganó la carrera
        if ($stmt->rowCount() > 0) {
            // Aquí le descuentas 1 TOKEN al profesional por ganar el trabajo
            $stmtToken = $db->prepare("UPDATE profesionales SET tokens_disponibles = tokens_disponibles - 1 WHERE id_profesional = :id_prof");
            $stmtToken->execute([':id_prof' => $idProfesional]);
            return true;
        }
        
        return false; // Alguien más se lo llevó
    }
}
