<?php
require __DIR__ . '/../layouts/header.php';
$criterios = [
    'puntuacion_general' => 'Valoración general',
    'puntualidad'        => 'Puntualidad',
    'calidad_trabajo'    => 'Calidad del trabajo',
];
?>

<div class="container py-4" style="max-width: 640px;">
    <a href="<?= BASE_URL ?>/solicitud/detalle/<?= (int) $solicitud['id_solicitud'] ?>" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
    <h1 class="fx-page-title h3 mt-2">Califica el servicio</h1>
    <p class="text-muted">
        <?= Security::e($solicitud['nombre_categoria'] ?? 'Servicio') ?>
        <?php if (!empty($solicitud['prof_nombre'])): ?> · <?= Security::e($solicitud['prof_nombre'] . ' ' . $solicitud['prof_apellido']) ?><?php endif; ?>
    </p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= nl2br(Security::e($error)) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/solicitud/calificar/<?= (int) $solicitud['id_solicitud'] ?>" class="card"><div class="card-body">
        <?= Security::campo() ?>

        <?php foreach ($criterios as $campo => $etiqueta): ?>
            <div class="mb-3">
                <div class="fw-semibold mb-1"><?= $etiqueta ?></div>
                <div class="fx-rate">
                    <?php for ($n = 5; $n >= 1; $n--): ?>
                        <input type="radio" id="<?= $campo . $n ?>" name="<?= $campo ?>" value="<?= $n ?>" <?= $n === 5 && false ? 'checked' : '' ?> required>
                        <label for="<?= $campo . $n ?>" title="<?= $n ?> estrella<?= $n > 1 ? 's' : '' ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="mb-4">
            <label class="form-label fw-semibold" for="comentario">Comentario <span class="text-muted fw-normal">(opcional)</span></label>
            <textarea class="form-control" id="comentario" name="comentario" rows="3" maxlength="500" placeholder="¿Cómo fue tu experiencia?"></textarea>
        </div>

        <button class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane me-2"></i>Enviar calificación</button>
    </div></form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>