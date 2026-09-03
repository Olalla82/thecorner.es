<?php
/**
 * Script de Limpieza Automática de Caché
 * 
 * Este script debe ejecutarse periódicamente (cada hora recomendado)
 * para limpiar archivos de caché expirados.
 * 
 * CONFIGURACIÓN WINDOWS (PowerShell como Administrador):
 * -------------------------------------------------------
 * schtasks /create /tn "CleanCacheTheCorner" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\cursos\cache-cleanup.php" /sc hourly /st 00:00
 * 
 * CONFIGURACIÓN LINUX (crontab):
 * --------------------------------
 * 0 * * * * /usr/bin/php /var/www/html/cursos/cache-cleanup.php >> /var/log/cache-cleanup.log 2>&1
 * 
 * EJECUCIÓN MANUAL:
 * -----------------
 * php cache-cleanup.php
 */

// Evitar timeout
set_time_limit(300);

// Cargar sistema de caché
require_once __DIR__ . '/inc/cache.php';

// Log file
$log_file = __DIR__ . '/cache/cleanup.log';

/**
 * Escribir en log
 */
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $line = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
    echo $line;
}

// ============================================
// INICIO DEL PROCESO
// ============================================

log_message("=== INICIO LIMPIEZA AUTOMÁTICA ===");

try {
    // Obtener estadísticas antes
    $stats_before = cache()->getStats();
    log_message("Estado inicial:");
    log_message("  - Archivos: {$stats_before['file_count']}");
    log_message("  - Tamaño: {$stats_before['cache_size_formatted']}");
    log_message("  - Hit rate: {$stats_before['hit_rate']}%");
    
    // Limpiar archivos expirados
    log_message("Limpiando archivos expirados...");
    $cleaned = cache()->cleanExpired();
    log_message("✅ Se limpiaron {$cleaned} archivos expirados");
    
    // Estadísticas después
    $stats_after = cache()->getStats();
    log_message("Estado final:");
    log_message("  - Archivos: {$stats_after['file_count']}");
    log_message("  - Tamaño: {$stats_after['cache_size_formatted']}");
    
    // Calcular ahorro
    $space_saved = $stats_before['cache_size'] - $stats_after['cache_size'];
    $space_saved_mb = round($space_saved / (1024 * 1024), 2);
    log_message("  - Espacio liberado: {$space_saved_mb} MB");
    
    // Limpiar logs antiguos (mantener últimos 30 días)
    $log_max_age = 30 * 24 * 60 * 60; // 30 días
    if (file_exists($log_file)) {
        $log_age = time() - filemtime($log_file);
        if ($log_age > $log_max_age) {
            $lines = file($log_file);
            $recent_lines = array_slice($lines, -1000); // Mantener últimas 1000 líneas
            file_put_contents($log_file, implode('', $recent_lines));
            log_message("📝 Log truncado (manteniendo últimas 1000 entradas)");
        }
    }
    
    log_message("=== LIMPIEZA COMPLETADA EXITOSAMENTE ===\n");
    exit(0);
    
} catch (Exception $e) {
    log_message("❌ ERROR: " . $e->getMessage());
    log_message("=== LIMPIEZA FINALIZADA CON ERRORES ===\n");
    exit(1);
}
