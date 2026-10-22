<?php
/** FixGeo - Diagnóstico LOCAL. Bórralo antes de subir al hosting. */
require_once __DIR__ . '/../app/config/config.php';
if (APP_ENV !== 'local') { http_response_code(404); exit; }

$filas = [];
$ok = function (string $nombre, bool $bien, string $detalle = '') use (&$filas) {
    $filas[] = [$nombre, $bien, $detalle];
};

$ok('PHP 8.1 o superior', PHP_VERSION_ID >= 80100, PHP_VERSION);
foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'openssl'] as $ext) {
    $ok("Extensión $ext", extension_loaded($ext));
}
$ok('Entorno', true, APP_ENV . ' (debug ' . (APP_DEBUG ? 'sí' : 'no') . ')');
$ok('BASE_URL detectada', true, BASE_URL);
$ok('Base configurada', true, DB_HOST . ':' . DB_PORT . ' / ' . DB_NAME . ' / usuario ' . DB_USER);

$archivos = [
    'core/Security.php', 'core/Validador.php', 'core/Catalogo.php', 'core/Flash.php', 'core/Controller.php', 'core/Router.php',
    'app/controllers/AuthController.php', 'app/views/layouts/header.php', 'app/views/layouts/footer.php',
    'app/views/layouts/auth_header.php', 'app/views/layouts/alertas.php', 'app/views/auth/login.php',
    'public/css/fixgeo-theme.css', 'public/css/style.css', 'public/css/auth.css', 'public/js/auth-validation.js',
];
foreach ($archivos as $a) {
    $ok("Archivo $a", is_file(__DIR__ . '/../' . $a));
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
    $ok('Conexión a MySQL', true, 'Servidor ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION));

    foreach (['usuarios', 'clientes', 'profesionales', 'solicitudes_servicio', 'categorias', 'planes_suscripcion'] as $t) {
        try {
            $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $ok("Tabla $t", true, "$n filas");
        } catch (PDOException $e) {
            $ok("Tabla $t", false, 'No existe o sin permiso');
        }
    }
    $trg = $pdo->query("SHOW TRIGGERS WHERE `Trigger` IN ('trg_validar_profesional_solicitud','trg_bloquear_autoaceptacion')")->fetchAll();
    $ok('Triggers anti auto-solicitud', count($trg) === 2, count($trg) . ' de 2');
    $ok('Columna clientes.macrodistrito', (bool) $pdo->query("SHOW COLUMNS FROM clientes LIKE 'macrodistrito'")->fetch());
    $sch = $pdo->query("SHOW VARIABLES LIKE 'event_scheduler'")->fetch(PDO::FETCH_NUM);
    $ok('Programador de eventos', ($sch[1] ?? '') === 'ON', $sch[1] ?? '?');
} catch (PDOException $e) {
    $ok('Conexión a MySQL', false, $e->getMessage());
}

foreach (['uploads', 'uploads/documentos', 'uploads/chat'] as $d) {
    $ruta = __DIR__ . '/' . $d;
    if (!is_dir($ruta)) { @mkdir($ruta, 0755, true); }
    $ok("Carpeta public/$d escribible", is_dir($ruta) && is_writable($ruta));
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>FixGeo | Diagnóstico</title>
<style>
body{font-family:system-ui,sans-serif;background:#f6f8fc;color:#0f172a;max-width:860px;margin:2rem auto;padding:0 1rem}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,.05)}
td{padding:.55rem .8rem;border-bottom:1px solid #e3e8f2;font-size:.9rem}
.s{font-weight:700;width:2rem;text-align:center}.bien{color:#198754}.mal{color:#dc3545}
</style></head><body>
<h1>FixGeo · Diagnóstico local</h1>
<table>
<?php foreach ($filas as [$nombre, $bien, $detalle]): ?>
<tr><td class="s <?= $bien ? 'bien' : 'mal' ?>"><?= $bien ? '✔' : '✘' ?></td><td><?= htmlspecialchars($nombre) ?></td><td><?= htmlspecialchars($detalle) ?></td></tr>
<?php endforeach; ?>
</table>
<p>Todo en verde = listo para probar el sistema. Borra este archivo antes de subir al hosting.</p>
</body></html>