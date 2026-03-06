<?php
/**
 * OBSOLETE: remove before final dev commit!!!
 */

namespace ProDevign\BlockMeister\Pattern_Builder;

class Render_Block_Filter_OBSOLETE {


	/**
	 * Constructor for the Blocks_Stylesheet_Generator class
	 */
	public function __construct() {
	}


	/**
	 * Adds generated stylesheet to the document head.
	 */
	public function init() {
		add_filter( 'render_block', [ $this, 'on_render_block_maybe_inject_data_bm_block_id' ], 10, 2 );
	}

	/**
	 * Filters the content of a single block:
	 *
	 * If the block has custom css then we need to inject the related id attribute.
	 * We choose not to add that via the block editor 'blocks.getSaveContent.extraProps' filter
	 * in order to prevent invalidation of existing post blocks.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array $block The full block, including name and attributes.
	 *
	 * @return false|string
	 */
	public function on_render_block_maybe_inject_data_bm_block_id( $block_content, $block ) {
		if ( isset( $block['attrs']['bmID'] ) && ! empty( $block['attrs']['bmID'] ) ) {
			$block_content = trim($block_content);
			$doc = new \DOMDocument();
			$html = "<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head><body>{$block_content}</body>"; // note: head/body is temp in order to avoid char coing problems
			$doc->loadHTML( $html, LIBXML_NOERROR );
			$body = $doc->getElementsByTagName('body' )->item(0);
			$block_node = $body->childNodes->item(0);
			//$block_node->setAttribute('id', $block['attrs']['bmID'] );
			$block_node->setAttribute('data-bm-block-id', $block['attrs']['bmID'] );
			$block_content = $doc->saveHTML($block_node); // removes head/body, saves only the original block with injected id(s)
			$x=1;
		}

		return $block_content;
	}


}