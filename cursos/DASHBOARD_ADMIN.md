# 🎛️ DASHBOARD ADMINISTRATIVO CENTRALIZADO

**Fecha de creación:** 4 de Mayo de 2026  
**Estado:** ✅ ACTIVO Y FUNCIONANDO

---

## 🎯 ¿QUÉ ES?

Un panel de control centralizado donde después de hacer login puedes acceder a todas tus herramientas administrativas desde un solo lugar. Es el hub principal de administración.

---

## 🚀 CÓMO ACCEDER

### Opción 1: Hacer login
1. Accede a: `http://localhost/cursos/login`
2. Ingresa tus credenciales
3. Serás redirigido automáticamente al dashboard

### Opción 2: Acceso directo (si ya tienes sesión)
```
http://localhost/cursos/admin-dashboard
http://localhost/cursos/admin
http://localhost/cursos/dashboard
```

**Credenciales:**
- `admin` / `admin123`
- `thecorner` / `thecorner2026`

---

## 🛠️ HERRAMIENTAS DISPONIBLES

### ✅ Activas

#### 🚀 Sistema de Caché
- **Estado:** Activo
- **URL:** `cache-admin.php`
- **Descripción:** Panel completo de administración del sistema de caché multi-nivel
- **Estadísticas en vivo:**
  - Hit Rate (% de eficiencia)
  - Número de archivos cacheados
  - Tamaño total del caché
- **Funciones:** Limpiar caché, ver estadísticas, gestionar tipos de caché

#### 🧪 Tests del Sistema
- **Estado:** Activo
- **URL:** `cache-test.php`
- **Descripción:** Suite de pruebas automatizadas para el sistema de caché
- **Tests:** 20 tests con 100% de cobertura
- **Funciones:** Ejecutar tests, ver resultados, validar funcionamiento

#### 🔑 Generador de Contraseñas
- **Estado:** Activo
- **URL:** `generate-password.php`
- **Descripción:** Herramienta para generar hashes seguros
- **Funciones:** Generar hashes bcrypt, copiar al portapapeles

### 🔜 Próximamente

#### 💬 Sistema de Chatbot
- **Estado:** Próximamente
- **Descripción:** Panel de administración del chatbot inteligente
- **Funciones planificadas:**
  - Configuración del chatbot
  - Entrenamiento y ajustes
  - Estadísticas de conversaciones
  - Análisis de respuestas

#### 📊 Analytics Avanzado
- **Estado:** Próximamente
- **Descripción:** Panel de analíticas con métricas de rendimiento
- **Funciones planificadas:**
  - Gráficas interactivas
  - Métricas en tiempo real
  - Informes personalizados

#### 💾 Sistema de Backups
- **Estado:** Próximamente
- **Descripción:** Gestión automatizada de copias de seguridad
- **Funciones planificadas:**
  - Programación de backups
  - Restauración
  - Monitorización

---

## ➕ CÓMO AGREGAR NUEVAS HERRAMIENTAS

### 1. Crea tu herramienta

Ejemplo: `chatbot-admin.php`
```php
<?php
require_once __DIR__ . '/inc/auth.php';
auth_require();

// Tu código aquí
?>
```

### 2. Protégela con autenticación

Ya está incluido en el ejemplo anterior con `auth_require()`

### 3. Agrega la tarjeta al dashboard

Edita `admin-dashboard.php` y agrega en la sección `tools-grid`:

```php
<a href="chatbot-admin.php" class="tool-card">
    <div class="tool-header">
        <div class="tool-icon">💬</div>
        <div class="tool-info">
            <h3>Sistema de Chatbot</h3>
            <span class="tool-status active">✓ Activo</span>
        </div>
    </div>
    <div class="tool-description">
        Panel de administración del chatbot inteligente con estadísticas.
    </div>
    <div class="tool-stats">
        <div class="stat-item">
            <span class="stat-value">1,234</span>
            <span class="stat-label">Mensajes</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">89</span>
            <span class="stat-label">Usuarios</span>
        </div>
    </div>
    <span class="tool-action">
        Abrir Panel →
    </span>
</a>
```

### 4. Agrega la URL limpia al .htaccess

Edita `.htaccess` y agrega:
```apache
RewriteRule ^chatbot-admin/?$ chatbot-admin.php [L,QSA]
```

### 5. Agrega botón de retorno al dashboard

En tu herramienta nueva, agrega un botón:
```html
<a href="admin-dashboard.php">← Volver al Dashboard</a>
```

