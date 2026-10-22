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
	private $floating = false; // esta página lleva el chat flotante
	private $inline   = false; // esta página ya pintó el chat en el contenido

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
				'title'   => '',
				'welcome' => '',
			),
			is_array( $atts ) ? $atts : array(),
			'pds_web_talk'
		);

		$this->enqueue();
		$this->inline = true;

		$settings = PDSWT::get_settings();
		// Sin título o bienvenida en el bloque, los del idioma de la página.
		$title   = '' !== trim( $atts['title'] ) ? $atts['title'] : PDSWT_Strings::get( 'title' );
		$welcome = '' !== trim( $atts['welcome'] ) ? $atts['welcome'] : PDSWT_Strings::get( 'welcome' );
		$maxlen  = isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000;
		$t       = PDSWT_Strings::all();

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

	/**
	 * Post types que pueden llevar el chat flotante: los públicos con página
	 * propia (sin adjuntos).
	 */
	public static function floating_candidate_types() {
		$objs = get_post_types( array( 'public' => true ), 'objects' );
		unset( $objs['attachment'] );
		return $objs;
	}

	/**
	 * ¿Lleva esta página el chat flotante? Activado en ajustes, página individual
	 * de un tipo marcado, que no sea la portada y que no tenga ya el chat en el
	 * contenido (bloque o shortcode): dos chats a la vez no tienen sentido.
	 */
	private function should_float() {
		$s = PDSWT::get_settings();
		if ( empty( $s['floating_enabled'] ) || empty( $s['floating_post_types'] ) || is_admin() || is_front_page() ) {
			return false;
		}
		if ( ! is_singular( (array) $s['floating_post_types'] ) ) {
			return false;
		}
		$post = get_queried_object();
		if ( $post instanceof WP_Post && ( has_block( 'pdswt/chat', $post ) || has_shortcode( $post->post_content, 'pds_web_talk' ) ) ) {
			return false;
		}
		return true;
	}

	public function maybe_floating() {
		if ( $this->should_float() ) {
			$this->floating = true;
			$this->enqueue();
		}
	}

	/** Botón y panel del chat flotante, al pie de la página. */
	public function render_floating() {
		if ( ! $this->floating || $this->inline ) {
			return;
		}
		$settings = PDSWT::get_settings();
		$t        = PDSWT_Strings::all();
		$title    = $t['title'];
		$welcome  = $t['welcome'];
		$maxlen   = isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000;

		$template = locate_template( 'pds-web-talk/chat-floating.php' );
		if ( ! $template ) {
			$template = PDSWT_PLUGIN_DIR . 'public/partials/chat-floating.php';
		}
		include $template;
	}

	private function enqueue() {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;
		$settings       = PDSWT::get_settings();
		$t              = PDSWT_Strings::all();
		$t['pieceLabels'] = array(
			'partner' => $t['piece_partner'],
			'project' => $t['piece_project'],
			'page'    => $t['piece_page'],
			'service' => $t['piece_service'],
		);

		wp_enqueue_style( 'pdswt-chat' );
		wp_enqueue_script( 'pdswt-chat' );
		wp_localize_script(
			'pdswt-chat',
			'pdswtChat',
			array(
				'restUrl'       => esc_url_raw( rest_url( PDSWT_Rest::NAMESPACE . '/chat' ) ),
				'transcriptUrl' => esc_url_raw( rest_url( PDSWT_Rest::NAMESPACE . '/transcript' ) ),
				// Idioma de la página según WPML: el bot responde en él salvo que
				// la pregunta llegue claramente en otro.
				'lang'          => (string) apply_filters( 'wpml_current_language', '' ),
				'emailAfter'    => 2, // Ofrecer el envío por email tras N preguntas.
				'maxLen'        => isset( $settings['max_message_length'] ) ? (int) $settings['max_message_length'] : 1000,
				// Textos de la interfaz en el idioma de la página.
				'i18n'          => $t,
				// Los tres idiomas: la caja de email sigue el idioma en el que
				// responde el bot, que puede no ser el de la página.
				'emailI18n'     => PDSWT_Strings::by_lang(),
			)
		);
	}
}
