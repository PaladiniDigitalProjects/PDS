document.addEventListener('DOMContentLoaded', function() {
    jQuery(function($){
    // $('body').attr('data-barba', 'wrapper');


        $('figure#back').on('click', function(e){
            e.preventDefault();
            window.history.back();
        });
  
    var mywindow = $(window);
    var mypos = mywindow.scrollTop();

    mywindow.scroll(function() {
    if (mypos > 10) {
        if(mywindow.scrollTop() > mypos) {
            $('header.wp-block-template-part').addClass('headerup');
            $('header.wp-block-template-part').removeClass('fullheader');
            // $('.single-products .apertura').removeClass('fixed');
        } else {
            $('header.wp-block-template-part').removeClass('headerup');
            $('header.wp-block-template-part').addClass('fullheader');
            // $('.single-products .apertura').addClass('fixed');      
        } if (mypos = 0) {
            $('header.wp-block-template-part').removeClass('fullheader');
            }
        }
        mypos = mywindow.scrollTop();
        });
    });    
});


// Contraste del header sobre la imagen de apertura.
// En partners, proyectos y noticias la página abre con una imagen a toda pantalla
// y el header va fijo encima, sin fondo: el logo y los iconos se pierden.
// Mientras el header está sobre esa apertura le ponemos la clase 'header-sobre-media'
// (velo + logo y iconos en blanco); al pasarla, el CSS devuelve el azul original.
(function () {
    var header = document.querySelector('header.wp-block-template-part');
    // Las noticias no envuelven el contenido en <main>: ahí el contenedor es .wp-site-blocks.
    var contenedor = document.querySelector('main') || document.querySelector('.wp-site-blocks');
    if (!header || !contenedor) return;

    // Primer bloque que sirva de apertura: pegado arriba del todo, alto y con imagen.
    // Hay que descartar lo que vive dentro del propio header (el logo también es .wp-block-image).
    var apertura = null;
    var candidatos = contenedor.querySelectorAll('.wp-block-cover, .wp-block-post-featured-image, figure.wp-block-image');

    for (var i = 0; i < candidatos.length; i++) {
        var el = candidatos[i];
        if (header.contains(el)) continue;
        if (el.getBoundingClientRect().top + window.pageYOffset > 20) continue;
        if (el.offsetHeight < window.innerHeight * 0.4) continue;
        if (!el.querySelector('img') && getComputedStyle(el).backgroundImage === 'none') continue;
        apertura = el;
        break;
    }
    if (!apertura) return;

    function actualiza() {
        // La apertura siempre arranca arriba del todo (lo exige la selección de arriba),
        // así que su final en el documento es su propia altura. No se mide su posición
        // a cada paso a propósito: varias de estas imágenes se quedan ancladas al hacer
        // scroll (las noticias son position:fixed, y las de partner se comportan igual
        // pese a ser relative), con lo que su rect.top vale siempre 0 y el final se
        // desplazaría con la página, sin salir nunca de la imagen.
        var fin = apertura.offsetHeight;
        header.classList.toggle('header-sobre-media', window.pageYOffset + header.offsetHeight < fin);
    }

    actualiza();
    window.addEventListener('scroll', actualiza, { passive: true });
    window.addEventListener('resize', actualiza);
    window.addEventListener('load', actualiza); // la imagen puede cambiar de alto al cargar
})();
