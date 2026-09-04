<?php
class Tracking {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Registra un punto GPS, validando que la solicitud pertenezca al profesional y esté EN_CAMINO */
    public function registrarPosicion(int $idSolicitud, int $idUsuarioProfesional, float $lat, float $lng, float $velocidad = 0): void {
        $stmt = $this->db->prepare("
            SELECT s.id_solicitud, s.estado_servicio, p.id_profesional
            FROM solicitudes_servicio s
            INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
            WHERE s.id_solicitud = :id_sol AND p.id_usuario = :id_user
            LIMIT 1
        ");
        $stmt->execute([':id_sol' => $idSolicitud, ':id_user' => $idUsuarioProfesional]);
        $sol = $stmt->fetch();

        if (!$sol) {
            throw new Exception("No tiene permiso sobre esta solicitud.");
        }
        if ($sol['estado_servicio'] !== 'EN_CAMINO') {
            throw new Exception("El seguimiento solo está activo mientras el servicio está EN_CAMINO.");
        }

        $stmtIns = $this->db->prepare("
            INSERT INTO tracking_solicitud_gps (id_solicitud, id_profesional, latitud, longitud, velocidad_kmh)
            VALUES (:id_sol, :id_prof, :lat, :lng, :vel)
        ");
        $stmtIns->execute([
            ':id_sol'  => $idSolicitud,
            ':id_prof' => $sol['id_profesional'],
            ':lat'     => $lat,
            ':lng'     => $lng,
            ':vel'     => $velocidad
        ]);

        // También actualizamos la posición "base" del profesional
        $stmtProf = $this->db->prepare("UPDATE profesionales SET latitud_actual = :lat, longitud_actual = :lng WHERE id_profesional = :id");
        $stmtProf->execute([':lat' => $lat, ':lng' => $lng, ':id' => $sol['id_profesional']]);
    }

    public function obtenerUltimaPosicion(int $idSolicitud): ?array {
        $stmt = $this->db->prepare("
            SELECT latitud, longitud, velocidad_kmh, timestamp_registro
            FROM tracking_solicitud_gps
            WHERE id_solicitud = :id
            ORDER BY id_tracking DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $idSolicitud]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /** Verifica que el usuario (cliente o profesional) pertenece a esta solicitud */
    public function usuarioPerteneceASolicitud(int $idSolicitud, int $idUsuario): bool {
        $stmt = $this->db->prepare("
            SELECT s.id_solicitud
            FROM solicitudes_servicio s
            INNER JOIN clientes cl ON s.id_cliente = cl.id_cliente
            INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
            WHERE s.id_solicitud = :id_sol
              AND (cl.id_usuario = :id_user OR p.id_usuario = :id_user2)
            LIMIT 1
        ");
        $stmt->execute([':id_sol' => $idSolicitud, ':id_user' => $idUsuario, ':id_user2' => $idUsuario]);
        return (bool) $stmt->fetch();
    }
}