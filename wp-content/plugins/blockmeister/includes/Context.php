<?php

namespace ProDevign\BlockMeister;

use ProDevign\BlockMeister\Pattern_Builder\BlockMeister_Pattern_Category_Taxonomy;
use ProDevign\BlockMeister\Pattern_Builder\Pattern_Builder;
use WP_Screen;

class Context {

	/**
	 * Returns whether the current context is of a rest request for the optional given full or partial route.
	 *
	 * @param string $route The (start of the) route to test against the requested rest route.
	 *                            If empty then the only check that is done is if this is a json request.
	 *                            Default is the typical wp route.
	 * @param bool $is_sub_route If true then the test will return true if the requested rest route
	 *                            contains the given route. Else (default) the full route must match the
	 *                            requested rest route.
	 * @param string $context Optional. If set the given context is also checked, e.g. 'view', 'edit'
	 *
	 * @return bool
	 */
	public static  function is_rest_request( $route = '', $is_sub_route = false, $context = '' ) {

		$is_json_request = wp_is_json_request(); // rest requests are always json requests

		if ( $route === '' && $is_json_request ) { // no particular route to match
			return true;
		}

		if ( ! $is_json_request ) {
			return false;
		}

		if ( $context !== '' || ! isset( $_GET['context'] ) || $_GET['context'] !== $context ) { // given context doesn't match
			return true;
		}

		$rest_route = ''; // the rest_route for the current request to test the given route against

		// test based on get rest_route
		if( isset( $_GET['rest_route'] ) ) { // applies to plain permalinks (so user has not set permalinks to a structured path)
			$rest_route = $_GET['rest_route'];
		} elseif( ! empty( $_SERVER['REQUEST_URI'] ) ) { // applies with structured permalinks
			$decoded_request_uri = urldecode( $_SERVER['REQUEST_URI'] );
			$parsed_url = wp_parse_url( $decoded_request_uri );
			if ( isset( $parsed_url['path'] ) ) { // try to get rest_route from path
				// note: path may or may not start with /wp-json, remove it to get the rest route
				if ( preg_match('/(\/wp-json)?(?P<rest_route>.+)/', $parsed_url['path'], $matches) ) {
					$rest_route = $matches['rest_route'];
				}
			}
		}

		if ( $is_sub_route ) {
			$is_applicable_rest_request = strpos( $rest_route, $route ) !== false ;
		} else {
			$is_applicable_rest_request = $rest_route && $rest_route === $route;
		}

		return  $is_applicable_rest_request;

	}


	public static  function is_back_end() {
		return is_admin();
	}

	public static function is_front_end() {
		return ! self::is_back_end() && ! self::is_rest_request();
	}

	/**
	 * Check if the current screen is the list table for blockmeister_pattern post type.
	 * Note: this method can be used even before the global current_screen is available!
	 *
	 * @return bool True if this is the blockmeister_pattern list table, else false
	 */
	public static function is_blockmeister_pattern_list_table() {
		global $pagenow, $plugin_page;
		$is_blockmeister_pattern_list_table = is_admin() &&
		                                      $pagenow === 'edit.php' &&
		                                      is_null( $plugin_page ) &&
		                                      isset( $_GET['post_type'] ) && $_GET['post_type'] === Pattern_Builder::POST_TYPE;

		return $is_blockmeister_pattern_list_table;
	}

	/**
	 * Check if the current screen is the list table for blockmeister_pattern_category taxonomy.
	 * Note: this method can be used even before the global current_screen is available!
	 *
	 * @return bool True if this is the blockmeister_pattern_category list table or, else false
	 *                   an ajax call from the list table (e.g. when adding a catgeory)
	 */
	public static function is_blockmeister_pattern_category_list_table() {
		global $pagenow, $plugin_page;

		// check taxonomy:
		if ( ! isset( $_REQUEST['taxonomy'] ) || $_REQUEST['taxonomy'] !== BlockMeister_Pattern_Category_Taxonomy::TAXONOMY_NAME ) {
			return false;
		}

		// check for list table:
		if ( self::is_back_end() && $pagenow === 'edit-tags.php' && is_null( $plugin_page ) ) {
			return true;
		}

		// check for ajax request:
		if ( $pagenow === 'admin-ajax.php' && ( isset( $_POST['action'] ) && $_POST['action'] === 'add-tag' ) ) {
			return true;
		}

		return false;
	}

	public static function is_blockmeister_pattern_category_editor() {
		global $pagenow;

		$is_blockmeister_pattern_category_editor =
			is_admin() &&
			isset( $pagenow ) && $pagenow === "term.php" &&
			isset( $_GET["taxonomy"] ) && $_GET['taxonomy'] === 'pattern_category' &&
			isset( $_GET["post_type"] ) && $_GET['post_type'] === 'blockmeister_pattern';

		return $is_blockmeister_pattern_category_editor;
	}

	/**
	 * Return whether we currently are in any post_type editor for a post_type supporting the block editor.
	 *
	 * @return bool Whether the current post is or can be edited in the block editor.
	 */
	public static function is_block_editor() {

		global $current_screen;

		if ( $current_screen instanceof WP_Screen ) {
			return $current_screen->is_block_editor();
		}

		return false;

	}

	public static function is_site_editor() {

		global $current_screen, $pagenow;

		$is_site_editor = $pagenow === "site-editor.php" || // true in WP 5.9
		                  $pagenow === "themes.php" && $current_screen && $current_screen->id === "appearance_page_gutenberg-edit-site"; // true in WP 6.x
		return $is_site_editor;

	}

	public static function is_heartbeat() {
		global $pagenow;
		$is_heartbeat_request = $pagenow === 'admin-ajax.php' && isset( $_POST['action'] ) && $_POST['action'] === 'heartbeat';
		return $is_heartbeat_request;
	}

}