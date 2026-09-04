<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO CLIENTE — PREMIUM UI
   SOLO DISEÑO — NO MODIFICA LA LÓGICA PHP
   ============================================================ */

:root {
    --geo-dark: #071827;
    --geo-dark-2: #0b2435;
    --geo-primary: #08b7a5;
    --geo-primary-dark: #079486;
    --geo-primary-soft: #e8faf7;

    --geo-blue: #2563eb;
    --geo-blue-soft: #eff6ff;

    --geo-text: #172033;
    --geo-muted: #718096;

    --geo-bg: #f5f7fa;
    --geo-white: #ffffff;

    --geo-border: #e8edf2;

    --geo-shadow:
        0 12px 35px rgba(15, 23, 42, .07);

    --geo-radius: 22px;
}


/* ============================================================
   BASE
   ============================================================ */

body {
    background: var(--geo-bg) !important;
    color: var(--geo-text);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.container {
    max-width: 1180px;
}


/* ============================================================
   HEADER / MENÚ DEL USUARIO
   ============================================================ */

/*
   No cambiamos el HTML ni la lógica del header.
   Solamente hacemos que el dropdown existente se vea premium.
*/

.navbar .dropdown-menu {
    min-width: 270px !important;
    padding: 10px !important;

    background: rgba(255,255,255,.98) !important;

    border: 1px solid rgba(15,23,42,.07) !important;
    border-radius: 18px !important;

    box-shadow:
        0 24px 60px rgba(15,23,42,.14),
        0 5px 15px rgba(15,23,42,.06) !important;

    margin-top: 12px !important;

    animation: geoMenu .18s ease-out;
}

@keyframes geoMenu {
    from {
        opacity: 0;
        transform: translateY(-7px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}


/* Items del dropdown */

.navbar .dropdown-item {
    border-radius: 12px !important;

    padding: 12px 14px !important;

    color: #334155 !important;

    font-size: .92rem !important;
    font-weight: 600 !important;

    display: flex !important;
    align-items: center !important;

    transition:
        background .2s ease,
        transform .2s ease !important;
}

.navbar .dropdown-item:hover {
    background: #f4f7f9 !important;
    color: var(--geo-dark) !important;

    transform: translateX(2px);
}


/* Iconos normales */

.navbar .dropdown-item i {
    width: 32px !important;
    height: 32px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    margin-right: 11px !important;

    border-radius: 10px !important;

    background: #f1f5f9 !important;
    color: #64748b !important;

    font-size: .9rem !important;
}


/* Cerrar sesión */

.navbar .dropdown-item[href*="logout"],
.navbar .dropdown-item[href*="cerrar"],
.navbar .dropdown-item[href*="salir"] {
    margin-top: 8px !important;

    padding-top: 13px !important;

    border-top: 1px solid #eef1f4 !important;

    color: #dc2626 !important;

    background: transparent !important;
}

.navbar .dropdown-item[href*="logout"]:hover,
.navbar .dropdown-item[href*="cerrar"]:hover,
.navbar .dropdown-item[href*="salir"]:hover {
    background: #fff5f5 !important;

    color: #b91c1c !important;

    transform: none !important;
}

.navbar .dropdown-item[href*="logout"] i,
.navbar .dropdown-item[href*="cerrar"] i,
.navbar .dropdown-item[href*="salir"] i {
    background: #fff1f2 !important;
    color: #dc2626 !important;
}


/* ============================================================
   HERO
   ============================================================ */

.geo-hero {
    position: relative;

    overflow: hidden;

    margin-bottom: 0;

    padding:
        58px 20px
        85px;

    background:
        radial-gradient(
            circle at 85% 20%,
            rgba(8,183,165,.20),
            transparent 30%
        ),
        radial-gradient(
            circle at 10% 100%,
            rgba(37,99,235,.16),
            transparent 32%
        ),
        linear-gradient(
            135deg,
            #061421 0%,
            #092536 48%,
            #073f42 100%
        );

    border-radius:
        0 0 38px 38px;

    color: white;
}


/* círculos decorativos */

.geo-hero::before {
    content: "";

    position: absolute;

    width: 380px;
    height: 380px;

    right: -170px;
    top: -200px;

    border-radius: 50%;

    border: 1px solid rgba(255,255,255,.08);
}

.geo-hero::after {
    content: "";

    position: absolute;

    width: 220px;
    height: 220px;

    left: -130px;
    bottom: -150px;

    border-radius: 50%;

    border: 1px solid rgba(255,255,255,.06);
}


.geo-hero-content {
    position: relative;
    z-index: 2;

    text-align: center;

    max-width: 900px;

    margin: auto;
}


/* pequeño label */

.geo-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    padding: 7px 13px;

    margin-bottom: 17px;

    border-radius: 50px;

    background: rgba(255,255,255,.08);

    border: 1px solid rgba(255,255,255,.12);

    color: #8debe1;

    font-size: .76rem;
    font-weight: 700;

    letter-spacing: .5px;

    backdrop-filter: blur(8px);
}

.geo-kicker i {
    color: #3ddbd0;
}


/* título */

.search-title {
    color: #fff;

    font-size: clamp(2rem, 4vw, 3rem);

    font-weight: 800;

    letter-spacing: -1.5px;

    margin-bottom: 12px;
}


/* subtítulo */

.search-subtitle {
    color: #b9d4d9;

    font-size: 1rem;

    max-width: 650px;

    margin:
        0 auto
        30px;

    line-height: 1.7;
}


/* ============================================================
   SMART SEARCH
   ============================================================ */

.search-box {
    position: relative;

    display: flex;
    align-items: center;

    max-width: 850px;

    margin: auto;

    padding: 7px;

    background: #fff;

    border-radius: 19px;

    border: 1px solid rgba(255,255,255,.4);

    box-shadow:
        0 20px 50px rgba(0,0,0,.22);

    transition: .25s ease;
}

.search-box:focus-within {
    box-shadow:
        0 20px 55px rgba(0,0,0,.28),
        0 0 0 4px rgba(8,183,165,.15);
}


/* input */

.search-input {
    flex: 1;

    min-height: 58px;

    padding: 15px 18px;

    border: none !important;

    outline: none !important;

    resize: none;

    background: transparent !important;

    color: #172033 !important;

    font-size: .98rem;

    line-height: 1.5;
}

.search-input::placeholder {
    color: #9aa6b2;
}


/* botón */

.search-btn {
    flex-shrink: 0;

    min-height: 54px;

    padding:
        0 28px;

    border: none;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #08b7a5,
            #079486
        );

    color: white;

    font-weight: 750;

    font-size: .95rem;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 9px;

    box-shadow:
        0 8px 20px rgba(8,183,165,.25);

    transition: .25s ease;
}

.search-btn:hover {
    background:
        linear-gradient(
            135deg,
            #09c7b4,
            #078878
        );

    transform: translateY(-1px);

    box-shadow:
        0 12px 25px rgba(8,183,165,.30);
}


/* ============================================================
   CONTENIDO
   ============================================================ */

.geo-main {
    position: relative;

    margin-top: -35px;

    z-index: 5;
}

.geo-content {
    padding:
        0 20px
        55px;
}


/* ============================================================
   ALERTAS
   ============================================================ */

.geo-alert {
    border: none !important;

    border-radius: 17px !important;

    padding: 16px 18px !important;

    box-shadow:
        0 8px 25px rgba(15,23,42,.05);

    margin-bottom: 18px;
}


/* ============================================================
   SERVICIO ACTIVO
   ============================================================ */

.geo-service {
    position: relative;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 19px 22px;

    margin-bottom: 28px;

    border-radius: 20px;

    background:
        linear-gradient(
            110deg,
            #ffffff 0%,
            #f1fbfa 100%
        );

    border: 1px solid #d9f2ef;

    box-shadow:
        0 12px 30px rgba(8,183,165,.08);

    overflow: hidden;
}

.geo-service::before {
    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 4px;

    background:
        linear-gradient(
            #08b7a5,
            #2563eb
        );
}


.geo-service-info {
    display: flex;
    align-items: center;

    gap: 15px;
}

.geo-service-icon {
    width: 50px;
    height: 50px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    background: var(--geo-primary-soft);

    color: var(--geo-primary);

    font-size: 1.25rem;
}

.geo-service-title {
    margin: 0 0 6px;

    font-size: .93rem;

    font-weight: 750;

    color: #152033;
}

.geo-status {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 5px 10px;

    border-radius: 50px;

    background: #eaf2ff;

    color: #2563eb;

    font-size: .69rem;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .3px;
}

.geo-status::before {
    content: "";

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: currentColor;
}


.geo-follow-btn {
    display: inline-flex;
    align-items: center;
    gap: 9px;

    padding: 12px 17px;

    border-radius: 12px;

    background: var(--geo-dark);

    color: white !important;

    text-decoration: none;

    font-size: .82rem;

    font-weight: 750;

    transition: .2s ease;
}

.geo-follow-btn:hover {
    background: #0d3045;

    color: white !important;

    transform: translateX(2px);
}


/* ============================================================
   CABECERA DE SECCIÓN
   ============================================================ */

.geo-section-head {
    display: flex;

    align-items: flex-end;

    justify-content: space-between;

    margin-bottom: 18px;
}

.geo-section-title {
    margin: 0;

    font-size: 1.18rem;

    font-weight: 800;

    color: #152033;

    letter-spacing: -.4px;
}

.geo-section-subtitle {
    margin: 5px 0 0;

    color: #8995a4;

    font-size: .79rem;
}

.geo-verified {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 7px 11px;

    border-radius: 50px;

    background: #ecfaf7;

    color: #078c7e;

    font-size: .72rem;

    font-weight: 750;
}


/* ============================================================
   CATEGORÍAS
   ============================================================ */

.categories-grid {
    display: grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(145px, 1fr)
        );

    gap: 14px;

    margin-top: 0;
}


