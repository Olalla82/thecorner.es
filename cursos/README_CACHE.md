# 🚀 Sistema de Caché Multi-Nivel Potente + 🔐 Autenticación

**Estado:** ✅ ACTIVO Y PROTEGIDO  
**Última actualización:** 4 de Mayo de 2026

Sistema de caché profesional para PHP con soporte multi-nivel, estadísticas en tiempo real, panel de administración web y **sistema de autenticación seguro**.

---

## 🔐 SISTEMA DE AUTENTICACIÓN (NUEVO)

El panel de caché ahora está protegido con autenticación segura y forma parte de un **dashboard administrativo centralizado**.

### 🎛️ Dashboard Administrativo
- **Hub Principal:** http://localhost/cursos/admin-dashboard
- **Acceso único** a todas las herramientas administrativas
- **Estadísticas en vivo** del sistema de caché
- **Diseño modular** para agregar nuevas herramientas

### 🔑 Credenciales por defecto:
- **Admin:** `admin` / `admin123`
- **The Corner:** `thecorner` / `thecorner2026`

⚠️ **Cambia estas contraseñas en producción**

### 🔒 Enlaces protegidos:
- Dashboard Principal: http://localhost/cursos/admin-dashboard (hub central)
- Panel Caché: http://localhost/cursos/cache-admin (acceso directo)
- Tests: http://localhost/cursos/cache-test (acceso directo)
- Login: http://localhost/cursos/login
- Logout: http://localhost/cursos/logout

**📚 Documentación completa:**
- [DASHBOARD_ADMIN.md](DASHBOARD_ADMIN.md) - Dashboard centralizado
- [AUTH_SISTEMA.md](AUTH_SISTEMA.md) - Sistema de autenticación

---

## 📦 Características

✅ **Caché de consultas SQL** (TTL: 5 minutos)  
✅ **Caché de fragmentos HTML** (TTL: 5 minutos)  
✅ **Caché de objetos PHP** (TTL: 10 minutos)  
✅ **Panel de administración web**  
✅ **Sistema de estadísticas y métricas**  
✅ **Limpieza automática de archivos expirados**  
✅ **Compresión gzip para optimizar espacio**  
✅ **Sistema de invalidación selectiva**  
✅ **Protección con .htaccess**  

## 🎯 Beneficios Esperados

- ⚡ **70-80% más rápido** en páginas con consultas repetidas
- 📉 **90% menos queries** a la base de datos
- 🎯 **60-90% hit rate** después de calentar caché
- 💾 **Menos carga en servidor** de base de datos
- 🚀 **Mejor experiencia de usuario** con tiempos de carga reducidos

## 📁 Estructura de Archivos

```
cursos/
├── inc/
│   └── cache.php                    # Motor principal del sistema
├── cache/                            # Directorio de almacenamiento
│   ├── sql/                         # Caché de consultas SQL
│   ├── html/                        # Caché de fragmentos HTML
│   ├── objects/                     # Caché de objetos PHP
│   ├── stats.json                   # Archivo de estadísticas
│   ├── cleanup.log                  # Log de limpieza automática
│   └── .htaccess                    # Protección de acceso
├── cache-admin.php                  # Panel de administración web
├── cache-test.php                   # Suite de tests
├── cache-cleanup.php                # Script de limpieza automática
└── README_CACHE.md                  # Esta documentación
```

## 🚀 Instalación y Configuración

### 1. Verificar Instalación

El sistema ya está instalado. Verifica que funciona:

```bash
# Abrir en navegador:
http://localhost/cursos/cache-test.php
```

Deberías ver todos los tests en verde (✅).

### 2. Acceder al Panel de Administración

```bash
# Abrir en navegador:
http://localhost/cursos/cache-admin.php

# Si tu navegador tiene cacheado un 404 anterior, añade un parámetro único:
http://localhost/cursos/cache-admin.php?refresh=1
```

**💡 Tip:** Si actualizas el panel y no ves cambios, presiona **Ctrl + Shift + R** para forzar recarga sin caché.

