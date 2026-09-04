<?php
class SiteController extends Controller {
    public function index() {
        // Si ya está logueado, lo mandamos directo a su panel
        if (isset($_SESSION['user_id'])) {
            $rol = (int) $_SESSION['role_id'];
            $destino = in_array($rol, [1, 2], true) ? 'admin/dashboard'
                     : ($rol === 3 ? 'profesional/dashboard' : 'cliente/dashboard');
            header("Location: " . BASE_URL . "/" . $destino);
            exit;
        }
        $this->view('site/landing', ['titulo' => 'GEO-PRO | Servicios Técnicos con Geolocalización en La Paz']);
    }
}