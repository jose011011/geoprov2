<?php
/**
 * Tareas de mantenimiento. Ejecutar cada 5 a 10 minutos desde el cron del hosting:
 *   php /ruta/GEO_PRO_V2/cron/mantenimiento.php
 * En tu PC:  C:\xampp\php\php.exe C:\xampp\htdocs\GEO_PRO_V2\cron\mantenimiento.php
 * Solo funciona desde la línea de comandos.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// En consola no existe el entorno web: se simula uno local por si config.php lo consulta
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST']   ?? 'localhost';
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';

$db = Database::getInstance()->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function log_linea(string $texto): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $texto . PHP_EOL;
}

// 1) Membresías vencidas vuelven al plan gratuito (conservan sus tokens)
$n = $db->exec("
    UPDATE profesionales SET id_plan = 1, fin_suscripcion = NULL
     WHERE fin_suscripcion < CURDATE() AND id_plan <> 1
");
log_linea("Membresías vencidas degradadas: $n");

// 2) Profesionales conectados desde la app que dejaron de dar señal
$n = $db->exec("
    UPDATE profesionales SET estado_disponibilidad = 'NO_DISPONIBLE'
     WHERE estado_disponibilidad = 'DISPONIBLE'
       AND ultimo_latido_app IS NOT NULL
       AND ultimo_latido_app < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
");
log_linea("Profesionales sin señal desconectados: $n");

// 3) Chats de servicios terminados hace más de 15 días: borra fotos y mensajes
$stmt = $db->query("
    SELECT m.id_mensaje, m.archivo_adjunto
      FROM mensajes m
      INNER JOIN solicitudes_servicio s ON s.id_solicitud = m.id_solicitud
     WHERE s.estado_servicio IN ('FINALIZADA', 'CANCELADA')
       AND s.fecha_finalizacion < DATE_SUB(NOW(), INTERVAL 15 DAY)
");
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$carpeta = realpath(__DIR__ . '/../public/uploads/chat');
$archivos = 0;
$ids = [];
foreach ($filas as $f) {
    $ids[] = (int) $f['id_mensaje'];
    if (!empty($f['archivo_adjunto']) && $carpeta !== false) {
        $ruta = $carpeta . DIRECTORY_SEPARATOR . basename($f['archivo_adjunto']);
        if (is_file($ruta) && @unlink($ruta)) {
            $archivos++;
        }
    }
}
foreach (array_chunk($ids, 500) as $lote) {
    $marcas = implode(',', array_fill(0, count($lote), '?'));
    $db->prepare("DELETE FROM mensajes WHERE id_mensaje IN ($marcas)")->execute($lote);
}
log_linea('Mensajes eliminados: ' . count($ids) . " | fotos borradas del disco: $archivos");

// 4) Sesiones vencidas
$n = $db->exec('UPDATE usuarios SET api_token = NULL WHERE api_token_expira IS NOT NULL AND api_token_expira < NOW()');
log_linea("Sesiones vencidas cerradas: $n");