Aquí podrás:
- Ver estadísticas en tiempo real
- Limpiar caché por tipo
- Limpiar archivos expirados
- Monitorear hit rate

### 3. Configurar Limpieza Automática

#### Windows (PowerShell como Administrador):

```powershell
schtasks /create /tn "CleanCacheTheCorner" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\cursos\cache-cleanup.php" /sc hourly /st 00:00
```

#### Linux (crontab):

```bash
# Editar crontab
crontab -e

# Añadir línea (ejecutar cada hora):
0 * * * * /usr/bin/php /var/www/html/cursos/cache-cleanup.php >> /var/log/cache-cleanup.log 2>&1
```

## 💻 Uso del Sistema

### 1. Caché de Consultas SQL

```php
<?php
require_once __DIR__ . '/inc/cache.php';

// Ejemplo básico
$query = "SELECT * FROM gen_cursos WHERE mostrar_web = 1";
$cursos = cache()->sql($query, function() use ($db, $query) {
    return mysqli_query($db, $query);
});

// Con TTL personalizado (10 minutos)
$cursos = cache()->sql($query, function() use ($db, $query) {
    return mysqli_query($db, $query);
}, 600);
```

### 2. Caché de Fragmentos HTML

```php
<?php
// Cachear una sección completa
echo cache()->html("listado_cursos_destacados", function() use ($cursos) {
    ?>
    <div class="cursos-destacados">
        <?php foreach ($cursos as $curso): ?>
            <div class="curso-card">
                <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
                <p><?= htmlspecialchars($curso['descripcion']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
});

// Con TTL personalizado (2 minutos)
echo cache()->html("header_menu", function() {
    include 'components/header.php';
}, 120);
```

### 3. Caché de Objetos PHP

```php
<?php
// Cachear arrays procesados
$estadisticas = cache()->object("estadisticas_mensuales", function() use ($db) {
    // Procesamiento pesado
    $stats = [];
    // ... consultas y cálculos
    return $stats;
});

// Cachear objetos
$configuracion = cache()->object("config_sistema", function() {
    $config = new ConfiguracionSistema();
    $config->cargar();
    return $config;
}, 3600); // 1 hora
```

## 🧹 Gestión del Caché

### Limpiar Caché Completo

```php
// Limpiar todo
cache()->flush();

// Limpiar solo SQL
cache()->flush('sql');

// Limpiar solo HTML
cache()->flush('html');

// Limpiar solo objetos
cache()->flush('objects');
```

### Limpiar por Patrón

```php
// Limpiar todos los caché que contengan "curso"
cache()->flushPattern('sql', 'curso');

// Limpiar caché de un curso específico
cache()->flushPattern('html', 'curso_123');
```

### Limpiar Archivos Expirados

```php
// Manualmente
$archivos_limpiados = cache()->cleanExpired();
echo "Se limpiaron {$archivos_limpiados} archivos";
```

## 📊 Obtener Estadísticas

```php
<?php
// Estadísticas básicas
$stats = cache()->getStats();
echo "Hit Rate: {$stats['hit_rate']}%";
echo "Total Hits: {$stats['hits']}";
echo "Total Misses: {$stats['misses']}";
echo "Tamaño: {$stats['cache_size_formatted']}";

// Información completa
$info = cache()->getInfo();
print_r($info);
```

## 🎯 Ejemplos Prácticos

### Ejemplo 1: Página de Listado de Cursos

```php
<?php
require_once __DIR__ . '/inc/cache.php';
require_once __DIR__ . '/inc/common.php';

// Cachear la consulta SQL
$query = "SELECT * FROM gen_cursos WHERE mostrar_web = 1 ORDER BY nombre";
$cursos = cache()->sql($query, function() use ($db, $query) {
    $result = mysqli_query($db, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
});

// Cachear el HTML completo de la página
echo cache()->html("pagina_cursos", function() use ($cursos) {
    include 'header.php';
    ?>
    <div class="container">
        <h1>Cursos Disponibles</h1>
        <div class="cursos-grid">
            <?php foreach ($cursos as $curso): ?>
                <div class="curso-card">
                    <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
                    <p><?= htmlspecialchars($curso['descripcion']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    include 'footer.php';
});
```

