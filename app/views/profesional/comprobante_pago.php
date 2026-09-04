<?php require_once "../app/views/layouts/header.php"; ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 text-center">
                <h5><?= htmlspecialchars($plan['nombre_plan']) ?></h5>
                <div class="fs-2 fw-bold my-2">Bs <?= number_format($plan['precio_mensual'], 2) ?></div>

                <div class="bg-light p-4 rounded my-3">
                    <i class="fa-solid fa-qrcode fa-5x text-secondary"></i>
                    <p class="small text-muted mt-2 mb-0">Escanea con tu app QR Simple / banco y paga el monto exacto.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="text-start">
                    <label class="form-label small fw-bold">Código de comprobante / N° de transacción</label>
                    <input type="text" name="codigo_comprobante" class="form-control mb-3" placeholder="Ej. QR-2026090112345" required>
                    <button type="submit" class="btn btn-success w-100">Confirmar pago realizado</button>
                </form>
                <p class="small text-muted mt-3">Un administrador validará tu pago manualmente en las próximas horas.</p>
            </div>
        </div>
    </div>
</div>
<?php require_once "../app/views/layouts/footer.php"; ?>