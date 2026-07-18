<?php
/**
 * Carga del text domain para traducciones (.mo/.po nativo de WP).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_i18n {

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'pds-web-talk',
			false,
			dirname( PDSWT_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
