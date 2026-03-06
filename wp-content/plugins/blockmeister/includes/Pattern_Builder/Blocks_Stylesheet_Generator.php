<?php


namespace ProDevign\BlockMeister\Pattern_Builder;

use function ProDevign\BlockMeister\blockmeister_license;

/**
 * Generates a dynamic temp stylesheet in the front-end.
 *
 * Class Blocks_Stylesheet_Generator
 * @package ProDevign\BlockMeister\Pattern_Builder
 */
class Blocks_Stylesheet_Generator {


	/**
	 * Constructor for the Blocks_Stylesheet_Generator class
	 */
	public function __construct() {
	}


	/**
	 * Adds generated stylesheet to the document head.
	 * Requires a Pro or Expert license
	 */
	public function init() {
		if ( blockmeister_license()->is_plan( 'pro' ) || blockmeister_license()->is_plan( 'expert' ) ) {
			add_action( 'wp_head', [ $this, 'add_blocks_stylesheet_to_head' ] );
		}
	}


	public function add_blocks_stylesheet_to_head() {
		$stylesheet = $this->generate_blocks_stylesheet();
		if ( ! empty( $stylesheet ) ) {
			$handle = "blockmeister-block-scoped-css";
			echo "<style id='{$handle}'>" . PHP_EOL .
			     esc_html( $stylesheet ) .
			     "</style>" . PHP_EOL;
		}
	}


	private function generate_blocks_stylesheet() {
		global $post;

		if ( ! is_object( $post ) || ! has_blocks( $post ) ) {
			return "";
		}

		if ( $post->post_type === 'blockmeister_pattern') {
			$post_meta = (bool) get_post_meta( $post->ID, '_bm_is_custom_css_enabled', true );
			if ( $post_meta === false ) {
				return "";
			}
		}

		$blocks = parse_blocks( $post->post_content );

		if ( ! is_array( $blocks ) ) {
			return "";
		}

		return self::get_css_from_blocks( $blocks );

	}

	/**
	 * Recursively concatenate custom css of blocks that have bmCSS attribute.
	 *
	 *
	 * @param $blocks
	 *
	 * @return string The concatenated css rules.
	 */
	public static function get_css_from_blocks( $blocks ) {
		$style = '';

		foreach ( $blocks as $block ) {
			if ( isset( $block['attrs'] ) && ! empty( $block['attrs'] ) ) {
				if ( $bm_css = isset( $block['attrs']['bmCSS'] ) && ! empty( $block['attrs']['bmCSS'] ) ? $block['attrs']['bmCSS'] : false ) {
					$maybe_new_style = "\t" . preg_replace( '/ +/', ' ', $bm_css . PHP_EOL );
					if ( $is_new_style = strpos( $style, $maybe_new_style ) === false ) { // only add a particular style once
						$style .= $maybe_new_style;
					}
				}
			}

			if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$style .= self::get_css_from_blocks( $block['innerBlocks'] );
			}
		}

		return $style;
	}

}