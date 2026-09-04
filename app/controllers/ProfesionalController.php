<?php
class ProfesionalController extends Controller {
    private Profesional $profesionalModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        if ((int) $_SESSION['role_id'] !== 3) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->profesionalModel = new Profesional();
    }public function dashboard() {
    $idUsuario = (int) $_SESSION['user_id'];
    $perfil = $this->profesionalModel->buscarPorUsuario($idUsuario);

    if (!$perfil) {
        session_destroy();
        header("Location: " . BASE_URL . "/auth/login?error=perfil_no_encontrado");
        exit;
    }

    $documentos = $this->profesionalModel->obtenerDocumentos((int) $perfil['id_profesional']);
    $stats = $this->profesionalModel->obtenerEstadisticas((int) $perfil['id_profesional']);
    $membresiaVencida = $this->profesionalModel->membresiaVencida($perfil);
    $diasParaVencer = $this->profesionalModel->diasParaVencer($perfil);

    $this->view('profesional/dashboard', [
        'titulo'           => 'GEO-PRO | Mi Panel Profesional',
        'perfil'           => $perfil,
        'documentos'       => $documentos,
        'stats'            => $stats,
        'membresiaVencida' => $membresiaVencida,
        'diasParaVencer'   => $diasParaVencer
    ]);
}

    /** Endpoint AJAX: alterna DISPONIBLE / NO_DISPONIBLE */
    public function toggleDisponibilidad() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
        }

        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            $this->jsonResponse(['ok' => false, 'error' => 'Perfil no encontrado'], 404);
        }

        try {
            $resultado = $this->profesionalModel->actualizarDisponibilidad(
                (int) $perfil['id_profesional'],
                $perfil['estado_validacion'],
                $_POST['estado'] ?? ''
            );
            $this->jsonResponse(['ok' => true] + $resultado);
        } catch (Exception $e) {
            $this->jsonResponse(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }
    public function solicitudes() {
    require_once "../app/models/Solicitud.php";
    $solicitudModel = new Solicitud();

    $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
    if (!$perfil) {
        header("Location: " . BASE_URL . "/auth/login");
        exit;
    }

    $filtro = $_GET['estado'] ?? null;
    $solicitudes = $solicitudModel->listarPorProfesional((int) $perfil['id_profesional'], $filtro);

    $this->view('profesional/solicitudes', [
        'titulo'       => 'GEO-PRO | Mis Solicitudes',
        'solicitudes'  => $solicitudes,
        'filtroActual' => $filtro ?? 'TODAS'
    ]);
}

public function cambiarEstadoSolicitud() {
    require_once "../app/models/Solicitud.php";
    require_once "../app/models/Usuario.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/profesional/solicitudes");
        exit;
    }

    $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
    $solicitudModel = new Solicitud();
    $usuarioModel = new Usuario();

    $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
    $nuevoEstado = $_POST['estado'] ?? '';

    try {
        $solicitudModel->cambiarEstado($idSolicitud, $nuevoEstado, (int) $perfil['id_profesional']);
        $usuarioModel->auditar((int) $_SESSION['user_id'], 'CAMBIO_ESTADO_' . $nuevoEstado, 'solicitudes_servicio', $idSolicitud);

        require_once "../app/models/Notificacion.php";
$notifModel = new Notificacion();
$db = Database::getInstance()->getConnection();
$stmtCli = $db->prepare("
    SELECT u.id_usuario FROM solicitudes_servicio s
    INNER JOIN clientes cl ON s.id_cliente = cl.id_cliente
    INNER JOIN usuarios u ON cl.id_usuario = u.id_usuario
    WHERE s.id_solicitud = :id
");
$stmtCli->execute([':id' => $idSolicitud]);
$idUsuarioCliente = $stmtCli->fetchColumn();
if ($idUsuarioCliente) {
    $mensajes = [
        'ACEPTADA'   => 'Tu solicitud fue aceptada por el profesional.',
        'EN_CAMINO'  => 'Tu profesional está en camino.',
        'EN_PROCESO' => 'Tu profesional inició la atención.',
        'FINALIZADA' => 'Tu servicio fue finalizado. ¡Califícalo!',
        'CANCELADA'  => 'Tu solicitud fue cancelada.'
    ];
    if (isset($mensajes[$nuevoEstado])) {
        $notifModel->crear((int) $idUsuarioCliente, 'CAMBIO_ESTADO', $mensajes[$nuevoEstado], BASE_URL . '/solicitud/detalle/' . $idSolicitud);
    }
}
    } catch (Exception $e) {
        // Sprint futuro: flash message con $e->getMessage()
    }

    header("Location: " . BASE_URL . "/profesional/solicitudes");
    exit;
}
}