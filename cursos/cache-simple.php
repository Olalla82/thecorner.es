<?php
// Evitar caché del navegador en esta página de test
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Simple</title>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
        <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner" style="height: 50px; width: auto;">
        <div>
            <h1 style="margin: 0;">Test PHP Funcionando</h1>
            <p style="margin: 5px 0 0 0;">Si ves esto, PHP funciona correctamente</p>
        </div>
    </div>
    
    <?php
    echo "<p><strong>Versión PHP:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Hora actual:</strong> " . date('Y-m-d H:i:s') . "</p>";
    
    $cache_exists = file_exists(__DIR__ . '/inc/cache.php');
    echo "<p><strong>Archivo cache.php existe:</strong> " . ($cache_exists ? '✅ SÍ' : '❌ NO') . "</p>";
    
    if ($cache_exists) {
        echo "<p>Intentando cargar cache.php...</p>";
        try {
            require_once __DIR__ . '/inc/cache.php';
            echo "<p>✅ cache.php cargado exitosamente</p>";
            
            echo "<p>Probando función cache()...</p>";
            $c = cache();
            echo "<p>✅ Función cache() funciona</p>";
            
            echo "<p>Obteniendo estadísticas...</p>";
            $stats = $c->getStats();
            echo "<p>✅ getStats() funciona</p>";
            
            echo "<h2>Estadísticas:</h2>";
            echo "<ul>";
            echo "<li>Hits: " . $stats['hits'] . "</li>";
            echo "<li>Misses: " . $stats['misses'] . "</li>";
            echo "<li>Hit Rate: " . $stats['hit_rate'] . "%</li>";
            echo "<li>Tamaño: " . $stats['cache_size_formatted'] . "</li>";
            echo "<li>Archivos: " . $stats['file_count'] . "</li>";
            echo "</ul>";
            
            echo "<h2>✅ SISTEMA DE CACHÉ FUNCIONAL</h2>";
            echo '<p><a href="cache-admin.php">Ir al Panel de Administración →</a></p>';
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    }
    ?>
    
    <hr>
    <p><small>Archivo: <?= __FILE__ ?></small></p>
</body>
</html>
