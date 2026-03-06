<?php 

/*
 * @package           background Color
 * @author            Daniel Paladini
 * @copyright         2024 Paladini Digital Solutions
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       BKG Background Color
 * Plugin URI:        https://www.paladinidigital.com
 * Description:       Plugin for change entry backgrounds
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Daniel Paladini
 * Text Domain:       PDS
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 /* Main Plugin File */


function load_bkg_plugin() {
    wp_enqueue_script('bkg',  plugins_url( '/public/js/bkg.js', __FILE__ ));
    wp_enqueue_style( 'bkg-css', plugins_url( '/public/css/bkg.css', __FILE__ ));
}
add_action('init','load_bkg_plugin');



/* CHECK ACF */

define( 'MY_ACF_PATH', __DIR__ . '/advanced-custom-fields/' );
define( 'MY_ACF_URL', plugin_dir_url( __FILE__ ) . 'advanced-custom-fields/' );


// Include the ACF plugin.
include_once( MY_ACF_PATH . 'acf.php' );

// Customize the URL setting to fix incorrect asset URLs.
add_filter('acf/settings/url', 'my_acf_settings_url');
function my_acf_settings_url( $url ) {
    return MY_ACF_URL;
}

// Check if ACF free is installed
if ( ! file_exists( WP_PLUGIN_DIR . '/advanced-custom-fields/acf.php' ) ) {
    // Free plugin not installed
    // Hide the ACF admin menu item.
    add_filter( 'acf/settings/show_admin', '__return_false' );
    // Hide the ACF Updates menu
    add_filter( 'acf/settings/show_updates', '__return_false', 100 );
}

/* CREATE ACF FIELD */
add_action( 'acf/include_fields', function() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_66e477ecb1251',
	'title' => 'Background Color',
	'fields' => array(
		array(
			'key' => 'field_66e477eda8bf5',
			'label' => 'Background Color',
			'name' => 'bkg-color',
			'aria-label' => '',
			'type' => 'color_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#FFFFFF',
			'enable_opacity' => 1,
			'return_format' => 'string',
			'allow_in_bindings' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'proyecto',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'partners',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
		),
	),
	'menu_order' => 1,
	'position' => 'side',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	));
});

/* MENU COLOR */

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_66f3b14b8d156',
	'title' => 'Menu Logo Color',
	'fields' => array(
		array(
			'key' => 'field_66f3b14cb48b1',
			'label' => 'Logo y Menu Color',
			'name' => 'menu_logo_color',
			'aria-label' => '',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'#ffffff' => 'White',
				'#000000' => 'Black',
			),
			'default_value' => '#ffffff',
			'return_format' => 'value',
			'multiple' => 0,
			'allow_null' => 0,
			'ui' => 0,
			'ajax' => 0,
			'placeholder' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'proyecto',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'partners',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
		),
	),
	'menu_order' => 1,
	'position' => 'side',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	));
});


add_Action('wp_head','my_custom_css');
function my_custom_css(){
	global $post;
   	$post_id = ( empty( $post->ID ) ) ? get_the_ID() : $post->ID;
	if ( get_field('bkg-color', $post_id) or get_field( 'menu_logo_color', $post_id)) {
		$class_field = get_field('bkg-color', $post_id);
		$logo_field = get_field('menu_logo_color', $post_id);
		echo "<style>body { background:".$class_field.";} header.wp-block-template-part.fullheader { background-color:".$class_field.";}</style>";
		if ($logo_field =='#000000') {
			echo "<style>
			#ico-menu a img {filter:invert(100%);}
			#cerrar a img {filter:invert(100%);}
			.pll-switcher-select {color: #000000;}
			img.custom-logo {filter:invert(100%);}
			span.wp-block-navigation-item__label {filter:invert(100%);}
			.wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open { color: #000000;}
			html.has-modal-open .wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open { color: #000000;}
			html.has-modal-open .wp-block-navigation__responsive-container {background:".$class_field." !important;}
			.wp-block-navigation:not(.has-background) .wp-block-navigation__responsive-container.is-menu-open { background:".$class_field." !important;}
			header.wp-block-template-part ul.wp-block-navigation li:nth-child(2) { filter: invert(100%);}
			header.wp-block-template-part ul.wp-block-navigation li:nth-child(2) a { filter: invert(100%);}
			header.wp-block-template-part ul.wp-block-navigation li:nth-child(2) a:hover { text-decoration:none;}
			</style>";
		} else {
			echo "<style>
			#ico-menu a img {filter:none;}
			#cerrar a img {filter:none;}
			.pll-switcher-select {color: #ffffff;}
			img.custom-logo {filter:none;}
			span.wp-block-navigation-item__label{filter:none;}
			.wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open { color: #ffffff;}
			html.has-modal-open .wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open.wp-block-navigation__responsive-container-open { color: #000000;}
			html.has-modal-open .wp-block-navigation__responsive-container {background:".$class_field." !important;}
			.wp-block-navigation:not(.has-background) .wp-block-navigation__responsive-container.is-menu-open { background:".$class_field." !important;}
			header.wp-block-template-part ul.wp-block-navigation li:nth-child(2) a:hover { text-decoration:none;}
			</style>";	
		}
	}
}


add_Action('admin_head', 'my_custom_admin_css');
function my_custom_admin_css() {
	global $post;
    $post_id = ( empty( $post->ID ) ) ? get_the_ID() : $post->ID;
	if ( get_field( 'bkg-color', $post_id) ) {
		$class_field_css = get_field( 'bkg-color' );
		echo "<style>
		.editor-styles-wrapper { background:".$class_field_css." !important;}
		</style>";
	}
}