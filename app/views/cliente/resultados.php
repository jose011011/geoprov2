<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver a categorías</a>

    <?php if (!empty($errorSql)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorSql) ?></div>
    <?php endif; ?>

    <h3 class="mb-1"><i class="<?= htmlspecialchars($categoria['icono_fa']) ?> text-success me-2"></i><?= htmlspecialchars($categoria['nombre_categoria']) ?></h3>
    <p class="text-muted mb-4"><?= count($profesionales) ?> profesionales disponibles cerca de ti</p>

    <div class="row g-3">
        <?php foreach ($profesionales as $p): ?>
            <div class="col-md-6">
                <div class="card h-100 <?= $p['posicionamiento_destacado'] ? 'border-warning' : '' ?>">
                    <div class="card-body">
                        <?php if ($p['posicionamiento_destacado']): ?>
                            <span class="badge-estado badge-aprobado mb-2"><i class="fa-solid fa-star"></i> Destacado</span>
                        <?php endif; ?>
                        <h5 class="mb-1"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></h5>
                        <p class="text-muted small mb-2">
                            <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($p['zona_especifica']) ?>
                            · <strong><?= $p['distancia_km'] ?? '?' ?> km</strong>
                        </p>
                        <p class="small mb-2">
                            <i class="fa-solid fa-star text-warning"></i> <?= $p['promedio_estrellas'] ?>
                            · <?= (int) $p['total_servicios'] ?> servicios realizados
                            · <?= (int) $p['experiencia_anios'] ?> años de experiencia
                        </p>
                        <p class="small text-muted"><?= nl2br(htmlspecialchars(substr($p['descripcion_servicio'], 0, 120))) ?>...</p>
                        <a href="<?= BASE_URL ?>/solicitud/crear/<?= $p['id_profesional'] ?>" class="btn btn-success w-100">
                            <i class="fa-solid fa-paper-plane me-1"></i> Solicitar servicio
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($profesionales) && empty($errorSql)): ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fa-solid fa-user-slash"></i>
                    <p>No hay profesionales disponibles en esta categoría cerca de ti por el momento.</p>
                    <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-outline-success mt-2">Probar otra categoría</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../app/views/layouts/footer.php"; ?>