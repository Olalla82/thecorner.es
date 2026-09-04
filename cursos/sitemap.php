<?php

require_once __DIR__ . '/inc/common.php';

header('Content-Type: application/xml; charset=UTF-8');

function xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function sitemap_course_name(array $row): string
{
    $subsidyType = strtoupper(trim((string) ($row['tipus_subvencionada'] ?? '')));

    if ($subsidyType === 'FOAP' && !empty($row['nombre_modulo'])) {
        $moduleName = preg_replace('/\d+\/FOAP\/\d+\/\d+\/\d+/', '', (string) $row['nombre_modulo']);
        $moduleName = trim((string) $moduleName);

        if ($moduleName !== '') {
            return $moduleName;
        }
    }

    foreach (['nom_comercial', 'nom', 'nom_curs', 'curso_nom_comercial'] as $field) {
        $value = trim((string) ($row[$field] ?? ''));

        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function sitemap_course_slug(string $courseName, ?string $startDate, array &$usedSlugs, array $monthsCa): string
{
    $baseSlug = slugify($courseName);

    if ($baseSlug === '') {
        return '';
    }

    if (!isset($usedSlugs[$baseSlug])) {
        $usedSlugs[$baseSlug] = true;
        return $baseSlug;
    }

    $monthNumber = $startDate ? (int) date('n', strtotime($startDate)) : 0;
    $monthSlug = $monthsCa[$monthNumber] ?? '';
    $candidateSlug = $monthSlug !== '' ? $baseSlug . '-' . slugify($monthSlug) : $baseSlug;

    if ($candidateSlug === $baseSlug || isset($usedSlugs[$candidateSlug])) {
        $suffix = 2;
        do {
            $candidateSlug = $baseSlug . '-' . $suffix;
            $suffix++;
        } while (isset($usedSlugs[$candidateSlug]));
    }

    $usedSlugs[$candidateSlug] = true;
    return $candidateSlug;
}

function add_url(array &$urls, string $loc, string $changefreq, string $priority, string $lastmod, array $alternates = []): void
{
    $urls[] = [
        'loc' => $loc,
        'changefreq' => $changefreq,
        'priority' => $priority,
        'lastmod' => $lastmod,
        'alternates' => $alternates,
    ];
}

function add_course_urls(array &$urls, string $baseUrl, string $slug, string $lastmod): void
{
    $courseEs = $baseUrl . '/' . $slug;
    $courseCa = $baseUrl . '/ca/' . $slug;
    $courseAlternates = [
        ['hreflang' => 'es', 'href' => $courseEs],
        ['hreflang' => 'ca', 'href' => $courseCa],
        ['hreflang' => 'x-default', 'href' => $courseEs],
    ];

    add_url($urls, $courseEs, 'weekly', '0.8', $lastmod, $courseAlternates);
    add_url($urls, $courseCa, 'weekly', '0.8', $lastmod, $courseAlternates);
}

$siteRoot = rtrim(SITE_URL, '/');
if (str_ends_with($siteRoot, '/cursos')) {
    $siteRoot = substr($siteRoot, 0, -7);
}

$baseUrl = $siteRoot . '/cursos';
$today = date('Y-m-d');
$urls = [];

$listEs = $baseUrl . '/cursos-gratuitos';
$listCa = $baseUrl . '/cursos-gratuitos-ca';
$listAlternates = [
    ['hreflang' => 'es', 'href' => $listEs],
    ['hreflang' => 'ca', 'href' => $listCa],
    ['hreflang' => 'x-default', 'href' => $listEs],
];

add_url($urls, $listEs, 'daily', '1.0', $today, $listAlternates);
add_url($urls, $listCa, 'daily', '1.0', $today, $listAlternates);

$db = conectar_bbdd();
$items = [];

$publishedGroupsQuery = "
    SELECT
        c.id AS id_curs,
        c.nom_comercial AS curso_nom_comercial,
        TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) AS nom_curs,
        c.tipus_subvencionada,
        g.id AS id_grup,
        g.nom_comercial,
        g.nom,
        g.nom_soc,
        g.data_inici,
        g.data_final,
        m.modul AS nombre_modulo,
        'grupo' AS tipo_entrada
    FROM gen_cursos c
    INNER JOIN gen_grups g ON g.curs = c.id
    LEFT JOIN soc_moduls m ON m.id = g.nom_soc
    WHERE c.tipus_subvencionada IN ('FOAP', 'CONSORCI')
      AND g.mostrar_web = 1
      AND (
          (c.tipus_subvencionada = 'CONSORCI' AND g.data_final >= CURDATE())
          OR
          (c.tipus_subvencionada = 'FOAP' AND (g.data_final >= CURDATE() OR g.data_final IS NULL))
      )
";

$publishedGroupsResult = mysqli_query($db, $publishedGroupsQuery);
if ($publishedGroupsResult) {
    while ($row = mysqli_fetch_assoc($publishedGroupsResult)) {
        $items[] = $row;
    }
}

$parentCoursesQuery = "
    SELECT
        c.id AS id_curs,
        c.nom_comercial AS curso_nom_comercial,
        TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) AS nom_curs,
        c.tipus_subvencionada,
        NULL AS id_grup,
        NULL AS nom_comercial,
        NULL AS nom,
        NULL AS nom_soc,
        c.data_inici,
        c.data_fi AS data_final,
        NULL AS nombre_modulo,
        'curso' AS tipo_entrada
    FROM gen_cursos c
    WHERE c.tipus_subvencionada IN ('FOAP', 'CONSORCI')
      AND c.mostrar_web = 1
      AND (
          (c.tipus_subvencionada = 'CONSORCI' AND c.data_fi >= CURDATE())
          OR
          (c.tipus_subvencionada = 'FOAP' AND (c.data_fi >= CURDATE() OR c.data_fi IS NULL))
      )
      AND NOT EXISTS (
          SELECT 1
          FROM gen_grups g
          WHERE g.curs = c.id
            AND g.mostrar_web = 1
            AND (
                (c.tipus_subvencionada = 'CONSORCI' AND g.data_final >= CURDATE())
                OR
                (c.tipus_subvencionada = 'FOAP' AND (g.data_final >= CURDATE() OR g.data_final IS NULL))
            )
      )
