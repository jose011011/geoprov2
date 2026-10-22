<?php
require __DIR__ . '/../layouts/header.php';
$activas = (int) ($stats['activas'] ?? 0);
$atajos = [
    ['/cliente/especialistas',    'fa-users-gear', 'Catálogo de técnicos', 'Explora y elige tú mismo'],
    ['/solicitud/misSolicitudes', 'fa-list-check', 'Mis pedidos',          $activas . ' en curso'],
    ['/cliente/favoritos',        'fa-heart',      'Favoritos',            (int) ($stats['favoritos'] ?? 0) . ' guardados'],
    ['/cliente/perfil',           'fa-id-card',    'Mi perfil',            'Dirección y ubicación'],
];
?>

<div class="container py-4">

    <?php if (!empty($cliente) && trim((string) $cliente['direccion_referencia']) === ''): ?>
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="fa-solid fa-triangle-exclamation me-2"></i>Completa tu dirección para que los técnicos sepan dónde ir.</span>
            <a href="<?= BASE_URL ?>/cliente/perfil" class="btn btn-sm btn-primary">Completar mi perfil</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($solicitudActiva)): ?>
        <div class="card mb-4"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="text-muted small">Tienes un servicio en curso · <?= Security::e($solicitudActiva['codigo_seguimiento']) ?></div>
                <div class="fw-bold fs-5">
                    <?= Security::e($solicitudActiva['nombre_categoria'] ?? 'Servicio') ?>
                    <?= Catalogo::badgeEstado($solicitudActiva['estado_servicio']) ?>
                </div>
                <div class="text-muted small">
                    <?= $solicitudActiva['prof_nombre']
                        ? 'Técnico: ' . Security::e($solicitudActiva['prof_nombre'] . ' ' . $solicitudActiva['prof_apellido'])
                        : 'Buscando al mejor técnico para ti...' ?>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/solicitud/detalle/<?= (int) $solicitudActiva['id_solicitud'] ?>" class="btn btn-primary">
                <i class="fa-solid fa-location-dot me-1"></i>Ver seguimiento
            </a>
        </div></div>
    <?php endif; ?>

    <div class="fx-hero-card mb-4">
        <h1 class="fx-page-title h2 mb-1">¿Qué necesitas arreglar hoy?</h1>
        <p class="text-muted mb-3">Cuéntalo con tus palabras y te conectamos con el técnico indicado.</p>
        <form method="post" action="<?= BASE_URL ?>/cliente/buscarEspecialista">
            <?= Security::campo() ?>
            <div class="row g-2">
                <div class="col-lg-9">
                    <textarea class="form-control form-control-lg" name="descripcion" rows="2" maxlength="1000"
                              placeholder="Ej: se quemó el foco de mi sala y salta el tablero" required></textarea>
                </div>
                <div class="col-lg-3 d-grid">
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Buscar técnico</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($atajos as [$ruta, $icono, $titulo, $detalle]): ?>
            <div class="col-6 col-lg-3">
                <a class="fx-quick" href="<?= BASE_URL . $ruta ?>">
                    <span class="ico"><i class="fa-solid <?= $icono ?>"></i></span>
                    <span><span class="t d-block"><?= $titulo ?></span><small><?= $detalle ?></small></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="h5 fw-bold mb-3">O elige un tipo de servicio</h2>
    <div class="row g-3 mb-4">
        <?php foreach ($categorias as $c): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a class="fx-cat-tile" href="<?= BASE_URL ?>/cliente/buscarEspecialista?cat=<?= (int) $c['id_categoria'] ?>">
                    <span class="ico"><i class="<?= Security::e(Catalogo::icono($c['icono_fa'])) ?>"></i></span>
                    <span class="nombre"><?= Security::e($c['nombre_categoria']) ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
        <?php if ((int) ($_SESSION['role_id'] ?? 0) === 4): ?>
        <div class="fx-hero-card mb-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="fw-bold fs-5">¿Tienes un oficio o eres técnico?</div>
                <div class="text-muted small">Ofrece tus servicios con esta misma cuenta, sin registrarte otra vez.</div>
            </div>
            <a href="<?= BASE_URL ?>/cliente/ofrecerServicios" class="btn btn-primary"><i class="fa-solid fa-briefcase me-2"></i>Ofrecer mis servicios</a>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 fw-bold mb-0">Tus técnicos favoritos</h2>
                <a href="<?= BASE_URL ?>/cliente/favoritos" class="small fw-semibold">Ver todos</a>
            </div>
            <?php if (!$favoritos): ?>
                <div class="fx-order text-muted small"><i class="fa-regular fa-heart me-2"></i>Aún no tienes favoritos. Guarda a tus técnicos de confianza desde el catálogo.</div>
            <?php endif; ?>
            <div class="d-grid gap-2">
                <?php foreach ($favoritos as $f): ?>
                    <div class="fx-order d-flex align-items-center gap-3">
                        <div class="fx-pro avatar" style="width:44px;height:44px;border-radius:14px;padding:0;border:0;display:grid;place-items:center;background:var(--fx-gradient);font-weight:800;">
                            <?= Security::e(mb_strtoupper(mb_substr($f['nombre'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-semibold text-truncate"><?= Security::e($f['nombre'] . ' ' . $f['apellido']) ?></div>
                            <div class="small text-muted"><?= Security::e($f['nombre_categoria']) ?> · <?= Catalogo::estrellas((float) $f['promedio_estrellas']) ?></div>
                        </div>
                        <form method="post" action="<?= BASE_URL ?>/cliente/solicitarManual" class="m-0">
                            <?= Security::campo() ?>
                            <input type="hidden" name="id_profesional" value="<?= (int) $f['id_profesional'] ?>">
                            <button class="btn btn-outline-primary btn-sm">Pedir</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 fw-bold mb-0">Pedidos recientes</h2>
                <a href="<?= BASE_URL ?>/cliente/historial" class="small fw-semibold">Ver historial</a>
            </div>
            <?php if (!$recientes): ?>
                <div class="fx-order text-muted small"><i class="fa-regular fa-clipboard me-2"></i>Todavía no hiciste ningún pedido.</div>
            <?php endif; ?>
            <div class="d-grid gap-2">
                <?php foreach ($recientes as $r): ?>
                    <a class="fx-order d-flex justify-content-between align-items-center gap-3 text-decoration-none text-reset"
                       href="<?= BASE_URL ?>/solicitud/detalle/<?= (int) $r['id_solicitud'] ?>">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate"><?= Security::e($r['nombre_categoria'] ?? 'Servicio') ?></div>
                            <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($r['fecha_solicitud'])) ?></div>
                        </div>
                        <?= Catalogo::badgeEstado($r['estado_servicio']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>