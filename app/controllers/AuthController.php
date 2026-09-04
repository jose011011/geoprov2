<?php
class AuthController extends Controller {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function index() {
        $this->login();
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redireccionarPorRol($_SESSION['role_id']);
            return;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo   = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($correo) || empty($password)) {
                $error = "Por favor complete todos los campos obligatorios.";
            } else {
                $user = $this->usuarioModel->buscarPorCorreo($correo);
                if ($user && password_verify($password, $user['password'])) {
                    if ($user['estado'] !== 'ACTIVO') {
                        $error = "Su cuenta está inactiva o bloqueada. Contacte al soporte administrativo.";
                    } else {
                        $_SESSION['user_id']     = $user['id_usuario'];
                        $_SESSION['user_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
                        $_SESSION['user_correo'] = $user['correo'];
                        $_SESSION['role_id']     = (int) $user['id_rol'];

                        $this->usuarioModel->auditar($user['id_usuario'], 'LOGIN_SUCCESS', 'usuarios', $user['id_usuario']);
                        $this->redireccionarPorRol((int) $user['id_rol']);
                        return;
                    }
                } else {
                    $error = "Credenciales incorrectas. Verifique su correo o contraseña.";
                    $this->usuarioModel->auditar(null, 'LOGIN_FAILED', 'usuarios', null, ['correo_intentado' => $correo]);
                }
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function registroCliente() {
        $error = null;
         $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->usuarioModel->registrarCliente($_POST);
                header("Location: " . BASE_URL . "/auth/login?registered=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
                $old = $_POST; // conservamos lo que el usuario ya escribió
            }
        }
          $this->view('auth/registro_cliente', ['error' => $error, 'old' => $old]);
    }

    public function registroEmpirico() {
        $this->gestionarRegistroPrestador('OFICIO_EMPIRICO', 'auth/registro_empirico');
    }

    public function registroProfesional() {
        $this->gestionarRegistroPrestador('TECNICO_PROFESIONAL', 'auth/registro_profesional');
    }
private function gestionarRegistroPrestador(string $tipo, string $vista) {
    $error = null;
    $old = [];
    $db = Database::getInstance()->getConnection();

    $tipoFiltro = ($tipo === 'TECNICO_PROFESIONAL') ? 'TECNICO' : 'EMPIRICO_OFICIO';
    $stmt = $db->prepare("SELECT id_categoria, nombre_categoria, tipo_clasificacion FROM categorias WHERE estado = 1 AND (tipo_clasificacion = :tipo OR tipo_clasificacion = 'AMBOS')");
    $stmt->execute([':tipo' => $tipoFiltro]);
    $categorias = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $_POST['tipo_prestador'] = $tipo;
            $this->usuarioModel->registrarPrestador($_POST, $_FILES);
            header("Location: " . BASE_URL . "/auth/login?pending_validation=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $old = $_POST; // conservamos lo escrito (los archivos NO se pueden repoblar por seguridad del navegador)
        }
    }

    $this->view($vista, ['categorias' => $categorias, 'error' => $error, 'old' => $old]);
}

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->usuarioModel->auditar($_SESSION['user_id'], 'LOGOUT', 'usuarios', $_SESSION['user_id']);
        }
        session_destroy();
        header("Location: " . BASE_URL . "/auth/login");
        exit;
    }

    private function redireccionarPorRol(int $roleId): void {
        switch ($roleId) {
            case 1:
            case 2:
                header("Location: " . BASE_URL . "/admin/dashboard");
                break;
            case 3:
                header("Location: " . BASE_URL . "/profesional/dashboard");
                break;
            case 4:
            default:
                header("Location: " . BASE_URL . "/cliente/dashboard");
                break;
        }
        exit;
    }
}