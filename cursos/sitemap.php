<?php
require_once __DIR__ . '/inc/common.php';

header('Content-Type: application/xml; charset=UTF-8');

function xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

$siteRoot = rtrim(SITE_URL, '/');
if (str_ends_with($siteRoot, '/cursos')) {
    $siteRoot = substr($siteRoot, 0, -7);
}
$baseUrl = $siteRoot . '/cursos';
$today = date('Y-m-d');
$urls = [];

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

$query = "
    SELECT DISTINCT
        c.id,
        c.nom_comercial,
        TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) AS nom_curs,
        c.data_inici
    FROM gen_cursos c
    WHERE c.tipus_subvencionada IN ('FOAP', 'CONSORCI')
      AND c.mostrar_web = 1
      AND EXISTS (
          SELECT 1
          FROM gen_grups g
          WHERE g.curs = c.id
            AND g.data_inici >= CURDATE()
      )
    ORDER BY c.data_inici ASC
";

$result = mysqli_query($db, $query);
$usedSlugs = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nombreCurso = !empty(trim((string) $row['nom_curs']))
            ? trim((string) $row['nom_curs'])
            : trim((string) $row['nom_comercial']);

        if ($nombreCurso === '') {
            continue;
        }

        $slug = slugify($nombreCurso);
        if ($slug === '' || isset($usedSlugs[$slug])) {
            continue;
        }

        $usedSlugs[$slug] = true;
        $lastmod = !empty($row['data_inici']) ? substr((string) $row['data_inici'], 0, 10) : $today;

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
