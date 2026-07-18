<?php
/**
 * Plugin Name:       PDS-luge
 * Plugin URI:        https://paladinidigital.com
 * Description:       Animations & transitions using Luge, with a Gutenberg block that uses block attributes.
 * Version:           1.1.1
 * Author:            Ricard PDS
 * Author URI:        https://paladinidigital.com
 * License:           GPL-2.0+
 * Text Domain:       PDS-luge
 * Domain Path:       /languages
 * 
 */

// Abort if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define the plugin version.
 */
define( 'PDS_luge_VERSION', '1.1.1' );

/**
 * Include activation, deactivation, and core files.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-PDS-luge-activator.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-PDS-luge-deactivator.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-PDS-luge.php';

/**
 * Activation and deactivation hooks.
 */
register_activation_hook( __FILE__, 'activate_PDS_luge' );
function activate_PDS_luge() {
	PDS_luge_Activator::activate();
}

register_deactivation_hook( __FILE__, 'deactivate_PDS_luge' );
function deactivate_PDS_luge() {
	PDS_luge_Deactivator::deactivate();
}

/**
 * Kick off the plugin.
 */
function run_PDS_luge() {
	$plugin = new PDS_luge();
	$plugin->run();
}
run_PDS_luge();



function pds_luge_gsap_script(){
    // The core GSAP library
    wp_enqueue_script( 'gsap-js', 'https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js', array(), false, true );
    // ScrollTrigger - with gsap.js passed as a dependency
    wp_enqueue_script( 'gsap-st', 'https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js', array('gsap-js'), false, true );
   
}

add_action( 'wp_enqueue_scripts', 'pds_luge_gsap_script' );
/**
 * Enqueue public scripts and styles.
 *
 * Loads:
 * - Luge.css for animations
 * - A public CSS file for additional styling
 * - A bundled JS file (bundle.js) that includes Luge from node_modules and your custom code.
 */
function pds_luge_enqueue_assets() {
    $plugindir = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'luge-css', $plugindir . 'public/css/luge.css', array(), '1.0.0' );
    wp_enqueue_style( 'public-luge-css', $plugindir . 'public/css/PDS-luge-public.css', array(), '1.0.0' );
    
   
}
add_action( 'wp_enqueue_scripts', 'pds_luge_enqueue_assets' );


/*
 * REGISTER GUTENBERG BLOCK
 */
add_action( 'init', 'pds_luge_register_block' );
function pds_luge_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$plugin_dir_url = plugin_dir_url( __FILE__ );
	
	wp_register_script(
		'PDS-luge-block-editor',
		$plugin_dir_url . 'blocks/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'blocks/block.js' )
	);
	
	wp_register_style(
		'PDS-luge-block-editor-style',
		$plugin_dir_url . 'blocks/editor.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'blocks/editor.css' )
	);
	// Register the block.
	register_block_type( 'pds/luge-block', array(
		'editor_script'   => 'PDS-luge-block-editor',
		'editor_style'    => 'PDS-luge-block-editor-style',
		'style'           => 'PDS-luge-public',
		'attributes'      => array(
			'message' => array(
				'type'    => 'string',
				'default' => 'Luge Animations Activated!',
			),
		),
		'render_callback' => 'pds_luge_render_block',
	) );

// Register the video block with additional translation attributes.
register_block_type( 'pds/video-luge-block', array(
	'editor_script'   => 'PDS-luge-block-editor',
	'editor_style'    => 'PDS-luge-block-editor-style',
	'style'           => 'PDS-luge-public',
	'attributes'      => array(
		'videourl' => array(
			'type'    => 'string',
			'default' => 'http://lolliipop.paladinidigital.com/wp-content/uploads/2025/02/escen1_scrub.mp4',
		),
		'leftTrans' => array(
			'type'    => 'string',
			'default' => '-50%',
		),
		'topTrans' => array(
			'type'    => 'string',
			'default' => '-50%',
		),
	),
	'render_callback' => 'pds_video_luge_render_block',
) );
}


/**
 * Render callback for the Gutenberg block.
 *
 * @param array $attributes The block attributes.
 * @return string HTML markup for the block.
 */
function pds_luge_render_block( $attributes ) {
	$message = isset( $attributes['message'] ) ? $attributes['message'] : '';
	return '<div class="pds-luge-block" data-luge="true" data-lg-reveal="fade">
		<p>' . esc_html( $message ) . '</p>
	</div>';
}


function pds_video_luge_render_block( $attributes ) {
  
    $video_url = isset( $attributes['videourl'] ) ? esc_url( $attributes['videourl'] ) : 'http://lolliipop.paladinidigital.com/wp-content/uploads/2025/02/video_scrub.mp4';
    $leftTrans = isset( $attributes['leftTrans'] ) ? esc_attr( $attributes['leftTrans'] ) : '-50%';
    $topTrans  = isset( $attributes['topTrans'] ) ? esc_attr( $attributes['topTrans'] ) : '-50%';
	$classname = isset( $attributes['className'] )  ? esc_attr( $attributes['className'] ) : '';
    return ' 
        <video 
			class="' . $classname . '"
			data-left-trans="' . $leftTrans . '" 
			data-top-trans="' . $topTrans . '"
            src="' . $video_url . '" 
            playsinline="true"
            webkit-playsinline 
            muted="true"
            preload="auto" 
         >
        </video> ';
}

