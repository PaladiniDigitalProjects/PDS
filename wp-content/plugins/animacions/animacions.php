<?php 

/*
 * @package           GSAP Animations
 * @author            Daniel Paladini
 * @copyright         2024 Paladini Digital Solutions
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       GSAP Scroll Animations
 * Plugin URI:        https://www.paladinidigital.com
 * Description:       Plugin for animate elements on scroll
 * Version:           2.0.0
 * Requires PHP:      7.2
 * Author:            Daniel Paladini
 * Text Domain:       PDS
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */



function load_plugin() {
    wp_enqueue_script('gsap', plugins_url( 'public/js/gsap.min.js', __FILE__ ));
	wp_enqueue_script('gsapScroll', plugins_url( 'public/js/ScrollTrigger.min.js', __FILE__ ));
    wp_enqueue_script('animacions-js', plugins_url( 'public/js/animacionsjs.js', __FILE__ ));

    wp_enqueue_style('animacions-css', plugins_url( 'public/css/animacionscss.css', __FILE__ ));
}
add_action('init','load_plugin');
?>