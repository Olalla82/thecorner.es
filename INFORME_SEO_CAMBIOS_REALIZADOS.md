# Informe SEO - Cambios realizados en thecorner.es

Fecha: 7 de septiembre de 2026
Proyecto local: `C:\thecorner.es\public_html`
Repositorio: `https://github.com/Olalla82/thecorner.es.git`

## Resumen ejecutivo

Durante la revisión SEO se han corregido varios puntos técnicos que afectaban al rastreo, la indexación y la estabilidad de las páginas de cursos. La web combina WordPress con una parte custom en `/cursos`, por lo que los cambios se han realizado de forma acotada para no interferir con el funcionamiento general de WordPress.

Los trabajos principales han sido:

- Corrección de una redirección global de errores 404 que podía confundir a Google.
- Creación y ajuste del `robots.txt` de raíz.
- Inclusión del sitemap custom de cursos dentro de las directivas SEO.
- Corrección de la lógica del sitemap de `/cursos` para que siga la misma lógica de publicación que el catálogo.
- Mejora de caché del CSS en páginas de detalle de curso.
- Generación más segura del JSON-LD `Course`.
- Añadida tira de logos subvencionada en el footer de los listados de cursos.

## 1. Corrección de redirección global 404

### Problema detectado

En el tema hijo de WordPress existía una función que redirigía cualquier error 404 hacia una página de cursos. Esto es problemático para SEO porque una URL inexistente no debe responder como si fuera una página válida.

Riesgos SEO:

- Google podía interpretar URLs inexistentes como soft 404.
- Se podía desperdiciar presupuesto de rastreo.
- Se dificultaba la detección real de errores de URLs.
- Podía generar señales de indexación confusas.

### Cambio realizado

Se eliminó la redirección automática de 404.

Archivo afectado:

- `C:\thecorner.es\public_html\wp-content\themes\astra-child\functions.php`

Commit:

- `3e296eae Fix 404 handling and add root robots`

### Resultado esperado

Las URLs inexistentes deben devolver un 404 real, y no redirigir automáticamente a cursos.

## 2. Robots.txt de raíz

### Problema detectado

El sitio no tenía una configuración clara en raíz para declarar correctamente los sitemaps relevantes. Además, el proyecto tiene dos sistemas de contenido:

- WordPress, gestionado parcialmente por Rank Math.
- Cursos custom bajo `/cursos`.

### Cambio realizado

Se dejó configurado `robots.txt` con directivas estándar para WordPress y con declaración de los dos sitemaps importantes.

Archivo afectado:

- `C:\thecorner.es\public_html\robots.txt`

Contenido esperado:

```txt
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://thecorner.es/sitemap_index.xml
Sitemap: https://thecorner.es/cursos/sitemap-index.xml
```

Commits:

- `3e296eae Fix 404 handling and add root robots`
- `cb0a2f10 Update root robots sitemap directives`

### Resultado esperado

Google puede descubrir tanto el sitemap principal de WordPress como el sitemap custom de cursos.

## 3. Sitemap custom de cursos

### Problema detectado

El sitemap de `/cursos/sitemap.xml` usaba una lógica antigua basada principalmente en `gen_cursos`. Esto no coincidía con la lógica real del catálogo, que en muchos casos publica grupos (`gen_grups`) y no solo cursos padre.

Consecuencias:

- El sitemap podía omitir cursos/grupos visibles en el catálogo.
- El sitemap podía no representar correctamente FOAP y CONSORCI.
- Google recibía una imagen incompleta del catálogo.

### Cambio realizado

Se actualizó `sitemap.php` para que use una lógica alineada con el catálogo:

- Publica grupos cuando `gen_grups.mostrar_web = 1`.
- Para FOAP, usa el nombre del módulo desde `soc_moduls` cuando aplica.
- Publica el curso padre solo cuando corresponde: `gen_cursos.mostrar_web = 1` y no existe grupo publicado activo asociado.
- Genera URLs en español y catalán.
- Mantiene alternates `hreflang` entre `/cursos/slug` y `/cursos/ca/slug`.
- Mantiene el índice `/cursos/sitemap-index.xml` apuntando a `/cursos/sitemap.xml`.

Archivo afectado:

- `C:\thecorner.es\public_html\cursos\sitemap.php`

Commit:

- `abf2e4ba Align courses sitemap with catalog visibility`

### Resultado validado

En local, el sitemap pasó de listar muy pocas URLs a generar 44 URLs, incluyendo cursos/grupos reales del catálogo.

En producción se verificó que `/cursos/sitemap.xml` ya devuelve más URLs, incluyendo cursos como:

- `gestio-operativa-de-tresoreria`
- `enregistrament-de-dades`
- `excel-inicial-per-a-persones-amb-certificat-de-discapacitat`
- `angles-n2`
- `tiktok-per-a-empreses`

## 4. Caché del CSS en páginas de detalle de curso

### Problema detectado

En las páginas de detalle de curso se estaba cargando CSS con `?v=<?= time() ?>`. Esto cambia en cada carga y evita que navegador y Google cacheen correctamente el CSS.

Impacto SEO/Core Web Vitals:

- Peor aprovechamiento de caché.
- Más solicitudes repetidas.
- Posible impacto negativo en rendimiento percibido.

### Cambio realizado

Se sustituyó `time()` por una versión basada en `filemtime()`. Así, el CSS solo cambia de versión cuando cambia realmente el archivo.

Archivos afectados:

