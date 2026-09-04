<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container mt-4" style="max-width: 600px; margin: auto;">
    <h2 class="mb-4 text-center" style="color: #0A192F; font-weight: bold;">Nuevas Solicitudes</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center shadow-sm" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?>
            <br><a href="<?= BASE_URL ?>/profesional/planes" class="btn btn-sm btn-warning mt-2 text-dark fw-bold">Recargar Tokens</a>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['exito'])): ?>
        <div class="alert alert-success text-center shadow-sm" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-check"></i> ¡Estado actualizado correctamente!
        </div>
    <?php endif; ?>

    <?php if (!empty($solicitudes)): ?>
        <?php foreach ($solicitudes as $solicitud): ?>
            <div class="card mb-3 shadow-sm" style="border-radius: 15px; border: none; border-left: 5px solid <?= $solicitud['estado_servicio'] === 'PENDIENTE' ? '#ffc107' : '#00BFA6' ?>;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary" style="font-size: 0.8rem;"><?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></span>
                        <span class="badge <?= $solicitud['estado_servicio'] === 'PENDIENTE' ? 'bg-warning text-dark' : 'bg-success' ?>">
                            <?= htmlspecialchars($solicitud['estado_servicio']) ?>
                        </span>
                    </div>

                    <h5 class="card-title text-dark fw-bold">
                        <i class="fa-solid fa-user"></i> Cliente: <?= htmlspecialchars($solicitud['cliente_nombre'] . ' ' . $solicitud['cliente_apellido']) ?>
                    </h5>
                    
                    <p class="card-text text-muted mb-1">
                        <i class="fa-solid fa-location-dot" style="color: #00BFA6;"></i> <strong>Zona:</strong> <?= htmlspecialchars($solicitud['zona']) ?>
                    </p>
                    <div class="bg-light p-2 mb-3 rounded" style="border: 1px solid #eee;">
                        <p class="card-text mb-0" style="font-size: 0.95rem;">
                            <strong>Problema:</strong> <?= htmlspecialchars($solicitud['descripcion_problema']) ?>
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>/solicitud/detalle/<?= $solicitud['id_solicitud'] ?>" class="btn btn-outline-dark fw-bold flex-grow-1" style="border-radius: 10px;">
                            <i class="fa-solid fa-file-lines"></i> Detalles
                        </a>

                        <?php if ($solicitud['estado_servicio'] === 'PENDIENTE'): ?>
                            <!-- Botón que activa el Modal -->
                            <button type="button" class="btn text-white fw-bold flex-grow-1" style="background-color: #00BFA6; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalAceptar_<?= $solicitud['id_solicitud'] ?>">
                                <i class="fa-solid fa-check"></i> Aceptar Trabajo
                            </button>

                            <!-- MODAL DE CONFIRMACIÓN -->
                            <div class="modal fade" id="modalAceptar_<?= $solicitud['id_solicitud'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius: 15px;">
                                        <div class="modal-header bg-warning text-dark" style="border-radius: 15px 15px 0 0;">
                                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-coins"></i> Confirmar Aceptación</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="<?= BASE_URL ?>/solicitud/actualizarEstado" method="POST">
                                            <div class="modal-body text-center p-4">
                                                <i class="fa-solid fa-circle-info fa-3x text-info mb-3"></i>
                                                <p class="mb-3">Al aceptar este trabajo, se descontará <strong>1 Token</strong> de tu saldo.</p>
                                                
                                                <div class="text-start bg-light p-3 rounded border">
                                                    <label class="form-label fw-bold text-dark small"><i class="fa-solid fa-clock text-primary"></i> ¿En cuántos minutos llegas?</label>
                                                    <div class="input-group">
                                                        <input type="number" name="tiempo_estimado" class="form-control" placeholder="Ej: 15" required min="2" max="120" value="15">
                                                        <span class="input-group-text">Minutos</span>
                                                    </div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">Ingrese un valor entero mayor a 1.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">Cancelar</button>
                                                <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
                                                <button type="submit" name="estado" value="ACEPTADA" class="btn text-white fw-bold" style="background-color: #00BFA6; border-radius: 10px;">Aceptar y Gastar Token</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($solicitud['estado_servicio'] === 'ACEPTADA'): ?>
                            <a href="<?= BASE_URL ?>/chat/ver/<?= $solicitud['id_solicitud'] ?>" class="btn btn-outline-primary fw-bold flex-grow-1" style="border-radius: 10px;">
                                <i class="fa-solid fa-comments"></i> Chat
                            </a>
                            <form action="<?= BASE_URL ?>/solicitud/actualizarEstado" method="POST" class="flex-grow-1 d-flex">
                                <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
                                <button type="submit" name="estado" value="EN_CAMINO" class="btn btn-primary fw-bold w-100" style="border-radius: 10px;">
                                    <i class="fa-solid fa-motorcycle"></i> Iniciar Viaje
                                </button>
                            </form>
                            
                        <?php elseif ($solicitud['estado_servicio'] === 'EN_CAMINO'): ?>
                            <a href="<?= BASE_URL ?>/chat/ver/<?= $solicitud['id_solicitud'] ?>" class="btn btn-outline-primary fw-bold flex-grow-1" style="border-radius: 10px;">
                                <i class="fa-solid fa-comments"></i> Chat
                            </a>
                            <a href="<?= BASE_URL ?>/solicitud/mapaViaje/<?= $solicitud['id_solicitud'] ?>" class="btn btn-success fw-bold flex-grow-1" style="border-radius: 10px;">
                                <i class="fa-solid fa-map-location-dot"></i> Mapa de Viaje
                            </a>

                        <?php elseif ($solicitud['estado_servicio'] === 'EN_PROCESO'): ?>
                            <a href="<?= BASE_URL ?>/chat/ver/<?= $solicitud['id_solicitud'] ?>" class="btn btn-outline-primary fw-bold flex-grow-1" style="border-radius: 10px;">
                                <i class="fa-solid fa-comments"></i> Chat
                            </a>
                            <a href="<?= BASE_URL ?>/solicitud/mapaViaje/<?= $solicitud['id_solicitud'] ?>" class="btn btn-warning fw-bold flex-grow-1 text-dark" style="border-radius: 10px;">
                                <i class="fa-solid fa-screwdriver-wrench"></i> Panel de Trabajo
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center mt-5 text-muted">
            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
            <p>No tienes solicitudes pendientes por el momento.</p>
        </div>
    <?php endif; ?>
