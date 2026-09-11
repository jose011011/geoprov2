<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO RATING — PREMIUM UI
   ============================================================ */
:root {
    --geo-dark: #071827; --geo-primary: #08b7a5; --geo-primary-dark: #079486;
    --geo-bg: #f5f7fa; --geo-text: #172033; --geo-muted: #718096;
    --geo-border: #e8edf2; --geo-warning: #f59e0b;
}

body { background: var(--geo-bg) !important; color: var(--geo-text); font-family: 'Inter', sans-serif; overflow-x: hidden; }
.geo-navbar { display: none !important; }

/* Contenedor centralizado para no distraer al usuario */
.rating-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: radial-gradient(circle at top right, rgba(8, 183, 165, 0.05), transparent 40%),
                radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.05), transparent 40%);
}

.rating-card {
    background: #ffffff;
    width: 100%;
    max-width: 550px;
    border-radius: 24px;
    box-shadow: 0 15px 40px rgba(7, 24, 39, 0.08);
    border: 1px solid var(--geo-border);
    overflow: hidden;
    animation: slideUp 0.5s ease;
}

@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* Cabecera del Profesional */
.rating-header {
    padding: 35px 30px 25px;
    text-align: center;
    border-bottom: 1px solid var(--geo-border);
    background: #fafbfc;
}

.prof-avatar-lg {
    width: 90px;
    height: 90px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--geo-dark), #1e293b);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 800;
    box-shadow: 0 8px 20px rgba(7, 24, 39, 0.2);
    border: 4px solid #ffffff;
}

