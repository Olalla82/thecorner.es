<?php
// ⚠️ REQUIERE AUTENTICACIÓN
require_once __DIR__ . '/inc/auth.php';
auth_require();

// Evitar caché del navegador en esta página de administración
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
    <title>Panel de Administración de Caché</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e1545 0%, #2a1d5f 50%, #1e1545 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1e1545;
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .header p {
            color: #666;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #C2D500;
            font-size: 0.9em;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .stat-card .value {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
        }
        .stat-card .label {
            color: #999;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .progress-bar {
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #C2D500, #a3c62e);
            transition: width 0.3s;
        }
        .actions {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .actions h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #C2D500, #a3c62e);
            color: #1e1545;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(194, 213, 0, 0.4);
        }
        .btn-danger {
            background: #f56565;
            color: white;
        }
        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-2px);
        }
        .btn-warning {
            background: #ed8936;
            color: white;
        }
        .btn-warning:hover {
            background: #dd6b20;
            transform: translateY(-2px);
        }
        .details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .details h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
            font-weight: 600;
        }
        .detail-value {
            color: #333;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border: 1px solid #90cdf4;
        }
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
// Verificar que existe el sistema de caché
$cache_file = __DIR__ . '/inc/cache.php';
if (!file_exists($cache_file)) {
    ?>
    <div class="container">
        <div class="alert alert-error">
            <strong>❌ Error:</strong> No se encuentra el archivo inc/cache.php
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

try {
    require_once $cache_file;
} catch (Exception $e) {
    ?>
    <div class="container">
        <div class="alert alert-error">
            <strong>❌ Error al cargar caché:</strong> <?= htmlspecialchars($e->getMessage()) ?>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Procesar acciones
$message = '';
$message_type = '';

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'flush_all':
            cache()->flush();
            $message = '✅ Caché completo limpiado correctamente';
            $message_type = 'success';
            break;
        case 'flush_sql':
            cache()->flush('sql');
            $message = '✅ Caché SQL limpiado correctamente';
            $message_type = 'success';
            break;
        case 'flush_html':
            cache()->flush('html');
            $message = '✅ Caché HTML limpiado correctamente';
            $message_type = 'success';
            break;
        case 'flush_objects':
            cache()->flush('objects');
            $message = '✅ Caché de objetos limpiado correctamente';
            $message_type = 'success';
            break;
        case 'clean_expired':
            $cleaned = cache()->cleanExpired();
            $message = "✅ Se limpiaron $cleaned archivos expirados";
            $message_type = 'success';
            break;
    }
}

// Obtener información
$info = cache()->getInfo();
$stats = $info['stats'];
?>

<div class="container">
    <div class="header">
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner" style="height: 50px; width: auto;">
            <div style="flex: 1;">
                <h1 style="margin: 0;">🚀 Panel de Administración de Caché</h1>
                <p style="margin: 5px 0 0 0;">Sistema de caché multi-nivel potente</p>
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

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Hit Rate</h3>
            <div class="value"><?= $stats['hit_rate'] ?>%</div>
            <div class="label">Eficiencia del caché</div>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: <?= $stats['hit_rate'] ?>%"></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>Total Hits</h3>
            <div class="value"><?= number_format($stats['hits']) ?></div>
            <div class="label">Aciertos de caché</div>
        </div>

        <div class="stat-card">
            <h3>Total Misses</h3>
            <div class="value"><?= number_format($stats['misses']) ?></div>
            <div class="label">Fallos de caché</div>
        </div>

        <div class="stat-card">
            <h3>Tamaño Total</h3>
            <div class="value"><?= $stats['cache_size_formatted'] ?></div>
            <div class="label"><?= number_format($stats['file_count']) ?> archivos</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>📊 SQL Cache</h3>
            <div class="value"><?= number_format($info['types']['sql']['count']) ?></div>
            <div class="label">
                <?= $info['types']['sql']['size_formatted'] ?><br>
                Hits: <?= number_format($stats['hits_sql']) ?> | 
                Misses: <?= number_format($stats['misses_sql']) ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>🎨 HTML Cache</h3>
            <div class="value"><?= number_format($info['types']['html']['count']) ?></div>
            <div class="label">
                <?= $info['types']['html']['size_formatted'] ?><br>
                Hits: <?= number_format($stats['hits_html']) ?> | 
                Misses: <?= number_format($stats['misses_html']) ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>📦 Objects Cache</h3>
            <div class="value"><?= number_format($info['types']['objects']['count']) ?></div>
            <div class="label">
                <?= $info['types']['objects']['size_formatted'] ?><br>
                Hits: <?= number_format($stats['hits_objects']) ?> | 
                Misses: <?= number_format($stats['misses_objects']) ?>
            </div>
        </div>
    </div>

    <div class="actions">
        <h2>⚡ Acciones Rápidas</h2>
        <div class="btn-group">
            <a href="?action=clean_expired" class="btn btn-primary" onclick="return confirm('¿Limpiar archivos expirados?')">
                🧹 Limpiar Expirados
            </a>
            <a href="?action=flush_sql" class="btn btn-warning" onclick="return confirm('¿Limpiar caché SQL?')">
                🗄️ Limpiar SQL
            </a>
            <a href="?action=flush_html" class="btn btn-warning" onclick="return confirm('¿Limpiar caché HTML?')">
                🎨 Limpiar HTML
            </a>
            <a href="?action=flush_objects" class="btn btn-warning" onclick="return confirm('¿Limpiar caché de objetos?')">
                📦 Limpiar Objetos
            </a>
            <a href="?action=flush_all" class="btn btn-danger" onclick="return confirm('⚠️ ¿Estás seguro de limpiar TODO el caché?')">
                🗑️ Limpiar TODO
            </a>
        </div>
    </div>

    <div class="details">
        <h2>📋 Información Detallada</h2>
        <div class="detail-row">
            <span class="detail-label">Estado del sistema:</span>
            <span class="detail-value">✅ Activo</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">TTL SQL:</span>
            <span class="detail-value"><?= $info['config']['ttl_sql'] ?> segundos (<?= round($info['config']['ttl_sql']/60, 1) ?> min)</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">TTL HTML:</span>
            <span class="detail-value"><?= $info['config']['ttl_html'] ?> segundos (<?= round($info['config']['ttl_html']/60, 1) ?> min)</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">TTL Objetos:</span>
            <span class="detail-value"><?= $info['config']['ttl_object'] ?> segundos (<?= round($info['config']['ttl_object']/60, 1) ?> min)</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Compresión:</span>
            <span class="detail-value"><?= $info['config']['compression'] ? '✅ Habilitada' : '❌ Deshabilitada' ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tamaño máximo archivo:</span>
            <span class="detail-value"><?= round($info['config']['max_file_size'] / (1024*1024), 1) ?> MB</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Estadísticas desde:</span>
            <span class="detail-value"><?= date('d/m/Y H:i:s', $stats['created']) ?></span>
        </div>
    </div>
</div>

</body>
</html>
