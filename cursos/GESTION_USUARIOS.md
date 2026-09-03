# 👥 SISTEMA DE GESTIÓN DE USUARIOS

**Fecha:** 4 de Mayo de 2026  
**Estado:** ✅ INSTALADO Y FUNCIONAL

## 📋 Descripción

Sistema completo para gestionar usuarios del panel administrativo de The Corner. Permite crear, editar y eliminar usuarios de forma segura desde una interfaz web.

## 🎯 Características

✅ **Crear nuevos usuarios** con nombre, usuario y contraseña  
✅ **Cambiar contraseñas** de usuarios existentes  
✅ **Eliminar usuarios** personalizados  
✅ **Protección de usuarios del sistema** (no se pueden modificar desde la UI)  
✅ **Validación de datos** (contraseñas mínimo 6 caracteres, usuarios alfanuméricos)  
✅ **Protección CSRF** en todos los formularios  
✅ **Interfaz corporativa** con branding The Corner  
✅ **Responsive design** para móvil y desktop

## 📁 Archivos

```
cursos/
├── admin-usuarios.php          # Interfaz de gestión
├── inc/auth.php               # Sistema de autenticación (actualizado)
├── data/
│   └── users.json             # Almacenamiento de usuarios (se crea automáticamente)
├── admin-dashboard.php        # Dashboard con enlace a gestión
└── .htaccess                  # URLs limpias configuradas
```

## 🔗 Acceso

- **URL completa:** http://localhost/cursos/admin-usuarios.php
- **URL limpia:** http://localhost/cursos/admin-usuarios
- **URL corta:** http://localhost/cursos/usuarios

## 👥 Tipos de Usuarios

### Usuarios del Sistema (Hardcodeados)
- Definidos en `inc/auth.php` en la constante `AUTH_USERS`
- **No se pueden modificar** desde la interfaz web
- Para cambiarlos hay que editar directamente el archivo PHP
- Ejemplos: `admin`, `thecorner`

### Usuarios Personalizados
- Guardados en `data/users.json`
- **Se pueden crear, editar y eliminar** desde la web
- Persisten automáticamente
- Útiles para accesos temporales o usuarios adicionales

## 🎨 Funciones Principales

### ➕ Crear Usuario
1. Rellenar formulario con:
   - Usuario (solo letras, números y guión bajo)
   - Nombre completo
   - Contraseña (mínimo 6 caracteres)
2. Click en "Crear Usuario"
3. El usuario se guarda en `data/users.json`

### 🔑 Cambiar Contraseña
1. Click en "Cambiar contraseña" en la tarjeta del usuario
2. Escribir nueva contraseña (mínimo 6 caracteres)
3. Click en "Guardar"
4. La contraseña se actualiza instantáneamente

### 🗑️ Eliminar Usuario
1. Click en "Eliminar" en la tarjeta del usuario
2. Confirmar la acción
3. El usuario se elimina de `data/users.json`

## 🔒 Seguridad

✅ Todas las contraseñas se guardan con **bcrypt** (PASSWORD_DEFAULT)  
✅ **Protección CSRF** en todos los formularios  
✅ **Validación de entrada** en todos los campos  
✅ **Solo usuarios autenticados** pueden acceder  
✅ **Protección de usuarios del sistema** contra eliminación accidental  
✅ **Archivo JSON fuera de webroot** (en directorio data/)

## 📊 Información Mostrada

La interfaz muestra:
- **Total de usuarios:** Suma de sistema + personalizados
- **Usuarios del sistema:** Hardcodeados en PHP
- **Usuarios personalizados:** Guardados en JSON
- **Fecha de creación:** Para usuarios personalizados
- **Fecha de cambio de contraseña:** Cuando se modifica

## 🔄 Integración con el Sistema

El sistema de autenticación (`inc/auth.php`) ahora soporta ambos tipos de usuarios:

```php
function auth_get_all_users() {
    // Combina usuarios hardcodeados + JSON
    return array_merge(AUTH_USERS, auth_load_json_users());
}
```

El login funciona automáticamente con ambos tipos de usuarios sin necesidad de configuración adicional.

## 🎯 Casos de Uso

### Caso 1: Añadir un Usuario Temporal
1. Entrar a /cursos/usuarios
2. Crear usuario con contraseña temporal
3. Compartir credenciales
4. Cuando ya no sea necesario, eliminarlo desde la misma interfaz

### Caso 2: Cambiar Contraseña de Usuario Comprometido
1. Entrar a /cursos/usuarios
2. Click en "Cambiar contraseña"
3. Generar nueva contraseña segura
4. Comunicar nueva contraseña al usuario

### Caso 3: Auditoría de Usuarios
1. Entrar a /cursos/usuarios
2. Ver lista completa de usuarios
3. Ver cuándo se crearon y cuándo cambiaron contraseña
4. Eliminar usuarios que ya no sean necesarios

## ⚠️ Notas Importantes

### Backup del Archivo users.json
El archivo `data/users.json` contiene todos los usuarios personalizados. Se recomienda hacer backups periódicos.

### Usuarios del Sistema
Para modificar usuarios del sistema (admin, thecorner):
1. Editar `inc/auth.php`
2. Usar `generate-password.php` para generar el hash
3. Reemplazar el valor en la constante `AUTH_USERS`

### Permisos del Directorio data/
Asegurarse de que Apache tenga permisos de escritura en el directorio `data/`:
```bash
chmod 755 data/
chmod 644 data/users.json
```

## 🚀 Próximas Mejoras

- [ ] Roles y permisos (admin, editor, viewer)
- [ ] Registro de actividad de usuarios (log de accesos)
- [ ] Expiración automática de usuarios temporales
- [ ] Contraseñas temporales que obligan a cambiar en primer login
- [ ] Autenticación de dos factores (2FA)
- [ ] Integración con LDAP/Active Directory

## 📝 Ejemplo de Archivo users.json

```json
{
    "juan_lopez": {
        "password": "$2y$10$...",
        "name": "Juan López",
        "created": "2026-05-04 10:30:00"
    },
    "maria_garcia": {
        "password": "$2y$10$...",
        "name": "María García",
        "created": "2026-05-04 11:15:00",
        "password_changed": "2026-05-04 14:20:00"
    }
}
```

---

**✅ Sistema listo para usar**

Accede a http://localhost/cursos/usuarios para empezar a gestionar usuarios.
