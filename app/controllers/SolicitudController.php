<?php
require_once "../app/models/Usuario.php";

class SolicitudController extends Controller {
    private Solicitud $solicitudModel;
    private Usuario $usuarioModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        if (!in_array((int) $_SESSION['role_id'], [3, 4], true)) {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }
        $this->solicitudModel = new Solicitud();
        $this->usuarioModel = new Usuario();
    }

    /** Formulario para solicitar un servicio a un profesional específico */
    public function crear($idProfesional = null) {
        if (!$idProfesional) {
            header("Location: " . BASE_URL . "/cliente/dashboard");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // SPRINT 1: Agregamos p.id_usuario y p.tarifa_base
        $stmtProf = $db->prepare("
            SELECT p.id_profesional, p.id_usuario, p.macrodistrito_base, p.zona_especifica, p.tarifa_base, u.nombre, u.apellido, c.nombre_categoria
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_profesional = :id AND p.estado_validacion = 'APROBADO' AND p.estado_disponibilidad = 'DISPONIBLE'
            LIMIT 1
        ");
        $stmtProf->execute([':id' => (int) $idProfesional]);
        $profesional = $stmtProf->fetch();

        if (!$profesional) {
            header("Location: " . BASE_URL . "/cliente/dashboard?error=no_disponible");
            exit;
        }

        // SEGURIDAD ANTI-FRAUDE: Evitar autocontratación
        if ((int)$profesional['id_usuario'] === (int)$_SESSION['user_id']) {
            header("Location: " . BASE_URL . "/cliente/dashboard?error=auto_contratacion");
            exit;
        }

        $stmtCli = $db->prepare("SELECT id_cliente, direccion_referencia, zona, latitud_predeterminada, longitud_predeterminada FROM clientes WHERE id_usuario = :id LIMIT 1");
        $stmtCli->execute([':id' => $_SESSION['user_id']]);
        $cliente = $stmtCli->fetch();

        $error = null;
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Se ejecuta la inserción en la tabla solicitudes_servicio
                $idSolicitud = $this->solicitudModel->crear((int) $cliente['id_cliente'], (int) $idProfesional, $_POST);
                $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'CREAR_SOLICITUD', 'solicitudes_servicio', $idSolicitud);
                
                require_once "../app/models/Notificacion.php";
                $notifModel = new Notificacion();
                
                if ($profesional['id_usuario']) {
                    $notifModel->crear((int) $profesional['id_usuario'], 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', BASE_URL . '/profesional/solicitudes');
                }
                
                header("Location: " . BASE_URL . "/solicitud/detalle/" . $idSolicitud . "?creada=1");
                exit;
            } catch (Exception $e) {
                // DEPURACIÓN: Capturamos el error real de la base de datos para saber qué está fallando
                $msg = $e->getMessage();
                if (str_contains($msg, 'vencida')) {
                    $error = "Este profesional tiene su membresía vencida.";
                } elseif (str_contains($msg, 'validado documentalmente')) {
                    $error = "Este profesional aún no ha sido validado.";
                } elseif (str_contains($msg, 'DISPONIBLE')) {
                    $error = "Este profesional ya no está disponible.";
                } else {
                    // AQUÍ ESTÁ LA MAGIA: Si hay un error SQL, te lo mostrará en pantalla
                    $error = "Error del Sistema (SQL): " . $msg; 
                }
                $old = $_POST;
            }
        }

        $this->view('cliente/solicitud_form', [
            'titulo'      => 'GEO-PRO | Solicitar Servicio',
            'profesional' => $profesional,
            'cliente'     => $cliente,
            'error'       => $error,
            'old'         => $old
        ]);
    }

    public function detalle($idSolicitud = null) {
        $solicitud = $this->solicitudModel->obtenerPorId((int) $idSolicitud);
        if (!$solicitud) {
            header("Location: " . BASE_URL . "/cliente/dashboard");
            exit;
        }
        $this->view('cliente/solicitud_detalle', [
            'titulo'    => 'GEO-PRO | Solicitud ' . $solicitud['codigo_seguimiento'],
            'solicitud' => $solicitud
        ]);
    }

    public function misSolicitudes() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $cliente = $stmt->fetch();

        $solicitudes = $cliente ? $this->solicitudModel->listarPorCliente((int) $cliente['id_cliente']) : [];

        $this->view('cliente/mis_solicitudes', [
            'titulo'      => 'GEO-PRO | Mis Solicitudes',
            'solicitudes' => $solicitudes
        ]);
    }
    
    public function actualizarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
            $nuevoEstado = trim($_POST['estado'] ?? '');

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id_profesional FROM profesionales WHERE id_usuario = :id_usuario LIMIT 1");
            $stmt->execute([':id_usuario' => $_SESSION['user_id']]);
            $profesional = $stmt->fetch();

            if (!$profesional) {
                header("Location: " . BASE_URL . "/profesional/solicitudes?error=acceso_denegado");
                exit;
            }

            try {
              $tiempoEstimado = isset($_POST['tiempo_estimado']) ? (int) $_POST['tiempo_estimado'] : null;
$precioAcordado = isset($_POST['precio_acordado']) ? (float) $_POST['precio_acordado'] : null;

$this->solicitudModel->cambiarEstado($idSolicitud, $nuevoEstado, (int)$profesional['id_profesional'], $tiempoEstimado, $precioAcordado);
                $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'CAMBIO_ESTADO_' . $nuevoEstado, 'solicitudes_servicio', $idSolicitud);

                header("Location: " . BASE_URL . "/profesional/solicitudes?exito=estado_actualizado");
                exit;
            } catch (Exception $e) {
                header("Location: " . BASE_URL . "/profesional/solicitudes?error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }

    public function calificar($idSolicitud = null) {
        require_once "../app/models/Calificacion.php";
        $calificacionModel = new Calificacion();

        $idSolicitud = (int) $idSolicitud;
        $solicitud = $this->solicitudModel->obtenerPorId($idSolicitud);

        if (!$solicitud || $solicitud['estado_servicio'] !== 'FINALIZADA') {
            header("Location: " . BASE_URL . "/solicitud/misSolicitudes");
            exit;
        }
        if ($calificacionModel->yaCalificada($idSolicitud)) {
            header("Location: " . BASE_URL . "/solicitud/detalle/" . $idSolicitud);
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $calificacionModel->crear(
                    $idSolicitud,
                    (int) $_SESSION['user_id'],
                    (int) ($_POST['puntuacion_general'] ?? 0),
                    (int) ($_POST['puntualidad'] ?? 0),
                    (int) ($_POST['calidad_trabajo'] ?? 0),
                    $_POST['comentario'] ?? null
                );
                header("Location: " . BASE_URL . "/solicitud/detalle/" . $idSolicitud . "?calificado=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        $this->view('cliente/calificar', [
            'titulo'    => 'GEO-PRO | Calificar servicio',
            'solicitud' => $solicitud,
            'error'     => $error
        ]);
    }

    // SPRINT 2.5: Pantalla del Profesional para emitir GPS
    public function mapaViaje($idSolicitud = null) {
        $solicitud = $this->solicitudModel->obtenerPorId((int) $idSolicitud);
        
        // Verificamos que exista y que el profesional asignado sea el que está logueado
        if (!$solicitud) {
            header("Location: " . BASE_URL . "/profesional/solicitudes");
            exit;
        }

        $this->view('profesional/mapa_viaje', [
            'titulo' => 'GEO-PRO | Viaje en Curso',
            'solicitud' => $solicitud
        ]);
    }
}