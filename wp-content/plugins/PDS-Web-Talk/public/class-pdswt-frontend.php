<?php
/**
 * Frontend: shortcode [pds_web_talk] + bloque Gutenberg pdswt/chat.
 * Renderiza el widget de chat, que habla con el endpoint REST.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Frontend {

	private $enqueued = false;

	public function register_assets() {
		wp_register_style( 'pdswt-chat', PDSWT_PLUGIN_URL . 'public/css/pdswt-chat.css', array(), PDSWT_VERSION );
		wp_register_script( 'pdswt-chat', PDSWT_PLUGIN_URL . 'public/js/pdswt-chat.js', array(), PDSWT_VERSION, true );
	}

	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		register_block_type(
			'pdswt/chat',
			array(
				'api_version'     => 2,
				'title'           => __( 'PDS Web Talk — Chat', 'pds-web-talk' ),
				'category'        => 'widgets',
				'icon'            => 'format-chat',
				'editor_script'   => 'pdswt-block',
				'render_callback' => array( $this, 'render_widget' ),
				'attributes'      => array(
					'title'   => array( 'type' => 'string', 'default' => '' ),
					'welcome' => array( 'type' => 'string', 'default' => '' ),
				),
			)
		);
		wp_register_script(
			'pdswt-block',
			PDSWT_PLUGIN_URL . 'public/js/pdswt-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			PDSWT_VERSION,
			true
		);
	}

	public function register_shortcode() {
		add_shortcode( 'pds_web_talk', array( $this, 'render_widget' ) );
	}

	/**
	 * Encola assets (una vez) y devuelve el HTML del widget.
	 */
	public function render_widget( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'title'   => __( 'Asistente', 'pds-web-talk' ),
				'welcome' => __( '¡Hola! ¿En qué te puedo ayudar?', 'pds-web-talk' ),
			),
			is_array( $atts ) ? $atts : array(),
			'pds_web_talk'
		);

		$this->enqueue();

		$settings = PDSWT::get_settings();
		$title    = $atts['title'];
		$welcome  = $atts['welcome'];
		$maxlen   = isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000;

		// Plantilla con posibilidad de override desde el tema:
		// {tema}/pds-web-talk/chat-widget.php
		$template = locate_template( 'pds-web-talk/chat-widget.php' );
		if ( ! $template ) {
			$template = PDSWT_PLUGIN_DIR . 'public/partials/chat-widget.php';
		}

		ob_start();
		include $template;
		return ob_get_clean();
	}

	private function enqueue() {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;
		$settings       = PDSWT::get_settings();

		wp_enqueue_style( 'pdswt-chat' );
		wp_enqueue_script( 'pdswt-chat' );
		wp_localize_script(
			'pdswt-chat',
			'pdswtChat',
			array(
				'restUrl'       => esc_url_raw( rest_url( PDSWT_Rest::NAMESPACE . '/chat' ) ),
				'transcriptUrl' => esc_url_raw( rest_url( PDSWT_Rest::NAMESPACE . '/transcript' ) ),
				'emailAfter'    => 5, // Ofrecer el envío por email tras N preguntas.
				'maxLen'        => isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000,
				'i18n'          => array(
					'placeholder' => __( 'Type your message…', 'pds-web-talk' ),
					'send'        => __( 'Send', 'pds-web-talk' ),
					'typing'      => __( 'Typing…', 'pds-web-talk' ),
					'error'       => __( 'Sorry, something went wrong. Please try again.', 'pds-web-talk' ),
					'sources'     => __( 'Sources', 'pds-web-talk' ),
					'clear'       => __( 'Clear conversation', 'pds-web-talk' ),
					'example'     => __( 'examples:', 'pds-web-talk' ),
					'pieceLabels' => array(
						'partner' => __( 'partners:', 'pds-web-talk' ),
						'project' => __( 'projects:', 'pds-web-talk' ),
						'page'    => __( 'pages:', 'pds-web-talk' ),
					),
					'emailPrompt'      => __( 'Want a copy of this conversation by email?', 'pds-web-talk' ),
					'emailPlaceholder' => __( 'your@email.com', 'pds-web-talk' ),
					'emailSend'        => __( 'Send it to me', 'pds-web-talk' ),
					'emailSkip'        => __( 'No, thanks', 'pds-web-talk' ),
					'emailPrivacy'     => __( 'We’ll email you the conversation and keep a copy. No spam.', 'pds-web-talk' ),
					'emailSent'        => __( 'Done! We’ve emailed you the conversation — check your inbox.', 'pds-web-talk' ),
					'emailInvalid'     => __( 'Please enter a valid email.', 'pds-web-talk' ),
					'emailError'       => __( 'Couldn’t send it. Please try again.', 'pds-web-talk' ),
				),
			)
		);
	}
}
