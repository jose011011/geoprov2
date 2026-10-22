<?php
$e    = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$ruta = [
    'login'       => $base . '/auth/login',
    'cliente'     => $base . '/auth/registroCliente',
    'profesional' => $base . '/auth/registroProfesional',
    'oficio'      => $base . '/auth/registroEmpirico',
];
$versionCss = (int) @filemtime(__DIR__ . '/../../../public/css/landing.css');
$categorias = $categorias ?? [];
$planes     = array_values(array_filter($planes ?? [], fn($p) => (int) ($p['estado'] ?? 1) === 1));
$panelUrl   = $panelUrl ?? null;
$irA        = $panelUrl ?: $ruta['cliente'];
$linkCat    = fn($id) => $panelUrl ? $base . '/cliente/buscarEspecialista?cat=' . (int) $id : $ruta['cliente'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $e($titulo ?? 'FixGeo') ?></title>
<meta name="description" content="Pide un técnico o trabajador de oficio verificado en La Paz y El Alto. Cuéntanos el problema, sigue su llegada en el mapa y califica el servicio.">
<meta name="theme-color" content="#1d3fb4">
<meta property="og:type" content="website">
<meta property="og:title" content="FixGeo: técnicos verificados en La Paz y El Alto">
<meta property="og:description" content="Describe el problema, te conectamos con un técnico verificado y sigues su llegada en el mapa.">
<link rel="icon" href="<?= $base ?>/img/logo-fixgeo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="<?= $base ?>/css/landing.css?v=<?= $versionCss ?>" rel="stylesheet">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'FixGeo',
    'description' => 'Plataforma que conecta hogares con técnicos y trabajadores de oficio verificados en La Paz y El Alto, Bolivia.',
    'areaServed' => ['La Paz', 'El Alto'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body>
<a class="fg-skip" href="#contenido">Saltar al contenido</a>

<header class="fg-top">
    <div class="fg-wrap">
        <a class="fg-logo" href="#inicio" aria-label="FixGeo, inicio">
            <span class="fg-logo-marca"><i class="fa-solid fa-location-dot"></i></span><span>Fix<b>Geo</b></span>
        </a>
        <nav class="fg-nav" aria-label="Secciones">
            <a href="#como-funciona">Cómo funciona</a>
            <a href="#servicios">Servicios</a>
            <a href="#para-tecnicos">Para técnicos</a>
            <a href="#preguntas">Preguntas</a>
        </nav>
        <div class="fg-acciones">
            <?php if ($panelUrl): ?>
                <a class="fg-btn fg-btn-primary" href="<?= $e($panelUrl) ?>">Ir a mi panel</a>
            <?php else: ?>
                <a class="fg-btn fg-btn-ghost fg-solo-pc" href="<?= $e($ruta['login']) ?>">Ingresar</a>
                <a class="fg-btn fg-btn-primary fg-solo-pc" href="<?= $e($ruta['cliente']) ?>">Crear cuenta</a>
            <?php endif; ?>
            <button class="fg-menu-btn" id="abrirMenu" type="button" aria-controls="menuLateral" aria-expanded="false" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
</header>

<!-- Menú lateral (celular) -->
<div class="fg-fondo" id="menuFondo"></div>
<aside class="fg-drawer" id="menuLateral" aria-label="Menú">
    <div class="fg-drawer-cab">
        <span class="fg-logo"><span class="fg-logo-marca"><i class="fa-solid fa-location-dot"></i></span><span>Fix<b>Geo</b></span></span>
        <button type="button" id="cerrarMenu" class="fg-menu-btn" aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="fg-drawer-nav">
        <a href="#como-funciona">Cómo funciona</a>
        <a href="#servicios">Servicios</a>
        <a href="#confianza">Por qué confiar</a>
        <a href="#para-tecnicos">Para técnicos</a>
        <a href="#preguntas">Preguntas</a>
    </nav>
    <div class="fg-drawer-pie">
        <?php if ($panelUrl): ?>
            <a class="fg-btn fg-btn-primary" href="<?= $e($panelUrl) ?>">Ir a mi panel</a>
        <?php else: ?>
            <a class="fg-btn fg-btn-primary" href="<?= $e($ruta['cliente']) ?>">Crear cuenta</a>
            <a class="fg-btn fg-btn-ghost" href="<?= $e($ruta['login']) ?>">Ingresar</a>
        <?php endif; ?>
    </div>
</aside>

<main id="contenido">

<!-- ================= Inicio ================= -->
<section class="fg-hero" id="inicio">
    <svg class="fg-contornos" viewBox="0 0 600 600" aria-hidden="true" focusable="false">
        <defs><path id="curva" vector-effect="non-scaling-stroke" d="M300 60C420 50 540 140 550 270C560 400 470 530 330 545C190 560 70 470 55 330C40 190 160 70 300 60Z"/></defs>
        <g fill="none" stroke="currentColor" stroke-width="1.2">
            <use href="#curva"/>
            <use href="#curva" transform="rotate(7 300 300) translate(300 300) scale(.86) translate(-300 -300)"/>
            <use href="#curva" transform="rotate(-9 300 300) translate(306 296) scale(.72) translate(-300 -300)"/>
            <use href="#curva" transform="rotate(14 300 300) translate(296 306) scale(.58) translate(-300 -300)"/>
            <use href="#curva" transform="rotate(-18 300 300) translate(308 298) scale(.44) translate(-300 -300)"/>
            <use href="#curva" transform="rotate(24 300 300) translate(300 304) scale(.3) translate(-300 -300)"/>
            <use href="#curva" transform="rotate(-30 300 300) translate(303 300) scale(.17) translate(-300 -300)"/>
        </g>
    </svg>

    <div class="fg-wrap fg-hero-grid">
        <div class="fg-hero-texto">
            <h1>Cuéntanos qué se dañó. Te mandamos al técnico que lo arregla.</h1>
            <p class="fg-lead">FixGeo conecta hogares de La Paz y El Alto con técnicos y trabajadores de oficio verificados. Tú describes el problema y ves en el mapa cómo se acerca quien va a tu casa.</p>
            <div class="fg-cta">
                <a class="fg-btn fg-btn-primary" href="<?= $e($irA) ?>"><?= $panelUrl ? 'Pedir un servicio' : 'Pedir un servicio' ?></a>
                <a class="fg-btn fg-btn-text" href="#para-tecnicos">Tengo un oficio y busco trabajo</a>
            </div>
            <p class="fg-fine">Pedir un servicio no tiene costo para ti.</p>
        </div>

        <div class="fg-demo" role="group" aria-labelledby="demoTitulo">
            <label class="fg-demo-titulo" id="demoTitulo" for="demoTexto">Cuéntale a FixGeo qué pasó</label>
            <input class="fg-demo-input" id="demoTexto" type="text" maxlength="120" autocomplete="off" placeholder="Ej: se quemó el foco de mi sala">
            <div class="fg-chips" aria-label="Ejemplos para probar">
                <button type="button" data-ej="Se quemó el foco de mi sala">Foco quemado</button>
                <button type="button" data-ej="Gotea el grifo de la cocina">Grifo que gotea</button>
                <button type="button" data-ej="No abre la chapa de la puerta">Chapa trabada</button>
                <button type="button" data-ej="Necesito una niñera el sábado">Niñera</button>
            </div>

            <div class="fg-res" aria-live="polite">
                <p class="fg-res-cat"><span class="fg-ico"><i id="demoIcono" class="fa-solid fa-wand-magic-sparkles"></i></span><span id="demoTextoRes">Escribe un problema o toca un ejemplo.</span></p>
                <svg class="fg-mapa" viewBox="0 0 360 200" role="img" aria-label="Mapa de ejemplo: un técnico se acerca a la casa">
                    <g fill="none" stroke="#d3dcef" stroke-width="1.2">
                        <path d="M-10 60C60 20 120 80 190 50S320 10 370 40"/>
                        <path d="M-10 100C70 60 130 120 200 92S320 56 370 82"/>
                        <path d="M-10 142C80 106 140 166 210 136S330 104 370 126"/>
                        <path d="M-10 182C90 150 150 205 220 178S335 150 370 168"/>
                    </g>
                    <path id="rutaDemo" d="M30 165C95 150 105 92 168 100S262 66 320 42" fill="none" stroke="#1d3fb4" stroke-width="4" stroke-linecap="round" stroke-dasharray="6 9"/>
                    <g transform="translate(320 42)"><circle r="13" fill="#1d3fb4"/><circle r="4.5" fill="#fff"/></g>
                    <circle id="puntoTecnico" cx="30" cy="165" r="8" fill="#f5a524" stroke="#fff" stroke-width="3" opacity="0"/>
                </svg>
                <p class="fg-eta" id="demoEta" hidden>Tu técnico llega en <b>12 min</b> <span>(ejemplo)</span></p>
            </div>
            <p class="fg-fine">Demostración. En la app, el asistente elige la categoría y los técnicos reales de tu zona.</p>
        </div>
    </div>
</section>

<!-- ================= Cómo funciona ================= -->
<section class="fg-seccion fg-niebla" id="como-funciona">
    <div class="fg-wrap fg-dos">
        <div class="fg-dos-izq">
            <h2>Cómo funciona FixGeo</h2>
            <p class="fg-intro">Del pedido a la calificación, en cuatro paradas. Tú solo cuentas qué pasó.</p>
        </div>
        <ol class="fg-pasos">
            <li class="fg-paso">
                <span class="fg-n">1</span>
                <h3>Cuentas el problema</h3>
                <p>Lo escribes con tus palabras, por ejemplo «se quemó el foco de mi sala». El asistente elige la categoría. También puedes escogerla tú o elegir a un técnico del catálogo.</p>
            </li>
            <li class="fg-paso">
                <span class="fg-n">2</span>
                <h3>Avisamos a los técnicos de tu zona</h3>
                <p>Los técnicos verificados de esa categoría reciben el aviso. Los de plan Premium lo ven al instante y los demás, unos minutos después. Se queda con el trabajo quien acepta primero.</p>
            </li>
            <li class="fg-paso">
                <span class="fg-n">3</span>
                <h3>Sigues su llegada</h3>
                <p>Cuando el técnico sale hacia tu casa, lo ves moverse en el mapa. Puedes escribirle por el chat y enviarle fotos del problema.</p>
            </li>
            <li class="fg-paso">
                <span class="fg-n">4</span>
                <h3>Se hace el trabajo y calificas</h3>
                <p>Al terminar, el técnico registra el precio final y tú calificas el servicio. Tu opinión ayuda a que otros clientes elijan bien.</p>
            </li>
        </ol>
    </div>
</section>

<!-- ================= Servicios ================= -->
<section class="fg-seccion" id="servicios">
    <div class="fg-wrap">
        <h2>Qué puedes pedir</h2>
        <p class="fg-intro">Si no ves tu caso, descríbelo al pedir el servicio: el asistente lo entiende.</p>
        <?php if ($categorias): ?>
            <ul class="fg-servicios">
                <?php foreach ($categorias as $c): ?>
                    <li><a href="<?= $e($linkCat($c['id_categoria'])) ?>">
                        <i class="<?= $e(Catalogo::icono($c['icono_fa'])) ?>" aria-hidden="true"></i><span><?= $e($c['nombre_categoria']) ?></span>
                    </a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="fg-intro">Muy pronto verás aquí todas las categorías disponibles.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ================= Confianza ================= -->
<section class="fg-seccion fg-niebla" id="confianza">
    <div class="fg-wrap fg-dos">
        <div class="fg-dos-izq">
            <h2>Por qué puedes confiar</h2>
            <p class="fg-intro">Dejas entrar a alguien a tu casa. Por eso cuidamos quién aparece y qué información se comparte.</p>
        </div>
        <dl class="fg-confianza">
            <div><dt>Documentos revisados</dt><dd>Un administrador revisa el carnet y, si corresponde, el título de cada técnico antes de mostrarlo en el catálogo.</dd></div>
            <div><dt>Ubicación solo cuando hace falta</dt><dd>Ves la ubicación del técnico únicamente mientras va en camino hacia ti. Tu dirección exacta la ve solo quien acepta tu pedido.</dd></div>
            <div><dt>Chat privado</dt><dd>Solo tú y tu técnico ven la conversación. Los mensajes se borran 15 días después de terminar el servicio.</dd></div>
            <div><dt>Calificaciones reales</dt><dd>Solo califica quien contrató y terminó el servicio. Antes de elegir, ves las estrellas de cada técnico.</dd></div>
            <div><dt>Una sola cuenta</dt><dd>Si además tienes un oficio, activas tu perfil de técnico con la misma cuenta, sin registrarte otra vez.</dd></div>
        </dl>
    </div>
</section>

<!-- ================= Para técnicos ================= -->
<section class="fg-seccion" id="para-tecnicos">
    <div class="fg-wrap">
        <div class="fg-tecnicos-cab">
            <h2>Si tienes un oficio, aquí hay trabajo</h2>
            <p class="fg-intro">Te registras gratis, subes tus documentos y recibes los pedidos de tu categoría y tu zona. Cada trabajo que aceptas usa 1 token. Mientras más alto tu plan, antes ves cada pedido nuevo.</p>
        </div>

        <?php if ($planes): ?>
            <div class="fg-scroll">
                <table class="fg-tabla">
                    <caption class="fg-solo-lector">Planes para técnicos</caption>
                    <thead><tr><th scope="col">Plan</th><th scope="col">Ves un pedido nuevo</th><th scope="col">Tokens</th><th scope="col">Posición en el catálogo</th><th scope="col">Precio</th></tr></thead>
                    <tbody>
                    <?php foreach ($planes as $p):
                        $espera   = isset($p['retraso_cascada_min']) ? (int) $p['retraso_cascada_min'] : null;
                        $destacado = (int) ($p['posicionamiento_destacado'] ?? 0) === 1; ?>
                        <tr class="<?= $destacado ? 'fg-fila-destacada' : '' ?>">
                            <th scope="row"><?= $e(Catalogo::nombrePlan($p['nombre_plan'])) ?></th>
                            <td><?= $espera === null ? '—' : ($espera === 0 ? 'Al instante' : 'A los ' . $espera . ' minutos') ?></td>
                            <td><?= (int) $p['tokens_otorgados'] >= 999 ? 'Ilimitados' : (int) $p['tokens_otorgados'] . ' al mes' ?></td>
                            <td><?= $destacado ? 'Primero' : 'Normal' ?></td>
                            <td><?= (float) $p['precio'] <= 0 ? 'Gratis' : 'Bs ' . number_format((float) $p['precio'], 0) . ' al mes' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="fg-registro">
            <a class="fg-btn fg-btn-primary" href="<?= $e($ruta['profesional']) ?>">Soy técnico con título</a>
            <a class="fg-btn fg-btn-ghost" href="<?= $e($ruta['oficio']) ?>">Soy trabajador de oficio</a>
            <p class="fg-fine">Un administrador valida tus documentos antes de mostrarte en el catálogo.</p>
        </div>
    </div>
</section>

<!-- ================= Preguntas ================= -->
<section class="fg-seccion fg-niebla" id="preguntas">
    <div class="fg-wrap fg-dos">
        <div class="fg-dos-izq"><h2>Preguntas frecuentes</h2></div>
        <div class="fg-faq">
            <details><summary>¿Cuánto cuesta pedir un servicio?</summary><p>Pedir un servicio no tiene costo para ti. El precio del trabajo lo acuerdas con el técnico y queda registrado cuando termina.</p></details>
            <details><summary>¿Cómo sé que el técnico es de confianza?</summary><p>Antes de aparecer en el catálogo, un administrador revisa sus documentos. Además ves sus calificaciones, y después de cada servicio tú calificas al técnico.</p></details>
            <details><summary>¿Qué pasa si nadie acepta mi pedido?</summary><p>Tu pedido sigue abierto y los técnicos de tu zona lo reciben apenas les corresponde según su plan. Si no hay técnicos disponibles de esa categoría en tu zona, te lo decimos para que pruebes con el catálogo o más tarde.</p></details>
            <details><summary>¿Mi ubicación y mis mensajes son privados?</summary><p>Tu dirección exacta solo la ve el técnico que acepta tu pedido. Te mostramos la ubicación del técnico solo mientras va hacia ti, y los mensajes del chat se borran 15 días después de terminar el servicio.</p></details>
            <details><summary>¿Puedo ser cliente y técnico con la misma cuenta?</summary><p>Sí. Desde tu menú eliges «Ofrecer mis servicios», subes tus documentos y tu cuenta pasa a tener los dos perfiles.</p></details>
            <details><summary>¿En qué zonas funciona?</summary><p>En La Paz y El Alto. Cada técnico indica su macrodistrito y también recibe los pedidos que estén a menos de 10 km de donde se encuentra.</p></details>
        </div>
    </div>
</section>

<!-- ================= Cierre ================= -->
<section class="fg-final">
    <div class="fg-wrap">
        <h2>¿Algo se dañó hoy?</h2>
        <p>Cuéntanos qué pasó y te conectamos con un técnico verificado.</p>
        <div class="fg-cta">
            <a class="fg-btn fg-btn-claro" href="<?= $e($irA) ?>">Pedir un servicio</a>
            <?php if (!$panelUrl): ?><a class="fg-btn fg-btn-contorno" href="<?= $e($ruta['login']) ?>">Ya tengo cuenta</a><?php endif; ?>
        </div>
    </div>
</section>
</main>

<footer class="fg-pie">
    <div class="fg-wrap">
        <span class="fg-logo"><span class="fg-logo-marca"><i class="fa-solid fa-location-dot"></i></span><span>Fix<b>Geo</b></span></span>
        <p>Servicios técnicos y de oficio en La Paz y El Alto, Bolivia.</p>
        <nav aria-label="Enlaces del pie">
            <a href="<?= $e($ruta['login']) ?>">Ingresar</a>
            <a href="<?= $e($ruta['cliente']) ?>">Crear cuenta</a>
            <a href="<?= $e($ruta['profesional']) ?>">Registro de técnicos</a>
            <a href="<?= $e($ruta['oficio']) ?>">Registro de oficios</a>
        </nav>
        <small>© <?= date('Y') ?> FixGeo</small>
    </div>
</footer>

<script>
/* ---- Menú lateral ---- */
(function () {
    const abrir = document.getElementById('abrirMenu'), cerrar = document.getElementById('cerrarMenu');
    const fondo = document.getElementById('menuFondo'), panel = document.getElementById('menuLateral');
    function estado(abierto, devolverFoco) {
        document.body.classList.toggle('menu-abierto', abierto);
        abrir.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        if (abierto) { cerrar.focus(); } else if (devolverFoco) { abrir.focus(); }
    }
    abrir.addEventListener('click', () => estado(true));
    cerrar.addEventListener('click', () => estado(false, true));
    fondo.addEventListener('click', () => estado(false, true));
    panel.querySelectorAll('a').forEach(a => a.addEventListener('click', () => estado(false, false)));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && document.body.classList.contains('menu-abierto')) estado(false, true); });
    matchMedia('(min-width: 901px)').addEventListener('change', e => { if (e.matches) estado(false, false); });
})();

