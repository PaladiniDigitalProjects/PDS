<?php
/**
 * Plugin Name: Accordion 
 * Description: Create an organized and visually appealing FAQ section on your website with the Accordion Block with FAQ Schema option, featuring an instant search function for easy navigation.
 * Author: GutenbergHub
 * Author URI:  https://shop.gutenberghub.com/
 * Version: 1.1.0
 * Requires at least: 6.1
 * Requires PHP: 7.0
 * Text Domain: ghub-accordion
 * Tested up to: 6.3
 *
 * @package GutenberghubAccordion
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access' );
}

if ( ! defined( 'GHA_DIR_PATH' ) ) {
	define( 'GHA_DIR_PATH', \plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'GHA_PLUGIN_URL' ) ) {
	define( 'GHA_PLUGIN_URL', \plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'GHA_PLUGIN_BASE_NAME' ) ) {
	define( 'GHA_PLUGIN_BASE_NAME', \plugin_basename( __FILE__ ) );
}

if ( ! class_exists( 'Gutenberghub_Accordion' ) ) {
	/**
	 * Main plugin class
	 */
	final class Gutenberghub_Accordion {

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			// Gutenberghub SDK
			require_once GHA_DIR_PATH . 'gutenberghub-sdk/loader.php';
			
			// Blocks.
			require_once GHA_DIR_PATH . 'includes/blocks/class-gutenberghub-accordion.php';
			require_once GHA_DIR_PATH . 'includes/blocks/class-gutenberghub-accordion-item.php';
			require_once GHA_DIR_PATH . 'includes/blocks/class-gutenberghub-accordion-content.php';
			require_once GHA_DIR_PATH . 'includes/blocks/class-gutenberghub-accordion-title.php';

			// Core.
			require_once GHA_DIR_PATH . 'includes/class-gutenberghub-accordion-assets.php';


			// Filters
       		add_filter( 'block_categories_all' , array( $this, 'register_category' ));    
		}

	  /**
       * Register custom category
       * 
       */
      public function register_category($categories){
		// Check if "GutenbergHub" category already exists
		foreach ($categories as $category) {
			if ($category['slug'] === 'ghub-products') {
				// "GutenbergHub" category already exists, do not add again
				return $categories;
			}
		}

		// Adding "GutenbergHub" category.
		$categories[] = array(
			'slug'  => 'ghub-products',
			'title' => 'GutenbergHub'
		);

		return $categories;
      }
	}

	new Gutenberghub_Accordion();

}
