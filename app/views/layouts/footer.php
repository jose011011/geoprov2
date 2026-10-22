</main>

<footer class="bg-white border-top text-center text-muted small py-4">
    © <?= date('Y') ?> FixGeo · Servicios técnicos en La Paz y El Alto
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Evita doble envío: tras el primer envío válido se bloquea el botón (sin perder el valor de botones con name/value)
document.addEventListener('submit', function (e) {
    const f = e.target;
    if (f.method.toLowerCase() !== 'post' || e.defaultPrevented) return;
    if (f.dataset.enviando === '1') { e.preventDefault(); return; }
    f.dataset.enviando = '1';
    setTimeout(function () {
        f.querySelectorAll('button[type=submit], button:not([type])').forEach(function (b) {
            b.disabled = true;
            if (!b.dataset.txt) { b.dataset.txt = b.innerHTML; b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...'; }
        });
    }, 30);
});
// Si el usuario vuelve con el botón "atrás", se restauran los botones
window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    document.querySelectorAll('form').forEach(function (f) { f.dataset.enviando = ''; });
    document.querySelectorAll('button[data-txt]').forEach(function (b) { b.disabled = false; b.innerHTML = b.dataset.txt; delete b.dataset.txt; });
});
</script>
</body>
</html>