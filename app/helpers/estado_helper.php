<?php
function claseBadgeEstado(string $estado): string {
    $mapa = [
        'PENDIENTE'  => 'badge-pendiente',
        'ACEPTADA'   => 'badge-aceptada',
        'EN_CAMINO'  => 'badge-en_camino',
        'EN_PROCESO' => 'badge-en_proceso',
        'FINALIZADA' => 'badge-finalizada',
        'CANCELADA'  => 'badge-cancelada',
        'APROBADO'   => 'badge-aprobado',
        'RECHAZADO'  => 'badge-rechazado',
    ];
    return $mapa[$estado] ?? 'badge-pendiente';
}