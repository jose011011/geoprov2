<?php require_once "../app/views/layouts/header.php"; ?>
<div class="container py-4">
    <h3 class="mb-4">Planes de Membresía</h3>
    <div class="row g-3">
        <?php foreach ($planes as $p): ?>
            <div class="col-md-4">
                <div class="card h-100 <?= $p['posicionamiento_destacado'] ? 'border-warning' : '' ?> <?= (int)$perfil['id_plan'] === (int)$p['id_plan'] ? 'border-success border-3' : '' ?>">
                    <div class="card-body text-center">
                        <?php if ((int)$perfil['id_plan'] === (int)$p['id_plan']): ?>
                            <span class="badge bg-success mb-2">Plan Actual</span>
                        <?php endif; ?>
                        <h5><?= htmlspecialchars($p['nombre_plan']) ?></h5>
                        <div class="fs-3 fw-bold my-2">Bs <?= number_format($p['precio_mensual'], 2) ?><small class="fs-6 text-muted">/mes</small></div>
                        <p class="text-muted small"><?= htmlspecialchars($p['descripcion']) ?></p>
                        <ul class="list-unstyled small text-start">
                            <li><i class="fa-solid fa-check text-success me-1"></i> <?= (int)$p['tokens_mensuales'] ?> tokens/mes</li>
                            <?php if ($p['posicionamiento_destacado']): ?>
                                <li><i class="fa-solid fa-check text-success me-1"></i> Posicionamiento destacado</li>
                            <?php endif; ?>
                        </ul>
                        <?php if ((int)$perfil['id_plan'] !== (int)$p['id_plan']): ?>
                            <?php if ((float)$p['precio_mensual'] > 0): ?>
                                <a href="<?= BASE_URL ?>/membresia/comprobantePlan/<?= $p['id_plan'] ?>" class="btn btn-success w-100">Elegir plan</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-4 p-3">
        <h6>¿Se te acabaron los tokens?</h6>
        <a href="<?= BASE_URL ?>/membresia/comprobanteTokens" class="btn btn-outline-warning">
            <i class="fa-solid fa-coins me-1"></i> Comprar paquete de 10 tokens (Bs 10)
        </a>
    </div>
</div>
<?php require_once "../app/views/layouts/footer.php"; ?>