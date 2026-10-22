<?php
class SolicitudController extends Controller
{
    private Solicitud $solicitudModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->ir('/auth/login');
        }
        if (!in_array((int) $_SESSION['role_id'], [3, 4], true)) {
            $this->ir('/admin/dashboard');
        }
        $this->solicitudModel = new Solicitud();
        $this->usuarioModel   = new Usuario();
    }

    private function ir(string $ruta): void
    {
        header('Location: ' . BASE_URL . $ruta);
        exit;
    }



    /** Formulario para solicitar un servicio a un profesional específico */
    public function crear($idProfesional = null)
    {
        $idProfesional = (int) $idProfesional;
        $idUsuario     = (int) $_SESSION['user_id'];
        if ($idProfesional <= 0) {
            $this->ir('/cliente/dashboard');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.id_profesional, p.id_usuario, p.macrodistrito_base, p.zona_especifica, p.tarifa_base,
                   u.nombre, u.apellido, c.nombre_categoria
              FROM profesionales p
             INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
             INNER JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.id_profesional = :id AND p.estado_validacion = 'APROBADO' AND p.estado_disponibilidad = 'DISPONIBLE'
             LIMIT 1");
        $stmt->execute([':id' => $idProfesional]);
        $profesional = $stmt->fetch();

        if (!$profesional) {
            $this->ir('/cliente/dashboard?error=no_disponible');
        }
        if ((int) $profesional['id_usuario'] === $idUsuario) {
            $this->ir('/cliente/dashboard?error=auto_contratacion');
        }

        // Un profesional que contrata como cliente: se crea su perfil de cliente si aún no existe
        $clienteModel = new Cliente();
        $cliente = $clienteModel->buscarPorUsuario($idUsuario);
        if (!$cliente && (int) $_SESSION['role_id'] === 3) {
            $cliente = $clienteModel->crearDesdeProfesional($idUsuario);
        }
        if (!$cliente) {
            $this->ir('/cliente/perfil');
        }

        $error = null;
        $old   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idSolicitud = $this->solicitudModel->crear((int) $cliente['id_cliente'], $idProfesional, $_POST);
                $this->usuarioModel->auditar($idUsuario, 'CREAR_SOLICITUD', 'solicitudes_servicio', $idSolicitud);

                $notifModel = new Notificacion();
                $notifModel->crear((int) $profesional['id_usuario'], 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', BASE_URL . '/profesional/solicitudes');

                $this->ir('/solicitud/detalle/' . $idSolicitud . '?creada=1');
            } catch (DomainException $e) {
                $error = $e->getMessage();
                $old   = $_POST;
            } catch (Throwable $e) {
                error_log('[FixGeo] SolicitudController::crear: ' . $e->getMessage());
                $error = 'No se pudo crear la solicitud. Inténtalo de nuevo en unos minutos.';
                $old   = $_POST;
            }
        }

        $this->view('cliente/solicitud_form', [
            'titulo'      => 'FixGeo | Solicitar servicio',
            'profesional' => $profesional,
            'cliente'     => $cliente,
            'error'       => $error,
            'old'         => $old,
        ]);
    }

   public function detalle($idSolicitud = null)
    {
        $idSolicitud = (int) $idSolicitud;
        $solicitud   = $this->solicitudModel->obtenerParaUsuario($idSolicitud, (int) $_SESSION['user_id']);

        if (!$solicitud) {
            Flash::set('danger', 'No encontramos esa solicitud.');
            $this->ir($this->inicioPorRol());
        }

        $yaCalificada = true;
        if ($solicitud['estado_servicio'] === 'FINALIZADA') {
            try {
                $yaCalificada = (new Calificacion())->yaCalificada($idSolicitud);
            } catch (Throwable $e) {
                error_log('[FixGeo] detalle/yaCalificada: ' . $e->getMessage());
            }
        }

        $this->view('cliente/solicitud_detalle', [
            'titulo'       => 'FixGeo | Solicitud ' . $solicitud['codigo_seguimiento'],
            'solicitud'    => $solicitud,
            'yaCalificada' => $yaCalificada,
        ]);
    }

    public function misSolicitudes()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id_cliente FROM clientes WHERE id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => (int) $_SESSION['user_id']]);
        $idCliente = $stmt->fetchColumn();

        $this->view('cliente/mis_solicitudes', [
            'titulo'      => 'FixGeo | Mis solicitudes',
            'solicitudes' => $idCliente ? $this->solicitudModel->listarPorCliente((int) $idCliente) : [],
        ]);
    }

    /** Cambio de estado hecho por el profesional (misma lógica que ProfesionalController::cambiarEstadoSolicitud) */
    public function actualizarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->ir('/profesional/solicitudes');
        }

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $nuevoEstado = trim($_POST['estado'] ?? '');
        $tiempo      = (int) ($_POST['tiempo_estimado'] ?? 0);
        $precio      = (float) ($_POST['precio_acordado'] ?? 0);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id_profesional FROM profesionales WHERE id_usuario = :u LIMIT 1');
        $stmt->execute([':u' => (int) $_SESSION['user_id']]);
        $idProfesional = (int) $stmt->fetchColumn();
        if ($idProfesional <= 0) {
            $this->ir('/profesional/solicitudes?error=acceso_denegado');
        }

        try {
            if ($nuevoEstado === 'ACEPTADA') {
                if (!$this->solicitudModel->reclamarTrabajo($idSolicitud, $idProfesional)) {
                    $this->ir('/profesional/solicitudes?error=' . urlencode('Otro profesional aceptó este trabajo antes que tú.'));
                }
            } else {
                $this->solicitudModel->cambiarEstado($idSolicitud, $nuevoEstado, $idProfesional, $tiempo > 0 ? $tiempo : null, $precio > 0 ? $precio : null);
            }
            $this->ir('/profesional/solicitudes?exito=estado_actualizado');
        } catch (DomainException $e) {
            $this->ir('/profesional/solicitudes?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            error_log('[FixGeo] SolicitudController::actualizarEstado: ' . $e->getMessage());
            $this->ir('/profesional/solicitudes?error=' . urlencode('No se pudo actualizar la solicitud. Inténtalo de nuevo.'));
        }
    }

    public function calificar($idSolicitud = null)
    {
        $idSolicitud = (int) $idSolicitud;
        $idUsuario   = (int) $_SESSION['user_id'];
        $solicitud   = $this->solicitudModel->obtenerParaUsuario($idSolicitud, $idUsuario);

        // Solo el cliente de una solicitud FINALIZADA puede calificar
        if (!$solicitud || (int) $solicitud['cliente_id_usuario'] !== $idUsuario || $solicitud['estado_servicio'] !== 'FINALIZADA') {
            $this->ir('/solicitud/misSolicitudes');
        }

        $calificacionModel = new Calificacion();
        if ($calificacionModel->yaCalificada($idSolicitud)) {
            $this->ir('/solicitud/detalle/' . $idSolicitud);
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $calificacionModel->crear(
                    $idSolicitud,
                    $idUsuario,
                    (int) ($_POST['puntuacion_general'] ?? 0),
                    (int) ($_POST['puntualidad'] ?? 0),
                    (int) ($_POST['calidad_trabajo'] ?? 0),
                    trim($_POST['comentario'] ?? '') ?: null
                );
                Flash::set('success', '¡Gracias por calificar!');
                $this->ir('/cliente/dashboard?calificado=1');
            } catch (DomainException $e) {
                $error = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[FixGeo] SolicitudController::calificar: ' . $e->getMessage());
                $error = 'No se pudo guardar tu calificación. Inténtalo de nuevo.';
            }
        }

        $this->view('cliente/calificar', [
            'titulo'    => 'FixGeo | Calificar servicio',
            'solicitud' => $solicitud,
            'error'     => $error,
        ]);
    }

    /** Calificación rápida (una sola estrella para los tres criterios) */
    public function guardarCalificacion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->ir('/cliente/historial');
        }

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $idUsuario   = (int) $_SESSION['user_id'];
        $puntuacion  = (int) ($_POST['puntuacion_general'] ?? 0);
        $comentario  = trim($_POST['comentario'] ?? '');

        $solicitud = $this->solicitudModel->obtenerParaUsuario($idSolicitud, $idUsuario);
        if ($solicitud && (int) $solicitud['cliente_id_usuario'] === $idUsuario
            && $solicitud['estado_servicio'] === 'FINALIZADA' && $puntuacion >= 1 && $puntuacion <= 5) {
            try {
                $calificacionModel = new Calificacion();
                if (!$calificacionModel->yaCalificada($idSolicitud)) {
                    $calificacionModel->crear($idSolicitud, $idUsuario, $puntuacion, $puntuacion, $puntuacion, $comentario !== '' ? $comentario : null);
                }
            } catch (Throwable $e) {
                error_log('[FixGeo] SolicitudController::guardarCalificacion: ' . $e->getMessage());
            }
        }
        $this->ir('/solicitud/detalle/' . $idSolicitud . '?success=calificado');
    }

    /** El mapa del viaje es del profesional: se delega a su controlador, que ya valida que la solicitud es suya */
    public function mapaViaje($idSolicitud = null)
    {
        $this->ir('/profesional/mapaViaje/' . (int) $idSolicitud);
    }


        public function cancelar($idSolicitud = null)
    {
        $idSolicitud = (int) $idSolicitud;
        if (!$this->esPost()) {
            $this->ir('/solicitud/detalle/' . $idSolicitud);
        }
        try {
            $this->solicitudModel->cancelarPorCliente($idSolicitud, (int) $_SESSION['user_id']);
            Flash::set('success', 'Cancelaste la solicitud.');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] cancelar: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo cancelar. Inténtalo de nuevo.');
        }
        $this->ir('/solicitud/detalle/' . $idSolicitud);
    }
}