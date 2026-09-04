<?php
class Admin {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function estadisticas(): array {
        $stmt = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM profesionales WHERE estado_validacion = 'PENDIENTE') AS pendientes,
                (SELECT COUNT(*) FROM profesionales WHERE estado_validacion = 'APROBADO') AS aprobados,
                (SELECT COUNT(*) FROM profesionales WHERE estado_validacion = 'RECHAZADO') AS rechazados,
                (SELECT COUNT(*) FROM clientes) AS total_clientes,
                (SELECT COUNT(*) FROM solicitudes_servicio) AS total_solicitudes
        ");
        return $stmt->fetch();
    }

    public function listarProfesionales(string $filtroEstado = 'PENDIENTE'): array {
        $sql = "
            SELECT p.id_profesional, p.tipo_prestador, p.estado_validacion, p.fecha_registro,
                   p.macrodistrito_base, u.nombre, u.apellido, u.correo, u.celular,
                   c.nombre_categoria
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        ";
        $params = [];
        if (in_array($filtroEstado, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) {
            $sql .= " WHERE p.estado_validacion = :estado";
            $params[':estado'] = $filtroEstado;
        }
        $sql .= " ORDER BY p.fecha_registro DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerProfesionalDetalle(int $idProfesional): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, u.nombre, u.apellido, u.correo, u.celular, c.nombre_categoria
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_profesional = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $idProfesional]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function obtenerDocumentos(int $idProfesional): array {
        $stmt = $this->db->prepare("
            SELECT id_documento, tipo_documento_archivo, archivo_url, estado_revision, observacion, fecha_subida
            FROM documentos_profesional
            WHERE id_profesional = :id
            ORDER BY fecha_subida ASC
        ");
        $stmt->execute([':id' => $idProfesional]);
        return $stmt->fetchAll();
    }

    public function revisarDocumento(int $idDocumento, string $decision, ?int $revisadoPor, ?string $observacion): void {
        if (!in_array($decision, ['APROBADO', 'RECHAZADO'], true)) {
            throw new Exception("Decisión de revisión inválida.");
        }
        $stmt = $this->db->prepare("
            UPDATE documentos_profesional
            SET estado_revision = :decision, observacion = :obs, revisado_por = :rev, fecha_revision = NOW()
            WHERE id_documento = :id
        ");
        $stmt->execute([
            ':decision' => $decision,
            ':obs'      => $observacion,
            ':rev'      => $revisadoPor,
            ':id'       => $idDocumento
        ]);
    }

    /** Antes de aprobar al profesional, exige que no queden documentos PENDIENTES ni RECHAZADOS */
    public function aprobarProfesional(int $idProfesional): void {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS pendientes
            FROM documentos_profesional
            WHERE id_profesional = :id AND estado_revision != 'APROBADO'
        ");
        $stmt->execute([':id' => $idProfesional]);
        $check = $stmt->fetch();

        if ((int) $check['pendientes'] > 0) {
            throw new Exception("No puede aprobar al profesional: aún tiene documentos sin aprobar.");
        }

        $stmtUp = $this->db->prepare("
            UPDATE profesionales SET estado_validacion = 'APROBADO' WHERE id_profesional = :id
        ");
        $stmtUp->execute([':id' => $idProfesional]);
    }

    public function rechazarProfesional(int $idProfesional): void {
        $stmt = $this->db->prepare("
            UPDATE profesionales SET estado_validacion = 'RECHAZADO' WHERE id_profesional = :id
        ");
        $stmt->execute([':id' => $idProfesional]);
    }
    public function listarPagosPendientes(): array {
    $membresia = new Membresia();
    return $membresia->listarPendientes();
}

public function auditoriaReciente(int $limite = 50): array {
    $stmt = $this->db->prepare("SELECT * FROM vw_auditoria_detallada LIMIT :lim");
    $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}


public function listarCategorias(): array {
    $stmt = $this->db->query("
        SELECT c.*, COUNT(p.id_profesional) AS total_profesionales
        FROM categorias c
        LEFT JOIN profesionales p ON c.id_categoria = p.id_categoria
        GROUP BY c.id_categoria
        ORDER BY c.nombre_categoria ASC
    ");
    return $stmt->fetchAll();
}

public function crearCategoria(string $nombre, string $tipo, string $icono, ?string $descripcion): void {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));

    $stmtCheck = $this->db->prepare("SELECT id_categoria FROM categorias WHERE slug = :slug LIMIT 1");
    $stmtCheck->execute([':slug' => $slug]);
    if ($stmtCheck->fetch()) {
        throw new Exception("Ya existe una categoría con ese nombre.");
    }

    $stmt = $this->db->prepare("
        INSERT INTO categorias (nombre_categoria, slug, tipo_clasificacion, icono_fa, descripcion, estado)
        VALUES (:nombre, :slug, :tipo, :icono, :desc, 1)
    ");
    $stmt->execute([
        ':nombre' => trim($nombre),
        ':slug'   => $slug,
        ':tipo'   => $tipo,
        ':icono'  => $icono ?: 'fa-solid fa-wrench',
        ':desc'   => $descripcion ? trim($descripcion) : null
    ]);
}

public function toggleCategoria(int $idCategoria): void {
    $stmt = $this->db->prepare("UPDATE categorias SET estado = NOT estado WHERE id_categoria = :id");
    $stmt->execute([':id' => $idCategoria]);
}
}