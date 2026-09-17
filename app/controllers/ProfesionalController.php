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
       3. SOLICITUDES ENTRANTES (Bandeja de Entrada - CASCADA)
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
        
        if ($filtro === 'PENDIENTE') {
            // LÓGICA DE CASCADA: Buscar trabajos nuevos disponibles en su zona según su plan
            // (Premium los ve al instante, Básicos esperan 3 min, Gratis 7 min)
            $solicitudes = $solicitudModel->obtenerOportunidadesCascada(
                (int) $perfil['id_plan'], 
                $perfil['macrodistrito_base'] ?? 'CENTRO',
                (int) $perfil['id_categoria'],
                (int) $perfil['id_profesional']
            );
        } else {
            // Si está viendo "Trabajos Activos" (ACEPTADA, EN_CAMINO, EN_PROCESO)
            $solicitudes = $solicitudModel->listarPorProfesional((int) $perfil['id_profesional'], $filtro);
        }

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

        // Verificamos si tiene un pago pendiente
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM transacciones_suscripcion WHERE id_profesional = :id_prof AND estado_pago = 'PENDIENTE'");
        $stmtCheck->execute([':id_prof' => $perfil['id_profesional']]);
        $tienePagoPendiente = ($stmtCheck->fetchColumn() > 0);

        $this->view('profesional/comprar_tokens', [
            'titulo' => 'GEO-PRO | Tienda de Tokens y Planes',
            'perfil' => $perfil,
            'planes' => $planes,
            'tienePagoPendiente' => $tienePagoPendiente
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
       7. CAMBIO DE ESTADO Y RECLAMAR TRABAJO (Escudo anti-choques)
       ======================================================== */
    public function cambiarEstadoSolicitud() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profesional/solicitudes");
            exit;
        }

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? '';
        $tiempoLlegada = (int) ($_POST['tiempo_estimado'] ?? 0);
        $precioAcordado = (float) ($_POST['precio_acordado'] ?? 0);
        
        // Obtener el ID del profesional actual
        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        $idProfesional = (int) $perfil['id_profesional'];

        try {
            $db = Database::getInstance()->getConnection();
            
            // ==========================================
            // CASO ESPECIAL 1: RECLAMAR TRABAJO (CASCADA)
            // ==========================================
            if ($nuevoEstado === 'ACEPTADA') {
                require_once "../app/models/Solicitud.php";
                $solicitudModel = new Solicitud();
                
                // Intentamos reclamar el trabajo antes que otro profesional
                try {
                    $reclamado = $solicitudModel->reclamarTrabajo($idSolicitud, $idProfesional);
                    if (!$reclamado) {
                        header("Location: " . BASE_URL . "/profesional/solicitudes?error=" . urlencode("¡Lo sentimos! Otro profesional aceptó este trabajo antes que tú."));
                        exit;
                    }
                } catch (Exception $ex) {
                    header("Location: " . BASE_URL . "/profesional/solicitudes?error=" . urlencode($ex->getMessage()));
                    exit;
                }
                
                // Si lo reclamó con éxito, redirigir a ver los trabajos activos
                header("Location: " . BASE_URL . "/profesional/solicitudes?estado=ACEPTADA");
                exit;
            }
            
            // ==========================================
            // CASO NORMAL: ACTUALIZAR TRABAJO EXISTENTE
            // ==========================================
            $sql = "UPDATE solicitudes_servicio SET estado_servicio = :estado";
            
            if ($nuevoEstado === 'EN_CAMINO' && $tiempoLlegada > 0) {
                $sql .= ", tiempo_estimado_llegada_min = :tiempo";
            } elseif ($nuevoEstado === 'FINALIZADA' && $precioAcordado > 0) {
                $sql .= ", precio_acordado = :precio, fecha_finalizacion = CURRENT_TIMESTAMP";
            } elseif ($nuevoEstado === 'EN_PROCESO') {
                $sql .= ", fecha_inicio_atencion = CURRENT_TIMESTAMP";
            }
            
            $sql .= " WHERE id_solicitud = :id_sol AND id_profesional = :id_prof"; // Solo puede cambiarlo el dueño
            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id_sol', $idSolicitud);
            $stmt->bindParam(':id_prof', $idProfesional);
            
            if ($nuevoEstado === 'EN_CAMINO' && $tiempoLlegada > 0) $stmt->bindParam(':tiempo', $tiempoLlegada);
            if ($nuevoEstado === 'FINALIZADA' && $precioAcordado > 0) $stmt->bindParam(':precio', $precioAcordado);
            
            $stmt->execute();

            if ($nuevoEstado === 'EN_CAMINO') {
                // ÉXITO: Abre el mapa a pantalla completa
                header("Location: " . BASE_URL . "/profesional/mapaViaje/" . $idSolicitud);
                exit;
            } else {
                header("Location: " . BASE_URL . "/profesional/solicitudes?success=estado_actualizado");
                exit;
            }

        } catch (Exception $e) {
            die("<h2 style='color:red;'>Error de Base de Datos al cambiar estado: " . $e->getMessage() . "</h2>");
        }
    }
    /* ========================================================
       8. REGISTRAR PAGO Y ENVIAR A VERIFICACIÓN
       ======================================================== */
    public function registrarPago() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profesional/comprarTokens");
            exit;
        }

        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        // Recibimos los datos limpios del formulario
        $idPlan = (int) $_POST['id_plan'];
        $monto = (float) $_POST['monto'];
        $metodoPago = $_POST['metodo_pago'];
        $codigoComprobante = trim($_POST['codigo_comprobante']);
        $tipoTransaccion = 'MEMBRESIA_MENSUAL'; // Como están comprando un Plan, es Membresía

        // Validar que no tenga pagos pendientes
        $db = Database::getInstance()->getConnection();
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM transacciones_suscripcion WHERE id_profesional = :id_prof AND estado_pago = 'PENDIENTE'");
        $stmtCheck->execute([':id_prof' => $perfil['id_profesional']]);
        if ($stmtCheck->fetchColumn() > 0) {
            header("Location: " . BASE_URL . "/profesional/comprarTokens?error=pago_pendiente");
            exit;
        }

        // Validar que no tenga un plan activo con tokens
        if (isset($perfil['id_plan']) && $perfil['id_plan'] > 1 && isset($perfil['tokens_disponibles']) && $perfil['tokens_disponibles'] > 0) {
            header("Location: " . BASE_URL . "/profesional/comprarTokens?error=plan_activo");
            exit;
        }

        try {
            // Insertamos el pago en estado PENDIENTE
            $stmt = $db->prepare("
                INSERT INTO transacciones_suscripcion 
                (id_profesional, id_plan, tipo_transaccion, monto, metodo_pago, codigo_comprobante, estado_pago) 
                VALUES (:id_profesional, :id_plan, :tipo, :monto, :metodo, :codigo, 'PENDIENTE')
            ");
            
            $stmt->execute([
                ':id_profesional' => $perfil['id_profesional'],
                ':id_plan' => $idPlan,
                ':tipo' => $tipoTransaccion,
                ':monto' => $monto,
                ':metodo' => $metodoPago,
                ':codigo' => $codigoComprobante
            ]);

            $idTransaccion = $db->lastInsertId();

            // Guardamos en la tabla de auditoría
            require_once "../app/models/Usuario.php";
            $usuarioModel = new Usuario();
            $usuarioModel->auditar((int) $_SESSION['user_id'], 'REPORTE_PAGO_ENVIADO', 'transacciones_suscripcion', $idTransaccion);

            // Redirigimos con éxito
            header("Location: " . BASE_URL . "/profesional/comprarTokens?success=ok");
            exit;

        } catch (PDOException $e) {
            // Si el código de comprobante ya fue usado, la BD lanzará un error porque es UNIQUE
            header("Location: " . BASE_URL . "/profesional/comprarTokens?error=codigo_duplicado");
            exit;
        }
    }

