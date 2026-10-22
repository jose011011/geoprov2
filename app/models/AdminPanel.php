<?php
/** FixGeo - Consultas de revisión del administrador: técnicos, documentos y pagos. */
class AdminPanel
{
    private PDO $db;

    private const NOMBRES_DOC = [
        'CI_ANVERSO' => 'CI (anverso)', 'CI_REVERSO' => 'CI (reverso)', 'TITULO_TECNICO' => 'Título técnico',
        'CERTIFICADO_ANTECEDENTES' => 'Antecedentes', 'REFERENCIA_LABORAL' => 'Referencias', 'OTRO' => 'Otro documento',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function resumen(): array
    {
        $f = $this->db->query(
            "SELECT (SELECT COUNT(*) FROM profesionales WHERE estado_validacion = 'PENDIENTE') AS tecnicos,
                    (SELECT COUNT(*) FROM transacciones_suscripcion WHERE estado_pago = 'PENDIENTE') AS pagos"
        )->fetch();
        return ['tecnicos' => (int) $f['tecnicos'], 'pagos' => (int) $f['pagos']];
    }

    // ---------- Técnicos ----------
    public function tecnicos(string $filtro, string $buscar): array
    {
        $sql = "SELECT p.id_profesional, p.estado_validacion, p.estado_disponibilidad, p.tipo_prestador, p.numero_documento,
                       p.macrodistrito_base, u.nombre, u.apellido, u.correo, u.celular, u.fecha_registro, c.nombre_categoria,
                       (SELECT COUNT(*) FROM documentos_profesional d
                         WHERE d.id_profesional = p.id_profesional AND d.estado_revision = 'PENDIENTE') AS docs_pendientes
                  FROM profesionales p
                 INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
                 INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                 WHERE 1 = 1";
        $params = [];

        if (in_array($filtro, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) {
            $sql .= ' AND p.estado_validacion = :estado';
            $params[':estado'] = $filtro;
        }
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE :b1 OR u.apellido LIKE :b2 OR u.correo LIKE :b3 OR p.numero_documento LIKE :b4)';
            foreach ([':b1', ':b2', ':b3', ':b4'] as $k) {
                $params[$k] = '%' . $buscar . '%';
            }
        }
        $sql .= " ORDER BY (p.estado_validacion = 'PENDIENTE') DESC, u.fecha_registro DESC LIMIT 200";

        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function tecnico(int $idProfesional): ?array
    {
        $s = $this->db->prepare(
            'SELECT p.*, u.nombre, u.apellido, u.correo, u.celular, u.fecha_registro, c.nombre_categoria, pl.nombre_plan
               FROM profesionales p
              INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
              INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
              WHERE p.id_profesional = :id LIMIT 1'
        );
        $s->execute([':id' => $idProfesional]);
        return $s->fetch() ?: null;
    }

    public function documentos(int $idProfesional): array
    {
        $s = $this->db->prepare(
            'SELECT id_documento, tipo_documento_archivo, archivo_url, estado_revision, observacion, fecha_subida, fecha_revision
               FROM documentos_profesional WHERE id_profesional = :p ORDER BY id_documento'
        );
        $s->execute([':p' => $idProfesional]);
        return $s->fetchAll();
    }

    public function documentoPorId(int $idDocumento): ?array
    {
        $s = $this->db->prepare(
            'SELECT d.id_documento, d.id_profesional, d.tipo_documento_archivo, d.archivo_url, p.id_usuario
               FROM documentos_profesional d INNER JOIN profesionales p ON p.id_profesional = d.id_profesional
              WHERE d.id_documento = :id LIMIT 1'
        );
        $s->execute([':id' => $idDocumento]);
        return $s->fetch() ?: null;
    }

    public function revisarDocumento(int $idDocumento, string $decision, string $observacion, int $idAdmin): array
    {
        if (!in_array($decision, ['APROBADO', 'RECHAZADO'], true)) {
            throw new DomainException('Decisión no válida.');
        }
        if ($decision === 'RECHAZADO' && mb_strlen($observacion) < 5) {
            throw new DomainException('Explica brevemente por qué rechazas el documento.');
        }
        $doc = $this->documentoPorId($idDocumento);
        if (!$doc) {
            throw new DomainException('El documento no existe.');
        }
        $this->db->prepare(
            'UPDATE documentos_profesional SET estado_revision = :e, observacion = :o, revisado_por = :a, fecha_revision = NOW() WHERE id_documento = :id'
        )->execute([':e' => $decision, ':o' => $observacion !== '' ? $observacion : null, ':a' => $idAdmin, ':id' => $idDocumento]);
        return $doc;
    }

    /** Aprueba o rechaza al técnico. Para aprobar deben estar aprobados los documentos obligatorios. */
    public function decidirTecnico(int $idProfesional, string $decision, string $observacion): array
    {
        $t = $this->tecnico($idProfesional);
        if (!$t) {
            throw new DomainException('El técnico no existe.');
        }

        if ($decision === 'APROBAR') {
            $requeridos = ['CI_ANVERSO', 'CI_REVERSO'];
            if ($t['tipo_prestador'] === 'TECNICO_PROFESIONAL') {
                $requeridos[] = 'TITULO_TECNICO';
            }
            $s = $this->db->prepare("SELECT tipo_documento_archivo FROM documentos_profesional WHERE id_profesional = :p AND estado_revision = 'APROBADO'");
            $s->execute([':p' => $idProfesional]);
            $faltan = array_diff($requeridos, $s->fetchAll(PDO::FETCH_COLUMN));
            if ($faltan) {
                $nombres = array_map(fn($k) => self::NOMBRES_DOC[$k] ?? $k, $faltan);
                throw new DomainException('Antes de aprobar, revisa y aprueba: ' . implode(', ', $nombres) . '.');
            }
            $this->db->prepare("UPDATE profesionales SET estado_validacion = 'APROBADO' WHERE id_profesional = :p")->execute([':p' => $idProfesional]);
        } elseif ($decision === 'RECHAZAR') {
            if (mb_strlen($observacion) < 5) {
                throw new DomainException('Explica brevemente por qué rechazas al técnico.');
            }
            $this->db->prepare("UPDATE profesionales SET estado_validacion = 'RECHAZADO', estado_disponibilidad = 'NO_DISPONIBLE' WHERE id_profesional = :p")->execute([':p' => $idProfesional]);
        } else {
            throw new DomainException('Decisión no válida.');
        }
        return $t;
    }

    // ---------- Pagos (versiones que leen las dos columnas de foto) ----------
    public function pagos(string $filtro): array
    {
        $sql = 'SELECT t.id_transaccion, t.tipo_transaccion, t.tokens_comprados, t.monto, t.metodo_pago, t.codigo_comprobante,
                       t.estado_pago, t.fecha_pago, COALESCE(t.foto_comprobante, t.comprobante_imagen) AS foto_comprobante,
                       t.observacion, t.fecha_revision, u.nombre, u.apellido, u.correo, pl.nombre_plan
                  FROM transacciones_suscripcion t
                 INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
                 INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
                  LEFT JOIN planes_suscripcion pl ON pl.id_plan = t.id_plan
                 WHERE 1 = 1';
        $params = [];
        if (in_array($filtro, ['PENDIENTE', 'CONFIRMADO', 'FALLIDO'], true)) {
            $sql .= ' AND t.estado_pago = :e';
            $params[':e'] = $filtro;
        }
        $sql .= " ORDER BY (t.estado_pago = 'PENDIENTE') DESC, t.fecha_pago DESC LIMIT 200";

        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }
    public function comprobante(int $idTransaccion): ?string
    {
        $s = $this->db->prepare('SELECT COALESCE(foto_comprobante, comprobante_imagen) FROM transacciones_suscripcion WHERE id_transaccion = :id');
        $s->execute([':id' => $idTransaccion]);
        $f = $s->fetchColumn();
        return $f ?: null;
    }



    // ---------- Panel ----------
    public function indicadores(): array
    {
        return $this->db->query(
            "SELECT
               (SELECT COUNT(*) FROM clientes) AS clientes,
               (SELECT COUNT(*) FROM profesionales WHERE estado_validacion = 'APROBADO') AS tecnicos_aprobados,
               (SELECT COUNT(*) FROM profesionales WHERE estado_validacion = 'PENDIENTE') AS tecnicos_pendientes,
               (SELECT COUNT(*) FROM solicitudes_servicio) AS solicitudes_total,
               (SELECT COUNT(*) FROM solicitudes_servicio WHERE estado_servicio IN ('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO')) AS solicitudes_activas,
               (SELECT COUNT(*) FROM solicitudes_servicio WHERE estado_servicio = 'FINALIZADA' AND fecha_finalizacion >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS finalizadas_mes,
               (SELECT COALESCE(SUM(monto), 0) FROM transacciones_suscripcion WHERE estado_pago = 'CONFIRMADO' AND fecha_pago >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS ingresos_mes,
                (SELECT COUNT(*) FROM categorias WHERE estado = 0 AND descripcion = 'Especialidad ingresada por el prestador') AS categorias_pendientes,
               (SELECT COUNT(*) FROM transacciones_suscripcion WHERE estado_pago = 'PENDIENTE') AS pagos_pendientes"
        )->fetch();
    }

    public function solicitudesPorEstado(): array
    {
        return $this->db->query('SELECT estado_servicio, COUNT(*) AS total FROM solicitudes_servicio GROUP BY estado_servicio')->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function ultimasSolicitudes(int $limite = 8): array
    {
        return $this->db->query(
            'SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio, s.fecha_solicitud, s.macrodistrito,
                    uc.nombre AS cliente_nombre, uc.apellido AS cliente_apellido,
                    up.nombre AS prof_nombre, up.apellido AS prof_apellido, c.nombre_categoria
               FROM solicitudes_servicio s
              INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
              INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
               LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
               LEFT JOIN usuarios up ON up.id_usuario = p.id_usuario
               LEFT JOIN categorias c ON c.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
              ORDER BY s.fecha_solicitud DESC LIMIT ' . (int) $limite
        )->fetchAll();
    }

    // ---------- Clientes ----------
    public function clientes(string $buscar): array
    {
        $sql = 'SELECT u.id_usuario, u.id_rol, u.nombre, u.apellido, u.correo, u.celular, u.estado, u.fecha_registro,
                       c.zona, c.macrodistrito,
                       (SELECT COUNT(*) FROM solicitudes_servicio s WHERE s.id_cliente = c.id_cliente) AS solicitudes
                  FROM clientes c INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                 WHERE 1 = 1';
        $params = [];
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE :b1 OR u.apellido LIKE :b2 OR u.correo LIKE :b3 OR u.celular LIKE :b4)';
            foreach ([':b1', ':b2', ':b3', ':b4'] as $k) {
                $params[$k] = '%' . $buscar . '%';
            }
        }
        $s = $this->db->prepare($sql . ' ORDER BY u.fecha_registro DESC LIMIT 200');
        $s->execute($params);
        return $s->fetchAll();
    }

    public function cambiarEstadoUsuario(int $idUsuario, string $estado, int $idAdmin): array
    {
        if (!in_array($estado, ['ACTIVO', 'BLOQUEADO'], true)) {
            throw new DomainException('Estado no válido.');
        }
        if ($idUsuario === $idAdmin) {
            throw new DomainException('No puedes cambiar el estado de tu propia cuenta.');
        }
        $s = $this->db->prepare('SELECT id_rol, estado FROM usuarios WHERE id_usuario = :id');
        $s->execute([':id' => $idUsuario]);
        $u = $s->fetch();
        if (!$u) {
            throw new DomainException('El usuario no existe.');
        }
        if (in_array((int) $u['id_rol'], [1, 2], true)) {
            throw new DomainException('Las cuentas de administración no se pueden bloquear desde aquí.');
        }
        $this->db->prepare('UPDATE usuarios SET estado = :e WHERE id_usuario = :id')->execute([':e' => $estado, ':id' => $idUsuario]);
        return $u;
    }

    // ---------- Categorías ----------
    public function categorias(): array
    {
        return $this->db->query(
            "SELECT c.*, (SELECT COUNT(*) FROM profesionales p WHERE p.id_categoria = c.id_categoria) AS tecnicos,
                    (c.estado = 0 AND c.descripcion = 'Especialidad ingresada por el prestador') AS sugerida
               FROM categorias c
              ORDER BY sugerida DESC, c.estado DESC, c.nombre_categoria"
        )->fetchAll();
    }
    public function guardarCategoria(int $id, array $d): void
    {
        $nombre = trim($d['nombre']);
        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
            throw new DomainException('El nombre debe tener entre 3 y 100 caracteres.');
        }
        if (!in_array($d['tipo'], ['TECNICO', 'EMPIRICO_OFICIO', 'AMBOS'], true)) {
            throw new DomainException('Tipo de categoría no válido.');
        }

        $icono = trim($d['icono']);
        if ($icono === '') {
            $icono = IconosServicio::sugerir($nombre);        // sin ícono elegido: se sugiere según el nombre
        } elseif (!IconosServicio::existe($icono)) {
            throw new DomainException('Elige un ícono de la lista.');
        } else {
            $icono = IconosServicio::completo($icono);
        }

        $s = $this->db->prepare('SELECT 1 FROM categorias WHERE LOWER(nombre_categoria) = LOWER(:n) AND id_categoria <> :id');
        $s->execute([':n' => $nombre, ':id' => $id]);
        if ($s->fetchColumn()) {
            throw new DomainException('Ya existe una categoría con ese nombre.');
        }
        $descripcion = mb_substr(trim($d['descripcion']), 0, 500);

        if ($id > 0) {
            $this->db->prepare('UPDATE categorias SET nombre_categoria = :n, descripcion = :d, tipo_clasificacion = :t, icono_fa = :i WHERE id_categoria = :id')
                     ->execute([':n' => $nombre, ':d' => $descripcion !== '' ? $descripcion : null, ':t' => $d['tipo'], ':i' => $icono, ':id' => $id]);
        } else {
            $this->db->prepare('INSERT INTO categorias (nombre_categoria, slug, descripcion, tipo_clasificacion, icono_fa) VALUES (:n, :s, :d, :t, :i)')
                     ->execute([':n' => $nombre, ':s' => $this->slugUnico($nombre), ':d' => $descripcion !== '' ? $descripcion : null, ':t' => $d['tipo'], ':i' => $icono]);
        }
    }


    public function cambiarEstadoCategoria(int $id, int $estado): void
    {
        $this->db->prepare('UPDATE categorias SET estado = :e WHERE id_categoria = :id')->execute([':e' => $estado === 1 ? 1 : 0, ':id' => $id]);
    }

    private function slugUnico(string $nombre): string
    {
        $base = Catalogo::slug($nombre);
        $slug = $base;
        $i    = 2;
        $s    = $this->db->prepare('SELECT 1 FROM categorias WHERE slug = :s');
        while (true) {
            $s->execute([':s' => $slug]);
            if (!$s->fetchColumn()) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }

    // ---------- Planes ----------
    public function planes(): array
    {
        return $this->db->query(
            'SELECT p.*, (SELECT COUNT(*) FROM profesionales x WHERE x.id_plan = p.id_plan) AS tecnicos FROM planes_suscripcion p ORDER BY p.id_plan'
        )->fetchAll();
    }

        public function guardarPlan(int $id, array $d): void
    {
        $precio  = ($id === 1) ? 0.0 : (float) $d['precio'];  // el plan gratuito siempre cuesta 0
        $tokens  = (int) $d['tokens'];
        $retraso = (int) $d['retraso'];
        if ($precio < 0 || $precio > 9999) {
            throw new DomainException('El precio debe estar entre 0 y 9999 Bs.');
        }
        if ($tokens < 1 || $tokens > 9999) {
            throw new DomainException('Los tokens deben estar entre 1 y 9999 (999 equivale a ilimitados).');
        }
        if ($retraso < 0 || $retraso > 120) {
            throw new DomainException('La espera debe estar entre 0 y 120 minutos.');
        }
        $estado = ($id === 1) ? 1 : ((int) $d['estado'] === 1 ? 1 : 0); // el gratuito no se puede desactivar
        $this->db->prepare('UPDATE planes_suscripcion SET precio = :p, tokens_otorgados = :t, descripcion = :d, estado = :e, retraso_cascada_min = :r WHERE id_plan = :id')
                 ->execute([':p' => $precio, ':t' => $tokens, ':d' => mb_substr(trim($d['descripcion']), 0, 500), ':e' => $estado, ':r' => $retraso, ':id' => $id]);
    }

    // ---------- Reportes ----------
    public function reporteMensual(int $meses = 6): array
    {
        $desde = date('Y-m-01', strtotime('-' . ($meses - 1) . ' months'));

        $s = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha_solicitud, '%Y-%m') AS p, COUNT(*) AS creadas,
                    SUM(estado_servicio = 'FINALIZADA') AS finalizadas,
                    COALESCE(SUM(CASE WHEN estado_servicio = 'FINALIZADA' THEN precio_acordado END), 0) AS volumen
               FROM solicitudes_servicio WHERE fecha_solicitud >= :d GROUP BY p"
        );
        $s->execute([':d' => $desde]);
        $sol = [];
        foreach ($s->fetchAll() as $f) { $sol[$f['p']] = $f; }

        $s = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS p, COALESCE(SUM(monto), 0) AS ingresos
               FROM transacciones_suscripcion WHERE estado_pago = 'CONFIRMADO' AND fecha_pago >= :d GROUP BY p"
        );
        $s->execute([':d' => $desde]);
        $ing = $s->fetchAll(PDO::FETCH_KEY_PAIR);

