<?php require_once __DIR__ . '/../../helpers/estado_helper.php'; ?>
<?php
if (!defined('BASE_URL')) {
    die('BASE_URL no está definida. Verifique app/config/config.php');
}

$rolActual = (int) ($_SESSION['role_id'] ?? 0);
$nombreUsuario = $_SESSION['user_nombre'] ?? 'Usuario';

$rutaActual = trim($_GET['url'] ?? '', '/');
$enModoCliente = str_starts_with($rutaActual, 'cliente');
$enModoProfesional = str_starts_with($rutaActual, 'profesional');
?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= isset($titulo) ? htmlspecialchars($titulo) : 'GEO-PRO' ?>
    </title>


    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    ></script>


    <!-- CSS principal -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/style.css"
    >


    <!-- =========================================================
         GEO-PRO PREMIUM HEADER
         SOLO DISEÑO
         ========================================================= -->

    <style>

        :root {
            --geo-header-dark: #071827;
            --geo-header-dark-2: #0b2435;

            --geo-accent: #08c2b1;
            --geo-accent-dark: #07998d;

            --geo-header-text: #f8fafc;
            --geo-header-muted: #9eb0bd;

            --geo-border: rgba(255,255,255,.09);

            --geo-dropdown-shadow:
                0 25px 70px rgba(3, 15, 27, .18),
                0 5px 18px rgba(3, 15, 27, .08);
        }


        /* =====================================================
           NAVBAR
           ===================================================== */

        .geo-navbar {
            position: sticky;
            top: 0;
            z-index: 1050;

            min-height: 72px;

            background:
                linear-gradient(
                    110deg,
                    var(--geo-header-dark) 0%,
                    #081d2d 55%,
                    #082536 100%
                ) !important;

            border-bottom:
                1px solid rgba(255,255,255,.07);

            box-shadow:
                0 8px 30px rgba(3,15,27,.12);

            backdrop-filter: blur(14px);
        }


        .geo-navbar .container-fluid {
            min-height: 72px;
        }


        /* =====================================================
           LOGO
           ===================================================== */

        .geo-brand {
            display: inline-flex !important;

            align-items: center;

            gap: 10px;

            color: #ffffff !important;

            font-size: 1.35rem !important;

            font-weight: 850 !important;

            letter-spacing: -.7px;

            text-decoration: none !important;

            transition: .25s ease;
        }


        .geo-brand:hover {
            transform: translateY(-1px);
        }


        .geo-brand > i {
            width: 39px;
            height: 39px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background:
                rgba(8,194,177,.12);

            border:
                1px solid rgba(8,194,177,.18);

            color: var(--geo-accent);

            font-size: 1rem;

            box-shadow:
                0 5px 18px rgba(8,194,177,.08);
        }


        .geo-brand span {
            color: var(--geo-accent) !important;
        }


        /* =====================================================
           BOTONES DEL HEADER
           ===================================================== */

        .geo-navbar .btn {
            min-height: 39px;

            border-radius: 11px !important;

            font-size: .78rem;

            font-weight: 750;

            padding-left: 14px;
            padding-right: 14px;

            transition:
                transform .2s ease,
                background .2s ease,
                border-color .2s ease,
                box-shadow .2s ease;
        }


        .geo-navbar .btn:hover {
            transform: translateY(-1px);
        }


        /* Solicitudes / Mis pedidos */

        .geo-navbar .btn-outline-light {
            border-color:
                rgba(255,255,255,.22) !important;

            color:
                #eef6f8 !important;

            background:
                rgba(255,255,255,.035) !important;
        }


        .geo-navbar .btn-outline-light:hover {
            background:
                rgba(255,255,255,.09) !important;

            border-color:
                rgba(255,255,255,.32) !important;

            box-shadow:
                0 7px 18px rgba(0,0,0,.12);
        }


        /* =====================================================
           BOTÓN CAMBIAR MODO
           ===================================================== */

        .geo-navbar .btn-warning {
            background:
                linear-gradient(
                    135deg,
                    #f4d35e,
                    #f7c948
                ) !important;

            border:
                none !important;

            color:
                #10202e !important;

            box-shadow:
                0 6px 18px rgba(247,201,72,.13);
        }


        .geo-navbar .btn-warning:hover {
            background:
                linear-gradient(
                    135deg,
                    #f7dc76,
                    #f8ce58
                ) !important;

            box-shadow:
                0 9px 22px rgba(247,201,72,.19);
        }


        /* =====================================================
           ZONA DERECHA
           ===================================================== */

        .geo-navbar .d-flex.align-items-center.gap-3 {
            gap: 10px !important;
        }


        /* =====================================================
           NOTIFICACIONES
           ===================================================== */

        #btnNotificaciones {
            position: relative;

            width: 41px;
            height: 41px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            color: #dce9ee !important;

            background:
                rgba(255,255,255,.045);

            border:
                1px solid rgba(255,255,255,.07);

            text-decoration: none !important;

            transition: .22s ease;
        }


        #btnNotificaciones:hover {
            color: #ffffff !important;

            background:
                rgba(255,255,255,.095);

            border-color:
                rgba(255,255,255,.13);

            transform: translateY(-1px);
        }


        #btnNotificaciones i {
            font-size: .95rem;
        }


        /* Badge */

        .badge-notif {
            background:
                #ef4444 !important;

            color: white !important;

            border:
                2px solid var(--geo-header-dark);

            min-width: 18px;
            height: 18px;

            display: flex;

            align-items: center;
            justify-content: center;

            padding: 0 4px;

            font-weight: 800;

            box-shadow:
                0 4px 10px rgba(239,68,68,.28);
        }


        /* =====================================================
           DROPDOWN NOTIFICACIONES
           ===================================================== */

        #listaNotificaciones {
            width: 350px !important;

            max-height: 430px;

            overflow-y: auto;

            margin-top: 12px !important;

            padding: 9px !important;

            border:
                1px solid #e9eef2 !important;

            border-radius: 18px !important;

            background:
                rgba(255,255,255,.98) !important;

            box-shadow:
                var(--geo-dropdown-shadow) !important;

            animation:
                geoDropdown .18s ease-out;
        }


        /* scrollbar */

        #listaNotificaciones::-webkit-scrollbar {
            width: 5px;
        }

        #listaNotificaciones::-webkit-scrollbar-track {
            background: transparent;
        }

        #listaNotificaciones::-webkit-scrollbar-thumb {
            background: #cbd5dc;
            border-radius: 10px;
        }


        /* elementos */

        #listaNotificaciones .dropdown-item {
            white-space: normal;

            padding:
                11px 12px !important;

            border-radius: 12px !important;

            color: #334155;

            transition:
                background .18s ease,
                transform .18s ease;
        }


        #listaNotificaciones .dropdown-item:hover {
            background: #f5f8fa !important;

            transform: translateX(2px);
        }


        #listaNotificaciones .dropdown-item.fw-bold {
            background:
                #eefaf8 !important;

            color:
                #173a3a !important;
        }


        #listaNotificaciones .dropdown-divider {
            border-color:
                #edf1f4 !important;

            opacity: 1;
        }


        /* =====================================================
           MENÚ DE USUARIO
           ===================================================== */

        .geo-user-toggle {
            min-height: 43px;

            display: flex !important;

            align-items: center;

            gap: 9px;

            padding:
                4px 10px 4px 5px !important;

            border-radius: 13px;

            color: white !important;

            background:
                rgba(255,255,255,.035);

            border:
                1px solid rgba(255,255,255,.065);

            transition: .22s ease;

            text-decoration: none !important;
        }


        .geo-user-toggle:hover {
            background:
                rgba(255,255,255,.085);

            border-color:
                rgba(255,255,255,.11);
        }


        /* avatar */

        .geo-user-avatar {
            width: 35px;
            height: 35px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    var(--geo-accent),
                    #07988e
                );

            color: #ffffff;

            box-shadow:
                0 5px 15px rgba(8,194,177,.18);
        }


        .geo-user-avatar i {
            font-size: .9rem;
        }


        /* nombre */

        .geo-user-name {
            max-width: 155px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: #f5f9fa;

            font-size: .78rem;

            font-weight: 750;
        }


        .geo-user-chevron {
            margin-left: 2px;

            color: #93a8b3;

            font-size: .65rem;

            transition: transform .2s ease;
        }


        .geo-user-toggle[aria-expanded="true"]
        .geo-user-chevron {
            transform: rotate(180deg);
        }


        /* =====================================================
           DROPDOWN USUARIO
           ===================================================== */

        .geo-user-menu {
            width: 270px;

            margin-top: 12px !important;

            padding: 9px !important;

            border:
                1px solid #e7edf1 !important;

            border-radius: 18px !important;

            background:
                rgba(255,255,255,.99) !important;

            box-shadow:
                var(--geo-dropdown-shadow) !important;

            animation:
                geoDropdown .18s ease-out;
        }


        @keyframes geoDropdown {

            from {
                opacity: 0;

                transform:
                    translateY(-6px)
                    scale(.98);
            }

            to {
                opacity: 1;

                transform:
                    translateY(0)
                    scale(1);
            }

        }


        /* cabecera interna del usuario */

        .geo-account-header {
            display: flex;

            align-items: center;

            gap: 11px;

            padding:
                12px 11px 13px;

            margin-bottom: 5px;

            border-bottom:
                1px solid #edf1f4;
        }


        .geo-account-avatar {
            width: 42px;
            height: 42px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #08c2b1,
                    #078f87
                );

            color: white;

            box-shadow:
                0 6px 17px rgba(8,194,177,.18);
        }


        .geo-account-info {
            min-width: 0;
        }


        .geo-account-label {
            display: block;

            margin-bottom: 3px;

            color: #94a3af;

            font-size: .65rem;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .6px;
        }


        .geo-account-name {
            display: block;

            max-width: 180px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: #162231;

            font-size: .83rem;

            font-weight: 800;
        }


        /* =====================================================
           ITEM CERRAR SESIÓN
           ===================================================== */

        .geo-logout-item {
            display: flex !important;

            align-items: center;

            gap: 10px;

            margin-top: 6px;

            padding:
                11px 12px !important;

            border-radius: 12px !important;

            color: #c62828 !important;

            background: transparent !important;

            font-size: .78rem;

            font-weight: 750;

            transition:
                background .2s ease,
                color .2s ease,
                transform .2s ease;
        }


        .geo-logout-item:hover {
            color: #b21f1f !important;

            background:
                #fff2f2 !important;

            transform: translateX(2px);
        }


        .geo-logout-icon {
            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            border-radius: 9px;

            background:
                #fff0f0;

            color:
                #dc3545;

            font-size: .8rem;
        }


        /* =====================================================
           RESPONSIVE
           ===================================================== */

        @media (max-width: 991px) {

            .geo-navbar {
                min-height: 65px;
            }

            .geo-navbar .container-fluid {
                min-height: 65px;
            }

            .geo-brand {
                font-size: 1.2rem !important;
            }

            .geo-brand > i {
                width: 35px;
                height: 35px;
            }

            .geo-user-name {
                max-width: 110px;
            }

        }


        @media (max-width: 575px) {

            .geo-navbar .container-fluid {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .geo-navbar .d-flex.align-items-center.gap-3 {
                gap: 6px !important;
            }

            .geo-navbar .btn-outline-light {
                font-size: 0 !important;

                width: 40px;
                padding: 0 !important;

                display: flex;

                align-items: center;
                justify-content: center;
            }

            .geo-navbar .btn-outline-light i {
                margin: 0 !important;

                font-size: .85rem;
            }

            .geo-navbar .btn-warning {
                font-size: 0 !important;

                width: 40px;
                padding: 0 !important;

                display: flex;

                align-items: center;
                justify-content: center;
            }

            .geo-navbar .btn-warning i {
                margin: 0 !important;

                font-size: .85rem;
            }

            #listaNotificaciones {
                position: fixed !important;

                left: 10px !important;
                right: 10px !important;

                top: 67px !important;

                width: auto !important;

                margin: 0 !important;
            }

            .geo-user-name,
            .geo-user-chevron {
                display: none;
            }

            .geo-user-toggle {
                padding: 3px !important;

                border: none;

                background: transparent;
            }

            .geo-user-avatar {
                width: 39px;
                height: 39px;
            }

            .geo-user-menu {
                width: 255px;

                margin-top: 9px !important;
            }

        }

    </style>