.category-card {
    position: relative;

    min-height: 155px;

    padding: 21px 14px;

    background: #fff;

    border: 1px solid var(--geo-border);

    border-radius: 18px;

    text-decoration: none;

    color: #253044;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    gap: 14px;

    box-shadow:
        0 5px 15px rgba(15,23,42,.035);

    transition:
        transform .25s ease,
        box-shadow .25s ease,
        border-color .25s ease;
}


.category-card::after {
    content: "";

    position: absolute;

    left: 18px;
    right: 18px;
    bottom: 0;

    height: 2px;

    border-radius: 50px;

    background:
        linear-gradient(
            90deg,
            transparent,
            var(--geo-primary),
            transparent
        );

    opacity: 0;

    transition: .25s ease;
}


.category-card:hover {
    transform: translateY(-5px);

    border-color: #c8ebe6;

    box-shadow:
        0 16px 35px rgba(15,23,42,.08);

    color: #152033;
}

.category-card:hover::after {
    opacity: 1;
}


.cat-icon-wrapper {
    width: 58px;
    height: 58px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 16px;

    background:
        linear-gradient(
            135deg,
            #edfcf9,
            #e3f7f4
        );

    color: var(--geo-primary);

    font-size: 1.35rem;

    transition: .25s ease;
}


.category-card:hover .cat-icon-wrapper {
    background:
        linear-gradient(
            135deg,
            var(--geo-primary),
            #079486
        );

    color: white;

    transform: scale(1.05);
}


