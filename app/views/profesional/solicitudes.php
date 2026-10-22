<?php
require __DIR__ . '/../layouts/header.php';
$tabs = ['PENDIENTE' => 'Nuevas', 'ACTIVAS' => 'En curso', 'FINALIZADA' => 'Finalizadas', 'TODAS' => 'Todas'];
$vacios = [
    'PENDIENTE'  => 'No hay solicitudes nuevas por ahora. Mantente disponible y te avisamos.',
    'ACTIVAS'    => 'No tienes trabajos en curso.',
    'FINALIZADA' => 'Todavía no finalizaste ningún trabajo.',
    'TODAS'      => 'Aún no tienes solicitudes.',
];
?>

<div class="container py-4" style="max-width: 960px;">
    <h1 class="fx-page-title h3 mb-3">Solicitudes de trabajo</h1>

    <div class="fx-filter d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($tabs as $k => $t): ?>
            <a href="<?= BASE_URL ?>/profesional/solicitudes?estado=<?= $k ?>" class="btn btn-sm <?= $filtroActual === $k ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= $t ?> <span class="badge text-bg-light ms-1"><?= (int) $conteos[$k] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($filtroActual === 'PENDIENTE' && $trabajoActivo): ?>
        <div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>Tienes un trabajo en curso. Termínalo para ver y aceptar nuevas solicitudes abiertas.</div>
    <?php if ($filtroActual === 'PENDIENTE' && (int) $perfil['retraso_cascada_min'] > 0): ?>
        <div class="alert alert-light border small"><i class="fa-solid fa-hourglass-half me-1"></i>Tu plan ve las solicitudes nuevas <strong><?= (int) $perfil['retraso_cascada_min'] ?> minutos</strong> después que los técnicos Premium. <a href="<?= BASE_URL ?>/profesional/comprarTokens">Mejorar mi plan</a></div>
    <?php endif; ?>
    <?php if ($filtroActual === 'PENDIENTE' && (int) $perfil['tokens_disponibles'] <= 0 && (int) $perfil['posicionamiento_destacado'] !== 1): ?>
        <div class="alert alert-warning small"><i class="fa-solid fa-coins me-1"></i>No tienes tokens: puedes ver las solicitudes pero no aceptarlas. <a href="<?= BASE_URL ?>/profesional/comprarTokens">Comprar tokens</a></div>
    <?php endif; ?>
    <?php endif; ?>
    <?php if ($filtroActual === 'PENDIENTE' && $perfil['estado_validacion'] !== 'APROBADO'): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-hourglass-half me-2"></i>Podrás ver y aceptar solicitudes cuando un administrador apruebe tu perfil.</div>
    <?php endif; ?>

    <?php if (!$solicitudes): ?>
        <div class="text-center text-muted py-5"><i class="fa-regular fa-folder-open fs-1 d-block mb-2"></i><?= $vacios[$filtroActual] ?></div>
    <?php endif; ?>

    <div class="d-grid gap-3">
        <?php foreach ($solicitudes as $s):
            $estado = $s['estado_servicio'];
            $clase  = $estado === 'PENDIENTE' ? 'nueva' : (in_array($estado, ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'], true) ? 'activa' : ($estado === 'FINALIZADA' ? 'fin' : ''));
            $ver    = $estado !== 'PENDIENTE'; // la dirección exacta se muestra solo después de aceptar ?>
            <div class="card fx-job <?= $clase ?>"><div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <div class="small text-muted"><?= Security::e($s['codigo_seguimiento']) ?> · <?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?>
                            <?php if (($s['origen'] ?? '') === 'DIRECTA'): ?><span class="badge text-bg-primary ms-1">Te lo pidieron a ti</span><?php endif; ?>
                        </div>
                        <div class="fw-bold fs-5"><?= Security::e($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></div>
                    </div>
                    <div><?= Catalogo::badgeEstado($estado) ?></div>
                </div>

                <div class="mb-2"><?= nl2br(Security::e(mb_substr((string) $s['descripcion_problema'], 0, 300))) ?></div>
                <div class="small text-muted mb-3">
                    <i class="fa-solid fa-location-dot me-1"></i>
                    <?= $ver ? Security::e($s['direccion_servicio']) . ' · ' : '' ?><?= Security::e($s['zona']) ?> · <?= Security::e(Catalogo::macroEtiqueta($s['macrodistrito'])) ?>
                    <?php if ($estado === 'FINALIZADA' && !empty($s['precio_acordado'])): ?>
                        · <strong class="text-primary">Bs <?= number_format((float) $s['precio_acordado'], 2) ?></strong>
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php if ($perfil['estado_validacion'] === 'APROBADO' || $estado !== 'PENDIENTE'): require __DIR__ . '/_acciones_solicitud.php'; endif; ?>
                    <?php if ($estado !== 'PENDIENTE' || !empty($s['id_profesional'])): ?>
                        <a class="btn btn-link btn-sm text-decoration-none" href="<?= BASE_URL ?>/solicitud/detalle/<?= (int) $s['id_solicitud'] ?>">Ver detalle</a>
                    <?php endif; ?>
                </div>
            </div></div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>