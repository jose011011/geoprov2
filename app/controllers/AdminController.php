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

    /* ========================================================
       1. DASHBOARD PRINCIPAL
       ======================================================== */
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

    /* ========================================================
       2. REVISIÓN Y AUDITORÍA DE EXPEDIENTE PROFESIONAL
       ======================================================== */
    public function verProfesional($id = null) {
        $idProfesional = (int) $id;
        $perfil = $this->adminModel->obtenerProfesionalDetalle($idProfesional);

        if (!$perfil) {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $documentos = $this->adminModel->obtenerDocumentos($idProfesional);

        $this->view('admin/ver_profesional', [
            'titulo'     => 'GEO-PRO | Revisión de ' . ($perfil['nombre'] ?? 'Profesional'),
            'perfil'     => $perfil,
            'documentos' => $documentos
        ]);
    }

    // Método unificado para procesar los botones de ver_profesional.php
    public function cambiarEstadoProfesional($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $idProfesional = (int) ($id ?? $_POST['id_profesional'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';

        try {
            if ($nuevoEstado === 'APROBADO') {
                $this->adminModel->aprobarProfesional($idProfesional);
                $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'APROBAR_PROFESIONAL', 'profesionales', $idProfesional);
            } elseif ($nuevoEstado === 'RECHAZADO') {
                $this->adminModel->rechazarProfesional($idProfesional);
                $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'RECHAZAR_PROFESIONAL', 'profesionales', $idProfesional);
            }
        } catch (Exception $e) {
            // Manejo de excepción o logs
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
        } catch (Exception $e) { }

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
        } catch (Exception $e) { }

        header("Location: " . BASE_URL . "/admin/verProfesional/" . $idProfesional);
        exit;
    }

    /* ========================================================
       3. GESTIÓN DE PLANES Y MONETIZACIÓN (SIDEBAR: Planes)
       ======================================================== */
    public function planes() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM planes_suscripcion ORDER BY id_plan ASC");
        $planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/planes', [
            'titulo' => 'GEO-PRO | Gestión de Planes y Monetización',
            'planes' => $planes
        ]);
    }

    /* ========================================================
       4. GESTIÓN COMPLETA DE PROFESIONALES (SIDEBAR: Profesionales)
       ======================================================== */
    public function profesionales() {
        $filtro = $_GET['estado'] ?? 'TODOS';
        $db = Database::getInstance()->getConnection();

        $sql = "
            SELECT p.*, u.nombre, u.apellido, u.correo, u.celular, u.estado AS estado_usuario,
                   c.nombre_categoria, pl.nombre_plan
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
        ";

        if ($filtro !== 'TODOS') {
            $sql .= " WHERE p.estado_validacion = :estado ORDER BY p.id_profesional DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':estado' => $filtro]);
        } else {
            $sql .= " ORDER BY p.id_profesional DESC";
            $stmt = $db->query($sql);
        }

        $profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/profesionales', [
            'titulo'        => 'GEO-PRO | Control de Profesionales',
            'profesionales' => $profesionales,
            'filtroActual'  => $filtro
        ]);
    }

    /* ========================================================
       5. CONTROL Y GESTIÓN DE CLIENTES (SIDEBAR: Clientes)
       ======================================================== */
    public function clientes() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT c.id_cliente, c.zona, c.direccion_referencia,
                   u.id_usuario, u.nombre, u.apellido, u.correo, u.celular, u.estado, u.fecha_registro,
                   (SELECT COUNT(*) FROM solicitudes_servicio s WHERE s.id_cliente = c.id_cliente) AS total_pedidos
            FROM clientes c
            INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
            ORDER BY c.id_cliente DESC
        ");
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/clientes', [
            'titulo'   => 'GEO-PRO | Control de Clientes',
            'clientes' => $clientes
        ]);
    }

    // Acción para Bloquear / Activar usuarios (Super Admin)
    public function toggleEstadoUsuario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $redireccion = $_POST['redirect_to'] ?? '/admin/dashboard';

        $db = Database::getInstance()->getConnection();
        $stmtActual = $db->prepare("SELECT estado FROM usuarios WHERE id_usuario = :id");
        $stmtActual->execute([':id' => $idUsuario]);
        $estadoActual = $stmtActual->fetchColumn();

        if ($estadoActual) {
            $nuevoEstado = ($estadoActual === 'ACTIVO') ? 'BLOQUEADO' : 'ACTIVO';
            $stmtUpdate = $db->prepare("UPDATE usuarios SET estado = :nuevo WHERE id_usuario = :id");
            $stmtUpdate->execute([':nuevo' => $nuevoEstado, ':id' => $idUsuario]);

            $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'CAMBIO_ESTADO_USUARIO_' . $nuevoEstado, 'usuarios', $idUsuario);
        }

        header("Location: " . BASE_URL . $redireccion);
        exit;
    }

    /* ========================================================
       6. REPORTES GENERALES Y FINANCIEROS (SIDEBAR: Reportes)
       ======================================================== */
    public function reportes() {
        // Obtenemos los reportes reales de la base de datos
        $financiero = $this->adminModel->reporteFinancieroMes();
        $demanda = $this->adminModel->reporteDemandaCategorias();
        $topProfesionales = $this->adminModel->reporteTopProfesionales();

        $this->view('admin/reportes', [
            'titulo'           => 'GEO-PRO | Reportes y Analíticas',
            'financiero'       => $financiero,
            'demanda'          => $demanda,
            'topProfesionales' => $topProfesionales
        ]);
    }

    /* ========================================================
       7. PAGOS, TOKENS Y MEMBRESÍAS (SIDEBAR: Pagos QR)
       ======================================================== */
    public function pagos() {
        require_once "../app/models/Membresia.php";
        $membresiaModel = new Membresia();
        $this->view('admin/pagos', [
            'titulo' => 'GEO-PRO | Pagos Pendientes',
            'pagos'  => $membresiaModel->listarTodasTransacciones()
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
        } catch (Exception $e) {
            die("<h1>Error al confirmar el pago:</h1><p>" . $e->getMessage() . "</p><br><p>Por favor revisa la base de datos o contacta a soporte.</p>");
        }

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

    /* ========================================================
       8. LOGS Y AUDITORÍA DEL SISTEMA (SIDEBAR: Auditoría)
       ======================================================== */
    public function auditoria() {
        $this->view('admin/auditoria', [
            'titulo' => 'GEO-PRO | Auditoría del Sistema',
            'logs'   => $this->adminModel->auditoriaReciente(50)
        ]);
    }

    /* ========================================================
       9. GESTIÓN DE CATEGORÍAS (SIDEBAR: Categorías)
       ======================================================== */
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
            $err = urlencode($e->getMessage());
            header("Location: " . BASE_URL . "/admin/categorias?error=" . $err);
            exit;
        }

        header("Location: " . BASE_URL . "/admin/categorias?success=Categoria+creada");
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
    }/* ========================================================
       ACTUALIZAR PLANES DE SUSCRIPCIÓN (A prueba de fallos)
       ======================================================== */
    /* ========================================================
       ACTUALIZAR PLANES DE SUSCRIPCIÓN (A prueba de balas)
       ======================================================== */
    public function actualizarPlan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/planes");
            exit;
        }
        
        $idPlan = (int) ($_POST['id_plan'] ?? 0);
        $precio = (float) ($_POST['precio'] ?? 0);
        $tokens = (int) ($_POST['tokens'] ?? 0);

        if ($idPlan > 0) {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Detecta dinámicamente cómo se llaman tus columnas en la BD
                $columnas = $db->query("SHOW COLUMNS FROM planes_suscripcion")->fetchAll(PDO::FETCH_COLUMN);
                $colPrecio = in_array('precio', $columnas) ? 'precio' : 'precio';
                $colTokens = in_array('tokens_otorgados', $columnas) ? 'tokens_otorgados' : 'tokens_otorgados';

                // Actualizamos usando el ID exacto
                $sql = "UPDATE planes_suscripcion SET {$colPrecio} = :precio, {$colTokens} = :tokens WHERE id_plan = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':precio' => $precio,
                    ':tokens' => $tokens,
                    ':id' => $idPlan
                ]);

                $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'ACTUALIZAR_PLAN_' . $idPlan, 'planes_suscripcion', $idPlan);
            } catch (Exception $e) { 
                // Si algo falla, esta línea te mostrará el error exacto en pantalla
                die("Error de Base de Datos: " . $e->getMessage()); 
            }
        }

        header("Location: " . BASE_URL . "/admin/planes");
        exit;
    }
    
}