/* ---- Demostración: escribe un problema, se entiende y el técnico llega ---- */
(function () {
    const DEMO = [
        { n: 'Electricidad',      i: 'fa-bolt',          k: ['foco', 'luz', 'enchuf', 'cable', 'tablero', 'corto', 'electric', 'apag', 'interruptor'] },
        { n: 'Plomería',          i: 'fa-faucet-drip',   k: ['agua', 'fuga', 'grifo', 'tuberia', 'inodoro', 'desague', 'gotea', 'calefon', 'ducha', 'lavamanos'] },
        { n: 'Cerrajería',        i: 'fa-key',           k: ['chapa', 'cerradura', 'candado', 'puerta', 'cerraj', 'llaves'] },
        { n: 'Electrodomésticos', i: 'fa-tv',            k: ['refri', 'lavadora', 'microondas', 'televis', 'tele', 'heladera', 'plancha'] },
        { n: 'Pintura',           i: 'fa-paint-roller',  k: ['pintar', 'pintur', 'brocha'] },
        { n: 'Albañilería',       i: 'fa-trowel-bricks', k: ['grieta', 'ladrillo', 'muro', 'cemento', 'revoque', 'ceramica', 'piso', 'techo', 'pared'] },
        { n: 'Cuidado del hogar', i: 'fa-baby-carriage', k: ['ninera', 'cuidar', 'nino', 'bebe', 'limpieza', 'abuel'] },
    ];
    const $ = id => document.getElementById(id);
    const input = $('demoTexto'), icono = $('demoIcono'), texto = $('demoTextoRes'), eta = $('demoEta');
    const ruta = $('rutaDemo'), punto = $('puntoTecnico');
    const sinMovimiento = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const largo = ruta.getTotalLength();
    let cuadro = null, tecla = null, debounce = null, ultima = '', interactuo = false;

    const norm = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const detectar = t => { const x = norm(t); return DEMO.find(d => d.k.some(k => x.includes(k))) || null; };

    function mover(p) {
        const pt = ruta.getPointAtLength(largo * p);
        punto.setAttribute('cx', pt.x); punto.setAttribute('cy', pt.y);
        eta.querySelector('b').textContent = Math.max(1, Math.round(12 * (1 - p))) + ' min';
    }

    function viajar() {
        cancelAnimationFrame(cuadro);
        eta.hidden = false;
        punto.style.opacity = 1;
        if (sinMovimiento) { mover(.8); return; }
        punto.style.opacity = 0;
        const inicio = performance.now() + 500, duracion = 4200;
        const paso = ahora => {
            if (ahora >= inicio) { punto.style.opacity = 1; mover(Math.min(1, (ahora - inicio) / duracion) * .92); }
            if (ahora - inicio < duracion) { cuadro = requestAnimationFrame(paso); }
        };
        cuadro = requestAnimationFrame(paso);
    }

    function mostrar() {
        const t = input.value.trim();
        const d = t ? detectar(t) : null;
        if (!t) {
            ultima = ''; icono.className = 'fa-solid fa-wand-magic-sparkles';
            texto.textContent = 'Escribe un problema o toca un ejemplo.'; eta.hidden = true; punto.style.opacity = 0; return;
        }
        if (!d) {
            ultima = ''; icono.className = 'fa-solid fa-circle-question';
            texto.textContent = 'No reconocimos ese problema en esta demostración. En la app puedes elegir la categoría tú mismo.';
            eta.hidden = true; punto.style.opacity = 0; return;
        }
        icono.className = 'fa-solid ' + d.i;
        texto.textContent = 'Es un trabajo de ' + d.n + '. Avisamos a los técnicos verificados de tu zona.';
        if (ultima !== d.n) { ultima = d.n; viajar(); }
    }

    function detener() {
        interactuo = true;
        if (tecla) { clearInterval(tecla); tecla = null; }
    }

    input.addEventListener('focus', detener);
    input.addEventListener('keydown', detener);
    input.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(mostrar, 350); });
    document.querySelectorAll('.fg-chips button').forEach(b => b.addEventListener('click', () => {
        detener(); input.value = b.dataset.ej; mostrar();
    }));

    // Único movimiento automático de la página: se escribe un ejemplo una vez
    const ejemplo = 'Se quemó el foco de mi sala';
    if (sinMovimiento) { input.value = ejemplo; mostrar(); return; }
    setTimeout(() => {
        if (interactuo) return;
        let i = 0;
        tecla = setInterval(() => {
            input.value = ejemplo.slice(0, ++i);
            if (i >= ejemplo.length) { clearInterval(tecla); tecla = null; mostrar(); }
        }, 55);
    }, 1100);
})();
</script>
</body>
</html>