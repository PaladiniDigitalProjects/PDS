<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PDS_luge
 * @subpackage PDS_luge/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PDS_luge
 * @subpackage PDS_luge/admin
 * @author     Your Name <email@example.com>
 */
class PDS_luge_Admin {

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
	 * @param      string    $PDS_luge       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $PDS_luge, $version ) {

		$this->PDS_luge = $PDS_luge;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PDS_luge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The PDS_luge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->PDS_luge, plugin_dir_url( __FILE__ ) . 'css/PDS-luge-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PDS_luge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The PDS_luge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->PDS_luge, plugin_dir_url( __FILE__ ) . 'js/PDS-luge-admin.js', array( 'jquery' ), $this->version, false );

	}

}
