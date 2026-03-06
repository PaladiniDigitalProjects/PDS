<?php

namespace ProDevign\BlockMeister\Pattern_Builder;

use WP_Block_Pattern_Categories_Registry;
use WP_Taxonomy;

/**
 * Defines and registers Custom taxonomy for post type: 'blockmeister_pattern'
 */
class BlockMeister_Pattern_Category_Taxonomy {

	const TAXONOMY_NAME = 'pattern_category';

	public function __construct() {
	}

	public function init() {
		// Init with a low priority so other plugins/theme can register there block pattern categories first.
		// Note: if in future an even lower priority is set, remember to decrease the priority of
		//       Block_Pattern_Registry->init() too!
		add_action( 'init', function (){
			$this->register_blockmeister_pattern_category_taxonomy();
			$this->synchronize_registered_pattern_categories_with_pattern_category_terms();
			$this->make_sure_source_is_set_for_all_pattern_categories();
			$this->register_custom_pattern_categories();
		}, 1001);

	}

	public function register_blockmeister_pattern_category_taxonomy() {

		$capabilities = [
			'manage_terms' => 'manage_blockmeister_pattern_category',
			'edit_terms'   => 'manage_blockmeister_pattern_category',
			'delete_terms' => 'manage_blockmeister_pattern_category',
			'assign_terms' => 'edit_blockmeister_patterns',
		];

		// customize labels:
		$labels['menu_name'] = esc_html__( 'Categories', 'blockmeister' );
		$labels['name'] = esc_html__( 'Custom Pattern Categories', 'blockmeister' );

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'capabilities'      => $capabilities,
			'default_term'      => $this->get_default_category(),
		);

		register_taxonomy( self::TAXONOMY_NAME, 'blockmeister_pattern', $args );

	}


	/**
	 * Returns the (maybe custom) existing term data for the default pattern category or returns
	 * defaults based on the name of the site.
	 *
	 * Note: we use direct DB access because this method is called before the taxonomy is registered.
	 * Note: the user is allowed to change that name (and also the slug and description).
	 *
	 * @return string[]
	 */
	public function get_default_category() {

		global $wpdb;

		$default_term_option_name = 'default_term_' . self::TAXONOMY_NAME;
		$default_term_id = get_option( $default_term_option_name );

		// try to get default term based on $default_term_id or slug = 'default'
		$default_term = $wpdb->get_row(
			$wpdb->prepare( "SELECT name,slug,description FROM {$wpdb->terms} AS t JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE t.term_id = '%d' OR slug = 'default' LIMIT 1", $default_term_id )
		);

		if ( is_object( $default_term ) ) { // return existing term data for the default category:
			return [
				'name'        => $default_term->name,
				'slug'        => $default_term->slug,
				'description' => $default_term->description,
			];
		} else { // return default data based on site name:
			return [
				'name'        => esc_html( get_bloginfo( 'name' ) ),
				'slug'        => 'default',
				'description' => esc_html__( 'Default category', 'blockmeister' ),
			];
		}

	}


	/**
	 * Add/remove pattern_category terms based on block_pattern_category registrations by core/theme/plugins
	 */
	public function synchronize_registered_pattern_categories_with_pattern_category_terms() {

		$wp_pattern_category_registry  = WP_Block_Pattern_Categories_Registry::get_instance();
		$registered_pattern_categories = $wp_pattern_category_registry->get_all_registered();

		// Get all pattern categories registered with register_block_pattern_category() by
		// core, plugins or themes and make sure they are added as pattern category terms
		foreach ( $registered_pattern_categories as $registered_category ) {
			if ( ! term_exists( $registered_category['name'], self::TAXONOMY_NAME ) ) { // add new
				$inserted_term = wp_insert_term(
					$registered_category['label'],
					self::TAXONOMY_NAME,
					[ 'slug' => sanitize_title( $registered_category['name'] ), ]
				);
				if ( ! is_wp_error( $inserted_term ) ) {
					add_term_meta( $inserted_term['term_id'], 'source', 'site', true );
				}
			}
		}

		$pattern_category_terms = get_terms( array(
			'taxonomy'   => BlockMeister_Pattern_Category_Taxonomy::TAXONOMY_NAME,
			'hide_empty' => false,
		) );

		// Clean up:
		// Remove all terms sourced by site (core/theme/plugin) that are no longer being registered by core/theme/plugin
		// This auto cleans the taxonomy e.g after the admin user switches themes or removes plugins.
		foreach ( $pattern_category_terms as $pattern_category_term ) {
			$source = get_term_meta( $pattern_category_term->term_id, 'source', true );
			if ( $source === 'site' ) {
				// look term slug up in $registered_pattern_categories
				$is_term_still_being_registered_by_site_source = false;
				foreach ( $registered_pattern_categories as $registered_pattern_category ) {
					if ( $registered_pattern_category['name'] === $pattern_category_term->slug ) {
						$is_term_still_being_registered_by_site_source = true;
						break;
					}
				};
				if ( ! $is_term_still_being_registered_by_site_source ) { // remove term
					wp_delete_term( $pattern_category_term->term_id, BlockMeister_Pattern_Category_Taxonomy::TAXONOMY_NAME );
				}
			}
		}

	}

	/**
	 * Register all custom pattern categories (for use in block editor) and
	 * make sure their source metadata is set.
	 */
	public function register_custom_pattern_categories() {

		$pattern_category_terms = get_terms( array(
			'taxonomy'     => self::TAXONOMY_NAME,
			'hide_empty'   => false,
			'meta_key'     => 'source',
			'meta_value'   => 'site',
			'meta_compare' => '!=',
		) );

		// Register (using register_block_pattern_category) all custom pattern_category terms
		foreach ( $pattern_category_terms as $pattern_category_term ) {
			register_block_pattern_category(
				sanitize_title( $pattern_category_term->slug ),
				[ 'label' => sanitize_textarea_field( $pattern_category_term->name ) ]
			);
		}
	}

	/**
	 * All pattern_categories need to have their 'source' termmeta set to either:
	 * - 'blockmeister' add by this plugin for the default category
	 * - 'user' for custom categories added by a user
	 * - 'site' for categories that where synced from registered block categories by core, theme or any plugin
	 *
	 * @since 3.1.5
	 */
	public function make_sure_source_is_set_for_all_pattern_categories() {

		$wp_pattern_category_registry  = WP_Block_Pattern_Categories_Registry::get_instance();

		$pattern_category_terms = get_terms( array(
			'taxonomy'     => self::TAXONOMY_NAME,
			'hide_empty'   => false,
		) );

		foreach ( $pattern_category_terms as $pattern_category_term ) {
			$term_meta_source = get_term_meta( $pattern_category_term->term_id, 'source', true );

			if ( ! $term_meta_source ) {
				if ( $pattern_category_term->slug === 'default' ) { // note: user may change the default name and slug
					$source = 'blockmeister';
				} elseif ( $wp_pattern_category_registry->is_registered( $pattern_category_term->slug ) ) { // site (=core/theme/plugin)
					$source = 'site';
				} else {
					$source = 'user'; // default
				}
				add_term_meta( $pattern_category_term->term_id, 'source', $source );
			}
		}

	}


}
