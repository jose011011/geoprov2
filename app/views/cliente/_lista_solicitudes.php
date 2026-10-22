<?php
$tabInicial = $tabInicial ?? 'curso';
$enCurso    = ['PENDIENTE', 'ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'];
$cuenta     = ['curso' => 0, 'fin' => 0, 'todas' => count($lista)];
foreach ($lista as $s) {
    if (in_array($s['estado_servicio'], $enCurso, true)) { $cuenta['curso']++; }
    elseif ($s['estado_servicio'] === 'FINALIZADA')      { $cuenta['fin']++; }
}
$tabs = ['curso' => 'En curso', 'fin' => 'Finalizados', 'todas' => 'Todos'];
?>

<div class="fx-filter d-flex flex-wrap gap-2 mb-3" id="filtros">
    <?php foreach ($tabs as $k => $t): ?>
        <button type="button" data-filtro="<?= $k ?>" class="btn btn-sm <?= $k === $tabInicial ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= $t ?> <span class="badge text-bg-light ms-1"><?= $cuenta[$k] ?></span>
        </button>
    <?php endforeach; ?>
</div>

<div class="d-grid gap-2" id="listaPedidos">
    <?php foreach ($lista as $s):
        $grupo = in_array($s['estado_servicio'], $enCurso, true) ? 'curso' : ($s['estado_servicio'] === 'FINALIZADA' ? 'fin' : 'otro'); ?>
        <a class="fx-order d-flex flex-wrap justify-content-between align-items-center gap-3 text-decoration-none text-reset"
           data-grupo="<?= $grupo ?>" href="<?= BASE_URL ?>/solicitud/detalle/<?= (int) $s['id_solicitud'] ?>">
            <div class="min-w-0">
                <div class="small text-muted"><?= Security::e($s['codigo_seguimiento']) ?> · <?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></div>
                <div class="fw-bold"><?= Security::e($s['nombre_categoria'] ?? 'Servicio') ?></div>
                <div class="small text-muted text-truncate" style="max-width: 520px;"><?= Security::e(mb_substr((string) $s['descripcion_problema'], 0, 110)) ?></div>
                <div class="small mt-1">
                    <i class="fa-solid fa-user-gear me-1 text-muted"></i>
                    <?= !empty($s['prof_nombre']) ? Security::e($s['prof_nombre'] . ' ' . $s['prof_apellido']) : '<span class="text-muted">Buscando técnico...</span>' ?>
                </div>
            </div>
            <div class="text-end">
                <?= Catalogo::badgeEstado($s['estado_servicio']) ?>
                <div class="small text-primary fw-semibold mt-2">Ver detalle <i class="fa-solid fa-arrow-right ms-1"></i></div>
            </div>
        </a>
    <?php endforeach; ?>
    <div id="sinResultados" class="text-center text-muted py-5 d-none">
        <i class="fa-regular fa-folder-open fs-1 d-block mb-2"></i>No hay solicitudes en esta sección.
    </div>
</div>

<script>
(function () {
    const botones = document.querySelectorAll('#filtros [data-filtro]');
    const tarjetas = document.querySelectorAll('#listaPedidos [data-grupo]');
    const vacio = document.getElementById('sinResultados');

    function filtrar(filtro) {
        let visibles = 0;
        tarjetas.forEach(t => {
            const ver = filtro === 'todas' || t.dataset.grupo === filtro;
            t.classList.toggle('d-none', !ver);
            if (ver) visibles++;
        });
        vacio.classList.toggle('d-none', visibles > 0);
        botones.forEach(b => {
            const activo = b.dataset.filtro === filtro;
            b.classList.toggle('btn-primary', activo);
            b.classList.toggle('btn-outline-primary', !activo);
        });
    }
    botones.forEach(b => b.addEventListener('click', () => filtrar(b.dataset.filtro)));
    filtrar(<?= json_encode($tabInicial) ?>);
})();
</script>