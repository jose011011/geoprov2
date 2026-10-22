<?php
/** FixGeo - Consultas del panel del cliente (perfil, catálogo, favoritos, historial). */
class ClientePanel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ---------- Categorías ----------
    public function categoriasActivas(): array
    {
        return $this->db->query(
            'SELECT id_categoria, nombre_categoria, descripcion, icono_fa FROM categorias WHERE estado = 1 ORDER BY nombre_categoria'
        )->fetchAll();
    }

    public function categoriaPorId(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM categorias WHERE id_categoria = :id AND estado = 1 LIMIT 1');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public function categoriaDeProfesional(int $idProfesional): ?array
    {
        $s = $this->db->prepare(
            'SELECT c.* FROM categorias c INNER JOIN profesionales p ON p.id_categoria = c.id_categoria
              WHERE p.id_profesional = :id AND c.estado = 1 LIMIT 1'
        );
        $s->execute([':id' => $idProfesional]);
        return $s->fetch() ?: null;
    }

    // ---------- Perfil del cliente ----------
    public function porUsuario(int $idUsuario): ?array
    {
        $s = $this->db->prepare(
            'SELECT c.*, u.nombre, u.apellido, u.correo, u.celular
               FROM clientes c INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
              WHERE c.id_usuario = :u LIMIT 1'
        );
        $s->execute([':u' => $idUsuario]);
        return $s->fetch() ?: null;
    }

    /** Un profesional (rol 3) que quiere contratar recibe su perfil de cliente con los datos de su zona de trabajo. */
    public function asegurarPerfil(int $idUsuario, int $rol): ?array
    {
        $cliente = $this->porUsuario($idUsuario);
        if ($cliente || $rol !== 3) {
            return $cliente;
        }

        $s = $this->db->prepare('SELECT macrodistrito_base, zona_especifica, latitud_actual, longitud_actual FROM profesionales WHERE id_usuario = :u LIMIT 1');
        $s->execute([':u' => $idUsuario]);
        $p = $s->fetch() ?: [];

        $this->db->prepare(
            'INSERT INTO clientes (id_usuario, direccion_referencia, zona, macrodistrito, latitud_predeterminada, longitud_predeterminada)
             VALUES (:u, :dir, :zona, :macro, :lat, :lng)'
        )->execute([
            ':u'     => $idUsuario,
            ':dir'   => '',
            ':zona'  => $p['zona_especifica'] ?? 'La Paz',
            ':macro' => Catalogo::macroValido($p['macrodistrito_base'] ?? null),
            ':lat'   => $p['latitud_actual'] ?? -16.50000000,
            ':lng'   => $p['longitud_actual'] ?? -68.15000000,
        ]);
        return $this->porUsuario($idUsuario);
    }

    public function guardarPerfil(int $idUsuario, array $d): void
    {
        $this->db->prepare(
            'UPDATE clientes SET direccion_referencia = :dir, zona = :zona, macrodistrito = :macro,
                    latitud_predeterminada = :lat, longitud_predeterminada = :lng
              WHERE id_usuario = :u'
        )->execute([
            ':dir'   => $d['direccion_referencia'],
            ':zona'  => $d['zona'],
            ':macro' => $d['macrodistrito'],
            ':lat'   => $d['latitud'],
            ':lng'   => $d['longitud'],
            ':u'     => $idUsuario,
        ]);
    }

    // ---------- Panel ----------
    public function estadisticas(int $idCliente): array
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(estado_servicio IN ('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO')) AS activas,
                    SUM(estado_servicio = 'FINALIZADA') AS finalizadas
               FROM solicitudes_servicio WHERE id_cliente = :c"
        );
        $s->execute([':c' => $idCliente]);
        $f = $s->fetch();

        $fav = $this->db->prepare('SELECT COUNT(*) FROM clientes_favoritos WHERE id_cliente = :c');
        $fav->execute([':c' => $idCliente]);

        return [
            'total'       => (int) ($f['total'] ?? 0),
            'activas'     => (int) ($f['activas'] ?? 0),
            'finalizadas' => (int) ($f['finalizadas'] ?? 0),
            'favoritos'   => (int) $fav->fetchColumn(),
        ];
    }

    public function solicitudActiva(int $idCliente): ?array
    {
        $s = $this->db->prepare(
            "SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio, s.fecha_solicitud, s.id_profesional,
                    u.nombre AS prof_nombre, u.apellido AS prof_apellido, c.nombre_categoria
               FROM solicitudes_servicio s
               LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
               LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
               LEFT JOIN categorias c ON c.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
              WHERE s.id_cliente = :c AND s.estado_servicio IN ('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO')
              ORDER BY s.fecha_solicitud DESC LIMIT 1"
        );
        $s->execute([':c' => $idCliente]);
        return $s->fetch() ?: null;
    }

    // ---------- Búsqueda de técnicos ----------
    /** Técnicos disponibles de una categoría (excluye al propio usuario si también es técnico). */
    public function profesionalesDeCategoria(int $idCategoria, int $idUsuario): array
    {
        $s = $this->db->prepare(
            "SELECT p.id_profesional, u.nombre, u.apellido, pl.nombre_plan, pl.posicionamiento_destacado,
                    p.descripcion_servicio, p.tarifa_base, p.experiencia_anios, p.macrodistrito_base, p.zona_especifica,
                    COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas,
                    (SELECT COUNT(*) FROM clientes_favoritos cf INNER JOIN clientes c ON c.id_cliente = cf.id_cliente
                      WHERE cf.id_profesional = p.id_profesional AND c.id_usuario = :u_fav) AS es_favorito
               FROM profesionales p
               INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
               INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
               LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
              WHERE p.id_categoria = :cat AND p.estado_validacion = 'APROBADO' AND p.estado_disponibilidad = 'DISPONIBLE'
                AND p.tokens_disponibles > 0 AND p.id_usuario <> :u_propio
              ORDER BY es_favorito DESC, pl.posicionamiento_destacado DESC, pl.precio DESC, promedio_estrellas DESC
              LIMIT 30"
        );
        $s->execute([':u_fav' => $idUsuario, ':cat' => $idCategoria, ':u_propio' => $idUsuario]);
        return $s->fetchAll();
    }

    /** Catálogo completo (con filtro opcional de categoría). Premium primero, luego mejor calificados. */
    public function catalogo(int $idCategoria, int $idUsuario): array
    {
        $sql = "SELECT p.id_profesional, u.nombre, u.apellido, c.nombre_categoria, c.icono_fa,
                       pl.nombre_plan, pl.posicionamiento_destacado, p.tarifa_base, p.experiencia_anios,
                       p.macrodistrito_base, p.zona_especifica,
                       COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas,
                       (SELECT COUNT(*) FROM clientes_favoritos cf INNER JOIN clientes cl ON cl.id_cliente = cf.id_cliente
                         WHERE cf.id_profesional = p.id_profesional AND cl.id_usuario = :u_fav) AS es_favorito
                  FROM profesionales p
                  INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
                  INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                  INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
                  LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
                 WHERE p.estado_validacion = 'APROBADO' AND p.estado_disponibilidad = 'DISPONIBLE'
                                      AND p.tokens_disponibles > 0 AND p.id_usuario <> :u_propio AND c.estado = 1";
        $params = [':u_fav' => $idUsuario, ':u_propio' => $idUsuario];

        if ($idCategoria > 0) {
            $sql .= ' AND p.id_categoria = :cat';
            $params[':cat'] = $idCategoria;
        }
        $sql .= ' ORDER BY pl.posicionamiento_destacado DESC, pl.id_plan DESC, promedio_estrellas DESC LIMIT 60';

        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    /** ¿Hay al menos un técnico que pueda ver una solicitud abierta de esa categoría y zona? */
    public function hayProfesionales(int $idCategoria, string $macrodistrito, int $idUsuario): bool
    {
        $s = $this->db->prepare(
            "SELECT 1 FROM profesionales
              WHERE id_categoria = :cat AND macrodistrito_base = :macro AND estado_validacion = 'APROBADO'
                AND estado_disponibilidad = 'DISPONIBLE' AND tokens_disponibles > 0 AND id_usuario <> :u LIMIT 1"
        );
        $s->execute([':cat' => $idCategoria, ':macro' => $macrodistrito, ':u' => $idUsuario]);
        return (bool) $s->fetchColumn();
    }

    // ---------- Favoritos e historial ----------
    public function favoritos(int $idCliente): array
    {
        $s = $this->db->prepare(
            'SELECT f.id_favorito, p.id_profesional, u.nombre, u.apellido, c.nombre_categoria, c.icono_fa, pl.nombre_plan,
                    COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas
               FROM clientes_favoritos f
               INNER JOIN profesionales p ON p.id_profesional = f.id_profesional
               INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
               INNER JOIN categorias c ON c.id_categoria = p.id_categoria
               INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
               LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
              WHERE f.id_cliente = :c ORDER BY f.fecha_agregado DESC'
        );
        $s->execute([':c' => $idCliente]);
        return $s->fetchAll();
    }

    public function agregarFavorito(int $idUsuario, int $idProfesional): void
    {
        $this->db->prepare(
            'INSERT INTO clientes_favoritos (id_cliente, id_profesional)
             SELECT c.id_cliente, p.id_profesional FROM clientes c
              INNER JOIN profesionales p ON p.id_profesional = :p
              WHERE c.id_usuario = :u AND p.id_usuario <> c.id_usuario
                AND NOT EXISTS (SELECT 1 FROM clientes_favoritos f WHERE f.id_cliente = c.id_cliente AND f.id_profesional = p.id_profesional)'
        )->execute([':p' => $idProfesional, ':u' => $idUsuario]);
    }

    public function quitarFavorito(int $idUsuario, int $idFavorito): void
    {
        $this->db->prepare(
            'DELETE f FROM clientes_favoritos f INNER JOIN clientes c ON c.id_cliente = f.id_cliente
              WHERE f.id_favorito = :f AND c.id_usuario = :u'
        )->execute([':f' => $idFavorito, ':u' => $idUsuario]);
    }

    public function historial(int $idCliente): array
    {
        $s = $this->db->prepare(
            'SELECT s.*, c.nombre_categoria, u.nombre AS prof_nombre, u.apellido AS prof_apellido,
                    COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas
               FROM solicitudes_servicio s
               LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
               LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
               LEFT JOIN categorias c ON c.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
               LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
              WHERE s.id_cliente = :c ORDER BY s.fecha_solicitud DESC'
        );
        $s->execute([':c' => $idCliente]);
        return $s->fetchAll();
    }

    /** Ubicación del técnico, solo para el cliente dueño y mientras va en camino. */
    public function ubicacionTecnico(int $idSolicitud, int $idUsuario): ?array
    {
        $s = $this->db->prepare(
            "SELECT p.latitud_actual, p.longitud_actual
               FROM solicitudes_servicio s
               INNER JOIN clientes c ON c.id_cliente = s.id_cliente
               INNER JOIN profesionales p ON p.id_profesional = s.id_profesional
              WHERE s.id_solicitud = :s AND c.id_usuario = :u AND s.estado_servicio = 'EN_CAMINO' LIMIT 1"
        );
        $s->execute([':s' => $idSolicitud, ':u' => $idUsuario]);
        return $s->fetch() ?: null;
    }

        /** Categorías disponibles según el tipo de prestador (técnico con título u oficio). */
    public function categoriasParaPrestador(string $tipoPrestador): array
    {
        $filtro = ($tipoPrestador === 'TECNICO_PROFESIONAL') ? 'TECNICO' : 'EMPIRICO_OFICIO';
        $s = $this->db->prepare(
            "SELECT id_categoria, nombre_categoria, tipo_clasificacion FROM categorias
              WHERE estado = 1 AND (tipo_clasificacion = :tipo OR tipo_clasificacion = 'AMBOS')
              ORDER BY nombre_categoria"
        );
        $s->execute([':tipo' => $filtro]);
        return $s->fetchAll();
    }


        /** Último servicio finalizado (últimos 30 días) que el cliente todavía no calificó. */
    public function servicioPorCalificar(int $idCliente): ?array
    {
        $s = $this->db->prepare(
            "SELECT s.id_solicitud, s.codigo_seguimiento
               FROM solicitudes_servicio s
               LEFT JOIN calificaciones k ON k.id_solicitud = s.id_solicitud
              WHERE s.id_cliente = :c AND s.estado_servicio = 'FINALIZADA' AND s.id_profesional IS NOT NULL
                AND k.id_solicitud IS NULL AND s.fecha_finalizacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              ORDER BY s.fecha_finalizacion DESC LIMIT 1"
        );
        $s->execute([':c' => $idCliente]);
        return $s->fetch() ?: null;
    }
}