<?php
/** FixGeo - Consultas del chat entre cliente y técnico. */
class ChatPanel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Datos del chat SOLO si el usuario es el cliente o el técnico asignado a esa solicitud. */
    public function conversacion(int $idSolicitud, int $idUsuario): ?array
    {
        $s = $this->db->prepare(
                        'SELECT s.id_solicitud, s.codigo_seguimiento, s.estado_servicio, s.fecha_finalizacion,
                    cl.id_usuario AS id_usuario_cliente, p.id_usuario AS id_usuario_prof,
                    uc.nombre AS cliente_nombre, uc.apellido AS cliente_apellido,
                    up.nombre AS prof_nombre, up.apellido AS prof_apellido, c.nombre_categoria
               FROM solicitudes_servicio s
              INNER JOIN clientes cl ON cl.id_cliente = s.id_cliente
              INNER JOIN usuarios uc ON uc.id_usuario = cl.id_usuario
              INNER JOIN profesionales p ON p.id_profesional = s.id_profesional
              INNER JOIN usuarios up ON up.id_usuario = p.id_usuario
               LEFT JOIN categorias c ON c.id_categoria = COALESCE(s.id_categoria, p.id_categoria)
              WHERE s.id_solicitud = :id AND (cl.id_usuario = :u1 OR p.id_usuario = :u2) LIMIT 1'
        );
        $s->execute([':id' => $idSolicitud, ':u1' => $idUsuario, ':u2' => $idUsuario]);
        $c = $s->fetch();
        if (!$c) {
            return null;
        }

        $soyCliente        = (int) $c['id_usuario_cliente'] === $idUsuario;
        $c['soy_cliente']  = $soyCliente;
        $c['id_otro']      = $soyCliente ? (int) $c['id_usuario_prof'] : (int) $c['id_usuario_cliente'];
        $c['otro_nombre']  = $soyCliente ? trim($c['prof_nombre'] . ' ' . $c['prof_apellido']) : trim($c['cliente_nombre'] . ' ' . $c['cliente_apellido']);
        $c['abierto']      = in_array($c['estado_servicio'], ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'], true);
        return $c;
    }

    public function mensajes(int $idSolicitud, int $desdeId): array
    {
        $s = $this->db->prepare(
            'SELECT id_mensaje, id_remitente, tipo_mensaje, mensaje, archivo_adjunto, fecha_envio
               FROM mensajes WHERE id_solicitud = :s AND id_mensaje > :d ORDER BY id_mensaje LIMIT 100'
        );
        $s->execute([':s' => $idSolicitud, ':d' => $desdeId]);
        return $s->fetchAll();
    }

    public function marcarLeidos(int $idSolicitud, int $idUsuario): void
    {
        $this->db->prepare('UPDATE mensajes SET leido = 1 WHERE id_solicitud = :s AND id_remitente <> :u AND leido = 0')
                 ->execute([':s' => $idSolicitud, ':u' => $idUsuario]);
    }

    /** ¿Ya hay mensajes míos sin leer en esta conversación? (para no llenar de avisos al otro usuario) */
    public function tengoSinLeer(int $idSolicitud, int $idUsuario): bool
    {
        $s = $this->db->prepare('SELECT 1 FROM mensajes WHERE id_solicitud = :s AND id_remitente = :u AND leido = 0 LIMIT 1');
        $s->execute([':s' => $idSolicitud, ':u' => $idUsuario]);
        return (bool) $s->fetchColumn();
    }

    public function enviar(int $idSolicitud, int $idUsuario, string $texto, ?string $archivo): int
    {
        $this->db->prepare(
            'INSERT INTO mensajes (id_solicitud, id_remitente, tipo_mensaje, mensaje, archivo_adjunto) VALUES (:s, :u, :t, :m, :a)'
        )->execute([
            ':s' => $idSolicitud, ':u' => $idUsuario,
            ':t' => $archivo ? 'IMAGEN' : 'TEXTO',
            ':m' => $texto !== '' ? $texto : null,
            ':a' => $archivo,
        ]);
        return (int) $this->db->lastInsertId();
    }
}