</div>

<!-- SCRIPT DE VALIDACIÓN ESTRICTA -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll('input[type="number"]');

    inputs.forEach(input => {
        let feedback = document.createElement('div');
        feedback.className = 'text-danger small mt-1 error-feedback';
        feedback.style.display = 'none';
        input.parentNode.appendChild(feedback);

        input.addEventListener('keydown', function(e) {
            if (['e', 'E', '+', '-'].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            let valorStr = this.value;
            if (valorStr.length > 6) {
                this.value = valorStr.slice(0, 6);
                valorStr = this.value;
            }
            let val = parseFloat(valorStr);

            if (valorStr === '') {
                feedback.innerText = "Este campo no puede estar vacío.";
                feedback.style.display = 'block';
                this.setCustomValidity("Inválido");
            } else if (isNaN(val) || val <= 1) {
                feedback.innerText = "⚠️ El valor debe ser estrictamente mayor a 1.";
                feedback.style.display = 'block';
                this.setCustomValidity("Debe ser mayor a 1");
            } else if (val > 9999) {
                feedback.innerText = "⚠️ El valor ingresado es demasiado alto.";
                feedback.style.display = 'block';
                this.setCustomValidity("Valor muy alto");
            } else {
                feedback.style.display = 'none';
                this.setCustomValidity("");
            }
        });
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>