.prof-name { font-weight: 900; font-size: 1.4rem; color: var(--geo-text); margin-bottom: 5px; }
.job-code { display: inline-block; background: #f1f5f9; padding: 4px 12px; border-radius: 50px; font-family: monospace; font-size: 0.8rem; color: #475569; font-weight: 600; border: 1px solid #e2e8f0; }

/* Cuerpo del Formulario */
.rating-body { padding: 35px 40px; }

/* Estrellas Interactivas */
.rating-group { margin-bottom: 25px; background: #fff; padding: 15px 20px; border-radius: 16px; border: 1px solid var(--geo-border); transition: all 0.3s; }
.rating-group:hover { border-color: var(--geo-primary); box-shadow: 0 4px 15px rgba(8, 183, 165, 0.05); }

.rating-label-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.rating-title { font-weight: 800; font-size: 0.95rem; color: var(--geo-text); margin: 0; }
.rating-status-text { font-size: 0.8rem; font-weight: 800; color: var(--geo-warning); text-transform: uppercase; letter-spacing: 0.5px; }

.stars-container { display: flex; gap: 8px; }
.star-icon { font-size: 2rem; color: #e2e8f0; cursor: pointer; transition: transform 0.2s, color 0.2s; }
.star-icon:hover { transform: scale(1.15); }
.star-icon.active { color: var(--geo-warning); text-shadow: 0 2px 10px rgba(245, 158, 11, 0.3); }

/* Textarea Premium */
.form-label-custom { font-weight: 800; font-size: 0.95rem; color: var(--geo-text); margin-bottom: 10px; display: block; }
.textarea-custom { width: 100%; border: 1px solid var(--geo-border); border-radius: 16px; padding: 15px; font-size: 0.95rem; background: #f8fafc; color: var(--geo-text); resize: none; transition: all 0.3s; }
.textarea-custom:focus { outline: none; border-color: var(--geo-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(8, 183, 165, 0.1); }

/* Botones */
.btn-submit { background: linear-gradient(135deg, var(--geo-primary), var(--geo-primary-dark)); color: white; border: none; width: 100%; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1.05rem; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s; margin-top: 10px; box-shadow: 0 8px 20px rgba(8, 183, 165, 0.3); }
.btn-submit:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(8, 183, 165, 0.4); }

.btn-cancel { display: block; text-align: center; color: var(--geo-muted); text-decoration: none; font-weight: 700; font-size: 0.9rem; margin-top: 20px; transition: color 0.2s; }
.btn-cancel:hover { color: var(--geo-text); }

/* Responsive */
@media(max-width: 576px) { .rating-body { padding: 25px 20px; } .star-icon { font-size: 1.8rem; } }
</style>

<div class="rating-wrapper">
    <div class="rating-card">
        
        <!-- VERIFICACIÓN: ¿Ya calificó este servicio? -->
        <?php 
            require_once "../app/models/Calificacion.php";
            $calModel = new Calificacion();
            $yaCalifico = $calModel->yaCalificada($solicitud['id_solicitud']); 
        ?>

        <?php if ($yaCalifico): ?>
            <!-- PANTALLA DE BLOQUEO (Si ya fue calificado) -->
            <div class="rating-body text-center py-5">
                <div class="mb-4">
                    <i class="fa-solid fa-circle-check text-success" style="font-size: 5rem;"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">¡Gracias por tu reseña!</h3>
                <p class="text-muted">Este servicio ya ha sido calificado anteriormente. Tu opinión ayuda a mantener la calidad de GEO-PRO.</p>
                <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn-submit text-decoration-none d-inline-block mt-3" style="width: auto; padding: 12px 30px;">
                    Volver a mis solicitudes
                </a>
            </div>

        <?php else: ?>
            <!-- PANTALLA NORMAL DE CALIFICACIÓN -->
            <div class="rating-header">
                <div class="prof-avatar-lg">
                    <?= strtoupper(substr($solicitud['prof_nombre'] ?? 'P', 0, 1)) ?>
                </div>
                <h2 class="prof-name">¿Cómo te fue con <?= htmlspecialchars($solicitud['prof_nombre']) ?>?</h2>
                <div class="job-code"><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></div>
            </div>

            <div class="rating-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 fw-bold small"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="ratingForm" action="<?= BASE_URL ?>/solicitud/calificar/<?= $solicitud['id_solicitud'] ?>">
                    
                    <?php
                    $campos = [
                        'puntuacion_general' => 'Calificación General del Servicio',
                        'puntualidad'        => 'Puntualidad de llegada',
                        'calidad_trabajo'    => 'Calidad y limpieza del trabajo'
                    ];
                    foreach ($campos as $name => $label): ?>
                        <div class="rating-group">
                            <div class="rating-label-flex">
                                <h4 class="rating-title"><?= $label ?></h4>
                                <span class="rating-status-text" id="status_<?= $name ?>">Excelente</span>
                            </div>
                            
                            <div class="stars-container" data-name="<?= $name ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-solid fa-star star-icon active" data-value="<?= $i ?>"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Input Oculto que guarda el valor real (Inicia en 5 por defecto) -->
                            <input type="hidden" name="<?= $name ?>" id="input_<?= $name ?>" value="5">
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-4 mb-2">
                        <label class="form-label-custom">Comentarios Adicionales (Opcional)</label>
                        <textarea name="comentario" class="textarea-custom" rows="3" placeholder="Ej: Fue muy amable y resolvió el problema en menos de 30 minutos..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <i class="fa-solid fa-paper-plane me-2"></i> Enviar Calificación
                    </button>
                    
                    <a href="<?= BASE_URL ?>/solicitud/detalle/<?= $solicitud['id_solicitud'] ?>" class="btn-cancel">
                        Cancelar y volver al detalle
                    </a>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const statusTexts = {
        1: 'Pésimo',
        2: 'Malo',
        3: 'Regular',
        4: 'Bueno',
        5: 'Excelente'
    };

    document.querySelectorAll('.stars-container').forEach(container => {
        const stars = container.querySelectorAll('.star-icon');
        const input = document.getElementById('input_' + container.dataset.name);
        const statusLabel = document.getElementById('status_' + container.dataset.name);

        function pintar(valor) {
            stars.forEach(s => {
                const val = parseInt(s.dataset.value);
                if (val <= valor) s.classList.add('active');
                else s.classList.remove('active');
            });
            if (statusTexts[valor]) {
                statusLabel.textContent = statusTexts[valor];
                statusLabel.style.color = valor <= 2 ? '#ef4444' : '#f59e0b';
            }
        }

        stars.forEach(star => {
            star.addEventListener('mouseover', function() { pintar(parseInt(this.dataset.value)); });
            star.addEventListener('mouseout',  function() { pintar(parseInt(input.value)); });
            star.addEventListener('click',     function() {
                input.value = parseInt(this.dataset.value);
                pintar(parseInt(this.dataset.value));
            });
        });
    });

    const form = document.getElementById('ratingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSubmit');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando reseña...';
            btn.style.opacity = '0.7';
            // Evitamos usar .disabled=true porque en algunos navegadores cancela el submit
            btn.style.pointerEvents = 'none'; 
        });
    }
});
</script>


<?php require_once "../app/views/layouts/footer.php"; ?>1