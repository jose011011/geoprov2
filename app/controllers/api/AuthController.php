<?php
require_once "../app/models/Usuario.php";

class AuthController extends Controller {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        
        // Cabeceras obligatorias para API REST (CORS y JSON)
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Responder OK automático a la petición pre-flight de Flutter
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /* ========================================================
       ENDPOINT: POST /api/auth/login
       ======================================================== */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            exit;
        }

        // Leer el JSON que envía Flutter
        $data = json_decode(file_get_contents("php://input"), true);
        
        $credencial = trim($data['correo_celular'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($credencial) || empty($password)) {
            echo json_encode(['ok' => false, 'error' => 'Faltan credenciales']);
            exit;
        }

        // Reutilizamos la lógica de autenticación de tu modelo actual
        $usuario = $this->usuarioModel->autenticar($credencial, $password);

        if ($usuario) {
            if ($usuario['estado'] !== 'ACTIVO') {
                echo json_encode(['ok' => false, 'error' => 'Esta cuenta está bloqueada o inactiva']);
                exit;
            }

            // Generar un token simple para identificar a la app en futuras peticiones
            $token = bin2hex(random_bytes(16)); 

            echo json_encode([
                'ok' => true,
                'token' => $token,
                'usuario' => [
                    'id_usuario' => (int) $usuario['id_usuario'],
                    'nombre' => $usuario['nombre'],
                    'apellido' => $usuario['apellido'],
                    'correo' => $usuario['correo'],
                    'celular' => $usuario['celular'],
                    'rol' => (int) $usuario['role_id']
                ]
            ]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos']);
        }
        exit;
    }

    /* ========================================================
       ENDPOINT: POST /api/auth/registro
       ======================================================== */
    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');
        $celular = trim($data['celular'] ?? '');
        $password = trim($data['password'] ?? '');
        $rol = (int) ($data['role_id'] ?? 4); // 4 = Cliente por defecto, 3 = Profesional

        if (empty($nombre) || empty($correo) || empty($password)) {
            echo json_encode(['ok' => false, 'error' => 'Faltan campos obligatorios']);
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            
            // 1. Validar que no exista el correo o celular
            $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo OR celular = :celular");
            $stmtCheck->execute([':correo' => $correo, ':celular' => $celular]);
            
            if ($stmtCheck->fetch()) {
                echo json_encode(['ok' => false, 'error' => 'El correo o celular ya están registrados']);
                exit;
            }

            $db->beginTransaction();

            // 2. Crear el Usuario
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("
                INSERT INTO usuarios (role_id, nombre, apellido, correo, celular, password, estado) 
                VALUES (:rol, :nombre, :apellido, :correo, :celular, :pass, 'ACTIVO')
            ");
            $stmt->execute([
                ':rol' => $rol,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':correo' => $correo,
                ':celular' => $celular,
                ':pass' => $hash
            ]);

            $idNuevoUsuario = $db->lastInsertId();

            // 3. Crear el Perfil Automático según el Rol
            if ($rol === 3) {
                // Profesional: Cae directo al Plan 1 (Gratis)
                $stmtProf = $db->prepare("INSERT INTO profesionales (id_usuario, id_categoria, id_plan) VALUES (:id, 1, 1)");
                $stmtProf->execute([':id' => $idNuevoUsuario]);
            } else if ($rol === 4) {
                // Cliente
                $stmtCli = $db->prepare("INSERT INTO clientes (id_usuario) VALUES (:id)");
                $stmtCli->execute([':id' => $idNuevoUsuario]);
            }

            $db->commit();
            
            echo json_encode([
                'ok' => true, 
                'mensaje' => 'Cuenta creada exitosamente', 
                'id_usuario' => $idNuevoUsuario
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'error' => 'Error interno en el servidor: ' . $e->getMessage()]);
        }
        exit;
    }
}