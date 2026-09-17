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

    public function listarTodasTransacciones(): array {
        $stmt = $this->db->query("
            SELECT t.id_transaccion, t.tipo_transaccion, t.monto, t.codigo_comprobante, t.fecha_pago, t.estado_pago,
                   p.id_profesional, pl.nombre_plan, u.nombre, u.apellido
            FROM transacciones_suscripcion t
            INNER JOIN profesionales p ON t.id_profesional = p.id_profesional
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            LEFT JOIN planes_suscripcion pl ON t.id_plan = pl.id_plan
            ORDER BY 
                CASE WHEN t.estado_pago = 'PENDIENTE' THEN 1 ELSE 2 END ASC,
                t.fecha_pago DESC
        ");
        return $stmt->fetchAll();
    }

   public function confirmarTransaccion($idTransaccion) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();

            // 1. Obtener los datos de la transacción antes de confirmarla
            $stmtTx = $db->prepare("
                SELECT t.id_profesional, t.id_plan, t.tipo_transaccion, p.tokens_mensuales 
                FROM transacciones_suscripcion t
                LEFT JOIN planes_suscripcion p ON t.id_plan = p.id_plan
                WHERE t.id_transaccion = :id
            ");
            $stmtTx->execute([':id' => $idTransaccion]);
            $tx = $stmtTx->fetch(PDO::FETCH_ASSOC);

            if (!$tx) {
                throw new Exception("La transacción no existe.");
            }

            // 2. Cambiar el estado del pago a CONFIRMADO
            $stmtUpdateTx = $db->prepare("UPDATE transacciones_suscripcion SET estado_pago = 'CONFIRMADO' WHERE id_transaccion = :id");
            $stmtUpdateTx->execute([':id' => $idTransaccion]);

            // 3. Asignar los beneficios al Profesional
            if ($tx['tipo_transaccion'] === 'MEMBRESIA_MENSUAL') {
                // Si compró un plan (Ej. Básico o Premium)
                $stmtProf = $db->prepare("
                    UPDATE profesionales 
                    SET id_plan = :id_plan,
                        tokens_disponibles = tokens_disponibles + :tokens,
                        fin_suscripcion = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    WHERE id_profesional = :id_prof
                ");
                $stmtProf->execute([
                    ':id_plan' => $tx['id_plan'],
                    ':tokens' => (int) $tx['tokens_mensuales'], // Asegúrate de que la columna se llame tokens_mensuales en la BD
                    ':id_prof' => $tx['id_profesional']
                ]);
            } else {
                // Si solo compró un paquete de tokens suelto
                $stmtProf = $db->prepare("
                    UPDATE profesionales 
                    SET tokens_disponibles = tokens_disponibles + :tokens
                    WHERE id_profesional = :id_prof
                ");
                $stmtProf->execute([
                    ':tokens' => (int) $tx['tokens_mensuales'],
                    ':id_prof' => $tx['id_profesional']
                ]);
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            // Lanza el error para que el desarrollador pueda verlo si algo falla
            throw new Exception("Error al confirmar: " . $e->getMessage()); 
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