<?php
require __DIR__ . '/../layouts/header.php';
$tipos = ['AMBOS' => 'Técnicos y oficios', 'TECNICO' => 'Solo técnicos con título', 'EMPIRICO_OFICIO' => 'Solo trabajadores de oficio'];

/** Selector visual de ícono: guarda el valor en un campo oculto y muestra la vista previa. */
$selector = function (string $valor, string $id = '') {
    $completo = IconosServicio::completo($valor !== '' ? $valor : IconosServicio::POR_DEFECTO);
    ob_start(); ?>
    <div class="fx-selector-icono d-flex align-items-center gap-2" <?= $id !== '' ? 'id="' . $id . '"' : '' ?>>
        <input type="hidden" name="icono" class="icono-valor" value="<?= Security::e($completo) ?>">
        <span class="fx-icono-vista"><i class="<?= Security::e($completo) ?>"></i></span>
        <button type="button" class="btn btn-outline-primary btn-sm btn-elegir-icono">Elegir</button>
    </div>
    <?php return ob_get_clean();
};
?>

<div class="container py-4" style="max-width: 1000px;">
    <h1 class="fx-page-title h3 mb-3">Categorías de servicio</h1>

    <details class="card mb-4"><summary class="card-body fw-bold" style="cursor:pointer;"><i class="fa-solid fa-plus me-2 text-primary"></i>Nueva categoría</summary>
        <form method="post" action="<?= BASE_URL ?>/admin/guardarCategoria" class="card-body border-top">
            <?= Security::campo() ?>
            <input type="hidden" name="id_categoria" value="0">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><label class="form-label fw-semibold">Nombre</label><input name="nombre" id="nombreNueva" class="form-control" maxlength="100" placeholder="Ej: Carpintería" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Ícono</label><?= $selector('', 'selectorNueva') ?>
                    <div class="small text-muted mt-1" id="pistaIcono">Se elige solo según el nombre. Puedes cambiarlo.</div></div>
                <div class="col-md-5"><label class="form-label fw-semibold">¿Para quién?</label>
                    <select name="tipo" class="form-select"><?php foreach ($tipos as $k => $t): ?><option value="<?= $k ?>"><?= $t ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><label class="form-label fw-semibold">Descripción <span class="text-muted fw-normal">(opcional)</span></label><input name="descripcion" class="form-control" maxlength="500"></div>
            </div>
            <button class="btn btn-primary mt-3">Crear categoría</button>
        </form>
    </details>

    <div class="row g-3">
        <?php foreach ($categorias as $c):
            $activa   = (int) $c['estado'] === 1;
            $sugerida = (int) $c['sugerida'] === 1; ?>
            <div class="col-12"><div class="card <?= $activa ? '' : 'opacity-75' ?> <?= $sugerida ? 'border-warning border-2' : '' ?>"><div class="card-body">
                <?php if ($sugerida): ?>
                    <div class="alert alert-warning py-2 small mb-3"><i class="fa-solid fa-lightbulb me-1"></i>Sugerida por un técnico al registrarse (<?= (int) $c['tecnicos'] ?> técnico(s)). Revisa el nombre y el ícono, y actívala para que los clientes la vean.</div>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/admin/guardarCategoria" class="row g-2 align-items-end">
                    <?= Security::campo() ?>
                    <input type="hidden" name="id_categoria" value="<?= (int) $c['id_categoria'] ?>">
                    <div class="col-md-3"><label class="form-label small text-muted mb-0">Nombre</label><input name="nombre" class="form-control form-control-sm" value="<?= Security::e($c['nombre_categoria']) ?>" maxlength="100" required></div>
                    <div class="col-md-2"><label class="form-label small text-muted mb-1 d-block">Ícono</label><?= $selector((string) $c['icono_fa']) ?></div>
                    <div class="col-md-3"><label class="form-label small text-muted mb-0">Para</label>
                        <select name="tipo" class="form-select form-select-sm"><?php foreach ($tipos as $k => $t): ?><option value="<?= $k ?>" <?= $c['tipo_clasificacion'] === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label small text-muted mb-0">Descripción</label><input name="descripcion" class="form-control form-control-sm" value="<?= Security::e($c['descripcion'] ?? '') ?>" maxlength="500"></div>
                    <div class="col-md-1 d-grid"><button class="btn btn-primary btn-sm">Guardar</button></div>
                </form>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted"><?= (int) $c['tecnicos'] ?> técnico(s) · <?= $activa ? 'Activa' : 'Desactivada' ?></small>
                    <form method="post" action="<?= BASE_URL ?>/admin/cambiarEstadoCategoria" class="m-0">
                        <?= Security::campo() ?>
                        <input type="hidden" name="id_categoria" value="<?= (int) $c['id_categoria'] ?>">
                        <input type="hidden" name="estado" value="<?= $activa ? 0 : 1 ?>">
                        <button class="btn btn-sm <?= $activa ? 'btn-link text-danger text-decoration-none' : 'btn-primary' ?>"><?= $activa ? 'Desactivar' : 'Activar' ?></button>
                    </form>
                </div>
            </div></div></div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Ventana para elegir el ícono -->
