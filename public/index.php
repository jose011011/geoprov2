<?php
session_start();

// 1. Configuraciones principales y base de datos
require_once '../app/config/config.php';
require_once '../app/config/Database.php';

// 2. Autoloader PSR-4 / Estructurado para MVC (Carga automática de Clases, Controladores y Modelos)
spl_autoload_register(function ($clase) {
    $directorios = [
        '../core/',
        '../app/controllers/',
        '../app/models/',
        '../app/config/'
    ];

    foreach ($directorios as $dir) {
        $archivo = $dir . $clase . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
});
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 3. Iniciar la aplicación
$app = new Router();
$app->run();