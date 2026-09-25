<?php

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

function my_theme_enqueue_styles() {
	$parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
	$theme        = wp_get_theme();
	wp_enqueue_style( $parenthandle,
		get_template_directory_uri() . '/style.css',
		array(),  // If the parent theme code has a dependency, copy it to here.
		$theme->parent()->get( 'Version' )
	);

	wp_enqueue_style( 'child-style',
		get_stylesheet_uri(),
		array( $parenthandle ),
		$theme->get( 'Version' ) // This only works if you have Version defined in the style header.
	);

	// Versión = fecha del fichero: al recompilar el SCSS o tocar el JS, los navegadores
	// dejan de servir la copia en caché sin tener que subir el número a mano.
	$css = get_stylesheet_directory() . '/assets/css/estils.css';
	$js  = get_stylesheet_directory() . '/assets/js/main.js';
	wp_enqueue_style( 'child-estils', get_template_directory_uri() . '-child/assets/css/estils.css',
		array(), file_exists( $css ) ? filemtime( $css ) : '1.0.0' );
	wp_enqueue_script('main',  get_template_directory_uri() . '-child/assets/js/main.js', array(),
		file_exists( $js ) ? filemtime( $js ) : '1.0.0', true);
	wp_enqueue_script('ajax',  'https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.6.0/p5.min.js');	
}

/* ADD ADMIN AND LOGIN STYLES */

function wpdocs_enqueue_custom_admin_style() {
	wp_register_style( 'custom_wp_admin_css', get_template_directory_uri() . '/assets/css/admin-styles.css', false, '1.0.0' );
	wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'wpdocs_enqueue_custom_admin_style' );

function login_stylesheet() {
    wp_enqueue_style( 'custom-login', get_stylesheet_directory_uri() . '/assets/css/login-styles.css' );
}
add_action( 'login_enqueue_scripts', 'login_stylesheet' );

/* ADD STYLE TO BUTTONS */

function register_button_block_style() {
	register_block_style(
		'core/button', // name of your block
		array(
			'name'  => 'arrow-button', // part of the class that gets added to the block.
			'label' => __( 'Botó amb icona', 'PDP' ),
		)
	);
  }
  add_action( 'init', 'register_button_block_style' );

/* TEMPLATE NAME */

/**
 * Set up My Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be added to the /languages/ directory.
 */
