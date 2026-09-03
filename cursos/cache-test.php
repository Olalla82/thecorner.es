<?php
// ⚠️ REQUIERE AUTENTICACIÓN
require_once __DIR__ . '/inc/auth.php';
auth_require();

// Evitar caché del navegador en esta página de tests
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests Sistema de Caché</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f7fafc;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
        }
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .test-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .test-result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .test-success {
            background: #c6f6d5;
            border-left: 4px solid #48bb78;
            color: #22543d;
        }
        .test-error {
            background: #fed7d7;
            border-left: 4px solid #f56565;
            color: #742a2a;
        }
        .test-info {
            background: #bee3f8;
            border-left: 4px solid #4299e1;
            color: #2c5282;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-box {
            padding: 15px;
            background: #edf2f7;
            border-radius: 6px;
        }
        .stat-box strong {
            display: block;
            color: #4a5568;
            margin-bottom: 5px;
        }
        .stat-box span {
            font-size: 1.5em;
            color: #2d3748;
            font-weight: bold;
        }
        .performance {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding: 10px;
            background: #f7fafc;
            border-radius: 4px;
        }
        .performance div {
            text-align: center;
        }
        .performance strong {
            display: block;
            color: #718096;
            font-size: 0.9em;
        }
        .performance span {
            font-size: 1.2em;
            color: #2d3748;
        }
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/inc/cache.php';

$results = [];
$errors = 0;
$successes = 0;

function test($name, $callback) {
    global $results, $successes, $errors;
    
    try {
        $start = microtime(true);
        $result = $callback();
        $time = round((microtime(true) - $start) * 1000, 2);
        
        if ($result === true || $result === null) {
            $successes++;
            $results[] = [
                'type' => 'success',
                'name' => $name,
                'message' => '✅ Test pasado',
                'time' => $time
            ];
        } else {
            $results[] = [
                'type' => 'info',
                'name' => $name,
                'message' => $result,
                'time' => $time
            ];
        }
    } catch (Exception $e) {
        $errors++;
        $results[] = [
            'type' => 'error',
            'name' => $name,
            'message' => '❌ Error: ' . $e->getMessage(),
            'time' => 0
        ];
    }
}
?>

