<?php
class Profesional {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorUsuario(int $idUsuario): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre_categoria, c.slug AS categoria_slug,
                   pl.nombre_plan, pl.tokens_mensuales, pl.posicionamiento_destacado
            FROM profesionales p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
            WHERE p.id_usuario = :id_usuario
            LIMIT 1
        ");
        $stmt->execute([':id_usuario' => $idUsuario]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function obtenerDocumentos(int $idProfesional): array {
        $stmt = $this->db->prepare("
            SELECT id_documento, tipo_documento_archivo, archivo_url, estado_revision, observacion, fecha_subida
            FROM documentos_profesional
            WHERE id_profesional = :id_prof
            ORDER BY fecha_subida DESC
        ");
        $stmt->execute([':id_prof' => $idProfesional]);
        return $stmt->fetchAll();
    }

    public function actualizarDisponibilidad(int $idProfesional, string $estadoValidacion, string $nuevoEstado): array {
        if ($estadoValidacion !== 'APROBADO') {
            throw new Exception("No puede activar su disponibilidad hasta que su cuenta sea validada por el administrador.");
        }
        if (!in_array($nuevoEstado, ['DISPONIBLE', 'NO_DISPONIBLE'], true)) {
            throw new Exception("Estado de disponibilidad inválido.");
        }

        $stmt = $this->db->prepare("
            UPDATE profesionales SET estado_disponibilidad = :estado, ultima_conexion = NOW()
            WHERE id_profesional = :id
        ");
        $stmt->execute([':estado' => $nuevoEstado, ':id' => $idProfesional]);

        return ['estado_disponibilidad' => $nuevoEstado];
    }

    public function actualizarUbicacion(int $idProfesional, float $lat, float $lng): bool {
        $stmt = $this->db->prepare("
            UPDATE profesionales SET latitud_actual = :lat, longitud_actual = :lng
            WHERE id_profesional = :id
        ");
        return $stmt->execute([':lat' => $lat, ':lng' => $lng, ':id' => $idProfesional]);
    }
    public function obtenerEstadisticas(int $idProfesional): array {
    $stmt = $this->db->prepare("
        SELECT
            COUNT(CASE WHEN s.estado_servicio = 'FINALIZADA' THEN 1 END) AS servicios_finalizados,
            COUNT(CASE WHEN s.estado_servicio = 'PENDIENTE' THEN 1 END) AS solicitudes_pendientes,
            COUNT(CASE WHEN s.estado_servicio IN ('ACEPTADA','EN_CAMINO','EN_PROCESO') THEN 1 END) AS servicios_activos,
            COALESCE(ROUND(AVG(cal.puntuacion_general), 1), 0) AS promedio_estrellas,
            COUNT(cal.id_calificacion) AS total_calificaciones
        FROM solicitudes_servicio s
        LEFT JOIN calificaciones cal ON s.id_solicitud = cal.id_solicitud
        WHERE s.id_profesional = :id
    ");
    $stmt->execute([':id' => $idProfesional]);
    return $stmt->fetch();
}

public function membresiaVencida(array $perfil): bool {
    if ((int) $perfil['id_plan'] === 1) return false; // plan gratuito nunca vence
    if (empty($perfil['fin_suscripcion'])) return true;
    return strtotime($perfil['fin_suscripcion']) < strtotime(date('Y-m-d'));
}

public function diasParaVencer(array $perfil): ?int {
    if ((int) $perfil['id_plan'] === 1 || empty($perfil['fin_suscripcion'])) return null;
    $dias = (strtotime($perfil['fin_suscripcion']) - strtotime(date('Y-m-d'))) / 86400;
    return (int) $dias;
}
}