.cat-title {
    font-size: .79rem;

    line-height: 1.4;

    font-weight: 750;

    max-width: 130px;
}


/* ============================================================
   TARJETAS LATERALES
   ============================================================ */

.side-card {
    background: #fff;

    border:
        1px solid var(--geo-border);

    border-radius: var(--geo-radius);

    overflow: hidden;

    margin-bottom: 18px;

    box-shadow:
        0 7px 25px rgba(15,23,42,.045);
}


/* cabecera */

.side-header {
    padding: 19px 20px;

    border-bottom:
        1px solid #edf1f4;
}

.side-header h5 {
    font-size: .9rem !important;

    margin-bottom: 14px !important;

    font-weight: 800 !important;
}

.side-header .fa-location-dot {
    width: 39px;
    height: 39px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #fff1f2;

    color: #dc2626 !important;

    font-size: 1rem !important;
}


/* mapa */

#mapaCliente {
    height: 245px !important;

    width: 100%;

    filter:
        saturate(.82)
        contrast(.97);
}


/* ============================================================
   ESTADÍSTICAS
   ============================================================ */

.stats-container {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    padding: 20px 14px;

    gap: 8px;
}

.stat-item {
    padding: 10px 5px;

    text-align: center;

    border-radius: 13px;

    transition: .2s ease;
}

