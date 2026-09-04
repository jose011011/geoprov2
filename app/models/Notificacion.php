<?php
class Notificacion {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear(int $idUsuario, string $tipo, string $mensaje, ?string $urlDestino = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
            VALUES (:id_user, :tipo, :msg, :url)
        ");
        $stmt->execute([
            ':id_user' => $idUsuario,
            ':tipo'    => $tipo,
            ':msg'     => $mensaje,
            ':url'     => $urlDestino
        ]);
    }

    public function contarNoLeidas(int $idUsuario): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM notificaciones WHERE id_usuario = :id AND leida = 0");
        $stmt->execute([':id' => $idUsuario]);
        return (int) $stmt->fetch()['total'];
    }

    public function listarRecientes(int $idUsuario, int $limite = 10): array {
        $stmt = $this->db->prepare("
            SELECT id_notificacion, tipo, mensaje, url_destino, leida, fecha_creacion
            FROM notificaciones
            WHERE id_usuario = :id
            ORDER BY id_notificacion DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function marcarTodasLeidas(int $idUsuario): void {
        $stmt = $this->db->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = :id AND leida = 0");
        $stmt->execute([':id' => $idUsuario]);
    }
}