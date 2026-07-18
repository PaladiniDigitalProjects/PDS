<?php
/**
 * Activación y upgrades de esquema.
 * Crea la tabla de corpus e inicializa los ajustes por defecto.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Activator {

	const DB_VERSION_OPTION = 'pdswt_db_version';
	const DB_VERSION        = '3'; // Subir al cambiar el esquema. v3: piezas visuales (is_component + render_data).

	public static function activate() {
		self::seed_settings();
		self::create_corpus_table();
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Se ejecuta en cada carga (barato): si el esquema cambió, lo actualiza.
	 * Necesario porque el plugin ya estaba activo antes de existir la tabla.
	 */
	public static function maybe_upgrade() {
		if ( get_option( self::DB_VERSION_OPTION ) !== self::DB_VERSION ) {
			self::seed_settings();
			self::create_corpus_table();
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
		}
	}

	/**
	 * Nombre real de la tabla de corpus (respeta el prefijo, aquí `PDP_`).
	 */
	public static function corpus_table() {
		global $wpdb;
		return $wpdb->prefix . 'pdswt_corpus';
	}

	private static function create_corpus_table() {
		global $wpdb;
		$table           = self::corpus_table();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			post_type VARCHAR(32) NOT NULL DEFAULT '',
			chunk_index INT(11) NOT NULL DEFAULT 0,
			chunk_text LONGTEXT NOT NULL,
			text_hash CHAR(32) NOT NULL DEFAULT '',
			embedding LONGTEXT NULL,
			token_estimate INT(11) NOT NULL DEFAULT 0,
			lang VARCHAR(8) NOT NULL DEFAULT '',
			weight FLOAT NOT NULL DEFAULT 1,
			is_component TINYINT(1) NOT NULL DEFAULT 0,
			render_data LONGTEXT NULL,
			updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY post_id (post_id),
			KEY post_type (post_type),
			KEY text_hash (text_hash),
			KEY is_component (is_component)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function seed_settings() {
		$defaults = array(
			'provider'            => 'claude',
			'claude_api_key'      => '',
			'claude_model'        => 'claude-sonnet-5',
			'openai_api_key'      => '',
			'openai_model'        => 'gpt-4o-mini',
			'embedding_model'     => 'text-embedding-3-small',
			'index_post_types'    => array( 'page', 'post', 'proyecto', 'pdswt_knowledge' ),
			'top_n'               => 4,
			'system_prompt'       => 'Eres el asistente de Paladini Digital. Responde SIEMPRE en el mismo idioma en que escribe el usuario. Sé claro y conciso. Usa solo la información del contexto proporcionado; si no está ahí, dilo con honestidad en vez de inventar.',
			'monthly_budget'      => 20,
			'rate_limit_per_hour' => 30,
			'max_message_length'  => 1000,
		);

		$existing = get_option( PDSWT_OPTION_KEY );
		if ( false === $existing || ! is_array( $existing ) ) {
			add_option( PDSWT_OPTION_KEY, $defaults );
		} else {
			update_option( PDSWT_OPTION_KEY, array_merge( $defaults, $existing ) );
		}
	}
}