.stat-item:hover {
    background: #f8fafc;
}


.stat-value {
    font-size: 1.55rem;

    font-weight: 850;

    line-height: 1;

    color: var(--geo-dark);
}

.stat-label {
    margin-top: 7px;

    font-size: .65rem;

    font-weight: 750;

    color: #9aa5b2;

    text-transform: uppercase;

    letter-spacing: .5px;
}


/* ============================================================
   MAPA POPUP
   ============================================================ */

.leaflet-popup-content-wrapper {
    border-radius: 13px !important;

    box-shadow:
        0 12px 30px rgba(15,23,42,.15) !important;
}

.leaflet-popup-content {
    margin: 12px 15px !important;

    font-size: .8rem;
}


/* ============================================================
   RESPONSIVE
   ============================================================ */

@media (max-width: 991px) {

    .geo-hero {
        padding-bottom: 70px;
    }

    .geo-content {
        padding-left: 15px;
        padding-right: 15px;
    }

}


@media (max-width: 767px) {

    .geo-hero {
        padding:
            42px 15px
            70px;

        border-radius:
            0 0 28px 28px;
    }

    .search-title {
        font-size: 1.85rem;
    }

    .search-subtitle {
        font-size: .88rem;
    }

    .search-box {
        flex-direction: column;

        padding: 8px;

        border-radius: 17px;
    }

    .search-input {
        width: 100%;

        min-height: 65px;
    }

    .search-btn {
        width: 100%;
    }

    .geo-main {
        margin-top: -28px;
    }

    .geo-service {
        flex-direction: column;

        align-items: stretch;
    }

    .geo-follow-btn {
        justify-content: center;
    }

    .geo-section-head {
        align-items: flex-start;

        gap: 10px;
    }

    .geo-verified {
        display: none;
    }

    .categories-grid {
        grid-template-columns:
            repeat(2, 1fr);

        gap: 10px;
    }

    .category-card {
        min-height: 145px;
    }

}


@media (max-width: 420px) {

    .categories-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .cat-icon-wrapper {
        width: 52px;
        height: 52px;
    }

    .cat-title {
        font-size: .73rem;
    }
}

</style>


<!-- ============================================================
     HERO / BUSCADOR
     ============================================================ -->

<section class="geo-hero">

    <div class="geo-hero-content">

        <div class="geo-kicker">
            <i class="fa-solid fa-sparkles"></i>
            GEO-PRO SMART SERVICES
        </div>

        <h1 class="search-title">
            ¿Qué servicio necesitas hoy?
        </h1>

        <p class="search-subtitle">
            Cuéntanos qué necesitas y nuestra inteligencia artificial
            te ayudará a encontrar al profesional adecuado.
        </p>


        <!-- MISMA LÓGICA DE BÚSQUEDA -->
        <form
            id="formBusqueda"
            action="<?= BASE_URL ?>/busqueda/inteligente"
            method="POST"
        >

            <div class="search-box">

                <textarea
                    name="descripcion"
                    id="descripcionProblema"
                    class="search-input"
                    rows="1"
                    placeholder="Describe tu problema... Ej. Se me cortó la luz de la cocina y saltó el térmico."
                    required
                ></textarea>

                <button
                    type="submit"
                    class="search-btn"
                >
                    <i class="fa-solid fa-wand-magic-sparkles"></i>

                    Buscar profesional

                </button>

            </div>

        </form>

    </div>

</section>



<!-- ============================================================
     CONTENIDO
     ============================================================ -->

