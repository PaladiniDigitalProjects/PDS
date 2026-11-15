<?php
/**
 * Plugin Name:       PDS Color de fons
 * Description:       Color de fondo por contenido (proyectos y partners). Sustituye al antiguo «BKG Background Color» sin depender de ACF: usa el mismo campo `bkg-color`, así que los colores guardados siguen valiendo.
 * Version:           1.0.3
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Paladini Digital Solutions
 * License:           GPL-2.0-or-later
 * Text Domain:       pds-color-fons
 */

defined( 'ABSPATH' ) || exit;

const PDSCF_META  = 'bkg-color';
// Solo proyectos y partners: páginas y entradas siguen el diseño (Daniel, 15/11). Los valores
// antiguos de páginas se quedan en la BBDD, pero no se usan.
const PDSCF_TYPES = array( 'proyecto', 'partners' );

/**
 * Campo del color: meta nativo, visible en la API REST para el panel del editor.
 */
add_action( 'init', function () {
	foreach ( PDSCF_TYPES as $type ) {
		register_post_meta( $type, PDSCF_META, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => '',
			'sanitize_callback' => 'pdscf_sanitize',
			'auth_callback'     => function ( $allowed, $key, $post_id ) { return current_user_can( 'edit_post', $post_id ); },
		) );
	}
} );

/**
 * Acepta #rgb, #rrggbb, rgb() y rgba(); cualquier otra cosa se guarda vacía.
 */
function pdscf_sanitize( $value ) {
	$value = trim( (string) $value );
	if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value ) ) {
		return strtolower( $value );
	}
	if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/i', $value ) ) {
		return preg_replace( '/\s+/', '', strtolower( $value ) );
	}
	return '';
}

/**
 * Color → [r, g, b, a] o null.
 */
function pdscf_rgba( $color ) {
	$color = pdscf_sanitize( $color );
	if ( '' === $color ) {
		return null;
	}
	if ( '#' === $color[0] ) {
		$hex = substr( $color, 1 );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ), 1.0 );
	}
	preg_match_all( '/[\d.]+/', $color, $m );
	$n = array_map( 'floatval', $m[0] );
	return array( (int) $n[0], (int) $n[1], (int) $n[2], isset( $n[3] ) ? $n[3] : 1.0 );
}

/**
 * Color de fondo que aplica a la página actual, o '' si no hay (o es transparente).
 */
function pdscf_current_color() {
	if ( ! is_singular( PDSCF_TYPES ) ) {
		return '';
	}
	$color = (string) get_post_meta( get_queried_object_id(), PDSCF_META, true );
	$rgba  = pdscf_rgba( $color );
	if ( ! $rgba || $rgba[3] <= 0 ) {
		return '';
	}
	return pdscf_sanitize( $color );
}

/**
 * ¿Fondo oscuro? Luminancia relativa (WCAG) por debajo de 0.179: a partir de ahí el blanco
 * contrasta más que el negro.
 */
function pdscf_is_dark( $color ) {
	$rgba = pdscf_rgba( $color );
	if ( ! $rgba ) {
		return false;
	}
	$lin = function ( $c ) {
		$c /= 255;
		return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	};
	$l = 0.2126 * $lin( $rgba[0] ) + 0.7152 * $lin( $rgba[1] ) + 0.0722 * $lin( $rgba[2] );
	return $l < 0.179;
}

/**
 * Clases en el body: `pds-color-fons` y, si el color es oscuro, `pds-color-fons--fosc`.
 */
add_filter( 'body_class', function ( $classes ) {
	$color = pdscf_current_color();
	if ( $color ) {
		$classes[] = 'pds-color-fons';
		if ( pdscf_is_dark( $color ) ) {
			$classes[] = 'pds-color-fons--fosc';
		}
	}
	return $classes;
} );

/**
 * Estilos en la página: el fondo (solo en modo claro) y, con fondo oscuro, logo y
 * menú del header en blanco, como cuando el header va sobre una imagen.
 * Prioridad alta para que vaya después de las hojas del tema.
 */
add_action( 'wp_head', function () {
	$color = pdscf_current_color();
	if ( ! $color ) {
		return;
	}
	$css = 'html:not(.pds-dark) body.pds-color-fons{background-color:' . $color . ' !important;background-image:none !important}';
	if ( pdscf_is_dark( $color ) ) {
		$css .= 'html:not(.pds-dark) body.pds-color-fons--fosc header .pds-logo__circle,'
			. 'html:not(.pds-dark) body.pds-color-fons--fosc header .pds-logo__letter{fill:#fff}'
			. 'html:not(.pds-dark) body.pds-color-fons--fosc header img:not(.custom-logo){filter:brightness(0) invert(1)}'
			. 'html:not(.pds-dark) body.pds-color-fons--fosc header .pds-tema,'
			. 'html:not(.pds-dark) body.pds-color-fons--fosc header .wp-block-navigation__responsive-container-open{color:#fff}'
			. 'html:not(.pds-dark) body.pds-color-fons--fosc header .wp-block-navigation:has(.wp-block-navigation__responsive-container:not(.is-menu-open)),'
			. 'html:not(.pds-dark) body.pds-color-fons--fosc header .wp-block-navigation__responsive-container:not(.is-menu-open) .wp-block-navigation-item__content{color:#fff !important}'
			// Texto del contenido sin color propio, en blanco (los bloques con color elegido lo mantienen).
			. 'html:not(.pds-dark) body.pds-color-fons--fosc .wp-site-blocks>:not(header):not(footer) :where(h1,h2,h3,h4,h5,h6,p,li,figcaption,.wp-block-post-title,.wp-block-post-title a):not(.has-text-color){color:#fff}';
	}
	echo '<style id="pds-color-fons">' . $css . "</style>\n"; // $color ya está saneado.
}, 100 );

/**
 * Panel en el editor (barra lateral del documento).
 */
add_action( 'enqueue_block_editor_assets', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, PDSCF_TYPES, true ) ) {
		return;
	}
	wp_enqueue_script(
		'pds-color-fons-editor',
		plugins_url( 'editor.js', __FILE__ ),
		array( 'wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-i18n' ),
		filemtime( __DIR__ . '/editor.js' ),
		true
	);
} );
