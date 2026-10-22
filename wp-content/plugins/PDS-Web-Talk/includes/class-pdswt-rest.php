<?php
/**
 * Endpoint REST público del chat: /wp-json/pdswt/v1/chat
 * Protegido con: validación de longitud, rate limit por IP, tope de gasto y chequeo de origen.
 * Las respuestas del bot van firmadas (HMAC): el historial que devuelve el
 * navegador solo conserva los turnos «assistant» que el servidor reconoce.
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
					'lang'    => array( 'required' => false, 'type' => 'string' ),
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
		$lang = PDSWT_Strings::lang( $request->get_param( 'lang' ) );
		if ( ! $this->same_origin( $request ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_origin', $lang ) ), 403 );
		}

		// Honeypot: campo trampa que solo rellenan los bots → éxito silencioso.
		if ( '' !== trim( (string) $request->get_param( 'website' ) ) ) {
			return new WP_REST_Response( array( 'ok' => true ), 200 );
		}

		$email = sanitize_email( (string) $request->get_param( 'email' ) );
		if ( ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_email', $lang ), 'code' => 'email' ), 400 );
		}

		// Rate limit propio y estricto (anti-abuso del envío de correo).
		if ( ! PDSWT_Rate_Limiter::check_and_increment( 3, 'transcript' ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_email_rate', $lang ), 'code' => 'rate' ), 429 );
		}

		$turns = $this->sanitize_history( $request->get_param( 'history' ), 4000, 40 );
		if ( empty( $turns ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_no_turns', $lang ) ), 400 );
		}

		$result = PDSWT_Transcript::send( $email, $turns );
		if ( empty( $result['ok'] ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_email_failed', $lang ) ), 500 );
		}
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	public function handle_chat( WP_REST_Request $request ) {
		$settings = PDSWT::get_settings();

		// Idioma de la página (WPML): base del idioma de la respuesta y de los errores.
		$page_lang = strtolower( (string) $request->get_param( 'lang' ) );
		$page_lang = in_array( $page_lang, PDSWT_Strings::LANGS, true ) ? $page_lang : null;
		$lang      = PDSWT_Strings::lang( $page_lang );

		// 1) Chequeo de origen (CSRF suave, seguro con caché).
		if ( ! $this->same_origin( $request ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_origin', $lang ) ), 403 );
		}

		// 2) Validación del mensaje.
		$message = trim( (string) $request->get_param( 'message' ) );
		$maxlen  = isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000;
		if ( '' === $message ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_empty', $lang ) ), 400 );
		}
		if ( mb_strlen( $message ) > $maxlen ) {
			return new WP_REST_Response( array( 'error' => sprintf( PDSWT_Strings::get( 'err_too_long', $lang ), $maxlen ) ), 400 );
		}

		// 3) Tope de gasto mensual.
		if ( PDSWT_Usage::budget_exceeded() ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_budget', $lang ), 'code' => 'budget' ), 429 );
		}

		// 4) Rate limit por IP.
		$limit = isset( $settings['rate_limit_per_hour'] ) ? (int) $settings['rate_limit_per_hour'] : 30;
		if ( ! PDSWT_Rate_Limiter::check_and_increment( $limit ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_rate', $lang ), 'code' => 'rate' ), 429 );
		}

		// 5) Historial saneado (últimos turnos).
		$history = $this->alternate( $this->sanitize_history( $request->get_param( 'history' ), $maxlen ) );

		// 6) Respuesta con RAG.
		$chat   = new PDSWT_Chat( $settings );
		$result = $chat->answer( $message, $history, $page_lang );
		if ( empty( $result['ok'] ) ) {
			return new WP_REST_Response( array( 'error' => PDSWT_Strings::get( 'err_failed', $lang ) ), 502 );
		}

		return new WP_REST_Response(
			array(
				'reply'   => $result['reply'],
				'sig'     => self::sign( $result['reply'] ),
				'sources' => $result['sources'],
				'pieces'  => isset( $result['pieces'] ) ? $result['pieces'] : array(),
				'lang'    => isset( $result['lang'] ) ? $result['lang'] : null,
			),
			200
		);
	}

	/**
	 * Deja el historial alternado y empezando por el usuario, como lo espera el
	 * modelo: al descartar un turno sin firma pueden quedar dos del mismo rol
	 * seguidos (se unen) o uno del bot al principio (se quita).
	 */
	private function alternate( $turns ) {
		$out = array();
		foreach ( $turns as $t ) {
			if ( empty( $out ) && 'assistant' === $t['role'] ) {
				continue;
			}
			$last = count( $out ) - 1;
			if ( $last >= 0 && $out[ $last ]['role'] === $t['role'] ) {
				$out[ $last ]['content'] .= "\n\n" . $t['content'];
			} else {
				$out[] = $t;
			}
		}
		// El mensaje nuevo es del usuario: el historial tiene que acabar en el bot.
		if ( ! empty( $out ) && 'user' === $out[ count( $out ) - 1 ]['role'] ) {
			array_pop( $out );
		}
		return $out;
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
	 * Firma de una respuesta del bot. La clave sale de las sales de WordPress:
	 * no hay que configurarla y no viaja nunca al navegador.
	 */
	public static function sign( $text ) {
		return hash_hmac( 'sha256', (string) $text, wp_salt( 'auth' ) . '|pdswt-reply' );
	}

	private static function signed( $text, $sig ) {
		return is_string( $sig ) && '' !== $sig && hash_equals( self::sign( $text ), $sig );
	}

	/**
	 * Sanea el historial recibido del cliente y lo limita a los últimos turnos.
	 * Los turnos «assistant» sin firma válida se descartan: así nadie puede
	 * poner en boca del bot algo que no ha dicho (ni en el contexto del modelo
	 * ni en la conversación que se envía por email).
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
			$role = ( 'assistant' === $turn['role'] ) ? 'assistant' : 'user';
			if ( 'assistant' === $role && ! self::signed( (string) $turn['content'], isset( $turn['sig'] ) ? $turn['sig'] : '' ) ) {
				continue;
			}
			$content = mb_substr( sanitize_textarea_field( (string) $turn['content'] ), 0, $content_maxlen );
			if ( '' === trim( $content ) ) {
				continue;
			}
			$clean[] = array( 'role' => $role, 'content' => $content );
		}
		return array_slice( $clean, -$max_turns );
	}
}
