<?php
require_once "../app/models/Profesional.php";
require_once "../app/models/Solicitud.php";

class ApiProfesionalController extends Controller {
    private Profesional $profesionalModel;
    private Solicitud $solicitudModel;

    public function __construct() {
        $this->profesionalModel = new Profesional();
        $this->solicitudModel = new Solicitud();
        
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
       FUNCIÓN DE APOYO: OBTENER PERFIL DEL PROFESIONAL
       ======================================================== */
    private function getPerfilProf($idUsuario) {
        $perfil = $this->profesionalModel->buscarPorUsuario($idUsuario);
        if (!$perfil) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Perfil de profesional no encontrado']);
            exit;
        }
        return $perfil;
    }

    /* ========================================================
       1. EL LATIDO DE LA CASCADA: OBTENER TRABAJOS LIBRES
       ======================================================== */
    public function oportunidades() {
        // 1. El guardián verifica el Token de Flutter
        $usuario = $this->validarTokenApi();
        $perfil = $this->getPerfilProf($usuario['id_usuario']);

        // 2. Si su radar está apagado, devolvemos un array vacío
        if ($perfil['estado_disponibilidad'] !== 'DISPONIBLE') {
            echo json_encode(['ok' => true, 'data' => []]);
            exit;
        }

        // 3. Traemos las solicitudes aplicando la IA de Cascada (0, 3 o 7 min de retraso)
        $solicitudes = $this->solicitudModel->obtenerOportunidadesCascada(
            (int) $perfil['id_plan'], 
            $perfil['macrodistrito_base'] ?? 'CENTRO',
            (int) $perfil['id_categoria']
        );

        echo json_encode(['ok' => true, 'data' => $solicitudes]);
        exit;
    }

