<?php
/**
 * Límite de peticiones por IP y ventana horaria (usa transients).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Rate_Limiter {

	/**
	 * IP del cliente (best-effort, saneada).
	 */
	public static function client_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ip    = trim( $parts[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
		}
		$ip = filter_var( $ip, FILTER_VALIDATE_IP );
		return $ip ? $ip : 'unknown';
	}

	/**
	 * Comprueba y consume una petición para la IP actual.
	 *
	 * @param int $limit Peticiones por hora (0 = sin límite).
	 * @return bool true si se permite, false si se ha superado el límite.
	 */
	public static function check_and_increment( $limit, $bucket = 'chat' ) {
		$limit = (int) $limit;
		if ( $limit <= 0 ) {
			return true;
		}
		$key   = 'pdswt_rl_' . md5( $bucket . '_' . self::client_ip() . '_' . gmdate( 'Y-m-d-H' ) );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return true;
	}
}
