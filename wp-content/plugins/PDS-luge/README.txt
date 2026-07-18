=== Plugin Name ===
Contributors: RicardPDS
Tags: PDS
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

# Cómo usar Animaciones con GSAP, ScrollTrigger y Luge en Gutenberg Blocks

Este tutorial está diseñado para usuarios medios que desean añadir animaciones avanzadas a su sitio WordPress usando Gutenberg Blocks. Aprenderás a aplicar clases y atributos directamente en los bloques para activar:

- Transiciones de página (Luge)
- Animaciones de revelado (Reveal)
- Animaciones controladas por scroll (ScrollTrigger)
- Animaciones de fotogramas por scroll (Frame-by-frame)
- Animaciones de vídeo con scroll
- Animaciones de texto y contadores

---

## 1. Instalación y Configuración Básica

1. **Instala el plugin** PDS-Luge (o tu plugin que contenga el script):
    - Sube `PDS-luge.php` a `/wp-content/plugins/`
    - Actívalo en el panel de Plugins de WordPress.
2. **Añade el hook en tu plantilla** (por ejemplo en `header.php`):
    
    ```php
    <?php do_action('PDS_luge_hook'); ?>
    
    ```
    
3. **Registra y carga las librerías** en tu bundle JS (usando npm o tu compilación):
    
    ```bash
    npm install gsap @waaark/luge
    
    ```
    
    ```
    import { gsap } from 'gsap';
    import { ScrollTrigger } from 'gsap/ScrollTrigger';
    import luge from '@waaark/luge';
    
    gsap.registerPlugin(ScrollTrigger);
    window.luge = luge;
    
    ```
    
4. **Asegúrate** de que tu JavaScript se carga en el frontend tras `DOMContentLoaded`.

---

## 2. Añadir Transiciones de Página

**Objetivo**: Definir animaciones de entrada y salida cuando el usuario navega entre páginas.

### Paso a paso:

1. En tu archivo JS, define:
    
    ```
    luge.transition.add('in', 'default', (page, done) => {
      gsap.from(page, { opacity: 0, backgroundColor: '#fff', duration: 0.3, ease: 'power1.in', onComplete: done });
    });
    luge.transition.add('out', 'default', (page, done) => {
      gsap.to(page, { opacity: 0, duration: 0.5, ease: 'power1.out', onComplete: done });
    });
    
    ```
    
2. **Páginas específicas**: añade `data-lg-page="home"` o `menu` al bloque `<body>` o a un bloque contenedor en el HTML del tema.
    - Abre el editor de plantillas de Gutenberg.
    - Selecciona el bloque **Plantilla** (o un bloque HTML) y, en "Atributos adicionales", añade:
        - **Clase CSS**: `page-home`
        - **HTML Attribute**: `data-lg-page="home"`
3. Repite para cada página (ej. `data-lg-page="menu"`).

---

## 3. Animaciones de Revelado (Reveal)

**Objetivo**: Animar elementos en el viewport usando data-attributes.

### Uso:

1. Define tu configuración en el JS:
    
    ```
    luge.reveal.add('in', 'tlsReveal', el => gsap.from(el, { x: '-140vw', duration: 1.5, ease: 'power2.out' }));
    
    ```
    
2. En Gutenberg, coloca el bloque deseado (párrafo, imagen, etc.) y en "HTML atribs":
    - **Clase CSS**: opcional (p.ej. `mi-elemento`)
    - **HTML Attribute**: `data-lg-reveal="tlsReveal"`
3. ¡Guarda y prueba! El elemento se deslizará desde la izquierda.

---

## 4. Animaciones Controladas por Scroll

Puedes animar propiedades (posición, opacidad, escala) al hacer scroll.

### Ejemplo: Texto en movimiento

1. JS básico:
    
    ```
    gsap.utils.toArray('.moveText').forEach(el => {
      gsap.to(el, { x: '0%', ease: 'power1.in', scrollTrigger: { trigger: el, start: 'top 96%', end: 'top 65%', scrub: true } });
    });
    
    ```
    
2. En Gutenberg:
    - Bloque: Párrafo o Heading.
    - CSS Class: `moveText`
3. Publica y haz scroll; verás el texto deslizar.

### Variantes:

