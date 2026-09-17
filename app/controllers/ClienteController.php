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

    /* ========================================================
       2. MÓDULO: BÚSQUEDA Y ASIGNACIÓN (Error SQL Arreglado)
       ======================================================== */
    public function buscarEspecialista() {
        $idUsuario = (int) $_SESSION['user_id'];
        $descripcionProblema = $_POST['descripcion'] ?? ''; 
        $idCategoriaDirecta = $_GET['cat'] ?? null; 
        $idProfSeleccionado = (int) ($_GET['id_prof'] ?? 0);

        $db = Database::getInstance()->getConnection();
        
        $categorias = $db->query("SELECT * FROM categorias WHERE estado = 1")->fetchAll(PDO::FETCH_ASSOC);

        $categoriaDetectada = null;
        $esBusquedaIA = false; 

        if ($idProfSeleccionado > 0) {
            // Si viene del catálogo manual, obtenemos la categoría del profesional directamente
            $stmt = $db->prepare("SELECT c.* FROM categorias c INNER JOIN profesionales p ON c.id_categoria = p.id_categoria WHERE p.id_profesional = :id");
            $stmt->execute([':id' => $idProfSeleccionado]);
            $categoriaDetectada = $stmt->fetch(PDO::FETCH_ASSOC);
        } else if ($idCategoriaDirecta) {
            $stmt = $db->prepare("SELECT * FROM categorias WHERE id_categoria = :id");
            $stmt->execute([':id' => $idCategoriaDirecta]);
            $categoriaDetectada = $stmt->fetch(PDO::FETCH_ASSOC);
        } else if (!empty($descripcionProblema)) {
            $esBusquedaIA = true;
            
            // SIMULADOR DE LLM MEJORADO: Detección inteligente con tolerancia a errores (Typos y Sinónimos)
            $unwanted_array = ['Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ñ'=>'N', 'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n'];
            
            // 1. Limpieza de texto (Normalización)
            $descLimpia = strtolower(strtr($descripcionProblema, $unwanted_array));
            $descLimpia = preg_replace('/[^a-z0-9\s]/', '', $descLimpia);
            $palabrasCliente = explode(' ', $descLimpia); // Tokenización
            
            // 2. Base de conocimientos (Diccionario de Sinónimos)
            $diccionario = [
                'electr' => ['luz', 'lus', 'luces', 'electric', 'elec', 'chipa', 'chispa', 'enchufe', 'enchuf', 'cable', 'cables', 'cortocircuito', 'foco', 'focos', 'iluminacion', 'apagon', 'quemado', 'quemo', 'electrocuta', 'corriente', 'energia'],
                'plomer' => ['agua', 'ava', 'tubo', 'tuberia', 'fuga', 'gotea', 'gotera', 'inodoro', 'banio', 'bano', 'cano', 'caneria', 'pileta', 'lavamano', 'ducha', 'desague', 'inunda', 'humedad', 'plomero', 'plomo', 'gas', 'calefon'],
                'jardin' => ['jardin', 'pasto', 'cesped', 'cespe', 'arbol', 'arboles', 'planta', 'plantas', 'tierra', 'hojas', 'poda', 'podar', 'regar', 'riego', 'maleza', 'flores', 'flor'],
                'cerraj' => ['llave', 'yave', 'yaves', 'chapa', 'puerta', 'cerrojo', 'candado', 'trancado', 'tranco', 'abrir', 'cerrado', 'cerrajero'],
                'alban'  => ['pared', 'ladrillo', 'cemento', 'techo', 'construc', 'piso', 'ceramica', 'azulejo', 'grieta', 'rajadura', 'pintura', 'pintar', 'obra', 'albanil', 'muralla']
            ];

            $mejorCoincidencia = null;
            $maxPuntaje = 0;

            // 3. Motor de Inferencia (Fuzzy Matching con Distancia de Levenshtein)
            foreach ($categorias as $cat) {
                $nombreCat = strtolower(strtr($cat['nombre_categoria'], $unwanted_array));
                $puntaje = 0;
                
                $palabrasClave = [$nombreCat]; // Iniciamos con el nombre real de la categoría
                foreach ($diccionario as $llaveDic => $palabras) {
                    if (str_contains($nombreCat, $llaveDic)) {
                        $palabrasClave = array_merge($palabrasClave, $palabras);
                        break;
                    }
                }

                foreach ($palabrasCliente as $palabra) {
                    if (strlen($palabra) < 3) continue; // Ignorar conectores cortos (de, la, el)
                    
                    foreach ($palabrasClave as $clave) {
                        // Coincidencia exacta o contenida (ej: "enchufes" contiene "enchuf")
                        if (str_contains($palabra, $clave) || str_contains($clave, $palabra)) {
                            $puntaje += 10;
                        } else {
                            // Algoritmo Levenshtein: Calcula cuántas letras hay de diferencia (Typos)
                            // Si escribe "luses" en vez de "luces", la distancia es 1.
                            $distancia = levenshtein($palabra, $clave);
                            if ($distancia === 1 || ($distancia === 2 && strlen($clave) >= 5)) {
                                $puntaje += 5; // Suma puntos si es un error ortográfico muy parecido
                            }
                        }
                    }
                }
                
                if ($puntaje > $maxPuntaje) {
                    $maxPuntaje = $puntaje;
                    $mejorCoincidencia = $cat;
                }
            }
            
            // Si el modelo no logra entender (puntaje 0), devuelve la primera categoría por defecto
            $categoriaDetectada = ($mejorCoincidencia) ? $mejorCoincidencia : (count($categorias) > 0 ? $categorias[0] : null);
        }

        $listaProfesionales = [];
        if ($categoriaDetectada) {
            // ERROR ARREGLADO: Ahora usamos :id_usuario_fav y :id_usuario_propio para que PDO no se confunda
            $stmtProf = $db->prepare("
                SELECT p.id_profesional, u.nombre, u.apellido,
                       pl.nombre_plan, pl.posicionamiento_destacado,
                       p.descripcion_servicio, p.tarifa_base,
                       COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas,
                       (SELECT COUNT(*) FROM clientes_favoritos cf 
                        INNER JOIN clientes c ON cf.id_cliente = c.id_cliente 
                        WHERE cf.id_profesional = p.id_profesional AND c.id_usuario = :id_usuario_fav) as es_favorito
                FROM profesionales p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
                LEFT JOIN vw_metricas_profesionales vw ON p.id_profesional = vw.id_profesional
                WHERE p.id_categoria = :id_categoria 
                  AND p.estado_validacion = 'APROBADO' 
                  AND p.estado_disponibilidad = 'DISPONIBLE'
                  AND p.tokens_disponibles > 0
                  AND p.id_usuario != :id_usuario_propio
                ORDER BY es_favorito DESC, pl.posicionamiento_destacado DESC, pl.precio DESC, promedio_estrellas DESC
            ");
            
            $stmtProf->execute([
                ':id_categoria' => $categoriaDetectada['id_categoria'],
                ':id_usuario_fav' => $idUsuario,
                ':id_usuario_propio' => $idUsuario
            ]);
            $listaProfesionales = $stmtProf->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmtC = $db->prepare("SELECT direccion_referencia, zona FROM clientes WHERE id_usuario = :id_usuario");
        $stmtC->execute([':id_usuario' => $idUsuario]);
        $clienteInfo = $stmtC->fetch(PDO::FETCH_ASSOC);

        $this->view('cliente/buscar_especialista', [
            'titulo' => 'GEO-PRO | Detalles del Servicio',
            'problemaOriginal' => $descripcionProblema,
            'categoria' => $categoriaDetectada,
            'profesionales' => $listaProfesionales,
            'esBusquedaIA' => $esBusquedaIA,
            'clienteInfo' => $clienteInfo,
            'idProfSeleccionado' => $idProfSeleccionado
        ]);
    }

    public function registrarPedido() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/cliente/dashboard");
            exit;
        }

        $idUsuario = (int) $_SESSION['user_id'];
        $idCategoria = (int) $_POST['id_categoria'];
        $descripcion = trim($_POST['descripcion']);
        $tipoAsignacion = $_POST['tipo_asignacion']; 
        $idProfesionalSeleccionado = (int) ($_POST['id_profesional'] ?? 0);

        try {
            $db = Database::getInstance()->getConnection();

            $stmtC = $db->prepare("SELECT id_cliente, zona, direccion_referencia FROM clientes WHERE id_usuario = :id_usuario");
            $stmtC->execute([':id_usuario' => $idUsuario]);
            $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);

            $codigoSeguimiento = 'GEO-' . strtoupper(substr(md5(uniqid()), 0, 8));

            if ($tipoAsignacion === 'MANUAL' && $idProfesionalSeleccionado > 0) {
                $stmtSol = $db->prepare("
                    INSERT INTO solicitudes_servicio 
                    (codigo_seguimiento, id_cliente, id_profesional, descripcion_problema, direccion_servicio, macrodistrito, zona, latitud_destino, longitud_destino, estado_servicio) 
                    VALUES (:codigo, :idc, :idp, :desc, :dir, 'CENTRO', :zona, -16.5000, -68.1500, 'PENDIENTE')
                ");
                $stmtSol->execute([
                    ':codigo' => $codigoSeguimiento,
                    ':idc' => $cliente['id_cliente'],
                    ':idp' => $idProfesionalSeleccionado,
                    ':desc' => $descripcion,
                    ':dir' => $cliente['direccion_referencia'],
                    ':zona' => $cliente['zona']
                ]);
            } else {
                $stmtIA = $db->prepare("
                    SELECT p.id_profesional 
                    FROM profesionales p
                    INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
                    WHERE p.id_categoria = :id_cat AND p.id_usuario != :id_usu AND p.estado_disponibilidad = 'DISPONIBLE'
                    ORDER BY pl.posicionamiento_destacado DESC, pl.precio DESC, RAND() LIMIT 1
                ");
                $stmtIA->execute([':id_cat' => $idCategoria, ':id_usu' => $idUsuario]);
                $mejorProfesional = $stmtIA->fetchColumn();

                if (!$mejorProfesional) {
                    header("Location: " . BASE_URL . "/cliente/dashboard?error=sin_profesionales");
                    exit;
                }

                $stmtSol = $db->prepare("
                    INSERT INTO solicitudes_servicio 
                    (codigo_seguimiento, id_cliente, id_profesional, descripcion_problema, direccion_servicio, macrodistrito, zona, latitud_destino, longitud_destino, estado_servicio) 
                    VALUES (:codigo, :idc, :idp, :desc, :dir, 'CENTRO', :zona, -16.5000, -68.1500, 'PENDIENTE')
                ");
                $stmtSol->execute([
                    ':codigo' => $codigoSeguimiento,
                    ':idc' => $cliente['id_cliente'],
                    ':idp' => $mejorProfesional,
                    ':desc' => "[ASIGNACIÓN I.A. CASCADA] - " . $descripcion,
                    ':dir' => $cliente['direccion_referencia'],
                    ':zona' => $cliente['zona']
                ]);
            }

            header("Location: " . BASE_URL . "/cliente/dashboard?success=ok");
            exit;

        } catch (Exception $e) {
            header("Location: " . BASE_URL . "/cliente/dashboard?error=db");
            exit;
        }
    }

    /* ========================================================
       3. MIS FAVORITOS
       ======================================================== */
    public function favoritos() {
        $idUsuario = (int) $_SESSION['user_id'];
        $db = Database::getInstance()->getConnection();

        $stmtC = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id_usuario");
        $stmtC->execute([':id_usuario' => $idUsuario]);
        $idCliente = $stmtC->fetchColumn();

        $favoritos = [];
        if ($idCliente) {
            $stmtFav = $db->prepare("
                SELECT f.id_favorito, p.id_profesional, u.nombre, u.apellido, 
                       c.nombre_categoria, c.icono_fa, pl.nombre_plan,
                       COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas
                FROM clientes_favoritos f
                INNER JOIN profesionales p ON f.id_profesional = p.id_profesional
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
                LEFT JOIN vw_metricas_profesionales vw ON p.id_profesional = vw.id_profesional
                WHERE f.id_cliente = :id_cliente
                ORDER BY f.fecha_agregado DESC
            ");
            $stmtFav->execute([':id_cliente' => $idCliente]);
            $favoritos = $stmtFav->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('cliente/favoritos', [
            'titulo' => 'GEO-PRO | Mis Favoritos',
            'favoritos' => $favoritos
        ]);
    }

    public function eliminarFavorito($idFavorito) {
        if (!isset($_SESSION['user_id'])) { header("Location: " . BASE_URL . "/auth/login"); exit; }
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM clientes_favoritos WHERE id_favorito = :id");
            $stmt->execute([':id' => (int) $idFavorito]);
            header("Location: " . BASE_URL . "/cliente/favoritos?success=eliminado");
        } catch (Exception $e) { header("Location: " . BASE_URL . "/cliente/favoritos?error=db"); }
        exit;
    }
    public function agregarFavorito($idProfesional) {
        if (!isset($_SESSION['user_id'])) { 
            header("Location: " . BASE_URL . "/auth/login"); 
            exit; 
        }
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar el ID del cliente actual
            $stmtC = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id_usuario");
            $stmtC->execute([':id_usuario' => (int)$_SESSION['user_id']]);
            $idCliente = $stmtC->fetchColumn();

            if ($idCliente) {
                // INSERT IGNORE evita que se duplique si el cliente hace doble clic
                $stmt = $db->prepare("INSERT IGNORE INTO clientes_favoritos (id_cliente, id_profesional) VALUES (:idc, :idp)");
                $stmt->execute([':idc' => $idCliente, ':idp' => (int)$idProfesional]);
            }
            
            // Lo devolvemos a la misma página donde estaba
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } catch (Exception $e) { 
            header("Location: " . $_SERVER['HTTP_REFERER']); 
        }
        exit;
    }

  /* ========================================================
       4. HISTORIAL DE SOLICITUDES (Corregido: Muestra TODAS)
       ======================================================== */
    public function historial() {
        $idUsuario = (int) $_SESSION['user_id'];
        $db = Database::getInstance()->getConnection();
        
        $stmtC = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id_usuario");
        $stmtC->execute([':id_usuario' => $idUsuario]);
        $idCliente = $stmtC->fetchColumn();

        $pedidos = [];
        if ($idCliente) {
            // CORRECCIÓN: Quitamos el filtro de "FINALIZADA" para que muestre TODAS las solicitudes
            $stmtSol = $db->prepare("
                SELECT s.*, c.nombre_categoria, u.nombre as prof_nombre, u.apellido as prof_apellido, 
                       COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas
                FROM solicitudes_servicio s
                INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                LEFT JOIN vw_metricas_profesionales vw ON p.id_profesional = vw.id_profesional
                WHERE s.id_cliente = :id_cliente 
                ORDER BY s.fecha_solicitud DESC
            ");
            $stmtSol->execute([':id_cliente' => $idCliente]);
            $pedidos = $stmtSol->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('cliente/historial', [
            'titulo' => 'GEO-PRO | Mis Solicitudes',
            'pedidos' => $pedidos
        ]);
    }

   /* ========================================================
       5. PERFIL Y DIRECCIONES (Protegido contra cuelgues)
       ======================================================== */
    public function perfil() {
        $idUsuario = (int) $_SESSION['user_id'];
        $db = Database::getInstance()->getConnection();
        
        $stmtC = $db->prepare("
            SELECT c.*, u.nombre, u.apellido, u.correo, u.celular 
            FROM clientes c 
            INNER JOIN usuarios u ON c.id_usuario = u.id_usuario 
            WHERE c.id_usuario = :id_usuario
        ");
        $stmtC->execute([':id_usuario' => $idUsuario]);
        $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $zona = trim($_POST['zona'] ?? '');
                $direccion = trim($_POST['direccion_referencia'] ?? '');
                $lat = (float) ($_POST['latitud'] ?? -16.500000);
                $lng = (float) ($_POST['longitud'] ?? -68.150000);
                
                $check = $db->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = :id");
                $check->execute([':id' => $idUsuario]);
                
                if ($check->fetchColumn()) {
                    $stmtU = $db->prepare("UPDATE clientes SET zona = :zona, direccion_referencia = :dir, latitud_predeterminada = :lat, longitud_predeterminada = :lng WHERE id_usuario = :id");
                    $stmtU->execute([':zona' => $zona, ':dir' => $direccion, ':lat' => $lat, ':lng' => $lng, ':id' => $idUsuario]);
                } else {
                    $stmtI = $db->prepare("INSERT INTO clientes (id_usuario, zona, direccion_referencia, latitud_predeterminada, longitud_predeterminada) VALUES (:id, :zona, :dir, :lat, :lng)");
                    $stmtI->execute([':id' => $idUsuario, ':zona' => $zona, ':dir' => $direccion, ':lat' => $lat, ':lng' => $lng]);
                }
                
                header("Location: " . BASE_URL . "/cliente/perfil?success=ok");
                exit;
            } catch (Exception $e) {
                // SI FALLA, MUESTRA EL ERROR EN PANTALLA EN LUGAR DE CONGELARSE
                die("<h2 style='color:red;'>Error de Base de Datos en Perfil: " . $e->getMessage() . "</h2>");
            }
        }

        $this->view('cliente/perfil', [
            'titulo' => 'GEO-PRO | Mis Direcciones',
            'cliente' => $cliente
        ]);
    }

    /* ========================================================
       ENDPOINT AJAX: RASTREAR AL PROFESIONAL ASIGNADO
       ======================================================== */
    public function rastrearProfesional($idSolicitud) {
        if (!isset($_SESSION['user_id'])) exit;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.latitud_actual, p.longitud_actual 
            FROM solicitudes_servicio s
            INNER JOIN profesionales p ON s.id_profesional = p.id_profesional
            WHERE s.id_solicitud = :id_sol
        ");
        $stmt->execute([':id_sol' => (int)$idSolicitud]);
        $coords = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($coords ?: ['latitud_actual' => null, 'longitud_actual' => null]);
        exit;
    }
    /* ========================================================
       BUSCADOR MANUAL DE ESPECIALISTAS (Catálogo)
       ======================================================== */
    public function especialistas() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // Filtro opcional por categoría
        $filtroCategoria = (int) ($_GET['categoria'] ?? 0);
        $params = [];
        
        $sql = "
            SELECT p.id_profesional, u.nombre, u.apellido, c.nombre_categoria,
                   pl.nombre_plan, pl.posicionamiento_destacado,
                   p.tarifa_base, p.macrodistrito_base, p.zona_especifica,
                   COALESCE(vw.promedio_estrellas, 5.0) AS promedio_estrellas,
                   COALESCE(vw.total_resenas, 0) AS total_resenas
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
            LEFT JOIN vw_metricas_profesionales vw ON p.id_profesional = vw.id_profesional
            WHERE p.estado_validacion = 'APROBADO' 
              AND p.estado_disponibilidad = 'DISPONIBLE'
              AND p.tokens_disponibles > 0
        ";

        if ($filtroCategoria > 0) {
            $sql .= " AND p.id_categoria = :id_categoria";
            $params[':id_categoria'] = $filtroCategoria;
        }

        // ORDEN CRÍTICO DE NEGOCIO: Premium primero, mejores calificados después
        $sql .= " ORDER BY pl.posicionamiento_destacado DESC, pl.id_plan DESC, promedio_estrellas DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $especialistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtenemos categorías para el select del filtro
        $categorias = $db->query("SELECT id_categoria, nombre_categoria FROM categorias WHERE estado = 1 ORDER BY nombre_categoria ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('cliente/especialistas', [
            'titulo' => 'GEO-PRO | Catálogo de Profesionales',
            'especialistas' => $especialistas,
            'categorias' => $categorias,
            'categoriaActual' => $filtroCategoria
        ]);
    }

    /* ========================================================
       CONEXIÓN: ENVIAR AL CLIENTE A CREAR EL PEDIDO MANUAL
       ======================================================== */
    public function solicitarManual() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/cliente/especialistas");
            exit;
        }

        $idProfesional = (int) ($_POST['id_profesional'] ?? 0);

        if ($idProfesional > 0) {
            // Redirige al formulario universal, pasándole el ID del técnico
            header("Location: " . BASE_URL . "/cliente/buscarEspecialista?id_prof=" . $idProfesional);
            exit;
        }

        header("Location: " . BASE_URL . "/cliente/especialistas?error=id_invalido");
        exit;
    }
}