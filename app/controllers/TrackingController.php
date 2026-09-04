<?php
class TrackingController extends Controller {
    private Tracking $trackingModel;

    public function __construct() {
        // Validación JWT/Sesión: Vital para la seguridad que pide el documento
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'No autenticado'], 401);
            exit;
        }
        $this->trackingModel = new Tracking();
    }

    /** AJAX/Flutter: el profesional envía su posición actual (solo mientras EN_CAMINO) */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
            exit;
        }

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $lat = (float) ($_POST['lat'] ?? 0);
        $lng = (float) ($_POST['lng'] ?? 0);
        $vel = (float) ($_POST['velocidad'] ?? 0);

        // Validación de coordenadas (La Paz está aprox en Lat -16, Lng -68)
        if ($lat === 0.0 || $lng === 0.0) {
            $this->jsonResponse(['ok' => false, 'error' => 'Coordenadas inválidas'], 422);
            exit;
        }

        try {
            $this->trackingModel->registrarPosicion($idSolicitud, (int) $_SESSION['user_id'], $lat, $lng, $vel);
            $this->jsonResponse(['ok' => true, 'mensaje' => 'Ubicación actualizada en servidor']);
        } catch (Exception $e) {
            $this->jsonResponse(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /** AJAX/Flutter: el cliente consulta la última posición conocida del profesional */
    public function ultimaPosicion($idSolicitud = null) {
        $idSolicitud = (int) $idSolicitud;

        // Control de acceso: Solo el cliente o el técnico de esta solicitud pueden ver el GPS
        if (!$this->trackingModel->usuarioPerteneceASolicitud($idSolicitud, (int) $_SESSION['user_id'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'No autorizado para ver este trayecto'], 403);
            exit;
        }

        $posicion = $this->trackingModel->obtenerUltimaPosicion($idSolicitud);
        
        if ($posicion) {
            $this->jsonResponse(['ok' => true, 'posicion' => $posicion]);
        } else {
            $this->jsonResponse(['ok' => false, 'error' => 'Aún no hay datos de GPS registrados']);
        }
    }
}