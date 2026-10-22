<?php
/**
 * FixGeo - Configuración general.
 * Detecta si estás en XAMPP (local) o en producción y carga el archivo de entorno correspondiente.
 */

// ---- 1. Entorno ----
$fxHost = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));
$fxEsLocal = (bool) preg_match(
    '/^(localhost|127\.0\.0\.1|\[::1\]|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+|172\.(1[6-9]|2\d|3[01])\.\d+\.\d+)$/',
    $fxHost
);

if (PHP_SAPI === 'cli') {
    define('APP_ENV', getenv('FIXGEO_ENV') ?: 'production');
} else {
    define('APP_ENV', $fxEsLocal ? 'local' : 'production');
}
unset($fxHost, $fxEsLocal);

require __DIR__ . '/env.' . (APP_ENV === 'local' ? 'local' : 'production') . '.php';

// ---- 2. URL base (se calcula sola si el entorno no la fijó) ----
if (!defined('BASE_URL')) {
    $https   = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $carpeta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    define('BASE_URL', ($https ? 'https' : 'http') . '://' . $host . $carpeta);
    unset($https, $host, $carpeta);
}

// ---- 3. Ajustes comunes ----
define('APP_DEBUG', APP_ENV === 'local');
define('DB_CHARSET', 'utf8mb4');
define('CSRF_ENFORCE', false); // pasa a true cuando todos los formularios lleven Security::campo()

date_default_timezone_set('America/La_Paz');
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');