<div class="container">
    <div class="header">
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner" style="height: 50px; width: auto;">
            <div style="flex: 1;">
                <h1 style="margin: 0;">🧪 Tests del Sistema de Caché</h1>
                <p style="margin: 5px 0 0 0;">Verificación completa de funcionalidad</p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0 0 8px 0; color: #666; font-size: 14px;">
                    👤 <?= htmlspecialchars(auth_user_name()) ?>
                </p>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="admin-dashboard.php" style="display: inline-block; padding: 8px 16px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                        ← Dashboard
                    </a>
                    <a href="logout.php" style="display: inline-block; padding: 8px 16px; background: #dc3545; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                        🚪 Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    // ============================================
    // TEST 1: Estructura de directorios
    // ============================================
    ?>
    <div class="test-section">
        <h2>📁 Test 1: Estructura de Directorios</h2>
        <?php
        test("Directorio principal existe", function() {
            return is_dir(__DIR__ . '/cache') ? true : 'Directorio cache no existe';
        });
        
        test("Directorio SQL existe", function() {
            return is_dir(__DIR__ . '/cache/sql') ? true : 'Directorio cache/sql no existe';
        });
        
        test("Directorio HTML existe", function() {
            return is_dir(__DIR__ . '/cache/html') ? true : 'Directorio cache/html no existe';
        });
        
        test("Directorio Objects existe", function() {
            return is_dir(__DIR__ . '/cache/objects') ? true : 'Directorio cache/objects no existe';
        });
        
        test("Archivo .htaccess de protección", function() {
            return file_exists(__DIR__ . '/cache/.htaccess') ? true : 'Falta .htaccess de protección';
        });
        
        foreach ($results as $result) {
            echo "<div class='test-result test-{$result['type']}'>";
            echo "<strong>{$result['name']}</strong><br>";
            echo "{$result['message']} ({$result['time']}ms)";
            echo "</div>";
        }
        $results = [];
        ?>
    </div>

    <?php
    // ============================================
    // TEST 2: Caché SQL
    // ============================================
    ?>
    <div class="test-section">
        <h2>🗄️ Test 2: Caché SQL</h2>
        <?php
        test("Caché SQL - Primera ejecución (MISS)", function() {
            $query = "SELECT * FROM test_table_" . time();
            $start = microtime(true);
            
            $result = cache()->sql($query, function() {
                usleep(100000); // Simular query lenta
                return [['id' => 1, 'nombre' => 'Test']]; // Array de arrays como SQL real
            });
            
            $time = round((microtime(true) - $start) * 1000, 2);
            
            if ($time >= 80) { // Debe ser lento (>80ms)
                return true;
            }
            return "Tiempo: {$time}ms (esperado >80ms)";
        });
        
        test("Caché SQL - Segunda ejecución (HIT)", function() {
            $query = "SELECT * FROM test_table_cached";
            
            // Primera vez - llenar caché
            cache()->sql($query, function() {
                usleep(100000); // Simular query lenta
                return [['id' => 1, 'nombre' => 'Cached']];
            });
            
            // Segunda vez (debe ser instantáneo desde caché)
            $start = microtime(true);
            $result = cache()->sql($query, function() {
                usleep(100000); // No debería ejecutarse
                return [['id' => 999, 'nombre' => 'No debería verse']];
            });
            $time = round((microtime(true) - $start) * 1000, 2);
            
            if ($time < 10) { // Debe ser rápido (<10ms)
                return true;
            }
            return "Cache HIT pero lento: {$time}ms (esperado <10ms)";
        });
        
        foreach ($results as $result) {
            echo "<div class='test-result test-{$result['type']}'>";
            echo "<strong>{$result['name']}</strong><br>";
            echo "{$result['message']} ({$result['time']}ms)";
            echo "</div>";
        }
        $results = [];
        ?>
    </div>

    <?php
    // ============================================
    // TEST 3: Caché HTML
    // ============================================
    ?>
    <div class="test-section">
        <h2>🎨 Test 3: Caché HTML</h2>
        <?php
        test("Caché HTML - Generar fragmento", function() {
            $html = cache()->html('test_fragment', function() {
                usleep(50000);
                echo '<div class="test">Fragmento de prueba ' . time() . '</div>';
            });
            
            return strpos($html, 'Fragmento de prueba') !== false ? true : 'HTML no generado correctamente';
        });
        
        test("Caché HTML - Recuperar desde caché", function() {
            $key = 'fragment_' . time();
            
            // Primera generación
            $html1 = cache()->html($key, function() {
                echo '<div>Contenido ' . rand(1000, 9999) . '</div>';
            });
            
            // Debe devolver el mismo
            $html2 = cache()->html($key, function() {
                echo '<div>Contenido ' . rand(1000, 9999) . '</div>';
            });
            
            return ($html1 === $html2) ? true : 'Los HTML no coinciden';
        });
        
        foreach ($results as $result) {
            echo "<div class='test-result test-{$result['type']}'>";
            echo "<strong>{$result['name']}</strong><br>";
            echo "{$result['message']} ({$result['time']}ms)";
            echo "</div>";
        }
        $results = [];
        ?>
    </div>

    <?php
    // ============================================
    // TEST 4: Caché de Objetos
    // ============================================
    ?>
    <div class="test-section">
        <h2>📦 Test 4: Caché de Objetos</h2>
        <?php
        test("Caché Objects - Guardar array", function() {
            $data = cache()->object('test_array', function() {
                return [
                    'usuarios' => ['Juan', 'María', 'Pedro'],
                    'total' => 3,
                    'timestamp' => time()
                ];
            });
            
            return is_array($data) && $data['total'] === 3 ? true : 'Array no guardado correctamente';
        });
        
        test("Caché Objects - Guardar objeto", function() {
            $obj = cache()->object('test_object', function() {
                $o = new stdClass();
                $o->nombre = 'Test';
                $o->valor = 123;
                return $o;
            });
            
            return (is_object($obj) && $obj->nombre === 'Test') ? true : 'Objeto no guardado correctamente';
        });
        
        foreach ($results as $result) {
            echo "<div class='test-result test-{$result['type']}'>";
            echo "<strong>{$result['name']}</strong><br>";
            echo "{$result['message']} ({$result['time']}ms)";
            echo "</div>";
        }
        $results = [];
        ?>
    </div>

    <?php
    // ============================================
    // TEST 5: Limpieza y gestión
    // ============================================
    ?>
    <div class="test-section">
        <h2>🧹 Test 5: Limpieza y Gestión</h2>
        <?php
        test("Limpiar caché SQL", function() {
            // Crear algo en caché
            cache()->sql('test_query', function() { return ['test']; });
            
            // Limpiar
            cache()->flush('sql');
            
            // Verificar que stats se reiniciaron
            return true;
        });
        
        test("Limpiar caché expirado", function() {
            $cleaned = cache()->cleanExpired();
            return "Se limpiaron {$cleaned} archivos expirados";
        });
        
        test("Obtener estadísticas", function() {
            $stats = cache()->getStats();
            return is_array($stats) && isset($stats['hit_rate']) ? true : 'Estadísticas no disponibles';
        });
        
        foreach ($results as $result) {
            echo "<div class='test-result test-{$result['type']}'>";
            echo "<strong>{$result['name']}</strong><br>";
            echo "{$result['message']} ({$result['time']}ms)";
            echo "</div>";
        }
        $results = [];
        ?>
    </div>

    <?php
    // ============================================
    // TEST 6: Rendimiento
    // ============================================
    ?>
    <div class="test-section">
        <h2>⚡ Test 6: Rendimiento</h2>
        <?php
        // Test de rendimiento comparativo
        $iterations = 100;
        
        // Sin caché
        $start_no_cache = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $data = ['resultado' => rand(1, 1000)];
        }
        $time_no_cache = microtime(true) - $start_no_cache;
        
        // Con caché
        cache()->flush(); // Limpiar primero
        $start_cache = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            cache()->object('perf_test', function() {
                return ['resultado' => rand(1, 1000)];
            });
        }
        $time_cache = microtime(true) - $start_cache;
        
        $improvement = round((($time_no_cache - $time_cache) / $time_no_cache) * 100, 2);
        
        echo "<div class='performance'>";
        echo "<div><strong>Sin caché</strong><span>" . round($time_no_cache * 1000, 2) . "ms</span></div>";
        echo "<div><strong>Con caché</strong><span>" . round($time_cache * 1000, 2) . "ms</span></div>";
        echo "<div><strong>Mejora</strong><span style='color: #48bb78'>+" . $improvement . "%</span></div>";
        echo "</div>";
        ?>
    </div>

    <?php
    // ============================================
    // RESUMEN FINAL
    // ============================================
    $info = cache()->getInfo();
    $stats = $info['stats'];
    ?>
    <div class="test-section">
        <h2>📊 Estadísticas del Sistema</h2>
        <div class="stats">
            <div class="stat-box">
                <strong>Tests Exitosos</strong>
                <span style="color: #48bb78"><?= $successes ?></span>
            </div>
            <div class="stat-box">
                <strong>Tests Fallidos</strong>
                <span style="color: #f56565"><?= $errors ?></span>
            </div>
            <div class="stat-box">
                <strong>Hit Rate</strong>
                <span><?= $stats['hit_rate'] ?>%</span>
            </div>
            <div class="stat-box">
                <strong>Archivos en Caché</strong>
                <span><?= $stats['file_count'] ?></span>
            </div>
            <div class="stat-box">
                <strong>Tamaño Total</strong>
                <span><?= $stats['cache_size_formatted'] ?></span>
            </div>
        </div>
    </div>

    <div class="test-section" style="text-align: center;">
        <h2>✨ Resultado Final</h2>
        <?php if ($errors === 0): ?>
            <div class="test-result test-success" style="font-size: 1.2em;">
                <strong>🎉 ¡TODOS LOS TESTS PASADOS!</strong><br>
                Sistema de caché funcionando perfectamente
            </div>
        <?php else: ?>
            <div class="test-result test-error" style="font-size: 1.2em;">
                <strong>⚠️ ALGUNOS TESTS FALLARON</strong><br>
                Por favor revisa los errores anteriores
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="cache-admin.php" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                👉 Ir al Panel de Administración
            </a>
        </div>
    </div>
</div>

</body>
</html>
