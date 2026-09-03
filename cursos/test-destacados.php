<?php
require_once __DIR__ . '/inc/common.php';
$enlace = conectar_bbdd();

echo "<h1>Diagnóstico de Cursos Destacados</h1>";
echo "<style>body{font-family:Arial;margin:20px} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ddd;padding:8px;text-align:left} th{background:#4CAF50;color:white} .fail{background:#ffdddd} .ok{background:#ddffdd}</style>";

// 1. Ver todos los grupos marcados como destacados
echo "<h2>1. Grupos marcados como destacados (curs_destacat = 1)</h2>";
$query1 = "
SELECT 
    g.id as id_grup,
    g.curs as id_curs,
    c.nom_comercial,
    g.curs_destacat,
    g.mostrar_web as grup_mostrar_web,
    c.mostrar_web as curs_mostrar_web,
    g.data_inici,
    g.data_final,
    DATEDIFF(g.data_inici, CURDATE()) as dias_hasta_inicio,
    DATEDIFF(g.data_final, CURDATE()) as dias_hasta_final,
    c.tipus_subvencionada
FROM gen_grups g
INNER JOIN gen_cursos c ON c.id = g.curs
WHERE g.curs_destacat = 1
ORDER BY g.data_inici ASC
";

$result1 = mysqli_query($enlace, $query1);
echo "<table>";
echo "<tr><th>ID Grupo</th><th>ID Curso</th><th>Nombre</th><th>Destacado</th><th>Grup Visible</th><th>Curso Visible</th><th>Fecha Inicio</th><th>Fecha Final</th><th>Días hasta inicio</th><th>Días hasta final</th><th>Tipo</th><th>¿Por qué no sale?</th></tr>";

while ($row = mysqli_fetch_assoc($result1)) {
    $problemas = [];
    $clase = "ok";
    
    if ($row['grup_mostrar_web'] != 1) {
        $problemas[] = "Grupo NO visible";
        $clase = "fail";
    }
    if ($row['curs_mostrar_web'] != 1) {
        $problemas[] = "Curso NO visible";
        $clase = "fail";
    }
    if ($row['dias_hasta_final'] < 0) {
        $problemas[] = "Ya finalizó";
        $clase = "fail";
    }
    if ($row['dias_hasta_inicio'] < -30) {
        $problemas[] = "Inició hace más de 30 días";
        $clase = "fail";
    }
    
    $motivo = empty($problemas) ? "✅ Debería aparecer" : implode(", ", $problemas);
    
    echo "<tr class='$clase'>";
    echo "<td>{$row['id_grup']}</td>";
    echo "<td>{$row['id_curs']}</td>";
    echo "<td>{$row['nom_comercial']}</td>";
    echo "<td>{$row['curs_destacat']}</td>";
    echo "<td>{$row['grup_mostrar_web']}</td>";
    echo "<td>{$row['curs_mostrar_web']}</td>";
    echo "<td>{$row['data_inici']}</td>";
    echo "<td>{$row['data_final']}</td>";
    echo "<td>{$row['dias_hasta_inicio']}</td>";
    echo "<td>{$row['dias_hasta_final']}</td>";
    echo "<td>{$row['tipus_subvencionada']}</td>";
    echo "<td><strong>$motivo</strong></td>";
    echo "</tr>";
}
echo "</table>";

// 2. Ejecutar la query exacta de la web (ESPAÑOL)
echo "<h2>2. Query EXACTA IMPLEMENTADA en cursos-gratuitos.php (ESPAÑOL)</h2>";
$query2 = "
  SELECT DISTINCT
    c.id as id_curs,
    c.nom_comercial,
    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,
    g.curs_destacat AS destacat,
    g.id as id_grup,
    g.data_inici,
    g.data_final,
    c.tipus_subvencionada,
    c.especialitat_formativa,
    g.modalitat,
    saf.consorci_soc
  FROM gen_cursos c
  INNER JOIN gen_grups g ON g.curs = c.id
  LEFT JOIN soc_accions_formatives saf ON saf.id = g.soc_accio_formativa
  WHERE g.curs_destacat = 1
    AND g.data_final >= CURDATE()
    AND (
      (saf.consorci_soc = 1 AND g.mostrar_web = 1) OR
      (COALESCE(saf.consorci_soc, 0) != 1 AND (c.mostrar_web = 1 OR g.mostrar_web = 1))
    )
  ORDER BY g.data_inici ASC
  LIMIT 3
";

$result2 = mysqli_query($enlace, $query2);
$count = mysqli_num_rows($result2);
echo "<p><strong>Resultados: $count cursos</strong></p>";

