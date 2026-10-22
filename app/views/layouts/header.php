<?php require_once __DIR__ . '/../../helpers/estado_helper.php'; ?>
<?php
if (!defined('BASE_URL')) {
    die('BASE_URL no está definida. Verifique app/config/config.php');
}

$rolActual         = (int) ($_SESSION['role_id'] ?? 0);
$nombreUsuario     = $_SESSION['user_nombre'] ?? 'Usuario';
$inicialUsuario    = mb_strtoupper(mb_substr($nombreUsuario, 0, 1, 'UTF-8'), 'UTF-8');
$rutaActual        = trim($_GET['url'] ?? '', '/');
$enModoProfesional = str_starts_with($rutaActual, 'profesional');
$modoPro           = ($rolActual === 3 && $enModoProfesional);
$esAdmin           = in_array($rolActual, [1, 2], true);
$inicioUrl         = BASE_URL . '/' . ($rolActual === 3 ? 'profesional/dashboard' : ($esAdmin ? 'admin/dashboard' : 'cliente/dashboard'));
$perfilUrl         = BASE_URL . '/' . ($modoPro ? 'profesional/perfil' : 'cliente/perfil');

// Versión del CSS según la fecha del archivo (evita problemas de caché al editar)
$cssDir = __DIR__ . '/../../../public/css/';
$cssV   = fn(string $f): int => (int) @filemtime($cssDir . $f);

