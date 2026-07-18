<?php
/**
 * Envío de la transcripción de la conversación por email (al usuario y a PDS)
 * y registro como entry en un formulario de WPForms (para tener el lead en el
 * panel de WPForms → Entries). Si WPForms no está, el envío por email sigue.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Transcript {

	const FORM_TITLE = 'PDS Web Talk — Conversación';

	/**
	 * Envía la transcripción y la registra. $turns: [ {role, content}, ... ].
	 *
	 * @return array { ok, error }
	 */
	public static function send( $email, $turns ) {
		$email = sanitize_email( (string) $email );
		if ( ! is_email( $email ) ) {
			return array( 'ok' => false, 'error' => __( 'Email no válido.', 'pds-web-talk' ) );
		}

		$transcript = self::format( $turns );
		if ( '' === $transcript ) {
			return array( 'ok' => false, 'error' => __( 'No hay conversación que enviar.', 'pds-web-talk' ) );
		}

		$settings = PDSWT::get_settings();
		$notify   = ! empty( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
		$site     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		// 1) Email al usuario con su conversación.
		$sent = wp_mail(
			$email,
			sprintf( __( 'Your conversation with %s', 'pds-web-talk' ), $site ),
			self::user_body( $transcript, $site )
		);

		// 2) Copia a PDS (lead) con el email del visitante.
		wp_mail(
			$notify,
			sprintf( __( 'New chatbot conversation — %s', 'pds-web-talk' ), $email ),
			"Email: {$email}\n\n" . $transcript
		);

		// 3) Registro en WPForms (si está disponible).
		self::record_entry( $email, $transcript );

		if ( ! $sent ) {
			return array( 'ok' => false, 'error' => __( 'No se pudo enviar el email.', 'pds-web-talk' ) );
		}
		return array( 'ok' => true );
	}

	/**
	 * Transcripción en texto plano (Q/A).
	 */
	private static function format( $turns ) {
		$lines = array();
		foreach ( (array) $turns as $t ) {
			$role    = ( isset( $t['role'] ) && 'user' === $t['role'] ) ? 'Q' : 'A';
			$content = isset( $t['content'] ) ? trim( wp_strip_all_tags( (string) $t['content'] ) ) : '';
			if ( '' !== $content ) {
				$lines[] = $role . ': ' . $content;
			}
		}
		return implode( "\n\n", $lines );
	}

	private static function user_body( $transcript, $site ) {
		return sprintf(
			/* translators: %s: site name */
			__( "Here's your conversation with %s:", 'pds-web-talk' ),
			$site
		) . "\n\n" . $transcript;
	}

	/**
	 * Registra la conversación como entry de WPForms. No hace nada si WPForms
	 * (Pro) no está disponible.
	 */
	private static function record_entry( $email, $transcript ) {
		if ( ! function_exists( 'wpforms' ) || ! isset( wpforms()->entry ) ) {
			return;
		}
		$form_id = self::ensure_form();
		if ( ! $form_id ) {
			return;
		}
		$fields = array(
			'1' => array( 'name' => 'Email', 'value' => $email, 'id' => '1', 'type' => 'email' ),
			'2' => array( 'name' => 'Conversación', 'value' => $transcript, 'id' => '2', 'type' => 'textarea' ),
		);
		wpforms()->entry->add(
			array(
				'form_id' => $form_id,
				'status'  => '',
				'fields'  => wp_json_encode( $fields ),
				'date'    => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Crea (una vez) el formulario de WPForms que aloja las entries y guarda su
	 * ID en los ajustes. Idempotente: si ya existe, devuelve su ID.
	 */
	public static function ensure_form() {
		$settings = PDSWT::get_settings();
		$form_id  = isset( $settings['transcript_form_id'] ) ? (int) $settings['transcript_form_id'] : 0;
		if ( $form_id && get_post( $form_id ) && 'wpforms' === get_post_type( $form_id ) ) {
			return $form_id;
		}
		if ( ! function_exists( 'wpforms' ) ) {
			return 0;
		}

		$form_id = wp_insert_post(
			array(
				'post_type'    => 'wpforms',
				'post_status'  => 'publish',
				'post_title'   => self::FORM_TITLE,
				'post_content' => '{}',
			)
		);
		if ( ! $form_id || is_wp_error( $form_id ) ) {
			return 0;
		}

		$config = array(
			'id'       => $form_id,
			'field_id' => 3,
			'fields'   => array(
				'1' => array( 'id' => '1', 'type' => 'email', 'label' => 'Email', 'required' => '1' ),
				'2' => array( 'id' => '2', 'type' => 'textarea', 'label' => 'Conversación' ),
			),
			'settings' => array(
				'form_title'          => self::FORM_TITLE,
				'submit_text'         => 'Enviar',
				'notification_enable' => '0', // Los emails los envía el plugin.
				'antispam'            => '1',
			),
		);
		wp_update_post( array( 'ID' => $form_id, 'post_content' => wp_slash( wp_json_encode( $config ) ) ) );

		$settings['transcript_form_id'] = $form_id;
		update_option( PDSWT_OPTION_KEY, $settings );

		return $form_id;
	}
}
