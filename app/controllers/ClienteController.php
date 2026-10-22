<?php
class ClienteController extends Controller
{
    private ClientePanel $panel;

    public function __construct()
    {
        // Cliente (4) o profesional (3) que contrata a un colega
        $this->requiereSesion([3, 4]);
        $this->panel = new ClientePanel();
    }

    private function perfilActual(): ?array
    {
        return $this->panel->asegurarPerfil((int) $_SESSION['user_id'], (int) $_SESSION['role_id']);
    }

    // ================= INICIO =================
     public function dashboard()
    {
        $cliente   = $this->perfilActual();
        $idCliente = $cliente ? (int) $cliente['id_cliente'] : 0;

        $this->view('cliente/dashboard', [
            'titulo'          => 'FixGeo | Inicio',
            'categorias'      => $this->panel->categoriasActivas(),
            'cliente'         => $cliente,
            'stats'           => $idCliente ? $this->panel->estadisticas($idCliente) : null,
            'solicitudActiva' => $idCliente ? $this->panel->solicitudActiva($idCliente) : null,
            'favoritos'       => $idCliente ? array_slice($this->panel->favoritos($idCliente), 0, 4) : [],
            'recientes'       => $idCliente ? array_slice($this->panel->historial($idCliente), 0, 3) : [],
        ]);
    }

    // ================= BÚSQUEDA (asistente, categoría o técnico elegido) =================
    public function buscarEspecialista()
        
    {
        $this->exigirCalificacion();
        $idUsuario   = (int) $_SESSION['user_id'];
        $descripcion = mb_substr(trim((string) ($_POST['descripcion'] ?? '')), 0, 1000);
        $idCat       = (int) ($_GET['cat'] ?? 0);
        $idProf      = (int) ($_GET['id_prof'] ?? 0);

        $categorias = $this->panel->categoriasActivas();
        $categoria  = null;
        $esIA       = false;

        if ($idProf > 0) {
            $categoria = $this->panel->categoriaDeProfesional($idProf);
        } elseif ($idCat > 0) {
            $categoria = $this->panel->categoriaPorId($idCat);
        } elseif ($descripcion !== '') {
            $esIA      = true;
            $categoria = Asistente::detectarCategoria($descripcion, $categorias);
        } else {
            Flash::set('warning', 'Cuéntanos qué necesitas arreglar o elige una categoría.');
            $this->redirigir('/cliente/dashboard');
        }

        $this->view('cliente/buscar_especialista', [
            'titulo'             => 'FixGeo | Detalles del servicio',
            'problemaOriginal'   => $descripcion,
            'categoria'          => $categoria,
            'categorias'         => $categorias,
            'profesionales'      => $categoria ? $this->panel->profesionalesDeCategoria((int) $categoria['id_categoria'], $idUsuario) : [],
            'esBusquedaIA'       => $esIA,
            'clienteInfo'        => $this->perfilActual(),
            'idProfSeleccionado' => $idProf,
        ]);
    }

    // ================= CREAR EL PEDIDO =================
    public function registrarPedido()
    {
        $this->exigirCalificacion();
        if (!$this->esPost()) {
            $this->redirigir('/cliente/dashboard');
        }

        $idUsuario     = (int) $_SESSION['user_id'];
        $idProfesional = (int) ($_POST['id_profesional'] ?? 0);   // 0 = que FixGeo asigne
        $idCategoria   = (int) ($_POST['id_categoria'] ?? 0);
        $descripcion   = trim((string) ($_POST['descripcion'] ?? ''));

        try {
            $cliente = $this->perfilActual();
            if (!$cliente || trim((string) $cliente['direccion_referencia']) === '') {
                Flash::set('warning', 'Antes de pedir un servicio, completa tu dirección en "Mi perfil".');
                $this->redirigir('/cliente/perfil');
            }

            $datos = [
                'descripcion_problema' => $descripcion,
                'direccion_servicio'   => $cliente['direccion_referencia'],
                'macrodistrito'        => $cliente['macrodistrito'] ?? 'CENTRO',
                'zona'                 => $cliente['zona'],
                'latitud_destino'      => $cliente['latitud_predeterminada'] ?? -16.5,
                'longitud_destino'     => $cliente['longitud_predeterminada'] ?? -68.15,
            ];

            $solicitudes = new Solicitud();

            if ($idProfesional > 0) {
                $idSolicitud = $solicitudes->crear((int) $cliente['id_cliente'], $idProfesional, $datos);
                Flash::set('success', 'Solicitud enviada. Te avisaremos cuando el técnico la acepte.');
            } else {
                if (!$this->panel->hayProfesionales($idCategoria, (string) $datos['macrodistrito'], $idUsuario)) {
                    Flash::set('warning', 'Por ahora no hay técnicos disponibles de esa categoría en tu zona. Prueba con el catálogo de especialistas.');
                    $this->redirigir('/cliente/especialistas');
                }
                $idSolicitud = $solicitudes->crear((int) $cliente['id_cliente'], null, $datos, $idCategoria);
                Flash::set('success', 'Estamos buscando al mejor técnico para ti. Te avisaremos apenas alguien acepte.');
            }

            $this->redirigir('/solicitud/detalle/' . $idSolicitud);
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
            $this->redirigir('/cliente/dashboard');
        } catch (PDOException $e) {
            error_log('[FixGeo] registrarPedido: ' . $e->getMessage());
            Flash::set('danger', 'No pudimos registrar tu pedido. Inténtalo de nuevo en unos minutos.');
            $this->redirigir('/cliente/dashboard');
        }
    }

