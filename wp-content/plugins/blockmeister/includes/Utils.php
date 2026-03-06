<?php

namespace ProDevign\BlockMeister;


class Utils {


	public static function camel2dashed( $word ) {
		return strtolower( preg_replace( '/([A-Z])/', '-$1', $word ) );
	}


	public static function get_pattern_name_sans_namespace( $pattern_name ) {
		return preg_replace( '/^.+\//', '', $pattern_name ); // remove namespace
	}


	/**
	 * Add multiple filters to a closure
	 *
	 * @param $tags
	 * @param $function_to_add
	 * @param int $priority
	 * @param int $accepted_args
	 *
	 * @return bool true
	 */
	public static function add_filters($tags, $function_to_add, $priority = 10, $accepted_args = 1) {
		//If the filter names are not an array, create an array containing one item
		if(!is_array($tags))
			$tags = array($tags);

		//For each filter name
		foreach($tags as $index => $tag)
			add_filter($tag, $function_to_add, (int)(is_array($priority) ? $priority[$index] : $priority), (int)(is_array($accepted_args) ? $accepted_args[$index] : $accepted_args));

		return true;
	}


	/**
	 * Add multiple actions to a closure
	 *
	 * @param $tags
	 * @param $function_to_add
	 * @param int $priority
	 * @param int $accepted_args
	 *
	 * @return bool true
	 */
	public static function add_actions($tags, $function_to_add, $priority = 10, $accepted_args = 1) {
		//add_action() is just a wrapper around add_filter(), so we do the same
		return self::add_filters($tags, $function_to_add, $priority, $accepted_args);
	}


	/**
	 * @param string $notice The notice to display
	 * @param string $type Either 'success', 'error', 'warning', 'info', default 'success'
	 * @param bool $is_dismissible Default true
	 */
	public static function add_admin_notice( $notice, $type = 'success', $is_dismissible = true ) {
		add_action( 'admin_notices', function () use ( $type, $notice, $is_dismissible ) {
			$is_dismissible_class = $is_dismissible ? 'is-dismissible' : '';
			echo "<div class='notice notice-{$type} {$is_dismissible_class}'>";
			echo "  <p>{$notice}</p>";
			echo "</div>";
		} );
	}


	/**
	 * By default any custom or registered pattern are considered active.
	 * Inactive patten names are stored in option 'blockmeister_inactive_patterns'
	 *
	 * @param $pattern
	 *
	 * @return bool
	 */
	public static function is_pattern_active( $pattern ) {
		$inactive_patterns = get_option( 'blockmeister_inactive_patterns', [] );

		return ! in_array( $pattern, $inactive_patterns, true );
	}

}