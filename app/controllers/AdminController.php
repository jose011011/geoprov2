<?php
require_once "../app/models/Usuario.php";

class AdminController extends Controller {
    private Admin $adminModel;
    private Usuario $usuarioModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        if (!in_array((int) $_SESSION['role_id'], [1, 2], true)) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->adminModel = new Admin();
        $this->usuarioModel = new Usuario();
    }

    public function dashboard() {
        $stats = $this->adminModel->estadisticas();
        $filtro = $_GET['estado'] ?? 'PENDIENTE';
        $profesionales = $this->adminModel->listarProfesionales($filtro);

        $this->view('admin/dashboard', [
            'titulo'        => 'GEO-PRO | Panel Administrativo',
            'stats'         => $stats,
            'profesionales' => $profesionales,
            'filtroActual'  => $filtro
        ]);
    }

    public function verProfesional($id = null) {
        $idProfesional = (int) $id;
        $perfil = $this->adminModel->obtenerProfesionalDetalle($idProfesional);

        if (!$perfil) {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $documentos = $this->adminModel->obtenerDocumentos($idProfesional);

        $this->view('admin/ver_profesional', [
            'titulo'     => 'GEO-PRO | Revisión de ' . $perfil['nombre'],
            'perfil'     => $perfil,
            'documentos' => $documentos
        ]);
    }

    public function revisarDocumento() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $idDocumento   = (int) ($_POST['id_documento'] ?? 0);
        $idProfesional = (int) ($_POST['id_profesional'] ?? 0);
        $decision      = $_POST['decision'] ?? '';
        $observacion   = trim($_POST['observacion'] ?? '');

        try {
            $this->adminModel->revisarDocumento($idDocumento, $decision, (int) $_SESSION['user_id'], $observacion ?: null);
            $this->usuarioModel->auditar(
                (int) $_SESSION['user_id'],
                'REVISION_DOCUMENTO_' . $decision,
                'documentos_profesional',
                $idDocumento,
                ['id_profesional' => $idProfesional]
            );
        } catch (Exception $e) {
            // En un sprint futuro: pasar mensaje de error a la vista vía sesión flash
        }

        header("Location: " . BASE_URL . "/admin/verProfesional/" . $idProfesional);
        exit;
    }

    public function aprobarProfesional() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }
        $idProfesional = (int) ($_POST['id_profesional'] ?? 0);

        try {
            $this->adminModel->aprobarProfesional($idProfesional);
            $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'APROBAR_PROFESIONAL', 'profesionales', $idProfesional);
        } catch (Exception $e) {
            // idem: mostrar $e->getMessage() en la vista con flash message
        }

        header("Location: " . BASE_URL . "/admin/verProfesional/" . $idProfesional);
        exit;
    }

    public function rechazarProfesional() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }
        $idProfesional = (int) ($_POST['id_profesional'] ?? 0);

        $this->adminModel->rechazarProfesional($idProfesional);
        $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'RECHAZAR_PROFESIONAL', 'profesionales', $idProfesional);

        header("Location: " . BASE_URL . "/admin/verProfesional/" . $idProfesional);
        exit;
    }






    public function pagos() {
    require_once "../app/models/Membresia.php";
    $membresiaModel = new Membresia();
    $this->view('admin/pagos', [
        'titulo' => 'GEO-PRO | Pagos Pendientes',
        'pagos'  => $membresiaModel->listarPendientes()
    ]);
}

public function confirmarPago() {
    require_once "../app/models/Membresia.php";
    $membresiaModel = new Membresia();
    $id = (int) ($_POST['id_transaccion'] ?? 0);
    try {
        $membresiaModel->confirmarTransaccion($id);
        $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'CONFIRMAR_PAGO', 'transacciones_suscripcion', $id);
        require_once "../app/models/Notificacion.php";
$notifModel = new Notificacion();
$db = Database::getInstance()->getConnection();
$stmtProf = $db->prepare("
    SELECT u.id_usuario FROM transacciones_suscripcion t
    INNER JOIN profesionales p ON t.id_profesional = p.id_profesional
    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
    WHERE t.id_transaccion = :id
");
$stmtProf->execute([':id' => $id]);
$idUsuarioProf = $stmtProf->fetchColumn();
if ($idUsuarioProf) {
    $notifModel->crear((int) $idUsuarioProf, 'PAGO_CONFIRMADO', 'Tu pago fue confirmado. ¡Membresía activada!', BASE_URL . '/profesional/dashboard');
}

    } catch (Exception $e) { /* futuro: flash message */ }
    header("Location: " . BASE_URL . "/admin/pagos");
    exit;
}

public function rechazarPago() {
    require_once "../app/models/Membresia.php";
    $membresiaModel = new Membresia();
    $id = (int) ($_POST['id_transaccion'] ?? 0);
    $membresiaModel->rechazarTransaccion($id);
    $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'RECHAZAR_PAGO', 'transacciones_suscripcion', $id);
    header("Location: " . BASE_URL . "/admin/pagos");
    exit;
}

public function auditoria() {
    $this->view('admin/auditoria', [
        'titulo' => 'GEO-PRO | Auditoría del Sistema',
        'logs'   => $this->adminModel->auditoriaReciente(50)
    ]);
}


public function categorias() {
    $this->view('admin/categorias', [
        'titulo'     => 'GEO-PRO | Gestión de Categorías',
        'categorias' => $this->adminModel->listarCategorias()
    ]);
}

public function crearCategoria() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/admin/categorias");
        exit;
    }
    try {
        $this->adminModel->crearCategoria(
            $_POST['nombre_categoria'] ?? '',
            $_POST['tipo_clasificacion'] ?? 'AMBOS',
            $_POST['icono_fa'] ?? '',
            $_POST['descripcion'] ?? null
        );
        $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'CREAR_CATEGORIA', 'categorias', null);
    } catch (Exception $e) {
        // futuro: flash message
    }
    header("Location: " . BASE_URL . "/admin/categorias");
    exit;
}

public function toggleCategoria() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/admin/categorias");
        exit;
    }
    $id = (int) ($_POST['id_categoria'] ?? 0);
    $this->adminModel->toggleCategoria($id);
    $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'TOGGLE_CATEGORIA', 'categorias', $id);
    header("Location: " . BASE_URL . "/admin/categorias");
    exit;
}



}