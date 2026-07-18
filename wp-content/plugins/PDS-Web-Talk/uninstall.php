<?php
/**
 * Desinstalación: limpia las opciones del plugin.
 * (La tabla de corpus se añadirá aquí en la Fase 1.)
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'pdswt_settings' );