### Ejemplo 2: Invalidar Caché al Actualizar

```php
<?php
// Al actualizar un curso
function actualizarCurso($id, $datos) {
    global $db;
    
    // Actualizar en base de datos
    $query = "UPDATE gen_cursos SET ... WHERE id = $id";
    mysqli_query($db, $query);
    
    // Invalidar caché relacionado
    cache()->flush('sql'); // Limpiar todas las consultas SQL
    cache()->flushPattern('html', "curso_$id"); // Limpiar HTML del curso específico
    
    return true;
}
```

### Ejemplo 3: Widget con Actualización Frecuente

```php
<?php
// Sidebar con información que cambia poco
echo cache()->html("sidebar_info", function() {
    ?>
    <aside class="sidebar">
        <div class="widget">
            <h3>Cursos Populares</h3>
            <?php 
            $populares = cache()->sql("SELECT * FROM gen_cursos ORDER BY visitas DESC LIMIT 5", function() use ($db) {
                $result = mysqli_query($db, "SELECT * FROM gen_cursos ORDER BY visitas DESC LIMIT 5");
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            });
            
            foreach ($populares as $curso): ?>
                <div class="curso-mini">
                    <a href="/curso/<?= $curso['slug'] ?>"><?= $curso['nombre'] ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>
    <?php
}, 600); // 10 minutos
```

## ⚙️ Configuración Avanzada

### Modificar TTL por Defecto

Edita `inc/cache.php`:

```php
const TTL_SQL = 300;      // 5 minutos (cambiar según necesidad)
const TTL_HTML = 300;     // 5 minutos
const TTL_OBJECT = 600;   // 10 minutos
```

### Deshabilitar Compresión

Si tienes problemas de rendimiento con compresión:

```php
$this->config = [
    'enabled' => true,
    'compression' => false,  // Cambiar a false
    // ...
];
```

### Aumentar Tamaño Máximo de Archivo

```php
$this->config = [
    'max_file_size' => 10 * 1024 * 1024, // 10MB en lugar de 5MB
    // ...
];
```

## 🔧 Troubleshooting

### Problema: Caché no se guarda

**Solución:**
```bash
# Verificar permisos
chmod 755 cache/
chmod 755 cache/sql cache/html cache/objects
```

### Problema: Hit rate muy bajo

**Causas posibles:**
- TTL demasiado corto
- Páginas con parámetros dinámicos
- Invalidación muy frecuente

**Solución:**
- Aumentar TTL
- Usar claves de caché más específicas
- Revisar lógica de invalidación

### Problema: Espacio en disco

**Solución:**
```bash
# Ejecutar limpieza manual
php cache-cleanup.php

# O desde panel web
# Ir a cache-admin.php > "Limpiar Expirados"
```

## 📈 Monitoreo y Métricas

### Ver logs de limpieza automática

```bash
# Windows
type cache\cleanup.log

# Linux
tail -f cache/cleanup.log
```

### Métricas importantes a monitorear

- **Hit Rate:** Objetivo > 60% (bueno), > 80% (excelente)
- **Cache Size:** Mantener < 100MB para óptimo rendimiento
- **File Count:** Limpiar si supera 10,000 archivos

## 🚨 Cuándo NO usar Caché

❌ No cachear:
- Formularios con tokens CSRF
- Datos de sesión de usuario
- Contenido personalizado por usuario
- Datos que cambian en tiempo real (inventario, stock)
- Información sensible o privada

## 📚 Recursos Adicionales

- **Panel Admin:** `/cursos/cache-admin.php`
- **Tests:** `/cursos/cache-test.php`
- **Logs:** `/cursos/cache/cleanup.log`

## 🆘 Soporte

Si encuentras problemas:

1. Ejecuta tests: `http://localhost/cursos/cache-test.php`
2. Revisa logs: `cache/cleanup.log`
3. Verifica panel admin: `cache-admin.php`
4. Limpia caché: `cache()->flush()`

---

**Versión:** 2.0  
**Fecha:** Abril 2026  
**Estado:** ✅ Producción