<main class="geo-main">

    <div class="container geo-content">


        <!-- ====================================================
             ALERTAS
             ==================================================== -->

        <?php if (isset($_GET['error']) && $_GET['error'] === 'auto_contratacion'): ?>

            <div
                class="alert alert-danger geo-alert d-flex align-items-center"
                style="
                    background:#fff5f5;
                    color:#991b1b;
                "
            >

                <div
                    class="rounded-circle d-flex align-items-center justify-content-center me-3"
                    style="
                        width:42px;
                        height:42px;
                        flex-shrink:0;
                        background:#fee2e2;
                        color:#dc2626;
                    "
                >
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div>

                    <h6 class="fw-bold mb-1">
                        Acción no permitida
                    </h6>

                    <span class="small">
                        Por políticas de seguridad, no puedes solicitar un servicio a tu propio perfil profesional.
                    </span>

                </div>

            </div>

        <?php endif; ?>


        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_coincidencia'): ?>

            <div
                class="alert alert-warning geo-alert"
                style="
                    background:#fffbeb;
                    color:#92400e;
                "
            >

                <i class="fa-solid fa-robot me-2"></i>

                No pudimos identificar tu categoría automáticamente.
                Elige una manualmente abajo.

            </div>

        <?php endif; ?>



        <!-- ====================================================
             SERVICIO ACTIVO
             ==================================================== -->

        <?php if ($solicitudActiva): ?>

            <div class="geo-service">

                <div class="geo-service-info">

                    <div class="geo-service-icon">

                        <i class="fa-solid fa-motorcycle"></i>

                    </div>

                    <div>

                        <p class="geo-service-title">

                            Servicio en curso con

                            <?= htmlspecialchars(
                                $solicitudActiva['prof_nombre']
                                . ' '
                                . $solicitudActiva['prof_apellido']
                            ) ?>

                        </p>

                        <span class="geo-status">

                            <?= htmlspecialchars(
                                $solicitudActiva['estado_servicio']
                            ) ?>

                        </span>

                    </div>

                </div>


                <a
                    href="<?= BASE_URL ?>/solicitud/detalle/<?= $solicitudActiva['id_solicitud'] ?>"
                    class="geo-follow-btn"
                >

                    Ver seguimiento

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>

        <?php endif; ?>



        <!-- ====================================================
             GRID PRINCIPAL
             ==================================================== -->

        <div class="row g-4">


            <!-- =================================================
                 CATEGORÍAS
                 ================================================= -->

            <div class="col-lg-8">

                <div class="geo-section-head">

                    <div>

                        <h2 class="geo-section-title">
                            Explora servicios
                        </h2>

                        <p class="geo-section-subtitle">
                            Encuentra profesionales según lo que necesitas.
                        </p>

                    </div>


                    <span class="geo-verified">

                        <i class="fa-solid fa-circle-check"></i>

                        Profesionales verificados

                    </span>

                </div>



                <div class="categories-grid">

                    <?php foreach ($categorias as $cat): ?>

                        <!-- MISMA RUTA Y MISMA LÓGICA -->

                        <a
                            href="<?= BASE_URL ?>/busqueda/resultados/<?= htmlspecialchars($cat['slug']) ?>"
                            class="category-card"
                        >

                            <div class="cat-icon-wrapper">

                                <i
                                    class="<?= htmlspecialchars($cat['icono_fa']) ?>"
                                ></i>

                            </div>

                            <div class="cat-title">

                                <?= htmlspecialchars(
                                    $cat['nombre_categoria']
                                ) ?>

                            </div>

                        </a>

                    <?php endforeach; ?>



                    <?php if (empty($categorias)): ?>

                        <div
                            class="col-12 text-center p-5 bg-white rounded-4 shadow-sm border border-light"
                        >

                            <i
                                class="fa-solid fa-folder-open fa-3x text-muted opacity-25 mb-3"
                            ></i>

                            <h6 class="text-muted fw-bold">
                                Sin categorías
                            </h6>

                        </div>

                    <?php endif; ?>

                </div>

            </div>



            <!-- =================================================
                 PANEL DERECHO
                 ================================================= -->

            <div class="col-lg-4">


                <!-- =============================================
                     UBICACIÓN
                     ============================================= -->

                <div class="side-card">

                    <div class="side-header">

                        <h5 class="fw-bold text-dark">

                            Mi ubicación

                        </h5>


                        <?php if ($cliente): ?>

                            <div class="d-flex align-items-center">

                                <i
                                    class="fa-solid fa-location-dot me-3"
                                ></i>

                                <div>

                                    <h6
                                        class="fw-bold text-dark mb-1"
                                        style="font-size:.85rem;"
                                    >

                                        <?= htmlspecialchars(
                                            $cliente['zona']
                                        ) ?>

                                    </h6>

                                    <p
                                        class="text-muted small mb-0"
                                        style="line-height:1.45;"
                                    >

                                        <?= htmlspecialchars(
                                            $cliente['direccion_referencia']
                                        ) ?>

                                    </p>

                                </div>

                            </div>

                        <?php else: ?>

                            <p class="text-muted small mb-0">

                                <i
                                    class="fa-solid fa-map-location-dot me-2"
                                ></i>

                                Aún no registraste una ubicación.

                            </p>

                        <?php endif; ?>

                    </div>


                    <!-- MISMA ID PARA LEAFLET -->

                    <div
                        id="mapaCliente"
                        style="
                            width:100%;
                            z-index:1;
                        "
                    ></div>

                </div>



                <!-- =============================================
                     ESTADÍSTICAS
                     ============================================= -->

                <?php if ($stats && (int) $stats['total_solicitudes'] > 0): ?>

                    <div class="side-card">

                        <div
                            class="side-header"
                            style="padding-bottom:0;"
                        >

                            <h5 class="fw-bold text-dark mb-0">

                                Resumen de actividad

                            </h5>

                        </div>


                        <div class="stats-container">


                            <div class="stat-item">

                                <div class="stat-value">

                                    <?= (int) $stats['total_solicitudes'] ?>

                                </div>

                                <div class="stat-label">

                                    Pedidos

                                </div>

                            </div>


                            <div class="stat-item">

                                <div class="stat-value text-primary">

                                    <?= (int) $stats['activas'] ?>

                                </div>

                                <div class="stat-label">

                                    Activas

                                </div>

                            </div>


                            <div class="stat-item">

                                <div class="stat-value text-success">

                                    <?= (int) $stats['finalizadas'] ?>

                                </div>

                                <div class="stat-label">

                                    Completas

                                </div>

                            </div>


                        </div>

                    </div>

                <?php endif; ?>


            </div>

        </div>

    </div>