- `C:\thecorner.es\public_html\cursos\curso.php`
- `C:\thecorner.es\public_html\cursos\curso-ca.php`

Commit:

- `c4a37823 Fix lightweight SEO technical issues`

### Resultado esperado

Mejor comportamiento de caché sin perder actualización automática cuando se modifica el CSS.

## 5. JSON-LD Course generado de forma segura

### Problema detectado

El schema `Course` se generaba interpolando strings manualmente. Si un título o descripción contenía comillas, saltos raros o caracteres especiales, podía romperse el JSON-LD.

Riesgo SEO:

- Datos estructurados inválidos.
- Posibles errores en Rich Results Test o Search Console.
- Pérdida de señales semánticas para Google.

### Cambio realizado

Se cambió la generación del JSON-LD para usar `json_encode()` con opciones adecuadas.

Archivos afectados:

- `C:\thecorner.es\public_html\cursos\curso.php`
- `C:\thecorner.es\public_html\cursos\curso-ca.php`

Commit:

- `c4a37823 Fix lightweight SEO technical issues`

### Resultado esperado

El schema es más robusto frente a caracteres especiales y menos propenso a romperse.

## 6. Footer con logos de formación subvencionada

### Objetivo

Añadir la tira de logos institucionales al footer de los listados de cursos subvencionados para reforzar contexto, confianza y cumplimiento comunicativo.

### Cambio realizado

Se añadió la imagen de logos al proyecto y se insertó en el footer de los dos listados.

Archivos afectados:

- `C:\thecorner.es\public_html\cursos\cursos-gratuitos.php`
- `C:\thecorner.es\public_html\cursos\cursos-gratuitos-ca.php`
- `C:\thecorner.es\public_html\cursos\assets\css\cursos-gratuitos.css`
- `C:\thecorner.es\public_html\cursos\assets\css\cursos-gratuitos-ca.css`
- `C:\thecorner.es\public_html\cursos\assets\img\logos-subvencionada-2026.jpg`

Commit:

- `913e6274 Add subsidized program logos to course catalog footer`

### Detalles técnicos

- Imagen servida desde `/cursos/assets/img/logos-subvencionada-2026.jpg`.
- Carga con `loading="lazy"` y `decoding="async"`.
- Dimensiones declaradas para reducir desplazamientos de layout.
- CSS responsive en las hojas propias de ES y CA.
- Versionado del CSS de catálogo con `filemtime()` para evitar caché obsoleta tras despliegue.

## Validaciones realizadas

Se realizaron estas comprobaciones en local:

- Lint PHP correcto en:
  - `cursos-gratuitos.php`
  - `cursos-gratuitos-ca.php`
  - `curso.php`
  - `curso-ca.php`
  - `sitemap.php`
- Sitemap XML generado correctamente.
- Comprobación de que el sitemap custom genera más URLs y refleja cursos/grupos publicados.
- Comprobación HTTP local de los listados ES y CA con respuesta `200`.
- Comprobación HTTP local de la imagen de logos con respuesta `200`.
- Revisión de estado Git limpio tras commits.

## Commits relacionados

```txt
913e6274 Add subsidized program logos to course catalog footer
cb0a2f10 Update root robots sitemap directives
abf2e4ba Align courses sitemap with catalog visibility
3e296eae Fix 404 handling and add root robots
c4a37823 Fix lightweight SEO technical issues
```

## Checklist de validación en producción

Tras subir los archivos, comprobar:

1. `https://thecorner.es/robots.txt`

Debe incluir:

```txt
Sitemap: https://thecorner.es/sitemap_index.xml
Sitemap: https://thecorner.es/cursos/sitemap-index.xml
```

2. `https://thecorner.es/cursos/sitemap-index.xml`

Debe responder XML y enlazar a:

```txt
https://thecorner.es/cursos/sitemap.xml
```

3. `https://thecorner.es/cursos/sitemap.xml`

Debe listar los cursos publicados del catálogo custom, no solo unos pocos cursos.

4. `https://thecorner.es/cursos/cursos-gratuitos`

Debe cargar correctamente el catálogo y mostrar la tira de logos en el footer.

5. `https://thecorner.es/cursos/cursos-gratuitos-ca`

Debe cargar correctamente la versión catalana y mostrar la tira de logos en el footer.

6. Probar varias fichas de curso desde el catálogo.

Las URLs limpias deben resolver correctamente y no devolver 404 si el curso/grupo está publicado.

## Observaciones pendientes recomendadas

Quedan mejoras SEO aconsejables para siguientes fases:

- Revisar canonical, `hreflang`, `lang` y `og:locale` en páginas de detalle catalanas.
- Validar automáticamente que todas las URLs del sitemap custom responden `200` en producción.
- Revisar páginas técnicas/admin/test/cache bajo `/cursos` para evitar indexación accidental.
- Optimizar imágenes de tarjetas y hero para mejorar Core Web Vitals.
- Enriquecer el schema `Course` con más campos si la base de datos ofrece datos fiables.

## Archivos a tener especialmente controlados en despliegues

```txt
/cursos/curso.php
/cursos/curso-ca.php
/cursos/cursos-gratuitos.php
/cursos/cursos-gratuitos-ca.php
/cursos/sitemap.php
/cursos/assets/css/curso-shared.css
/cursos/assets/css/cursos-gratuitos.css
/cursos/assets/css/cursos-gratuitos-ca.css
/cursos/assets/img/logos-subvencionada-2026.jpg
/robots.txt
/wp-content/themes/astra-child/functions.php
```