function twentytwentyfour_theme_setup() {
	load_child_theme_textdomain( 'twentytwentyfour', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'twentytwentyfour_theme_setup' );

/* ADMIN FEATURED PAGE */

// show featured images in dashboard
add_image_size( 'haizdesign-admin-post-featured-image', 120, 120, false );

// Add the posts and pages columns filter. They both use the same function.
add_filter('manage_posts_columns', 'haizdesign_add_post_admin_thumbnail_column', 2);
add_filter('manage_pages_columns', 'haizdesign_add_post_admin_thumbnail_column', 2);

// Add the column
function haizdesign_add_post_admin_thumbnail_column($haizdesign_columns){
    $haizdesign_columns['haizdesign_thumb'] = __('Featured Image');
    return $haizdesign_columns;
}

// Manage Post and Page Admin Panel Columns
add_action('manage_posts_custom_column', 'haizdesign_show_post_thumbnail_column', 5, 2);
add_action('manage_pages_custom_column', 'haizdesign_show_post_thumbnail_column', 5, 2);

// Get featured-thumbnail size post thumbnail and display it
function haizdesign_show_post_thumbnail_column($haizdesign_columns, $haizdesign_id){
    switch($haizdesign_columns){
        case 'haizdesign_thumb':
        if( function_exists('the_post_thumbnail') ) {
            echo the_post_thumbnail( 'haizdesign-admin-post-featured-image' );
        }
        else
            echo 'hmm… your theme doesn\'t support featured image…';
        break;
    }
}

/* POST TYPE */

/* POST TYPE — `proyecto`
   Aquí había DOS funciones idénticas registrando el mismo CPT (`cptui_register_my_cpts`
   y `cptui_register_my_cpts_proyecto`), más una tercera definición en ACF (post 10813,
   importada de CPT UI en 2024). El 2026-09-23 se deja solo esta y se desactiva la de ACF:
   el registro vive en código. */

function cptui_register_my_cpts_proyecto() {

	/**
	 * Post Type: Proyectos.
	 */

	$labels = [
		"name" => esc_html__( "Proyectos", "PDS" ),
		"singular_name" => esc_html__( "Proyecto", "PDS" ),
		"menu_name" => esc_html__( "Proyectos", "PDS" ),
		"all_items" => esc_html__( "Todos los Proyectos", "PDS" ),
		"add_new" => esc_html__( "Añadir nuevo", "PDS" ),
		"add_new_item" => esc_html__( "Añadir nuevo Proyecto", "PDS" ),
		"edit_item" => esc_html__( "Editar Proyecto", "PDS" ),
		"new_item" => esc_html__( "Nuevo Proyecto", "PDS" ),
		"view_item" => esc_html__( "Ver Proyecto", "PDS" ),
		"view_items" => esc_html__( "Ver Proyectos", "PDS" ),
		"search_items" => esc_html__( "Buscar Proyectos", "PDS" ),
		"not_found" => esc_html__( "No se ha encontrado Proyectos", "PDS" ),
		"not_found_in_trash" => esc_html__( "No se han encontrado Proyectos en la papelera", "PDS" ),
		"parent" => esc_html__( "Proyecto superior", "PDS" ),
		"featured_image" => esc_html__( "Imagen destacada para Proyecto", "PDS" ),
		"set_featured_image" => esc_html__( "Establece una imagen destacada para Proyecto", "PDS" ),
		"remove_featured_image" => esc_html__( "Eliminar la imagen destacada de Proyecto", "PDS" ),
		"use_featured_image" => esc_html__( "Usar como imagen destacada de Proyecto", "PDS" ),
		"archives" => esc_html__( "Archivos de Proyecto", "PDS" ),
		"insert_into_item" => esc_html__( "Insertar en Proyecto", "PDS" ),
		"uploaded_to_this_item" => esc_html__( "Subir a Proyecto", "PDS" ),
		"filter_items_list" => esc_html__( "Filtrar la lista de Proyectos", "PDS" ),
		"items_list_navigation" => esc_html__( "Navegación de la lista de Proyectos", "PDS" ),
		"items_list" => esc_html__( "Lista de Proyectos", "PDS" ),
		"attributes" => esc_html__( "Atributos de Proyectos", "PDS" ),
		"name_admin_bar" => esc_html__( "Proyecto", "PDS" ),
		"item_published" => esc_html__( "Proyecto publicado", "PDS" ),
		"item_published_privately" => esc_html__( "Proyecto publicado como privado.", "PDS" ),
		"item_reverted_to_draft" => esc_html__( "Proyecto devuelto a borrador.", "PDS" ),
		"item_trashed" => esc_html__( "Proyecto enviado a la papelera.", "PDS" ),
		"item_scheduled" => esc_html__( "Proyecto programado", "PDS" ),
		"item_updated" => esc_html__( "Proyecto actualizado.", "PDS" ),
		"parent_item_colon" => esc_html__( "Proyecto superior", "PDS" ),
	];

	$args = [
		"label" => esc_html__( "Proyectos", "PDS" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"can_export" => true,
		"rewrite" => [ "slug" => "proyecto", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "excerpt", "custom-fields", "page-attributes", "post-formats" ],
		"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "proyecto", $args );
}

add_action( 'init', 'cptui_register_my_cpts_proyecto' );

/* HIDE PATTERN */

add_action('init', function() {
    remove_theme_support('core-block-patterns');
});
  
/* EDIT PAGE */

edit_post_link( __( 'Editar', 'textdomain' ), '<p>', '</p>', null, 'btn btn-primary btn-edit-post-link' );
add_filter('the_content', 'mycontent');
add_filter('avf_template_builder_content', 'mycontent');

function mycontent( $content ) {
	if( is_singular() && is_user_logged_in() ) {
		$content = $content . '<div class="btn btn-primary edit-post-link"><a href="' . get_edit_post_link( get_the_ID(), 'Editar') . '">Editar</a></div>';
	}
	return $content;
}
/* ─────────────────────────────────────────────────────────────
   CASE STUDIES
   CPT `case_study`.
   Un case study es la versión ampliada y en profundidad de un
   `proyecto`. El vínculo con el proyecto de origen se guarda en
   el meta `_pds_extends_project` (metabox más abajo): permite
   redirigir las citas del chatbot al caso nuevo y decidir si el
   proyecto antiguo sigue pesando en el corpus del RAG.
   ───────────────────────────────────────────────────────────── */

function pds_register_case_study_cpt() {

	register_post_type( 'case_study', [
		'label'               => 'Case Studies',
		'labels'              => [
			'name'          => 'Case Studies',
			'singular_name' => 'Case Study',
			'menu_name'     => 'Case Studies',
			'all_items'     => 'Todos los Case Studies',
			'add_new_item'  => 'Añadir nuevo Case Study',
			'edit_item'     => 'Editar Case Study',
			'view_item'     => 'Ver Case Study',
			'search_items'  => 'Buscar Case Studies',
			'not_found'     => 'No se han encontrado Case Studies',
		],
		'public'              => true,
		'publicly_queryable'  => true,
		'show_in_rest'        => true,
		'hierarchical'        => false,
		'has_archive'         => 'case-studies',
		'rewrite'             => [ 'slug' => 'case-studies', 'with_front' => false ],
		'menu_icon'           => 'dashicons-portfolio',
		'menu_position'       => 21,
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ],
	] );

}
add_action( 'init', 'pds_register_case_study_cpt' );

/**
 * CPT `service` — las páginas de servicio.
 *
 * Se comporta como una página: mismos `supports` que `page` (incluido
 * `page-attributes`, para ordenarlos a mano) y `hierarchical`, por si algún
 * servicio cuelga de otro.
 *
 * El slug de URL es `services`, en plural. Lo era para no chocar con la
 * taxonomía `pds_service`, eliminada el 2026-09-23 al quedar este CPT como
 * única fuente de los servicios; se mantiene el plural por no cambiar URLs.
 */
function pds_register_service_cpt() {

	register_post_type( 'service', [
		'labels' => [
			'name'          => 'Servicios',
			'singular_name' => 'Servicio',
			'menu_name'     => 'Servicios',
			'add_new_item'  => 'Añadir nuevo servicio',
			'edit_item'     => 'Editar servicio',
			'all_items'     => 'Todos los servicios',
		],
		'public'        => true,
		'hierarchical'  => true,
		'has_archive'   => 'services',
		'rewrite'       => [ 'slug' => 'services', 'with_front' => false ],
		'menu_position' => 20,
		'menu_icon'     => 'dashicons-screenoptions',
		'show_in_rest'  => true,
		// Los mismos que `page`, verificados con get_all_post_type_supports('page').
		'supports'      => [ 'title', 'editor', 'author', 'thumbnail', 'page-attributes', 'custom-fields', 'revisions' ],
	] );
}
add_action( 'init', 'pds_register_service_cpt' );

/**
 * Metabox: qué proyecto amplía este case study.
 */
function pds_case_study_add_metabox() {
	add_meta_box(
		'pds_extends_project',
		'Amplía el proyecto',
		'pds_case_study_metabox_html',
		'case_study',
		'side'
	);
}
add_action( 'add_meta_boxes', 'pds_case_study_add_metabox' );

function pds_case_study_metabox_html( $post ) {

	wp_nonce_field( 'pds_extends_project_save', 'pds_extends_project_nonce' );

	$actual   = (int) get_post_meta( $post->ID, '_pds_extends_project', true );
	$proyectos = get_posts( [
		'post_type'      => 'proyecto',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	echo '<select name="pds_extends_project" style="width:100%">';
	echo '<option value="0">— ninguno —</option>';
	foreach ( $proyectos as $p ) {
		printf(
			'<option value="%d" %s>%s</option>',
			$p->ID,
			selected( $actual, $p->ID, false ),
			esc_html( wp_trim_words( $p->post_title, 10 ) )
		);
	}
	echo '</select>';
	echo '<p class="description">El proyecto original que este case study desarrolla. Se usa para que el chatbot cite el caso nuevo en lugar del antiguo.</p>';
}

function pds_case_study_save_metabox( $post_id ) {

	if ( ! isset( $_POST['pds_extends_project_nonce'] )
		|| ! wp_verify_nonce( $_POST['pds_extends_project_nonce'], 'pds_extends_project_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$valor = isset( $_POST['pds_extends_project'] ) ? (int) $_POST['pds_extends_project'] : 0;

	if ( $valor > 0 ) {
		update_post_meta( $post_id, '_pds_extends_project', $valor );
	} else {
		delete_post_meta( $post_id, '_pds_extends_project' );
	}
}
add_action( 'save_post_case_study', 'pds_case_study_save_metabox' );

/* ─────────────────────────────────────────────────────────────
   PARTNERS Y SOPORTE
   Migrados desde el plugin Custom Post Type UI (2026-09-19) para
   poder desactivarlo. Código generado por el propio plugin
   (cptui_get_post_type_code), con la función renombrada: el nombre
   original, cptui_register_my_cpts(), ya lo usa el registro de
   `proyecto` más arriba en este mismo fichero.
   ───────────────────────────────────────────────────────────── */

function pds_register_cpts_partners_soporte() {

	/**
	 * Post Type: Partners.
	 */

	$labels = [
		"name" => esc_html__( "Partners", "PDS" ),
		"singular_name" => esc_html__( "Partner", "PDS" ),
		"menu_name" => esc_html__( "Partners", "PDS" ),
	];

	$args = [
		"label" => esc_html__( "Partners", "PDS" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "partners", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail" ],
		"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "partners", $args );

	/**
	 * Post Type: Soportes.
	 */

	$labels = [
		"name" => esc_html__( "Soportes", "PDS" ),
		"singular_name" => esc_html__( "Soporte", "PDS" ),
	];

	$args = [
		"label" => esc_html__( "Soportes", "PDS" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "page",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "soporte", "with_front" => true ],
		"query_var" => true,
		"menu_position" => 20,
		"menu_icon" => "dashicons-hammer",
		"supports" => [ "title", "editor", "thumbnail" ],
		"show_in_graphql" => false,
	];

	register_post_type( "soporte", $args );
}

add_action( 'init', 'pds_register_cpts_partners_soporte' );

/**
 * Traducción de los literales de los formularios de WPForms.
 *
 * WPML no puede traducirlos: los formularios son "paquetes" y su flujo pasa por
 * ATE (servicio de pago). Comprobado el 2026-09-24 que las cadenas quedan
 * registradas y visibles en String Translation, pero no llegan al front.
 *
 * Aquí se sustituyen antes de renderizar, según el idioma activo de WPML. Es
 * texto del formulario, no del plugin, así que no hay .mo que valga.
 *
 * Para añadir un formulario nuevo, añade su ID al array con sus literales.
 */
function pds_wpforms_literales() {
	return [
		14352 => [ // Contact Us (el del footer, en todas las páginas)
			'es' => [
				'submit'      => 'Enviar',
				'processing'  => 'Enviando...',
				'labels'      => [ 0 => 'Nombre de la empresa', 1 => 'Correo electrónico', 5 => 'Teléfono', 6 => 'Política de privacidad' ],
				'placeholders'=> [ 0 => '*Nombre de la empresa', 1 => '*Correo electrónico', 5 => '*Teléfono' ],
				'choices'     => [ 6 => [ 1 => '*Acepto la <a href="%PRIVACY%" target="_blank" rel="nofollow">política de privacidad</a> de Paladini Digital Solutions' ] ],
			],
			'ca' => [
				'submit'      => 'Envia',
				'processing'  => 'Enviant...',
				'labels'      => [ 0 => 'Nom de l’empresa', 1 => 'Correu electrònic', 5 => 'Telèfon', 6 => 'Política de privadesa' ],
				'placeholders'=> [ 0 => '*Nom de l’empresa', 1 => '*Correu electrònic', 5 => '*Telèfon' ],
				'choices'     => [ 6 => [ 1 => '*Accepto la <a href="%PRIVACY%" target="_blank" rel="nofollow">política de privadesa</a> de Paladini Digital Solutions' ] ],
			],
		],
	];
}

function pds_wpforms_traducir( $form_data ) {
	if ( ! function_exists( 'icl_object_id' ) && ! defined( 'ICL_LANGUAGE_CODE' ) ) return $form_data;

	$lang = apply_filters( 'wpml_current_language', null );
	$mapa = pds_wpforms_literales();
	$id   = (int) ( $form_data['id'] ?? 0 );

	if ( ! $lang || ! isset( $mapa[ $id ][ $lang ] ) ) return $form_data;
	$t = $mapa[ $id ][ $lang ];

	// URL de la política de privacidad en el idioma actual
	$privacidad = get_permalink( apply_filters( 'wpml_object_id', 16599, 'page', true, $lang ) );

	if ( ! empty( $t['submit'] ) )     $form_data['settings']['submit_text'] = $t['submit'];
	if ( ! empty( $t['processing'] ) ) $form_data['settings']['submit_text_processing'] = $t['processing'];

	foreach ( (array) ( $t['labels'] ?? [] ) as $campo => $texto ) {
		if ( isset( $form_data['fields'][ $campo ] ) ) $form_data['fields'][ $campo ]['label'] = $texto;
	}
	foreach ( (array) ( $t['placeholders'] ?? [] ) as $campo => $texto ) {
		if ( ! isset( $form_data['fields'][ $campo ] ) ) continue;
		$form_data['fields'][ $campo ]['placeholder'] = $texto;
		// los campos "name" simples usan su propia clave
		if ( isset( $form_data['fields'][ $campo ]['simple_placeholder'] ) ) {
			$form_data['fields'][ $campo ]['simple_placeholder'] = $texto;
		}
	}
	foreach ( (array) ( $t['choices'] ?? [] ) as $campo => $opciones ) {
		foreach ( $opciones as $i => $texto ) {
			if ( isset( $form_data['fields'][ $campo ]['choices'][ $i ] ) ) {
				$form_data['fields'][ $campo ]['choices'][ $i ]['label'] = str_replace( '%PRIVACY%', esc_url( $privacidad ), $texto );
			}
		}
	}
	return $form_data;
}
add_filter( 'wpforms_frontend_form_data', 'pds_wpforms_traducir' );


/**
 * dataLayer para Google Tag Manager
 * ---------------------------------
 * GTM no puede deducir por sí solo el idioma de la página ni qué se está viendo,
 * así que se lo damos aquí. Con esto, cualquier etiqueta o evento que se cree
 * en GTM puede segmentar por idioma sin tocar el tema otra vez.
 *
 * Se imprime en el <head>, antes que el contenedor, porque GTM lee el dataLayer
 * al arrancar: si se imprimiera después, la primera página vista iría sin datos.
 */
function pds_datalayer() {
	$datos = [
		'pagina_idioma' => apply_filters( 'wpml_current_language', null ) ?: substr( get_bloginfo( 'language' ), 0, 2 ),
		'pagina_tipo'   => 'otro',
	];

	if ( is_front_page() ) {
		$datos['pagina_tipo'] = 'home';
	} elseif ( is_singular() ) {
		$tipo = get_post_type();
		// nombres en claro, para no tener que traducir slugs dentro de GTM
		$mapa = [
			'page'      => 'pagina',
			'post'      => 'articulo',
			'service'   => 'servicio',
			'proyecto'  => 'proyecto',
			'partners'  => 'partner',
		];
		$datos['pagina_tipo']   = $mapa[ $tipo ] ?? $tipo;
		$datos['contenido_id']  = get_the_ID();
		// el título del original en inglés: así un mismo proyecto se agrupa en los tres idiomas
		$original = apply_filters( 'wpml_object_id', get_the_ID(), $tipo, true, 'en' );
		$datos['contenido']     = get_the_title( $original );
	} elseif ( is_post_type_archive() || is_home() ) {
		$datos['pagina_tipo'] = 'listado';
	}

	echo "<script>window.dataLayer = window.dataLayer || []; window.dataLayer.push(" . wp_json_encode( $datos ) . ");</script>\n";
}
add_action( 'wp_head', 'pds_datalayer', 1 );
