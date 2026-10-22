<?php
/**
 * FixGeo - Íconos disponibles para las categorías, con palabras clave para sugerir el ícono según el nombre.
 * Formato: [clase Font Awesome (solid), nombre en español, palabras clave sin tildes (una sola palabra cada una)].
 * Las más específicas van primero: se usa la primera que coincida.
 */
final class IconosServicio
{
    public const POR_DEFECTO = 'fa-screwdriver-wrench';

    public const LISTA = [
        ['fa-bolt',               'Electricidad',            'electric luz energia cable corriente tablero cortocircuito'],
        ['fa-plug',               'Enchufes',                'enchuf toma'],
        ['fa-lightbulb',          'Iluminación',             'foco lampara ilumin luminaria'],
        ['fa-solar-panel',        'Panel solar',             'solar fotovolt'],
        ['fa-battery-full',       'Baterías',                'bateria'],
        ['fa-tv',                 'Televisores',             'televis tele electronic electrodomest'],
        ['fa-computer',           'Computadoras',            'computad pc ordenador'],
        ['fa-laptop',             'Laptops',                 'laptop notebook portatil'],
        ['fa-mobile-screen',      'Celulares',               'celular movil telefono smartphone'],
        ['fa-wifi',               'Internet y Wi-Fi',        'wifi internet router'],
        ['fa-network-wired',      'Redes',                   'redes cableado'],
        ['fa-video',              'Cámaras',                 'camara cctv vigilancia'],
        ['fa-fan',                'Ventilación',             'ventilador extractor ventila'],
        ['fa-snowflake',          'Refrigeración',           'refriger frio congelador heladera nevera acondicionado'],
        ['fa-faucet-drip',        'Plomería',                'plomer gasfit fuga tuberia'],
        ['fa-faucet',             'Grifería',                'griferia grifo lavamanos pileta'],
        ['fa-droplet',            'Agua',                    'agua tanque cisterna'],
        ['fa-shower',             'Duchas y calefones',      'ducha calefon terma'],
        ['fa-toilet',             'Baños',                   'bano inodoro sanitario'],
        ['fa-bath',               'Bañeras',                 'banera tina'],
        ['fa-fire-flame-curved',  'Gas y calefacción',       'calefa calentador estufa'],
        ['fa-wrench',             'Mecánica',                'mecanic taller'],
        ['fa-car',                'Autos',                   'auto carro vehicul'],
        ['fa-motorcycle',         'Motos',                   'moto motocicleta'],
        ['fa-hammer',             'Carpintería',             'carpint martill ebanist madera'],
        ['fa-helmet-safety',      'Construcción',            'construc obra contratista'],
        ['fa-trowel-bricks',      'Albañilería',             'alban ladrill muro cemento'],
        ['fa-trowel',             'Revoque y yeso',          'revoque estuco yeso'],
        ['fa-paint-roller',       'Pintura',                 'pintur pintor pintar'],
        ['fa-paintbrush',         'Barnizado',               'brocha barniz'],
        ['fa-palette',            'Decoración',              'decorac interior'],
        ['fa-ruler-combined',     'Planos y medidas',        'plano arquitect topograf medicion'],
        ['fa-border-all',         'Cerámica y pisos',        'ceramic azulej piso porcelan'],
        ['fa-window-maximize',    'Ventanas y vidrios',      'ventana vidri cristal aluminio'],
        ['fa-door-open',          'Puertas',                 'puerta'],
        ['fa-key',                'Cerrajería',              'cerraj chapa duplicad'],
        ['fa-lock',               'Seguridad',               'candado alarma'],
        ['fa-house-chimney',      'Techos',                  'techo teja calamina cubierta'],
        ['fa-baby-carriage',      'Niñeras',                 'ninera ninos carriage'],
        ['fa-baby',               'Bebés',                   'bebe infan guarderia'],
        ['fa-child',              'Niños',                   'nino menor tutor'],
        ['fa-person-cane',        'Adultos mayores',         'anciano mayores abuel geriatr'],
        ['fa-hand-holding-heart', 'Cuidado de personas',     'cuidador enfermer acompan'],
        ['fa-heart-pulse',        'Salud',                   'salud fisioterap masaje'],
        ['fa-broom',              'Limpieza',                'limpie aseo barrer'],
        ['fa-spray-can-sparkles', 'Desinfección',            'desinfec sanitiz'],
        ['fa-soap',               'Lavado',                  'lavad jabon lavander'],
        ['fa-shirt',              'Ropa y costura',          'ropa costur sastre modista tintoreria'],
        ['fa-bug-slash',          'Fumigación',              'fumig plaga'],
        ['fa-utensils',           'Cocina',                  'cocin chef catering comida'],
        ['fa-cake-candles',       'Eventos',                 'event fiesta pastel repostero'],
        ['fa-couch',              'Muebles',                 'sofa tapiz mueble'],
        ['fa-bed',                'Colchones',               'colchon cama'],
        ['fa-leaf',               'Jardinería',              'jardin pasto cesped poda'],
        ['fa-seedling',           'Plantas',                 'vivero siembra planta'],
        ['fa-tree',               'Árboles',                 'arbol tala'],
        ['fa-truck-moving',       'Mudanzas',                'mudanza flete'],
        ['fa-truck',              'Transporte',              'camion carga transporte'],
        ['fa-box',                'Embalaje',                'embal paquet'],
        ['fa-camera',             'Fotografía',              'foto fotograf'],
        ['fa-print',              'Impresoras',              'impresora fotocop'],
        ['fa-graduation-cap',     'Clases',                  'clase profesor tutoria reforzamiento'],
        ['fa-music',              'Música',                  'music sonido'],
        ['fa-paw',                'Mascotas',                'mascota perro gato veterin'],
        ['fa-scissors',           'Peluquería',              'peluquer corte barber'],
        ['fa-spa',                'Belleza',                 'belleza estetic manicur'],
        ['fa-fire-extinguisher',  'Extintores',              'incendio extintor'],
        ['fa-warehouse',          'Almacén',                 'almacen deposito'],
        ['fa-industry',           'Industria y soldadura',   'fabrica industria soldad'],
        ['fa-gears',              'Maquinaria',              'maquinari'],
        ['fa-toolbox',            'Herramientas',            'herramient multiservic mantenimiento'],
        ['fa-building',           'Edificios',               'edificio departamento condominio'],
        ['fa-house',              'Hogar',                   'hogar vivienda domestic'],
        ['fa-screwdriver-wrench', 'Reparaciones generales',  'tecnico general'],
        ['fa-screwdriver',        'Armado y montaje',        'armado montaje'],
        ['fa-gear',               'Otros servicios',         'otro'],
    ];

    private static function sinTildes(string $s): string
    {
        return strtr(mb_strtolower($s, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    /** Ícono sugerido (con "fa-solid") según el nombre de la categoría. */
    public static function sugerir(string $nombre): string
    {
        $t = self::sinTildes($nombre);
        foreach (self::LISTA as [$clase, , $claves]) {
            foreach (explode(' ', $claves) as $k) {
                if ($k !== '' && str_contains($t, $k)) {
                    return 'fa-solid ' . $clase;
                }
            }
        }
        return 'fa-solid ' . self::POR_DEFECTO;
    }

    /** ¿El ícono (con o sin "fa-solid") es uno de la lista? */
    public static function existe(string $valor): bool
    {
        $partes = preg_split('/\s+/', trim($valor));
        $clase  = end($partes);
        foreach (self::LISTA as $i) {
            if ($i[0] === $clase) {
                return true;
            }
        }
        return false;
    }

    /** Siempre devuelve "fa-solid fa-xxx". */
    public static function completo(string $valor): string
    {
        $partes = preg_split('/\s+/', trim($valor));
        return 'fa-solid ' . (end($partes) ?: self::POR_DEFECTO);
    }
}