<?php
class Calificacion {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function yaCalificada(int $idSolicitud): bool {
        $stmt = $this->db->prepare("SELECT id_calificacion FROM calificaciones WHERE id_solicitud = :id LIMIT 1");
        $stmt->execute([':id' => $idSolicitud]);
        return (bool) $stmt->fetch();
    }

    public function crear(int $idSolicitud, int $idClienteUsuario, int $puntuacion, int $puntualidad, int $calidad, ?string $comentario): void {
        // Nota: el controlador ya verificó que la solicitud es FINALIZADA y pertenece al cliente
        // Solo validamos que no esté ya calificada y que los valores sean correctos
        if ($this->yaCalificada($idSolicitud)) {
            throw new Exception("Ya has calificado este servicio.");
        }
        foreach ([$puntuacion, $puntualidad, $calidad] as $val) {
            if ($val < 1 || $val > 5) {
                throw new Exception("Las calificaciones deben estar entre 1 y 5.");
            }
        }

        $stmtIns = $this->db->prepare("
            INSERT INTO calificaciones (id_solicitud, puntuacion_general, puntualidad, calidad_trabajo, comentario)
            VALUES (:id_sol, :gen, :punt, :calidad, :comentario)
        ");
        $stmtIns->execute([
            ':id_sol'     => $idSolicitud,
            ':gen'        => $puntuacion,
            ':punt'       => $puntualidad,
            ':calidad'    => $calidad,
            ':comentario' => $comentario ? trim($comentario) : null
        ]);
    }

}