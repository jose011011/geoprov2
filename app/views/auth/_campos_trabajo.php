<?php
$old       = $old ?? [];
$esTecnico = $esTecnico ?? false;
$esOtro    = (($old['id_categoria'] ?? '') === 'OTRO');
?>

<div class="fx-section-title">Tu trabajo</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="id_categoria">Especialidad</label>
        <select class="form-select val-select" id="id_categoria" name="id_categoria" required>
            <option value="">Selecciona...</option>
            <?php foreach (($categorias ?? []) as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>" <?= (($old['id_categoria'] ?? '') == $c['id_categoria']) ? 'selected' : '' ?>>
                    <?= Security::e($c['nombre_categoria']) ?>
                </option>
            <?php endforeach; ?>
            <option value="OTRO" <?= $esOtro ? 'selected' : '' ?>>Otra especialidad...</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6 <?= $esOtro ? '' : 'd-none' ?>" id="wrapper-otra-especialidad">
        <label class="form-label fw-semibold" for="otra_especialidad">¿Cuál es tu especialidad?</label>
        <input type="text" class="form-control" id="otra_especialidad" name="otra_especialidad"
               value="<?= Security::e($old['otra_especialidad'] ?? '') ?>" maxlength="80" <?= $esOtro ? 'required' : '' ?>>
        <div class="invalid-feedback"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold" for="tipo_doc">Documento</label>
        <select class="form-select val-select" id="tipo_doc" name="tipo_doc" required>
            <?php foreach (['CI' => 'Carnet de identidad', 'NIT' => 'NIT', 'EXTRANJERO' => 'Documento extranjero'] as $k => $t): ?>
                <option value="<?= $k ?>" <?= (($old['tipo_doc'] ?? 'CI') === $k) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-8">
        <label class="form-label fw-semibold" for="numero_documento">Número de documento</label>
        <input type="text" class="form-control" id="numero_documento" name="numero_documento"
               value="<?= Security::e($old['numero_documento'] ?? '') ?>" maxlength="20" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" for="experiencia_anios">Años de experiencia</label>
        <input type="number" class="form-control" id="experiencia_anios" name="experiencia"
               value="<?= Security::e($old['experiencia'] ?? '') ?>" min="0" max="50" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="tarifa">Tarifa base (Bs)</label>
        <input type="number" class="form-control" id="tarifa" name="tarifa"
               value="<?= Security::e($old['tarifa'] ?? '') ?>" min="0" max="9999" step="0.5" placeholder="Ej: 50">
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold" for="descripcion">Cuéntanos sobre tu experiencia</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" minlength="20" maxlength="500"
                  placeholder="Qué trabajos haces, desde cuándo, qué te distingue..." required><?= Security::e($old['descripcion'] ?? '') ?></textarea>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="fx-section-title">¿Dónde trabajas?</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="zona_especifica">Zona específica</label>
        <input type="text" class="form-control val-direccion" id="zona_especifica" name="zona_especifica"
               value="<?= Security::e($old['zona_especifica'] ?? '') ?>" minlength="3" maxlength="150"
               placeholder="Ej: Obrajes, Calacoto" required>
        <div class="invalid-feedback"></div>
    </div>
    <?php require __DIR__ . '/_ubicacion.php'; ?>
</div>

<div class="fx-section-title">Documentos (JPG, PNG o PDF, máximo 5 MB cada uno)</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="doc_ci_anverso">CI - anverso</label>
        <input type="file" class="form-control file-doc" id="doc_ci_anverso" name="doc_ci_anverso" accept=".jpg,.jpeg,.png,.pdf" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="doc_ci_reverso">CI - reverso</label>
        <input type="file" class="form-control file-doc" id="doc_ci_reverso" name="doc_ci_reverso" accept=".jpg,.jpeg,.png,.pdf" required>
        <div class="invalid-feedback"></div>
    </div>
    <?php if ($esTecnico): ?>
        <div class="col-12">
            <label class="form-label fw-semibold" for="doc_titulo">Título institucional</label>
            <input type="file" class="form-control file-doc" id="doc_titulo" name="doc_titulo" accept=".jpg,.jpeg,.png,.pdf" required>
            <div class="invalid-feedback"></div>
        </div>
    <?php endif; ?>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="doc_antecedentes">Antecedentes <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="file" class="form-control file-doc" id="doc_antecedentes" name="doc_antecedentes" accept=".jpg,.jpeg,.png,.pdf">
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="doc_referencias">Referencias <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="file" class="form-control file-doc" id="doc_referencias" name="doc_referencias" accept=".jpg,.jpeg,.png,.pdf">
        <div class="invalid-feedback"></div>
    </div>
</div>