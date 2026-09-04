<?php
class Cliente {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorUsuario(int $idUsuario): ?array {
        $stmt = $this->db->prepare("
            SELECT id_cliente, direccion_referencia, zona, latitud_predeterminada, longitud_predeterminada
            FROM clientes WHERE id_usuario = :id_usuario LIMIT 1
        ");
        $stmt->execute([':id_usuario' => $idUsuario]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Crea un perfil de cliente para un usuario que ya es PROFESIONAL,
     * usando su ubicación base como punto de partida.
     */
    public function crearDesdeProfesional(int $idUsuario): ?array {
        $stmtProf = $this->db->prepare("
            SELECT latitud_actual, longitud_actual, zona_especifica, macrodistrito_base
            FROM profesionales WHERE id_usuario = :id_usuario LIMIT 1
        ");
        $stmtProf->execute([':id_usuario' => $idUsuario]);
        $prof = $stmtProf->fetch();

        if (!$prof) {
            return null;
        }

        $stmtInsert = $this->db->prepare("
            INSERT INTO clientes (id_usuario, direccion_referencia, zona, latitud_predeterminada, longitud_predeterminada)
            VALUES (:id_usuario, :direccion, :zona, :lat, :lng)
        ");
        $stmtInsert->execute([
            ':id_usuario' => $idUsuario,
            ':direccion'  => $prof['zona_especifica'] ?: 'Sin especificar',
            ':zona'       => $prof['macrodistrito_base'],
            ':lat'        => $prof['latitud_actual'] ?? -16.50000000,
            ':lng'        => $prof['longitud_actual'] ?? -68.15000000
        ]);

        return $this->buscarPorUsuario($idUsuario);
    }

    public function obtenerCategoriasActivas(): array {
        $stmt = $this->db->query("
            SELECT id_categoria, nombre_categoria, slug, icono_fa
            FROM categorias WHERE estado = 1
            ORDER BY nombre_categoria ASC
        ");
        return $stmt->fetchAll();
    }



    public function obtenerEstadisticas(int $idCliente): array {
    $stmt = $this->db->prepare("
        SELECT
            COUNT(*) AS total_solicitudes,
            COUNT(CASE WHEN estado_servicio IN ('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO') THEN 1 END) AS activas,
            COUNT(CASE WHEN estado_servicio = 'FINALIZADA' THEN 1 END) AS finalizadas
        FROM solicitudes_servicio
        WHERE id_cliente = :id
    ");
    $stmt->execute([':id' => $idCliente]);
    return $stmt->fetch();
}

public function obtenerSolicitudActivaReciente(int $idCliente): ?array {
    $stmt = $this->db->prepare("
        SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio,
               u.nombre AS prof_nombre, u.apellido AS prof_apellido
        FROM solicitudes_servicio s
        INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
        INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE s.id_cliente = :id AND s.estado_servicio IN ('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO')
        ORDER BY s.fecha_solicitud DESC
        LIMIT 1
    ");
    $stmt->execute([':id' => $idCliente]);
    $res = $stmt->fetch();
    return $res ?: null;
}
}