</head>


<body>


<!-- ============================================================
     GEO-PRO NAVBAR
     LA LÓGICA DE LOS ENLACES SE MANTIENE
     ============================================================ -->

<nav class="navbar navbar-expand-lg navbar-dark geo-navbar sticky-top">

    <div class="container-fluid px-4">


        <!-- =====================================================
             LOGO
             MISMO ENLACE
             ===================================================== -->

        <a
            class="navbar-brand geo-brand"
            href="<?= BASE_URL ?>/<?= $rolActual === 3 ? 'profesional/dashboard' : 'cliente/dashboard' ?>"
        >

            <i class="fa-solid fa-location-crosshairs fa-spin-hover"></i>

            <span style="color: #ffffff;">
                GEO<span>PRO</span>
            </span>

        </a>



        <!-- =====================================================
             CONTROLES DERECHA
             ===================================================== -->

        <div class="d-flex align-items-center gap-3 ms-auto">


            <!-- =================================================
                 SOLICITUDES / MIS PEDIDOS
                 MISMA LÓGICA
                 ================================================= -->

            <?php if ($rolActual === 3 && $enModoProfesional): ?>

                <a
                    href="<?= BASE_URL ?>/profesional/solicitudes"
                    class="btn btn-outline-light btn-sm"
                >

                    <i class="fa-solid fa-list-check me-1"></i>

                    Solicitudes

                </a>

            <?php elseif ($rolActual === 4 || ($rolActual === 3 && $enModoCliente)): ?>

                <a
                    href="<?= BASE_URL ?>/solicitud/misSolicitudes"
                    class="btn btn-outline-light btn-sm"
                >

                    <i class="fa-solid fa-list-check me-1"></i>

                    Mis Pedidos

                </a>

            <?php endif; ?>



            <!-- =================================================
                 CAMBIO DE MODO
                 MISMA LÓGICA Y MISMAS RUTAS
                 ================================================= -->

            <?php if ($rolActual === 3): ?>

                <?php if ($enModoProfesional): ?>

                    <a
                        href="<?= BASE_URL ?>/cliente/dashboard"
                        class="btn btn-warning btn-sm px-3 fw-bold"
                    >

                        <i class="fa-solid fa-user me-1"></i>

                        Modo Cliente

                    </a>

                <?php else: ?>

                    <a
                        href="<?= BASE_URL ?>/profesional/dashboard"
                        class="btn btn-warning btn-sm px-3 fw-bold"
                    >

                        <i class="fa-solid fa-briefcase me-1"></i>

                        Modo Técnico

                    </a>

                <?php endif; ?>

            <?php endif; ?>



            <!-- =================================================
                 NOTIFICACIONES
                 MISMA LÓGICA
                 ================================================= -->

            <div class="dropdown">

                <a
                    id="btnNotificaciones"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    <i class="fa-solid fa-bell"></i>


                    <span
                        id="badgeNotif"
                        class="badge badge-notif rounded-pill position-absolute top-0 start-100 translate-middle d-none"
                        style="font-size:0.65rem;"
                    >
                        0
                    </span>

                </a>


                <ul
                    class="dropdown-menu dropdown-menu-end"
                    id="listaNotificaciones"
                >

                    <li class="text-muted small text-center py-3">

                        <i
                            class="fa-regular fa-bell-slash mb-2 d-block"
                            style="font-size:1.2rem; color:#cbd5dc;"
                        ></i>

                        Sin notificaciones nuevas

                    </li>

                </ul>

            </div>



            <!-- =================================================
                 MENÚ DE USUARIO
                 MISMO LOGOUT
                 ================================================= -->

            <div class="dropdown">


                <a
                    class="geo-user-toggle dropdown-toggle"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    <span class="geo-user-avatar">

                        <i class="fa-solid fa-user"></i>

                    </span>


                    <span class="geo-user-name">

                        <?= htmlspecialchars($nombreUsuario) ?>

                    </span>


                    <i
                        class="fa-solid fa-chevron-down geo-user-chevron"
                    ></i>

                </a>



                <!-- =================================================
                     DROPDOWN
                     ================================================= -->

                <ul
                    class="dropdown-menu dropdown-menu-end geo-user-menu"
                >


                    <!-- Cabecera visual -->

                    <li>

                        <div class="geo-account-header">

                            <div class="geo-account-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>


                            <div class="geo-account-info">

                                <span class="geo-account-label">
                                    Sesión iniciada como
                                </span>

                                <span class="geo-account-name">

                                    <?= htmlspecialchars($nombreUsuario) ?>

                                </span>

                            </div>

                        </div>

                    </li>



                    <!-- =================================================
                         CERRAR SESIÓN
                         MISMO ENLACE
                         ================================================= -->

                    <li>

                        <a
                            class="dropdown-item geo-logout-item"
                            href="<?= BASE_URL ?>/auth/logout"
                        >

                            <span class="geo-logout-icon">

                                <i class="fa-solid fa-right-from-bracket"></i>

                            </span>

                            <span>
                                Cerrar sesión
                            </span>

                        </a>

                    </li>

                </ul>

            </div>


        </div>

    </div>