";

$parentCoursesResult = mysqli_query($db, $parentCoursesQuery);
if ($parentCoursesResult) {
    while ($row = mysqli_fetch_assoc($parentCoursesResult)) {
        $items[] = $row;
    }
}

usort($items, static function (array $a, array $b): int {
    return strcmp((string) ($a['data_inici'] ?? ''), (string) ($b['data_inici'] ?? ''));
});

$usedSlugs = [];
$monthsCa = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

foreach ($items as $row) {
    $courseName = sitemap_course_name($row);

    if ($courseName === '') {
        continue;
    }

    if (($row['tipo_entrada'] ?? '') === 'curso' && strtoupper((string) ($row['tipus_subvencionada'] ?? '')) === 'FOAP') {
        $courseName .= ' - Curs complet';
    }

    $startDate = !empty($row['data_inici']) ? substr((string) $row['data_inici'], 0, 10) : null;
    $slug = sitemap_course_slug($courseName, $startDate, $usedSlugs, $monthsCa);

    if ($slug === '') {
        continue;
    }

    add_course_urls($urls, $baseUrl, $slug, $startDate ?: $today);
}

mysqli_close($db);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";

foreach ($urls as $item) {
    echo "  <url>\n";
    echo '    <loc>' . xml_escape($item['loc']) . "</loc>\n";

    if (!empty($item['alternates']) && is_array($item['alternates'])) {
        foreach ($item['alternates'] as $alternate) {
            if (empty($alternate['hreflang']) || empty($alternate['href'])) {
                continue;
            }

            echo '    <xhtml:link rel="alternate" hreflang="' . xml_escape((string) $alternate['hreflang'])
                . '" href="' . xml_escape((string) $alternate['href']) . '" />' . "\n";
        }
    }

    echo '    <lastmod>' . xml_escape($item['lastmod']) . "</lastmod>\n";
    echo '    <changefreq>' . xml_escape($item['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . xml_escape($item['priority']) . "</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
