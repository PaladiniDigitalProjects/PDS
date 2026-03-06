<?php

namespace ProDevign\BlockMeister\Pattern_Builder\Admin;

use ProDevign\BlockMeister\Pattern_Builder\BlockMeister_Pattern_Category_Taxonomy;
use ProDevign\BlockMeister\Context;

class BlockMeister_Pattern_Category_List_Table {

	public function __construct() {}

	public function init() {

	    if ( ! Context::is_blockmeister_pattern_category_list_table() && ! Context::is_blockmeister_pattern_category_editor() ) {
	        return;
	    }

		add_action( 'admin_head', [ $this, 'output_custom_css' ] );

		if ( Context::is_blockmeister_pattern_category_list_table() ) {

			add_filter( 'get_terms_args', [ $this, 'filter_out_third_party_categories_on_get_term_args' ], 10, 2 );

			//Note: following hooks are no longer in use since 3.1.5,
			//      but don't delete those yet (we may start using them again in the future):
			//add_filter( 'manage_edit-pattern_category_columns', [ $this, 'manage_pattern_category_columns_filter' ] );
			//add_action( 'manage_pattern_category_custom_column', [ $this, 'render_source_column' ], 10, 3 );
		}

	}


	/**
	 * Note: no longer used since 3.1.5, but left for maybe future re-use.
	 *
	 * Filters the columns.
	 *
	 * @param $columns
	 *
	 * @return array $columns (sans the posts (count) columns
	 */
	public function manage_pattern_category_columns_filter( $columns ) {
		$posts = $columns['posts'];
		unset( $columns['posts'] ); // remove 'Counts' column (since BlockMeister 3.1.0)
		$columns['source'] = esc_html__( 'Source', 'blockmeister' );
		//$columns['posts']  = $posts; // put Counts column last

		return $columns;
	}


	/**
	 * Note: no longer used since 3.1.5, but left for maybe future re-use.
	 */
	public function render_source_column( $post_id, $column, $term_id ) {
		if ( $column === 'source' ) {
			$term_meta_source = get_term_meta( $term_id, 'source', true );
			switch ( $term_meta_source ) {
				case 'blockmeister':
					$source = "BlockMeister";
					break;
				case 'site':
					$source = esc_html__( 'WordPress/Theme/Plugin', 'blockmeister' );
					break;
				case 'user' :
					$source = esc_html__( 'User', 'blockmeister' );
					break;
				default:
					$source = $term_meta_source;
					break;
			}
			echo $source;
		}
	}


	/**
	 * Some CSS hacks:
	 * - hide term parent input
	 * - add 'Default' suffix to default category name
	 */
	public function output_custom_css() {

		$default_term_option_name = 'default_term_' . BlockMeister_Pattern_Category_Taxonomy::TAXONOMY_NAME;
		$default_term_id          = (int) get_option( $default_term_option_name );
		$default_suffix = " — " . esc_html__( 'Default', 'blockmeister' );

		echo "<style>" .
		     "  .taxonomy-pattern_category .form-field.term-parent-wrap { display: none; }" .
		     "  #tag-{$default_term_id} strong::after { content: '{$default_suffix}'; } ".
		     "</style>";
	}


	/**
	 * Filters out the non-custom (synced site) terms from core/theme/plugins.
	 *
	 * @param array $args An array of get_terms() arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 *
	 * @since 3.1.5
	 *
	 */
	public function filter_out_third_party_categories_on_get_term_args( $args, $taxonomies ) {

		$args['meta_key']     = 'source';
		$args['meta_value']   = 'site';
		$args['meta_compare'] = '!=';

		return $args;
	}

}
