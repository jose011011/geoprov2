<?php
require_once "../app/models/Usuario.php";

class MembresiaController extends Controller {
    private Membresia $membresiaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || (int) $_SESSION['role_id'] !== 3) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->membresiaModel = new Membresia();
    }

    public function planes() {
        $profModel = new Profesional();
        $perfil = $profModel->buscarPorUsuario((int) $_SESSION['user_id']);
        $planes = $this->membresiaModel->obtenerPlanesActivos();

        $this->view('profesional/planes', [
            'titulo' => 'GEO-PRO | Planes de Membresía',
            'planes' => $planes,
            'perfil' => $perfil
        ]);
    }

    public function comprobantePlan($idPlan = null) {
        $profModel = new Profesional();
        $perfil = $profModel->buscarPorUsuario((int) $_SESSION['user_id']);
        $plan = $this->membresiaModel->obtenerPlanPorId((int) $idPlan);

        if (!$plan) {
            header("Location: " . BASE_URL . "/membresia/planes");
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo_comprobante'] ?? '');
            if (strlen($codigo) < 4) {
                $error = "Ingrese un código de comprobante válido (mínimo 4 caracteres).";
            } else {
                try {
                    $this->membresiaModel->solicitarCambioPlan((int) $perfil['id_profesional'], (int) $idPlan, (float) $plan['precio_mensual'], $codigo);
                    header("Location: " . BASE_URL . "/profesional/dashboard?pago_enviado=1");
                    exit;
                } catch (Exception $e) {
                    $error = "Ese código de comprobante ya fue registrado.";
                }
            }
        }

        $this->view('profesional/comprobante_pago', [
            'titulo' => 'GEO-PRO | Confirmar Pago',
            'plan'   => $plan,
            'error'  => $error,
            'tipo'   => 'plan'
        ]);
    }

    public function comprobanteTokens() {
        $profModel = new Profesional();
        $perfil = $profModel->buscarPorUsuario((int) $_SESSION['user_id']);

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo_comprobante'] ?? '');
            if (strlen($codigo) < 4) {
                $error = "Ingrese un código de comprobante válido.";
            } else {
                try {
                    $this->membresiaModel->solicitarPaqueteTokens((int) $perfil['id_profesional'], 10.00, $codigo);
                    header("Location: " . BASE_URL . "/profesional/dashboard?pago_enviado=1");
                    exit;
                } catch (Exception $e) {
                    $error = "Ese código de comprobante ya fue registrado.";
                }
            }
        }

        $this->view('profesional/comprobante_pago', [
            'titulo' => 'GEO-PRO | Comprar Tokens',
            'plan'   => ['nombre_plan' => 'Paquete de 10 Tokens', 'precio_mensual' => 10.00],
            'error'  => $error,
            'tipo'   => 'tokens'
        ]);
    }
}