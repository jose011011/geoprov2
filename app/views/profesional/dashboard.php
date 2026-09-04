<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <h2 class="mb-4">Mi Panel Profesional</h2>

    <?php if (isset($_GET['pago_enviado'])): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>Tu pago fue enviado. Un administrador lo confirmará pronto.</div>
    <?php endif; ?>

    <?php if ($perfil['estado_validacion'] !== 'APROBADO'): ?>
        <div class="alert alert-<?= $perfil['estado_validacion'] === 'RECHAZADO' ? 'danger' : 'warning' ?> d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div>
                <?php if ($perfil['estado_validacion'] === 'RECHAZADO'): ?>
                    <strong>Tu perfil fue rechazado.</strong> Revisa las observaciones en tus documentos abajo.
                <?php else: ?>
                    <strong>Tu perfil está en revisión.</strong> No podrás recibir solicitudes hasta que un administrador apruebe tus documentos.
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($membresiaVencida): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation fa-lg"></i>
            <div>
                <strong>Tu membresía está vencida.</strong> No podrás recibir nuevas solicitudes hasta renovar tu plan.
                <a href="<?= BASE_URL ?>/membresia/planes" class="alert-link">Renovar ahora</a>
            </div>
        </div>
    <?php elseif ($diasParaVencer !== null && $diasParaVencer <= 5): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="fa-solid fa-clock fa-lg"></i>
            <div>
                Tu membresía vence en <strong><?= $diasParaVencer ?> día(s)</strong>.
                <a href="<?= BASE_URL ?>/membresia/planes" class="alert-link">Renovar ahora</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-value text-success"><?= (int) $stats['servicios_finalizados'] ?></div>
                <div class="kpi-label">Servicios finalizados</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-value text-warning"><i class="fa-solid fa-star"></i> <?= $stats['promedio_estrellas'] ?></div>
                <div class="kpi-label"><?= (int) $stats['total_calificaciones'] ?> calificaciones</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-value text-primary"><?= (int) $stats['servicios_activos'] ?></div>
                <div class="kpi-label">En curso</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-value text-secondary"><?= (int) $stats['solicitudes_pendientes'] ?></div>
                <div class="kpi-label">Por responder</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($perfil['nombre_categoria']) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($perfil['tipo_prestador'] === 'TECNICO_PROFESIONAL' ? 'Técnico Profesional' : 'Oficio Empírico') ?> · <?= (int) $perfil['experiencia_anios'] ?> años de experiencia</small>
                        </div>
                        <span class="badge-estado <?= claseBadgeEstado($perfil['estado_validacion']) ?>">
                            <?= htmlspecialchars($perfil['estado_validacion']) ?>
                        </span>
                    </div>

                    <?php if ($perfil['estado_validacion'] === 'APROBADO'): ?>
                        <button id="btnDisponibilidad" class="btn w-100 <?= $perfil['estado_disponibilidad'] === 'DISPONIBLE' ? 'btn-success' : 'btn-outline-secondary' ?>"
                                data-estado="<?= $perfil['estado_disponibilidad'] ?>">
                            <i class="fa-solid fa-power-off me-1"></i>
                            <?= $perfil['estado_disponibilidad'] === 'DISPONIBLE' ? 'DISPONIBLE — clic para pausar' : 'NO DISPONIBLE — clic para activar' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Mis Documentos</span>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($documentos as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($doc['tipo_documento_archivo']) ?></span>
                            <div class="text-end">
                                <span class="badge-estado <?= claseBadgeEstado($doc['estado_revision']) ?>">
                                    <?= htmlspecialchars($doc['estado_revision']) ?>
                                </span>
                                <?php if ($doc['observacion']): ?>
                                    <div class="small text-danger mt-1"><?= htmlspecialchars($doc['observacion']) ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($documentos)): ?>
                        <li class="list-group-item text-muted">No se han cargado documentos aún.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3 <?= $perfil['posicionamiento_destacado'] ? 'border-warning' : '' ?>">
                <div class="card-body">
                    <h6 class="mb-1"><i class="fa-solid fa-id-card-clip me-1"></i> Membresía</h6>
                    <p class="mb-1"><strong><?= htmlspecialchars($perfil['nombre_plan']) ?></strong></p>
                    <p class="mb-1 small text-muted">
                        Tokens disponibles: <strong><?= (int) $perfil['tokens_disponibles'] ?></strong> / <?= (int) $perfil['tokens_mensuales'] ?>
                    </p>
                    <?php if ($perfil['fin_suscripcion']): ?>
                        <p class="small text-muted mb-2">Vence: <?= date('d/m/Y', strtotime($perfil['fin_suscripcion'])) ?></p>
                    <?php endif; ?>
                    <?php if (!$perfil['posicionamiento_destacado']): ?>
                        <a href="<?= BASE_URL ?>/membresia/planes" class="btn btn-sm btn-warning w-100">
                            <i class="fa-solid fa-star me-1"></i> Mejorar a Premium
                        </a>
                    <?php else: ?>
                        <span class="badge-estado badge-finalizada w-100 d-block text-center p-2"><i class="fa-solid fa-crown me-1"></i> Perfil Destacado Activo</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>/profesional/solicitudes" class="list-group-item list-group-item-action d-flex justify-content-between">
                        <span><i class="fa-solid fa-list-check me-2 text-success"></i> Mis Solicitudes</span>
                        <?php if ($stats['solicitudes_pendientes'] > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= (int) $stats['solicitudes_pendientes'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= BASE_URL ?>/cliente/dashboard" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-right-left me-2 text-primary"></i> Modo Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnDisponibilidad')?.addEventListener('click', function () {
    const actual = this.dataset.estado;
    const nuevo = actual === 'DISPONIBLE' ? 'NO_DISPONIBLE' : 'DISPONIBLE';
    fetch('<?= BASE_URL ?>/profesional/toggleDisponibilidad', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'estado=' + nuevo
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
        else alert(data.error || 'Error al actualizar disponibilidad');
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>