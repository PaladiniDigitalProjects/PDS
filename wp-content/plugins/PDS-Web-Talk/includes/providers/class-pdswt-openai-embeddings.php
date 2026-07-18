<?php
/**
 * Proveedor de embeddings: OpenAI (text-embedding-3).
 * Multiidioma. Usa la HTTP API nativa de WordPress. Sin dependencias.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_OpenAI_Embeddings {

	const API_URL = 'https://api.openai.com/v1/embeddings';

	private $api_key;
	private $model;

	public function __construct( $api_key, $model = 'text-embedding-3-small' ) {
		$this->api_key = trim( (string) $api_key );
		$this->model   = $model ? $model : 'text-embedding-3-small';
	}

	public function is_configured() {
		return '' !== $this->api_key;
	}

	/**
	 * Genera embeddings para uno o varios textos.
	 *
	 * @param string|array $input Texto o lista de textos.
	 * @return array {
	 *     @type bool  $ok
	 *     @type array $vectors Lista de vectores (array de floats), en el mismo orden que $input.
	 *     @type string $error
	 * }
	 */
	public function embed( $input ) {
		if ( '' === $this->api_key ) {
			return array( 'ok' => false, 'error' => __( 'Falta la API key de OpenAI en los ajustes.', 'pds-web-talk' ) );
		}

		$is_batch = is_array( $input );
		$payload  = array(
			'model' => $this->model,
			'input' => $is_batch ? array_values( $input ) : (string) $input,
		);

		$response = wp_remote_post(
			self::API_URL,
			array(
				'timeout' => 60,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'ok' => false, 'error' => $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== (int) $code ) {
			$msg = isset( $data['error']['message'] ) ? $data['error']['message'] : sprintf( 'HTTP %d', $code );
			return array( 'ok' => false, 'error' => $msg );
		}

		if ( empty( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return array( 'ok' => false, 'error' => __( 'Respuesta de embeddings vacía.', 'pds-web-talk' ) );
		}

		// Registro de uso (los embeddings solo consumen tokens de entrada).
		if ( isset( $data['usage']['prompt_tokens'] ) && class_exists( 'PDSWT_Usage' ) ) {
			PDSWT_Usage::record( 'openai', $this->model, $data['usage']['prompt_tokens'], 0 );
		}

		// Ordena por índice para preservar el orden de entrada.
		usort( $data['data'], function ( $a, $b ) {
			return ( isset( $a['index'], $b['index'] ) ) ? $a['index'] - $b['index'] : 0;
		} );

		$vectors = array();
		foreach ( $data['data'] as $item ) {
			$vectors[] = isset( $item['embedding'] ) ? $item['embedding'] : array();
		}

		return array( 'ok' => true, 'vectors' => $vectors );
	}
}
