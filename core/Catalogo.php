<?php
/** FixGeo - Listas fijas y ayudas de presentación compartidas por controladores y vistas. */
final class Catalogo
{
    public const MACRODISTRITOS = [
        'ZONA_SUR'   => 'Zona Sur',
        'SOPOCACHI'  => 'Sopocachi',
        'CENTRO'     => 'Centro',
        'MIRAFLORES' => 'Miraflores',
        'SAN_PEDRO'  => 'San Pedro',
        'COTAHUMA'   => 'Cotahuma',
        'PERIFERICA' => 'Periférica',
        'EL_ALTO'    => 'El Alto',
    ];

    private const ESTADOS = [
        'PENDIENTE'  => ['Pendiente',  'pendiente'],
        'ACEPTADA'   => ['Aceptada',   'aceptada'],
        'EN_CAMINO'  => ['En camino',  'en_camino'],
        'EN_PROCESO' => ['En proceso', 'en_proceso'],
        'FINALIZADA' => ['Finalizada', 'finalizada'],
        'CANCELADA'  => ['Cancelada',  'cancelada'],
    ];

    public static function macroValido(?string $valor): string
    {
        return isset(self::MACRODISTRITOS[(string) $valor]) ? (string) $valor : 'CENTRO';
    }

    public static function macroEtiqueta(?string $valor): string
    {
        return self::MACRODISTRITOS[(string) $valor] ?? '-';
    }

    /** Los iconos de la base vienen como "fa-bolt" o "fa-solid fa-wrench": se normalizan. */
    public static function icono(?string $fa): string
    {
        $fa = trim((string) $fa);
        if ($fa === '') {
            return 'fa-solid fa-screwdriver-wrench';
        }
        return preg_match('/\bfa-(solid|regular|brands)\b/', $fa) ? $fa : 'fa-solid ' . $fa;
    }

    public static function etiquetaEstado(string $estado): string
    {
        return self::ESTADOS[$estado][0] ?? $estado;
    }

    /** Insignia HTML del estado (usa las clases .badge-estado de style.css). */
    public static function badgeEstado(string $estado): string
    {
        [$texto, $clase] = self::ESTADOS[$estado] ?? [$estado, 'pendiente'];
        return '<span class="badge-estado badge-' . $clase . '">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    /** Estrellas HTML (llenas, media y vacías) para un promedio de 0 a 5. */
    public static function estrellas(float $promedio): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($promedio >= $i) {
                $html .= '<i class="fa-solid fa-star"></i>';
            } elseif ($promedio >= $i - 0.5) {
                $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
            } else {
                $html .= '<i class="fa-regular fa-star"></i>';
            }
        }
        return '<span class="fx-stars">' . $html . '</span>';
    }

        /** Los planes se guardan como BASICO_MENSUAL, PREMIUM_DESTACADO...; aquí se muestran con nombre amigable. */
    public static function nombrePlan(?string $plan): string
    {
        return match ($plan) {
            'GRATUITO_TOKENS'   => 'Gratuito',
            'BASICO_MENSUAL'    => 'Básico',
            'PREMIUM_DESTACADO' => 'Premium',
            default             => ucwords(strtolower(str_replace('_', ' ', (string) $plan))),
        };
    }

        /** Texto para URL: sin tildes, en minúsculas y con guiones. */
    public static function slug(string $texto): string
    {
        $t = strtr(mb_strtolower($texto, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', $t), '-') ?: 'categoria';
    }
}