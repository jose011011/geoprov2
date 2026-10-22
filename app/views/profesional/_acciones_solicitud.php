<?php
$idS     = (int) $s['id_solicitud'];
$estado  = $s['estado_servicio'];
$accion  = BASE_URL . '/profesional/cambiarEstadoSolicitud';
$directa = !empty($s['id_profesional']);
$tiempos = [10, 15, 20, 30, 45, 60];
$ocultarMapa = $ocultarMapa ?? false;
?>
<div class="d-flex flex-wrap gap-2 align-items-center">

<?php if ($estado === 'PENDIENTE'): ?>
    <form method="post" action="<?= $accion ?>" class="d-flex flex-wrap gap-2 align-items-center m-0">
        <?= Security::campo() ?>
        <input type="hidden" name="id_solicitud" value="<?= $idS ?>">
        <input type="hidden" name="estado" value="ACEPTADA">
        <select name="tiempo_estimado" class="form-select form-select-sm" style="width:auto" aria-label="Tiempo estimado de llegada">
            <?php foreach ($tiempos as $t): ?><option value="<?= $t ?>">Llego en <?= $t ?> min</option><?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-check me-1"></i>Aceptar (-1 token)</button>
    </form>
    <?php if ($directa): ?>
        <form method="post" action="<?= $accion ?>" class="m-0" onsubmit="return confirm('¿Rechazar esta solicitud?');">
            <?= Security::campo() ?>
            <input type="hidden" name="id_solicitud" value="<?= $idS ?>">
            <input type="hidden" name="estado" value="CANCELADA">
            <button class="btn btn-outline-danger btn-sm">Rechazar</button>
        </form>
    <?php endif; ?>

<?php elseif ($estado === 'ACEPTADA'): ?>
    <form method="post" action="<?= $accion ?>" class="d-flex flex-wrap gap-2 align-items-center m-0">
        <?= Security::campo() ?>
        <input type="hidden" name="id_solicitud" value="<?= $idS ?>">
        <input type="hidden" name="estado" value="EN_CAMINO">
        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-route me-1"></i>Iniciar viaje</button>
    </form>

<?php elseif ($estado === 'EN_CAMINO'): ?>
    <?php if (!$ocultarMapa): ?>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/profesional/mapaViaje/<?= $idS ?>"><i class="fa-solid fa-map-location-dot me-1"></i>Ver mapa</a>
    <?php endif; ?>
    <form method="post" action="<?= $accion ?>" class="m-0">
        <?= Security::campo() ?>
        <input type="hidden" name="id_solicitud" value="<?= $idS ?>">
        <input type="hidden" name="estado" value="EN_PROCESO">
        <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-location-dot me-1"></i>Llegué al domicilio</button>
    </form>

<?php elseif ($estado === 'EN_PROCESO'): ?>
    <form method="post" action="<?= $accion ?>" class="d-flex flex-wrap gap-2 align-items-center m-0"
          onsubmit="return confirm('¿Finalizar el trabajo y registrar el cobro?');">
        <?= Security::campo() ?>
        <input type="hidden" name="id_solicitud" value="<?= $idS ?>">
        <input type="hidden" name="estado" value="FINALIZADA">
        <div class="input-group input-group-sm" style="width:180px">
            <span class="input-group-text">Bs</span>
            <input type="number" name="precio_acordado" class="form-control" min="1" max="99999" step="0.5" placeholder="Precio final" required>
        </div>
        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-flag-checkered me-1"></i>Finalizar y cobrar</button>
    </form>
<?php endif; ?>

<?php if (in_array($estado, ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'], true)): ?>
    <form method="post" action="<?= $accion ?>" class="m-0 ms-auto" onsubmit="return confirm('¿Cancelar este trabajo?');">
        <?= Security::campo() ?>
        <input type="hidden" name="id_solicitud" value="<?= $idS ?>">
        <input type="hidden" name="estado" value="CANCELADA">
        <button class="btn btn-link btn-sm text-danger text-decoration-none">Cancelar trabajo</button>
    </form>
<?php endif; ?>
</div>