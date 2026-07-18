<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://paladinidigital.com
 * @since      1.0.0
 *
 * @package    PDS_luge
 * @subpackage PDS_luge/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two example hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    PDS_luge
 * @subpackage PDS_luge/public
 * @author     Your Name <email@example.com>
 */
class PDS_luge_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $PDS_luge    The ID of this plugin.
	 */
	private $PDS_luge;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $PDS_luge       The name of the plugin.
	 * @param      string    $version        The version of this plugin.
	 */
	public function __construct( $PDS_luge, $version ) {

		$this->PDS_luge = $PDS_luge;
		$this->version  = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		wp_enqueue_style( $this->PDS_luge, plugin_dir_url( __FILE__ ) . 'css/PDS-luge-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		// Ensure bundle.js is loaded AFTER GSAP & ScrollTrigger
		wp_enqueue_script(
			$this->PDS_luge, 
			plugin_dir_url(__FILE__) . 'js/bundle.js', 
			array(),  // ✅ Declare dependencies
			$this->version, 
			true
		);
	
		// Optionally enqueue jQuery if needed
		wp_enqueue_script('pds-jquery', plugin_dir_url(__FILE__) . 'js/jquery-3.7.1.min.js', array(), null, true);
	}
}
