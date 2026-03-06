<?php

/**
 * Plugin Name:       Query Load More Block by GutenbergHub
 * Description:	  Load More Posts block in WordPress Gutenberg adds dynamic, engaging user experience by displaying a set number of posts initially and then allowing users to load more with a single click.
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version:           1.0.3
 * Author:            GutenbergHub
 * Author URI:		  https://shop.gutenberghub.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       query-load-more
 *
 * @package           gutenberghub-query
 */

define('GUTENBERGHUB_LOAD_MORE', plugins_url('/', __FILE__));

if (!defined('GUTENBERGHUB_LOAD_MORE_PATH')) {
	define('GUTENBERGHUB_LOAD_MORE_PATH', plugin_dir_path(__FILE__));
}
require_once GUTENBERGHUB_LOAD_MORE_PATH . 'gutenberghub-sdk/loader.php';

/**
 * Prepares all blocks to consume our custom query id.
 *
 * @param array  $args - Registeration arguments.
 * @param string $block_name - Block type name.
 *
 * @return array - Modified arguments to consume custom query context id.
 */
function prepare_query_id_consumption_load_more($args, $block_name) {
	if ('core/query' === $block_name) {
		$args['attributes'] = array_merge(
			$args['attributes'],
			array(
				'ghubQueryId' => array(
					'type' => 'string',
					'default' => ''
				)
			)
		);
		$args['provides_context'] = array_merge($args['provides_context'], array(
			'query'			=> "query",
			'queryId'			=> "queryId",
			'ghubQueryId'		=> 'ghubQueryId'
		));
	} else {
		$current_use_context  = isset($args['uses_context']) ? $args['uses_context'] : array();
		$args['uses_context'] = array_merge($current_use_context, array(
			'query',
			'queryId',
			'ghubQueryId'
		));
	}

	return $args;
}
add_filter('register_block_type_args',  'prepare_query_id_consumption_load_more', 10, 2);


/**
 * @param string 	$block_content - Block Content.
 * @param array 	$block - Parsed Block.
 * @param WP_Block 	$block_instance - Parsed Block instance.
 * 
 * @return string - Rendered query block.
 */
add_filter("render_block", function ($block_content, $block, $block_instance) {

	if ('ghub/query-load-more' !== $block_instance->name) {
		if ('core/post-template' === $block_instance->name) {
			$query_id = $block_instance->context['ghubQueryId'];
			$block_content = str_replace('<ul', '<ul id="' . $query_id . '"', $block_content);
			return $block_content;
		}
		return $block_content;
	}

	/**
	 * Dynamic Query
	 */
	// Use global query if needed.
	$block_query = $block_instance->context['query'];
	$use_global_query = (isset($block_query['inherit']) && $block_query['inherit']);

	/**
	 * @var WP_Query - Query.
	 */
	$query = null;
	$page_key = isset($block_instance->context['queryId']) ? 'query-' . $block_instance->context['queryId'] . '-page' : 'query-page';
	$page     = empty($_GET[$page_key]) ? 1 : (int) $_GET[$page_key];
	$per_page = (int) $block_query['perPage'];

	if ($use_global_query) {
		global $wp_query;
		$query = clone $wp_query;
	} else {
		$query_args = build_query_vars_from_query_block($block_instance, $page);
		$query_args['paged'] = $page;
		$query_args['posts_per_page'] = $per_page;

		$query      = new WP_Query($query_args);
	}
	$total_pages = ceil($query->found_posts / $per_page);

	return sprintf(
		str_replace('<div class=', '<div data-loadingtext="%3$s" data-autotrigger="%4$s" data-pagekey="%1$s"  data-totalpages="%2$s" class=', $block_content),
		$page_key, //1
		$total_pages, //2
		array_key_exists('loadingText', $block['attrs']) ? $block['attrs']['loadingText'] : "loading...",
		isset($block['attrs']['autoTrigger'])  && true ===  $block['attrs']['autoTrigger'] ? "true" : "false",
	);
}, 10, 3);



function gutenberghub_query_query_load_more_block_init() {
	wp_register_script(
		"gutenberghub-query-load-more-plugin-frontend-script",
		GUTENBERGHUB_LOAD_MORE . 'scripts/script.js',
		array('jquery'),
		uniqid()
	);
	register_block_type(__DIR__ . '/build');
}

add_filter('block_categories_all', function ($categories) {
	// Check if "GutenbergHub" category already exists
	foreach ($categories as $category) {
		if ($category['slug'] === 'ghub-products') {
			// "GutenbergHub" category already exists, do not add again
			return $categories;
		}
	}

	// Adding "GutenbergHub" category.
	$categories[] = array(
		'slug'  => 'ghub-products',
		'title' => 'GutenbergHub'
	);

	return $categories;
});

add_action('init', function () {
	register_block_type_from_metadata(
		GUTENBERGHUB_LOAD_MORE . 'dist/block.json',
	);
});
add_action('init', 'gutenberghub_query_query_load_more_block_init');
