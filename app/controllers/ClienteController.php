<?php
class ClienteController extends Controller {
    private Cliente $clienteModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        // Solo CLIENTE (4) o PROFESIONAL (3, puede contratar a otro colega)
        if (!in_array((int) $_SESSION['role_id'], [3, 4], true)) {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $this->clienteModel = new Cliente();
    }

    public function dashboard() {
    $categorias = $this->clienteModel->obtenerCategoriasActivas();
    $cliente = $this->clienteModel->buscarPorUsuario((int) $_SESSION['user_id']);

    if (!$cliente && (int) $_SESSION['role_id'] === 3) {
        $cliente = $this->clienteModel->crearDesdeProfesional((int) $_SESSION['user_id']);
    }

    $stats = null;
    $solicitudActiva = null;
    if ($cliente) {
        $stats = $this->clienteModel->obtenerEstadisticas((int) $cliente['id_cliente']);
        $solicitudActiva = $this->clienteModel->obtenerSolicitudActivaReciente((int) $cliente['id_cliente']);
    }

    $this->view('cliente/dashboard', [
        'titulo'          => 'GEO-PRO | Buscar Servicios',
        'categorias'      => $categorias,
        'cliente'         => $cliente,
        'stats'           => $stats,
        'solicitudActiva' => $solicitudActiva
    ]);
}
}