<?php
// public/index.php - Punto de entrada único de FixGeo
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Validador.php';

Security::boot(); // sesión segura + cabeceras

// CSRF solo para la web (la API del celular queda fuera)
if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
    Security::verificar();
}

// CORS solo en desarrollo y solo para localhost
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
if (APP_DEBUG && preg_match('#^https?://localhost(:\d+)?$#', $origen)) {
    header('Access-Control-Allow-Origin: ' . $origen);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin');
    header('Access-Control-Max-Age: 86400');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoloader (core, controladores, modelos)
spl_autoload_register(function (string $clase): void {
    if (!preg_match('/^\w+$/', $clase)) {
        return;
    }
    foreach (['core', 'app/controllers', 'app/models', 'app/config'] as $dir) {
        $archivo = __DIR__ . "/../{$dir}/{$clase}.php";
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

(new Router())->run();