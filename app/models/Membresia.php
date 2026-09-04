<?php
class Membresia {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPlanesActivos(): array {
        $stmt = $this->db->query("SELECT * FROM planes_suscripcion WHERE estado = 1 ORDER BY precio_mensual ASC");
        return $stmt->fetchAll();
    }

    public function obtenerPlanPorId(int $idPlan): ?array {
        $stmt = $this->db->prepare("SELECT * FROM planes_suscripcion WHERE id_plan = :id LIMIT 1");
        $stmt->execute([':id' => $idPlan]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /** Profesional sube comprobante de pago -> queda PENDIENTE hasta que admin confirme */
    public function solicitarCambioPlan(int $idProfesional, int $idPlan, float $monto, string $codigoComprobante): int {
        $stmt = $this->db->prepare("
            INSERT INTO transacciones_suscripcion (id_profesional, id_plan, tipo_transaccion, monto, codigo_comprobante, estado_pago)
            VALUES (:id_prof, :id_plan, 'MEMBRESIA_MENSUAL', :monto, :codigo, 'PENDIENTE')
        ");
        $stmt->execute([
            ':id_prof' => $idProfesional,
            ':id_plan' => $idPlan,
            ':monto'   => $monto,
            ':codigo'  => $codigoComprobante
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function solicitarPaqueteTokens(int $idProfesional, float $monto, string $codigoComprobante): int {
        $stmt = $this->db->prepare("
            INSERT INTO transacciones_suscripcion (id_profesional, id_plan, tipo_transaccion, monto, codigo_comprobante, estado_pago)
            VALUES (:id_prof, NULL, 'PAQUETE_TOKENS', :monto, :codigo, 'PENDIENTE')
        ");
        $stmt->execute([':id_prof' => $idProfesional, ':monto' => $monto, ':codigo' => $codigoComprobante]);
        return (int) $this->db->lastInsertId();
    }

    public function listarPendientes(): array {
        $stmt = $this->db->query("
            SELECT t.id_transaccion, t.tipo_transaccion, t.monto, t.codigo_comprobante, t.fecha_pago,
                   p.id_profesional, pl.nombre_plan, u.nombre, u.apellido
            FROM transacciones_suscripcion t
            INNER JOIN profesionales p ON t.id_profesional = p.id_profesional
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            LEFT JOIN planes_suscripcion pl ON t.id_plan = pl.id_plan
            WHERE t.estado_pago = 'PENDIENTE'
            ORDER BY t.fecha_pago ASC
        ");
        return $stmt->fetchAll();
    }

    public function confirmarTransaccion(int $idTransaccion): void {
        $stmt = $this->db->prepare("SELECT * FROM transacciones_suscripcion WHERE id_transaccion = :id LIMIT 1");
        $stmt->execute([':id' => $idTransaccion]);
        $trans = $stmt->fetch();

        if (!$trans || $trans['estado_pago'] !== 'PENDIENTE') {
            throw new Exception("Transacción no encontrada o ya procesada.");
        }

        $this->db->beginTransaction();
        try {
            if ($trans['tipo_transaccion'] === 'MEMBRESIA_MENSUAL') {
                $plan = $this->obtenerPlanPorId((int) $trans['id_plan']);
                $stmtUp = $this->db->prepare("
                    UPDATE profesionales
                    SET id_plan = :id_plan, tokens_disponibles = :tokens, fin_suscripcion = DATE_ADD(NOW(), INTERVAL 30 DAY)
                    WHERE id_profesional = :id_prof
                ");
                $stmtUp->execute([
                    ':id_plan' => $trans['id_plan'],
                    ':tokens'  => $plan['tokens_mensuales'],
                    ':id_prof' => $trans['id_profesional']
                ]);
            } elseif ($trans['tipo_transaccion'] === 'PAQUETE_TOKENS') {
                $stmtUp = $this->db->prepare("
                    UPDATE profesionales SET tokens_disponibles = tokens_disponibles + 10
                    WHERE id_profesional = :id_prof
                ");
                $stmtUp->execute([':id_prof' => $trans['id_profesional']]);
            }

            $stmtConf = $this->db->prepare("UPDATE transacciones_suscripcion SET estado_pago = 'CONFIRMADO' WHERE id_transaccion = :id");
            $stmtConf->execute([':id' => $idTransaccion]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function rechazarTransaccion(int $idTransaccion): void {
        $stmt = $this->db->prepare("UPDATE transacciones_suscripcion SET estado_pago = 'FALLIDO' WHERE id_transaccion = :id AND estado_pago = 'PENDIENTE'");
        $stmt->execute([':id' => $idTransaccion]);
    }

    public function suscripcionVencida(?string $finSuscripcion, int $idPlan): bool {
        if ($idPlan === 1) return false; // plan gratuito no vence
        if (!$finSuscripcion) return true;
        return strtotime($finSuscripcion) < time();
    }
}