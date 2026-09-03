<?php
// Evitar caché del navegador en esta página de ejemplos
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

/**
 * EJEMPLO DE INTEGRACIÓN DEL SISTEMA DE CACHÉ
 * 
 * Este archivo muestra cómo integrar el sistema de caché
 * en las páginas existentes del proyecto.
 * 
 * COPIAR Y ADAPTAR A CADA PÁGINA
 */

// ============================================
// 1. INCLUIR EL SISTEMA DE CACHÉ
// ============================================
require_once __DIR__ . '/inc/cache.php';
require_once __DIR__ . '/inc/common.php';

// ============================================
// 2. EJEMPLO: PÁGINA CURSOS-GRATUITOS.PHP
// ============================================

/*
ANTES (sin caché):
------------------
$query = "SELECT * FROM gen_cursos WHERE mostrar_web = 1";
$result = mysqli_query($db, $query);
$cursos = mysqli_fetch_all($result, MYSQLI_ASSOC);


DESPUÉS (con caché):
--------------------
$query = "SELECT * FROM gen_cursos WHERE mostrar_web = 1";
$cursos = cache()->sql($query, function() use ($db, $query) {
    $result = mysqli_query($db, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
});
*/

// ============================================
// 3. EJEMPLO: CACHEAR FRAGMENTOS HTML
// ============================================

/*
ANTES (sin caché):
------------------
<div class="listado-cursos">
    <?php foreach ($cursos as $curso): ?>
        <div class="curso-card">
            <h3><?= $curso['nombre'] ?></h3>
        </div>
    <?php endforeach; ?>
</div>


DESPUÉS (con caché):
--------------------
<?php
echo cache()->html("listado_cursos_home", function() use ($cursos) {
    ?>
    <div class="listado-cursos">
        <?php foreach ($cursos as $curso): ?>
            <div class="curso-card">
                <h3><?= $curso['nombre'] ?></h3>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
});
?>
*/

// ============================================
// 4. EJEMPLO COMPLETO: CURSO.PHP
// ============================================

/*
// Al inicio del archivo
require_once __DIR__ . '/inc/cache.php';

// Cachear consulta del curso
$query = "SELECT * FROM gen_cursos WHERE slug = '$slug'";
$curso = cache()->sql($query, function() use ($db, $query) {
    $result = mysqli_query($db, $query);
    return mysqli_fetch_assoc($result);
});

// Cachear consulta de grupos
$query_grupos = "SELECT * FROM gen_grups WHERE curs = {$curso['id']} AND mostrar_web = 1";
$grupos = cache()->sql($query_grupos, function() use ($db, $query_grupos) {
    $result = mysqli_query($db, $query_grupos);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
});

// Cachear HTML del contenido del curso
echo cache()->html("curso_contenido_{$curso['id']}", function() use ($curso) {
    ?>
    <div class="curso-detalle">
        <h1><?= htmlspecialchars($curso['nombre']) ?></h1>
        <div class="descripcion">
            <?= $curso['descripcion'] ?>
        </div>
    </div>
    <?php
});
*/

// ============================================
// 5. INVALIDAR CACHÉ CUANDO SE ACTUALIZA
// ============================================

/*
// En el panel de administración, cuando se actualiza un curso:
function actualizarCurso($id) {
    global $db;
    
    // Actualizar en BD
    mysqli_query($db, "UPDATE gen_cursos SET ... WHERE id = $id");
    
    // Invalidar caché relacionado
    cache()->flush('sql'); // Limpiar todas las queries SQL
    cache()->flushPattern('html', "curso_$id"); // Limpiar HTML del curso
    
    return true;
}

// O limpiar todo el caché después de cambios importantes:
cache()->flush();
*/

// ============================================
// 6. ESTRATEGIAS DE CACHÉ POR PÁGINA
// ============================================

/*
CURSOS-GRATUITOS.PHP:
- Cachear query principal de cursos (5 min)
- Cachear listado HTML completo (5 min)
- Invalidar al actualizar/añadir cursos

CURSO.PHP:
- Cachear query del curso específico (5 min)
- Cachear query de grupos del curso (5 min)
- Cachear HTML del contenido (5 min)
- Invalidar al actualizar el curso específico

SITEMAP.PHP:
- Cachear query de todos los cursos (10 min)
- Cachear XML generado (10 min)
- Invalidar al añadir/modificar cursos
*/

