<?php
// ⚠️ REQUIERE AUTENTICACIÓN
require_once __DIR__ . '/inc/auth.php';
auth_require();

// Cargar sistema de caché para obtener estadísticas
require_once __DIR__ . '/inc/cache.php';

// Obtener estadísticas del caché
$cache_info = cache()->getInfo();
$cache_stats = $cache_info['stats'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo - The Corner</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e1545 0%, #2a1d5f 50%, #1e1545 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, rgba(194, 213, 0, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header-left img {
            height: 50px;
            width: auto;
        }
        
        .header-title h1 {
            color: #1e1545;
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .header-title p {
            color: #666;
            font-size: 14px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-info p {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .user-info strong {
            color: #333;
        }
        
        .btn-logout {
            display: inline-block;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        /* Welcome Section */
        .welcome {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
            font-size: 16px;
        }
        
        /* Tools Grid */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .tool-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }
        
        .tool-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #C2D500, #a3c62e);
        }
        
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .tool-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .tool-card.disabled:hover {
            transform: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .tool-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .tool-icon {
            font-size: 48px;
            line-height: 1;
        }
        
        .tool-info h3 {
            color: #333;
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .tool-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .tool-status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .tool-status.coming-soon {
            background: #fff3cd;
            color: #856404;
        }
        
        .tool-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .tool-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #C2D500;
            display: block;
        }
        
        .stat-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .tool-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #C2D500, #a3c62e);
            color: #1e1545;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 15px;
            transition: all 0.3s;
        }
        
        .tool-card:hover .tool-action {
            transform: translateX(5px);
        }
        
        .tool-card.disabled .tool-action {
            background: #ccc;
            transform: none;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            color: white;
            padding: 20px;
            font-size: 14px;
        }
        
        .footer a {
            color: white;
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header-left {
                flex-direction: column;
            }
            
            .user-info {
                text-align: center;
            }
            
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner">
                <div class="header-title">
                    <h1>🎛️ Dashboard Administrativo</h1>
                    <p>Panel de control de herramientas</p>
                </div>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <p>👤 Sesión activa</p>
                    <p><strong><?= htmlspecialchars(auth_user_name()) ?></strong></p>
                </div>
                <a href="logout.php" class="btn-logout">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </div>
        
        <!-- Welcome Section -->
        <div class="welcome">
            <h2>¡Bienvenido, <?= htmlspecialchars(auth_user_name()) ?>! 👋</h2>
            <p>Selecciona una herramienta para administrar el sistema. Todas las herramientas requieren autenticación y están protegidas.</p>
        </div>
        
        <!-- Tools Grid -->
        <div class="tools-grid">
            <!-- Sistema de Caché -->
            <a href="cache-admin.php" class="tool-card">
                <div class="tool-header">
                    <div class="tool-icon">🚀</div>
                    <div class="tool-info">
                        <h3>Sistema de Caché</h3>
                        <span class="tool-status active">✓ Activo</span>
                    </div>
                </div>
                <div class="tool-description">
                    Panel de administración del sistema de caché multi-nivel. Gestiona caché SQL, HTML y objetos PHP con estadísticas en tiempo real.
                </div>
                <div class="tool-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?= $cache_stats['hit_rate'] ?>%</span>
                        <span class="stat-label">Hit Rate</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= number_format($cache_info['stats']['file_count']) ?></span>
                        <span class="stat-label">Archivos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= $cache_stats['cache_size_formatted'] ?></span>
                        <span class="stat-label">Tamaño</span>
                    </div>
                </div>
                <span class="tool-action">
                    Abrir Panel →
                </span>
            </a>
            
            <!-- Tests de Caché -->
            <a href="cache-test.php" class="tool-card">
                <div class="tool-header">
                    <div class="tool-icon">🧪</div>
                    <div class="tool-info">
                        <h3>Tests del Sistema</h3>
                        <span class="tool-status active">✓ Activo</span>
                    </div>
                </div>
                <div class="tool-description">
                    Suite de pruebas automatizadas para verificar el funcionamiento del sistema de caché. Tests unitarios y de integración.
                </div>
                <div class="tool-stats">
                    <div class="stat-item">
                        <span class="stat-value">20</span>
                        <span class="stat-label">Tests</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">100%</span>
                        <span class="stat-label">Cobertura</span>
                    </div>
                </div>
                <span class="tool-action">
                    Ejecutar Tests →
                </span>
            </a>
            
            <!-- Chatbot (Coming Soon) -->
            <div class="tool-card disabled">
                <div class="tool-header">
                    <div class="tool-icon">💬</div>
                    <div class="tool-info">
                        <h3>Sistema de Chatbot</h3>
                        <span class="tool-status coming-soon">Próximamente</span>
                    </div>
                </div>
                <div class="tool-description">
                    Panel de administración del chatbot inteligente. Configuración, entrenamiento, estadísticas de conversaciones y análisis de respuestas.
                </div>
                <div class="tool-stats">
                    <div class="stat-item">
                        <span class="stat-value">-</span>
                        <span class="stat-label">Mensajes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">-</span>
                        <span class="stat-label">Usuarios</span>
                    </div>
                </div>
                <span class="tool-action">
                    Próximamente →
                </span>
            </div>
            
            <!-- Gestión de Usuarios -->
            <a href="admin-usuarios.php" class="tool-card">
                <div class="tool-header">
                    <div class="tool-icon">👥</div>
                    <div class="tool-info">
                        <h3>Gestión de Usuarios</h3>
                        <span class="tool-status active">✓ Activo</span>
                    </div>
                </div>
                <div class="tool-description">
                    Crear, editar y eliminar usuarios del sistema. Gestiona accesos y contraseñas de forma centralizada.
                </div>
                <span class="tool-action">
                    Gestionar Usuarios →
                </span>
            </a>
            
            <!-- Analytics (Coming Soon) -->
            <div class="tool-card disabled">
                <div class="tool-header">
                    <div class="tool-icon">📊</div>
                    <div class="tool-info">
                        <h3>Analytics Avanzado</h3>
                        <span class="tool-status coming-soon">Próximamente</span>
                    </div>
                </div>
                <div class="tool-description">
                    Panel de analíticas avanzadas con métricas de rendimiento, estadísticas de uso y gráficas interactivas en tiempo real.
                </div>
                <span class="tool-action">
                    Próximamente →
                </span>
            </div>
            
            <!-- Backup System (Coming Soon) -->
            <div class="tool-card disabled">
                <div class="tool-header">
                    <div class="tool-icon">💾</div>
                    <div class="tool-info">
                        <h3>Sistema de Backups</h3>
                        <span class="tool-status coming-soon">Próximamente</span>
                    </div>
                </div>
                <div class="tool-description">
                    Gestión automatizada de copias de seguridad. Programación, restauración y monitorización de backups del sistema.
                </div>
                <span class="tool-action">
                    Próximamente →
                </span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>🔐 Panel de Administración Seguro | The Corner - <?= date('Y') ?></p>
            <p><a href="https://thecorner.es">Volver al sitio principal</a></p>
        </div>
    </div>
</body>
</html>
