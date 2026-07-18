<?php
/**
 * Página de admin para construir el índice (corpus) por lotes.
 * Presentación en admin/partials/index-page.php.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Index_Page {

	const MENU_SLUG  = 'pdswt-index';
	const BATCH_SIZE = 3; // Posts por lote (evita timeouts en hosting compartido).

	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=' . PDSWT_CPT::POST_TYPE,
			__( 'Indexación del bot', 'pds-web-talk' ),
			__( 'Indexación', 'pds-web-talk' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' )
		);
	}

	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, self::MENU_SLUG ) ) {
			return;
		}
		wp_enqueue_style( 'pdswt-admin', PDSWT_PLUGIN_URL . 'admin/css/pdswt-admin.css', array(), PDSWT_VERSION );
		wp_enqueue_script( 'pdswt-index', PDSWT_PLUGIN_URL . 'admin/js/pdswt-index.js', array( 'jquery' ), PDSWT_VERSION, true );
		wp_localize_script( 'pdswt-index', 'pdswtIndex', array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'pdswt_index' ),
			'batchSize' => self::BATCH_SIZE,
			'i18n'      => array(
				'preparing' => __( 'Preparando…', 'pds-web-talk' ),
				'indexing'  => __( 'Indexando', 'pds-web-talk' ),
				'done'      => __( 'Indexación completada.', 'pds-web-talk' ),
				'cleared'   => __( 'Índice vaciado.', 'pds-web-talk' ),
				'error'     => __( 'Error', 'pds-web-talk' ),
				'confirmClear' => __( '¿Vaciar todo el índice?', 'pds-web-talk' ),
			),
		) );
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$indexer = new PDSWT_Indexer();
		$stats   = $indexer->get_stats();
		$settings = PDSWT::get_settings();
		require PDSWT_PLUGIN_DIR . 'admin/partials/index-page.php';
	}

	/* ------------------------------------------------------------------ */
	/*  AJAX                                                              */
	/* ------------------------------------------------------------------ */

	private function guard() {
		check_ajax_referer( 'pdswt_index', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'No autorizado.', 'pds-web-talk' ) ), 403 );
		}
	}

	/** Devuelve la lista de IDs a indexar. */
	public function ajax_ids() {
		$this->guard();
		$indexer = new PDSWT_Indexer();
		$ids     = $indexer->get_indexable_ids();
		wp_send_json_success( array( 'ids' => $ids, 'total' => count( $ids ) ) );
	}

	/** Indexa un lote de IDs. */
	public function ajax_batch() {
		$this->guard();
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Lote vacío.', 'pds-web-talk' ) ), 400 );
		}

		$indexer = new PDSWT_Indexer();
		$results = array();
		$chunks  = 0;
		foreach ( $ids as $id ) {
			$r = $indexer->index_post( $id );
			if ( empty( $r['ok'] ) ) {
				wp_send_json_error( array( 'message' => $r['error'], 'post_id' => $id ) );
			}
			$chunks   += (int) $r['chunks'];
			$results[] = array( 'id' => $id, 'title' => get_the_title( $id ), 'chunks' => (int) $r['chunks'] );
		}
		wp_send_json_success( array( 'results' => $results, 'chunks' => $chunks ) );
	}

	/** Vacía el corpus. */
	public function ajax_clear() {
		$this->guard();
		$indexer = new PDSWT_Indexer();
		$indexer->clear_all();
		PDSWT_Corpus_IO::export(); // Refleja el vaciado en el archivo.
		wp_send_json_success( array( 'stats' => $indexer->get_stats() ) );
	}

	/** Regenera el archivo JSON al terminar la indexación (llamado por el JS). */
	public function ajax_export() {
		$this->guard();
		$r = PDSWT_Corpus_IO::export();
		if ( empty( $r['ok'] ) ) {
			wp_send_json_error( array( 'message' => $r['error'] ) );
		}
		wp_send_json_success( $r );
	}

	/* ------------------------------------------------------------------ */
	/*  Archivo de corpus (admin-post)                                    */
	/* ------------------------------------------------------------------ */

	private function guard_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No autorizado.', 'pds-web-talk' ) );
		}
		check_admin_referer( 'pdswt_corpus' );
	}

	private function back( $args ) {
		$url = admin_url( 'admin.php?page=' . PDSWT_Admin::MENU_SLUG . '&tab=index' );
		wp_safe_redirect( add_query_arg( $args, $url ) );
		exit;
	}

	public function handle_regen() {
		$this->guard_post();
		$r = PDSWT_Corpus_IO::export();
		$this->back( array( 'pdswt_msg' => empty( $r['ok'] ) ? 'regen_err' : 'regen_ok' ) );
	}

	public function handle_download() {
		$this->guard_post();
		$path = PDSWT_Corpus_IO::file_path();
		if ( ! file_exists( $path ) ) {
			$this->back( array( 'pdswt_msg' => 'nofile' ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="pdswt-corpus-' . gmdate( 'Ymd-His' ) . '.json"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path );
		exit;
	}

	public function handle_import() {
		$this->guard_post();
		if ( empty( $_FILES['corpus_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['corpus_file']['tmp_name'] ) ) {
			$this->back( array( 'pdswt_msg' => 'import_err' ) );
		}
		$contents = file_get_contents( $_FILES['corpus_file']['tmp_name'] );
		$r        = PDSWT_Corpus_IO::import( $contents );
		if ( empty( $r['ok'] ) ) {
			$this->back( array( 'pdswt_msg' => 'import_err' ) );
		}
		PDSWT_Corpus_IO::export(); // Reescribe el archivo canónico tras importar.
		$this->back( array( 'pdswt_msg' => 'import_ok', 'n' => (int) $r['chunks'] ) );
	}
}
