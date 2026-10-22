<?php
require __DIR__ . '/../layouts/header.php';

$nombresDoc = ['CI_ANVERSO' => 'CI (anverso)', 'CI_REVERSO' => 'CI (reverso)', 'TITULO_TECNICO' => 'Título técnico',
               'CERTIFICADO_ANTECEDENTES' => 'Antecedentes', 'REFERENCIA_LABORAL' => 'Referencias', 'OTRO' => 'Otro documento'];
$claseDoc   = ['APROBADO' => 'success', 'RECHAZADO' => 'danger', 'PENDIENTE' => 'warning'];
$idProf     = (int) $tecnico['id_profesional'];
?>

<div class="container py-4" style="max-width: 1000px;">
    <a href="<?= BASE_URL ?>/admin/revisionTecnicos" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Volver a la lista</a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 mb-3">
        <div>
            <h1 class="fx-page-title h3 mb-0"><?= Security::e($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?></h1>
            <div class="text-muted small"><?= Security::e($tecnico['correo']) ?> · <?= Security::e($tecnico['celular']) ?></div>
        </div>
        <span class="badge fs-6 text-bg-<?= ['APROBADO' => 'success', 'RECHAZADO' => 'danger'][$tecnico['estado_validacion']] ?? 'warning' ?>"><?= Security::e(ucfirst(strtolower($tecnico['estado_validacion']))) ?></span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card h-100"><div class="card-body">
            <div class="small text-muted">Datos del trabajo</div>
            <div class="fw-semibold"><?= Security::e($tecnico['nombre_categoria']) ?> · <?= $tecnico['tipo_prestador'] === 'TECNICO_PROFESIONAL' ? 'Técnico profesional' : 'Trabajador de oficio' ?></div>
            <div class="small"><?= (int) $tecnico['experiencia_anios'] ?> años de experiencia · tarifa desde Bs <?= number_format((float) $tecnico['tarifa_base'], 0) ?></div>
            <div class="small text-muted"><?= Security::e(Catalogo::macroEtiqueta($tecnico['macrodistrito_base'])) ?> · <?= Security::e($tecnico['zona_especifica']) ?></div>
            <hr class="my-2">
            <div class="small text-muted">Documento de identidad</div>
            <div class="fw-semibold"><?= Security::e($tecnico['tipo_documento_identidad'] ?? 'CI') ?> <?= Security::e($tecnico['numero_documento']) ?></div>
        </div></div></div>
        <div class="col-md-6"><div class="card h-100"><div class="card-body">
            <div class="small text-muted">Descripción que escribió</div>
            <div><?= nl2br(Security::e($tecnico['descripcion_servicio'])) ?></div>
            <hr class="my-2">
            <div class="small text-muted">Plan: <?= Security::e(Catalogo::nombrePlan($tecnico['nombre_plan'])) ?> · <?= (int) $tecnico['tokens_disponibles'] ?> tokens · registrado el <?= date('d/m/Y', strtotime($tecnico['fecha_registro'])) ?></div>
        </div></div></div>
    </div>

    <h2 class="h5 fw-bold mb-3">Documentos</h2>
    <?php if (!$documentos): ?><div class="alert alert-warning">Este técnico no subió documentos.</div><?php endif; ?>

    <div class="row g-3 mb-4">
        <?php foreach ($documentos as $d):
            $est   = strtoupper((string) $d['estado_revision']);
            $esPdf = strtolower(pathinfo((string) $d['archivo_url'], PATHINFO_EXTENSION)) === 'pdf';
            $urlDoc = BASE_URL . '/admin/verDocumento/' . (int) $d['id_documento']; ?>
            <div class="col-md-6"><div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong><?= Security::e($nombresDoc[$d['tipo_documento_archivo']] ?? $d['tipo_documento_archivo']) ?></strong>
                    <span class="badge text-bg-<?= $claseDoc[$est] ?? 'secondary' ?>"><?= Security::e(ucfirst(strtolower($est))) ?></span>
                </div>
                <a href="<?= $urlDoc ?>" target="_blank" rel="noopener" class="d-block mb-2">
                    <?php if ($esPdf): ?>
                        <div class="border rounded p-4 text-center text-muted"><i class="fa-regular fa-file-pdf fs-1 d-block"></i>Abrir PDF</div>
                    <?php else: ?>
                        <img src="<?= $urlDoc ?>" alt="Documento" class="img-fluid rounded border" style="max-height: 260px; width: 100%; object-fit: contain; background:#f6f8fc;">
                    <?php endif; ?>
                </a>
                <?php if (!empty($d['observacion'])): ?><div class="small text-danger mb-2"><i class="fa-solid fa-comment me-1"></i><?= Security::e($d['observacion']) ?></div><?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/admin/revisarDocumentoTecnico" class="d-flex flex-wrap gap-2 m-0">
                    <?= Security::campo() ?>
                    <input type="hidden" name="id_documento" value="<?= (int) $d['id_documento'] ?>">
                    <input type="hidden" name="id_profesional" value="<?= $idProf ?>">
                    <input type="text" name="observacion" class="form-control form-control-sm" placeholder="Motivo (obligatorio si rechazas)" maxlength="250">
                    <button name="decision" value="APROBADO" class="btn btn-primary btn-sm">Aprobar</button>
                    <button name="decision" value="RECHAZADO" class="btn btn-outline-danger btn-sm">Rechazar</button>
                </form>
            </div></div></div>
        <?php endforeach; ?>
    </div>

    <h2 class="h5 fw-bold mb-3">Decisión final</h2>
    <form method="post" action="<?= BASE_URL ?>/admin/decidirValidacionTecnico" class="card"><div class="card-body">
        <?= Security::campo() ?>
        <input type="hidden" name="id_profesional" value="<?= $idProf ?>">
        <p class="text-muted small">Para <strong>aprobar</strong> deben estar aprobados el CI (anverso y reverso)<?= $tecnico['tipo_prestador'] === 'TECNICO_PROFESIONAL' ? ' y el título' : '' ?>. Para <strong>rechazar</strong> escribe el motivo: el técnico lo verá.</p>
        <textarea name="observacion" class="form-control mb-3" rows="2" maxlength="250" placeholder="Observación (obligatoria al rechazar)"></textarea>
        <div class="d-flex flex-wrap gap-2">
            <button name="decision" value="APROBAR" class="btn btn-primary"><i class="fa-solid fa-circle-check me-1"></i>Aprobar técnico</button>
            <button name="decision" value="RECHAZAR" class="btn btn-outline-danger" onclick="return confirm('¿Rechazar a este técnico?');"><i class="fa-solid fa-circle-xmark me-1"></i>Rechazar</button>
        </div>
    </div></form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>