<?php
/**
 * FixGeo - Validaciones del servidor. Espejo de public/js/auth-validation.js.
 * Cada método devuelve un string con el error, o null si es válido.
 */
final class Validador
{
    public static function nombre(string $v): ?string
    {
        $v = trim($v);
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]{2,}( [a-zA-ZáéíóúÁÉÍÓÚñÑ]+)?$/u', $v)) {
            return 'El nombre debe tener 1 o 2 palabras, solo letras (mínimo 2).';
        }
        return null;
    }

    public static function apellido(string $v, bool $obligatorio = true): ?string
    {
        if ($v === '' && !$obligatorio) return null;
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]{2,}$/u', $v) ? null : 'Apellido: solo letras, sin espacios (mínimo 2).';
    }

    public static function correo(string $v): ?string
    {
        return (strlen($v) <= 120 && filter_var($v, FILTER_VALIDATE_EMAIL)) ? null : 'Ingrese un correo válido.';
    }

    public static function celular(string $v): ?string
    {
        return preg_match('/^[67]\d{7}$/', $v) ? null : 'Celular de 8 dígitos que empiece con 6 o 7.';
    }

    public static function documento(string $tipo, string $v): ?string
    {
        $v = strtoupper(trim($v));
        return match ($tipo) {
            'CI'         => preg_match('/^[0-9A-Z\-]{5,12}$/', $v) ? null : 'CI: entre 5 y 12 caracteres.',
            'NIT'        => preg_match('/^\d{7,15}$/', $v)         ? null : 'NIT: entre 7 y 15 números.',
            'EXTRANJERO' => preg_match('/^[0-9A-Z\-]{5,15}$/', $v) ? null : 'Documento: entre 5 y 15 caracteres.',
            default      => 'Tipo de documento no válido.',
        };
    }

    public static function experiencia(string $v): ?string
    {
        return (ctype_digit($v) && (int) $v <= 50) ? null : 'Años de experiencia: entre 0 y 50.';
    }

    public static function password(string $v): ?string
    {
        if (preg_match('/\s/', $v))              return 'La contraseña no puede tener espacios.';
        if (strlen($v) < 8)                      return 'Mínimo 8 caracteres.';
        if (!preg_match('/[A-Z]/', $v))          return 'Falta 1 mayúscula.';
        if (!preg_match('/[0-9]/', $v))          return 'Falta 1 número.';
        if (!preg_match('/[@$!%*?&\-_.,]/', $v)) return 'Falta 1 símbolo (@$!%*?&-_.,).';
        return null;
    }

    /** Junta los errores no nulos: Validador::errores(['nombre' => Validador::nombre($n), ...]) */
    public static function errores(array $resultados): array
    {
        return array_filter($resultados, fn($e) => $e !== null);
    }


        /** Reglas comunes para registrar a un técnico u oficio (sin datos personales). Devuelve solo los errores. */
    public static function prestador(array $d, array $f, string $tipo): array
    {
        $r = [];

        $cat = (string) ($d['id_categoria'] ?? '');
        $r['categoria'] = ($cat === 'OTRO' || ctype_digit($cat)) ? null : 'Selecciona tu especialidad.';
        if ($cat === 'OTRO' && mb_strlen((string) ($d['otra_especialidad'] ?? '')) < 3) {
            $r['otra'] = 'Escribe tu especialidad (mínimo 3 letras).';
        }

        $r['documento']   = self::documento((string) ($d['tipo_doc'] ?? ''), (string) ($d['numero_documento'] ?? ''));
        $r['experiencia'] = self::experiencia((string) ($d['experiencia'] ?? ''));

        $desc = mb_strlen((string) ($d['descripcion'] ?? ''));
        $r['descripcion'] = ($desc >= 20 && $desc <= 500) ? null : 'Cuéntanos tu experiencia (entre 20 y 500 caracteres).';

        $zona = mb_strlen((string) ($d['zona_especifica'] ?? ''));
        $r['zona_esp'] = ($zona >= 3 && $zona <= 150) ? null : 'Indica la zona donde trabajas.';

        $r['macro'] = isset(Catalogo::MACRODISTRITOS[$d['macrodistrito'] ?? '']) ? null : 'Selecciona tu macrodistrito.';

        $tarifa = $d['tarifa'] ?? '';
        $r['tarifa'] = ($tarifa === '' || (is_numeric($tarifa) && $tarifa >= 0 && $tarifa <= 9999))
            ? null : 'La tarifa debe estar entre 0 y 9999 Bs.';

        $requeridos = ['doc_ci_anverso' => 'foto del CI (anverso)', 'doc_ci_reverso' => 'foto del CI (reverso)'];
        if ($tipo === 'TECNICO_PROFESIONAL') {
            $requeridos['doc_titulo'] = 'título institucional';
        }
        foreach ($requeridos as $campo => $nombre) {
            if (empty($f[$campo]['tmp_name']) || ($f[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $r[$campo] = "Falta subir: {$nombre}.";
            }
        }

        return self::errores($r);
    }
}