---

## 🎨 PERSONALIZACIÓN

### Cambiar colores

Edita `admin-dashboard.php` y modifica las variables CSS:

```css
/* Gradiente principal */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Color de tarjetas activas */
.tool-status.active {
    background: #d4edda;
    color: #155724;
}
```

### Agregar estadísticas dinámicas

```php
// Obtener datos de tu sistema
$mi_dato = obtener_estadistica();

// Mostrar en la tarjeta
<span class="stat-value"><?= $mi_dato ?></span>
<span class="stat-label">Mi Métrica</span>
```

---

## 🔒 SEGURIDAD

✅ **Todas las herramientas están protegidas** con `auth_require()`  
✅ **Sesiones seguras** con timeout de 2 horas  
✅ **Protección contra fuerza bruta** (5 intentos → bloqueo 5 min)  
✅ **URLs limpias** configuradas en .htaccess  
✅ **Botón de logout** visible en todas las páginas  

---

## 📱 RESPONSIVE

El dashboard es totalmente responsive y funciona en:
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px+)
- ✅ Tablet (768px+)
- ✅ Móvil (320px+)

---

## 🔗 NAVEGACIÓN

```
Login (login.php)
    ↓
Dashboard (admin-dashboard.php)
    ↓
    ├─→ Sistema de Caché (cache-admin.php) ←─ Botón volver
    ├─→ Tests Sistema (cache-test.php) ←─ Botón volver
    ├─→ Generador Contraseñas (generate-password.php)
    └─→ Logout (logout.php)
```

---

## 📊 ESTADÍSTICAS EN VIVO

El dashboard muestra automáticamente:
- Hit Rate del caché
- Número de archivos cacheados
- Tamaño total del caché

Para agregar más estadísticas:
```php
// En admin-dashboard.php
$tus_datos = obtener_tus_datos();

// En la tarjeta
<span class="stat-value"><?= $tus_datos ?></span>
```

---

## 🚨 SOLUCIÓN DE PROBLEMAS

### No veo el dashboard después del login
- Verifica que `admin-dashboard.php` exista
- Limpia la caché del navegador (Ctrl+F5)
- Revisa que la regla esté en .htaccess

### Las estadísticas no se cargan
- Verifica que `inc/cache.php` esté cargado
- Comprueba que el sistema de caché funcione
- Revisa el log de errores PHP

### Error 404 en las URLs limpias
- Verifica que mod_rewrite esté activo en Apache
- Comprueba que las reglas estén en .htaccess
- Reinicia Apache

---

## 📁 ARCHIVOS DEL SISTEMA

```
cursos/
├── admin-dashboard.php          # Dashboard principal
├── login.php                    # Login (redirige aquí)
├── logout.php                   # Cerrar sesión
├── cache-admin.php              # Herramienta 1
├── cache-test.php               # Herramienta 2
├── generate-password.php        # Herramienta 3
├── inc/
│   └── auth.php                 # Sistema de autenticación
└── .htaccess                    # URLs limpias
```

---

## ✅ CHECKLIST POST-INSTALACIÓN

- [x] Dashboard creado y funcionando
- [x] Login redirige al dashboard
- [x] URLs limpias configuradas
- [x] Botones de navegación agregados
- [x] Estadísticas en vivo del caché
- [x] Tarjetas para herramientas futuras
- [x] Diseño responsive
- [x] Sistema de autenticación integrado
- [ ] Agregar tu chatbot (cuando esté listo)
- [ ] Personalizar colores (opcional)
- [ ] Cambiar contraseñas por defecto

---

## 🎉 PRÓXIMOS PASOS

1. **Prueba el dashboard:** `http://localhost/cursos/admin-dashboard`
2. **Navega entre herramientas** usando las tarjetas
3. **Cuando tengas tu chatbot listo:**
   - Crea `chatbot-admin.php`
   - Protégelo con `auth_require()`
   - Cambia la tarjeta de "Próximamente" a "Activo"
   - Agrega las estadísticas que necesites

---

**Estado:** ✅ DASHBOARD COMPLETAMENTE FUNCIONAL

**Documentación relacionada:**
- [AUTH_SISTEMA.md](AUTH_SISTEMA.md) - Sistema de autenticación
- [README_CACHE.md](README_CACHE.md) - Sistema de caché
- [INSTALACION_LOGIN.md](INSTALACION_LOGIN.md) - Instalación del login
