<?php
/**
 * Guardado de precios de estimación de coste (admin-post).
 * La visualización del uso vive en la pestaña General de Ajustes.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Usage_Page {

	/**
	 * Guarda los precios editados y vuelve a la pantalla de origen.
	 */
	public function handle_save_rates() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No autorizado.', 'pds-web-talk' ) );
		}
		check_admin_referer( 'pdswt_save_rates' );

		$rates_in  = isset( $_POST['rate_in'] ) ? (array) $_POST['rate_in'] : array();
		$rates_out = isset( $_POST['rate_out'] ) ? (array) $_POST['rate_out'] : array();

		$rates = array();
		foreach ( $rates_in as $model => $val ) {
			$model             = sanitize_text_field( wp_unslash( $model ) );
			$rates[ $model ]   = array(
				'in'  => floatval( $val ),
				'out' => isset( $rates_out[ $model ] ) ? floatval( $rates_out[ $model ] ) : 0,
			);
		}
		PDSWT_Usage::save_rates( $rates );

		$referer = wp_get_referer();
		$target  = $referer ? add_query_arg( 'updated', '1', $referer ) : admin_url( 'admin.php?page=' . PDSWT_Admin::MENU_SLUG );
		wp_safe_redirect( $target );
		exit;
	}
}
