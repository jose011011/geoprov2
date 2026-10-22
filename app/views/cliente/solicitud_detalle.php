<?php
require __DIR__ . '/../layouts/header.php';

$id        = (int) $solicitud['id_solicitud'];
$estado    = $solicitud['estado_servicio'];
$esCliente = (int) $solicitud['cliente_id_usuario'] === (int) $_SESSION['user_id'];
$conTecnico = !empty($solicitud['id_profesional']);
$activo    = in_array($estado, ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'], true);

$pasos    = ['PENDIENTE' => 'Enviada', 'ACEPTADA' => 'Aceptada', 'EN_CAMINO' => 'En camino', 'EN_PROCESO' => 'En proceso', 'FINALIZADA' => 'Finalizada'];
$posicion = array_search($estado, array_keys($pasos), true);

// Contacto de la otra persona (solo mientras el servicio está activo)
$otroNombre  = $esCliente ? trim(($solicitud['prof_nombre'] ?? '') . ' ' . ($solicitud['prof_apellido'] ?? '')) : trim($solicitud['cliente_nombre'] . ' ' . $solicitud['cliente_apellido']);
$otroCelular = $esCliente ? ($solicitud['prof_celular'] ?? '') : ($solicitud['cliente_celular'] ?? '');

// Enlace al chat: se detecta el método disponible en tu ChatController
$metodoChat = null;
if (class_exists('ChatController')) {
    foreach (['conversacion', 'ver', 'abrir', 'index'] as $m) {
        if (method_exists('ChatController', $m)) { $metodoChat = $m; break; }
    }
}
$urlChat = $metodoChat ? BASE_URL . '/chat/' . $metodoChat . '/' . $id : null;
?>

<div class="container py-4" style="max-width: 920px;">
    <a href="<?= BASE_URL . ($esCliente ? '/solicitud/misSolicitudes' : '/profesional/solicitudes') ?>" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 mb-3">
        <div>
            <h1 class="fx-page-title h3 mb-0"><?= Security::e($solicitud['nombre_categoria'] ?? 'Servicio') ?></h1>
            <div class="text-muted small">Código <?= Security::e($solicitud['codigo_seguimiento']) ?> · <?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></div>
        </div>
        <span class="fs-6"><?= Catalogo::badgeEstado($estado) ?></span>
    </div>

    <?php if ($posicion !== false): ?>
        <div class="card mb-3"><div class="card-body">
            <ol class="fx-steps mb-0">
                <?php $i = 0; foreach ($pasos as $clave => $texto):
                    $clase = ($i < $posicion) ? 'done' : (($i === $posicion) ? 'current' : ''); ?>
                    <li class="<?= $clase ?>"><?= $texto ?></li>
                <?php $i++; endforeach; ?>
            </ol>
            <?php if ($estado === 'PENDIENTE' && !$conTecnico): ?>
                <div class="text-center text-muted small mt-3"><i class="fa-solid fa-spinner fa-spin me-1"></i>Buscando al mejor técnico para ti. Esta página se actualiza sola.</div>
            <?php elseif ($estado === 'PENDIENTE'): ?>
                <div class="text-center text-muted small mt-3">Esperando que el técnico acepte tu solicitud.</div>
            <?php elseif (in_array($estado, ['ACEPTADA', 'EN_CAMINO'], true) && !empty($solicitud['tiempo_estimado_llegada_min'])): ?>
                <div class="text-center small mt-3"><i class="fa-regular fa-clock me-1"></i>Llegada estimada: <strong><?= (int) $solicitud['tiempo_estimado_llegada_min'] ?> min</strong></div>
            <?php endif; ?>
        </div></div>
    <?php elseif ($estado === 'CANCELADA'): ?>
        <div class="alert alert-secondary"><i class="fa-solid fa-ban me-2"></i>Esta solicitud fue cancelada.</div>
    <?php endif; ?>

    <?php if ($activo): ?>
        <div class="card mb-3"><div class="card-body p-2">
            <div id="mapaSeguimiento" style="height: 320px; border-radius: 14px;"></div>
            <?php if ($esCliente && $estado === 'EN_CAMINO'): ?>
                <div class="small text-muted text-center mt-2" id="estadoMapa"><i class="fa-solid fa-satellite-dish me-1"></i>Buscando la ubicación del técnico...</div>
            <?php endif; ?>
        </div></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1"><?= $esCliente ? 'Tu técnico' : 'Cliente' ?></div>
            <?php if ($otroNombre !== ''): ?>
                <div class="fw-bold fs-5"><?= Security::e($otroNombre) ?></div>
                <?php if ($activo && $otroCelular !== ''): ?>
                    <a class="btn btn-outline-primary btn-sm mt-2 me-1" href="tel:+591<?= Security::e($otroCelular) ?>"><i class="fa-solid fa-phone me-1"></i>Llamar</a>
                <?php endif; ?>
                <?php if ($urlChat && in_array($estado, ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO', 'FINALIZADA'], true)): ?>
                    <a class="btn btn-primary btn-sm mt-2" href="<?= $urlChat ?>"><i class="fa-regular fa-comments me-1"></i>Chat</a>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-muted">Aún sin asignar</div>
            <?php endif; ?>
        </div></div></div>

        <div class="col-md-6"><div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Lugar del servicio</div>
            <div class="fw-semibold"><?= Security::e($solicitud['direccion_servicio']) ?></div>
            <div class="small text-muted"><?= Security::e($solicitud['zona']) ?> · <?= Security::e(Catalogo::macroEtiqueta($solicitud['macrodistrito'])) ?></div>
            <?php if ($estado === 'FINALIZADA' && !empty($solicitud['precio_acordado'])): ?>
                <div class="mt-2">Precio final: <strong class="text-primary">Bs <?= number_format((float) $solicitud['precio_acordado'], 2) ?></strong></div>
            <?php endif; ?>
        </div></div></div>
    </div>

    <div class="card mb-3"><div class="card-body">
        <div class="small text-muted mb-1">Problema descrito</div>
        <div><?= nl2br(Security::e($solicitud['descripcion_problema'])) ?></div>
    </div></div>

    <div class="d-flex flex-wrap gap-2">
        <?php if ($esCliente && in_array($estado, ['PENDIENTE', 'ACEPTADA'], true)): ?>
            <form method="post" action="<?= BASE_URL ?>/solicitud/cancelar/<?= $id ?>" class="m-0"
                  onsubmit="return confirm('¿Seguro que quieres cancelar esta solicitud?');">
                <?= Security::campo() ?>
                <button class="btn btn-outline-danger"><i class="fa-solid fa-ban me-1"></i>Cancelar solicitud</button>
            </form>
        <?php endif; ?>

        <?php if ($esCliente && $estado === 'FINALIZADA' && empty($yaCalificada)): ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/solicitud/calificar/<?= $id ?>"><i class="fa-solid fa-star me-1"></i>Calificar el servicio</a>
        <?php elseif ($esCliente && $estado === 'FINALIZADA'): ?>
            <span class="text-muted small align-self-center"><i class="fa-solid fa-circle-check text-success me-1"></i>Ya calificaste este servicio.</span>
        <?php endif; ?>
    </div>
</div>

<?php if ($activo): ?>
<script>
(function () {
    const destino = [<?= (float) $solicitud['latitud_destino'] ?>, <?= (float) $solicitud['longitud_destino'] ?>];
    const mapa = L.map('mapaSeguimiento', { zoomControl: true }).setView(destino, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
    L.circleMarker(destino, { radius: 10, color: '#1d3fb4', fillColor: '#38bdf8', fillOpacity: .9, weight: 3 }).addTo(mapa).bindTooltip('Lugar del servicio');

    <?php if ($esCliente && $estado === 'EN_CAMINO'): ?>
    let marcador = null;
    const aviso = document.getElementById('estadoMapa');
    async function actualizar() {
        try {
            const r = await fetch(FX.base + '/cliente/rastrearProfesional/<?= $id ?>', { headers: { 'Accept': 'application/json' } });
            const d = await r.json();
            if (d.latitud_actual && d.longitud_actual) {
                const pos = [parseFloat(d.latitud_actual), parseFloat(d.longitud_actual)];
                if (!marcador) {
                    marcador = L.circleMarker(pos, { radius: 11, color: '#fff', fillColor: '#f59e0b', fillOpacity: 1, weight: 3 }).addTo(mapa).bindTooltip('Tu técnico');
                    mapa.fitBounds([pos, destino], { padding: [40, 40] });
                } else {
                    marcador.setLatLng(pos);
                }
                aviso.innerHTML = '<i class="fa-solid fa-satellite-dish me-1 text-success"></i>Ubicación en vivo del técnico';
            }
        } catch (e) { /* se reintenta en 5 s */ }
    }
    actualizar();
    setInterval(() => { if (!document.hidden) actualizar(); }, 5000);
    <?php endif; ?>
})();
</script>
<?php endif; ?>

<?php if (in_array($estado, ['PENDIENTE', 'ACEPTADA', 'EN_PROCESO'], true)): ?>
<script>
// Refresca la página para mostrar cambios de estado (el mapa en vivo se actualiza aparte)
setTimeout(function () { if (!document.hidden) { location.reload(); } }, 20000);
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>