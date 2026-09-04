<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark geo-navbar">
    <div class="container">
        <a class="navbar-brand geo-brand" href="<?= BASE_URL ?>/">
            <i class="fa-solid fa-location-crosshairs"></i> GEO<span class="text-success">-PRO</span>
        </a>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline-light btn-sm">Iniciar sesión</a>
            <a href="<?= BASE_URL ?>/auth/registroCliente" class="btn btn-success btn-sm">Regístrate gratis</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="landing-hero text-center text-white py-5">
    <div class="container py-5">
        <h1 class="display-5 fw-bold">Encuentra al profesional idóneo, cerca de ti</h1>
        <p class="lead text-white-50 mb-4">
            Electricistas, plomeros, técnicos y más — verificados, con geolocalización en tiempo real,
            en la ciudad de La Paz.
        </p>
        <a href="<?= BASE_URL ?>/auth/registroCliente" class="btn btn-success btn-lg me-2">Necesito un servicio</a>
        <a href="<?= BASE_URL ?>/auth/registroProfesional" class="btn btn-outline-light btn-lg">Soy profesional / técnico</a>
    </div>
</section>

<!-- CÓMO FUNCIONA -->
<section class="container py-5">
    <h2 class="text-center mb-5">¿Cómo funciona?</h2>
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="landing-step">
                <i class="fa-solid fa-magnifying-glass fa-2x text-success mb-3"></i>
                <h5>1. Describe tu problema</h5>
                <p class="text-muted">Cuéntanos qué necesitas: una falla eléctrica, una fuga de agua, o cualquier otro servicio del hogar.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="landing-step">
                <i class="fa-solid fa-map-location-dot fa-2x text-success mb-3"></i>
                <h5>2. Conectamos al más cercano</h5>
                <p class="text-muted">Nuestro sistema ubica al profesional verificado disponible más cercano a tu zona.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="landing-step">
                <i class="fa-solid fa-shield-heart fa-2x text-success mb-3"></i>
                <h5>3. Contrata con confianza</h5>
                <p class="text-muted">CI, títulos y antecedentes verificados por nuestro equipo antes de habilitar cada perfil.</p>
            </div>
        </div>
    </div>
</section>

<!-- POR QUÉ GEO-PRO -->
<section class="bg-white py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <h2>Cero comisiones sobre tu trabajo</h2>
                <p class="text-muted">
                    A diferencia de otras plataformas, GEO-PRO no cobra comisión sobre el precio acordado
                    entre cliente y profesional. Nuestro modelo se sostiene con membresías accesibles,
                    para que el 100% de tu trabajo sea tuyo.
                </p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Verificación de identidad y credenciales</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Ubicación en tiempo real del profesional</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Chat directo y calificaciones reales</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Sin intermediación en el cobro del servicio</li>
                </ul>
            </div>
            <div class="col-md-6">
                <div class="row g-3">
                    <div class="col-6"><div class="categoria-card"><i class="fa-solid fa-bolt"></i><div>Electricidad</div></div></div>
                    <div class="col-6"><div class="categoria-card"><i class="fa-solid fa-faucet-drip"></i><div>Plomería</div></div></div>
                    <div class="col-6"><div class="categoria-card"><i class="fa-solid fa-tv"></i><div>Electrónica</div></div></div>
                    <div class="col-6"><div class="categoria-card"><i class="fa-solid fa-trowel-bricks"></i><div>Albañilería</div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA final -->
<section class="landing-hero text-center text-white py-5">
    <div class="container">
        <h3 class="mb-3">¿Eres técnico o profesional independiente?</h3>
        <p class="text-white-50 mb-4">Aumenta tus ingresos sin depender del boca a boca. Regístrate gratis y recibe tus primeros 5 tokens.</p>
        <a href="<?= BASE_URL ?>/auth/registroProfesional" class="btn btn-success btn-lg">Registrarme como profesional</a>
    </div>
</section>

<footer class="geo-footer text-center py-4">
    <strong>GEO-PRO La Paz</strong> · Servicios Profesionales y Técnicos bajo Demanda
    <div class="text-muted small">&copy; <?= date('Y') ?> Todos los derechos reservados.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>