// Menú principal: [ruta, icono, texto, prefijos que lo marcan como activo]
if ($esAdmin) {
    $menu = [];
    foreach ([
        ['dashboard',        'fa-gauge-high',         'Panel',            'admin/dashboard'],
        ['revisionTecnicos', 'fa-user-check',         'Validar técnicos', 'admin/revisionTecnico'],
        ['revisionPagos',    'fa-money-check-dollar', 'Pagos',            'admin/revisionPagos'],
        ['clientes',         'fa-users',              'Clientes',         'admin/clientes'],
        ['categorias',       'fa-tags',               'Categorías',       'admin/categorias'],
        ['planes',           'fa-layer-group',        'Planes',           'admin/planes'],
        ['reportes',         'fa-chart-line',         'Reportes',         'admin/reportes'],
        ['informe',          'fa-file-pdf',           'Informe PDF',      'admin/informe'],
        ['auditoria',        'fa-clipboard-list',     'Auditoría',        'admin/auditoria'],
    ] as [$metodo, $icono, $texto, $prefijo]) {
        if (!class_exists('AdminController') || !method_exists('AdminController', $metodo)) { continue; }
        if (in_array($metodo, ['planes', 'auditoria'], true) && $rolActual !== 1) { continue; }
        $menu[] = ['/admin/' . $metodo, $icono, $texto, [$prefijo]];
    }
} elseif ($modoPro) {
    $menu = [
        ['/profesional/dashboard',     'fa-gauge-high',        'Inicio',          ['profesional/dashboard']],
        ['/profesional/solicitudes',   'fa-list-check',        'Solicitudes',     ['profesional/solicitudes', 'profesional/mapaViaje']],
        ['/profesional/historial',     'fa-clock-rotate-left', 'Historial',       ['profesional/historial']],
        ['/profesional/comprarTokens', 'fa-coins',             'Tokens y planes', ['profesional/comprarTokens']],
        ['/profesional/perfil',        'fa-id-card',           'Mi perfil',       ['profesional/perfil']],
    ];
} elseif (in_array($rolActual, [3, 4], true)) {
    $menu = [
        ['/cliente/dashboard',        'fa-house',             'Inicio',      ['cliente/dashboard', 'cliente/buscarEspecialista']],
        ['/cliente/especialistas',    'fa-users-gear',        'Técnicos',    ['cliente/especialistas']],
        ['/solicitud/misSolicitudes', 'fa-list-check',        'Mis pedidos', ['solicitud/', 'chat/']],
        ['/cliente/favoritos',        'fa-heart',             'Favoritos',   ['cliente/favoritos']],
        ['/cliente/historial',        'fa-clock-rotate-left', 'Historial',   ['cliente/historial']],
        ['/cliente/perfil',           'fa-id-card',           'Mi perfil',   ['cliente/perfil']],
    ];
} else {
    $menu = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1d3fb4">
    <meta name="csrf-token" content="<?= Security::token() ?>">
    <title><?= Security::e($titulo ?? 'FixGeo') ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/img/logo-fixgeo.png" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link href="<?= BASE_URL ?>/css/fixgeo-theme.css?v=<?= $cssV('fixgeo-theme.css') ?>" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/style.css?v=<?= $cssV('style.css') ?>" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/fixgeo-app.css?v=<?= $cssV('fixgeo-app.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar fx-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">

        <div class="d-flex align-items-center gap-2">
            <?php if ($menu): ?>
                                <button class="fx-icon-btn d-lg-none border" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuPrincipal"
                        aria-controls="menuPrincipal" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
            <?php endif; ?>
            <a class="fx-brand" href="<?= $inicioUrl ?>">
                <span class="fx-brand-mark"><i class="fa-solid fa-location-dot"></i></span>
                <span>Fix<b>Geo</b></span>
            </a>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">

            <?php if ($rolActual === 3): ?>
                <?php if ($modoPro): ?>
                    <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-fx-gradient btn-sm">
                        <i class="fa-solid fa-user"></i><span class="fx-btn-label">Modo cliente</span>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/profesional/dashboard" class="btn btn-fx-gradient btn-sm">
                        <i class="fa-solid fa-briefcase"></i><span class="fx-btn-label">Modo técnico</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Notificaciones -->
            <div class="dropdown">
                <a id="btnNotificaciones" class="fx-icon-btn" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                    <i class="fa-solid fa-bell"></i>
                    <span id="badgeNotif" class="badge badge-notif rounded-pill position-absolute top-0 start-100 translate-middle d-none" style="font-size:.65rem;">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end fx-menu fx-notif" id="listaNotificaciones">
                    <li class="text-muted small text-center py-3">
                        <i class="fa-regular fa-bell-slash mb-2 d-block fs-5"></i>Sin notificaciones nuevas
                    </li>
                </ul>
            </div>

            <!-- Usuario -->
            <div class="dropdown">
                <a class="fx-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="fx-avatar-sm"><?= Security::e($inicialUsuario) ?></span>
                    <span class="fx-user-name"><?= Security::e($nombreUsuario) ?></span>
                    <i class="fa-solid fa-chevron-down fx-user-chevron"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end fx-menu fx-user-menu">
                    <li class="fx-account-header">
                        <span class="fx-avatar-sm lg"><?= Security::e($inicialUsuario) ?></span>
                        <div class="min-w-0">
                            <small class="text-uppercase text-muted fw-semibold" style="font-size:.65rem;letter-spacing:.06em;">Sesión iniciada como</small>
                            <div class="fw-bold text-truncate"><?= Security::e($nombreUsuario) ?></div>
                        </div>
                    </li>
                    <?php if (in_array($rolActual, [3, 4], true)): ?>
                        <li><a class="dropdown-item" href="<?= $perfilUrl ?>"><i class="fa-regular fa-id-card me-2"></i>Mi perfil</a></li>
                    <?php endif; ?>
                    <?php if ($rolActual === 4): ?>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/cliente/ofrecerServicios"><i class="fa-solid fa-briefcase me-2"></i>Ofrecer mis servicios</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item text-danger fw-semibold" href="<?= BASE_URL ?>/auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión</a></li>
                </ul>
            </div>

        </div>
    </div>
</nav>
<?php if ($menu): ?>
<nav class="fx-subnav" aria-label="Menú principal">
    <div class="container-fluid px-0 px-lg-4">
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="menuPrincipal" aria-labelledby="menuPrincipalTitulo">
            <div class="offcanvas-header border-bottom d-lg-none">
                <span class="fx-brand" id="menuPrincipalTitulo">
                    <span class="fx-brand-mark"><i class="fa-solid fa-location-dot"></i></span><span>Fix<b>Geo</b></span>
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#menuPrincipal" aria-label="Cerrar menú"></button>
            </div>
            <div class="offcanvas-body p-2 p-lg-0">
                <ul class="nav w-100">
                    <?php foreach ($menu as [$ruta, $icono, $texto, $prefijos]):
                        $activo = false;
                        foreach ($prefijos as $p) {
                            if (str_starts_with($rutaActual, $p)) { $activo = true; break; }
                        } ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activo ? 'active' : '' ?>" href="<?= BASE_URL . $ruta ?>" <?= $activo ? 'aria-current="page"' : '' ?>>
                                <i class="fa-solid <?= $icono ?>"></i><?= $texto ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="p-3 border-top d-lg-none">
                <div class="small text-muted mb-2 text-truncate"><i class="fa-regular fa-user me-1"></i><?= Security::e($nombreUsuario) ?></div>
                <a class="btn btn-outline-danger w-100" href="<?= BASE_URL ?>/auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión</a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="pb-5">
<?php require __DIR__ . '/alertas.php'; ?>

<script>
(function () {
    'use strict';
    // Datos globales para el resto de scripts (chat, GPS...): fetch(url, {headers: {'X-CSRF-Token': FX.csrf}})
    window.FX = { base: <?= json_encode(BASE_URL) ?>, csrf: <?= json_encode(Security::token()) ?> };

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const urlSegura = u => {
        if (typeof u !== 'string') return '#';
        if (u.startsWith(FX.base)) return u;
        if (u.startsWith('/') && !u.startsWith('//')) return FX.base + u;
        if (/^[a-z0-9_\/-]+(\?.*)?$/i.test(u)) return FX.base + '/' + u;   // rutas como "profesional/solicitudes"
        return '#';
    };
    const badge = document.getElementById('badgeNotif');
    const lista = document.getElementById('listaNotificaciones');
    const vacio = '<li class="text-muted small text-center py-3"><i class="fa-regular fa-bell-slash mb-2 d-block fs-5"></i>Sin notificaciones nuevas</li>';

    async function contar() {
        if (document.hidden) return;
        try {
            const data = await (await fetch(FX.base + '/notificacion/contar')).json();
            if (data.ok && data.total > 0) {
                badge.textContent = data.total > 9 ? '9+' : data.total;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        } catch (e) { /* silencioso */ }
    }
    contar();
    setInterval(contar, 15000);

    document.getElementById('btnNotificaciones')?.addEventListener('click', async function () {
        try {
            const data = await (await fetch(FX.base + '/notificacion/listar')).json();
            if (data.ok && data.notificaciones.length > 0) {
                lista.innerHTML = data.notificaciones.map(n => `
                    <li>
                        <a class="dropdown-item small py-2 ${n.leida == 0 ? 'fw-bold' : ''}" href="${esc(urlSegura(n.url_destino))}">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>${esc(n.mensaje)}
                            <div class="text-muted mt-1 fw-normal" style="font-size:.7rem;">
                                <i class="fa-regular fa-clock"></i>
                                ${esc(new Date(String(n.fecha_creacion).replace(' ', 'T')).toLocaleString('es-BO'))}
                            </div>
                        </a>
                    </li>`).join('<li><hr class="dropdown-divider my-1"></li>');
            } else {
                lista.innerHTML = vacio;
            }
            badge.classList.add('d-none');
        } catch (e) { lista.innerHTML = vacio; }
    });
})();
</script>