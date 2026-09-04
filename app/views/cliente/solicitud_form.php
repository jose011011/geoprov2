<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-4" style="border-radius: 15px; border-top: 5px solid #00BFA6;">
                <h4 class="mb-1 text-dark fw-bold">Solicitar a <?= htmlspecialchars($profesional['nombre'] . ' ' . $profesional['apellido']) ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars($profesional['nombre_categoria']) ?></p>
                <!-- AQUÍ VA EL NUEVO CÓDIGO DE LA TARIFA -->
    <p class="badge bg-success mb-0" style="font-size: 0.9rem;"><i class="fa-solid fa-money-bill-wave"></i> Tarifa Base de Visita: Bs. <?= number_format($profesional['tarifa_base'], 2) ?></p>

                <!-- AQUÍ SE MOSTRARÁ EL ERROR REAL SI FALLA -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="border-radius: 10px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario corregido con la acción correcta -->
                <form action="<?= BASE_URL ?>/solicitud/crear/<?= $profesional['id_profesional'] ?>" method="POST" id="formSolicitud">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Describe el problema</label>
                        <textarea name="descripcion_problema" class="form-control" rows="3" required minlength="10" placeholder="Ej. Se cortó la luz y el interruptor huele a quemado..."><?= htmlspecialchars($old['descripcion_problema'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Dirección donde se requiere el servicio</label>
                        <input type="text" name="direccion_servicio" class="form-control" placeholder="Av. Principal #123" value="<?= htmlspecialchars($old['direccion_servicio'] ?? $cliente['direccion_referencia'] ?? '') ?>" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Macrodistrito</label>
                            <select name="macrodistrito" class="form-select" required>
                                <?php
                                $opciones = ['SOPOCACHI'=>'Sopocachi','MIRAFLORES'=>'Miraflores','ZONA_SUR'=>'Zona Sur','CENTRO'=>'Centro','SAN_PEDRO'=>'San Pedro','COTAHUMA'=>'Cotahuma','PERIFERICA'=>'Periférica','EL_ALTO'=>'El Alto'];
                                $seleccionado = $old['macrodistrito'] ?? $profesional['macrodistrito_base'];
                                foreach ($opciones as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $seleccionado === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Zona Específica</label>
                            <input type="text" name="zona" class="form-control" placeholder="Ej. Obrajes" value="<?= htmlspecialchars($old['zona'] ?? $cliente['zona'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- MAPA PARA CAPTURAR COORDENADAS -->
                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="fa-solid fa-location-dot text-danger"></i> Confirma la ubicación en el mapa</label>
                        <p class="text-muted small mb-2">Mueve el marcador rojo a la ubicación exacta. Esto ayudará al técnico a llegar más rápido.</p>
                        <div id="mapaUbicacion" style="height: 300px; width: 100%; border-radius: 10px; border: 1px solid #ccc;"></div>
                        
                        <!-- Campos Ocultos Obligatorios -->
                        <input type="hidden" id="lat" name="latitud_destino" value="<?= $cliente['latitud_predeterminada'] ?? -16.5 ?>">
                        <input type="hidden" id="lng" name="longitud_destino" value="<?= $cliente['longitud_predeterminada'] ?? -68.15 ?>">
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold" style="border-radius: 10px; font-size: 1.1rem; background-color: #00BFA6; border: none;">
                        <i class="fa-solid fa-paper-plane me-2"></i> Enviar Solicitud de Servicio
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Librerías de Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let latInicial = parseFloat(document.getElementById('lat').value);
    let lngInicial = parseFloat(document.getElementById('lng').value);

    const mapa = L.map('mapaUbicacion').setView([latInicial, lngInicial], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);

    let marcador = L.marker([latInicial, lngInicial], { draggable: true })
        .addTo(mapa).bindPopup("<b>Ubicación del Servicio</b><br>Arrastrame al punto exacto.").openPopup();

    marcador.on('dragend', function(event) {
        let posicion = marcador.getLatLng();
        document.getElementById('lat').value = posicion.lat;
        document.getElementById('lng').value = posicion.lng;
    });

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            function(posicion) {
                let latReal = posicion.coords.latitude;
                let lngReal = posicion.coords.longitude;
                mapa.setView([latReal, lngReal], 16);
                marcador.setLatLng([latReal, lngReal]);
                document.getElementById('lat').value = latReal;
                document.getElementById('lng').value = lngReal;
            },
            function(error) {
                console.log("Se usará la ubicación por defecto.");
            }
        );
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>