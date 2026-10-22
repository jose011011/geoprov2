<?php
/**
 *   GET  /api/membresia/planes
 *   POST /api/membresia/comprar_tokens         { cantidad }    (de 10 en 10)
 *   POST /api/membresia/comprar_plan           { id_plan }
 *   POST /api/membresia/adjuntar_comprobante   { codigo, imagen_base64 }
 * Solo profesionales (rol 3).
 * Las compras quedan PENDIENTES: el administrador valida el pago y recién ahí
 * se acredita (sp_aprobar_pago).
 */
class ApiMembresiaController {
    private PDO $db;

    private const ROL_PROFESIONAL  = 3;
    private const BLOQUE_TOKENS    = 10;     // los tokens se venden de 10 en 10
    private const PRECIO_BLOQUE    = 10.00;  // Bs por cada bloque de 10 tokens
    private const MAX_TOKENS       = 500;    // por solicitud
    private const MAX_PENDIENTES   = 3;      // pagos esperando validación a la vez
    private const TOKENS_ILIMITADO = 999;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       PLANES + ESTADO ACTUAL + ÚLTIMOS PAGOS
       ========================================================== */
    public function planes() {
        try {
            $p = $this->profesionalActual();
            $tok = Utilidades::sqlTokensPlan($this->db, 'pl');

            $filas = $this->db->query("
                SELECT pl.*, $tok AS tokens_mensuales_calc
                  FROM planes_suscripcion pl
                 ORDER BY pl.precio ASC, pl.id_plan ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $planes = [];
            foreach ($filas as $r) {
                if (isset($r['estado']) && (int) $r['estado'] !== 1) {
                    continue; // plan desactivado
                }
                $planes[] = [
                    'id_plan'                   => (int) $r['id_plan'],
                    'nombre_plan'               => $r['nombre_plan'],
                    'precio'                    => $r['precio'],
                    'tokens_mensuales'          => (int) $r['tokens_mensuales_calc'],
                    'posicionamiento_destacado' => $r['posicionamiento_destacado'] ?? 0,
                    'descripcion'               => $r['descripcion'] ?? '',
                ];
            }

            $stmt = $this->db->prepare("
                SELECT t.id_transaccion, t.codigo_comprobante, t.tipo_transaccion, t.monto,
                       t.estado_pago, t.fecha_pago, t.observacion,
                       (t.comprobante_imagen IS NOT NULL) AS tiene_comprobante,
                       pl.nombre_plan
                  FROM transacciones_suscripcion t
                  LEFT JOIN planes_suscripcion pl ON pl.id_plan = t.id_plan
                 WHERE t.id_profesional = :p
                 ORDER BY t.fecha_pago DESC, t.id_transaccion DESC
                 LIMIT 30
            ");
            $stmt->bindValue(':p', (int) $p['id_profesional'], PDO::PARAM_INT);
            $stmt->execute();

            $this->responder([
                'ok' => true,
                'plan_actual'     => (int) $p['id_plan'],
                'fin_suscripcion' => $p['fin_suscripcion'],
                'vigente'         => $this->planVigente($p),
                'ilimitado'       => $this->esIlimitado($p),
                'tokens'          => (int) $p['tokens_disponibles'],
                'bloque_tokens'   => self::BLOQUE_TOKENS,
                'precio_bloque'   => self::PRECIO_BLOQUE,
                'max_tokens'      => self::MAX_TOKENS,
                'planes'          => $planes,
                'transacciones'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       SOLICITAR RECARGA DE TOKENS (queda PENDIENTE)
       ========================================================== */
    public function comprar_tokens() {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();
            $cantidad = (int) ($d['cantidad'] ?? 0);

            if ($cantidad < self::BLOQUE_TOKENS
                || $cantidad > self::MAX_TOKENS
                || $cantidad % self::BLOQUE_TOKENS !== 0) {
                $this->responder(['ok' => false, 'error' =>
                    'Los tokens se compran de 10 en 10 (mínimo 10 y máximo ' . self::MAX_TOKENS . ').'], 422);
            }
            if ($p['estado_validacion'] !== 'APROBADO') {
                $this->responder(['ok' => false, 'error' =>
                    'Tu cuenta aún está en revisión. Podrás comprar cuando sea aprobada.'], 403);
            }

            $idPro = (int) $p['id_profesional'];
            $this->exigirCupo($idPro);

            $monto  = ($cantidad / self::BLOQUE_TOKENS) * self::PRECIO_BLOQUE;
            $codigo = $this->codigoComprobante();

            $stmt = $this->db->prepare("
                INSERT INTO transacciones_suscripcion
                    (id_profesional, id_plan, tipo_transaccion, monto, tokens_comprados,
                     metodo_pago, codigo_comprobante, estado_pago)
                VALUES (:p, NULL, 'PAQUETE_TOKENS', :m, :t, 'QR_SIMPLE_BOLIVIA', :c, 'PENDIENTE')
            ");
            $stmt->execute([':p' => $idPro, ':m' => $monto, ':t' => $cantidad, ':c' => $codigo]);

            Utilidades::auditar($this->db, (int) $p['id_usuario'], 'RECARGA_SOLICITADA_APP',
                'transacciones_suscripcion', (int) $this->db->lastInsertId(),
                ['tokens' => $cantidad, 'monto' => $monto, 'comprobante' => $codigo]);

            $this->avisarAdmins("Pago pendiente de validar: $cantidad tokens, Bs " . number_format($monto, 2) . " ($codigo).");

            $this->responder([
                'ok' => true,
                'pendiente' => true,
                'codigo_comprobante' => $codigo,
                'monto' => $monto,
                'tokens_solicitados' => $cantidad,
                'mensaje' => 'Registramos tu solicitud. Tus tokens se acreditarán cuando el administrador valide tu pago.',
            ], 201);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       SOLICITAR UN PLAN (queda PENDIENTE)
       ========================================================== */
    public function comprar_plan() {
        $this->soloPost();
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();
            $idPlan = (int) ($d['id_plan'] ?? 0);

            if ($p['estado_validacion'] !== 'APROBADO') {
                $this->responder(['ok' => false, 'error' =>
                    'Tu cuenta aún está en revisión. Podrás contratar un plan cuando sea aprobada.'], 403);
            }

            $stmt = $this->db->prepare("SELECT * FROM planes_suscripcion WHERE id_plan = :id");
            $stmt->execute([':id' => $idPlan]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$plan || (isset($plan['estado']) && (int) $plan['estado'] !== 1)) {
                $this->responder(['ok' => false, 'error' => 'El plan elegido no existe.'], 404);
            }
            $precio = (float) $plan['precio'];
            if ($precio <= 0) {
                $this->responder(['ok' => false, 'error' =>
                    'El plan gratuito es tu plan base y no se contrata.'], 422);
            }

            // No se baja de plan mientras el actual siga vigente
            if ($this->planVigente($p) && (int) $p['id_plan'] !== $idPlan
                && (float) $p['precio'] > $precio) {
                $this->responder(['ok' => false, 'error' =>
                    'Tu plan actual sigue vigente hasta el ' . $p['fin_suscripcion']
                    . '. Podrás cambiar a uno menor cuando venza.'], 409);
            }

            $idPro = (int) $p['id_profesional'];
            $this->exigirCupo($idPro);

            $stmt = $this->db->prepare("
                SELECT 1 FROM transacciones_suscripcion
                 WHERE id_profesional = :p AND id_plan = :pl AND estado_pago = 'PENDIENTE' LIMIT 1
            ");
            $stmt->execute([':p' => $idPro, ':pl' => $idPlan]);
            if ($stmt->fetchColumn()) {
                $this->responder(['ok' => false, 'error' =>
                    'Ya tienes una solicitud pendiente para este plan. Espera a que el administrador la valide.'], 409);
            }

            $codigo = $this->codigoComprobante();
            $stmt = $this->db->prepare("
                INSERT INTO transacciones_suscripcion
                    (id_profesional, id_plan, tipo_transaccion, monto, tokens_comprados,
                     metodo_pago, codigo_comprobante, estado_pago)
                VALUES (:p, :pl, 'MEMBRESIA_MENSUAL', :m, 0, 'QR_SIMPLE_BOLIVIA', :c, 'PENDIENTE')
            ");
            $stmt->execute([':p' => $idPro, ':pl' => $idPlan, ':m' => $precio, ':c' => $codigo]);

            Utilidades::auditar($this->db, (int) $p['id_usuario'], 'PLAN_SOLICITADO_APP',
                'transacciones_suscripcion', (int) $this->db->lastInsertId(),
                ['id_plan' => $idPlan, 'monto' => $precio, 'comprobante' => $codigo]);

            $this->avisarAdmins('Pago pendiente de validar: ' . $plan['nombre_plan']
                . ', Bs ' . number_format($precio, 2) . " ($codigo).");

            $this->responder([
                'ok' => true,
                'pendiente' => true,
                'codigo_comprobante' => $codigo,
                'monto' => $precio,
                'tokens_solicitados' => 0,
                'mensaje' => 'Registramos tu solicitud. Tu plan se activará cuando el administrador valide tu pago.',
            ], 201);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       ADJUNTAR LA CAPTURA DEL PAGO (respaldo para el administrador)
       ========================================================== */
    public function adjuntar_comprobante() {
        $this->soloPost();
        $archivoNuevo = null;
        try {
            $p = $this->profesionalActual();
            $d = $this->leerJson();
            $codigo = trim((string) ($d['codigo'] ?? ''));

            $stmt = $this->db->prepare("
                SELECT id_transaccion, estado_pago, comprobante_imagen
                  FROM transacciones_suscripcion
                 WHERE codigo_comprobante = :c AND id_profesional = :p
                 LIMIT 1
            ");
            $stmt->execute([':c' => $codigo, ':p' => (int) $p['id_profesional']]);
            $t = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$t) {
                $this->responder(['ok' => false, 'error' => 'No encontramos ese pago.'], 404);
            }
            if ($t['estado_pago'] !== 'PENDIENTE') {
                $this->responder(['ok' => false, 'error' => 'Este pago ya fue revisado.'], 409);
            }

            $errores = [];
            $img = Utilidades::imagen($d['imagen_base64'] ?? null, 'tu comprobante de pago', $errores);
            if ($errores) {
                $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
            }

            $dir = Utilidades::carpetaUploads('comprobantes');
            $nombre = 'COMP_' . $t['id_transaccion'] . '_' . bin2hex(random_bytes(8)) . '.' . $img['ext'];
            if (file_put_contents($dir . $nombre, $img['bin']) === false) {
                throw new RuntimeException('No se pudo guardar el comprobante.');
            }
            $archivoNuevo = $dir . $nombre;

            $this->db->prepare('UPDATE transacciones_suscripcion SET comprobante_imagen = :f WHERE id_transaccion = :i')
                ->execute([':f' => 'uploads/comprobantes/' . $nombre, ':i' => $t['id_transaccion']]);

            // Borra el comprobante anterior si lo reemplazó
            if (!empty($t['comprobante_imagen'])) {
                $viejo = $dir . basename((string) $t['comprobante_imagen']);
                if (is_file($viejo)) {
                    @unlink($viejo);
                }
            }

            $this->responder(['ok' => true, 'mensaje' => 'Comprobante enviado. El administrador lo revisará.']);
        } catch (Throwable $e) {
            if ($archivoNuevo && is_file($archivoNuevo)) {
                @unlink($archivoNuevo);
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       AUXILIARES
       ========================================================== */
    private function profesionalActual(): array {
        $usuario = ApiAuth::exigir($this->db);
        if ((int) $usuario['id_rol'] !== self::ROL_PROFESIONAL) {
            $this->responder(['ok' => false, 'error' => 'Esta sección es solo para profesionales.'], 403);
        }

        $tok = Utilidades::sqlTokensPlan($this->db, 'pl');
        $stmt = $this->db->prepare("
            SELECT p.id_profesional, p.id_usuario, p.id_plan, p.tokens_disponibles,
                   p.fin_suscripcion, p.estado_validacion,
                   pl.nombre_plan, pl.precio, $tok AS tokens_mensuales,
                   (p.fin_suscripcion IS NOT NULL AND p.fin_suscripcion >= CURDATE()) AS vigente
              FROM profesionales p
              INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
             WHERE p.id_usuario = :u
             LIMIT 1
        ");
        $stmt->execute([':u' => $usuario['id_usuario']]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            $this->responder(['ok' => false, 'error' => 'No encontramos tu perfil profesional.'], 403);
        }
        return $fila;
    }

    private function planVigente(array $p): bool {
        return (float) $p['precio'] > 0 && (int) $p['vigente'] === 1;
    }

    private function esIlimitado(array $p): bool {
        return $this->planVigente($p) && (int) $p['tokens_mensuales'] >= self::TOKENS_ILIMITADO;
    }

    /** Evita llenar al administrador de solicitudes sin pagar. */
    private function exigirCupo(int $idPro): void {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM transacciones_suscripcion
             WHERE id_profesional = :p AND estado_pago = 'PENDIENTE'
        ");
        $stmt->execute([':p' => $idPro]);
        if ((int) $stmt->fetchColumn() >= self::MAX_PENDIENTES) {
            $this->responder(['ok' => false, 'error' =>
                'Ya tienes ' . self::MAX_PENDIENTES . ' pagos esperando validación. '
                . 'Espera a que el administrador los revise.'], 409);
        }
    }

    private function codigoComprobante(): string {
        do {
            $c = 'GP-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $this->db->prepare('SELECT 1 FROM transacciones_suscripcion WHERE codigo_comprobante = :c');
            $stmt->execute([':c' => $c]);
        } while ($stmt->fetchColumn());
        return $c;
    }

    /** Avisa a los administradores que hay un pago por validar. */
    private function avisarAdmins(string $mensaje): void {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
            SELECT id_usuario, 'PAGO_PENDIENTE', :m, 'admin/pagos'
              FROM usuarios WHERE id_rol IN (1, 2) AND estado = 'ACTIVO'
        ");
        $stmt->execute([':m' => mb_substr($mensaje, 0, 255)]);
    }

    private function soloPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
    }

    private function leerJson(): array {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiMembresia] ' . $e->getMessage());
        $resp = ['ok' => false, 'error' => 'No pudimos procesar tu solicitud. Intenta de nuevo.'];
        if (Utilidades::debug()) {
            $resp['detalle'] = $e->getMessage();
        }
        $this->responder($resp, 500);
    }
}