</main>



<!-- ============================================================
     MAPA
     MISMA LÓGICA JAVASCRIPT
     ============================================================ -->

<script>

document.addEventListener("DOMContentLoaded", function() {

    const lat =
        <?= $cliente['latitud_predeterminada'] ?? -16.5 ?>;

    const lng =
        <?= $cliente['longitud_predeterminada'] ?? -68.15 ?>;


    const mapa = L.map(
        'mapaCliente',
        {
            zoomControl: false
        }
    ).setView(
        [lat, lng],
        15
    );


    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; OpenStreetMap'
        }
    ).addTo(mapa);


    /* Marcador premium */

    const iconoDestino = L.divIcon({

        html: `
            <div
                style="
                    width:40px;
                    height:40px;
                    border-radius:50%;
                    background:#dc2626;
                    color:white;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border:3px solid white;
                    box-shadow:
                        0 6px 18px rgba(220,38,38,.35);
                "
            >
                <i class="fa-solid fa-house"></i>
            </div>
        `,

        className: 'bg-transparent',

        iconSize: [40, 40],

        iconAnchor: [20, 20]

    });


    L.marker(
        [lat, lng],
        {
            icon: iconoDestino
        }
    )
    .addTo(mapa)

    .bindPopup(
        '<b class="text-dark">Punto de atención</b>'
    )

    .openPopup();

});

</script>


<?php require_once "../app/views/layouts/footer.php"; ?>