<?php
/**
 * Proveedor de chat: Claude (Anthropic Messages API).
 * Usa la HTTP API nativa de WordPress (wp_remote_post). Sin dependencias.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Claude_Provider implements PDSWT_Provider_Interface {

	const API_URL         = 'https://api.anthropic.com/v1/messages';
	const ANTHROPIC_VERSION = '2023-06-01';

	private $api_key;
	private $model;

	public function __construct( $api_key, $model = 'claude-sonnet-5' ) {
		$this->api_key = trim( (string) $api_key );
		$this->model   = $model ? $model : 'claude-sonnet-5';
	}

	public function get_label() {
		return 'Claude (Anthropic)';
	}

	public function chat( $system_prompt, $messages ) {
		if ( '' === $this->api_key ) {
			return array(
				'ok'    => false,
				'error' => __( 'Falta la API key de Claude en los ajustes.', 'pds-web-talk' ),
			);
		}

		$body = array(
			'model'      => $this->model,
			'max_tokens' => 1024,
			'system'     => (string) $system_prompt,
			'messages'   => $this->format_messages( $messages ),
		);

		$response = wp_remote_post(
			self::API_URL,
			array(
				'timeout' => 30,
				'headers' => array(
					'x-api-key'         => $this->api_key,
					'anthropic-version' => self::ANTHROPIC_VERSION,
					'content-type'      => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'ok'    => false,
				'error' => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== (int) $code ) {
			$msg = isset( $data['error']['message'] ) ? $data['error']['message'] : sprintf( 'HTTP %d', $code );
			return array(
				'ok'    => false,
				'error' => $msg,
			);
		}

		// Registro de uso (tokens exactos que devuelve la API).
		if ( isset( $data['usage'] ) && class_exists( 'PDSWT_Usage' ) ) {
			PDSWT_Usage::record(
				'claude',
				$this->model,
				isset( $data['usage']['input_tokens'] ) ? $data['usage']['input_tokens'] : 0,
				isset( $data['usage']['output_tokens'] ) ? $data['usage']['output_tokens'] : 0
			);
		}

		// La respuesta viene en data.content[] con bloques {type:'text', text:'...'}.
		$text = '';
		if ( isset( $data['content'] ) && is_array( $data['content'] ) ) {
			foreach ( $data['content'] as $block ) {
				if ( isset( $block['type'], $block['text'] ) && 'text' === $block['type'] ) {
					$text .= $block['text'];
				}
			}
		}

		if ( '' === $text ) {
			return array(
				'ok'    => false,
				'error' => __( 'Respuesta vacía del modelo.', 'pds-web-talk' ),
			);
		}

		return array(
			'ok'      => true,
			'content' => $text,
		);
	}

	/**
	 * Normaliza los mensajes al formato de Anthropic.
	 */
	private function format_messages( $messages ) {
		$out = array();
		foreach ( (array) $messages as $m ) {
			if ( empty( $m['content'] ) ) {
				continue;
			}
			$role = ( isset( $m['role'] ) && 'assistant' === $m['role'] ) ? 'assistant' : 'user';
			$out[] = array(
				'role'    => $role,
				'content' => (string) $m['content'],
			);
		}
		return $out;
	}
}
