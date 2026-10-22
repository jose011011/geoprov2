<?php
/** FixGeo - Consultas del panel del técnico (perfil, estadísticas, planes, pagos, GPS). */
class ProfesionalPanel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function perfilPorUsuario(int $idUsuario): ?array
    {
        $s = $this->db->prepare(
            'SELECT p.*, u.nombre, u.apellido, u.correo, u.celular,
                    c.nombre_categoria, c.icono_fa,                     pl.nombre_plan, pl.posicionamiento_destacado, pl.retraso_cascada_min,
                    COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas
               FROM profesionales p
               INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
               INNER JOIN categorias c ON c.id_categoria = p.id_categoria
               INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
               LEFT JOIN vw_metricas_profesionales vw ON vw.id_profesional = p.id_profesional
              WHERE p.id_usuario = :u LIMIT 1'
        );
        $s->execute([':u' => $idUsuario]);
        return $s->fetch() ?: null;
    }

    public function documentos(int $idProfesional): array
    {
                $s = $this->db->prepare('SELECT id_documento, tipo_documento_archivo, estado_revision, observacion FROM documentos_profesional WHERE id_profesional = :p ORDER BY tipo_documento_archivo');
        $s->execute([':p' => $idProfesional]);
        return $s->fetchAll();
    }
    public function documentoPropio(int $idDocumento, int $idUsuario): ?array
    {
        $s = $this->db->prepare(
            'SELECT d.id_documento, d.archivo_url, d.estado_revision FROM documentos_profesional d
              INNER JOIN profesionales p ON p.id_profesional = d.id_profesional
              WHERE d.id_documento = :d AND p.id_usuario = :u'
        );
        $s->execute([':d' => $idDocumento, ':u' => $idUsuario]);
        return $s->fetch() ?: null;
    }

    public function reemplazarDocumento(int $idDocumento, int $idProfesional, string $archivoUrl): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "UPDATE documentos_profesional SET archivo_url = :a, estado_revision = 'PENDIENTE', observacion = NULL, fecha_revision = NULL, fecha_subida = NOW()
                  WHERE id_documento = :d AND id_profesional = :p"
            )->execute([':a' => $archivoUrl, ':d' => $idDocumento, ':p' => $idProfesional]);

            $this->db->prepare("UPDATE profesionales SET estado_validacion = 'PENDIENTE' WHERE id_profesional = :p AND estado_validacion = 'RECHAZADO'")
                     ->execute([':p' => $idProfesional]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function idsAdministradores(): array
    {
        return $this->db->query("SELECT id_usuario FROM usuarios WHERE id_rol IN (1, 2) AND estado = 'ACTIVO'")->fetchAll(PDO::FETCH_COLUMN);
    }
    

    public function estadisticas(int $idProfesional): array
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(estado_servicio = 'FINALIZADA') AS finalizadas,
                    SUM(estado_servicio IN ('ACEPTADA','EN_CAMINO','EN_PROCESO')) AS activas,
                    COALESCE(SUM(CASE WHEN estado_servicio = 'FINALIZADA' THEN precio_acordado END), 0) AS ganancias,
                    COALESCE(SUM(CASE WHEN estado_servicio = 'FINALIZADA' AND fecha_finalizacion >= DATE_FORMAT(NOW(), '%Y-%m-01')
                                      THEN precio_acordado END), 0) AS ganancias_mes
               FROM solicitudes_servicio WHERE id_profesional = :p"
        );
        $s->execute([':p' => $idProfesional]);
        $f = $s->fetch();
        return [
            'total'         => (int) ($f['total'] ?? 0),
            'finalizadas'   => (int) ($f['finalizadas'] ?? 0),
            'activas'       => (int) ($f['activas'] ?? 0),
            'ganancias'     => (float) ($f['ganancias'] ?? 0),
            'ganancias_mes' => (float) ($f['ganancias_mes'] ?? 0),
        ];
    }

    public function membresiaVencida(array $perfil): bool
    {
        if ((int) $perfil['id_plan'] <= 1) {
            return false;
        }
        return empty($perfil['fin_suscripcion']) || strtotime($perfil['fin_suscripcion']) < strtotime('today');
    }

    /** Días que faltan para que venza el plan de pago (null si es el plan gratuito). */
    public function diasParaVencer(array $perfil): ?int
    {
        if ((int) $perfil['id_plan'] <= 1 || empty($perfil['fin_suscripcion'])) {
            return null;
        }
        return (int) floor((strtotime($perfil['fin_suscripcion']) - strtotime('today')) / 86400);
    }

    /** Cambia entre DISPONIBLE y NO_DISPONIBLE (solo técnicos aprobados). */
    public function actualizarDisponibilidad(int $idProfesional, string $estadoValidacion, string $nuevo): array
    {
        if (!in_array($nuevo, ['DISPONIBLE', 'NO_DISPONIBLE'], true)) {
            throw new DomainException('Estado no válido.');
        }
        if ($estadoValidacion !== 'APROBADO') {
            throw new DomainException('Podrás activarte cuando un administrador apruebe tu perfil.');
        }
        $this->db->prepare('UPDATE profesionales SET estado_disponibilidad = :e WHERE id_profesional = :p')
                 ->execute([':e' => $nuevo, ':p' => $idProfesional]);
        return ['estado' => $nuevo];
    }

    public function planes(): array
    {
        return $this->db->query('SELECT * FROM planes_suscripcion ORDER BY id_plan ASC')->fetchAll();
    }

    public function tienePagoPendiente(int $idProfesional): bool
    {
        $s = $this->db->prepare("SELECT COUNT(*) FROM transacciones_suscripcion WHERE id_profesional = :p AND estado_pago = 'PENDIENTE'");
        $s->execute([':p' => $idProfesional]);
        return (int) $s->fetchColumn() > 0;
    }

    

    public function actualizarPerfil(int $idUsuario, int $idProfesional, array $d): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE usuarios SET celular = :c WHERE id_usuario = :u')
                     ->execute([':c' => $d['celular'], ':u' => $idUsuario]);
            $this->db->prepare(
                'UPDATE profesionales SET macrodistrito_base = :m, zona_especifica = :z, tarifa_base = :t, descripcion_servicio = :d
                  WHERE id_profesional = :p'
            )->execute([':m' => $d['macrodistrito'], ':z' => $d['zona'], ':t' => $d['tarifa'], ':d' => $d['descripcion'], ':p' => $idProfesional]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Guarda la ubicación solo si el técnico tiene un viaje en curso (EN_CAMINO). */
    public function actualizarGps(int $idUsuario, float $lat, float $lng): void
    {
        $this->db->prepare(
            "UPDATE profesionales p
              INNER JOIN solicitudes_servicio s ON s.id_profesional = p.id_profesional AND s.estado_servicio = 'EN_CAMINO'
                 SET p.latitud_actual = :lat, p.longitud_actual = :lng
               WHERE p.id_usuario = :u"
        )->execute([':lat' => $lat, ':lng' => $lng, ':u' => $idUsuario]);
    }




    //desde aqui
        public const PAQUETE_MIN     = 10;
    public const PAQUETE_MAX     = 100;
    public const PAQUETE_PASO    = 10;
    /** 1.0 = el token suelto cuesta igual que dentro del plan Básico. Sube (ej. 1.25) si quieres que los planes convengan más. */
    public const RECARGO_PAQUETE = 1.0;

    /** Precio de 1 token según tu tabla de planes (primer plan de pago con tokens limitados, hoy Básico). */
    public function precioPorToken(): float
    {
        $f = $this->db->query(
            'SELECT precio, tokens_otorgados FROM planes_suscripcion
              WHERE id_plan > 1 AND estado = 1 AND tokens_otorgados > 0 AND tokens_otorgados < 999 AND precio > 0
              ORDER BY id_plan LIMIT 1'
        )->fetch();
        if (!$f) {
            return 0.0;
        }
        return ((float) $f['precio'] / (int) $f['tokens_otorgados']) * self::RECARGO_PAQUETE;
    }

    /** Precio de N tokens, redondeado hacia arriba a 0.50 Bs. */
    public function precioPaquete(int $tokens): float
    {
        $unitario = $this->precioPorToken();
        return $unitario > 0 ? ceil($tokens * $unitario * 2) / 2 : 0.0;
    }

    public function pagoPendiente(int $idProfesional): ?array
    {
        $s = $this->db->prepare(
            "SELECT t.id_transaccion, t.monto, t.codigo_comprobante, t.fecha_pago, t.tipo_transaccion, t.tokens_comprados, pl.nombre_plan
               FROM transacciones_suscripcion t
               LEFT JOIN planes_suscripcion pl ON pl.id_plan = t.id_plan
              WHERE t.id_profesional = :p AND t.estado_pago = 'PENDIENTE'
              ORDER BY t.fecha_pago DESC LIMIT 1"
        );
        $s->execute([':p' => $idProfesional]);
        return $s->fetch() ?: null;
    }

    /** Premium vigente = tokens ilimitados. */
    public function tieneTokensIlimitados(array $perfil): bool
    {
        return (int) $perfil['posicionamiento_destacado'] === 1 && !$this->membresiaVencida($perfil);
    }

    /** Explica por qué NO se puede comprar ahora (null = se puede). */
    public function motivoBloqueoCompra(array $perfil, ?array $pendiente): ?string
    {
        if ($perfil['estado_validacion'] !== 'APROBADO') {
            return 'Podrás comprar planes y tokens cuando un administrador apruebe tu perfil.';
        }
        if ($pendiente) {
            return 'Tienes un pago en revisión. Podrás enviar otro cuando lo confirmemos o lo rechacemos.';
        }
        return null;
    }

    /** $tipo = 'PLAN' ($idItem = id del plan) o 'PAQUETE' ($idItem = cantidad de tokens). El monto sale SIEMPRE de la base de datos. */
    public function registrarPago(array $perfil, string $tipo, int $idItem, string $metodo, string $codigo, ?string $foto): int
    {
               $idPlan = null;
        $tokens = 0;

        if ($tipo === 'PLAN') {
            $s = $this->db->prepare('SELECT id_plan, precio FROM planes_suscripcion WHERE id_plan = :id AND id_plan > 1 AND estado = 1');
            $s->execute([':id' => $idItem]);
            $plan = $s->fetch();
            if (!$plan) {
                throw new DomainException('El plan seleccionado no es válido.');
            }
            if (!$this->membresiaVencida($perfil) && (int) $perfil['id_plan'] > (int) $plan['id_plan']) {
                throw new DomainException('Tu plan actual es superior y sigue vigente. Podrás cambiar a este cuando venza.');
            }
            $idPlan = (int) $plan['id_plan'];
            $monto  = (float) $plan['precio'];
            $tipoTx = 'MEMBRESIA_MENSUAL';
        } else {
            if ($this->tieneTokensIlimitados($perfil)) {
                throw new DomainException('Tu plan Premium ya incluye tokens ilimitados.');
            }
            if ($idItem < self::PAQUETE_MIN || $idItem > self::PAQUETE_MAX || $idItem % self::PAQUETE_PASO !== 0) {
                throw new DomainException('Elige una cantidad de tokens entre ' . self::PAQUETE_MIN . ' y ' . self::PAQUETE_MAX . ', de ' . self::PAQUETE_PASO . ' en ' . self::PAQUETE_PASO . '.');
            }
            $monto = $this->precioPaquete($idItem);
            if ($monto <= 0) {
                throw new DomainException('La compra de tokens sueltos no está disponible por ahora.');
            }
            $tokens = $idItem;
            $tipoTx = 'PAQUETE_TOKENS';
        }

        $this->db->prepare(
            "INSERT INTO transacciones_suscripcion
                (id_profesional, id_plan, tokens_comprados, tipo_transaccion, monto, metodo_pago, codigo_comprobante, estado_pago, foto_comprobante)
             VALUES (:p, :plan, :tok, :tipo, :monto, :metodo, :codigo, 'PENDIENTE', :foto)"
        )->execute([
            ':p' => (int) $perfil['id_profesional'], ':plan' => $idPlan, ':tok' => $tokens,
            ':tipo' => $tipoTx, ':monto' => $monto, ':metodo' => $metodo, ':codigo' => $codigo, ':foto' => $foto,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function ultimosPagos(int $idProfesional, int $limite = 6): array
    {
        $s = $this->db->prepare(
            'SELECT t.id_transaccion, t.tipo_transaccion, t.tokens_comprados, t.monto, t.codigo_comprobante, t.estado_pago,
                    t.observacion, t.fecha_pago, COALESCE(t.foto_comprobante, t.comprobante_imagen) AS foto_comprobante, pl.nombre_plan
               FROM transacciones_suscripcion t
               LEFT JOIN planes_suscripcion pl ON pl.id_plan = t.id_plan
              WHERE t.id_profesional = :p
              ORDER BY t.fecha_pago DESC LIMIT ' . (int) $limite
        );
        $s->execute([':p' => $idProfesional]);
        return $s->fetchAll();
    }

    /** Nombre del comprobante, solo si el pago pertenece a este usuario. */
    public function comprobanteDe(int $idTransaccion, int $idUsuario): ?string
    {
        $s = $this->db->prepare(
            'SELECT COALESCE(t.foto_comprobante, t.comprobante_imagen) FROM transacciones_suscripcion t
              INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
              WHERE t.id_transaccion = :t AND p.id_usuario = :u'
        );
        $s->execute([':t' => $idTransaccion, ':u' => $idUsuario]);
        $f = $s->fetchColumn();
        return $f ?: null;
    }
}