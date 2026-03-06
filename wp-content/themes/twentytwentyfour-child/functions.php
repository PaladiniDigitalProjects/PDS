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

	wp_enqueue_style( 'child-estils', get_template_directory_uri() . '-child/assets/css/estils.css',);
	wp_enqueue_script('main',  get_template_directory_uri() . '-child/assets/js/main.js', array(), '1.0.0', true);
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

function cptui_register_my_cpts() {

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

add_action( 'init', 'cptui_register_my_cpts' );


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