    // ================= CATÁLOGO MANUAL =================
    public function especialistas()
    {
        $idCategoria = (int) ($_GET['categoria'] ?? 0);

        $this->view('cliente/especialistas', [
            'titulo'          => 'FixGeo | Catálogo de técnicos',
            'especialistas'   => $this->panel->catalogo($idCategoria, (int) $_SESSION['user_id']),
            'categorias'      => $this->panel->categoriasActivas(),
            'categoriaActual' => $idCategoria,
        ]);
    }

    public function solicitarManual()
    {
        $this->exigirCalificacion();
        $idProfesional = (int) ($_POST['id_profesional'] ?? 0);
        if ($this->esPost() && $idProfesional > 0) {
            $this->redirigir('/cliente/buscarEspecialista?id_prof=' . $idProfesional);
        }
        Flash::set('danger', 'No pudimos identificar al técnico seleccionado.');
        $this->redirigir('/cliente/especialistas');
    }

    // ================= FAVORITOS =================
    public function favoritos()
    {
        $cliente = $this->perfilActual();
        $this->view('cliente/favoritos', [
            'titulo'    => 'FixGeo | Mis favoritos',
            'favoritos' => $cliente ? $this->panel->favoritos((int) $cliente['id_cliente']) : [],
        ]);
    }

    public function agregarFavorito($idProfesional = 0)
    {
        try {
            $this->panel->agregarFavorito((int) $_SESSION['user_id'], (int) $idProfesional);
            Flash::set('success', 'Técnico agregado a tus favoritos.');
        } catch (PDOException $e) {
            error_log('[FixGeo] agregarFavorito: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo agregar a favoritos.');
        }
        $this->volver('/cliente/favoritos');
    }

    public function eliminarFavorito($idFavorito = 0)
    {
        try {
            $this->panel->quitarFavorito((int) $_SESSION['user_id'], (int) $idFavorito);
            Flash::set('success', 'Técnico quitado de tus favoritos.');
        } catch (PDOException $e) {
            error_log('[FixGeo] eliminarFavorito: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo quitar de favoritos.');
        }
        $this->redirigir('/cliente/favoritos');
    }

