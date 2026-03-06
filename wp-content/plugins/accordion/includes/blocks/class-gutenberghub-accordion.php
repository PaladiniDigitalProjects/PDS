<?php
/**
 * Accordion Block
 *
 * @package GutenberghubAccordion
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access' );
}

/**
 * Main class for handling block functionalities.
 */
class Gutenberghub_Accordion_Block {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Main registeration.
	 *
	 * @return void
	 */
	public function register() {
		register_block_type_from_metadata(
			GHA_DIR_PATH . 'dist/accordion-block.json',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

}

new Gutenberghub_Accordion_Block();
