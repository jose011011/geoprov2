<?php
/**
 * FixGeo - Limpieza diaria: borra del disco las fotos de chat cuyos mensajes ya fueron eliminados (purga de 15 días).
 * Se ejecuta por línea de comandos, no desde el navegador.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';

$pdo = Database::getInstance()->getConnection();
$enUso = array_flip($pdo->query('SELECT archivo_adjunto FROM mensajes WHERE archivo_adjunto IS NOT NULL')->fetchAll(PDO::FETCH_COLUMN));

$borrados = 0;
foreach (glob(__DIR__ . '/../public/uploads/chat/CHAT_*') ?: [] as $ruta) {
    if (isset($enUso[basename($ruta)])) {
        continue;                                  // todavía pertenece a un mensaje
    }
    if (filemtime($ruta) > time() - 86400) {
        continue;                                  // margen de seguridad: archivos de las últimas 24 h
    }
    if (@unlink($ruta)) {
        $borrados++;
    }
}
echo date('Y-m-d H:i') . " - fotos de chat eliminadas: {$borrados}\n";