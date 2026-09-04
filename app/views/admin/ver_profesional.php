<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver</a>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4><?= htmlspecialchars($perfil['nombre'] . ' ' . $perfil['apellido']) ?></h4>
                    <p class="text-muted mb-2"><?= htmlspecialchars($perfil['correo']) ?> · <?= htmlspecialchars($perfil['celular']) ?></p>
                </div>
                <span class="badge-estado <?= claseBadgeEstado($perfil['estado_validacion']) ?> fs-6">
                    <?= htmlspecialchars($perfil['estado_validacion']) ?>
                </span>
            </div>

            <p class="mb-1"><strong>Categoría:</strong> <?= htmlspecialchars($perfil['nombre_categoria']) ?>
               · <strong>Tipo:</strong> <?= htmlspecialchars($perfil['tipo_prestador']) ?></p>
            <p class="mb-1"><strong>Documento:</strong> <?= htmlspecialchars($perfil['tipo_documento_identidad']) ?> - <?= htmlspecialchars($perfil['numero_documento']) ?></p>
            <p class="mb-3"><strong>Zona:</strong> <?= htmlspecialchars($perfil['macrodistrito_base']) ?> - <?= htmlspecialchars($perfil['zona_especifica']) ?></p>

            <p class="text-muted"><?= nl2br(htmlspecialchars($perfil['descripcion_servicio'])) ?></p>

            <div class="d-flex gap-2 flex-wrap mt-3">
                <?php if ($perfil['estado_validacion'] !== 'APROBADO'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/aprobarProfesional" class="d-inline">
                    <input type="hidden" name="id_profesional" value="<?= $perfil['id_profesional'] ?>">
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('¿Aprobar a este profesional? Requiere todos los documentos aprobados.')">
                        <i class="fa-solid fa-check"></i> Aprobar profesional
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($perfil['estado_validacion'] !== 'RECHAZADO'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/rechazarProfesional" class="d-inline">
                    <input type="hidden" name="id_profesional" value="<?= $perfil['id_profesional'] ?>">
                    <button type="submit" class="btn btn-outline-danger"
                            onclick="return confirm('¿Rechazar a este profesional?')">
                        <i class="fa-solid fa-xmark"></i> Rechazar profesional
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Documentos cargados</h5>
    <div class="row g-3">
        <?php foreach ($documentos as $doc): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <?php
                        $rutaArchivo = BASE_URL . '/' . $doc['archivo_url'];
                        $esPdf = str_ends_with(strtolower($doc['archivo_url']), '.pdf');
                    ?>
                    <?php if ($esPdf): ?>
                        <div class="p-4 text-center bg-light" style="border-radius: 14px 14px 0 0;">
                            <a href="<?= $rutaArchivo ?>" target="_blank" class="text-decoration-none">
                                <i class="fa-solid fa-file-pdf fa-3x text-danger"></i>
                                <div class="small mt-2 text-muted">Ver documento PDF</div>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?= $rutaArchivo ?>" target="_blank">
                            <img src="<?= $rutaArchivo ?>" class="card-img-top" style="height:180px;object-fit:cover; border-radius:14px 14px 0 0;">
                        </a>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><?= htmlspecialchars($doc['tipo_documento_archivo']) ?></h6>
                            <span class="badge-estado <?= claseBadgeEstado($doc['estado_revision']) ?>">
                                <?= htmlspecialchars($doc['estado_revision']) ?>
                            </span>
                        </div>

                        <?php if ($doc['observacion']): ?>
                            <p class="small text-danger mb-2"><i class="fa-solid fa-circle-exclamation me-1"></i><?= htmlspecialchars($doc['observacion']) ?></p>
                        <?php endif; ?>

                        <form method="POST" action="<?= BASE_URL ?>/admin/revisarDocumento" class="d-flex gap-2">
                            <input type="hidden" name="id_documento" value="<?= $doc['id_documento'] ?>">
                            <input type="hidden" name="id_profesional" value="<?= $perfil['id_profesional'] ?>">
                            <input type="text" name="observacion" class="form-control form-control-sm" placeholder="Observación (opcional)">
                            <button type="submit" name="decision" value="APROBADO" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i></button>
                            <button type="submit" name="decision" value="RECHAZADO" class="btn btn-sm btn-danger"><i class="fa-solid fa-xmark"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($documentos)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <p>Este profesional no ha cargado documentos todavía.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../app/views/layouts/footer.php"; ?>