<?php
/**
 * Exporta / importa el corpus a un archivo JSON (estructura estilo API de WP).
 * El archivo vive en un directorio PROTEGIDO (contiene conocimiento oculto).
 * Sirve como copia portable, verificación manual y caché anti-coste (import = 0 llamadas a IA).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Corpus_IO {

	const FILENAME = 'corpus.json';

	/**
	 * Directorio protegido en wp-content/pdswt-corpus/.
	 * (Fuera de uploads a propósito: en Lando, uploads se excluye de la
	 * sincronización host↔contenedor, así el archivo sí es visible en el proyecto.)
	 */
	public static function dir() {
		return trailingslashit( WP_CONTENT_DIR ) . 'pdswt-corpus';
	}

	public static function file_path() {
		return trailingslashit( self::dir() ) . self::FILENAME;
	}

	/**
	 * Crea el directorio y lo blinda (.htaccess deny + index.php).
	 */
	public static function ensure_dir() {
		$dir = self::dir();
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$htaccess = trailingslashit( $dir ) . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			$rules = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n";
			file_put_contents( $htaccess, $rules );
		}
		$index = trailingslashit( $dir ) . 'index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php // Silence is golden.\n" );
		}
		return $dir;
	}

	/**
	 * Exporta el corpus actual a JSON y lo escribe en el archivo.
	 *
	 * @return array { ok, items, chunks, bytes, path, error }
	 */
	public static function export() {
		self::ensure_dir();
		global $wpdb;
		$table = PDSWT_Activator::corpus_table();

		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY post_id, chunk_index" );

		$items    = array();
		$n_chunks = 0;
		foreach ( (array) $rows as $r ) {
			$pid = (int) $r->post_id;
			if ( ! isset( $items[ $pid ] ) ) {
				$items[ $pid ] = array(
					'id'         => $pid,
					'type'       => $r->post_type,
					'slug'       => get_post_field( 'post_name', $pid ),
					'title'      => get_the_title( $pid ),
					'link'       => get_permalink( $pid ),
					'modified'   => get_post_field( 'post_modified_gmt', $pid ),
					'status'     => get_post_status( $pid ),
					'visibility' => PDSWT::get_visibility( $r->post_type ),
					'lang'       => $r->lang,
					'chunks'     => array(),
				);
			}
			$embedding = json_decode( $r->embedding, true );
			$items[ $pid ]['chunks'][] = array(
				'index'          => (int) $r->chunk_index,
				'text'           => $r->chunk_text,
				'hash'           => $r->text_hash,
				'tokens'         => (int) $r->token_estimate,
				'embedding_dims' => is_array( $embedding ) ? count( $embedding ) : 0,
				'embedding'      => $embedding,
			);
			$n_chunks++;
		}

		$payload = array(
			'generated'       => gmdate( 'c' ),
			'site'            => home_url(),
			'embedding_model' => ( $s = PDSWT::get_settings() ) && ! empty( $s['embedding_model'] ) ? $s['embedding_model'] : '',
			'items_count'     => count( $items ),
			'chunks_count'    => $n_chunks,
			'items'           => array_values( $items ),
		);

		$json  = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$bytes = file_put_contents( self::file_path(), $json );

		if ( false === $bytes ) {
			return array( 'ok' => false, 'error' => __( 'No se pudo escribir el archivo de corpus.', 'pds-web-talk' ) );
		}
		return array( 'ok' => true, 'items' => count( $items ), 'chunks' => $n_chunks, 'bytes' => $bytes, 'path' => self::file_path() );
	}

	/**
	 * Importa el corpus desde un JSON (reemplaza el actual). Sin llamadas a la IA.
	 *
	 * @return array { ok, items, chunks, error }
	 */
	public static function import( $json_string ) {
		$data = json_decode( $json_string, true );
		if ( ! is_array( $data ) || empty( $data['items'] ) ) {
			return array( 'ok' => false, 'error' => __( 'JSON no válido o sin items.', 'pds-web-talk' ) );
		}

		global $wpdb;
		$table = PDSWT_Activator::corpus_table();
		$wpdb->query( "TRUNCATE TABLE {$table}" );

		$now      = current_time( 'mysql' );
		$n_items  = 0;
		$n_chunks = 0;
		foreach ( $data['items'] as $item ) {
			$pid   = isset( $item['id'] ) ? (int) $item['id'] : 0;
			$ptype = isset( $item['type'] ) ? sanitize_key( $item['type'] ) : '';
			$lang  = isset( $item['lang'] ) ? substr( sanitize_text_field( $item['lang'] ), 0, 8 ) : '';
			if ( empty( $item['chunks'] ) ) {
				continue;
			}
			$n_items++;
			foreach ( $item['chunks'] as $c ) {
				if ( empty( $c['embedding'] ) ) {
					continue;
				}
				$text = isset( $c['text'] ) ? $c['text'] : '';
				$wpdb->insert(
					$table,
					array(
						'post_id'        => $pid,
						'post_type'      => $ptype,
						'chunk_index'    => isset( $c['index'] ) ? (int) $c['index'] : 0,
						'chunk_text'     => $text,
						'text_hash'      => isset( $c['hash'] ) && $c['hash'] ? $c['hash'] : md5( $text ),
						'embedding'      => wp_json_encode( $c['embedding'] ),
						'token_estimate' => isset( $c['tokens'] ) ? (int) $c['tokens'] : 0,
						'lang'           => $lang,
						'weight'         => 1,
						'updated_at'     => $now,
					),
					array( '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%f', '%s' )
				);
				$n_chunks++;
			}
		}
		return array( 'ok' => true, 'items' => $n_items, 'chunks' => $n_chunks );
	}

	/**
	 * Info del archivo para mostrar en el admin.
	 */
	public static function info() {
		$path = self::file_path();
		if ( ! file_exists( $path ) ) {
			return array( 'exists' => false );
		}
		return array(
			'exists'   => true,
			'path'     => $path,
			'size'     => filesize( $path ),
			'modified' => filemtime( $path ),
		);
	}
}