- `.moveTextLeft` (texto desde la izquierda)
- `.moveFade` (fade in)
- `.moveTextFade` (desplaza + fade)

---

## 5. Animación de Fotogramas (Frame-by-Frame)

Crea secuencias de imágenes que varían según el scroll.

1. Genera tus frames (por FFmpeg) y súbelos:
    
    ```bash
    ffmpeg -i video2_slow.mp4 -vf fps=60 -c:v libwebp frame_%04d.webp
    
    ```
    
2. JS:
    
    ```
    const frames = generateFrameUrls('/uploads/2025/02', 1158);
    preloadImages(frames).then(() => {
      initScrollFrameAnimation('.animation-container', frames);
    });
    
    ```
    
3. Gutenberg:
    - Bloque: HTML personalizado o Figura.
    - Dentro: `<img>` sin `srcset`.
    - Atributo HTML: `class="animation-container"`

---

## 6. Vídeos Controlados por Scroll

Reproduce y pausa el vídeo según scroll.

1. JS: usa `initVideoScrollTrigger('#miVideo', '#miContainer', '-50%', '-50%')`.
2. HTML/Gutenberg:
    
    ```html
    <div id="miContainer">
      <video id="miVideo" src="video_scrub.mp4" data-top-trans="-50%" data-left-trans="-50%" muted playsinline></video>
    </div>
    
    ```
    

---

## 7. Contadores Animados

### Enteros:

```
gsap.utils.toArray('.numberCount').forEach(el => {
  const end = parseInt(el.textContent, 10);
  el.textContent = '0';
  gsap.to(el, { textContent: end, duration: 2, ease: 'power1.out', snap: { textContent: 1 }, scrollTrigger: { trigger: el, start: 'top 80%', once: true } });
});

```

- Gutenberg: **CSS Class**: `numberCount`.

### Decimales (`.FloatCount`): mismo concepto pero formateando con dos decimales.

---

## 8. Resumen de las **clases CSS** y **data-attributes**

que puedes usar en tus bloques de Gutenberg, junto con su descripción:

| Clase / Atributo | Tipo | Descripción |
| --- | --- | --- |
| `.moveText` | Clase CSS | Desplaza el texto horizontalmente desde la derecha al hacer scroll. |
| `.moveTextLeft` | Clase CSS | Desplaza el texto horizontalmente desde la izquierda al hacer scroll. |
| `.moveFade` | Clase CSS | Aplica un fade-in (opacidad de 0 a 1) al elemento al hacer scroll. |
| `.moveTextFade` | Clase CSS | Combina desplazamiento horizontal y fade-in. |
| `.numberCount` | Clase CSS | Anima un contador de números enteros desde 0 hasta su valor final. |
| `.FloatCount` | Clase CSS | Anima un contador numérico con decimales (2 decimales). |
| `.animation-container` | Clase CSS | Contenedor para animación frame-by-frame (inicia la secuencia de imágenes). |
| `.animation-container2` | Clase CSS | Segundo contenedor alternativo para otra secuencia frame-by-frame. |
| `data-lg-page="home"` | Data-attribute Luge | Identifica la página como "home" para transiciones específicas. |
| `data-lg-page="menu"` | Data-attribute Luge | Identifica la página como "menu" para transiciones específicas. |
| `data-lg-reveal="tlsReveal"` | Data-attribute Luge | Aplica la animación de revelado configurada en `tlsReveal`. |
| `data-lg-reveal="tlsLeftReveal"` | Data-attribute Luge | Revela el elemento desde la derecha (configuración `tlsLeftReveal`). |
| `data-lg-reveal="tlsToTop"` | Data-attribute Luge | Revela el elemento desplazándolo hacia arriba (config `tlsToTop`). |
| `data-lg-reveal="tlsScale"` | Data-attribute Luge | Revela con efecto de escala (configuración `tlsScale`). |
| `data-lg-reveal="tlsFade"` | Data-attribute Luge | Revela con fade-in (configuración `tlsFade`). |
| `data-top-trans="–50%"` | Data-attribute JS | Valor X de inicio para animaciones de vídeo con scroll (initVideo). |
| `data-left-trans="–50%"` | Data-attribute JS | Valor Y de inicio para animaciones de vídeo con scroll (initVideo). |

---