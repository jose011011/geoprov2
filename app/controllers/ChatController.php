<?php
class ChatController extends Controller {
    private Mensaje $mensajeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'No autenticado'], 401);
        }
        $this->mensajeModel = new Mensaje();
    }

    public function ver($idSolicitud = null) {
        $idSolicitud = (int) $idSolicitud;

        if (!$this->mensajeModel->usuarioPerteneceASolicitud($idSolicitud, (int) $_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/cliente/dashboard");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT s.codigo_seguimiento, s.estado_servicio,
                   uc.nombre AS cliente_nombre, uc.apellido AS cliente_apellido,
                   up.nombre AS prof_nombre, up.apellido AS prof_apellido
            FROM solicitudes_servicio s
            INNER JOIN clientes cl ON s.id_cliente = cl.id_cliente
            INNER JOIN usuarios uc ON cl.id_usuario = uc.id_usuario
            INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
            INNER JOIN usuarios up ON p.id_usuario = up.id_usuario
            WHERE s.id_solicitud = :id LIMIT 1
        ");
        $stmt->execute([':id' => $idSolicitud]);
        $solicitud = $stmt->fetch();

        $mensajes = $this->mensajeModel->obtenerTodos($idSolicitud);

        $this->view('chat/conversacion', [
            'titulo'      => 'GEO-PRO | Chat',
            'idSolicitud' => $idSolicitud,
            'solicitud'   => $solicitud,
            'mensajes'    => $mensajes
        ]);
    }

    /** AJAX: enviar mensaje de texto */
    public function enviar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
        }

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $texto = trim($_POST['mensaje'] ?? '');

        if (!$this->mensajeModel->usuarioPerteneceASolicitud($idSolicitud, (int) $_SESSION['user_id'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'No autorizado'], 403);
        }
        if ($texto === '' && empty($_FILES['adjunto']['tmp_name'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'Mensaje vacío'], 422);
        }

        $rutaArchivo = null;
        $tipo = 'TEXTO';

        if (!empty($_FILES['adjunto']['tmp_name']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            try {
                $rutaArchivo = $this->procesarAdjunto($_FILES['adjunto'], $idSolicitud);
                $tipo = 'IMAGEN';
            } catch (Exception $e) {
                $this->jsonResponse(['ok' => false, 'error' => $e->getMessage()], 422);
            }
        }

        $idMensaje = $this->mensajeModel->enviar($idSolicitud, (int) $_SESSION['user_id'], $texto, $rutaArchivo, $tipo);
        $this->jsonResponse(['ok' => true, 'id_mensaje' => $idMensaje]);
    }

    /** AJAX: polling de mensajes nuevos */
    public function nuevos($idSolicitud = null) {
        $idSolicitud = (int) $idSolicitud;
        $ultimoId = (int) ($_GET['ultimo_id'] ?? 0);

        if (!$this->mensajeModel->usuarioPerteneceASolicitud($idSolicitud, (int) $_SESSION['user_id'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'No autorizado'], 403);
        }

        $mensajes = $this->mensajeModel->obtenerDesde($idSolicitud, $ultimoId);
        $this->jsonResponse(['ok' => true, 'mensajes' => $mensajes, 'mi_id' => (int) $_SESSION['user_id']]);
    }

    private function procesarAdjunto(array $archivo, int $idSolicitud): string {
        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("La imagen supera el límite de 5 MB.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeReal, $mimesPermitidos, true)) {
            throw new Exception("Solo se permiten imágenes JPG, PNG o WEBP.");
        }

        $carpeta = '../public/uploads/chat/';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $ext = ($mimeReal === 'image/png') ? 'png' : (($mimeReal === 'image/webp') ? 'webp' : 'jpg');
        $nombreSeguro = 'CHAT_' . $idSolicitud . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombreSeguro)) {
            throw new Exception("Error al guardar la imagen.");
        }

        return 'uploads/chat/' . $nombreSeguro;
    }
}