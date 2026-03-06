<?php

namespace ProDevign\BlockMeister;

class Debug {

	/**
	 * Simple helper to debug to the console
	 *
	 * @param $data object|array|string
	 * @param bool $wp_debug_only Set to false if the message should always be shown regardless of WP_DEBUG being false.
	 * @param string $type log|warn|error
	 * @param $context string  Optional a description. Default calling file and line number.
	 *
	 * @return string
	 */
	public static function write_to_console( $data, $type = 'log', $wp_debug_only = true, $context = '' ) {

		if ( ! $wp_debug_only || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {

//			if ( '' === $context ) {
//				$stack                = debug_backtrace();
//				$caller_path          = $stack[0]['file'];
//				$calling_line         = $stack[0]['line'];
//				$caller_path_exploded = explode( '\\', $caller_path );
//				$caller               = array_pop( $caller_path_exploded );
//				$context              = "[$caller, line $calling_line]:";
//			}

			// Buffering to solve problems frameworks, like header() in this and not a solid return.
			ob_start();

			$json_data = json_encode( $data );
			//$output    = "console.{$type}('$context',$json_data)";
			$output    = "console.{$type}($json_data)";
			$output    = sprintf( '<script>%s</script>', $output );

			echo $output;
		}
	}

}
