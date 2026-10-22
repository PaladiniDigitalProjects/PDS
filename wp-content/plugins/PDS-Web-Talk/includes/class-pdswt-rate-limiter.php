<?php
/**
 * Límite de peticiones por IP y ventana horaria (usa transients).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Rate_Limiter {

	/**
	 * IP del cliente (saneada). Las cabeceras X-Forwarded-For y CF-Connecting-IP
	 * las puede poner cualquiera: solo se miran si la petición llega desde una
	 * IP privada o local (un proxy propio delante de PHP), y entonces se toma el
	 * último valor de X-Forwarded-For, que es el que añade ese proxy.
	 */
	public static function client_ip() {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) : false;
		$public = $remote && filter_var( $remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
		if ( $remote && ! $public && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = array_map( 'trim', explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$fwd   = filter_var( end( $parts ), FILTER_VALIDATE_IP );
			if ( $fwd ) {
				return $fwd;
			}
		}
		return $remote ? $remote : 'unknown';
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
