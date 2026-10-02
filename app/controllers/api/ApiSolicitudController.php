<?php
class ApiSolicitudController extends Controller {

    public function __construct() {
        // Cabeceras CORS obligatorias para Flutter
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /* ========================================================
       1. RECUPERAR TRABAJO ACTIVO (Crucial para cuando la app se reinicia)
       ======================================================== */
    public function activa() {
        $usuario = $this->validarTokenApi();
        $db = Database::getInstance()->getConnection();
        
        $solicitudActiva = null;

        if ($usuario['role_id'] == 3) {
            // Es PROFESIONAL: Buscamos si tiene un trabajo que no esté finalizado ni cancelado
            $stmt = $db->prepare("
                SELECT s.*, u.nombre AS cliente_nombre, u.celular AS cliente_celular, c.nombre_categoria 
                FROM solicitudes_servicio s
                INNER JOIN clientes cli ON s.id_cliente = cli.id_cliente
                INNER JOIN usuarios u ON cli.id_usuario = u.id_usuario
                INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
                LEFT JOIN categorias c ON s.id_categoria = c.id_categoria
                WHERE p.id_usuario = :id_usuario 
                  AND s.estado_servicio NOT IN ('FINALIZADA', 'CANCELADA')
                LIMIT 1
            ");
            $stmt->execute([':id_usuario' => $usuario['id_usuario']]);
            $solicitudActiva = $stmt->fetch(PDO::FETCH_ASSOC);

        } elseif ($usuario['role_id'] == 4) {
            // Es CLIENTE: Buscamos si tiene un pedido en curso
            $stmt = $db->prepare("
                SELECT s.*, u.nombre AS prof_nombre, u.celular AS prof_celular, c.nombre_categoria 
                FROM solicitudes_servicio s
                INNER JOIN clientes cli ON s.id_cliente = cli.id_cliente
                LEFT JOIN profesionales p ON s.id_profesional = p.id_profesional
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                LEFT JOIN categorias c ON s.id_categoria = c.id_categoria
                WHERE cli.id_usuario = :id_usuario 
                  AND s.estado_servicio NOT IN ('FINALIZADA', 'CANCELADA')
                LIMIT 1
            ");
            $stmt->execute([':id_usuario' => $usuario['id_usuario']]);
            $solicitudActiva = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'ok' => true, 
            'tiene_activa' => ($solicitudActiva !== false && $solicitudActiva !== null),
            'data' => $solicitudActiva ?: null
        ]);
        exit;
    }

    /* ========================================================
       2. CANCELAR SOLICITUD (Solo si el técnico aún no llega)
       ======================================================== */
    public function cancelar() {
        $usuario = $this->validarTokenApi();
        $db = Database::getInstance()->getConnection();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $idSolicitud = (int) ($data['id_solicitud'] ?? 0);
        $motivo = trim($data['motivo'] ?? 'Cancelado por el usuario');

        if ($idSolicitud <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID de solicitud inválido']);
            exit;
        }

        // 1. Verificar el estado actual de la solicitud
        $stmtCheck = $db->prepare("SELECT estado_servicio, id_cliente, id_profesional FROM solicitudes_servicio WHERE id_solicitud = :id");
        $stmtCheck->execute([':id' => $idSolicitud]);
        $solicitud = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$solicitud) {
            echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada']);
            exit;
        }

        // 2. Regla de negocio: No se puede cancelar si ya están trabajando o si ya terminó
        if (in_array($solicitud['estado_servicio'], ['EN_PROCESO', 'FINALIZADA', 'CANCELADA'])) {
            echo json_encode(['ok' => false, 'error' => 'No puedes cancelar un servicio en este estado.']);
            exit;
        }

        // 3. Ejecutar la cancelación
        try {
            $stmt = $db->prepare("
                UPDATE solicitudes_servicio 
                SET estado_servicio = 'CANCELADA', fecha_finalizacion = CURRENT_TIMESTAMP
                WHERE id_solicitud = :id
            ");
            $stmt->execute([':id' => $idSolicitud]);
            
            echo json_encode(['ok' => true, 'mensaje' => 'El servicio ha sido cancelado exitosamente.']);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Error al cancelar la solicitud.']);
        }
        exit;
    }
}