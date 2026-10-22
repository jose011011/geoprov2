<?php
class SiteController extends Controller
{
    public function index()
    {
        $categorias = [];
        $planes     = [];
        try {
            $categorias = (new ClientePanel())->categoriasActivas();
            $planes     = (new ProfesionalPanel())->planes();
        } catch (Throwable $e) {
            error_log('[FixGeo] landing: ' . $e->getMessage());   // la página se muestra igual, sin esas secciones
        }

        $this->view('site/landing', [
            'titulo'     => 'FixGeo | Técnicos verificados en La Paz y El Alto',
            'categorias' => $categorias,
            'planes'     => $planes,
            'panelUrl'   => isset($_SESSION['user_id']) ? BASE_URL . $this->inicioPorRol() : null,
        ]);
    }
}