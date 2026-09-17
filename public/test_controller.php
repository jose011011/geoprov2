<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Admin.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

// Mock session and POST
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['nombre_categoria'] = 'Prueba API';
$_POST['tipo_clasificacion'] = 'AMBOS';
$_POST['icono_fa'] = 'fa-solid fa-wrench';
$_POST['descripcion'] = 'Prueba de creacion';

$controller = new AdminController();
try {
    // This will either redirect and exit, or we can catch it before exit if we buffer
    ob_start();
    $controller->crearCategoria();
    echo ob_get_clean();
} catch (Exception $e) {
    echo "CAUGHT EXCEPTION: " . $e->getMessage();
}

