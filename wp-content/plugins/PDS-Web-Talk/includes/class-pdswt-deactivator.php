<?php
/**
 * Desactivación del plugin.
 * Fase 0: no elimina datos (eso es tarea de uninstall.php).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Deactivator {

	public static function deactivate() {
		// Sin acciones destructivas al desactivar.
		// La limpieza de opciones/tablas vive en uninstall.php.
	}
}
