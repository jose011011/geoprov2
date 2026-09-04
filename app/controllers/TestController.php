<?php
class TestController extends Controller {

    public function conexion() {
        try {
            // Obtenemos la instancia PDO de la base de datos
            $db = Database::getInstance()->getConnection();
            
            // Consultamos las categorías y roles de marketplace_servicios_lp_v2
            $stmtCategorias = $db->query("SELECT nombre_categoria, slug, tipo_clasificacion FROM categorias");
            $categorias = $stmtCategorias->fetchAll();

            $stmtRoles = $db->query("SELECT nombre_rol FROM roles");
            $roles = $stmtRoles->fetchAll();

            $stmtPlanes = $db->query("SELECT nombre_plan, precio_mensual FROM planes_suscripcion");
            $planes = $stmtPlanes->fetchAll();

            // Renderizado de estado del sistema
            echo "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <title>GEO-PRO | Verificación de Conexión</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f0f2f5; padding: 30px; margin: 0; }
                    .card { background: #ffffff; padding: 30px; border-radius: 12px; max-width: 680px; margin: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
                    .status-ok { color: #0f5132; background-color: #d1e7dd; padding: 12px; border-radius: 6px; font-weight: bold; border-left: 5px solid #198754; }
                    .badge { background: #0d6efd; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; }
                    h2 { margin-top: 0; color: #1c1e21; }
                    h4 { margin-bottom: 8px; color: #333; }
                    ul { list-style: none; padding-left: 0; line-height: 1.8; }
                    li { padding: 4px 0; border-bottom: 1px solid #eee; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <h2>🚀 GEO-PRO V2 — Test de Conexión</h2>
                    <div class='status-ok'>✓ Conexión establecida con éxito a MySQL (marketplace_servicios_lp_v2)</div>
                    
                    <h4>Roles del Sistema (RBAC):</h4>
                    <ul>";
                    foreach ($roles as $r) {
                        echo "<li><span class='badge'>Rol</span> " . htmlspecialchars($r['nombre_rol']) . "</li>";
                    }
            echo "  </ul>

                    <h4>Categorías Iniciales:</h4>
                    <ul>";
                    foreach ($categorias as $c) {
                        echo "<li><strong>" . htmlspecialchars($c['nombre_categoria']) . "</strong> — <em>" . htmlspecialchars($c['tipo_clasificacion']) . "</em> (<code>" . htmlspecialchars($c['slug']) . "</code>)</li>";
                    }
            echo "  </ul>

                    <h4>Planes de Membresía:</h4>
                    <ul>";
                    foreach ($planes as $p) {
                        echo "<li><strong>" . htmlspecialchars($p['nombre_plan']) . "</strong>: Bs. " . htmlspecialchars($p['precio_mensual']) . "/mes</li>";
                    }
            echo "  </ul>
                </div>
            </body>
            </html>";

        } catch (Exception $e) {
            echo "
            <div style='background:#f8d7da; color:#842029; padding:20px; border-radius:8px; max-width:600px; margin:40px auto; font-family:sans-serif;'>
                <h3>❌ Error al conectar a la Base de Datos</h3>
                <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            </div>";
        }
    }
}