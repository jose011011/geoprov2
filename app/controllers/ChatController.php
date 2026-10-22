<?php
class ChatController extends Controller
{
    private ChatPanel $panel;

    public function __construct()
    {
        $this->requiereSesion([3, 4]);
        $this->panel = new ChatPanel();
    }

    /** Pantalla del chat. */
    public function conversacion($idSolicitud = 0)
    {
        $idSolicitud = (int) $idSolicitud;
        $idUsuario   = (int) $_SESSION['user_id'];
        $chat        = $this->panel->conversacion($idSolicitud, $idUsuario);

        if (!$chat) {
            Flash::set('danger', 'No tienes acceso a ese chat.');
            $this->redirigir($this->inicioPorRol());
        }

        $this->panel->marcarLeidos($idSolicitud, $idUsuario);
        $this->view('chat/conversacion', [
            'titulo'   => 'FixGeo | Chat',
            'chat'     => $chat,
            'mensajes' => $this->panel->mensajes($idSolicitud, 0),
            'yo'       => $idUsuario,
        ]);
    }

    /** AJAX: mensajes nuevos desde un id. */
    public function mensajes($idSolicitud = 0)
    {
        $idSolicitud = (int) $idSolicitud;
        $idUsuario   = (int) $_SESSION['user_id'];
        $chat        = $this->panel->conversacion($idSolicitud, $idUsuario);
        if (!$chat) {
            $this->jsonResponse(['ok' => false, 'error' => 'Sin acceso'], 403);
        }

        $this->panel->marcarLeidos($idSolicitud, $idUsuario);
        $this->jsonResponse([
            'ok'       => true,
            'abierto'  => $chat['abierto'],
            'mensajes' => $this->panel->mensajes($idSolicitud, max(0, (int) ($_GET['desde'] ?? 0))),
        ]);
    }

    /** AJAX: enviar texto y/o una imagen. */
    public function enviar($idSolicitud = 0)
    {
        if (!$this->esPost()) {
            $this->jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
        }

        $idSolicitud = (int) $idSolicitud;
        $idUsuario   = (int) $_SESSION['user_id'];
        $chat        = $this->panel->conversacion($idSolicitud, $idUsuario);

        if (!$chat) {
            $this->jsonResponse(['ok' => false, 'error' => 'Sin acceso'], 403);
        }
        if (!$chat['abierto']) {
            $this->jsonResponse(['ok' => false, 'error' => 'Este servicio ya terminó: el chat quedó solo para lectura.'], 409);
        }

        $clave = 'chat|' . $idUsuario;
        if (Security::bloqueado($clave, 40, 60)) {
            $this->jsonResponse(['ok' => false, 'error' => 'Estás enviando muy rápido. Espera unos segundos.'], 429);
        }
        Security::registrarFallo($clave, 60);

        $texto   = mb_substr(trim((string) ($_POST['mensaje'] ?? '')), 0, 1000);
        $archivo = null;

        if (!empty($_FILES['imagen']['name'])) {
            try {
                $archivo = Security::guardarImagen($_FILES['imagen'], __DIR__ . '/../../public/uploads/chat', 'CHAT_' . $idSolicitud, 3);
            } catch (RuntimeException $e) {
                $this->jsonResponse(['ok' => false, 'error' => $e->getMessage()], 422);
            }
        }
        if ($texto === '' && !$archivo) {
            $this->jsonResponse(['ok' => false, 'error' => 'Escribe un mensaje o adjunta una imagen.'], 422);
        }

        try {
            $avisar = !$this->panel->tengoSinLeer($idSolicitud, $idUsuario); // solo el primer mensaje sin leer genera aviso
            $this->panel->enviar($idSolicitud, $idUsuario, $texto, $archivo);

            if ($avisar) {
                (new Notificacion())->crear(
                    (int) $chat['id_otro'], 'MENSAJE_CHAT',
                    'Tienes un mensaje nuevo en tu solicitud ' . $chat['codigo_seguimiento'] . '.',
                    BASE_URL . '/chat/conversacion/' . $idSolicitud
                );
            }
            $this->jsonResponse(['ok' => true]);
        } catch (Throwable $e) {
            error_log('[FixGeo] chat/enviar: ' . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => 'No se pudo enviar el mensaje.'], 500);
        }
    }
}