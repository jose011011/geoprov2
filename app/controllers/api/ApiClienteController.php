<?php
class ApiClienteController extends Controller {

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
       FUNCIÓN DE APOYO: OBTENER ID DEL CLIENTE
       ======================================================== */
    private function getIdCliente($idUsuario) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id");
        $stmt->execute([':id' => $idUsuario]);
        $idCliente = $stmt->fetchColumn();

        // Si es un Profesional pidiendo un servicio y no está en la tabla clientes, lo insertamos
        if (!$idCliente) {
            $stmtIns = $db->prepare("INSERT INTO clientes (id_usuario) VALUES (:id)");
            $stmtIns->execute([':id' => $idUsuario]);
            return $db->lastInsertId();
        }
        return $idCliente;
    }

    /* ========================================================
       1. OBTENER CATEGORÍAS DISPONIBLES
       ======================================================== */
    public function categorias() {
        $this->validarTokenApi(); // Solo usuarios logueados en la app
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT id_categoria, nombre_categoria, icono_fa FROM categorias WHERE estado = 1 ORDER BY nombre_categoria ASC");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['ok' => true, 'data' => $categorias]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Error de BD']);
        }
        exit;
    }

    /* ========================================================
       2. EL CATÁLOGO: BUSCAR ESPECIALISTAS (MODELO DIRECTORIO)
       ======================================================== */
    public function especialistas() {
        $this->validarTokenApi();
        
        $idCategoria = (int) ($_GET['id_categoria'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $params = [];
        
        $sql = "
            SELECT p.id_profesional, u.nombre, u.apellido, c.nombre_categoria,
                   pl.nombre_plan, pl.posicionamiento_destacado,
                   p.tarifa_base, p.macrodistrito_base, p.zona_especifica,
                   COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas,
                   COALESCE(vw.total_resenas, 0) AS total_resenas
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
            LEFT JOIN vw_metricas_profesionales vw ON p.id_profesional = vw.id_profesional
            WHERE p.estado_validacion = 'APROBADO' 
              AND p.estado_disponibilidad = 'DISPONIBLE'
              AND p.tokens_disponibles > 0
        ";

        if ($idCategoria > 0) {
            $sql .= " AND p.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        // ORDEN CRÍTICO: Los que pagan (Premium) salen primero en la app
        $sql .= " ORDER BY pl.posicionamiento_destacado DESC, pl.id_plan DESC, promedio_estrellas DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $especialistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'data' => $especialistas]);
        exit;
    }

    /* ========================================================
       3. CREAR SOLICITUD (I.A. CASCADA O MANUAL)
       ======================================================== */
    public function crearSolicitud() {
        $usuario = $this->validarTokenApi();
        $idCliente = $this->getIdCliente($usuario['id_usuario']);

        $data = json_decode(file_get_contents("php://input"), true);
        
        $tipoAsignacion = $data['tipo_asignacion'] ?? 'AUTO'; // 'AUTO' o 'MANUAL'
        $idCategoria    = (int) ($data['id_categoria'] ?? 0);
        $idProfesional  = (int) ($data['id_profesional'] ?? 0);
        
        $descripcion    = trim($data['descripcion_problema'] ?? '');
        $direccion      = trim($data['direccion_servicio'] ?? '');
        $macrodistrito  = trim($data['macrodistrito'] ?? 'CENTRO');
        $zona           = trim($data['zona'] ?? 'La Paz');
        $lat            = (float) ($data['lat'] ?? -16.5000);
        $lng            = (float) ($data['lng'] ?? -68.1500);

        if ($idCategoria === 0 || empty($descripcion) || empty($direccion)) {
            echo json_encode(['ok' => false, 'error' => 'Faltan datos obligatorios para crear la solicitud.']);
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $codigoSeguimiento = 'GEO-' . strtoupper(substr(md5(uniqid()), 0, 8));
            
            // Si es AUTO, el profesional queda NULL para que caiga en la Piscina I.A. (Cascada)
            // Si es MANUAL, se asigna directamente al ID que eligió el cliente
            $idProfInsert = ($tipoAsignacion === 'MANUAL' && $idProfesional > 0) ? $idProfesional : null;

            $stmt = $db->prepare("
                INSERT INTO solicitudes_servicio 
                (codigo_seguimiento, id_cliente, id_categoria, id_profesional, descripcion_problema, direccion_servicio, macrodistrito, zona, latitud_destino, longitud_destino, estado_servicio) 
                VALUES (:codigo, :idc, :idcat, :idp, :desc, :dir, :macro, :zona, :lat, :lng, 'PENDIENTE')
            ");
            
            $stmt->execute([
                ':codigo' => $codigoSeguimiento,
                ':idc'    => $idCliente,
                ':idcat'  => $idCategoria,
                ':idp'    => $idProfInsert,
                ':desc'   => $descripcion,
                ':dir'    => $direccion,
                ':macro'  => $macrodistrito,
                ':zona'   => $zona,
                ':lat'    => $lat,
                ':lng'    => $lng
            ]);

            echo json_encode([
                'ok' => true, 
                'mensaje' => 'Solicitud enviada con éxito',
                'id_solicitud' => $db->lastInsertId(),
                'codigo' => $codigoSeguimiento
            ]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Fallo al procesar la solicitud en BD']);
        }
        exit;
    }

    /* ========================================================
       4. MOTOR GPS: RECEPTOR PARA FLUTTER
       ======================================================== */
    public function rastrearProfesional() {
        $this->validarTokenApi();
        
        $idSolicitud = (int) ($_GET['id_solicitud'] ?? 0);

        if ($idSolicitud <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID de solicitud requerido']);
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT p.latitud_actual, p.longitud_actual, s.estado_servicio 
                FROM solicitudes_servicio s
                INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
                WHERE s.id_solicitud = :id_sol
            ");
            $stmt->execute([':id_sol' => $idSolicitud]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($datos) {
                echo json_encode([
                    'ok' => true, 
                    'lat' => (float) $datos['latitud_actual'], 
                    'lng' => (float) $datos['longitud_actual'],
                    'estado' => $datos['estado_servicio']
                ]);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada o sin profesional asignado']);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Error de BD']);
        }
        exit;
    }

    /* ========================================================
       5. FINALIZAR Y CALIFICAR SERVICIO
       ======================================================== */
    public function calificar() {
        $usuario = $this->validarTokenApi();
        $idCliente = $this->getIdCliente($usuario['id_usuario']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        $idSolicitud = (int) ($data['id_solicitud'] ?? 0);
        $estrellas   = (int) ($data['estrellas'] ?? 5);
        $comentario  = trim($data['comentario'] ?? '');

        if ($idSolicitud <= 0 || $estrellas < 1 || $estrellas > 5) {
            echo json_encode(['ok' => false, 'error' => 'Datos de calificación inválidos']);
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificamos que la solicitud es del cliente y está en un estado que permita calificar
            $stmt = $db->prepare("
                UPDATE solicitudes_servicio 
                SET calificacion_estrellas = :estrellas, 
                    comentario_cliente = :comentario 
                WHERE id_solicitud = :id_sol AND id_cliente = :id_cli
            ");
            
            $stmt->execute([
                ':estrellas'  => $estrellas,
                ':comentario' => $comentario,
                ':id_sol'     => $idSolicitud,
                ':id_cli'     => $idCliente
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'mensaje' => 'Calificación enviada. ¡Gracias por tu opinión!']);
            } else {
                echo json_encode(['ok' => false, 'error' => 'No se pudo aplicar la calificación.']);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Fallo al procesar la calificación']);
        }
        exit;
    }
}