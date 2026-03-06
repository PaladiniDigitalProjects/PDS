<?php

namespace ProDevign\BlockMeister\Pattern_Builder;


class BlockMeister_Pattern_Keywords_Taxonomy {

	const TAXONOMY_NAME = 'pattern_keyword';


	public function __construct() {
	}


	public function init() {
		add_action( 'init', [ $this, 'register_blockmeister_pattern_keywords_taxonomy' ], 1010 );
	}


	public function register_blockmeister_pattern_keywords_taxonomy() {

		$capabilities = [
			'manage_terms' => 'manage_blockmeister_pattern_keyword',
			'edit_terms'   => 'manage_blockmeister_pattern_keyword',
			'delete_terms' => 'edit_blockmeister_patterns',
			'assign_terms' => 'edit_blockmeister_patterns',
		];

		$labels = [
			'name'                       => esc_html__( 'Keywords', 'blockmeister' ),
			'singular_name'              => esc_html__( 'Keyword', 'blockmeister' ),
			'add_new_item'               => esc_html__( 'Add New Keyword', 'blockmeister' ),
		];

		$args = array(
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'label'             => $labels['name'],
			'labels'            => $labels,
			'capabilities'      => $capabilities,

		);

		register_taxonomy( self::TAXONOMY_NAME, 'blockmeister_pattern', $args );

	}

}
