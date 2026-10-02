<?php
require_once "../app/models/Usuario.php";
require_once "../app/models/Solicitud.php";

class ApiUsuarioController extends Controller {

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
       1. MI PERFIL (Datos Completos Inteligentes)
       ======================================================== */
    public function perfil() {
        $usuario = $this->validarTokenApi();
        $db = Database::getInstance()->getConnection();

        // Base de datos general del usuario
        $datosPerfil = $usuario;

        // Si es Profesional, extraemos sus estadísticas y tokens
        if ($usuario['role_id'] == 3) {
            $stmt = $db->prepare("
                SELECT p.*, c.nombre_categoria, pl.nombre_plan 
                FROM profesionales p
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                LEFT JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
                WHERE p.id_usuario = :id
            ");
            $stmt->execute([':id' => $usuario['id_usuario']]);
            $datosPerfil['profesional'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } 
        // Si es Cliente, extraemos su zona
        elseif ($usuario['role_id'] == 4) {
            $stmt = $db->prepare("SELECT * FROM clientes WHERE id_usuario = :id");
            $stmt->execute([':id' => $usuario['id_usuario']]);
            $datosPerfil['cliente'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode(['ok' => true, 'data' => $datosPerfil]);
        exit;
    }

    /* ========================================================
       2. HISTORIAL DE TRABAJOS (Para Cliente y Profesional)
       ======================================================== */
    public function historial() {
        $usuario = $this->validarTokenApi();
        $db = Database::getInstance()->getConnection();
        
        $solicitudes = [];

        // Historial si el que consulta es el PROFESIONAL
        if ($usuario['role_id'] == 3) {
            require_once "../app/models/Solicitud.php";
            $solicitudModel = new Solicitud();
            
            $stmt = $db->prepare("SELECT id_profesional FROM profesionales WHERE id_usuario = :id");
            $stmt->execute([':id' => $usuario['id_usuario']]);
            $idProf = $stmt->fetchColumn();
            
            if ($idProf) {
                // Traemos todos los trabajos sin importar el estado
                $solicitudes = $solicitudModel->listarPorProfesional($idProf, null); 
            }
        } 
        // Historial si el que consulta es el CLIENTE
        elseif ($usuario['role_id'] == 4) {
            $stmt = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id");
            $stmt->execute([':id' => $usuario['id_usuario']]);
            $idCli = $stmt->fetchColumn();
            
            if ($idCli) {
                $sql = "
                    SELECT s.*, u.nombre AS prof_nombre, u.apellido AS prof_apellido, c.nombre_categoria 
                    FROM solicitudes_servicio s
                    LEFT JOIN profesionales p ON s.id_profesional = p.id_profesional
                    LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                    LEFT JOIN categorias c ON s.id_categoria = c.id_categoria
                    WHERE s.id_cliente = :id_cli
                    ORDER BY s.fecha_solicitud DESC
                ";
                $stmtSol = $db->prepare($sql);
                $stmtSol->execute([':id_cli' => $idCli]);
                $solicitudes = $stmtSol->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        echo json_encode(['ok' => true, 'data' => $solicitudes]);
        exit;
    }

    /* ========================================================
       3. LISTADO DE PLANES (Pantalla de Monetización)
       ======================================================== */
    public function planes() {
        $this->validarTokenApi();
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM planes_suscripcion ORDER BY id_plan ASC");
        $planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'data' => $planes]);
        exit;
    }
}