    /** Vuelve a la página de origen solo si es de este mismo sitio. */
    private function volver(string $respaldo): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        header('Location: ' . (str_starts_with($ref, BASE_URL) ? $ref : BASE_URL . $respaldo));
        exit;
    }

    // ================= HISTORIAL =================
    public function historial()
    {
        $cliente = $this->perfilActual();
        $this->view('cliente/historial', [
            'titulo'  => 'FixGeo | Mis solicitudes',
            'pedidos' => $cliente ? $this->panel->historial((int) $cliente['id_cliente']) : [],
        ]);
    }

    // ================= PERFIL =================
    public function perfil()
    {
        $idUsuario = (int) $_SESSION['user_id'];
        $cliente   = $this->perfilActual();
        $error     = null;

        if ($this->esPost()) {
            $lat = filter_var($_POST['latitud'] ?? null, FILTER_VALIDATE_FLOAT);
            $lng = filter_var($_POST['longitud'] ?? null, FILTER_VALIDATE_FLOAT);
            $datos = [
                'zona'                 => trim((string) ($_POST['zona'] ?? '')),
                'direccion_referencia' => trim((string) ($_POST['direccion_referencia'] ?? '')),
                'macrodistrito'        => (string) ($_POST['macrodistrito'] ?? ''),
                'latitud'              => ($lat !== false && abs($lat) <= 90)  ? $lat : ($cliente['latitud_predeterminada'] ?? -16.5),
                'longitud'             => ($lng !== false && abs($lng) <= 180) ? $lng : ($cliente['longitud_predeterminada'] ?? -68.15),
            ];

            $errores = [];
            if (mb_strlen($datos['direccion_referencia']) < 5 || mb_strlen($datos['direccion_referencia']) > 255) {
                $errores[] = 'Ingresa una dirección de referencia (entre 5 y 255 caracteres).';
            }
            if (mb_strlen($datos['zona']) < 2 || mb_strlen($datos['zona']) > 100) {
                $errores[] = 'Ingresa tu zona o barrio.';
            }
            if (!isset(Catalogo::MACRODISTRITOS[$datos['macrodistrito']])) {
                $errores[] = 'Selecciona tu macrodistrito.';
            }

            if (!$errores) {
                try {
                    $this->panel->guardarPerfil($idUsuario, $datos);
                    Flash::set('success', 'Tus datos se guardaron correctamente.');
                    $this->redirigir('/cliente/perfil');
                } catch (PDOException $e) {
                    error_log('[FixGeo] perfil: ' . $e->getMessage());
                    $error = 'No pudimos guardar tus datos. Inténtalo de nuevo.';
                }
            } else {
                $error = implode("\n", $errores);
            }
            $cliente = array_merge($cliente ?? [], [
                'zona' => $datos['zona'], 'direccion_referencia' => $datos['direccion_referencia'],
                'macrodistrito' => $datos['macrodistrito'],
            ]);
        }

        $this->view('cliente/perfil', [
            'titulo'  => 'FixGeo | Mi perfil',
            'cliente' => $cliente,
            'error'   => $error,
        ]);
    }

    // ================= AJAX: ubicación del técnico (solo el cliente dueño, solo "en camino") =================
    public function rastrearProfesional($idSolicitud = 0)
    {
        $coords = $this->panel->ubicacionTecnico((int) $idSolicitud, (int) $_SESSION['user_id']);
        $this->jsonResponse($coords ?: ['latitud_actual' => null, 'longitud_actual' => null]);
    }


        // ================= UN CLIENTE OFRECE SERVICIOS (misma cuenta, sin registrarse de nuevo) =================
    public function ofrecerServicios()
    {
        if ((int) $_SESSION['role_id'] === 3) {
            Flash::set('info', 'Tu cuenta ya tiene un perfil de técnico. Usa "Modo técnico" cuando quieras.');
            $this->redirigir('/profesional/dashboard');
        }

        $mapa = ['profesional' => 'TECNICO_PROFESIONAL', 'oficio' => 'OFICIO_EMPIRICO'];
        $tipoUrl = (string) ($_GET['tipo'] ?? '');
        $tipo    = $mapa[$tipoUrl] ?? null;

        $error      = null;
        $old        = [];
        $categorias = $tipo ? $this->panel->categoriasParaPrestador($tipo) : [];

        if ($tipo && $this->esPost()) {
            $datos = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
            $datos['tipo_prestador'] = $tipo;
            $old = $datos;

            $errores = Validador::prestador($datos, $_FILES, $tipo);
            if (!$errores) {
                try {
                    (new Usuario())->activarPerfilPrestador((int) $_SESSION['user_id'], $datos, $_FILES);
                    $_SESSION['role_id'] = 3;
                    Flash::set('success', '¡Listo! Enviamos tu perfil de técnico a revisión. Mientras validamos tus documentos puedes seguir usando FixGeo como cliente.');
                    $this->redirigir('/profesional/dashboard');
                } catch (PDOException $e) {
                    error_log('[FixGeo] ofrecerServicios: ' . $e->getMessage());
                    $error = 'No pudimos guardar tu perfil. Inténtalo de nuevo en unos minutos.';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = implode("\n", $errores);
            }
        }

        $this->view('cliente/ofrecer_servicios', [
            'titulo'        => 'FixGeo | Ofrecer mis servicios',
            'tipoPrestador' => $tipo,
            'tipoUrl'       => $tipoUrl,
            'categorias'    => $categorias,
            'error'         => $error,
            'old'           => $old,
        ]);
    }
        /** Enlace que usan las notificaciones: cliente/solicitud/ID */
    public function solicitud($idSolicitud = 0)
    {
        $this->redirigir('/solicitud/detalle/' . (int) $idSolicitud);
    }
        /** Regla del manual: no se puede pedir otro servicio sin calificar el anterior. */
    private function exigirCalificacion(): void
    {
        $cliente = $this->perfilActual();
        if (!$cliente) {
            return;
        }
        $pendiente = $this->panel->servicioPorCalificar((int) $cliente['id_cliente']);
        if ($pendiente) {
            Flash::set('warning', 'Antes de pedir otro servicio, califica el anterior (' . $pendiente['codigo_seguimiento'] . '). Solo toma un minuto y ayuda a otros clientes.');
            $this->redirigir('/solicitud/calificar/' . (int) $pendiente['id_solicitud']);
        }
    }
}