<?php
class NotificacionController extends Controller {
    private Notificacion $notifModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'No autenticado'], 401);
        }
        $this->notifModel = new Notificacion();
    }

    /** AJAX: polling para el contador de campanita */
    public function contar() {
        $total = $this->notifModel->contarNoLeidas((int) $_SESSION['user_id']);
        $this->jsonResponse(['ok' => true, 'total' => $total]);
    }

    /** AJAX: lista para el dropdown, marca como leídas al abrir */
    public function listar() {
        $notificaciones = $this->notifModel->listarRecientes((int) $_SESSION['user_id']);
        $this->notifModel->marcarTodasLeidas((int) $_SESSION['user_id']);
        $this->jsonResponse(['ok' => true, 'notificaciones' => $notificaciones]);
    }
}