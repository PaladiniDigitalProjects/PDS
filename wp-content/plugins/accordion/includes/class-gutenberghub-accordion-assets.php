<?php
/**
 * Admin Class.
 *
 * @package GutenberghubAccordion
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access' );
}

/**
 * Main class for handling related assets and functionalities.
 */
class Gutenberghub_Accordion_Block_Assets {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'load_block_assets' ) );
	}

	/**
	 * All block editor assets should be loaded here.
	 *
	 * @return void
	 */
	public function register_block_assets() {
		wp_register_script(
			'gutenberghub-accordion-frontend',
			GHA_PLUGIN_URL . 'dist/frontend.js',
			array(),
			uniqid(),
			false
		);

		wp_register_style(
			'gutenberghub-accordion-frontend-style',
			GHA_PLUGIN_URL . 'dist/frontend.css',
			array(),
			uniqid(),
		);
	}
/**
	 * Provides the asset metadata.
	 * 
	 * @param string $asset_name - Asset to check for.
	 * 
	 * @return array - Metadata.
	 */
	public function get_asset_metadata($asset_name)
	{

		$default_metadata = array(
			'version'	   => 'initial',
			'dependencies' => array()
		);

		$asset_path = GHA_DIR_PATH . 'dist/' . $asset_name . '.asset.php';

		return is_readable($asset_path) ? require $asset_path : $default_metadata;
	}


	/**
	 * Load BLock Assets.
	 *
	 * @return void
	 */
	public function load_block_assets()
	{
		$editor_script_metadata = $this->get_asset_metadata('gutenberg');

		wp_register_script(
			'gutenberghub-accordion-gutenberg-script',
			GHA_PLUGIN_URL . 'dist/gutenberg.js',
			$editor_script_metadata['dependencies'],
			$editor_script_metadata['version'],
		);

		wp_register_style(
			'gutenberghub-accordion-gutenberg-style',
			GHA_PLUGIN_URL . 'dist/gutenberg.css',
			array(),
			uniqid()
		);
	}
}

new Gutenberghub_Accordion_Block_Assets();
