<?php
require_once "../app/models/Usuario.php";

class ApiAuthController extends Controller {
    private Usuario $usuarioModel;

    public function __construct() {
        // Inicializamos el modelo de usuario (asegúrate de que tu modelo exista en app/models/Usuario.php)
        $this->usuarioModel = new Usuario();
    }

    /* ========================================================
       1. LOGIN DESDE LA APLICACIÓN MÓVIL
       ======================================================== */
      public function login() {
        header('Content-Type: application/json; charset=UTF-8');

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $credencial = trim($data['correo_celular'] ?? $data['correo'] ?? '');
        $password   = $data['password'] ?? '';

        if ($credencial === '' || $password === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Por favor, llena todos los campos.']);
            exit;
        }

        $usuario = $this->usuarioModel->login($credencial, $password);

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos.']);
            exit;
        }

        $token = bin2hex(random_bytes(32));

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE usuarios SET api_token = :token WHERE id_usuario = :id");
        $stmt->execute([':token' => $token, ':id' => $usuario['id_usuario']]);

        echo json_encode([
            'ok'    => true,
            'token' => $token,
            'usuario' => [
                'id_usuario' => (int) $usuario['id_usuario'],
                'id_rol'     => (int) ($usuario['id_rol'] ?? $usuario['role_id'] ?? 0),
                'nombre'     => $usuario['nombre']   ?? '',
                'apellido'   => $usuario['apellido'] ?? '',
                'correo'     => $usuario['correo']   ?? '',
                'celular'    => $usuario['celular']  ?? '',
            ]
        ]);
        exit;
    }
    /* ========================================================
       2. CERRAR SESIÓN (LOGOUT)
       ======================================================== */
    public function logout() {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $db = Database::getInstance()->getConnection();
            $token = ApiAuth::tokenActual();

            if ($token !== '') {
                $stmt = $db->prepare('SELECT id_usuario FROM usuarios WHERE api_token = :t LIMIT 1');
                $stmt->execute([':t' => $token]);
                $idUsuario = (int) $stmt->fetchColumn();

                if ($idUsuario > 0) {
                    // Un profesional que sale de la app deja de recibir solicitudes
                    $db->prepare("
                        UPDATE profesionales SET estado_disponibilidad = 'NO_DISPONIBLE'
                         WHERE id_usuario = :u AND estado_disponibilidad = 'DISPONIBLE'
                    ")->execute([':u' => $idUsuario]);

                    // Invalida el token en el servidor
                    $db->prepare('UPDATE usuarios SET api_token = NULL WHERE id_usuario = :u')
                       ->execute([':u' => $idUsuario]);
                }
            }
        } catch (Throwable $e) {
            error_log('[ApiAuth] logout: ' . $e->getMessage());
        }

        echo json_encode(['ok' => true, 'mensaje' => 'Sesión cerrada exitosamente']);
        exit;
    }
    

}