# ANHQV Tech v2.1.0 — Guía de actualización

## ✅ Qué se ha corregido en esta versión

| # | Fix | Archivo | Impacto |
|---|-----|---------|---------|
| 1 | Google Fonts asíncrono (no bloquea render) | functions.php | ⬆️ LCP / Core Web Vitals |
| 2 | Canonical URL en todas las páginas | functions.php | ⬆️ SEO (evita duplicados) |
| 3 | Meta description + Open Graph bug corregido | functions.php | ⬆️ CTR en Google |
| 4 | Schema.org completo (wordCount, breadcrumb...) | functions.php | ⬆️ Rich Results |
| 5 | Posts relacionados con caché (transients) | functions.php | ⬆️ Velocidad BD |
| 6 | Tiempo de lectura: 238 ppm + imágenes | functions.php | ✔️ Precisión |
| 7 | Social share: Twitter → X (URL correcta) | functions.php | ✔️ Fix funcional |
| 8 | Sticky header oculto solo en móvil | main.js | ⬆️ UX escritorio |
| 9 | Animaciones: primeras 4 tarjetas no se ocultan | main.js | ⬆️ LCP score |
| 10 | Sidebar: layout de 1 columna si está vacío | sidebar.php + index.php | ⬆️ UX |
| + | Tabla de contenidos (TOC) automática | main.js + style-additions.css | ⬆️ Engagement |
| + | Lightbox mejorado con animación y cierre | main.js + style-additions.css | ⬆️ UX |


---

## 📦 Instalación paso a paso

### Paso 1 — Haz una copia de seguridad
Antes de nada, haz backup de tu tema actual desde Plesk o con un plugin como Duplicator.

### Paso 2 — Sube los archivos por FTP/SFTP (o desde Plesk)

La ruta base es `/wp-content/themes/anhqv-tech/`

Sube y **reemplaza** estos archivos:
```
functions.php     → /wp-content/themes/anhqv-tech/functions.php
main.js           → /wp-content/themes/anhqv-tech/js/main.js
sidebar.php       → /wp-content/themes/anhqv-tech/sidebar.php
index.php         → /wp-content/themes/anhqv-tech/index.php
```

### Paso 3 — Añadir el nuevo CSS

Abre tu `style.css` actual y **pega al final** todo el contenido de `style-additions.css`.
NO reemplaces el style.css completo, solo añade al final.

### Paso 4 — Limpiar caché

Desde tu panel de WordPress o Plesk:
1. Limpia la caché de WordPress (si usas WP Super Cache, W3 Total Cache, etc.)
2. Limpia la caché de Cloudflare si la usas
3. En el navegador: Ctrl+Shift+R para forzar recarga sin caché

---

## 🧪 Verificar que todo funciona

### Test SEO (Meta tags)
1. Ve a un artículo tuyo
2. Haz clic derecho → Ver código fuente
3. Busca `<meta name="description"` → debe aparecer con tu excerpt
4. Busca `og:title` → debe tener el título del artículo
5. Busca `application/ld+json` → debe haber un JSON grande con `wordCount`, `breadcrumb`, etc.

### Test Google Fonts (asíncrono)
En el código fuente busca:
```html
<link rel="preload" as="style" ... onload="this.onload=null;this.rel='stylesheet'">
```
Si lo ves, está cargando de forma no bloqueante. ✅

### Test TOC
Entra en un artículo con 3 o más subtítulos (h2/h3).
Debe aparecer un recuadro azul de "📋 Contenido" al inicio del artículo.

### Test Schema.org
1. Ve a: https://validator.schema.org/
2. Pega la URL de un artículo
3. Debe detectar tipo "Article" con todos los campos rellenos

### Test Core Web Vitals
1. Ve a: https://pagespeed.web.dev/
2. Pon la URL de tu home y de un artículo
3. El LCP debería mejorar respecto a la versión anterior

---

## ❓ Preguntas frecuentes

**¿El TOC aparece en todos los artículos?**
Solo en artículos con 3 o más headings h2/h3. En artículos cortos no aparece.

**¿Los posts relacionados se actualizan automáticamente?**
Sí. La caché se invalida cada 12 horas y también cuando guardas/actualizas el propio post.

**¿Necesito algún plugin adicional?**
No. Todas las mejoras son nativas del tema.

**¿Qué pasa con el sidebar si lo activo más adelante?**
En cuanto añadas widgets al "Sidebar Home" desde Apariencia → Widgets, el layout vuelve automáticamente a 2 columnas.
