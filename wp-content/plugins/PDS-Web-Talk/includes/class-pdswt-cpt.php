<?php
/**
 * CPT "Conocimiento del bot": contenido oculto para el frontend
 * pero indexable por el RAG (valores, FAQs, tono, límites…).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_CPT {

	const POST_TYPE = 'pdswt_knowledge';

	public function register() {
		$labels = array(
			'name'          => __( 'Conocimiento del bot', 'pds-web-talk' ),
			'singular_name' => __( 'Entrada de conocimiento', 'pds-web-talk' ),
			'add_new'       => __( 'Añadir', 'pds-web-talk' ),
			'add_new_item'  => __( 'Añadir conocimiento', 'pds-web-talk' ),
			'edit_item'     => __( 'Editar conocimiento', 'pds-web-talk' ),
			'new_item'      => __( 'Nuevo conocimiento', 'pds-web-talk' ),
			'search_items'  => __( 'Buscar conocimiento', 'pds-web-talk' ),
			'not_found'     => __( 'Sin entradas', 'pds-web-talk' ),
			'menu_name'     => __( 'Conocimiento del bot', 'pds-web-talk' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => $labels,
				'public'             => false,      // Sin frontend, sin URL pública.
				'publicly_queryable' => false,
				'exclude_from_search'=> true,
				'show_ui'            => true,       // Editable en admin.
				'show_in_menu'       => 'pds-web-talk', // Anidado bajo el menú del plugin.
				'show_in_rest'       => true,       // Editor de bloques (Gutenberg).
				'supports'           => array( 'title', 'editor', 'revisions' ),
				'has_archive'        => false,
				'rewrite'            => false,
				'can_export'         => true,
			)
		);
	}
}