// ============================================
// 7. TESTING Y VERIFICACIÓN
// ============================================

/*
1. Implementar caché en una página
2. Abrir: http://localhost/cursos/cache-admin.php
3. Verificar que hit rate aumenta con cada recarga
4. Objetivo: Hit rate > 60% después de varias visitas

ANTES:
- Primera carga: 500ms
- Segunda carga: 500ms
- Hit rate: 0%

DESPUÉS:
- Primera carga: 500ms (genera caché)
- Segunda carga: 50ms (desde caché)
- Hit rate: 90%
- Mejora: 90% más rápido
*/

// ============================================
// 8. PLAN DE IMPLEMENTACIÓN SUGERIDO
// ============================================

/*
DÍA 1: Páginas de listado
- cursos-gratuitos.php
- cursos-gratuitos-ca.php

DÍA 2: Páginas de detalle
- curso.php
- curso-ca.php

DÍA 3: Otros archivos
- sitemap.php
- sitemap-index.php

DÍA 4: Optimización
- Monitorear hit rate
- Ajustar TTL según necesidad
- Configurar limpieza automática
*/

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejemplo de Integración de Caché</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .example {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        .example h3 {
            color: #667eea;
            margin-top: 0;
        }
        pre {
            background: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .highlight {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
        <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner" style="height: 60px; width: auto;">
        <h1 style="margin: 0;">📚 Guía de Integración del Sistema de Caché</h1>
    </div>
    
    <div class="highlight">
        <strong>⚡ Este archivo contiene ejemplos de código para integrar el caché en tus páginas.</strong><br>
        Lee el código fuente PHP de este archivo para ver todos los ejemplos.
    </div>

    <div class="example">
        <h3>🚀 Enlaces Rápidos</h3>
        <a href="cache-admin.php" class="btn">Panel de Administración</a>
        <a href="cache-test.php" class="btn">Ejecutar Tests</a>
        <a href="README_CACHE.md" class="btn">Documentación Completa</a>
    </div>

    <div class="example">
        <h3>📖 Ejemplo 1: Caché SQL</h3>
        <pre><?php
echo htmlspecialchars('<?php
$query = "SELECT * FROM gen_cursos WHERE mostrar_web = 1";
$cursos = cache()->sql($query, function() use ($db, $query) {
    $result = mysqli_query($db, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
});
?>');
        ?></pre>
    </div>

    <div class="example">
        <h3>🎨 Ejemplo 2: Caché HTML</h3>
        <pre><?php
echo htmlspecialchars('<?php
echo cache()->html("listado_cursos", function() use ($cursos) {
    foreach ($cursos as $curso) {
        echo "<div class=\'curso\'>{$curso[\'nombre\']}</div>";
    }
});
?>');
        ?></pre>
    </div>

    <div class="example">
        <h3>📦 Ejemplo 3: Caché de Objetos</h3>
        <pre><?php
echo htmlspecialchars('<?php
$estadisticas = cache()->object("stats_mensuales", function() {
    // Procesamiento pesado aquí
    return calcularEstadisticas();
}, 3600); // 1 hora
?>');
        ?></pre>
    </div>

    <div class="example">
        <h3>🧹 Ejemplo 4: Limpiar Caché</h3>
        <pre><?php
echo htmlspecialchars('<?php
// Limpiar todo
cache()->flush();

// Limpiar solo SQL
cache()->flush("sql");

// Limpiar por patrón
cache()->flushPattern("html", "curso_123");
?>');
        ?></pre>
    </div>

    <div class="highlight">
        <strong>💡 Próximos Pasos:</strong>
        <ol>
            <li>Ejecuta los tests: <code>cache-test.php</code></li>
            <li>Abre el panel: <code>cache-admin.php</code></li>
            <li>Lee la documentación: <code>README_CACHE.md</code></li>
            <li>Implementa en tu primera página</li>
            <li>Monitorea el hit rate</li>
        </ol>
    </div>

</body>
</html>