/* ========================================================
       9. ACTUALIZAR DATOS DEL PERFIL
       ======================================================== */
    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profesional/perfil");
            exit;
        }

        $idUsuario = (int) $_SESSION['user_id'];
        $perfil = $this->profesionalModel->buscarPorUsuario($idUsuario);
        
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        // Recibimos los datos y los limpiamos
        $celular = trim($_POST['celular'] ?? '');
        $macrodistrito = $_POST['macrodistrito_base'] ?? '';
        $zona = trim($_POST['zona_especifica'] ?? '');
        $tarifa = (float) ($_POST['tarifa_base'] ?? 0);
        $descripcion = trim($_POST['descripcion_servicio'] ?? '');

        try {
            $db = Database::getInstance()->getConnection();
            
            // 1. Actualizamos el celular en la tabla 'usuarios'
            $stmtU = $db->prepare("UPDATE usuarios SET celular = :celular WHERE id_usuario = :id_usuario");
            $stmtU->execute([':celular' => $celular, ':id_usuario' => $idUsuario]);

            // 2. Actualizamos la info operativa en la tabla 'profesionales'
            $stmtP = $db->prepare("
                UPDATE profesionales 
                SET macrodistrito_base = :macro, zona_especifica = :zona, tarifa_base = :tarifa, descripcion_servicio = :desc 
                WHERE id_profesional = :id_profesional
            ");
            $stmtP->execute([
                ':macro' => $macrodistrito,
                ':zona' => $zona,
                ':tarifa' => $tarifa,
                ':desc' => $descripcion,
                ':id_profesional' => $perfil['id_profesional']
            ]);

            header("Location: " . BASE_URL . "/profesional/perfil?success=ok");
        } catch (PDOException $e) {
            // Error clásico: Intentó poner un celular que ya existe en otro usuario
            header("Location: " . BASE_URL . "/profesional/perfil?error=celular_duplicado");
        }
        exit;
    }


    /* ========================================================
       10. MÓDULO: PEDIR UN SERVICIO (MODO CLIENTE CON IA)
       ======================================================== */
    public function pedirServicio() {
        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // 1. Traemos las categorías
        $categorias = $db->query("SELECT id_categoria, nombre_categoria, icono_fa FROM categorias WHERE estado = 1 ORDER BY nombre_categoria ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Traemos a TODOS los profesionales (excepto él mismo) ORDENADOS POR PLAN Y ESTRELLAS
        $stmtProf = $db->prepare("
            SELECT p.id_profesional, p.id_categoria, u.nombre, u.apellido, 
                   c.nombre_categoria, pl.nombre_plan, pl.posicionamiento_destacado,
                   COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
            LEFT JOIN vw_metricas_profesionales vw ON p.id_profesional = vw.id_profesional
            WHERE p.estado_validacion = 'APROBADO' 
              AND p.estado_disponibilidad = 'DISPONIBLE'
              AND p.tokens_disponibles > 0
              AND p.id_usuario != :id_usuario
            ORDER BY pl.posicionamiento_destacado DESC, pl.id_plan DESC, promedio_estrellas DESC
        ");
        $stmtProf->execute([':id_usuario' => (int) $_SESSION['user_id']]);
        $listaProfesionales = $stmtProf->fetchAll(PDO::FETCH_ASSOC);

        $this->view('profesional/pedir_servicio', [
            'titulo'             => 'GEO-PRO | Solicitar Asistencia',
            'perfil'             => $perfil,
            'categorias'         => $categorias,
            'listaProfesionales' => $listaProfesionales
        ]);
    }

    public function registrarPedidoServicio() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profesional/pedirServicio");
            exit;
        }

        $idUsuario = (int) $_SESSION['user_id'];
        $perfil = $this->profesionalModel->buscarPorUsuario($idUsuario);

        $idCategoria = (int) $_POST['id_categoria'];
        $descripcion = trim($_POST['descripcion_problema']);
        $direccion   = trim($_POST['direccion_servicio']);
        $tipoAsignacion = $_POST['tipo_asignacion']; // 'AUTO' o 'MANUAL'
        $idProfesionalManual = (int) ($_POST['id_profesional_seleccionado'] ?? 0);

        try {
            $db = Database::getInstance()->getConnection();

            // 1. Insertamos al profesional como cliente si aún no existe
            $stmtC = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id_usuario");
            $stmtC->execute([':id_usuario' => $idUsuario]);
            $idCliente = $stmtC->fetchColumn();

            if (!$idCliente) {
                $stmtIns = $db->prepare("INSERT INTO clientes (id_usuario, direccion_referencia, zona) VALUES (:id, :dir, :zona)");
                $stmtIns->execute([':id' => $idUsuario, ':dir' => $direccion, ':zona' => $perfil['zona_especifica'] ?? 'La Paz']);
                $idCliente = $db->lastInsertId();
            }

            // 2. LÓGICA DE ASIGNACIÓN (EL CORAZÓN DEL SISTEMA)
            $idProfesionalAsignado = null;

            if ($tipoAsignacion === 'MANUAL' && $idProfesionalManual > 0) {
                $idProfesionalAsignado = $idProfesionalManual;
            } else {
                // ASIGNACIÓN AUTOMÁTICA POR LA I.A. (Respeta planes y tokens)
                $stmtIA = $db->prepare("
                    SELECT p.id_profesional 
                    FROM profesionales p
                    INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
                    WHERE p.id_categoria = :id_categoria 
                      AND p.id_usuario != :id_usuario_propio 
                      AND p.estado_validacion = 'APROBADO' 
                      AND p.estado_disponibilidad = 'DISPONIBLE'
                      AND p.tokens_disponibles > 0 
                    ORDER BY pl.posicionamiento_destacado DESC, pl.id_plan DESC, RAND() 
                    LIMIT 1
                ");
                $stmtIA->execute([':id_categoria' => $idCategoria, ':id_usuario_propio' => $idUsuario]);
                $idProfesionalAsignado = $stmtIA->fetchColumn();
            }

            if (!$idProfesionalAsignado) {
                header("Location: " . BASE_URL . "/profesional/pedirServicio?error=sin_profesionales");
                exit;
            }

           // 3. REGISTRAMOS LA SOLICITUD EN LA PISCINA
            $codigoSeguimiento = 'GEO-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $stmtSol = $db->prepare("
                INSERT INTO solicitudes_servicio 
                (codigo_seguimiento, id_cliente, id_categoria, id_profesional, descripcion_problema, direccion_servicio, macrodistrito, zona, latitud_destino, longitud_destino, estado_servicio) 
                VALUES (:codigo, :idc, :idcat, :idp, :desc, :dir, :macro, :zona, :lat, :lng, 'PENDIENTE')
            ");
            
            $stmtSol->execute([
                ':codigo' => $codigoSeguimiento,
                ':idc' => $idCliente,
                ':idcat' => $idCategoria, // AQUÍ GUARDAMOS LA CATEGORÍA
                ':idp' => $idProfesionalAsignado, // Será NULL si es por Cascada automática
                ':desc' => $descripcion,
                ':dir' => $direccion,
                ':macro' => $perfil['macrodistrito_base'] ?? 'CENTRO',
                ':zona' => $perfil['zona_especifica'] ?? 'La Paz',
                ':lat' => $perfil['latitud_actual'] ?? -16.5000,
                ':lng' => $perfil['longitud_actual'] ?? -68.1500
            ]);

            header("Location: " . BASE_URL . "/profesional/pedirServicio?success=ok");
            exit;

        } catch (Exception $e) {
            header("Location: " . BASE_URL . "/profesional/pedirServicio?error=db");
            exit;
        }
    }
    /* ========================================================
       11. MAPA DE VIAJE Y TRACKING GPS (Para el Profesional)
       ======================================================== */
    public function mapaViaje($idSolicitud) {
        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        require_once "../app/models/Solicitud.php";
        $solicitudModel = new Solicitud();
        
        // Obtenemos los detalles de esta solicitud específica
        $solicitud = $solicitudModel->obtenerPorId((int) $idSolicitud);

        // Seguridad: Verificar que esta solicitud le pertenece a este profesional
        if (!$solicitud || (int)$solicitud['id_profesional'] !== (int)$perfil['id_profesional']) {
            header("Location: " . BASE_URL . "/profesional/solicitudes");
            exit;
        }

        $this->view('profesional/mapa_viaje', [
            'titulo'    => 'GEO-PRO | Tracking de Viaje',
            'perfil'    => $perfil,
            'solicitud' => $solicitud
        ]);
    }


    /* ========================================================
       12. ENDPOINT AJAX: LATIDO EN TIEMPO REAL (POLLING)
       ======================================================== */
    public function checkNuevasSolicitudes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false]);
            exit;
        }

        $perfil = $this->profesionalModel->buscarPorUsuario((int) $_SESSION['user_id']);
        
        // Si no está disponible o no tiene perfil, respondemos 0 para no molestarlo
        if (!$perfil || $perfil['estado_disponibilidad'] !== 'DISPONIBLE') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'nuevas' => 0]);
            exit;
        }

        require_once "../app/models/Solicitud.php";
        $solicitudModel = new Solicitud();

        // Ejecutamos la magia de la cascada
        $solicitudes = $solicitudModel->obtenerOportunidadesCascada(
            (int) $perfil['id_plan'], 
            $perfil['macrodistrito_base'] ?? 'CENTRO',
            (int) $perfil['id_categoria'],
            (int) $perfil['id_profesional']
        );

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true, 
            'nuevas' => count($solicitudes)
        ]);
        exit;
    }


    /* ========================================================
       ENDPOINT AJAX: ACTUALIZAR UBICACIÓN GPS DEL PROFESIONAL
       ======================================================== */
    public function actualizarGPS() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) exit;

        $lat = $_POST['lat'] ?? null;
        $lng = $_POST['lng'] ?? null;

        if ($lat && $lng) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE profesionales SET latitud_actual = :lat, longitud_actual = :lng WHERE id_usuario = :id_user");
            $stmt->execute([':lat' => $lat, ':lng' => $lng, ':id_user' => (int)$_SESSION['user_id']]);
            echo json_encode(['ok' => true]);
        }
        exit;
    }
}