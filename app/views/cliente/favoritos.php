<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="fx-page-title h3 mb-1">Mis favoritos</h1>
    <p class="text-muted">Tus técnicos de confianza, a un clic.</p>

    <?php if (!$favoritos): ?>
        <div class="text-center text-muted py-5">
            <i class="fa-regular fa-heart fs-1 d-block mb-2"></i>
            Aún no tienes favoritos.<br>
            <a href="<?= BASE_URL ?>/cliente/especialistas" class="btn btn-primary btn-sm mt-3">Explorar el catálogo</a>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($favoritos as $f):
            $inicial = mb_strtoupper(mb_substr($f['nombre'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
            <div class="col-md-6 col-xl-4">
                <div class="fx-pro">
                    <div class="d-flex gap-3 mb-3">
                        <div class="avatar"><?= Security::e($inicial) ?></div>
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate"><?= Security::e($f['nombre'] . ' ' . $f['apellido']) ?></div>
                            <div class="small text-muted"><i class="<?= Security::e(Catalogo::icono($f['icono_fa'])) ?> me-1"></i><?= Security::e($f['nombre_categoria']) ?></div>
                            <div><?= Catalogo::estrellas((float) $f['promedio_estrellas']) ?> <small class="text-muted"><?= number_format((float) $f['promedio_estrellas'], 1) ?></small></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= BASE_URL ?>/cliente/eliminarFavorito/<?= (int) $f['id_favorito'] ?>" class="small text-danger text-decoration-none"
                           onclick="return confirm('¿Quitar a este técnico de tus favoritos?');"><i class="fa-solid fa-heart-crack me-1"></i>Quitar</a>
                        <form method="post" action="<?= BASE_URL ?>/cliente/solicitarManual" class="m-0">
                            <?= Security::campo() ?>
                            <input type="hidden" name="id_profesional" value="<?= (int) $f['id_profesional'] ?>">
                            <button class="btn btn-primary btn-sm">Solicitar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>