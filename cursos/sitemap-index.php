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

$sitemaps = [
    [
        'loc' => $baseUrl . '/sitemap.xml',
        'lastmod' => $today,
    ],
];

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

foreach ($sitemaps as $item) {
    echo "  <sitemap>\n";
    echo '    <loc>' . xml_escape($item['loc']) . "</loc>\n";
    echo '    <lastmod>' . xml_escape($item['lastmod']) . "</lastmod>\n";
    echo "  </sitemap>\n";
}

echo "</sitemapindex>\n";
