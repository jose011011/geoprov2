<?php
if (!defined('BASE_URL')) {
    die('BASE_URL no está definida. Verifique app/config/config.php');
}
$authAncho = $authAncho ?? 'sm';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1d3fb4">
    <meta name="csrf-token" content="<?= Security::token() ?>">
    <title><?= Security::e($titulo ?? 'FixGeo') ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/img/logo-fixgeo.png" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/fixgeo-theme.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/auth.css" rel="stylesheet">
</head>
<body class="fx-auth">

<header class="fx-auth-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="fx-brand" href="<?= BASE_URL ?>/">
            <span class="fx-brand-mark"><i class="fa-solid fa-location-dot"></i></span>
            <span>Fix<b>Geo</b></span>
        </a>
        <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/"><i class="fa-solid fa-arrow-left me-1"></i>Volver al inicio</a>
    </div>
</header>

<main class="fx-auth-main">
    <div class="container">
        <div class="fx-auth-card fx-auth-card--<?= Security::e($authAncho) ?>">
            <?php $alertasContenedor = false; require __DIR__ . '/alertas.php'; ?>