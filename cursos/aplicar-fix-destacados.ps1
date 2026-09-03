##############################################
# Script para corregir queries de destacados
##############################################

Write-Host "================================================" -ForegroundColor Cyan
Write-Host " CORRECCIÓN DE QUERIES DE CURSOS DESTACADOS" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# ARCHIVO 1: cursos-gratuitos.php (ESPAÑOL)
$file1 = 'c:\xampp\htdocs\cursos\cursos-gratuitos.php'
Write-Host "📄 Procesando: $file1" -ForegroundColor Yellow

# Backup
Copy-Item $file1 "$file1.bak.$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Force
Write-Host "   ✓ Backup creado" -ForegroundColor Gray

# Leer contenido
$content1 = Get-Content $file1 -Raw -Encoding UTF8

# Patrón a buscar (usando regex para flexibilidad en espacios)
$pattern1 = [regex]::Escape('// Restaurar lógica antigua de cursos destacados') + 
            '[\s\S]*?' +
            [regex]::Escape('$query_destacados = "') +
            '[\s\S]*?' +
            [regex]::Escape('WHERE g.curs_destacat = 1') +
            '[\s\S]*?' +
            [regex]::Escape('AND c.mostrar_web = 1') +
            '[\s\S]*?' +
            [regex]::Escape('AND g.data_inici >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)') +
            '[\s\S]*?' +
            [regex]::Escape('LIMIT 3') +
            '\s*' +
            [regex]::Escape('";')

# Replacement
$replacement1 = @'
// Cursos destacados: usar misma lógica que listado normal (CONSORCI solo g.mostrar_web, FOAP c.mostrar_web O g.mostrar_web)
$query_destacados = "
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
'@

$newContent1 = $content1 -replace $pattern1, $replacement1

if ($newContent1 -ne $content1) {
    Set-Content $file1 -Value $newContent1 -NoNewline -Encoding UTF8
    Write-Host "   ✅ cursos-gratuitos.php ACTUALIZADO" -ForegroundColor Green
} else {
    Write-Host "   ❌ No se pudo aplicar el cambio (patrón no encontrado)" -ForegroundColor Red
}

Write-Host ""

# ARCHIVO 2: cursos-gratuitos-ca.php (CATALÁN)
$file2 = 'c:\xampp\htdocs\cursos\cursos-gratuitos-ca.php'
Write-Host "📄 Procesando: $file2" -ForegroundColor Yellow

# Backup
Copy-Item $file2 "$file2.bak.$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Force
Write-Host "   ✓ Backup creado" -ForegroundColor Gray

# Leer contenido
$content2 = Get-Content $file2 -Raw -Encoding UTF8

# Patrón a buscar
$pattern2 = [regex]::Escape('// Query base per a cursos destacats (FOAP i CONSORCI)') +
            '[\s\S]*?' +
            [regex]::Escape('$query_destacados = "') +
            '[\s\S]*?' +
            [regex]::Escape('WHERE c.tipus_subvencionada IN') +
            '[\s\S]*?' +
            [regex]::Escape('AND c.mostrar_web = 1') +
            '[\s\S]*?' +
            [regex]::Escape('AND g.data_inici >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)') +
            '[\s\S]*?' +
            [regex]::Escape('LIMIT 3') +
            '\s*' +
            [regex]::Escape('";')

# Replacement
$replacement2 = @'
// Cursos destacats: usar mateixa lògica que llistat normal (CONSORCI només g.mostrar_web, FOAP c.mostrar_web O g.mostrar_web)
$query_destacados = "
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
    WHERE c.tipus_subvencionada IN ('FOAP', 'CONSORCI')
    AND g.curs_destacat = 1
    AND g.data_final >= CURDATE()
    AND (
      (saf.consorci_soc = 1 AND g.mostrar_web = 1) OR
      (COALESCE(saf.consorci_soc, 0) != 1 AND (c.mostrar_web = 1 OR g.mostrar_web = 1))
    )
    ORDER BY g.data_inici ASC
    LIMIT 3
";
'@

$newContent2 = $content2 -replace $pattern2, $replacement2

if ($newContent2 -ne $content2) {
    Set-Content $file2 -Value $newContent2 -NoNewline -Encoding UTF8
    Write-Host "   ✅ cursos-gratuitos-ca.php ACTUALIZADO" -ForegroundColor Green
} else {
    Write-Host "   ❌ No se pudo aplicar el cambio (patrón no encontrado)" -ForegroundColor Red
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host " SIGUIENTE PASO: Limpiar caché" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Accede a: http://localhost/cursos/cache-admin.php" -ForegroundColor Yellow
Write-Host "Y pulsa 'Limpiar Todo'" -ForegroundColor Yellow
Write-Host ""
