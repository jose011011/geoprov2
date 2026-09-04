<?php
class BusquedaController extends Controller {
    private Busqueda $busquedaModel;

public function __construct() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/auth/login");
        exit;
    }
    if (!in_array((int) $_SESSION['role_id'], [3, 4], true)) {
        header("Location: " . BASE_URL . "/admin/dashboard");
        exit;
    }
    $this->busquedaModel = new Busqueda();
}
public function resultados($slug = null) {
    if (!$slug) {
        header("Location: " . BASE_URL . "/cliente/dashboard");
        exit;
    }

    $categoria = $this->busquedaModel->obtenerCategoriaPorSlug($slug);
    if (!$categoria) {
        header("Location: " . BASE_URL . "/cliente/dashboard?error=categoria_no_existe");
        exit;
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT latitud_predeterminada, longitud_predeterminada FROM clientes WHERE id_usuario = :id LIMIT 1");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $cliente = $stmt->fetch();

    $lat = $cliente['latitud_predeterminada'] ?? -16.5;
    $lng = $cliente['longitud_predeterminada'] ?? -68.15;

    $errorSql = null;
    $profesionales = [];
    try {
        $profesionales = $this->busquedaModel->buscarPorCategoria($slug, (float) $lat, (float) $lng);
    } catch (Exception $e) {
        $errorSql = "Ocurrió un error al buscar profesionales. Intenta de nuevo.";
        // Para depurar en desarrollo, descomenta la siguiente línea:
        // $errorSql .= ' DEBUG: ' . $e->getMessage();
    }

    $this->view('cliente/resultados', [
        'titulo'        => 'GEO-PRO | ' . $categoria['nombre_categoria'],
        'categoria'     => $categoria,
        'profesionales' => $profesionales,
        'errorSql'      => $errorSql,
        'slug'          => $slug
    ]);
}
public function inteligente() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/cliente/dashboard");
        exit;
    }

    $texto = trim($_POST['descripcion'] ?? '');
    if (mb_strlen($texto) < 5) {
        header("Location: " . BASE_URL . "/cliente/dashboard?error=descripcion_corta");
        exit;
    }

    // Ahora usa Gemini con fallback automático a palabras clave
    $slug = $this->busquedaModel->detectarCategoriaConIA($texto);

    if ($slug) {
        header("Location: " . BASE_URL . "/busqueda/resultados/" . $slug);
    } else {
        header("Location: " . BASE_URL . "/cliente/dashboard?error=sin_coincidencia");
    }
    exit;
}
}