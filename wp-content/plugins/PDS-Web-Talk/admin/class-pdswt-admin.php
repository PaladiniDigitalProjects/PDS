<?php
/**
 * Área de administración: página de ajustes con pestañas (General | Indexación)
 * + chat de prueba. La presentación vive en admin/partials/.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Admin {

	const MENU_SLUG      = 'pds-web-talk';
	const SETTINGS_GROUP = 'pdswt_settings_group';

	/** Post types que nunca se ofrecen para indexar. */
	private static $excluded_types = array(
		'attachment', 'wp_block', 'wp_template', 'wp_template_part',
		'wp_navigation', 'wp_font_family', 'wp_font_face', 'wpforms', 'wpforms-template',
		'blockmeister_pattern',
	);

	public function add_settings_page() {
		add_menu_page(
			__( 'PDS Web Talk', 'pds-web-talk' ),
			__( 'PDS Web Talk', 'pds-web-talk' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' ),
			'dashicons-format-chat',
			58
		);
		// Renombra el primer submenú (que duplica el título del menú) a "Ajustes".
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Ajustes', 'pds-web-talk' ),
			__( 'Ajustes', 'pds-web-talk' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( self::SETTINGS_GROUP, PDSWT_OPTION_KEY, array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Saneamiento tipo "merge": solo pisa las claves presentes en el envío,
	 * para que cada pestaña/formulario no borre los campos de las demás.
	 */
	public function sanitize_settings( $input ) {
		$out = PDSWT::get_settings();
		if ( ! is_array( $out ) ) {
			$out = array();
		}

		if ( isset( $input['provider'] ) ) {
			$out['provider'] = ( 'openai' === $input['provider'] ) ? 'openai' : 'claude';
		}
		foreach ( array( 'claude_api_key', 'claude_model', 'openai_api_key', 'openai_model', 'embedding_model' ) as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( $input[ $k ] );
			}
		}
		if ( isset( $input['system_prompt'] ) ) {
			$out['system_prompt'] = sanitize_textarea_field( $input['system_prompt'] );
		}
		if ( isset( $input['monthly_budget'] ) ) {
			$out['monthly_budget'] = max( 0, floatval( $input['monthly_budget'] ) );
		}
		if ( isset( $input['rate_limit_per_hour'] ) ) {
			$out['rate_limit_per_hour'] = max( 0, absint( $input['rate_limit_per_hour'] ) );
		}
		if ( isset( $input['max_message_length'] ) ) {
			$out['max_message_length'] = max( 1, absint( $input['max_message_length'] ) );
		}
		if ( isset( $input['top_n'] ) ) {
			$out['top_n'] = max( 1, absint( $input['top_n'] ) );
		}
		// Selección de post types indexables + visibilidad (solo si el formulario de indexación se envió).
		if ( isset( $input['index_types_submitted'] ) ) {
			$types = isset( $input['index_post_types'] ) ? (array) $input['index_post_types'] : array();
			$out['index_post_types'] = array_values( array_map( 'sanitize_key', $types ) );

			$vis = array();
			if ( isset( $input['index_visibility'] ) && is_array( $input['index_visibility'] ) ) {
				foreach ( $input['index_visibility'] as $pt => $v ) {
					$vis[ sanitize_key( $pt ) ] = ( 'hidden' === $v ) ? 'hidden' : 'shown';
				}
			}
			$out['index_visibility'] = $vis;
		}

		return $out;
	}

	/**
	 * Post types candidatos a indexar (objetos), con su condición de visibilidad.
	 */
	public static function indexable_post_types() {
		$objs = get_post_types( array( 'show_ui' => true ), 'objects' );
		$out  = array();
		foreach ( $objs as $obj ) {
			if ( in_array( $obj->name, self::$excluded_types, true ) ) {
				continue;
			}
			if ( 0 === strpos( $obj->name, 'acf-' ) || 0 === strpos( $obj->name, 'wpforms' ) ) {
				continue;
			}
			$out[ $obj->name ] = $obj;
		}
		return $out;
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}
		$tab = $this->current_tab();

		wp_enqueue_style( 'pdswt-admin', PDSWT_PLUGIN_URL . 'admin/css/pdswt-admin.css', array(), PDSWT_VERSION );

		if ( 'general' === $tab ) {
			wp_enqueue_script( 'pdswt-admin', PDSWT_PLUGIN_URL . 'admin/js/pdswt-admin.js', array( 'jquery' ), PDSWT_VERSION, true );
			wp_localize_script( 'pdswt-admin', 'pdswtAdmin', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'pdswt_test_chat' ),
				'i18n'    => array(
					'thinking' => __( 'Pensando…', 'pds-web-talk' ),
					'error'    => __( 'Error', 'pds-web-talk' ),
					'empty'    => __( 'Escribe un mensaje.', 'pds-web-talk' ),
				),
			) );
		}

		if ( 'index' === $tab ) {
			wp_enqueue_script( 'pdswt-index', PDSWT_PLUGIN_URL . 'admin/js/pdswt-index.js', array( 'jquery' ), PDSWT_VERSION, true );
			wp_localize_script( 'pdswt-index', 'pdswtIndex', array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'pdswt_index' ),
				'batchSize' => PDSWT_Index_Page::BATCH_SIZE,
				'i18n'      => array(
					'preparing'    => __( 'Preparando…', 'pds-web-talk' ),
					'indexing'     => __( 'Indexando', 'pds-web-talk' ),
					'done'         => __( 'Indexación completada.', 'pds-web-talk' ),
					'cleared'      => __( 'Índice vaciado.', 'pds-web-talk' ),
					'error'        => __( 'Error', 'pds-web-talk' ),
					'confirmClear' => __( '¿Vaciar todo el índice?', 'pds-web-talk' ),
				),
			) );
		}
	}

	private function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		return in_array( $tab, array( 'general', 'index' ), true ) ? $tab : 'general';
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$tab      = $this->current_tab();
		$settings = PDSWT::get_settings();
		$base_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		$tabs     = array(
			'general' => __( 'General', 'pds-web-talk' ),
			'index'   => __( 'Indexación', 'pds-web-talk' ),
		);
		?>
		<div class="wrap pdswt-settings">
			<h1><?php esc_html_e( 'PDS Web Talk', 'pds-web-talk' ); ?> <span class="pdswt-version">v<?php echo esc_html( PDSWT_VERSION ); ?></span></h1>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, $base_url ) ); ?>" class="nav-tab <?php echo ( $tab === $slug ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</h2>
			<?php
			if ( 'index' === $tab ) {
				$indexer   = new PDSWT_Indexer();
				$stats     = $indexer->get_stats();
				$types     = self::indexable_post_types();
				$file_info = PDSWT_Corpus_IO::info();
				require PDSWT_PLUGIN_DIR . 'admin/partials/tab-index.php';
			} else {
				$usage_month = PDSWT_Usage::get_month();
				$month_total = PDSWT_Usage::month_cost();
				$rates       = PDSWT_Usage::get_rates();
				require PDSWT_PLUGIN_DIR . 'admin/partials/tab-general.php';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Handler AJAX del chat de prueba (Fase 0, sin RAG todavía).
	 */
	public function ajax_test_chat() {
		check_ajax_referer( 'pdswt_test_chat', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'No autorizado.', 'pds-web-talk' ) ), 403 );
		}
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		if ( '' === trim( $message ) ) {
			wp_send_json_error( array( 'message' => __( 'Mensaje vacío.', 'pds-web-talk' ) ), 400 );
		}
		// Chat con RAG: recupera contexto del corpus y responde con él.
		$chat   = new PDSWT_Chat();
		$result = $chat->answer( $message );
		if ( empty( $result['ok'] ) ) {
			wp_send_json_error( array( 'message' => $result['error'] ) );
		}
		wp_send_json_success( array( 'reply' => $result['reply'], 'sources' => $result['sources'] ) );
	}
}