<div class="modal fade" id="modalIconos" tabindex="-1" aria-labelledby="tituloIconos" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="tituloIconos">Elige un ícono</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <input type="search" id="buscarIcono" class="form-control mb-3" placeholder="Buscar: electricidad, pintura, auto, niños...">
            <div class="row g-2" id="gridIconos"></div>
        </div>
    </div></div>
</div>

<script>
window.addEventListener('DOMContentLoaded', function () {
    const ICONOS = <?= json_encode(IconosServicio::LISTA, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;   // [clase, nombre, claves]
    const sinTildes = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const modal = new bootstrap.Modal(document.getElementById('modalIconos'));
    const grid = document.getElementById('gridIconos');
    const buscar = document.getElementById('buscarIcono');
    let actual = null; // selector que abrió la ventana

    function poner(selector, clase, manual) {
        selector.querySelector('.icono-valor').value = 'fa-solid ' + clase;
        selector.querySelector('.fx-icono-vista i').className = 'fa-solid ' + clase;
        selector.dataset.manual = manual ? '1' : '';
    }

    function dibujar(filtro) {
        const q = sinTildes(filtro || '');
        const claseActual = actual ? actual.querySelector('.icono-valor').value.split(' ').pop() : '';
        grid.innerHTML = '';
        ICONOS.filter(([c, n, k]) => !q || sinTildes(n).includes(q) || k.includes(q)).forEach(([clase, nombre]) => {
            const col = document.createElement('div');
            col.className = 'col-4 col-sm-3 col-md-2';
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'fx-icono-opcion' + (clase === claseActual ? ' sel' : '');
            b.dataset.clase = clase;
            b.innerHTML = '<i class="fa-solid ' + clase + '"></i><span></span>';
            b.querySelector('span').textContent = nombre;
            col.appendChild(b);
            grid.appendChild(col);
        });
        if (!grid.children.length) { grid.innerHTML = '<div class="col-12 text-center text-muted py-4">No hay íconos con ese nombre.</div>'; }
    }

    document.querySelectorAll('.btn-elegir-icono').forEach(btn => btn.addEventListener('click', function () {
        actual = btn.closest('.fx-selector-icono');
        buscar.value = '';
        dibujar('');
        modal.show();
    }));
    buscar.addEventListener('input', () => dibujar(buscar.value));
    grid.addEventListener('click', function (e) {
        const b = e.target.closest('.fx-icono-opcion');
        if (!b || !actual) return;
        poner(actual, b.dataset.clase, true);
        if (actual.id === 'selectorNueva') { document.getElementById('pistaIcono').textContent = 'Ícono elegido por ti.'; }
        modal.hide();
    });

    // Al escribir el nombre de una categoría nueva, se sugiere el ícono (mientras no hayas elegido uno a mano)
    const nombre = document.getElementById('nombreNueva');
    const selNueva = document.getElementById('selectorNueva');
    nombre.addEventListener('input', function () {
        if (selNueva.dataset.manual === '1') return;
        const t = sinTildes(nombre.value);
        let clase = 'fa-screwdriver-wrench';
        outer: for (const [c, , claves] of ICONOS) {
            for (const k of claves.split(' ')) { if (k && t.includes(k)) { clase = c; break outer; } }
        }
        poner(selNueva, clase, false);
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>