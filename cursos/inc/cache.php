<?php
/**
 * Sistema de Caché Multi-Nivel Potente
 * Versión: 2.0
 * Fecha: Abril 2026
 * 
 * Características:
 * - Caché de consultas SQL
 * - Caché de fragmentos HTML
 * - Caché de objetos PHP
 * - Sistema de estadísticas
 * - Limpieza automática
 * - TTL configurable
 */

/**
 * Wrapper para resultados cacheados que imita mysqli_result
 */
class CachedResult {
    private $data;
    private $position = 0;
    
    public function __construct($data) {
        $this->data = is_array($data) ? $data : [];
    }
    
    // Para compatibilidad con foreach
    public function getIterator() {
        foreach ($this->data as $row) {
            yield $row;
        }
    }
    
    // Para compatibilidad con código existente
    public function fetch() {
        if ($this->position >= count($this->data)) {
            return null;
        }
        return $this->data[$this->position++];
    }
    
    public function num_rows() {
        return count($this->data);
    }
    
    // Método data_seek para reiniciar posición
    public function data_seek($offset) {
        if ($offset >= 0 && $offset < count($this->data)) {
            $this->position = $offset;
            return true;
        }
        return false;
    }
}

/**
 * Helper function para fetch que funciona con mysqli_result y CachedResult
 */
function fetch_result($result) {
    if ($result instanceof CachedResult) {
        return $result->fetch();
    }
    return mysqli_fetch_assoc($result);
}

/**
 * Helper function para data_seek que funciona con mysqli_result y CachedResult
 */
function data_seek_result($result, $offset) {
    if ($result instanceof CachedResult) {
        return $result->data_seek($offset);
    }
    return mysqli_data_seek($result, $offset);
}

class CacheSystem {
    
    private $cache_dir;
    private $stats_file;
    private $config;
    private $stats;
    
    // TTL por defecto en segundos
    const TTL_SQL = 300;      // 5 minutos
    const TTL_HTML = 300;     // 5 minutos
    const TTL_OBJECT = 600;   // 10 minutos
    
    public function __construct($cache_dir = null) {
        $this->cache_dir = $cache_dir ?: __DIR__ . '/../cache';
        $this->stats_file = $this->cache_dir . '/stats.json';
        
        // Configuración
        $this->config = [
            'enabled' => true,
            'ttl_sql' => self::TTL_SQL,
            'ttl_html' => self::TTL_HTML,
            'ttl_object' => self::TTL_OBJECT,
            'max_file_size' => 5 * 1024 * 1024, // 5MB
            'compression' => true
        ];
        
        // Cargar estadísticas
        $this->loadStats();
        
        // Crear directorios si no existen
        $this->ensureDirectories();
    }
    
