<?php
class Busqueda {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca profesionales APROBADOS y DISPONIBLES de una categoría,
     * ordenados por cercanía real (km) al cliente.
     */
    public function buscarPorCategoria(string $slugCategoria, float $latCliente, float $lngCliente, int $radioKm = 20): array {
        $sql = "
            SELECT
                p.id_profesional,
                p.tipo_prestador,
                p.experiencia_anios,
                p.descripcion_servicio,
                p.tarifa_base,
                p.macrodistrito_base,
                p.zona_especifica,
                p.latitud_actual,
                p.longitud_actual,
                u.nombre,
                u.apellido,
                c.nombre_categoria,
                pl.posicionamiento_destacado,
                COALESCE(ROUND(AVG(cal.puntuacion_general), 1), 5.0) AS promedio_estrellas,
                COUNT(DISTINCT s.id_solicitud) AS total_servicios,
                ROUND(
                    6371 * ACOS(
                        COS(RADIANS(:lat1)) * COS(RADIANS(p.latitud_actual)) *
                        COS(RADIANS(p.longitud_actual) - RADIANS(:lng1)) +
                        SIN(RADIANS(:lat2)) * SIN(RADIANS(p.latitud_actual))
                    ), 2
                ) AS distancia_km
            FROM profesionales p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
            LEFT JOIN solicitudes_servicio s ON p.id_profesional = s.id_profesional AND s.estado_servicio = 'FINALIZADA'
            LEFT JOIN calificaciones cal ON s.id_solicitud = cal.id_solicitud
            WHERE c.slug = :slug
              AND p.estado_validacion = 'APROBADO'
              AND p.estado_disponibilidad = 'DISPONIBLE'
              AND p.latitud_actual IS NOT NULL
              AND p.longitud_actual IS NOT NULL
            GROUP BY p.id_profesional
            HAVING distancia_km <= :radio OR distancia_km IS NULL
            ORDER BY pl.posicionamiento_destacado DESC, distancia_km ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':slug'  => $slugCategoria,
            ':lat1'  => $latCliente,
            ':lng1'  => $lngCliente,
            ':lat2'  => $latCliente,
            ':radio' => $radioKm
        ]);
        return $stmt->fetchAll();
    }

    public function obtenerCategoriaPorSlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT id_categoria, nombre_categoria, icono_fa FROM categorias WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Detección simple por palabras clave (fallback cuando Gemini falla o no encuentra coincidencia).
     */
    public function detectarCategoriaPorTexto(string $texto): ?string {
        $texto = mb_strtolower($texto, 'UTF-8');

        $mapa = [
            'electricidad' => ['luz', 'corto', 'chispa', 'cable', 'enchufe', 'térmico', 'termico', 'cortocircuito', 'apagón', 'apagon', 'tablero', 'breaker', 'foco', 'electricidad', 'electricista'],
            'electronica'  => ['tv', 'televisor', 'refrigerador', 'heladera', 'microondas', 'lavadora', 'electrodoméstico', 'electrodomestico', 'no enciende', 'pantalla'],
            'plomeria'     => ['fuga', 'agua', 'cañería', 'caneria', 'inodoro', 'baño', 'grifo', 'llave de agua', 'desagüe', 'desague', 'plomero', 'gasfitero'],
            'cuidado-nineras' => ['niñera', 'ninera', 'cuidado', 'niños', 'ninos', 'adulto mayor', 'hogar'],
            'albanileria'  => ['pared', 'muro', 'estuco', 'revoque', 'construcción', 'construccion', 'albañil', 'albanil', 'cerámica', 'ceramica', 'grieta'],
        ];

        foreach ($mapa as $slug => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($texto, $palabra)) {
                    return $slug;
                }
            }
        }
        return null;
    }

    /**
     * Usa Gemini para detectar la categoría a partir de una descripción en lenguaje natural.
     * Si Gemini falla o no encuentra coincidencia, cae automáticamente al detector de palabras clave.
     */
    public function detectarCategoriaConIA(string $texto): ?string {
        $stmt = $this->db->query("SELECT nombre_categoria, slug FROM categorias WHERE estado = 1");
        $categorias = $stmt->fetchAll();

        if (empty($categorias)) {
            return null;
        }

        $listaCategorias = array_map(fn($c) => $c['slug'] . ' (' . $c['nombre_categoria'] . ')', $categorias);
        $listaTexto = implode(', ', $listaCategorias);

        $prompt = "Eres un clasificador de solicitudes de servicios técnicos y oficios en La Paz, Bolivia. "
            . "Dada la descripción de un problema, responde ÚNICAMENTE con el slug exacto de la categoría más adecuada de esta lista: "
            . "{$listaTexto}. "
            . "Si ninguna categoría coincide claramente, responde exactamente: NINGUNA. "
            . "No agregues explicaciones, comillas, ni texto adicional. Solo el slug o NINGUNA.\n\n"
            . "Descripción del cliente: \"{$texto}\"";

        try {
            $slugDetectado = $this->llamarGemini($prompt);
        } catch (Exception $e) {
            return $this->detectarCategoriaPorTexto($texto);
        }

        if (!$slugDetectado || $slugDetectado === 'NINGUNA') {
            return $this->detectarCategoriaPorTexto($texto);
        }

        $slugsValidos = array_column($categorias, 'slug');
        $slugDetectado = trim(strtolower($slugDetectado));

        return in_array($slugDetectado, $slugsValidos, true) ? $slugDetectado : $this->detectarCategoriaPorTexto($texto);
    }

    private function llamarGemini(string $prompt): ?string {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent";

        $payload = json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 20
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . GEMINI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Error de conexión con Gemini: " . $curlError);
        }
        if ($httpCode !== 200) {
            throw new Exception("Gemini respondió con código HTTP " . $httpCode);
        }

        $data = json_decode($response, true);
        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return $texto ? trim($texto) : null;
    }
}