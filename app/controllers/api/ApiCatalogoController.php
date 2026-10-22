<?php
/**
 * Endpoints PÚBLICOS (sin token) para el catálogo.
 *   GET /api/catalogo/categorias
 *   GET /api/catalogo/profesionales?categoria=&macrodistrito=&preferir_macro=&disponibles=1&pagina=1
 *   GET /api/catalogo/profesional/{id}
 */
class ApiCatalogoController {
    private PDO $db;

    private const MACROS = [
        'ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES',
        'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO'
    ];
    private const POR_PAGINA = 20;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ---------------- 1. CATEGORÍAS ---------------- */
    public function categorias() {
        try {
            $sql = "
                SELECT c.id_categoria, c.nombre_categoria, c.slug, c.descripcion,
                       c.tipo_clasificacion, c.icono_fa,
                       (SELECT COUNT(*)
                          FROM profesionales p
                          INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
                         WHERE p.id_categoria = c.id_categoria
                           AND p.estado_validacion = 'APROBADO'
                           AND u.estado = 'ACTIVO') AS total_profesionales
                  FROM categorias c
                 WHERE c.estado = 1
                 ORDER BY c.nombre_categoria ASC
            ";
            $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $this->responder(['ok' => true, 'categorias' => $rows]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ---------------- 2. PROFESIONALES (cascada por plan) ---------------- */
    public function profesionales() {
        try {
            $where  = ["p.estado_validacion = 'APROBADO'", "u.estado = 'ACTIVO'"];
            $params = [];
                        // Quien consulta no se ve a sí mismo (un profesional no puede contratarse)
            $yo = ApiAuth::opcional($this->db);
            if ($yo) {
                $where[] = 'p.id_usuario <> :yo';
                $params[':yo'] = (int) $yo['id_usuario'];
            }

            $idCategoria = (int) ($_GET['categoria'] ?? 0);
            if ($idCategoria > 0) {
                $where[] = 'p.id_categoria = :cat';
                $params[':cat'] = $idCategoria;
            }

            $macro = strtoupper(trim($_GET['macrodistrito'] ?? ''));
            if ($macro !== '' && in_array($macro, self::MACROS, true)) {
                $where[] = 'p.macrodistrito_base = :macro';
                $params[':macro'] = $macro;
            }

                     if (($_GET['disponibles'] ?? '') === '1') {
                // Disponible y con señal reciente (los de la web no tienen latido: se aceptan)
                $where[] = "p.estado_disponibilidad = 'DISPONIBLE'
                            AND (p.ultimo_latido_app IS NULL
                                 OR p.ultimo_latido_app >= DATE_SUB(NOW(), INTERVAL 10 MINUTE))";
            }

            $ordenZona = '';
            $pref = strtoupper(trim($_GET['preferir_macro'] ?? ''));
            if ($pref !== '' && in_array($pref, self::MACROS, true)) {
                $ordenZona = '(p.macrodistrito_base = :pref) DESC,';
                $params[':pref'] = $pref;
            }

            $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
            $offset = ($pagina - 1) * self::POR_PAGINA;

            $sql = $this->sqlBase()
                 . ' WHERE ' . implode(' AND ', $where)
                 . " ORDER BY pl.posicionamiento_destacado DESC,
                              pl.precio DESC,
                              $ordenZona
                              promedio_estrellas DESC,
                              FIELD(p.estado_disponibilidad, 'DISPONIBLE', 'OCUPADO', 'NO_DISPONIBLE'),
                              p.id_profesional ASC
                     LIMIT :lim OFFSET :off";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', self::POR_PAGINA, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $this->responder([
                'ok' => true,
                'pagina' => $pagina,
                'profesionales' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ---------------- 3. PERFIL PÚBLICO + OPINIONES ---------------- */
    public function profesional($id = 0) {
        try {
            $id = (int) $id;
            if ($id <= 0) {
                $this->responder(['ok' => false, 'error' => 'Profesional no válido.'], 400);
            }

            $sql = $this->sqlBase()
                 . " WHERE p.id_profesional = :id
                       AND p.estado_validacion = 'APROBADO'
                       AND u.estado = 'ACTIVO'
                     LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$perfil) {
                $this->responder(['ok' => false, 'error' => 'No encontramos a este profesional.'], 404);
            }

            $stmtR = $this->db->prepare("
                SELECT cal.puntuacion_general, cal.puntualidad, cal.calidad_trabajo,
                       cal.comentario, cal.fecha_calificacion
                  FROM calificaciones cal
                  INNER JOIN solicitudes_servicio s ON s.id_solicitud = cal.id_solicitud
                 WHERE s.id_profesional = :id
                 ORDER BY cal.fecha_calificacion DESC
                 LIMIT 10
            ");
            $stmtR->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtR->execute();

            $this->responder([
                'ok' => true,
                'profesional' => $perfil,
                'resenas' => $stmtR->fetchAll(PDO::FETCH_ASSOC)
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ---------------- AUXILIARES ---------------- */
    private function sqlBase(): string {
        return "
            SELECT p.id_profesional, u.nombre, u.apellido, u.foto_perfil,
                   c.id_categoria, c.nombre_categoria,
                   p.tipo_prestador, p.experiencia_anios, p.descripcion_servicio,
                   p.macrodistrito_base, p.zona_especifica, p.tarifa_base,
                   p.estado_disponibilidad,
                   pl.posicionamiento_destacado, pl.nombre_plan,
                   pl.precio AS precio_plan,
                   YEAR(p.fecha_registro) AS anio_registro,
                   COALESCE(r.promedio, 5.00) AS promedio_estrellas,
                   COALESCE(r.total, 0)       AS total_calificaciones
              FROM profesionales p
              INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
              INNER JOIN planes_suscripcion pl ON pl.id_plan = p.id_plan
              LEFT JOIN (
                    SELECT s.id_profesional,
                           ROUND(AVG(cal.puntuacion_general), 2) AS promedio,
                           COUNT(*) AS total
                      FROM calificaciones cal
                      INNER JOIN solicitudes_servicio s ON s.id_solicitud = cal.id_solicitud
                     GROUP BY s.id_profesional
              ) r ON r.id_profesional = p.id_profesional
        ";
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiCatalogo] ' . $e->getMessage());
        $this->responder([
            'ok' => false,
            'error' => 'No pudimos cargar la información. Intenta de nuevo.'
        ], 500);
    }
}