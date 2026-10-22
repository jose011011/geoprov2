<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="fx-page-title h3 mb-1">Catálogo de técnicos</h1>
    <p class="text-muted">Profesionales verificados y disponibles ahora.</p>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= BASE_URL ?>/cliente/especialistas" class="btn btn-sm <?= $categoriaActual === 0 ? 'btn-primary' : 'btn-outline-primary' ?>">Todos</a>
        <?php foreach ($categorias as $c): ?>
            <a href="<?= BASE_URL ?>/cliente/especialistas?categoria=<?= (int) $c['id_categoria'] ?>"
               class="btn btn-sm <?= $categoriaActual === (int) $c['id_categoria'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= Security::e($c['nombre_categoria']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!$especialistas): ?>
        <div class="text-center text-muted py-5">
            <i class="fa-regular fa-face-meh fs-1 d-block mb-2"></i>
            Por ahora no hay técnicos disponibles en esta categoría.
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($especialistas as $e):
            $premium = (int) $e['posicionamiento_destacado'] === 1;
            $inicial = mb_strtoupper(mb_substr($e['nombre'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
            <div class="col-md-6 col-xl-4">
                <div class="fx-pro <?= $premium ? 'premium' : '' ?>">
                    <a class="fav <?= $e['es_favorito'] ? 'on' : '' ?>" title="Agregar a favoritos"
                       href="<?= BASE_URL ?>/cliente/agregarFavorito/<?= (int) $e['id_profesional'] ?>">
                        <i class="<?= $e['es_favorito'] ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                    </a>
                    <div class="d-flex gap-3 mb-2">
                        <div class="avatar"><?= Security::e($inicial) ?></div>
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate"><?= Security::e($e['nombre'] . ' ' . $e['apellido']) ?></div>
                            <div class="small text-muted"><i class="<?= Security::e(Catalogo::icono($e['icono_fa'])) ?> me-1"></i><?= Security::e($e['nombre_categoria']) ?></div>
                            <div><?= Catalogo::estrellas((float) $e['promedio_estrellas']) ?> <small class="text-muted"><?= number_format((float) $e['promedio_estrellas'], 1) ?></small></div>
                        </div>
                    </div>
                    <div class="small text-muted mb-3">
                        <i class="fa-solid fa-location-dot me-1"></i><?= Security::e(Catalogo::macroEtiqueta($e['macrodistrito_base'])) ?> · <?= Security::e($e['zona_especifica']) ?><br>
                        <i class="fa-solid fa-briefcase me-1"></i><?= (int) $e['experiencia_anios'] ?> años de experiencia
                        <?php if ((float) $e['tarifa_base'] > 0): ?> · desde Bs <?= number_format((float) $e['tarifa_base'], 0) ?><?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if ($premium): ?><span class="badge text-bg-primary"><i class="fa-solid fa-crown me-1"></i><?= Security::e(Catalogo::nombrePlan($e['nombre_plan'])) ?></span><?php else: ?><span></span><?php endif; ?>
                        <form method="post" action="<?= BASE_URL ?>/cliente/solicitarManual" class="m-0">
                            <?= Security::campo() ?>
                            <input type="hidden" name="id_profesional" value="<?= (int) $e['id_profesional'] ?>">
                            <button class="btn btn-primary btn-sm">Solicitar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>