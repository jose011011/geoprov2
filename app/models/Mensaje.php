<?php
class Mensaje {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function enviar(int $idSolicitud, int $idRemitente, string $texto, ?string $rutaArchivo = null, string $tipo = 'TEXTO'): int {
        $stmt = $this->db->prepare("
            INSERT INTO mensajes (id_solicitud, id_remitente, tipo_mensaje, mensaje, archivo_adjunto)
            VALUES (:id_sol, :id_rem, :tipo, :msg, :archivo)
        ");
        $stmt->execute([
            ':id_sol'  => $idSolicitud,
            ':id_rem'  => $idRemitente,
            ':tipo'    => $tipo,
            ':msg'     => trim($texto) ?: null,
            ':archivo' => $rutaArchivo
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Trae mensajes nuevos desde un ID (para polling incremental) */
    public function obtenerDesde(int $idSolicitud, int $ultimoIdVisto = 0): array {
        $stmt = $this->db->prepare("
            SELECT m.id_mensaje, m.id_remitente, m.tipo_mensaje, m.mensaje, m.archivo_adjunto, m.fecha_envio,
                   u.nombre, u.apellido
            FROM mensajes m
            INNER JOIN usuarios u ON m.id_remitente = u.id_usuario
            WHERE m.id_solicitud = :id_sol AND m.id_mensaje > :ultimo_id
            ORDER BY m.id_mensaje ASC
        ");
        $stmt->execute([':id_sol' => $idSolicitud, ':ultimo_id' => $ultimoIdVisto]);
        return $stmt->fetchAll();
    }

    public function obtenerTodos(int $idSolicitud): array {
        return $this->obtenerDesde($idSolicitud, 0);
    }

    /** Verifica que el usuario pertenece a esta solicitud (cliente o profesional) */
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