<?php
/**
 * Clase principal: define hooks e inicializa los componentes.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT {

	protected $loader;

	public function __construct() {
		// Asegura el esquema de BBDD si cambió (plugin ya activo).
		PDSWT_Activator::maybe_upgrade();

		$this->loader = new PDSWT_Loader();
		$this->set_locale();
		$this->define_content_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function set_locale() {
		$i18n = new PDSWT_i18n();
		$this->loader->add_action( 'init', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * CPT de conocimiento + auto-reindex al guardar/borrar.
	 */
	private function define_content_hooks() {
		$cpt = new PDSWT_CPT();
		$this->loader->add_action( 'init', $cpt, 'register' );

		$this->loader->add_action( 'save_post', $this, 'on_save_post', 20, 3 );
		$this->loader->add_action( 'trashed_post', $this, 'on_delete_post' );
		$this->loader->add_action( 'before_delete_post', $this, 'on_delete_post' );
	}

	private function define_admin_hooks() {
		$admin = new PDSWT_Admin();
		$this->loader->add_action( 'admin_menu', $admin, 'add_settings_page' );
		$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
		$this->loader->add_action( 'wp_ajax_pdswt_test_chat', $admin, 'ajax_test_chat' );

		// La UI de indexación vive en la pestaña "Indexación" de Ajustes; aquí solo los AJAX y handlers de archivo.
		$index = new PDSWT_Index_Page();
		$this->loader->add_action( 'wp_ajax_pdswt_index_ids', $index, 'ajax_ids' );
		$this->loader->add_action( 'wp_ajax_pdswt_index_batch', $index, 'ajax_batch' );
		$this->loader->add_action( 'wp_ajax_pdswt_index_clear', $index, 'ajax_clear' );
		$this->loader->add_action( 'wp_ajax_pdswt_index_export', $index, 'ajax_export' );
		$this->loader->add_action( 'admin_post_pdswt_corpus_regen', $index, 'handle_regen' );
		$this->loader->add_action( 'admin_post_pdswt_corpus_download', $index, 'handle_download' );
		$this->loader->add_action( 'admin_post_pdswt_corpus_import', $index, 'handle_import' );

		// "Uso y coste" vive en la pestaña de Ajustes; aquí solo el guardado de precios.
		$usage = new PDSWT_Usage_Page();
		$this->loader->add_action( 'admin_post_pdswt_save_rates', $usage, 'handle_save_rates' );

		// Metabox "Pieza para el chatbot" en proyecto/partners.
		$piece = new PDSWT_Piece_Meta();
		$this->loader->add_action( 'add_meta_boxes', $piece, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $piece, 'save', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $piece, 'enqueue' );
	}

	/**
	 * Reindexa un post al guardarlo (si es de un tipo indexable y está publicado).
	 */
	public function on_save_post( $post_id, $post = null, $update = null ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$settings = self::get_settings();
		$types    = ! empty( $settings['index_post_types'] ) ? (array) $settings['index_post_types'] : array();
		$ptype    = get_post_type( $post_id );
		if ( ! in_array( $ptype, $types, true ) ) {
			return;
		}
		// Reindex en segundo plano tras la respuesta (no bloquea el guardado).
		$indexer = new PDSWT_Indexer( $settings );
		$indexer->index_post( $post_id );
	}

	/**
	 * Elimina los chunks de un post al enviarlo a papelera o borrarlo.
	 */
	public function on_delete_post( $post_id ) {
		$indexer = new PDSWT_Indexer();
		$indexer->delete_post( $post_id );
	}

	/**
	 * Endpoint REST público + widget de chat (shortcode/bloque).
	 */
	private function define_public_hooks() {
		$rest = new PDSWT_Rest();
		$this->loader->add_action( 'rest_api_init', $rest, 'register_routes' );

		$front = new PDSWT_Frontend();
		$this->loader->add_action( 'init', $front, 'register_assets' );
		$this->loader->add_action( 'init', $front, 'register_block' );
		$this->loader->add_action( 'init', $front, 'register_shortcode' );
	}

	public function run() {
		$this->loader->run();
	}

	/**
	 * Acceso centralizado a los ajustes.
	 */
	public static function get_settings() {
		$settings = get_option( PDSWT_OPTION_KEY );
		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Visibilidad seleccionada de un post type: 'shown' (indexar y mostrar)
	 * o 'hidden' (indexar y no mostrar). Si no se ha elegido, se deriva del
	 * flag público del post type.
	 */
	public static function get_visibility( $post_type ) {
		$s   = self::get_settings();
		$map = isset( $s['index_visibility'] ) ? (array) $s['index_visibility'] : array();
		if ( isset( $map[ $post_type ] ) && in_array( $map[ $post_type ], array( 'shown', 'hidden' ), true ) ) {
			return $map[ $post_type ];
		}
		$obj = get_post_type_object( $post_type );
		return ( $obj && ! empty( $obj->public ) ) ? 'shown' : 'hidden';
	}
}
