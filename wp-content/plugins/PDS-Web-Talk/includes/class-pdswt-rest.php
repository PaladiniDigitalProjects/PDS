<?php
/**
 * Endpoint REST público del chat: /wp-json/pdswt/v1/chat
 * Protegido con: validación de longitud, rate limit por IP, tope de gasto y chequeo de origen.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Rest {

	const NAMESPACE = 'pdswt/v1';

	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/chat',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_chat' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'message' => array( 'required' => true, 'type' => 'string' ),
					'history' => array( 'required' => false, 'type' => 'array' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/transcript',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_transcript' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Envía la conversación por email (al usuario y a PDS) y la registra en
	 * WPForms. Protegido: origen, honeypot, email válido y rate limit propio.
	 */
	public function handle_transcript( WP_REST_Request $request ) {
		if ( ! $this->same_origin( $request ) ) {
			return new WP_REST_Response( array( 'error' => __( 'Origen no permitido.', 'pds-web-talk' ) ), 403 );
		}

		// Honeypot: campo trampa que solo rellenan los bots → éxito silencioso.
		if ( '' !== trim( (string) $request->get_param( 'website' ) ) ) {
			return new WP_REST_Response( array( 'ok' => true ), 200 );
		}

		$email = sanitize_email( (string) $request->get_param( 'email' ) );
		if ( ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'error' => __( 'Email no válido.', 'pds-web-talk' ) ), 400 );
		}

		// Rate limit propio y estricto (anti-abuso del envío de correo).
		if ( ! PDSWT_Rate_Limiter::check_and_increment( 3, 'transcript' ) ) {
			return new WP_REST_Response( array( 'error' => __( 'Demasiados envíos. Inténtalo más tarde.', 'pds-web-talk' ), 'code' => 'rate' ), 429 );
		}

		$turns = $this->sanitize_history( $request->get_param( 'history' ), 4000, 40 );
		if ( empty( $turns ) ) {
			return new WP_REST_Response( array( 'error' => __( 'No hay conversación que enviar.', 'pds-web-talk' ) ), 400 );
		}

		$result = PDSWT_Transcript::send( $email, $turns );
		if ( empty( $result['ok'] ) ) {
			return new WP_REST_Response( array( 'error' => $result['error'] ), 500 );
		}
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	public function handle_chat( WP_REST_Request $request ) {
		$settings = PDSWT::get_settings();

		// 1) Chequeo de origen (CSRF suave, seguro con caché).
		if ( ! $this->same_origin( $request ) ) {
			return new WP_REST_Response( array( 'error' => __( 'Origen no permitido.', 'pds-web-talk' ) ), 403 );
		}

		// 2) Validación del mensaje.
		$message = trim( (string) $request->get_param( 'message' ) );
		$maxlen  = isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000;
		if ( '' === $message ) {
			return new WP_REST_Response( array( 'error' => __( 'Mensaje vacío.', 'pds-web-talk' ) ), 400 );
		}
		if ( mb_strlen( $message ) > $maxlen ) {
			return new WP_REST_Response( array( 'error' => sprintf( __( 'Mensaje demasiado largo (máx. %d).', 'pds-web-talk' ), $maxlen ) ), 400 );
		}

		// 3) Tope de gasto mensual.
		if ( PDSWT_Usage::budget_exceeded() ) {
			return new WP_REST_Response( array( 'error' => __( 'El asistente no está disponible ahora mismo. Inténtalo más tarde.', 'pds-web-talk' ), 'code' => 'budget' ), 429 );
		}

		// 4) Rate limit por IP.
		$limit = isset( $settings['rate_limit_per_hour'] ) ? (int) $settings['rate_limit_per_hour'] : 30;
		if ( ! PDSWT_Rate_Limiter::check_and_increment( $limit ) ) {
			return new WP_REST_Response( array( 'error' => __( 'Has enviado demasiados mensajes. Espera un rato e inténtalo de nuevo.', 'pds-web-talk' ), 'code' => 'rate' ), 429 );
		}

		// 5) Historial saneado (últimos turnos).
		$history = $this->sanitize_history( $request->get_param( 'history' ), $maxlen );

		// 6) Respuesta con RAG.
		$chat   = new PDSWT_Chat( $settings );
		$result = $chat->answer( $message, $history );
		if ( empty( $result['ok'] ) ) {
			return new WP_REST_Response( array( 'error' => __( 'No se pudo generar la respuesta.', 'pds-web-talk' ) ), 502 );
		}

		return new WP_REST_Response(
			array(
				'reply'   => $result['reply'],
				'sources' => $result['sources'],
				'pieces'  => isset( $result['pieces'] ) ? $result['pieces'] : array(),
			),
			200
		);
	}

	/**
	 * Acepta solo peticiones cuyo Origin/Referer sea el propio site.
	 */
	private function same_origin( WP_REST_Request $request ) {
		$origin = $request->get_header( 'origin' );
		if ( ! $origin ) {
			$ref    = $request->get_header( 'referer' );
			$origin = $ref ? $ref : '';
		}
		if ( '' === $origin ) {
			return false;
		}
		$origin_host = wp_parse_url( $origin, PHP_URL_HOST );
		$site_host   = wp_parse_url( home_url(), PHP_URL_HOST );
		return $origin_host && $site_host && strtolower( $origin_host ) === strtolower( $site_host );
	}

	/**
	 * Sanea el historial recibido del cliente y lo limita a los últimos turnos.
	 */
	private function sanitize_history( $history, $content_maxlen, $max_turns = 6 ) {
		if ( ! is_array( $history ) ) {
			return array();
		}
		$clean = array();
		foreach ( $history as $turn ) {
			if ( ! is_array( $turn ) || empty( $turn['role'] ) || ! isset( $turn['content'] ) ) {
				continue;
			}
			$role    = ( 'assistant' === $turn['role'] ) ? 'assistant' : 'user';
			$content = mb_substr( sanitize_textarea_field( (string) $turn['content'] ), 0, $content_maxlen );
			if ( '' === trim( $content ) ) {
				continue;
			}
			$clean[] = array( 'role' => $role, 'content' => $content );
		}
		return array_slice( $clean, -$max_turns );
	}
}