if ($count == 0) {
    echo "<p style='color:red;font-weight:bold'>❌ NO SE ENCONTRARON CURSOS DESTACADOS con la query implementada</p>";
    echo "<p>Verifica que los grupos tengan soc_accio_formativa asignado correctamente.</p>";
} else {
    echo "<p style='color:green;font-weight:bold'>✅ Cursos encontrados con la query implementada</p>";
    echo "<table>";
    echo "<tr><th>ID Grupo</th><th>ID Curso</th><th>Nombre</th><th>Tipo</th><th>consorci_soc</th><th>Fecha Inicio</th><th>Fecha Final</th></tr>";
    while ($row = mysqli_fetch_assoc($result2)) {
        echo "<tr class='ok'>";
        echo "<td>{$row['id_grup']}</td>";
        echo "<td>{$row['id_curs']}</td>";
        echo "<td>{$row['nom_curs']}</td>";
        echo "<td>{$row['tipus_subvencionada']}</td>";
        echo "<td>" . ($row['consorci_soc'] ?? 'NULL') . "</td>";
        echo "<td>{$row['data_inici']}</td>";
        echo "<td>{$row['data_final']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Query MEJORADA (sin filtro de 30 días)
echo "<h2>3. Query MEJORADA - IMPLEMENTADA (lógica correcta CONSORCI/FOAP)</h2>";
$query3 = "
  SELECT DISTINCT
    c.id as id_curs,
    c.nom_comercial,
    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,
    g.curs_destacat AS destacat,
    g.id as id_grup,
    g.data_inici,
    g.data_final,
    c.tipus_subvencionada,
    c.especialitat_formativa,
    g.modalitat,
    saf.consorci_soc
  FROM gen_cursos c
  INNER JOIN gen_grups g ON g.curs = c.id
  LEFT JOIN soc_accions_formatives saf ON saf.id = g.soc_accio_formativa
  WHERE g.curs_destacat = 1
    AND g.data_final >= CURDATE()
    AND (
      (saf.consorci_soc = 1 AND g.mostrar_web = 1) OR
      (COALESCE(saf.consorci_soc, 0) != 1 AND (c.mostrar_web = 1 OR g.mostrar_web = 1))
    )
  ORDER BY g.data_inici ASC
  LIMIT 3
";

$result3 = mysqli_query($enlace, $query3);
$count3 = mysqli_num_rows($result3);
echo "<p><strong>Resultados: $count3 cursos</strong></p>";

if ($count3 > 0) {
    echo "<p style='color:green;font-weight:bold'>✅ Con la query mejorada SÍ encontramos cursos destacados</p>";
    echo "<table>";
    echo "<tr><th>ID Grupo</th><th>ID Curso</th><th>Nombre</th><th>Tipo</th><th>consorci_soc</th><th>Fecha Inicio</th><th>Fecha Final</th></tr>";
    while ($row = mysqli_fetch_assoc($result3)) {
        echo "<tr class='ok'>";
        echo "<td>{$row['id_grup']}</td>";
        echo "<td>{$row['id_curs']}</td>";
        echo "<td>{$row['nom_curs']}</td>";
        echo "<td>{$row['tipus_subvencionada']}</td>";
        echo "<td>" . ($row['consorci_soc'] ?? 'NULL') . "</td>";
        echo "<td>{$row['data_inici']}</td>";
        echo "<td>{$row['data_final']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;font-weight:bold'>❌ Aún con la query mejorada NO se encuentran cursos</p>";
    echo "<p>Esto significa que hay un problema con los datos o la configuración del JOIN.</p>";
}

// 3.5 Diagnóstico del JOIN
echo "<h2>3.5. Verificación de JOIN a soc_accions_formatives</h2>";
echo "<p>Verificando si los grupos destacados tienen soc_accio_formativa asignado:</p>";

$query_join = "
SELECT 
    g.id as id_grup,
    c.id as id_curs,
    c.nom_comercial,
    g.curs_destacat,
    g.mostrar_web,
    g.data_final,
    g.soc_accio_formativa,
    saf.id as saf_id,
    saf.consorci_soc,
    c.tipus_subvencionada
FROM gen_grups g
INNER JOIN gen_cursos c ON c.id = g.curs
LEFT JOIN soc_accions_formatives saf ON saf.id = g.soc_accio_formativa
WHERE g.curs_destacat = 1
ORDER BY g.id
";

$result_join = mysqli_query($enlace, $query_join);
echo "<table>";
echo "<tr><th>ID Grupo</th><th>Nombre</th><th>g.soc_accio_formativa</th><th>saf.id</th><th>saf.consorci_soc</th><th>Tipo</th><th>¿JOIN OK?</th></tr>";

while ($row = mysqli_fetch_assoc($result_join)) {
    $join_ok = !empty($row['saf_id']) ? '✅ SÍ' : '❌ NO';
    $clase = !empty($row['saf_id']) ? 'ok' : 'fail';
    
    echo "<tr class='$clase'>";
    echo "<td>{$row['id_grup']}</td>";
    echo "<td>" . htmlspecialchars($row['nom_comercial']) . "</td>";
    echo "<td>" . ($row['soc_accio_formativa'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['saf_id'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['consorci_soc'] ?? 'NULL') . "</td>";
    echo "<td>{$row['tipus_subvencionada']}</td>";
    echo "<td><strong>$join_ok</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<div style='background:#e7f3ff;padding:15px;margin-top:15px;border-left:4px solid #2196F3'>";
echo "<p><strong>💡 IMPORTANTE:</strong></p>";
echo "<p>Si <code>saf.id</code> es NULL, significa que <code>g.soc_accio_formativa</code> no apunta a un registro válido en <code>soc_accions_formatives</code>.</p>";
echo "<p>Para cursos CONSORCI, necesitan tener <code>g.soc_accio_formativa</code> asignado y <code>saf.consorci_soc = 1</code> para aparecer en destacados.</p>";
echo "</div>";

// 4. Recomendación
echo "<h2>4. Diagnóstico y Solución</h2>";
echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107'>";
echo "<h3>Problema identificado:</h3>";
echo "<p>La query actual usa el filtro: <code>g.data_inici >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)</code></p>";
echo "<p>Esto significa que <strong>SOLO</strong> muestra cursos que empiecen en los últimos 30 días o en el futuro.</p>";
echo "<p>Si un curso ya empezó hace más de 30 días, aunque esté destacado y visible, NO aparecerá.</p>";
echo "<h3>Solución:</h3>";
echo "<p>Cambiar el filtro a: <code>g.data_final >= CURDATE()</code></p>";
echo "<p>Esto mostrará todos los cursos destacados que aún no hayan finalizado, sin importar cuándo empezaron.</p>";
echo "</div>";

mysqli_close($enlace);
?>