    /* ========================================================
       2. TOGGLE DEL RADAR (DISPONIBLE / OCUPADO)
       ======================================================== */
    public function toggleRadar() {
        $usuario = $this->validarTokenApi();
        $perfil = $this->getPerfilProf($usuario['id_usuario']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        $nuevoEstado = trim($data['estado'] ?? ''); 

        if (!in_array($nuevoEstado, ['DISPONIBLE', 'OCUPADO'])) {
            echo json_encode(['ok' => false, 'error' => 'Estado inválido. Usa DISPONIBLE o OCUPADO.']);
            exit;
        }

        try {
            $this->profesionalModel->actualizarDisponibilidad(
                (int) $perfil['id_profesional'],
                $perfil['estado_validacion'],
                $nuevoEstado
            );
            echo json_encode(['ok' => true, 'mensaje' => 'Radar actualizado a ' . $nuevoEstado]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ========================================================
       3. ACEPTAR TRABAJO (ESCUDO ANTI-CHOQUES Y COBRO DE TOKEN)
       ======================================================== */
    public function aceptarTrabajo() {
        $usuario = $this->validarTokenApi();
        $perfil = $this->getPerfilProf($usuario['id_usuario']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        $idSolicitud = (int) ($data['id_solicitud'] ?? 0);

        if ($idSolicitud <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID de solicitud inválido']);
            exit;
        }

        if ((int)$perfil['tokens_disponibles'] <= 0) {
            echo json_encode(['ok' => false, 'error' => 'No tienes tokens suficientes. Recarga tu billetera.']);
            exit;
        }

        try {
            // Validación: Un profesional no puede tener múltiples trabajos activos simultáneamente
            $db = Database::getInstance()->getConnection();
            $stmtActivos = $db->prepare("
                SELECT COUNT(*) FROM solicitudes_servicio 
                WHERE id_profesional = :id_prof 
                AND estado_servicio IN ('ACEPTADA', 'EN_CAMINO', 'EN_PROCESO')
            ");
            $stmtActivos->execute([':id_prof' => $perfil['id_profesional']]);
            
            if ($stmtActivos->fetchColumn() > 0) {
                echo json_encode(['ok' => false, 'error' => 'Ya tienes un trabajo activo. Finalízalo antes de aceptar otro.']);
                exit;
            }

            // El modelo hace el UPDATE con "WHERE id_profesional IS NULL"
            $reclamado = $this->solicitudModel->reclamarTrabajo($idSolicitud, (int) $perfil['id_profesional']);
            
            if ($reclamado) {
                echo json_encode(['ok' => true, 'mensaje' => '¡Trabajo asignado exitosamente!']);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Lo sentimos, otro experto aceptó este trabajo antes.']);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Error de servidor: ' . $e->getMessage()]);
        }
        exit;
    }

    /* ========================================================
       4. AVANZAR EL FLUJO (EN CAMINO -> EN PROCESO -> FINALIZADA)
       ======================================================== */
    public function cambiarEstado() {
        $usuario = $this->validarTokenApi();
        $perfil = $this->getPerfilProf($usuario['id_usuario']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        $idSolicitud = (int) ($data['id_solicitud'] ?? 0);
        $nuevoEstado = $data['estado'] ?? '';
        $tiempoLlegada = (int) ($data['tiempo_estimado'] ?? 0);
        $precioAcordado = (float) ($data['precio_acordado'] ?? 0);

        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE solicitudes_servicio SET estado_servicio = :estado";
            if ($nuevoEstado === 'EN_CAMINO' && $tiempoLlegada > 0) {
                $sql .= ", tiempo_estimado_llegada_min = :tiempo";
            } elseif ($nuevoEstado === 'FINALIZADA' && $precioAcordado > 0) {
                $sql .= ", precio_acordado = :precio, fecha_finalizacion = CURRENT_TIMESTAMP";
            } elseif ($nuevoEstado === 'EN_PROCESO') {
                $sql .= ", fecha_inicio_atencion = CURRENT_TIMESTAMP";
            }
            
            // Seguridad: Solo el dueño de la solicitud puede cambiar su estado
            $sql .= " WHERE id_solicitud = :id_sol AND id_profesional = :id_prof";
            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id_sol', $idSolicitud);
            $stmt->bindParam(':id_prof', $perfil['id_profesional']);
            if ($nuevoEstado === 'EN_CAMINO' && $tiempoLlegada > 0) $stmt->bindParam(':tiempo', $tiempoLlegada);
            if ($nuevoEstado === 'FINALIZADA' && $precioAcordado > 0) $stmt->bindParam(':precio', $precioAcordado);
            
            $stmt->execute();
            echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado a ' . $nuevoEstado]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Fallo al conectar con la base de datos']);
        }
        exit;
    }

    /* ========================================================
       5. MOTOR GPS: TRANSMISOR DEL PROFESIONAL
       ======================================================== */
    public function transmitirGps() {
        $usuario = $this->validarTokenApi();
        $perfil = $this->getPerfilProf($usuario['id_usuario']);

        // Soportamos tanto JSON nativo como FormData para máxima compatibilidad con Flutter
        $data = json_decode(file_get_contents("php://input"), true);
        $lat = $data['lat'] ?? $_POST['lat'] ?? null;
        $lng = $data['lng'] ?? $_POST['lng'] ?? null;

        if ($lat && $lng) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE profesionales SET latitud_actual = :lat, longitud_actual = :lng WHERE id_profesional = :id_prof");
            $stmt->execute([':lat' => $lat, ':lng' => $lng, ':id_prof' => (int) $perfil['id_profesional']]);
            
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Coordenadas faltantes']);
        }
        exit;
    }

    /* ========================================================
       6. COMPRA DE TOKENS (Sube imagen con Multipart/Form-Data)
       ======================================================== */
    public function registrarPago() {
        $usuario = $this->validarTokenApi();
        $perfil = $this->getPerfilProf($usuario['id_usuario']);

        // NOTA PARA FLUTTER: Esta petición no puede ser JSON. 
        // Obligatoriamente debe enviarse como "MultipartRequest" en Flutter porque lleva una imagen física.
        $idPlan = (int) ($_POST['id_plan'] ?? 0);
        $monto = (float) ($_POST['monto'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? 'Código QR Simple';
        $codigoComprobante = trim($_POST['codigo_comprobante'] ?? '');

        if ($idPlan === 0 || empty($codigoComprobante)) {
            echo json_encode(['ok' => false, 'error' => 'Faltan datos obligatorios del formulario']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // 1. Validar que no tenga pagos pendientes
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM transacciones_suscripcion WHERE id_profesional = :id_prof AND estado_pago = 'PENDIENTE'");
        $stmtCheck->execute([':id_prof' => $perfil['id_profesional']]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['ok' => false, 'error' => 'Ya tienes un pago pendiente de verificación.']);
            exit;
        }

        // 2. Ejecutar la subida segura del archivo (El método está heredado de core/Controller)
        $nombreComprobante = null;
        if (isset($_FILES['comprobante_foto']) && $_FILES['comprobante_foto']['size'] > 0) {
            $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . '/GEO_PRO_V2/public/uploads/comprobantes';
            
            $resultadoUpload = $this->subirArchivoSeguro($_FILES['comprobante_foto'], $carpetaDestino);
            
            if ($resultadoUpload['ok']) {
                $nombreComprobante = $resultadoUpload['nombre_archivo'];
            } else {
                echo json_encode(['ok' => false, 'error' => $resultadoUpload['error']]);
                exit;
            }
        } else {
            echo json_encode(['ok' => false, 'error' => 'Debes adjuntar la captura del comprobante QR.']);
            exit;
        }

        // 3. Guardar en Base de Datos
        try {
            $stmt = $db->prepare("
                INSERT INTO transacciones_suscripcion 
                (id_profesional, id_plan, tipo_transaccion, monto, metodo_pago, codigo_comprobante, estado_pago, foto_comprobante) 
                VALUES (:id_prof, :id_plan, 'MEMBRESIA_MENSUAL', :monto, :metodo, :codigo, 'PENDIENTE', :foto)
            ");
            $stmt->execute([
                ':id_prof' => $perfil['id_profesional'],
                ':id_plan' => $idPlan,
                ':monto' => $monto,
                ':metodo' => $metodoPago,
                ':codigo' => $codigoComprobante,
                ':foto' => $nombreComprobante
            ]);
            
            echo json_encode(['ok' => true, 'mensaje' => 'Pago subido con éxito. El Super Admin lo revisará en breve.']);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'error' => 'El código de comprobante ya ha sido utilizado.']);
        }
        exit;
    }
}