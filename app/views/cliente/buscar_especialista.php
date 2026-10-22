<?php
require __DIR__ . '/../layouts/header.php';

$sinDireccion = empty($clienteInfo) || trim((string) $clienteInfo['direccion_referencia']) === '';
$hayTecnicos  = !empty($profesionales);

// Solo se preselecciona si el técnico pedido realmente está en la lista
$preseleccion = 0;
foreach ($profesionales as $p) {
    if ((int) $p['id_profesional'] === (int) $idProfSeleccionado) { $preseleccion = (int) $idProfSeleccionado; break; }
}
?>

<div class="container py-4" style="max-width: 920px;">
    <a href="<?= BASE_URL ?>/cliente/dashboard" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
    <h1 class="fx-page-title h3 mt-2 mb-3">Detalles del servicio</h1>

<?php if (!$categoria): ?>

    <div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>No pudimos identificar el tipo de servicio. Elige una categoría:</div>
    <div class="row g-3">
        <?php foreach ($categorias as $c): ?>
            <div class="col-6 col-md-4">
                <a class="fx-cat-tile" href="<?= BASE_URL ?>/cliente/buscarEspecialista?cat=<?= (int) $c['id_categoria'] ?>">
                    <span class="ico"><i class="<?= Security::e(Catalogo::icono($c['icono_fa'])) ?>"></i></span>
                    <span class="nombre"><?= Security::e($c['nombre_categoria']) ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>

    <!-- Categoría detectada + cambiar (conserva lo que ya escribiste) -->
    <form method="post" id="formCambioCategoria" action="<?= BASE_URL ?>/cliente/buscarEspecialista">
        <?= Security::campo() ?>
        <input type="hidden" name="descripcion" id="descCambio" value="">
        <div class="card mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="fx-cat-tile" style="width:52px;height:52px;padding:0;border-radius:16px;justify-content:center;">
                    <i class="<?= Security::e(Catalogo::icono($categoria['icono_fa'])) ?> text-primary fs-5"></i>
                </span>
                <div>
                    <?php if ($esBusquedaIA): ?><span class="badge text-bg-primary mb-1"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Detectado por el asistente</span><?php endif; ?>
                    <div class="fw-bold fs-5"><?= Security::e($categoria['nombre_categoria']) ?></div>
                </div>
            </div>
            <div class="dropdown">
                <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Cambiar categoría</button>
                <ul class="dropdown-menu dropdown-menu-end fx-menu">
                    <?php foreach ($categorias as $c): ?>
                        <li>
                            <button type="submit" class="dropdown-item" formaction="<?= BASE_URL ?>/cliente/buscarEspecialista?cat=<?= (int) $c['id_categoria'] ?>">
                                <i class="<?= Security::e(Catalogo::icono($c['icono_fa'])) ?> me-2"></i><?= Security::e($c['nombre_categoria']) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div></div>
    </form>

    <form method="post" id="formPedido" action="<?= BASE_URL ?>/cliente/registrarPedido">
        <?= Security::campo() ?>
        <input type="hidden" name="id_categoria" value="<?= (int) $categoria['id_categoria'] ?>">
        <input type="hidden" name="id_profesional" id="idProfesional" value="<?= $preseleccion ?>">

        <div class="mb-4">
            <label class="form-label fw-semibold" for="descripcion">Describe el problema</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" minlength="10" maxlength="1000" required
                      placeholder="Cuéntale al técnico qué pasó, desde cuándo y cualquier detalle útil."><?= Security::e($problemaOriginal) ?></textarea>
        </div>

        <div class="card mb-4"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="small text-muted">El técnico irá a</div>
                <?php if ($sinDireccion): ?>
                    <div class="text-danger fw-semibold">Aún no registraste tu dirección</div>
                <?php else: ?>
                    <div class="fw-semibold"><?= Security::e($clienteInfo['direccion_referencia']) ?></div>
                    <div class="small text-muted"><?= Security::e($clienteInfo['zona']) ?> · <?= Security::e(Catalogo::macroEtiqueta($clienteInfo['macrodistrito'] ?? '')) ?></div>
                <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>/cliente/perfil" class="btn btn-outline-primary btn-sm"><?= $sinDireccion ? 'Agregar dirección' : 'Cambiar' ?></a>
        </div></div>

        <h2 class="h5 fw-bold mb-3">¿Quién te atiende?</h2>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <button type="button" class="fx-mode <?= $preseleccion ? '' : 'active' ?>" data-modo="auto">
                    <span class="ico"><i class="fa-solid fa-bolt"></i></span>
                    <span><span class="fw-bold d-block">El primero disponible</span>
                    <small class="text-muted">Avisamos a los técnicos de la zona y te atiende quien acepte primero.</small></span>
                </button>
            </div>
            <div class="col-md-6">
                <button type="button" class="fx-mode <?= $preseleccion ? 'active' : '' ?>" data-modo="manual" <?= $hayTecnicos ? '' : 'disabled' ?>>
                    <span class="ico"><i class="fa-solid fa-user-check"></i></span>
                    <span><span class="fw-bold d-block">Elegir yo al técnico</span>
                    <small class="text-muted"><?= $hayTecnicos ? count($profesionales) . ' disponible' . (count($profesionales) === 1 ? '' : 's') . ' en esta categoría' : 'Ninguno disponible ahora' ?></small></span>
                </button>
            </div>
        </div>

        <?php if (!$hayTecnicos): ?>
            <div class="alert alert-light border small">
                <i class="fa-regular fa-clock me-1"></i>No hay técnicos disponibles ahora en esta categoría. Si eliges
                "El primero disponible", tu pedido quedará abierto y lo verán los técnicos de tu zona apenas se conecten.
            </div>
        <?php endif; ?>

        <div id="listaTecnicos" class="<?= $preseleccion ? '' : 'd-none' ?> mb-4">
            <div class="row g-3">
                <?php foreach ($profesionales as $p):
                    $premium = (int) $p['posicionamiento_destacado'] === 1;
                    $inicial = mb_strtoupper(mb_substr($p['nombre'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    <div class="col-md-6">
                        <button type="button" class="fx-pro-pick" data-id="<?= (int) $p['id_profesional'] ?>">
                            <div class="fx-pro <?= $premium ? 'premium' : '' ?>">
                                <div class="d-flex gap-3">
                                    <div class="avatar"><?= Security::e($inicial) ?></div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-truncate"><?= Security::e($p['nombre'] . ' ' . $p['apellido']) ?>
                                            <?php if ($p['es_favorito']): ?><i class="fa-solid fa-heart text-danger ms-1"></i><?php endif; ?>
                                            <?php if ($premium): ?><span class="badge text-bg-primary ms-1"><i class="fa-solid fa-crown"></i></span><?php endif; ?>
                                        </div>
                                        <div><?= Catalogo::estrellas((float) $p['promedio_estrellas']) ?> <small class="text-muted"><?= number_format((float) $p['promedio_estrellas'], 1) ?></small></div>
                                        <div class="small text-muted"><?= (int) $p['experiencia_anios'] ?> años · <?= Security::e(Catalogo::macroEtiqueta($p['macrodistrito_base'])) ?>
                                            <?php if ((float) $p['tarifa_base'] > 0): ?> · desde Bs <?= number_format((float) $p['tarifa_base'], 0) ?><?php endif; ?></div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-end mt-2">
                <a class="small fw-semibold" href="<?= BASE_URL ?>/cliente/especialistas?categoria=<?= (int) $categoria['id_categoria'] ?>">Ver todos en el catálogo <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
        </div>

        <button class="btn btn-primary btn-lg w-100" <?= $sinDireccion ? 'disabled' : '' ?>>
            <i class="fa-solid fa-paper-plane me-2"></i>Enviar solicitud
        </button>
    </form>

    <script>
    (function () {
        const campo = document.getElementById('idProfesional');
        const lista = document.getElementById('listaTecnicos');
        const modos = document.querySelectorAll('.fx-mode');
        const tecnicos = document.querySelectorAll('.fx-pro-pick');

        const marcar = id => tecnicos.forEach(b => b.classList.toggle('active', b.dataset.id === String(id)));

        modos.forEach(boton => boton.addEventListener('click', function () {
            if (boton.disabled) return;
            modos.forEach(m => m.classList.toggle('active', m === boton));
            const manual = boton.dataset.modo === 'manual';
            lista.classList.toggle('d-none', !manual);
            if (!manual) { campo.value = '0'; marcar(0); }
        }));

        tecnicos.forEach(b => b.addEventListener('click', function () { campo.value = b.dataset.id; marcar(b.dataset.id); }));

        document.getElementById('formPedido').addEventListener('submit', function (e) {
            const manual = document.querySelector('.fx-mode[data-modo="manual"]').classList.contains('active');
            if (manual && campo.value === '0') {
                e.preventDefault();
                alert('Elige un técnico de la lista o selecciona "El primero disponible".');
            }
        });

        // Al cambiar de categoría se conserva lo que ya escribiste
        document.querySelectorAll('#formCambioCategoria button[type=submit]').forEach(b => b.addEventListener('click', function () {
            document.getElementById('descCambio').value = document.getElementById('descripcion').value;
        }));

        marcar(campo.value);
    })();
    </script>

<?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>