<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <h3 class="mb-4">Mis Solicitudes</h3>

    <?php foreach ($solicitudes as $s): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($s['prof_nombre'] . ' ' . $s['prof_apellido']) ?></h6>
                        <p class="text-muted small mb-1"><?= htmlspecialchars($s['nombre_categoria']) ?></p>
                    </div>
                    <span class="badge-estado <?= claseBadgeEstado($s['estado_servicio']) ?>"><?= htmlspecialchars($s['estado_servicio']) ?></span>
                </div>

                <p class="small mb-2"><?= htmlspecialchars(substr($s['descripcion_problema'], 0, 150)) ?></p>
                <p class="text-muted small mb-2">
                    <i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($s['codigo_seguimiento']) ?>
                    · <?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?>
                </p>

                <a href="<?= BASE_URL ?>/solicitud/detalle/<?= $s['id_solicitud'] ?>" class="btn btn-sm btn-outline-success">
                    Ver detalle
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($solicitudes)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <p>Aún no has realizado ninguna solicitud.</p>
            <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-success mt-2">Buscar un servicio</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../app/views/layouts/footer.php"; ?>