        $salida = [];
        for ($i = 0; $i < $meses; $i++) {
            $p = date('Y-m', strtotime($desde . " +$i months"));
            $salida[] = [
                'periodo'     => $p,
                'creadas'     => (int) ($sol[$p]['creadas'] ?? 0),
                'finalizadas' => (int) ($sol[$p]['finalizadas'] ?? 0),
                'volumen'     => (float) ($sol[$p]['volumen'] ?? 0),
                'ingresos'    => (float) ($ing[$p] ?? 0),
            ];
        }
        return $salida;
    }

    public function topTecnicos(int $limite = 5): array
    {
        return $this->db->query(
            "SELECT u.nombre, u.apellido, c.nombre_categoria, COUNT(*) AS trabajos,
                    COALESCE(SUM(s.precio_acordado), 0) AS cobrado, COALESCE(vw.promedio_estrellas, 5.0) AS estrellas
               FROM solicitudes_servicio s
              INNER JOIN profesionales p ON p.id_profesional = s.id_profesional
              INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
               LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
              WHERE s.estado_servicio = 'FINALIZADA'
              GROUP BY p.id_profesional, u.nombre, u.apellido, c.nombre_categoria, vw.promedio_estrellas
              ORDER BY trabajos DESC LIMIT " . (int) $limite
        )->fetchAll();
    }

    public function solicitudesPorCategoria(): array
    {
        return $this->db->query(
            'SELECT c.nombre_categoria,
                    (SELECT COUNT(*) FROM solicitudes_servicio s LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
                      WHERE COALESCE(s.id_categoria, p.id_categoria) = c.id_categoria) AS total
               FROM categorias c ORDER BY total DESC'
        )->fetchAll();
    }

    // ---------- Auditoría ----------
    public function auditoria(string $buscar): array
    {
        $sql = 'SELECT l.id_log, l.accion, l.tabla_afectada, l.registro_id, l.ip_origen, l.fecha_evento, l.valores_nuevos,
                       u.nombre, u.apellido, u.correo
                  FROM auditoria_logs l LEFT JOIN usuarios u ON u.id_usuario = l.id_usuario
                 WHERE 1 = 1';
        $params = [];
        if ($buscar !== '') {
            $sql .= ' AND (l.accion LIKE :b1 OR l.tabla_afectada LIKE :b2 OR u.correo LIKE :b3 OR l.ip_origen LIKE :b4)';
            foreach ([':b1', ':b2', ':b3', ':b4'] as $k) {
                $params[$k] = '%' . $buscar . '%';
            }
        }
        $s = $this->db->prepare($sql . ' ORDER BY l.id_log DESC LIMIT 200');
        $s->execute($params);
        return $s->fetchAll();
    }










































    /**
     * Confirma o rechaza un pago. Todo en una transacción.
     * - Plan: lo activa 30 días (si renueva el mismo plan vigente, SUMA 30 días al vencimiento) y suma sus tokens
     *   (Premium deja 999 como mínimo).
     * - Paquete: suma los tokens comprados, sin tocar el plan.
     */
    public function decidirPago(int $idTransaccion, string $decision, string $observacion, int $idAdmin): array
    {
        if (!in_array($decision, ['CONFIRMAR', 'RECHAZAR'], true)) {
            throw new DomainException('Decisión no válida.');
        }
        if ($decision === 'RECHAZAR' && mb_strlen($observacion) < 5) {
            throw new DomainException('Explica brevemente por qué rechazas el pago.');
        }

        try {
            $this->db->beginTransaction();

            $s = $this->db->prepare(
                'SELECT t.*, p.id_usuario, p.id_plan AS plan_actual, p.fin_suscripcion
                   FROM transacciones_suscripcion t INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
                  WHERE t.id_transaccion = :id FOR UPDATE'
            );
            $s->execute([':id' => $idTransaccion]);
            $t = $s->fetch();
            if (!$t) {
                throw new DomainException('El pago no existe.');
            }
            if ($t['estado_pago'] !== 'PENDIENTE') {
                throw new DomainException('Este pago ya fue revisado.');
            }

            $nuevoFin = null;
            if ($decision === 'CONFIRMAR') {
                if ($t['tipo_transaccion'] === 'PAQUETE_TOKENS') {
                    $n = (int) $t['tokens_comprados'];
                    if ($n <= 0) {
                        throw new DomainException('El pago no indica cuántos tokens se compraron.');
                    }
                    $this->db->prepare('UPDATE profesionales SET tokens_disponibles = tokens_disponibles + :n WHERE id_profesional = :p')
                             ->execute([':n' => $n, ':p' => (int) $t['id_profesional']]);
                } else {
                    $ps = $this->db->prepare('SELECT tokens_otorgados FROM planes_suscripcion WHERE id_plan = :id');
                    $ps->execute([':id' => (int) $t['id_plan']]);
                    $otorgados = (int) $ps->fetchColumn();
                    if ($otorgados <= 0) {
                        throw new DomainException('El plan del pago no es válido.');
                    }

                    $vigente = ((int) $t['plan_actual'] === (int) $t['id_plan'])
                        && !empty($t['fin_suscripcion']) && strtotime($t['fin_suscripcion']) >= strtotime('today');
                    $base     = $vigente ? $t['fin_suscripcion'] : date('Y-m-d');
                    $nuevoFin = date('Y-m-d', strtotime($base . ' +30 days'));
                    $tokensSql = $otorgados >= 999 ? 'GREATEST(tokens_disponibles, :otorgados)' : 'tokens_disponibles + :otorgados';

                    $this->db->prepare(
                        "UPDATE profesionales SET id_plan = :plan, fin_suscripcion = :fin, tokens_disponibles = {$tokensSql} WHERE id_profesional = :p"
                    )->execute([':plan' => (int) $t['id_plan'], ':fin' => $nuevoFin, ':otorgados' => $otorgados, ':p' => (int) $t['id_profesional']]);
                }
            }

            $nuevoEstado = $decision === 'CONFIRMAR' ? 'CONFIRMADO' : 'FALLIDO';
            $this->db->prepare(
                'UPDATE transacciones_suscripcion SET estado_pago = :e, observacion = :o, revisado_por = :a, fecha_revision = NOW() WHERE id_transaccion = :id'
            )->execute([':e' => $nuevoEstado, ':o' => $observacion !== '' ? $observacion : null, ':a' => $idAdmin, ':id' => $idTransaccion]);

            $this->db->commit();
            return $t + ['nuevo_estado' => $nuevoEstado, 'nuevo_fin' => $nuevoFin];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }


    // ---------- Método auxiliar para consultas SQL ----------
    private function consulta(string $sql, array $params): PDOStatement
    {
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s;
    }



    // ---------- Informe del sistema ----------
 
 public function informe(string $desde, string $hasta, int $idCategoria = 0): array
    {
        $r = [':d' => $desde, ':h' => $hasta];
        $catS = 'COALESCE(s.id_categoria, (SELECT px.id_categoria FROM profesionales px WHERE px.id_profesional = s.id_profesional))';
        $fs   = $idCategoria > 0 ? " AND $catS = :cat" : '';          // filtro sobre solicitudes
        $fp   = $idCategoria > 0 ? ' AND p.id_categoria = :cat' : '';  // filtro sobre técnicos
        $rc   = $idCategoria > 0 ? $r + [':cat' => $idCategoria] : $r;

        $resumen = $this->consulta(
            "SELECT COUNT(*) AS creadas,
                    COALESCE(SUM(s.estado_servicio = 'FINALIZADA'), 0) AS finalizadas,
                    COALESCE(SUM(s.estado_servicio = 'CANCELADA'), 0) AS canceladas,
                    COALESCE(SUM(s.estado_servicio IN ('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO')), 0) AS abiertas
               FROM solicitudes_servicio s
              WHERE s.fecha_solicitud >= :d AND s.fecha_solicitud < DATE_ADD(:h, INTERVAL 1 DAY) $fs", $rc
        )->fetch();

        $cobrado = $this->consulta(
            "SELECT COUNT(*) AS trabajos, COALESCE(SUM(s.precio_acordado), 0) AS monto
               FROM solicitudes_servicio s
              WHERE s.estado_servicio = 'FINALIZADA' AND s.fecha_finalizacion >= :d AND s.fecha_finalizacion < DATE_ADD(:h, INTERVAL 1 DAY) $fs", $rc
        )->fetch();

        $ingresos = $this->consulta(
            "SELECT COALESCE(SUM(t.monto), 0) AS total,
                    COALESCE(SUM(CASE WHEN t.tipo_transaccion = 'MEMBRESIA_MENSUAL' THEN t.monto END), 0) AS planes,
                    COALESCE(SUM(CASE WHEN t.tipo_transaccion = 'PAQUETE_TOKENS' THEN t.monto END), 0) AS tokens,
                    COUNT(*) AS pagos
               FROM transacciones_suscripcion t INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
              WHERE t.estado_pago = 'CONFIRMADO' AND t.fecha_pago >= :d AND t.fecha_pago < DATE_ADD(:h, INTERVAL 1 DAY) $fp", $rc
        )->fetch();

        $clientesNuevos = $idCategoria > 0 ? null : (int) $this->consulta(
            'SELECT COUNT(*) FROM clientes c INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
              WHERE u.fecha_registro >= :d AND u.fecha_registro < DATE_ADD(:h, INTERVAL 1 DAY)', $r
        )->fetchColumn();

        $tecnicosNuevos = (int) $this->consulta(
            "SELECT COUNT(*) FROM profesionales p INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
              WHERE u.fecha_registro >= :d AND u.fecha_registro < DATE_ADD(:h, INTERVAL 1 DAY) $fp", $rc
        )->fetchColumn();

        $estados = $this->consulta(
            "SELECT s.estado_servicio, COUNT(*) AS total FROM solicitudes_servicio s
              WHERE s.fecha_solicitud >= :d AND s.fecha_solicitud < DATE_ADD(:h, INTERVAL 1 DAY) $fs GROUP BY s.estado_servicio", $rc
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $porCategoria = $this->consulta(
            "SELECT c.nombre_categoria, COUNT(*) AS solicitudes,
                    COALESCE(SUM(s.estado_servicio = 'FINALIZADA'), 0) AS finalizadas,
                    COALESCE(SUM(CASE WHEN s.estado_servicio = 'FINALIZADA' THEN s.precio_acordado END), 0) AS cobrado
               FROM solicitudes_servicio s
               LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
              INNER JOIN categorias c ON c.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
              WHERE s.fecha_solicitud >= :d AND s.fecha_solicitud < DATE_ADD(:h, INTERVAL 1 DAY) $fs
              GROUP BY c.id_categoria, c.nombre_categoria ORDER BY solicitudes DESC", $rc
        )->fetchAll();

        $porMacro = $this->consulta(
            "SELECT s.macrodistrito, COUNT(*) AS total FROM solicitudes_servicio s
              WHERE s.fecha_solicitud >= :d AND s.fecha_solicitud < DATE_ADD(:h, INTERVAL 1 DAY) $fs
              GROUP BY s.macrodistrito ORDER BY total DESC", $rc
        )->fetchAll();

        $top = $this->consulta(
            "SELECT u.nombre, u.apellido, c.nombre_categoria, COUNT(*) AS trabajos,
                    COALESCE(SUM(s.precio_acordado), 0) AS cobrado, COALESCE(vw.promedio_estrellas, 5.0) AS estrellas
               FROM solicitudes_servicio s
              INNER JOIN profesionales p ON p.id_profesional = s.id_profesional
              INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
               LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
              WHERE s.estado_servicio = 'FINALIZADA' AND s.fecha_finalizacion >= :d AND s.fecha_finalizacion < DATE_ADD(:h, INTERVAL 1 DAY) $fp
              GROUP BY p.id_profesional, u.nombre, u.apellido, c.nombre_categoria, vw.promedio_estrellas
              ORDER BY trabajos DESC, cobrado DESC LIMIT 10", $rc
        )->fetchAll();

        $calidad = $this->consulta(
            "SELECT COUNT(*) AS total, AVG(k.puntuacion_general) AS general, AVG(k.puntualidad) AS puntualidad, AVG(k.calidad_trabajo) AS calidad
               FROM calificaciones k INNER JOIN solicitudes_servicio s ON s.id_solicitud = k.id_solicitud
              WHERE k.fecha_calificacion >= :d AND k.fecha_calificacion < DATE_ADD(:h, INTERVAL 1 DAY) $fs", $rc
        )->fetch();

        $pagos = $this->consulta(
            "SELECT t.fecha_pago, u.nombre, u.apellido, t.tipo_transaccion, t.tokens_comprados, pl.nombre_plan, t.monto, t.estado_pago
               FROM transacciones_suscripcion t
              INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
              INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
               LEFT JOIN planes_suscripcion pl ON pl.id_plan = t.id_plan
              WHERE t.fecha_pago >= :d AND t.fecha_pago < DATE_ADD(:h, INTERVAL 1 DAY) $fp
              ORDER BY t.fecha_pago DESC LIMIT 100", $rc
        )->fetchAll();

        return [
            'resumen' => $resumen, 'cobrado' => $cobrado, 'ingresos' => $ingresos,
            'altas' => ['clientes' => $clientesNuevos, 'tecnicos' => $tecnicosNuevos],
            'estados' => $estados, 'porCategoria' => $porCategoria, 'porMacro' => $porMacro,
            'top' => $top, 'calidad' => $calidad, 'pagos' => $pagos,
        ];
    }
   


    public function exportarSolicitudes(string $desde, string $hasta, int $idCategoria = 0): array
    {
        $catS   = 'COALESCE(s.id_categoria, p.id_categoria)';
        $filtro = $idCategoria > 0 ? " AND $catS = :cat" : '';
        $params = [':d' => $desde, ':h' => $hasta] + ($idCategoria > 0 ? [':cat' => $idCategoria] : []);

        return $this->consulta(
            "SELECT s.codigo_seguimiento AS codigo, s.fecha_solicitud AS fecha,
                    CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
                    COALESCE(CONCAT(up.nombre, ' ', up.apellido), 'Sin asignar') AS tecnico,
                    COALESCE(c.nombre_categoria, '') AS categoria, s.macrodistrito, s.zona,
                    s.estado_servicio AS estado, COALESCE(s.precio_acordado, 0) AS precio_bs
               FROM solicitudes_servicio s
              INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
              INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
               LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
               LEFT JOIN usuarios up ON up.id_usuario = p.id_usuario
               LEFT JOIN categorias c ON c.id_categoria = $catS
              WHERE s.fecha_solicitud >= :d AND s.fecha_solicitud < DATE_ADD(:h, INTERVAL 1 DAY) $filtro
              ORDER BY s.fecha_solicitud DESC LIMIT 5000", $params
        )->fetchAll();
    }



    public function exportarPagos(string $desde, string $hasta, int $idCategoria = 0): array
    {
        $filtro = $idCategoria > 0 ? ' AND p.id_categoria = :cat' : '';
        $params = [':d' => $desde, ':h' => $hasta] + ($idCategoria > 0 ? [':cat' => $idCategoria] : []);

        return $this->consulta(
            "SELECT t.id_transaccion AS id, t.fecha_pago AS fecha, CONCAT(u.nombre, ' ', u.apellido) AS tecnico,
                    t.tipo_transaccion AS tipo, COALESCE(pl.nombre_plan, '') AS plan, t.tokens_comprados AS tokens,
                    t.monto AS monto_bs, t.metodo_pago AS metodo, t.codigo_comprobante AS comprobante, t.estado_pago AS estado
               FROM transacciones_suscripcion t
              INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
              INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
               LEFT JOIN planes_suscripcion pl ON pl.id_plan = t.id_plan
              WHERE t.fecha_pago >= :d AND t.fecha_pago < DATE_ADD(:h, INTERVAL 1 DAY) $filtro
              ORDER BY t.fecha_pago DESC LIMIT 5000", $params
        )->fetchAll();
    }

}