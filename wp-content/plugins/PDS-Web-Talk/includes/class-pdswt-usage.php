<?php
/**
 * Contador de uso: registra tokens por proveedor/modelo y mes,
 * y estima el coste con precios editables.
 * Los tokens son exactos (los devuelve la API); el coste es estimación.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Usage {

	const OPTION       = 'pdswt_usage';
	const RATES_OPTION = 'pdswt_rates';

	/**
	 * Precios por defecto (USD por 1M de tokens). ORIENTATIVOS: ajústalos
	 * a tu tarifa real en la página de Uso. Los de salida no aplican a embeddings.
	 */
	public static function default_rates() {
		return array(
			'claude-opus-4-8'           => array( 'in' => 5.00, 'out' => 25.00 ),
			'claude-sonnet-5'           => array( 'in' => 3.00, 'out' => 15.00 ),
			'claude-haiku-4-5-20251001' => array( 'in' => 1.00, 'out' => 5.00 ),
			'text-embedding-3-small'    => array( 'in' => 0.02, 'out' => 0.00 ),
			'text-embedding-3-large'    => array( 'in' => 0.13, 'out' => 0.00 ),
		);
	}

	public static function get_rates() {
		$saved = get_option( self::RATES_OPTION );
		$rates = is_array( $saved ) ? $saved : array();
		return array_merge( self::default_rates(), $rates );
	}

	public static function save_rates( $rates ) {
		update_option( self::RATES_OPTION, (array) $rates, false );
	}

	/**
	 * Registra un uso. Se llama desde los proveedores tras una llamada correcta.
	 *
	 * @param string $provider 'claude' | 'openai'
	 * @param string $model
	 * @param int    $in_tokens
	 * @param int    $out_tokens
	 */
	public static function record( $provider, $model, $in_tokens, $out_tokens = 0 ) {
		$in_tokens  = max( 0, (int) $in_tokens );
		$out_tokens = max( 0, (int) $out_tokens );
		if ( 0 === $in_tokens && 0 === $out_tokens ) {
			return;
		}

		$month = gmdate( 'Y-m' );
		$data  = get_option( self::OPTION );
		$data  = is_array( $data ) ? $data : array();

		if ( ! isset( $data[ $month ][ $provider ][ $model ] ) ) {
			$data[ $month ][ $provider ][ $model ] = array( 'in' => 0, 'out' => 0, 'req' => 0 );
		}
		$data[ $month ][ $provider ][ $model ]['in']  += $in_tokens;
		$data[ $month ][ $provider ][ $model ]['out'] += $out_tokens;
		$data[ $month ][ $provider ][ $model ]['req'] += 1;

		update_option( self::OPTION, $data, false );
	}

	/**
	 * Coste estimado (USD) de un modelo dado sus tokens.
	 */
	public static function cost( $model, $in_tokens, $out_tokens ) {
		$rates = self::get_rates();
		if ( empty( $rates[ $model ] ) ) {
			return null; // Precio desconocido.
		}
		$r = $rates[ $model ];
		return ( $in_tokens / 1e6 ) * $r['in'] + ( $out_tokens / 1e6 ) * $r['out'];
	}

	/**
	 * Datos agregados de un mes (por defecto, el actual).
	 *
	 * @return array [ provider => [ model => ['in','out','req','cost'] ] ]
	 */
	public static function get_month( $month = null ) {
		$month = $month ? $month : gmdate( 'Y-m' );
		$data  = get_option( self::OPTION );
		$data  = is_array( $data ) && isset( $data[ $month ] ) ? $data[ $month ] : array();

		foreach ( $data as $provider => $models ) {
			foreach ( $models as $model => $row ) {
				$data[ $provider ][ $model ]['cost'] = self::cost( $model, $row['in'], $row['out'] );
			}
		}
		return $data;
	}

	/**
	 * Totales por proveedor de un mes: [ provider => {req,in,out,cost,cost_known} ].
	 */
	public static function provider_totals( $month = null ) {
		$out = array();
		foreach ( self::get_month( $month ) as $provider => $models ) {
			$t = array( 'req' => 0, 'in' => 0, 'out' => 0, 'cost' => 0.0, 'cost_known' => false );
			foreach ( $models as $row ) {
				$t['req'] += $row['req'];
				$t['in']  += $row['in'];
				$t['out'] += $row['out'];
				if ( null !== $row['cost'] ) {
					$t['cost']      += $row['cost'];
					$t['cost_known'] = true;
				}
			}
			$out[ $provider ] = $t;
		}
		return $out;
	}

	/**
	 * Coste total estimado (USD) de un mes.
	 */
	public static function month_cost( $month = null ) {
		$total = 0.0;
		foreach ( self::get_month( $month ) as $models ) {
			foreach ( $models as $row ) {
				if ( null !== $row['cost'] ) {
					$total += $row['cost'];
				}
			}
		}
		return $total;
	}

	/**
	 * ¿Se ha superado el tope de gasto mensual? (0 = sin tope).
	 */
	public static function budget_exceeded() {
		$s      = PDSWT::get_settings();
		$budget = isset( $s['monthly_budget'] ) ? (float) $s['monthly_budget'] : 0;
		if ( $budget <= 0 ) {
			return false;
		}
		return self::month_cost() >= $budget;
	}

	/**
	 * Lista de meses con datos (desc).
	 */
	public static function get_months() {
		$data = get_option( self::OPTION );
		$data = is_array( $data ) ? $data : array();
		$keys = array_keys( $data );
		rsort( $keys );
		return $keys;
	}
}
