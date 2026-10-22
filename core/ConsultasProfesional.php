<?php
/** Consulta base de profesionales con plan y calificación promedio. */
class ConsultasProfesional {
    public static function base(): string {
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
}