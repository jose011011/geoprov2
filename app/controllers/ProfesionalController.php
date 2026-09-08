<?php
require_once "../app/models/Usuario.php";

class ProfesionalController extends Controller {
    private Profesional $profesionalModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        // Rol 3 = Profesional / Empírico
        if ((int) $_SESSION['role_id'] !== 3) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->profesionalModel = new Profesional();
    }

    /* ========================================================
       1. DASHBOARD (Centro de Mando / Mapa)
       ======================================================== */
    public function dashboard() {
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

    /* ========================================================
       2. TOGGLE DE DISPONIBILIDAD (Botón del Radar)
       ======================================================== */
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

    /* ========================================================
       3. SOLICITUDES ENTRANTES (Bandeja de Entrada)
       ======================================================== */
    public function solicitudes() {
        require_once "../app/models/Solicitud.php";
        $solicitudModel = new Solicitud();

        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $filtro = $_GET['estado'] ?? null;
        // Si no hay filtro, mostrar solicitudes PENDIENTES o ACEPTADAS que requieren atención
        $solicitudes = $solicitudModel->listarPorProfesional((int) $perfil['id_profesional'], $filtro);

        $this->view('profesional/solicitudes', [
            'titulo'       => 'GEO-PRO | Mis Solicitudes de Trabajo',
            'perfil'       => $perfil,
            'solicitudes'  => $solicitudes,
            'filtroActual' => $filtro ?? 'TODAS'
        ]);
    }

    /* ========================================================
       4. HISTORIAL DE TRABAJOS (Completados / Cancelados)
       ======================================================== */
    public function historial() {
        require_once "../app/models/Solicitud.php";
        $solicitudModel = new Solicitud();

        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        // Filtramos directamente los trabajos terminados
        $solicitudes = $solicitudModel->listarPorProfesional((int) $perfil['id_profesional'], 'FINALIZADA');

        $this->view('profesional/historial', [
            'titulo'      => 'GEO-PRO | Historial de Trabajos',
            'perfil'      => $perfil,
            'solicitudes' => $solicitudes
        ]);
    }

    /* ========================================================
       5. COMPRAR TOKENS Y MEMBRESÍAS (Monetización)
       ======================================================== */
    public function comprarTokens() {
        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        // Extraemos los planes desde la Base de Datos para mostrarlos al Profesional
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM planes_suscripcion ORDER BY id_plan ASC");
        $planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('profesional/comprar_tokens', [
            'titulo' => 'GEO-PRO | Tienda de Tokens y Planes',
            'perfil' => $perfil,
            'planes' => $planes
        ]);
    }

    /* ========================================================
       6. MI PERFIL PÚBLICO (Edición de datos)
       ======================================================== */
    public function perfil() {
        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $this->view('profesional/perfil', [
            'titulo' => 'GEO-PRO | Mi Perfil',
            'perfil' => $perfil
        ]);
    }

    /* ========================================================
       7. CAMBIO DE ESTADO (Aceptar, En Camino, Finalizar)
       ======================================================== */
    public function cambiarEstadoSolicitud() {
        require_once "../app/models/Solicitud.php";
        require_once "../app/models/Usuario.php";
        require_once "../app/models/Notificacion.php";

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profesional/solicitudes");
            exit;
        }

        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        $solicitudModel = new Solicitud();
        $usuarioModel = new Usuario();
        $notifModel = new Notificacion();

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? '';

        try {
            // 1. Actualizar estado en la Base de Datos
            $solicitudModel->cambiarEstado($idSolicitud, $nuevoEstado, (int) $perfil['id_profesional']);
            
            // 2. Registrar en Logs de Auditoría
            $usuarioModel->auditar((int) $_SESSION['user_id'], 'CAMBIO_ESTADO_' . $nuevoEstado, 'solicitudes_servicio', $idSolicitud);

            // 3. Notificar al Cliente
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
                    'ACEPTADA'   => 'Tu solicitud fue aceptada por el profesional. Revisa los detalles.',
                    'EN_CAMINO'  => '¡Prepárate! El profesional está en camino a tu domicilio.',
                    'EN_PROCESO' => 'El profesional ha iniciado la asistencia técnica.',
                    'FINALIZADA' => 'Tu servicio ha concluido. ¡Por favor califica al profesional!',
                    'CANCELADA'  => 'La solicitud ha sido cancelada.'
                ];
                if (isset($mensajes[$nuevoEstado])) {
                    $notifModel->crear((int) $idUsuarioCliente, 'CAMBIO_ESTADO', $mensajes[$nuevoEstado], BASE_URL . '/solicitud/detalle/' . $idSolicitud);
                }
            }

            // Aquí a futuro: Si el estado es "ACEPTADA", descontar 1 Token de la tabla del profesional.

        } catch (Exception $e) {
            // Sprint futuro: flash message con $e->getMessage()
        }

        header("Location: " . BASE_URL . "/profesional/solicitudes");
        exit;
    }
}