    /**
     * Caché de consultas SQL
     */
    public function sql($query, $callback, $ttl = null) {
        if (!$this->config['enabled']) {
            return $callback();
        }
        
        $ttl = $ttl ?: $this->config['ttl_sql'];
        $key = 'sql_' . md5($query);
        
        $result = $this->get('sql', $key, function() use ($callback) {
            $result = $callback();
            
            // Si es mysqli_result, convertir a array para serializar
            if ($result instanceof mysqli_result) {
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
            
            return $result;
        }, $ttl);
        
        // Si es un array, envolverlo en un CachedResult para compatibilidad
        if (is_array($result)) {
            return new CachedResult($result);
        }
        
        return $result;
    }
    
    /**
     * Caché de fragmentos HTML
     */
    public function html($key, $callback, $ttl = null) {
        if (!$this->config['enabled']) {
            ob_start();
            $callback();
            return ob_get_clean();
        }
        
        $ttl = $ttl ?: $this->config['ttl_html'];
        $cache_key = 'html_' . md5($key);
        
        return $this->get('html', $cache_key, function() use ($callback) {
            ob_start();
            $callback();
            return ob_get_clean();
        }, $ttl);
    }
    
    /**
     * Caché de objetos PHP
     */
    public function object($key, $callback, $ttl = null) {
        if (!$this->config['enabled']) {
            return $callback();
        }
        
        $ttl = $ttl ?: $this->config['ttl_object'];
        $cache_key = 'obj_' . md5($key);
        
        return $this->get('objects', $cache_key, $callback, $ttl);
    }
    
    /**
     * Obtener del caché o ejecutar callback
     */
    private function get($type, $key, $callback, $ttl) {
        $file = $this->getCacheFile($type, $key);
        
        // Verificar si existe y no ha expirado
        if (file_exists($file)) {
            $data = $this->read($file);
            
            if ($data && (time() - $data['timestamp']) < $ttl) {
                $this->recordHit($type);
                return $data['content'];
            } else {
                // Expirado, eliminar
                @unlink($file);
            }
        }
        
        // Cache miss, ejecutar callback
        $this->recordMiss($type);
        $content = $callback();
        
        // Guardar en caché
        $this->write($file, $content);
        
        return $content;
    }
    
    /**
     * Limpiar caché completo
     */
    public function flush($type = null) {
        if ($type) {
            $this->clearDirectory($this->cache_dir . '/' . $type);
        } else {
            $this->clearDirectory($this->cache_dir . '/sql');
            $this->clearDirectory($this->cache_dir . '/html');
            $this->clearDirectory($this->cache_dir . '/objects');
        }
        
        $this->resetStats();
        return true;
    }
    
    /**
     * Limpiar caché por patrón
     */
    public function flushPattern($type, $pattern) {
        $dir = $this->cache_dir . '/' . $type;
        if (!is_dir($dir)) return false;
        
        $files = glob($dir . '/*' . $pattern . '*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        
        return true;
    }
    
    /**
     * Limpiar caché expirado
     */
    public function cleanExpired() {
        $cleaned = 0;
        $types = ['sql', 'html', 'objects'];
        $ttls = [
            'sql' => $this->config['ttl_sql'],
            'html' => $this->config['ttl_html'],
            'objects' => $this->config['ttl_object']
        ];
        
        foreach ($types as $type) {
            $dir = $this->cache_dir . '/' . $type;
            if (!is_dir($dir)) continue;
            
            $files = glob($dir . '/*.cache');
            foreach ($files as $file) {
                $data = $this->read($file);
                if (!$data || (time() - $data['timestamp']) > $ttls[$type]) {
                    if (@unlink($file)) {
                        $cleaned++;
                    }
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtener estadísticas
     */
    public function getStats() {
        $stats = $this->stats;
        
        // Calcular hit rate
        $total = $stats['hits'] + $stats['misses'];
        $stats['hit_rate'] = $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0;
        
        // Tamaño del caché
        $stats['cache_size'] = $this->getCacheSize();
        $stats['cache_size_formatted'] = $this->formatBytes($stats['cache_size']);
        
        // Archivos
        $stats['file_count'] = $this->countCacheFiles();
        
        return $stats;
    }
    
    /**
     * Obtener información detallada
     */
    public function getInfo() {
        $info = [
            'stats' => $this->getStats(),
            'config' => $this->config,
            'types' => []
        ];
        
        foreach (['sql', 'html', 'objects'] as $type) {
            $info['types'][$type] = [
                'count' => $this->countFiles($type),
                'size' => $this->getTypeSize($type),
                'size_formatted' => $this->formatBytes($this->getTypeSize($type))
            ];
        }
        
        return $info;
    }
    
    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================
    
    private function getCacheFile($type, $key) {
        return $this->cache_dir . '/' . $type . '/' . $key . '.cache';
    }
    
    private function read($file) {
        if (!file_exists($file)) return null;
        
        $content = file_get_contents($file);
        if ($this->config['compression']) {
            $content = @gzuncompress($content);
            if ($content === false) return null;
        }
        
        return unserialize($content);
    }
    
    private function write($file, $content) {
        $data = [
            'timestamp' => time(),
            'content' => $content
        ];
        
        $serialized = serialize($data);
        
        if ($this->config['compression']) {
            $serialized = gzcompress($serialized, 6);
        }
        
        // Verificar tamaño
        if (strlen($serialized) > $this->config['max_file_size']) {
            return false;
        }
        
        return file_put_contents($file, $serialized, LOCK_EX) !== false;
    }
    
    private function clearDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '/*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
    
    private function ensureDirectories() {
        $dirs = [
            $this->cache_dir,
            $this->cache_dir . '/sql',
            $this->cache_dir . '/html',
            $this->cache_dir . '/objects'
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Crear .htaccess para proteger
        $htaccess = $this->cache_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all");
        }
    }
    
    private function loadStats() {
        $default_stats = [
            'hits' => 0,
            'misses' => 0,
            'hits_sql' => 0,
            'misses_sql' => 0,
            'hits_html' => 0,
            'misses_html' => 0,
            'hits_objects' => 0,
            'misses_objects' => 0,
            'created' => time()
        ];
        
        if (file_exists($this->stats_file)) {
            $content = @file_get_contents($this->stats_file);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    // Merge con defaults para asegurar todas las claves
                    $this->stats = array_merge($default_stats, $decoded);
                    return;
                }
            }
        }
        
        // Si falla lectura o no existe, usar defaults
        $this->stats = $default_stats;
    }
    
    private function saveStats() {
        file_put_contents($this->stats_file, json_encode($this->stats, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    private function recordHit($type) {
        $this->stats['hits']++;
        $this->stats['hits_' . $type]++;
        $this->saveStats();
    }
    
    private function recordMiss($type) {
        $this->stats['misses']++;
        $this->stats['misses_' . $type]++;
        $this->saveStats();
    }
    
    private function resetStats() {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'hits_sql' => 0,
            'misses_sql' => 0,
            'hits_html' => 0,
            'misses_html' => 0,
            'hits_objects' => 0,
            'misses_objects' => 0,
            'created' => time()
        ];
        $this->saveStats();
    }
    
    private function getCacheSize() {
        $size = 0;
        $types = ['sql', 'html', 'objects'];
        
        foreach ($types as $type) {
            $dir = $this->cache_dir . '/' . $type;
            if (!is_dir($dir)) continue;
            
            $files = glob($dir . '/*.cache');
            foreach ($files as $file) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }
    
    private function getTypeSize($type) {
        $size = 0;
        $dir = $this->cache_dir . '/' . $type;
        
        if (is_dir($dir)) {
            $files = glob($dir . '/*.cache');
            foreach ($files as $file) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }
    
    private function countCacheFiles() {
        $count = 0;
        $types = ['sql', 'html', 'objects'];
        
        foreach ($types as $type) {
            $count += $this->countFiles($type);
        }
        
        return $count;
    }
    
    private function countFiles($type) {
        $dir = $this->cache_dir . '/' . $type;
        if (!is_dir($dir)) return 0;
        
        return count(glob($dir . '/*.cache'));
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// ============================================
// FUNCIÓN HELPER GLOBAL
// ============================================

/**
 * Obtener instancia del sistema de caché
 */
function cache() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CacheSystem();
    }
    return $instance;
}
