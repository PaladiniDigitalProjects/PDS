<?php
/**
 * Textos del chat en en/es/ca. El plugin no lleva .mo: la web es trilingüe con
 * WPML y los textos son pocos, así que van aquí, como ya iban los de la caja de
 * email. El idioma es el de la página (o el que pida la petición REST).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Strings {

	const LANGS = array( 'en', 'es', 'ca' );

	private static $t = array(
		'en' => array(
			// Interfaz.
			'title'            => 'Assistant',
			'welcome'          => 'Hi! How can I help you?',
			'placeholder'      => 'Type your message…',
			'send'             => 'Send',
			'typing'           => 'Typing…',
			'error'            => 'Sorry, something went wrong. Please try again.',
			'sources'          => 'Sources',
			'clear'            => 'Clear conversation',
			'exitFull'         => 'Exit full screen',
			'example'          => 'examples:',
			'piece_partner'    => 'partners:',
			'piece_project'    => 'projects:',
			'piece_page'       => 'pages:',
			'piece_service'    => 'services:',
			'open'             => 'Open chat',
			'close'            => 'Close chat',
			// Caja de email.
			'emailPrompt'      => 'Want a copy of this conversation by email?',
			'emailPlaceholder' => 'your@email.com',
			'emailSend'        => 'Send it to me',
			'emailSkip'        => 'No, thanks',
			'emailPrivacy'     => 'We’ll email you the conversation and keep a copy. No spam.',
			'emailSent'        => 'Done! We’ve emailed you the conversation — check your inbox.',
			'emailInvalid'     => 'Please enter a valid email.',
			'emailError'       => 'Couldn’t send it. Please try again.',
			'emailRate'        => 'You’ve sent a few already. Please try again later.',
			// Errores del servidor.
			'err_origin'       => 'Origin not allowed.',
			'err_empty'        => 'Empty message.',
			'err_too_long'     => 'Message too long (max. %d characters).',
			'err_budget'       => 'The assistant isn’t available right now. Please try again later.',
			'err_rate'         => 'You’ve sent too many messages. Please wait a while and try again.',
			'err_failed'       => 'The answer couldn’t be generated. Please try again.',
			'err_email'        => 'Invalid email.',
			'err_email_rate'   => 'Too many emails. Please try again later.',
			'err_no_turns'     => 'There’s no conversation to send.',
			'err_email_failed' => 'The email couldn’t be sent.',
		),
		'es' => array(
			'title'            => 'Asistente',
			'welcome'          => '¡Hola! ¿En qué te puedo ayudar?',
			'placeholder'      => 'Escribe tu mensaje…',
			'send'             => 'Enviar',
			'typing'           => 'Escribiendo…',
			'error'            => 'Lo siento, algo ha fallado. Inténtalo de nuevo.',
			'sources'          => 'Fuentes',
			'clear'            => 'Borrar la conversación',
			'exitFull'         => 'Salir de pantalla completa',
			'example'          => 'ejemplos:',
			'piece_partner'    => 'partners:',
			'piece_project'    => 'proyectos:',
			'piece_page'       => 'páginas:',
			'piece_service'    => 'servicios:',
			'open'             => 'Abrir el chat',
			'close'            => 'Cerrar el chat',
			'emailPrompt'      => '¿Quieres una copia de esta conversación por email?',
			'emailPlaceholder' => 'tu@email.com',
			'emailSend'        => 'Envíamela',
			'emailSkip'        => 'No, gracias',
			'emailPrivacy'     => 'Te enviaremos la conversación por email y guardaremos una copia. Sin spam.',
			'emailSent'        => '¡Hecho! Te hemos enviado la conversación: revisa tu bandeja de entrada.',
			'emailInvalid'     => 'Introduce un email válido.',
			'emailError'       => 'No se ha podido enviar. Inténtalo de nuevo.',
			'emailRate'        => 'Ya has enviado varias. Inténtalo más tarde.',
			'err_origin'       => 'Origen no permitido.',
			'err_empty'        => 'Mensaje vacío.',
			'err_too_long'     => 'Mensaje demasiado largo (máx. %d caracteres).',
			'err_budget'       => 'El asistente no está disponible ahora mismo. Inténtalo más tarde.',
			'err_rate'         => 'Has enviado demasiados mensajes. Espera un rato e inténtalo de nuevo.',
			'err_failed'       => 'No se ha podido generar la respuesta. Inténtalo de nuevo.',
			'err_email'        => 'Email no válido.',
			'err_email_rate'   => 'Demasiados envíos. Inténtalo más tarde.',
			'err_no_turns'     => 'No hay conversación que enviar.',
			'err_email_failed' => 'No se ha podido enviar el email.',
		),
		'ca' => array(
			'title'            => 'Assistent',
			'welcome'          => 'Hola! En què et puc ajudar?',
			'placeholder'      => 'Escriu el teu missatge…',
			'send'             => 'Envia',
			'typing'           => 'Escrivint…',
			'error'            => 'Ho sentim, alguna cosa ha fallat. Torna-ho a provar.',
			'sources'          => 'Fonts',
			'clear'            => 'Esborra la conversa',
			'exitFull'         => 'Surt de la pantalla completa',
			'example'          => 'exemples:',
			'piece_partner'    => 'partners:',
			'piece_project'    => 'projectes:',
			'piece_page'       => 'pàgines:',
			'piece_service'    => 'serveis:',
			'open'             => 'Obre el xat',
			'close'            => 'Tanca el xat',
			'emailPrompt'      => 'Vols una còpia d’aquesta conversa per correu?',
			'emailPlaceholder' => 'el-teu@correu.com',
			'emailSend'        => 'Envia-me-la',
			'emailSkip'        => 'No, gràcies',
			'emailPrivacy'     => 'T’enviarem la conversa per correu i en guardarem una còpia. Sense correu brossa.',
			'emailSent'        => 'Fet! T’hem enviat la conversa: revisa la safata d’entrada.',
			'emailInvalid'     => 'Introdueix un correu vàlid.',
			'emailError'       => 'No s’ha pogut enviar. Torna-ho a provar.',
			'emailRate'        => 'Ja n’has enviat unes quantes. Torna-ho a provar més tard.',
			'err_origin'       => 'Origen no permès.',
			'err_empty'        => 'Missatge buit.',
			'err_too_long'     => 'Missatge massa llarg (màx. %d caràcters).',
			'err_budget'       => 'L’assistent no està disponible ara mateix. Torna-ho a provar més tard.',
			'err_rate'         => 'Has enviat massa missatges. Espera una estona i torna-ho a provar.',
			'err_failed'       => 'No s’ha pogut generar la resposta. Torna-ho a provar.',
			'err_email'        => 'Correu no vàlid.',
			'err_email_rate'   => 'Massa enviaments. Torna-ho a provar més tard.',
			'err_no_turns'     => 'No hi ha cap conversa per enviar.',
			'err_email_failed' => 'No s’ha pogut enviar el correu.',
		),
	);

	/**
	 * Idioma válido: el pedido si es uno de los tres; si no, el de la página
	 * (WPML) y, en último caso, el inglés.
	 */
	public static function lang( $lang = null ) {
		$lang = strtolower( (string) $lang );
		if ( in_array( $lang, self::LANGS, true ) ) {
			return $lang;
		}
		$cur = (string) apply_filters( 'wpml_current_language', '' );
		return in_array( $cur, self::LANGS, true ) ? $cur : 'en';
	}

	/** Texto de una clave en un idioma (cae al inglés si falta). */
	public static function get( $key, $lang = null ) {
		$lang = self::lang( $lang );
		if ( isset( self::$t[ $lang ][ $key ] ) ) {
			return self::$t[ $lang ][ $key ];
		}
		return isset( self::$t['en'][ $key ] ) ? self::$t['en'][ $key ] : $key;
	}

	/** Todos los textos de un idioma (para el JS). */
	public static function all( $lang = null ) {
		return array_merge( self::$t['en'], self::$t[ self::lang( $lang ) ] );
	}

	/** Los tres idiomas (el JS los necesita si el bot cambia de idioma). */
	public static function by_lang() {
		$out = array();
		foreach ( self::LANGS as $l ) {
			$out[ $l ] = self::all( $l );
		}
		return $out;
	}
}