</nav>



<!-- ============================================================
     CONTENIDO
     ============================================================ -->

<main class="pb-5">


<script>


/* ==============================================================
   NOTIFICACIONES
   LÓGICA ORIGINAL — NO MODIFICADA
   ============================================================== */

async function consultarNotificaciones() {

    try {

        const res = await fetch(
            '<?= BASE_URL ?>/notificacion/contar'
        );

        const data = await res.json();

        const badge =
            document.getElementById('badgeNotif');


        if (data.ok && data.total > 0) {

            badge.textContent =
                data.total > 9
                    ? '9+'
                    : data.total;

            badge.classList.remove('d-none');

        } else {

            badge.classList.add('d-none');

        }

    } catch (e) {

        /* silencioso */

    }

}


consultarNotificaciones();


setInterval(
    consultarNotificaciones,
    15000
);



/* ==============================================================
   LISTAR NOTIFICACIONES
   LÓGICA ORIGINAL — NO MODIFICADA
   ============================================================== */

document
    .getElementById('btnNotificaciones')
    ?.addEventListener(
        'click',
        async function () {

            const lista =
                document.getElementById(
                    'listaNotificaciones'
                );


            const res =
                await fetch(
                    '<?= BASE_URL ?>/notificacion/listar'
                );


            const data =
                await res.json();


            if (
                data.ok &&
                data.notificaciones.length > 0
            ) {

                lista.innerHTML =
                    data.notificaciones
                        .map(n => `

                            <li>

                                <a
                                    class="dropdown-item small py-2 ${n.leida == 0 ? 'fw-bold bg-light rounded' : ''}"
                                    href="${n.url_destino || '#'}"
                                >

                                    <i
                                        class="fa-solid fa-circle-info text-primary me-1"
                                    ></i>

                                    ${n.mensaje}


                                    <div
                                        class="text-muted mt-1"
                                        style="font-size:0.7rem;"
                                    >

                                        <i
                                            class="fa-regular fa-clock"
                                        ></i>

                                        ${
                                            new Date(
                                                n.fecha_creacion
                                                    .replace(' ','T')
                                            )
                                            .toLocaleString('es-BO')
                                        }

                                    </div>

                                </a>

                            </li>

                        `)
                        .join(
                            '<hr class="dropdown-divider my-1">'
                        );


            } else {

                lista.innerHTML = `

                    <li class="text-muted small text-center py-3">

                        <i
                            class="fa-regular fa-bell-slash mb-2 d-block"
                            style="
                                font-size:1.2rem;
                                color:#cbd5dc;
                            "
                        ></i>

                        Sin notificaciones nuevas

                    </li>

                `;

            }


            document
                .getElementById('badgeNotif')
                .classList
                .add('d-none');

        }
    );

</script>