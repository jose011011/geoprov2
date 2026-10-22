<?php
/**
 *   GET  /api/chat/mensajes/{id_solicitud}?despues=ID
 *   POST /api/chat/enviar/{id_solicitud}   { texto | imagen_base64 | latitud+longitud }
 * Solo el cliente y el profesional de la solicitud.
 * Ubicación = mensaje de texto "geo:lat,lng".
 */
class ApiChatController {
    private PDO $db;

    private const MAX_POR_MINUTO = 30;
    private const ESTADOS_ESCRITURA = ['PENDIENTE', 'ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       MENSAJES (con consulta incremental: ?despues=último id recibido)
       ========================================================== */
    public function mensajes($id = 0) {
        try {
            $usuario = ApiAuth::exigir($this->db);
            $conv = $this->conversacion((int) $id, $usuario);
            $yo = (int) $usuario['id_usuario'];
            $despues = max(0, (int) ($_GET['despues'] ?? 0));

            $stmt = $this->db->prepare("
                SELECT id_mensaje, id_remitente, tipo_mensaje, mensaje,
                       archivo_adjunto, fecha_envio
                  FROM mensajes
                 WHERE id_solicitud = :s AND id_mensaje > :d
                 ORDER BY id_mensaje ASC
                 LIMIT 200
            ");
            $stmt->bindValue(':s', $conv['id_solicitud'], PDO::PARAM_INT);
            $stmt->bindValue(':d', $despues, PDO::PARAM_INT);
            $stmt->execute();
            $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Marca como leídos los del otro y apaga el aviso de "nuevo mensaje"
            $this->db->prepare("
                UPDATE mensajes SET leido = 1
                 WHERE id_solicitud = :s AND id_remitente <> :yo AND leido = 0
            ")->execute([':s' => $conv['id_solicitud'], ':yo' => $yo]);

            $this->db->prepare("
                UPDATE notificaciones SET leida = 1
                 WHERE id_usuario = :yo AND tipo = 'NUEVO_MENSAJE'
                   AND url_destino = :u AND leida = 0
            ")->execute([':yo' => $yo, ':u' => 'chat/' . $conv['id_solicitud']]);

            $this->responder([
                'ok' => true,
                'yo' => $yo,
                'otro_nombre' => $conv['otro_nombre'],
                'estado' => $conv['estado'],
                'puede_escribir' => $conv['puede_escribir'],
                'mensajes' => $mensajes,
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       ENVIAR (texto, foto o ubicación)
       ========================================================== */
    public function enviar($id = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }

        $archivoGuardado = null;
        try {
            $usuario = ApiAuth::exigir($this->db);
            $conv = $this->conversacion((int) $id, $usuario);
            $yo = (int) $usuario['id_usuario'];

            if (!$conv['puede_escribir']) {
                $this->responder(['ok' => false, 'error' =>
                    'Este chat es de solo lectura porque el servicio ya terminó.'], 409);
            }

            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            $texto = trim(strip_tags((string) ($d['texto'] ?? '')));
            $texto = mb_substr($texto, 0, 1000);
            $b64 = $d['imagen_base64'] ?? null;
            $lat = $d['latitud'] ?? null;
            $lng = $d['longitud'] ?? null;

            $tipo = 'TEXTO';
            $mensaje = $texto;
            $img = null;

            if ($b64) {
                $errores = [];
                $img = Utilidades::imagen($b64, 'tu mensaje', $errores);
                if ($errores) {
                    $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
                }
                $tipo = 'IMAGEN';
            } elseif ($lat !== null || $lng !== null) {
                if (!is_numeric($lat) || !is_numeric($lng)
                    || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    $this->responder(['ok' => false, 'error' => 'La ubicación no es válida.'], 422);
                }
                $mensaje = sprintf('geo:%.6f,%.6f', (float) $lat, (float) $lng);
            } elseif ($texto === '') {
                $this->responder(['ok' => false, 'error' => 'Escribe un mensaje.'], 422);
            }

            // Límite anti-spam
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM mensajes
                 WHERE id_remitente = :u AND fecha_envio >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            ");
            $stmt->execute([':u' => $yo]);
            if ((int) $stmt->fetchColumn() >= self::MAX_POR_MINUTO) {
                $this->responder(['ok' => false, 'error' =>
                    'Estás enviando muchos mensajes. Espera un momento.'], 429);
            }

            $adjunto = null;
            if ($img) {
                $dir = Utilidades::carpetaUploads('chat');
                $nombre = 'CHAT_' . $conv['id_solicitud'] . '_' . bin2hex(random_bytes(8)) . '.' . $img['ext'];
                if (file_put_contents($dir . $nombre, $img['bin']) === false) {
                    throw new RuntimeException('No se pudo guardar la foto.');
                }
                $archivoGuardado = $dir . $nombre;
                $adjunto = 'uploads/chat/' . $nombre;
            }

            $stmt = $this->db->prepare("
                INSERT INTO mensajes (id_solicitud, id_remitente, tipo_mensaje, mensaje, archivo_adjunto)
                VALUES (:s, :u, :t, :m, :a)
            ");
            $stmt->bindValue(':s', $conv['id_solicitud'], PDO::PARAM_INT);
            $stmt->bindValue(':u', $yo, PDO::PARAM_INT);
            $stmt->bindValue(':t', $tipo);
            if ($mensaje === '') {
                $stmt->bindValue(':m', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':m', $mensaje);
            }
            if ($adjunto === null) {
                $stmt->bindValue(':a', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':a', $adjunto);
            }
            $stmt->execute();
            $idMensaje = (int) $this->db->lastInsertId();

            // Un solo aviso pendiente por conversación (no uno por cada mensaje)
            $url = 'chat/' . $conv['id_solicitud'];
            $stmt = $this->db->prepare("
                SELECT 1 FROM notificaciones
                 WHERE id_usuario = :o AND tipo = 'NUEVO_MENSAJE' AND url_destino = :u AND leida = 0
                 LIMIT 1
            ");
            $stmt->execute([':o' => $conv['otro_user'], ':u' => $url]);
            if (!$stmt->fetchColumn()) {
                $this->db->prepare("
                    INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                    VALUES (:o, 'NUEVO_MENSAJE', :m, :u)
                ")->execute([
                    ':o' => $conv['otro_user'],
                    ':m' => 'Tienes mensajes nuevos de ' . $usuario['nombre'] . '.',
                    ':u' => $url,
                ]);
            }

            $stmt = $this->db->prepare("
                SELECT id_mensaje, id_remitente, tipo_mensaje, mensaje, archivo_adjunto, fecha_envio
                  FROM mensajes WHERE id_mensaje = :i
            ");
            $stmt->execute([':i' => $idMensaje]);

            $this->responder(['ok' => true, 'mensaje' => $stmt->fetch(PDO::FETCH_ASSOC)], 201);
        } catch (Throwable $e) {
            if ($archivoGuardado && is_file($archivoGuardado)) {
                @unlink($archivoGuardado);
            }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       AUXILIARES
       ========================================================== */

    /** Verifica que el usuario participe de la solicitud y arma los datos del chat. */
    private function conversacion(int $idSolicitud, array $usuario): array {
        $stmt = $this->db->prepare("
            SELECT s.id_solicitud, s.estado_servicio,
                   uc.id_usuario AS user_cliente,
                   CONCAT(uc.nombre, ' ', uc.apellido) AS nombre_cliente,
                   up.id_usuario AS user_pro,
                   CONCAT(up.nombre, ' ', up.apellido) AS nombre_pro
              FROM solicitudes_servicio s
              INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
              INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
              LEFT JOIN profesionales p ON p.id_profesional = s.id_profesional
              LEFT JOIN usuarios up ON up.id_usuario = p.id_usuario
             WHERE s.id_solicitud = :id
        ");
        $stmt->execute([':id' => $idSolicitud]);
        $f = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$f) {
            $this->responder(['ok' => false, 'error' => 'No encontramos esta conversación.'], 404);
        }

        $yo = (int) $usuario['id_usuario'];
        $esCliente = (int) $f['user_cliente'] === $yo;
        $esPro = $f['user_pro'] !== null && (int) $f['user_pro'] === $yo;

        if (!$esCliente && !$esPro) {
            $this->responder(['ok' => false, 'error' => 'No tienes acceso a esta conversación.'], 403);
        }
        if ($f['user_pro'] === null) {
            $this->responder(['ok' => false, 'error' =>
                'El chat se habilita cuando un profesional acepte la solicitud.'], 409);
        }

        return [
            'id_solicitud'   => (int) $f['id_solicitud'],
            'estado'         => $f['estado_servicio'],
            'otro_user'      => $esCliente ? (int) $f['user_pro'] : (int) $f['user_cliente'],
            'otro_nombre'    => $esCliente ? $f['nombre_pro'] : $f['nombre_cliente'],
            'puede_escribir' => in_array($f['estado_servicio'], self::ESTADOS_ESCRITURA, true),
        ];
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiChat] ' . $e->getMessage());
        $this->responder(['ok' => false, 'error' => 'No pudimos procesar el chat. Intenta de nuevo.'], 500);
    }
}