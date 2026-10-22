<?php
/** FixGeo - Mensajes flash. Tipos: success, danger, warning, info. */
final class Flash
{
    public static function set(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][] = ['t' => $tipo, 'm' => $mensaje];
    }

    public static function pull(): array
    {
        $mensajes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $mensajes;
    }
}