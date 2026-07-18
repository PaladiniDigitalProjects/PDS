<?php
/**
 * Indexer: convierte posts en fragmentos con embeddings y los guarda en el corpus.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Indexer {

	private $settings;
	private $embedder;

	public function __construct( $settings = null ) {
		$this->settings = is_array( $settings ) ? $settings : PDSWT::get_settings();
		$this->embedder = new PDSWT_OpenAI_Embeddings(
			isset( $this->settings['openai_api_key'] ) ? $this->settings['openai_api_key'] : '',
			isset( $this->settings['embedding_model'] ) ? $this->settings['embedding_model'] : 'text-embedding-3-small'
		);
	}

	/**
	 * IDs de posts indexables (publicados) según los post types de los ajustes.
	 */
	public function get_indexable_ids() {
		$types = ! empty( $this->settings['index_post_types'] ) ? (array) $this->settings['index_post_types'] : array( 'page', 'post' );
		$ids   = get_posts( array(
			'post_type'      => $types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'suppress_filters' => true,
		) );
		return array_map( 'intval', $ids );
	}

	/**
	 * Indexa un post: borra sus chunks previos, trocea, calcula embeddings y guarda.
	 *
	 * @return array { ok, chunks, error }
	 */
	public function index_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			$this->delete_post( $post_id );
			return array( 'ok' => true, 'chunks' => 0 );
		}

		if ( ! $this->embedder->is_configured() ) {
			return array( 'ok' => false, 'error' => __( 'Falta la API key de OpenAI (embeddings).', 'pds-web-talk' ) );
		}

		$chunks = PDSWT_Chunker::chunk( $post->post_title, $post->post_content );
		if ( empty( $chunks ) ) {
			$this->delete_post( $post_id );
			return array( 'ok' => true, 'chunks' => 0 );
		}

		// Lo que se embebe lleva el contexto de procedencia; lo que se guarda
		// y se le pasa al modelo es el fragmento original.
		$embed_texts = array();
		foreach ( $chunks as $i => $text ) {
			$embed_texts[ $i ] = PDSWT_Chunker::embed_text( $post->post_title, $text );
		}

		// Hash por fragmento y reutilización de embeddings ya calculados (ahorro de coste).
		$hashes = array_map( function ( $t ) { return md5( $t ); }, $embed_texts );
		$cache  = $this->get_cached_embeddings( array_unique( $hashes ) );

		// Solo se piden a la API los fragmentos que no estén ya en caché.
		$to_embed     = array();
		$to_embed_map = array();
		foreach ( $embed_texts as $i => $text ) {
			if ( ! isset( $cache[ $hashes[ $i ] ] ) ) {
				$to_embed_map[ count( $to_embed ) ] = $i;
				$to_embed[] = $text;
			}
		}

		$fresh = array();
		if ( ! empty( $to_embed ) ) {
			$result = $this->embedder->embed( $to_embed );
			if ( empty( $result['ok'] ) ) {
				return array( 'ok' => false, 'error' => $result['error'] );
			}
			foreach ( $result['vectors'] as $k => $vector ) {
				if ( isset( $to_embed_map[ $k ] ) ) {
					$fresh[ $to_embed_map[ $k ] ] = wp_json_encode( $vector );
				}
			}
		}

		// Reemplaza los chunks del post: borra y reinserta.
		$this->delete_post( $post_id );

		global $wpdb;
		$table   = PDSWT_Activator::corpus_table();
		$now     = current_time( 'mysql' );
		$lang    = substr( get_locale(), 0, 2 );
		$saved   = 0;
		$reused  = 0;

		foreach ( $chunks as $i => $text ) {
			if ( isset( $cache[ $hashes[ $i ] ] ) ) {
				$embedding_json = $cache[ $hashes[ $i ] ];
				$reused++;
			} elseif ( isset( $fresh[ $i ] ) ) {
				$embedding_json = $fresh[ $i ];
			} else {
				continue;
			}
			$wpdb->insert(
				$table,
				array(
					'post_id'        => $post_id,
					'post_type'      => $post->post_type,
					'chunk_index'    => $i,
					'chunk_text'     => $text,
					'text_hash'      => $hashes[ $i ],
					'embedding'      => $embedding_json,
					'token_estimate' => (int) ceil( strlen( $text ) / 4 ),
					'lang'           => $lang,
					'weight'         => 1,
					'updated_at'     => $now,
				),
				array( '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%f', '%s' )
			);
			$saved++;
		}

		// Si la entrada está catalogada como pieza visual, añade su componente.
		$this->maybe_index_piece( $post );

		return array( 'ok' => true, 'chunks' => $saved, 'reused' => $reused, 'embedded' => count( $to_embed ) );
	}

	/**
	 * Indexa la "pieza mostrable" de una entrada catalogada: un registro de
	 * componente con su texto (para recuperación), embedding, datos de render
	 * y peso. Se recupera como cualquier fragmento, pero se pinta como tarjeta.
	 */
	private function maybe_index_piece( $post ) {
		if ( ! class_exists( 'PDSWT_Piece_Meta' ) || ! PDSWT_Piece_Meta::is_enabled( $post->ID ) ) {
			return;
		}
		$piece = PDSWT_Piece_Meta::get_piece( $post->ID );
		if ( ! $piece ) {
			return;
		}

		// Texto por el que la búsqueda semántica encuentra la pieza.
		$body = trim( $piece['title'] . ' — ' . $piece['category'] );
		$text  = PDSWT_Chunker::embed_text( $post->post_title, $body );
		$hash  = md5( $text );

		$cache = $this->get_cached_embeddings( array( $hash ) );
		if ( isset( $cache[ $hash ] ) ) {
			$embedding_json = $cache[ $hash ];
		} else {
			$res = $this->embedder->embed( $text );
			if ( empty( $res['ok'] ) ) {
				return;
			}
			$embedding_json = wp_json_encode( $res['vectors'][0] );
		}

		global $wpdb;
		$wpdb->insert(
			PDSWT_Activator::corpus_table(),
			array(
				'post_id'        => $post->ID,
				'post_type'      => $post->post_type,
				'chunk_index'    => 9999,
				'chunk_text'     => $body,
				'text_hash'      => $hash,
				'embedding'      => $embedding_json,
				'token_estimate' => (int) ceil( strlen( $body ) / 4 ),
				'lang'           => substr( get_locale(), 0, 2 ),
				'weight'         => (float) $piece['weight'],
				'is_component'   => 1,
				'render_data'    => wp_json_encode( $piece ),
				'updated_at'     => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%f', '%d', '%s', '%s' )
		);
	}

	/**
	 * Devuelve un mapa hash => embedding(JSON) para los hashes dados,
	 * leyendo del corpus existente. Permite reutilizar vectores ya pagados.
	 */
	private function get_cached_embeddings( $hashes ) {
		if ( empty( $hashes ) ) {
			return array();
		}
		global $wpdb;
		$table        = PDSWT_Activator::corpus_table();
		$placeholders = implode( ',', array_fill( 0, count( $hashes ), '%s' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT text_hash, embedding FROM {$table} WHERE text_hash IN ({$placeholders}) AND embedding IS NOT NULL",
				$hashes
			)
		);
		$map = array();
		foreach ( (array) $rows as $row ) {
			if ( '' !== $row->text_hash && ! isset( $map[ $row->text_hash ] ) ) {
				$map[ $row->text_hash ] = $row->embedding;
			}
		}
		return $map;
	}

	/**
	 * Borra los chunks de un post del corpus.
	 */
	public function delete_post( $post_id ) {
		global $wpdb;
		$table = PDSWT_Activator::corpus_table();
		$wpdb->delete( $table, array( 'post_id' => (int) $post_id ), array( '%d' ) );
	}

	/**
	 * Vacía todo el corpus.
	 */
	public function clear_all() {
		global $wpdb;
		$table = PDSWT_Activator::corpus_table();
		$wpdb->query( "TRUNCATE TABLE {$table}" );
	}

	/**
	 * Estadísticas del índice.
	 */
	public function get_stats() {
		global $wpdb;
		$table = PDSWT_Activator::corpus_table();
		return array(
			'chunks' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
			'posts'  => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$table}" ),
		);
	}
}
