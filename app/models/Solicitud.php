<?php
class Solicitud
{
    private PDO $db;

    private const MACRODISTRITOS = ['ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES', 'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO'];
    private const ACTIVOS        = ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'];
    private const ESTADOS        = ['PENDIENTE', 'ACEPTADA', 'EN_CAMINO', 'EN_PROCESO', 'FINALIZADA', 'CANCELADA'];

    /** Qué cambios de estado están permitidos (ACEPTADA→EN_PROCESO se mantiene por compatibilidad con tu botón "Llegué"). */
    private const TRANSICIONES = [
        'PENDIENTE'  => ['ACEPTADA', 'CANCELADA'],
        'ACEPTADA'   => ['EN_CAMINO', 'EN_PROCESO', 'CANCELADA'],
        'EN_CAMINO'  => ['EN_PROCESO', 'CANCELADA'],
        'EN_PROCESO' => ['FINALIZADA', 'CANCELADA'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function macrodistritoValido(?string $valor): string
    {
        return in_array($valor, self::MACRODISTRITOS, true) ? $valor : 'CENTRO';
    }

    /** Retraso de la cascada según plan: 3 = Premium (0 min), 2 = Básico (3 min), resto = Gratuito (7 min). */
    private static function retrasoPorPlan(int $idPlan): int
    {
        return match ($idPlan) { 3 => 0, 2 => 3, default => 7 };
    }

    /** Convierte los errores de los triggers (SQLSTATE 45000) en mensajes amigables. */
    private function traducirError(PDOException $e): Throwable
    {
        if (($e->errorInfo[0] ?? '') === '45000') {
            $msg = preg_replace('/^Operación denegada:\s*/u', '', (string) ($e->errorInfo[2] ?? 'Operación no permitida.'));
            return new DomainException($msg);
        }
        return $e;
    }

    /* ===================== CREAR ===================== */
    public function crear(int $idCliente, ?int $idProfesional, array $data, ?int $idCategoria = null): int
    {
        $descripcion = trim((string) ($data['descripcion_problema'] ?? ''));
        $direccion   = trim((string) ($data['direccion_servicio'] ?? ''));
        $zona        = trim((string) ($data['zona'] ?? ''));
        $lat         = (float) ($data['latitud_destino'] ?? 0);
        $lng         = (float) ($data['longitud_destino'] ?? 0);

        if (mb_strlen($descripcion) < 10 || mb_strlen($descripcion) > 1000) {
            throw new DomainException('Describe el problema con entre 10 y 1000 caracteres.');
        }
        if (mb_strlen($direccion) < 5 || mb_strlen($direccion) > 255) {
            throw new DomainException('Ingresa una dirección válida.');
        }
        if ($zona === '' || mb_strlen($zona) > 100) {
            throw new DomainException('Indica la zona del servicio.');
        }
        if (abs($lat) > 90 || abs($lng) > 180 || ($lat == 0 && $lng == 0)) {
            throw new DomainException('La ubicación del servicio no es válida.');
        }

        if ($idProfesional !== null) {
            $stmt = $this->db->prepare('
                SELECT p.id_categoria, (p.id_usuario = c.id_usuario) AS es_propio
                  FROM profesionales p
                 INNER JOIN clientes c ON c.id_cliente = :cli
                 WHERE p.id_profesional = :prof LIMIT 1');
            $stmt->execute([':cli' => $idCliente, ':prof' => $idProfesional]);
            $fila = $stmt->fetch();
            if (!$fila) {
                throw new DomainException('El profesional seleccionado no existe.');
            }
            if ((int) $fila['es_propio'] === 1) {
                throw new DomainException('No puedes solicitarte un servicio a ti mismo.');
            }
            $idCategoria ??= (int) $fila['id_categoria'];
        } else {
            $stmt = $this->db->prepare('SELECT 1 FROM categorias WHERE id_categoria = :id AND estado = 1');
            $stmt->execute([':id' => (int) $idCategoria]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException('Selecciona una categoría válida.');
            }
        }

        try {
            $stmt = $this->db->prepare('
                INSERT INTO solicitudes_servicio (
                    codigo_seguimiento, id_cliente, id_categoria, id_profesional, descripcion_problema,
                    direccion_servicio, macrodistrito, zona, latitud_destino, longitud_destino
                ) VALUES (
                    :codigo, :cliente, :categoria, :profesional, :descripcion,
                    :direccion, :macro, :zona, :lat, :lng
                )');
            $stmt->execute([
                ':codigo'      => 'FIX-' . strtoupper(bin2hex(random_bytes(4))),
                ':cliente'     => $idCliente,
                ':categoria'   => $idCategoria,
                ':profesional' => $idProfesional,
                ':descripcion' => $descripcion,
                ':direccion'   => $direccion,
                ':macro'       => self::macrodistritoValido($data['macrodistrito'] ?? null),
                ':zona'        => $zona,
                ':lat'         => $lat,
                ':lng'         => $lng,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $this->traducirError($e);
        }
    }

    /* ===================== CONSULTAS ===================== */
    public function obtenerPorId(int $idSolicitud): ?array
    {
        $stmt = $this->db->prepare('
            SELECT s.*, cat.nombre_categoria,
                   uc.nombre AS cliente_nombre, uc.apellido AS cliente_apellido, uc.celular AS cliente_celular,
                   cl.id_usuario AS cliente_id_usuario,
                   up.nombre AS prof_nombre, up.apellido AS prof_apellido, up.celular AS prof_celular,
                   p.id_usuario AS prof_id_usuario
              FROM solicitudes_servicio s
             INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
             INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
              LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
              LEFT JOIN usuarios up ON up.id_usuario = p.id_usuario
              LEFT JOIN categorias cat ON cat.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
             WHERE s.id_solicitud = :id
             LIMIT 1');
        $stmt->execute([':id' => $idSolicitud]);
        return $stmt->fetch() ?: null;
    }

    /** Solo devuelve la solicitud si el usuario es el cliente o el profesional de esa solicitud. */
    public function obtenerParaUsuario(int $idSolicitud, int $idUsuario): ?array
    {
        $sol = $this->obtenerPorId($idSolicitud);
        if (!$sol) {
            return null;
        }
        $esCliente     = (int) $sol['cliente_id_usuario'] === $idUsuario;
        $esProfesional = $sol['prof_id_usuario'] !== null && (int) $sol['prof_id_usuario'] === $idUsuario;
        return ($esCliente || $esProfesional) ? $sol : null;
    }

    public function listarPorCliente(int $idCliente): array
    {
        $stmt = $this->db->prepare('
            SELECT s.id_solicitud, s.codigo_seguimiento, s.descripcion_problema, s.estado_servicio, s.fecha_solicitud,
                   u.nombre AS prof_nombre, u.apellido AS prof_apellido, c.nombre_categoria
              FROM solicitudes_servicio s
              LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
              LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
              LEFT JOIN categorias c ON c.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
             WHERE s.id_cliente = :id
             ORDER BY s.fecha_solicitud DESC');
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll();
    }

    public function listarPorProfesional($idProfesional, $estado = null): array
    {
        $sql = '
            SELECT s.*, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
              FROM solicitudes_servicio s
             INNER JOIN clientes c ON c.id_cliente = s.id_cliente
             INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
             WHERE s.id_profesional = :prof';
        $params = [':prof' => (int) $idProfesional];

        if ($estado && in_array($estado, self::ESTADOS, true)) {
            $sql .= ' AND s.estado_servicio = :estado';
            $params[':estado'] = $estado;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY s.fecha_solicitud DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function tieneTrabajoActivo(int $idProfesional): bool
    {
        $in = "'" . implode("','", self::ACTIVOS) . "'";
        $stmt = $this->db->prepare("SELECT 1 FROM solicitudes_servicio WHERE id_profesional = :id AND estado_servicio IN ($in) LIMIT 1");
        $stmt->execute([':id' => $idProfesional]);
        return (bool) $stmt->fetchColumn();
    }

    /* ===================== CASCADA ===================== */

        /** Radio (km) alrededor del servicio dentro del cual un técnico de la categoría también recibe la solicitud. */
    private const RADIO_CASCADA_KM = 10;

    /** Espera del plan (tabla planes_suscripcion) y ubicación del técnico. Si el plan no existe, se asume la espera más larga. */
    private function datosCascada(int $idPlan, ?int $idProfesional): array
    {
        $s = $this->db->prepare('SELECT retraso_cascada_min FROM planes_suscripcion WHERE id_plan = :id');
        $s->execute([':id' => $idPlan]);
        $retraso = $s->fetchColumn();

        $lat = $lng = null;
        if ($idProfesional) {
            $s = $this->db->prepare('SELECT latitud_actual, longitud_actual FROM profesionales WHERE id_profesional = :id');
            $s->execute([':id' => $idProfesional]);
            $f = $s->fetch();
            if ($f && $f['latitud_actual'] !== null && $f['longitud_actual'] !== null) {
                $lat = (float) $f['latitud_actual'];
                $lng = (float) $f['longitud_actual'];
            }
        }
        return ['retraso' => $retraso === false ? 10 : (int) $retraso, 'lat' => $lat, 'lng' => $lng];
    }
        /** Zona: mismo macrodistrito O a menos de 10 km. Sin GPS del técnico, solo macrodistrito. */
    private function sqlZona(bool $conGps): string
    {
        if (!$conGps) {
            return 's.macrodistrito = :macro';
        }
        return 's.macrodistrito = :macro OR (6371 * ACOS(LEAST(1,
                    COS(RADIANS(s.latitud_destino)) * COS(RADIANS(:lat1)) * COS(RADIANS(:lng1) - RADIANS(s.longitud_destino))
                    + SIN(RADIANS(s.latitud_destino)) * SIN(RADIANS(:lat2))))) <= ' . self::RADIO_CASCADA_KM;
    }

    public function obtenerOportunidadesCascada($idPlanProfesional, $macrodistritoProf, $idCategoriaProf, $idProfesional = null): array
    {
        if ($idProfesional && $this->tieneTrabajoActivo((int) $idProfesional)) {
            return []; // con un trabajo activo no ve más solicitudes
        }

        $d     = $this->datosCascada((int) $idPlanProfesional, $idProfesional ? (int) $idProfesional : null);
        $conGps = $d['lat'] !== null;

        $sql = "SELECT s.*, c.zona AS zona_cliente, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido
                  FROM solicitudes_servicio s
                 INNER JOIN clientes c ON c.id_cliente = s.id_cliente
                 INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                 WHERE s.estado_servicio = 'PENDIENTE' AND s.id_profesional IS NULL
                   AND s.id_categoria = :cat
                   AND s.fecha_solicitud <= DATE_SUB(NOW(), INTERVAL :retraso MINUTE)
                   AND (" . $this->sqlZona($conGps) . ')';
        if ($idProfesional) {
            $sql .= ' AND c.id_usuario <> (SELECT id_usuario FROM profesionales WHERE id_profesional = :self)';
        }
        $sql .= ' ORDER BY s.fecha_solicitud DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat', (int) $idCategoriaProf, PDO::PARAM_INT);
        $stmt->bindValue(':retraso', $d['retraso'], PDO::PARAM_INT);
        $stmt->bindValue(':macro', (string) $macrodistritoProf, PDO::PARAM_STR);
        if ($conGps) {
            $stmt->bindValue(':lat1', $d['lat']);
            $stmt->bindValue(':lng1', $d['lng']);
            $stmt->bindValue(':lat2', $d['lat']);
        }
        if ($idProfesional) {
            $stmt->bindValue(':self', (int) $idProfesional, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ===================== ACEPTAR (el primero que llega se la queda) ===================== */
    public function reclamarTrabajo($idSolicitud, $idProfesional): bool
    {
        $idSolicitud   = (int) $idSolicitud;
        $idProfesional = (int) $idProfesional;

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare('
                SELECT id_usuario, id_plan, id_categoria, macrodistrito_base, tokens_disponibles, estado_validacion
                  FROM profesionales WHERE id_profesional = :id FOR UPDATE');
            $stmt->execute([':id' => $idProfesional]);
            $prof = $stmt->fetch();

            if (!$prof || $prof['estado_validacion'] !== 'APROBADO') {
                throw new DomainException('Tu cuenta aún no está validada para aceptar trabajos.');
            }
            if ((int) $prof['tokens_disponibles'] <= 0) {
                throw new DomainException('No tienes tokens suficientes. Por favor, recarga tu membresía.');
            }
            if ($this->tieneTrabajoActivo($idProfesional)) {
                throw new DomainException('Ya tienes un trabajo activo. Finalízalo antes de aceptar otro.');
            }

            $stmt = $this->db->prepare('
                SELECT c.id_usuario FROM solicitudes_servicio s
                 INNER JOIN clientes c ON c.id_cliente = s.id_cliente WHERE s.id_solicitud = :id');
            $stmt->execute([':id' => $idSolicitud]);
            $duenio = $stmt->fetchColumn();
            if ($duenio !== false && (int) $duenio === (int) $prof['id_usuario']) {
                throw new DomainException('No puedes aceptar tu propia solicitud.');
            }

            // Misma regla que la lista y el aviso: la que le asignaron a él, o una abierta de su categoría,
            // en su zona (macrodistrito o 10 km) y que ya le toque ver según su plan
            $d     = $this->datosCascada((int) $prof['id_plan'], $idProfesional);
            $conGps = $d['lat'] !== null;

            $stmt = $this->db->prepare("
                UPDATE solicitudes_servicio s
                   SET s.id_profesional = :prof, s.estado_servicio = 'ACEPTADA'
                 WHERE s.id_solicitud = :sol AND s.estado_servicio = 'PENDIENTE'
                   AND ( s.id_profesional = :prof2
                         OR ( s.id_profesional IS NULL AND s.id_categoria = :cat
                              AND s.fecha_solicitud <= DATE_SUB(NOW(), INTERVAL :retraso MINUTE)
                              AND (" . $this->sqlZona($conGps) . ') ) )');
            $stmt->bindValue(':prof', $idProfesional, PDO::PARAM_INT);
            $stmt->bindValue(':sol', $idSolicitud, PDO::PARAM_INT);
            $stmt->bindValue(':prof2', $idProfesional, PDO::PARAM_INT);
            $stmt->bindValue(':cat', (int) $prof['id_categoria'], PDO::PARAM_INT);
            $stmt->bindValue(':retraso', $d['retraso'], PDO::PARAM_INT);
            $stmt->bindValue(':macro', (string) $prof['macrodistrito_base'], PDO::PARAM_STR);
            if ($conGps) {
                $stmt->bindValue(':lat1', $d['lat']);
                $stmt->bindValue(':lng1', $d['lng']);
                $stmt->bindValue(':lat2', $d['lat']);
            }
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            $this->descontarToken($idProfesional);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $this->traducirError($e);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /* ===================== CAMBIAR ESTADO ===================== */
    public function cambiarEstado(int $idSolicitud, string $nuevoEstado, int $idProfesional, ?int $tiempoEstimado = null, ?float $precioAcordado = null): void
    {
        if (!in_array($nuevoEstado, array_slice(self::ESTADOS, 1), true)) {
            throw new DomainException('Estado de servicio inválido.');
        }
        if ($nuevoEstado === 'FINALIZADA' && ($precioAcordado === null || $precioAcordado <= 0 || $precioAcordado > 99999)) {
            throw new DomainException('Ingresa el precio final acordado (mayor a 0).');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare('SELECT id_profesional, estado_servicio FROM solicitudes_servicio WHERE id_solicitud = :id LIMIT 1 FOR UPDATE');
            $stmt->execute([':id' => $idSolicitud]);
            $sol = $stmt->fetch();

            if (!$sol || (int) $sol['id_profesional'] !== $idProfesional) {
                throw new DomainException('No tienes permiso para modificar esta solicitud.');
            }

            $actual = $sol['estado_servicio'];
            if (!in_array($nuevoEstado, self::TRANSICIONES[$actual] ?? [], true)) {
                throw new DomainException("No se puede pasar de {$actual} a {$nuevoEstado}.");
            }

            if ($actual === 'PENDIENTE' && $nuevoEstado === 'ACEPTADA') {
                if ($this->tieneTrabajoActivo($idProfesional)) {
                    throw new DomainException('Ya tienes un trabajo activo. Finalízalo antes de aceptar otro.');
                }
                $this->descontarToken($idProfesional);
            }

            $sets   = ['estado_servicio = :estado'];
            $params = [':estado' => $nuevoEstado, ':id' => $idSolicitud];

            if (in_array($nuevoEstado, ['ACEPTADA', 'EN_CAMINO'], true) && $tiempoEstimado !== null && $tiempoEstimado > 0 && $tiempoEstimado <= 600) {
                $sets[] = 'tiempo_estimado_llegada_min = :tiempo';
                $params[':tiempo'] = $tiempoEstimado;
            }
            if ($nuevoEstado === 'EN_PROCESO') {
                $sets[] = 'fecha_inicio_atencion = NOW()';
            }
            if ($nuevoEstado === 'FINALIZADA') {
                $sets[] = 'fecha_finalizacion = NOW()';
                $sets[] = 'precio_acordado = :precio';
                $params[':precio'] = $precioAcordado;
            }
                        if ($nuevoEstado === 'CANCELADA') {
                $sets[] = 'fecha_finalizacion = NOW()';
                $sets[] = 'motivo_cancelacion = :motivo';
                $params[':motivo'] = ($actual === 'PENDIENTE') ? 'Rechazada por el profesional' : 'Cancelada por el profesional';
            }

            $this->db->prepare('UPDATE solicitudes_servicio SET ' . implode(', ', $sets) . ' WHERE id_solicitud = :id')->execute($params);
            $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $this->traducirError($e);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** Debe llamarse dentro de una transacción (bloquea la fila del profesional). */
    private function descontarToken(int $idProfesional): void
    {
        $stmt = $this->db->prepare('SELECT tokens_disponibles FROM profesionales WHERE id_profesional = :id FOR UPDATE');
        $stmt->execute([':id' => $idProfesional]);
        $tokens = $stmt->fetchColumn();
        if ($tokens === false || (int) $tokens <= 0) {
            throw new DomainException('No tienes tokens suficientes. Por favor, recarga tu membresía.');
        }
        $this->db->prepare('UPDATE profesionales SET tokens_disponibles = tokens_disponibles - 1 WHERE id_profesional = :id')
                 ->execute([':id' => $idProfesional]);
    }

        /** El cliente puede cancelar mientras el técnico aún no va en camino. */
    public function cancelarPorCliente(int $idSolicitud, int $idUsuario): void
    {
               $stmt = $this->db->prepare("
            UPDATE solicitudes_servicio s
             INNER JOIN clientes c ON c.id_cliente = s.id_cliente
               SET s.estado_servicio = 'CANCELADA', s.fecha_finalizacion = NOW(), s.motivo_cancelacion = 'Cancelada por el cliente'
             WHERE s.id_solicitud = :id AND c.id_usuario = :u AND s.estado_servicio IN ('PENDIENTE','ACEPTADA')");
        $stmt->execute([':id' => $idSolicitud, ':u' => $idUsuario]);

        if ($stmt->rowCount() === 0) {
            throw new DomainException('Esta solicitud ya no se puede cancelar.');
        }
    }

        public function guardarTiempoEstimado(int $idSolicitud, int $idProfesional, int $minutos): void
    {
        if ($minutos < 1 || $minutos > 600) {
            return;
        }
        $this->db->prepare(
            "UPDATE solicitudes_servicio SET tiempo_estimado_llegada_min = :m
              WHERE id_solicitud = :id AND id_profesional = :p AND estado_servicio = 'ACEPTADA'"
        )->execute([':m' => $minutos, ':id' => $idSolicitud, ':p' => $idProfesional]);
    }
}