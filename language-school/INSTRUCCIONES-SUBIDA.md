# 🚀 GUÍA DE SUBIDA AL HOSTING - THE CORNER

## 📁 Archivos a subir

Sube **TODO el contenido** de la carpeta `language-school` a la raíz de tu hosting:

```
public_html/    (o www/ según tu hosting)
├── .htaccess                  ← IMPORTANTE
├── landing/
│   ├── index.html
│   ├── css/
│   ├── js/
│   └── img/
└── kinder-corner/
    ├── index.html
    ├── css/
    ├── js/
    └── img/
```

---

## 🌐 URLs finales

Una vez subido, las URLs serán:

- **Landing general**: `https://thecorner.es/landing/`
- **Kinder Corner**: `https://thecorner.es/kinder-corner/`

⚠️ **IMPORTANTE**: La barra final `/` es importante. Sin ella, el navegador redirigirá automáticamente añadiéndola.

---

## 📊 Tracking con parámetro origin

Para campañas publicitarias, usa el parámetro `?origin=X`:

### Landing general (origin por defecto: 7):
```
https://thecorner.es/landing/?origin=google
https://thecorner.es/landing/?origin=facebook
https://thecorner.es/landing/?origin=instagram
```

### Kinder Corner (origin por defecto: 32):
```
https://thecorner.es/kinder-corner/?origin=google
https://thecorner.es/kinder-corner/?origin=facebook
https://thecorner.es/kinder-corner/?origin=meta
```

---

## 🔧 Métodos de subida

### **Opción 1: FTP/SFTP (Recomendado)**

Usando FileZilla, WinSCP o Cyberduck:

1. Conecta a tu servidor FTP
2. Ve a la carpeta `public_html/` o `www/`
3. Arrastra las carpetas `landing/` y `kinder-corner/`
4. Arrastra el archivo `.htaccess`

### **Opción 2: Panel cPanel**

1. Accede a tu cPanel
2. Ve a "Administrador de archivos"
3. Navega a `public_html/`
4. Sube las carpetas usando el botón "Subir"

### **Opción 3: Git (Si usas repositorio)**

```bash
git clone tu-repositorio.git
cd language-school
scp -r landing kinder-corner .htaccess usuario@thecorner.es:/public_html/
```

---

## ✅ Verificación post-subida

### 1. **Verificar URLs limpias:**
- Accede a `https://thecorner.es/landing/`
- Accede a `https://thecorner.es/kinder-corner/`
- NO deberías ver `/index.html` en la barra de direcciones

### 2. **Verificar formularios:**
- Rellena un formulario de prueba
- Verifica que redirige a `https://thecorner.es/thank-you-page-idiomas/`
- Comprueba en tu CRM que llegó el lead con el `origin` correcto

### 3. **Verificar origin dinámico:**
- Accede a `https://thecorner.es/landing/?origin=test`
- Abre la consola del navegador (F12)
- Rellena y envía el formulario
- Verifica en el CRM que el origin sea `test`

### 4. **Verificar recursos (CSS, JS, imágenes):**
- Abre la consola del navegador (F12)
- Ve a la pestaña "Network"
- Recarga la página
- NO deberían aparecer errores 404

---

## 🐛 Solución de problemas

### **Problema: Error 404 al acceder a /landing/**
**Solución**: Verifica que el archivo `.htaccess` se haya subido correctamente. A veces los archivos que empiezan con `.` están ocultos.

### **Problema: Se ve el código HTML en lugar de la página**
**Solución**: Verifica que el archivo se llame exactamente `index.html` (no `index.txt` o `Index.html`).

### **Problema: Los estilos no se cargan**
**Solución**: Verifica las rutas en el HTML. Deben ser relativas:
```html
<link rel="stylesheet" href="css/styles.css">   ✅
<link rel="stylesheet" href="/landing/css/styles.css">   ❌
```

### **Problema: El formulario no envía**
**Solución**: 
1. Verifica que la URL `https://thecorner.es/thank-you-page-idiomas/` exista
2. Comprueba la consola del navegador (F12) para ver errores
3. Verifica que los campos del formulario tengan los nombres correctos

---

## 📱 Pruebas responsivas

Después de subir, prueba en:
- ✅ Chrome/Edge (Desktop)
- ✅ Firefox (Desktop)
- ✅ Safari (Mac/iPhone)
- ✅ Chrome Mobile (Android)

Usa el modo responsive del navegador (F12 → Toggle device toolbar).

---

## 🔐 Seguridad

El archivo `.htaccess` incluye:
- ✅ Ocultar listado de directorios
- ✅ Protección contra clickjacking
- ✅ Headers de seguridad XSS
- ✅ Compresión GZIP para mejor rendimiento
- ✅ Cache para recursos estáticos

---

## 📞 Soporte

Si tienes problemas después de subir:
1. Verifica la consola del navegador (F12)
2. Revisa los logs de error del servidor (cPanel → Error Log)
3. Contacta con tu proveedor de hosting si los archivos no cargan

---

**Última actualización**: Mayo 2026  
**Creado por**: The Corner Digital Team
