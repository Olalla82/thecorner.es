<?php

if (!function_exists('send_utf8_html_header')) {
    function send_utf8_html_header(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach (headers_list() as $headerLine) {
            if (stripos($headerLine, 'Content-Type:') === 0) {
                return;
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
    }
}

send_utf8_html_header();

function load_env_file(string $filePath): void
{
    if (!is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if ($key === '') {
            continue;
        }

        if (($value[0] ?? '') === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

function env_value(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

load_env_file(__DIR__ . '/../.env');

if (!defined('DB_HOST')) {
    define('DB_HOST', env_value('DB_HOST', 'vps02.evotic.es'));
}
if (!defined('DB_USER')) {
    define('DB_USER', env_value('DB_USER', 'tc_app_dbuser'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', env_value('DB_PASS', 'hya_4H76'));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', env_value('DB_NAME', 'thecorner_app'));
}
if (!defined('SITE_URL')) {
    define('SITE_URL', env_value('SITE_URL', 'https://thecorner.es'));
}
if (!defined('GOOGLE_API_KEY')) {
    define('GOOGLE_API_KEY', env_value('GOOGLE_API_KEY', 'TU_API_KEY_AQUI'));
}
if (!defined('GOOGLE_PLACE_ID')) {
    define('GOOGLE_PLACE_ID', env_value('GOOGLE_PLACE_ID', 'TU_PLACE_ID_AQUI'));
}

function conectar_bbdd(): mysqli
{
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$db) {
        die('Error conexión BBDD');
    }

    mysqli_set_charset($db, 'utf8mb4');

    return $db;
}

function date_from_bdd(string $date): string
{
    // Manejar fechas vacías o inválidas
    if (empty($date) || strlen($date) < 8) {
        return 'A consultar';
    }
    $parts = explode('-', $date);
    if (count($parts) !== 3) {
        return 'A consultar';
    }
    [$anyo, $mes, $dia] = $parts;
    return str_pad($dia, 2, '0', STR_PAD_LEFT) . '/' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $anyo;
}

function slugify($text): string
{
    if ($text === null) {
        return '';
    }

    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }

    $text = mb_strtolower($text, 'UTF-8');

    $replacements = [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
        'ñ' => 'n', 'ç' => 'c'
    ];

    $text = strtr($text, $replacements);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    return trim((string) $text, '-');
}

function mes_inici_locale(string $date, string $locale = 'es'): string
{
    $monthNum = substr($date, 5, 2);

    $monthsEs = [
        '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
        '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
        '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
    ];

    $monthsCa = [
        '01' => 'gener', '02' => 'febrer', '03' => 'març', '04' => 'abril',
        '05' => 'maig', '06' => 'juny', '07' => 'juliol', '08' => 'agost',
        '09' => 'setembre', '10' => 'octubre', '11' => 'novembre', '12' => 'desembre'
    ];

    $map = $locale === 'ca' ? $monthsCa : $monthsEs;

    return $map[$monthNum] ?? '';
}

function default_google_reviews(string $locale = 'es'): array
{
    if ($locale === 'ca') {
        return [
            'reviews' => [
                [
                    'author_name' => 'Africa Reina',
                    'rating' => 5,
                    'text' => 'Referent al centre súper atents, implicats, organitzats i implicats amb els alumnes. Referent al curs, complet, tècnic, amè, organitzat i amb un gran dossier documental....',
                    'time' => strtotime('2024-11-19'),
                    'profile_photo_url' => ''
                ],
                [
                    'author_name' => 'María López',
                    'rating' => 5,
                    'text' => 'Excel·lent centre de formació! Els professors són molt professionals i les instal·lacions perfectes. Totalment recomanable per a qualsevol curs.',
                    'time' => strtotime('2024-10-05'),
                    'profile_photo_url' => ''
                ],
                [
                    'author_name' => 'Jordi Martínez',
                    'rating' => 5,
                    'text' => 'Molt content amb el curs realitzat. L\'atenció és molt personalitzada i el seguiment durant tot el procés excel·lent. Repetiré sens dubte!',
                    'time' => strtotime('2024-09-12'),
                    'profile_photo_url' => ''
                ]
            ],
            'rating' => 4.8,
            'user_ratings_total' => 167
        ];
    }

    return [
        'reviews' => [
            [
                'author_name' => 'Africa Reina',
                'rating' => 5,
                'text' => 'Referente al centro súper atentos, implicados, organizados e implicados con los alumnos. Referente al curso, completo, técnico, ameno, organizado y con un gran dossier documental....',
                'time' => strtotime('2024-11-19'),
                'profile_photo_url' => ''
            ],
            [
                'author_name' => 'María López',
                'rating' => 5,
                'text' => 'Excel·lent centre de formació! Els professors són molt professionals i les instal·lacions perfectes. Totalment recomanable per a qualsevol curs.',
                'time' => strtotime('2024-10-05'),
                'profile_photo_url' => ''
            ],
            [
                'author_name' => 'Jordi Martínez',
                'rating' => 5,
                'text' => 'Molt content amb el curs realitzat. L\'atenció és molt personalitzada i el seguiment durant tot el procés excel·lent. Repetiré sens dubte!',
                'time' => strtotime('2024-09-12'),
                'profile_photo_url' => ''
            ]
        ],
        'rating' => 4.8,
        'user_ratings_total' => 167
    ];
}

function obtener_google_reviews(string $locale = 'es'): array
{
    if (GOOGLE_API_KEY === 'TU_API_KEY_AQUI' || GOOGLE_PLACE_ID === 'TU_PLACE_ID_AQUI') {
        return default_google_reviews($locale);
    }

    $cache_file = sys_get_temp_dir() . '/google_reviews_cache_' . $locale . '.json';
    $cache_duration = 86400;

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        $cached_data = file_get_contents($cache_file);
        $decoded = json_decode((string) $cached_data, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    $url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . urlencode(GOOGLE_PLACE_ID)
        . '&fields=reviews,rating,user_ratings_total&key=' . GOOGLE_API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $data = json_decode((string) $response, true);
        if (isset($data['result'])) {
            $result = [
                'reviews' => $data['result']['reviews'] ?? [],
                'rating' => $data['result']['rating'] ?? 0,
                'user_ratings_total' => $data['result']['user_ratings_total'] ?? 0,
            ];

            file_put_contents($cache_file, json_encode($result));
            return $result;
        }
    }

    return [
        'reviews' => [],
        'rating' => 4.8,
        'user_ratings_total' => 167,
    ];
}

/**
 * Traduce las abreviaturas de días a formato completo según el idioma
 * 
 * @param string $dies_abrev Días en formato abreviado (ej: "Dll a Dv", "Dll Presencial, Dm Online")
 * @param string $lang Idioma: 'es' (español) o 'ca' (catalán)
 * @return string Días traducidos con primera letra en mayúscula
 */
function traducir_dies(string $dies_abrev, string $lang = 'es'): string
{
    if (empty($dies_abrev)) {
        return '';
    }
    
    // Mapeo de abreviaturas
    $traducciones = [
        'es' => [
            'Dll' => 'lunes',
            'Dm' => 'martes',
            'Dx' => 'miércoles',
            'Dj' => 'jueves',
            'Dv' => 'viernes',
            'Ds' => 'sábado',
            'Dg' => 'domingo'
        ],
        'ca' => [
            'Dll' => 'dilluns',
            'Dm' => 'dimarts',
            'Dx' => 'dimecres',
            'Dj' => 'dijous',
            'Dv' => 'divendres',
            'Ds' => 'dissabte',
            'Dg' => 'diumenge'
        ]
    ];
    
    $mapa = $traducciones[$lang] ?? $traducciones['es'];
    
    // Traducir cada abreviatura
    $resultado = $dies_abrev;
    foreach ($mapa as $abrev => $nombre_completo) {
        $resultado = str_replace($abrev, $nombre_completo, $resultado);
    }
    
    // Capitalizar primera letra
    $resultado = ucfirst($resultado);
    
    return $resultado;
}
