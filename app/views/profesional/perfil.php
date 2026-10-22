<?php
require __DIR__ . '/../layouts/header.php';

$aprobado   = $perfil['estado_validacion'] === 'APROBADO';
$inicial    = mb_strtoupper(mb_substr($perfil['nombre'], 0, 1, 'UTF-8'), 'UTF-8');
$nombresDoc = ['CI_ANVERSO' => 'CI (anverso)', 'CI_REVERSO' => 'CI (reverso)', 'TITULO_TECNICO' => 'Título técnico',
               'CERTIFICADO_ANTECEDENTES' => 'Antecedentes', 'REFERENCIA_LABORAL' => 'Referencias', 'OTRO' => 'Otro documento'];
$claseDoc   = ['APROBADO' => 'success', 'RECHAZADO' => 'danger', 'PENDIENTE' => 'warning'];
$textoDoc   = ['APROBADO' => 'Aprobado', 'RECHAZADO' => 'Rechazado', 'PENDIENTE' => 'En revisión'];
?>

<div class="container py-4" style="max-width: 960px;">
    <h1 class="fx-page-title h3 mb-3">Mi perfil de técnico</h1>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card"><div class="card-body text-center">
                <div class="fx-pro" style="border:0;padding:0;"><div class="avatar mx-auto mb-3" style="width:72px;height:72px;font-size:1.8rem;border-radius:22px;"><?= Security::e($inicial) ?></div></div>
                <div class="fw-bold fs-5"><?= Security::e($perfil['nombre'] . ' ' . $perfil['apellido']) ?></div>
                <div class="text-muted small mb-2"><i class="<?= Security::e(Catalogo::icono($perfil['icono_fa'])) ?> me-1"></i><?= Security::e($perfil['nombre_categoria']) ?></div>
                <div class="mb-3"><?= Catalogo::estrellas((float) $perfil['promedio_estrellas']) ?> <small class="text-muted"><?= number_format((float) $perfil['promedio_estrellas'], 1) ?></small></div>
                <span class="badge text-bg-<?= $aprobado ? 'success' : 'warning' ?> mb-2"><?= $aprobado ? 'Perfil verificado' : 'En revisión' ?></span>
                <div><span class="badge text-bg-primary"><?= Security::e(Catalogo::nombrePlan($perfil['nombre_plan'])) ?></span></div>
                <hr>
                <div class="row text-center">
                    <div class="col-6"><div class="fw-bold fs-5"><?= (int) $stats['finalizadas'] ?></div><small class="text-muted">Trabajos</small></div>
                    <div class="col-6"><div class="fw-bold fs-5"><?= (int) $perfil['experiencia_anios'] ?></div><small class="text-muted">Años exp.</small></div>
                </div>
            </div></div>
        </div>

        <div class="col-lg-8">
            <form method="post" action="<?= BASE_URL ?>/profesional/actualizarPerfil" class="card mb-4" novalidate><div class="card-body">
                <?= Security::campo() ?>
                <h2 class="h5 fw-bold mb-3">Datos públicos</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Correo</label>
                        <input type="email" class="form-control" value="<?= Security::e($perfil['correo']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="celular">Celular</label>
                        <input type="tel" class="form-control val-celular" id="celular" name="celular" value="<?= Security::e($perfil['celular']) ?>" inputmode="numeric" maxlength="8" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="macrodistrito_base">Macrodistrito donde trabajas</label>
                        <select class="form-select val-select" id="macrodistrito_base" name="macrodistrito_base" required>
                            <?php foreach (Catalogo::MACRODISTRITOS as $k => $t): ?>
                                <option value="<?= $k ?>" <?= $perfil['macrodistrito_base'] === $k ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="zona_especifica">Zona específica</label>
                        <input type="text" class="form-control val-direccion" id="zona_especifica" name="zona_especifica" value="<?= Security::e($perfil['zona_especifica']) ?>" minlength="3" maxlength="150" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="tarifa_base">Tarifa base (Bs)</label>
                        <input type="number" class="form-control" id="tarifa_base" name="tarifa_base" value="<?= Security::e($perfil['tarifa_base']) ?>" min="0" max="9999" step="0.5">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="descripcion_servicio">Sobre mi trabajo</label>
                        <textarea class="form-control" id="descripcion_servicio" name="descripcion_servicio" rows="4" minlength="20" maxlength="500" required><?= Security::e($perfil['descripcion_servicio']) ?></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <button class="btn btn-primary mt-4"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios</button>
            </div></form>

            
            <h2 class="h5 fw-bold mb-2">Mis documentos</h2>
            <div class="card"><div class="card-body">
                <?php if (!$documentos): ?><div class="text-muted small">Aún no hay documentos cargados.</div><?php endif; ?>
                <?php foreach ($documentos as $d): $est = strtoupper((string) $d['estado_revision']); ?>
                    <div class="py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fa-regular fa-file-lines me-2 text-muted"></i><?= Security::e($nombresDoc[$d['tipo_documento_archivo']] ?? $d['tipo_documento_archivo']) ?></span>
                            <span class="badge text-bg-<?= $claseDoc[$est] ?? 'secondary' ?>"><?= $textoDoc[$est] ?? $est ?></span>
                        </div>
                        <?php if ($est === 'RECHAZADO'): ?>
                            <?php if (!empty($d['observacion'])): ?>
                                <div class="small text-danger mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Motivo: <?= Security::e($d['observacion']) ?></div>
                            <?php endif; ?>
                            <form method="post" action="<?= BASE_URL ?>/profesional/reenviarDocumento" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 mt-2">
                                <?= Security::campo() ?>
                                <input type="hidden" name="id_documento" value="<?= (int) $d['id_documento'] ?>">
                                <input type="file" name="archivo" class="form-control form-control-sm" style="max-width:280px" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                <button class="btn btn-primary btn-sm"><i class="fa-solid fa-rotate me-1"></i>Volver a enviar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div></div>

        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/auth-validation.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>