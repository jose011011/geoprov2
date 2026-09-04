<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-1">Califica a <?= htmlspecialchars($solicitud['prof_nombre'] . ' ' . $solicitud['prof_apellido']) ?></h5>
                <p class="text-muted small mb-3">Código: <?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?php
                    $campos = [
                        'puntuacion_general' => 'Calificación general',
                        'puntualidad'        => 'Puntualidad',
                        'calidad_trabajo'    => 'Calidad del trabajo'
                    ];
                    foreach ($campos as $name => $label): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold small"><?= $label ?></label>
                            <div class="star-rating" data-name="<?= $name ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-solid fa-star star-icon" data-value="<?= $i ?>" style="cursor:pointer; font-size:1.5rem; color:#d1d5db; margin-right:4px;"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="<?= $name ?>" id="input_<?= $name ?>" value="5" required>
                        </div>
                    <?php endforeach; ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Comentario (opcional)</label>
                        <textarea name="comentario" class="form-control" rows="3" placeholder="Cuéntanos cómo fue tu experiencia..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold">Enviar calificación</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.star-rating').forEach(group => {
    const stars = group.querySelectorAll('.star-icon');
    const input = document.getElementById('input_' + group.dataset.name);

    function pintar(valor) {
        stars.forEach(s => {
            s.style.color = parseInt(s.dataset.value) <= valor ? '#f59e0b' : '#d1d5db';
        });
    }
    pintar(5); // valor inicial por defecto

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const valor = parseInt(this.dataset.value);
            input.value = valor;
            pintar(valor);
        });
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>