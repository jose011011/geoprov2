<?php
require_once "../app/models/Usuario.php";

class AuthController extends Controller {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        
        // Cabeceras obligatorias para API REST (CORS)
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

        try {
            // Buscar usuario por correo o celular
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = :credencial OR celular = :credencial LIMIT 1");
            $stmt->execute([':credencial' => $credencial]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                if ($usuario['estado'] !== 'ACTIVO') {
                    echo json_encode(['ok' => false, 'error' => 'Esta cuenta está bloqueada o inactiva']);
                    exit;
                }

                // 1. Generar un Token Único Seguro (Bearer Token)
                $token = bin2hex(random_bytes(32)); 

                // 2. Guardar el Token en la Base de Datos
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE usuarios SET api_token = :token WHERE id_usuario = :id");
                $stmt->execute([':token' => $token, ':id' => $usuario['id_usuario']]);

                // 3. Responder a Flutter con los datos
                echo json_encode([
                    'ok' => true,
                    'token' => $token, // Flutter debe guardar esto en SharedPreferences o SecureStorage
                    'usuario' => [
                        'id_usuario' => (int) $usuario['id_usuario'],
                        'nombre' => $usuario['nombre'],
                        'apellido' => $usuario['apellido'],
                        'correo' => $usuario['correo'],
                        'celular' => $usuario['celular'],
                        'rol' => (int) $usuario['id_rol']
                    ]
                ]);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos']);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]);
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
        if (!$data) {
            $data = $_POST;
        }
        
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');
        $celular = trim($data['celular'] ?? '');
        $password = trim($data['password'] ?? '');
        $rol = (int) ($data['id_rol'] ?? $data['role_id'] ?? 4); // 4 = Cliente por defecto, 3 = Profesional
        
        if (empty($nombre) || empty($correo) || empty($password)) {
            echo json_encode(['ok' => false, 'error' => 'Faltan campos obligatorios']);
            exit;
        }

        try {
            if ($rol === 3) {
                // El profesional requiere validación compleja, subida de documentos y tablas anexas
                $data['apellido_paterno'] = $apellido; 
                $this->usuarioModel->registrarPrestador($data, $_FILES);
                
                echo json_encode([
                    'ok' => true, 
                    'mensaje' => 'Cuenta de profesional creada exitosamente. Espera la validación del administrador.'
                ]);
                exit;
            } else {
                // Cliente
                $db = Database::getInstance()->getConnection();
                
                $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo OR celular = :celular");
                $stmtCheck->execute([':correo' => $correo, ':celular' => $celular]);
                
                if ($stmtCheck->fetch()) {
                    echo json_encode(['ok' => false, 'error' => 'El correo o celular ya están registrados']);
                    exit;
                }

                $db->beginTransaction();

                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("
                    INSERT INTO usuarios (id_rol, nombre, apellido, correo, celular, password, estado) 
                    VALUES (4, :nombre, :apellido, :correo, :celular, :pass, 'ACTIVO')
                ");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':correo' => $correo,
                    ':celular' => $celular,
                    ':pass' => $hash
                ]);

                $idNuevoUsuario = $db->lastInsertId();

                $stmtCli = $db->prepare("INSERT INTO clientes (id_usuario) VALUES (:id)");
                $stmtCli->execute([':id' => $idNuevoUsuario]);

                $db->commit();
                
                echo json_encode([
                    'ok' => true, 
                    'mensaje' => 'Cuenta creada exitosamente. Ya puedes iniciar sesión.'
                ]);
            }

        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ========================================================
       ENDPOINT: POST /api/auth/logout
       ======================================================== */
    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false]);
            exit;
        }

        // Leer el token de los headers de Flutter (Authorization: Bearer <token>)
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);

        if (!empty($token)) {
            $db = Database::getInstance()->getConnection();
            // Borramos el token de la base de datos para invalidar la sesión
            $stmt = $db->prepare("UPDATE usuarios SET api_token = NULL WHERE api_token = :token");
            $stmt->execute([':token' => $token]);
        }

        echo json_encode(['ok' => true, 'mensaje' => 'Sesión cerrada correctamente']);
        exit;
    }
}