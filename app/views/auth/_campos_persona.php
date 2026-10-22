<?php $v = fn(string $k): string => Security::e($old[$k] ?? ''); ?>

<div class="fx-section-title">Tus datos</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="nombre">Nombre(s)</label>
        <input type="text" class="form-control valida-nombre" id="nombre" name="nombre" value="<?= $v('nombre') ?>"
               maxlength="60" autocomplete="given-name" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="apellido_paterno">Apellido paterno</label>
        <input type="text" class="form-control valida-apellido" id="apellido_paterno" name="apellido_paterno"
               value="<?= $v('apellido_paterno') ?>" maxlength="40" autocomplete="family-name" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="apellido_materno">Apellido materno <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="text" class="form-control valida-apellido" id="apellido_materno" name="apellido_materno"
               value="<?= $v('apellido_materno') ?>" maxlength="40">
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="celular">Celular</label>
        <input type="tel" class="form-control val-celular" id="celular" name="celular" value="<?= $v('celular') ?>"
               inputmode="numeric" maxlength="8" placeholder="7XXXXXXX" autocomplete="tel-national" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold" for="correo">Correo electrónico</label>
        <input type="email" class="form-control val-correo" id="correo" name="correo" value="<?= $v('correo') ?>"
               maxlength="120" autocomplete="email" required>
        <div class="invalid-feedback"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="password">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required>
            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password"
                    aria-label="Mostrar u ocultar contraseña"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="invalid-feedback"></div>
        <div class="form-text">Mínimo 8 caracteres, una mayúscula, un número y un símbolo (@$!%*?&-_.,).</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold" for="password_confirm">Repite la contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirm"
                    aria-label="Mostrar u ocultar contraseña"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="invalid-feedback"></div>
    </div>
</div>