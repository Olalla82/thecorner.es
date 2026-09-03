# 🔐 SISTEMA DE AUTENTICACIÓN - PANEL DE CACHÉ

**Fecha de instalación:** 4 de Mayo de 2026  
**Estado:** ✅ ACTIVO

## 🎯 ¿QUÉ SE PROTEGE?

- ✅ Panel de administración (`cache-admin.php`)
- ✅ Sistema de tests (`cache-test.php`)
- ✅ Acceso a estadísticas y limpieza de caché

## 🔑 CREDENCIALES PREDETERMINADAS

### Usuario 1 (Admin Principal)
- **Usuario:** `admin`
- **Contraseña:** `admin123`

### Usuario 2 (The Corner)
- **Usuario:** `thecorner`
- **Contraseña:** `thecorner2026`

**⚠️ IMPORTANTE: Cambia estas contraseñas en producción**

## 📁 ARCHIVOS DEL SISTEMA

```
cursos/
├── inc/auth.php           # Motor de autenticación
├── login.php              # Página de login
├── logout.php             # Cierre de sesión
├── cache-admin.php        # Protegido ✅
└── cache-test.php         # Protegido ✅
```

## 🚀 CÓMO USAR

### 1️⃣ Acceder al panel
```
http://localhost/cursos/cache-admin.php
```
↳ Redirige automáticamente a login si no estás autenticado

### 2️⃣ Iniciar sesión
- Ingresa usuario y contraseña
- La sesión dura **2 horas**
- Después de la inactividad se cierra automáticamente

### 3️⃣ Cerrar sesión
- Click en el botón "🚪 Cerrar Sesión" en el panel
- O accede directamente a: `http://localhost/cursos/logout.php`

## 🔐 CARACTERÍSTICAS DE SEGURIDAD

✅ **Contraseñas hasheadas** (bcrypt)  
✅ **Protección contra fuerza bruta** (5 intentos → bloqueo 5 min)  
✅ **Sesiones seguras** (regeneración de ID)  
✅ **Timeout automático** (2 horas)  
✅ **Protección CSRF** (tokens incluidos)

## 🛠️ CAMBIAR CONTRASEÑAS

### Opción 1: Editar manualmente
Abre `inc/auth.php` y modifica el array `AUTH_USERS`:

```php
define('AUTH_USERS', [
    'admin' => [
        'password' => '$2y$10$...',  // Hash de la contraseña
        'name' => 'Administrador'
    ]
]);
```

### Opción 2: Generar hash de contraseña
Crea un archivo temporal `generate-password.php`:

```php
<?php
require_once 'inc/auth.php';
$password = 'MiNuevaContraseñaSegura123!';
echo "Hash: " . auth_generate_password($password);
```

Ejecuta el archivo y copia el hash generado a `inc/auth.php`.

## 👥 AGREGAR NUEVOS USUARIOS

Edita `inc/auth.php` y agrega en el array `AUTH_USERS`:

```php
define('AUTH_USERS', [
    'admin' => [...],
    'thecorner' => [...],
    'nuevo_usuario' => [
        'password' => '$2y$10$...hash_aqui...',
        'name' => 'Nombre Completo'
    ]
]);
```

## 🔍 SOLUCIÓN DE PROBLEMAS

### Problema: "Demasiados intentos fallidos"
**Solución:** Espera 5 minutos o borra las cookies del navegador

### Problema: La sesión expira muy rápido
**Solución:** Edita `inc/auth.php` línea ~26:
```php
(time() - $_SESSION['auth_time']) < 7200; // 2 horas (en segundos)
```

### Problema: No puedo acceder ni con las credenciales correctas
**Solución:** 
1. Verifica que `session.save_path` en PHP esté configurado
2. Revisa permisos de la carpeta de sesiones
3. Prueba en modo incógnito (puede ser un problema de cookies)

## 📊 LOGS Y AUDITORÍA

El sistema registra en sesión:
- Intentos de login fallidos
- Tiempo de última actividad
- Usuario actualmente autenticado

Para logs más detallados, considera implementar un sistema de logging en archivo.

## 🚧 MEJORAS FUTURAS (OPCIONAL)

- [ ] Sistema de roles (admin, viewer, editor)
- [ ] Autenticación de dos factores (2FA)
- [ ] Registro de actividad en archivo de log
- [ ] Recuperación de contraseña por email
- [ ] Integración con Active Directory / LDAP
- [ ] API tokens para acceso programático

## ✅ PRUEBA EL SISTEMA

1. Abre: `http://localhost/cursos/cache-admin.php`
2. Deberías ver la página de login
3. Ingresa: `admin` / `admin123`
4. Deberías ver el panel con tu nombre de usuario
5. Click en "Cerrar Sesión"
6. Deberías regresar al login

---

**Estado:** ✅ SISTEMA FUNCIONANDO CORRECTAMENTE
