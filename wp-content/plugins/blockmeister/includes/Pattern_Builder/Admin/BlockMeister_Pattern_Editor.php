<?php


namespace ProDevign\BlockMeister\Pattern_Builder\Admin;


use ProDevign\BlockMeister\Pattern_Builder\Pattern_Builder;
use ProDevign\BlockMeister\Context;
use ProDevign\BlockMeister\Utils;

class BlockMeister_Pattern_Editor {


	public function __construct() {
	}


	public function init() {
		Utils::add_actions( [ 'load-post.php', 'load-post-new.php' ], function () {
			global $current_screen;
			if ( $current_screen->post_type === Pattern_Builder::POST_TYPE && $current_screen->is_block_editor ) {
				add_filter( 'enter_title_here', function ( $text ) {
					return esc_html__( 'Enter pattern name', 'blockmeister' );
				} );
				add_filter( 'write_your_story', function ( $text ) {
					return esc_html__( 'Start writing or type / to choose a block to add to your block pattern', 'blockmeister' );
				} );
			}
		} );
	}

}