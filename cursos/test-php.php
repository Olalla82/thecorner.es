<?php
echo "✅ PHP está funcionando correctamente<br>";
echo "Versión PHP: " . phpversion() . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Archivo cache.php existe: " . (file_exists(__DIR__ . '/inc/cache.php') ? 'SÍ' : 'NO') . "<br>";

if (file_exists(__DIR__ . '/inc/cache.php')) {
    try {
        require_once __DIR__ . '/inc/cache.php';
        echo "✅ cache.php cargado correctamente<br>";
        
        $cache = cache();
        echo "✅ Función cache() funciona<br>";
        
        $stats = $cache->getStats();
        echo "✅ getStats() funciona<br>";
        echo "<pre>";
        print_r($stats);
        echo "</pre>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>
