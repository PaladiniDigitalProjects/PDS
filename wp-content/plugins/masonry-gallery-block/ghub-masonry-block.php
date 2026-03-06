<?php

/**
 * Plugin Name:       Masonry Gallery Block by GutenbergHub
 * Description:       Give your galleries a fresh, modern look with the Masonry Gallery Block.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.1.0
 * Author:            GutenbergHub
 * Author URI:  	  https://shop.gutenberghub.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ghub-masonry-ext
 *
 */
if (!defined('GHUB_MASONRY_BLOCK_EXT_PATH')) {
	define('GHUB_MASONRY_BLOCK_EXT_PATH', plugin_dir_path(__FILE__));
}

define('GHUB_MASONRY_BLOCK_EXT', plugins_url('/', __FILE__));

require_once GHUB_MASONRY_BLOCK_EXT_PATH . "gutenberghub-sdk/loader.php";

add_action('init', function () {
	wp_register_script(
		"ghub-masonry-script",
		GHUB_MASONRY_BLOCK_EXT . '/build/index.js',
		array(
			"lodash",
			"wp-element",
			"wp-compose",
			"wp-hooks",
			"wp-block-editor",
			"wp-i18n",
			
		),
		'initial'
	);
	wp_register_style(
		'ghub-masonry-editor-style',
		GHUB_MASONRY_BLOCK_EXT . '/build/index.css',
		array(),
		'initial'
	);

	wp_register_style(
		'ghub-masonry-frontend-style',
		GHUB_MASONRY_BLOCK_EXT . '/build/style-index.css',
		array(),
		'initial'
	);
});

add_action('enqueue_block_assets', function () {
	wp_enqueue_script('ghub-masonry-script');
	wp_enqueue_style('ghub-masonry-editor-style');
});
function masonry_gallery_custom_block_args($args, $block_type) {
	// Check if it's the specific block type you want to modify
	if ('core/gallery' === $block_type) {
		$current_provided_style = isset($args['style_handles']) ? $args['style_handles'] : array();
		$args['style_handles'] = array_merge($current_provided_style, array('ghub-masonry-frontend-style'));
	}

	return $args;
}
add_filter('register_block_type_args', 'masonry_gallery_custom_block_args', 10, 2);

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

add_filter('render_block', function ($block_content, $block) {
	if (
		'core/gallery' === $block['blockName'] &&
		array_key_exists('ghubVariation', $block['attrs']) &&
		"masonry-gallery" ===  $block['attrs']['ghubVariation']
	) {
		$block_content = str_replace('wp-block-gallery', 'wp-block-gallery is-style-masonry', $block_content);
		$style = "";
		if (array_key_exists("ghubHorizontalGap", $block['attrs'])) {
			$style .= '--ghub--gallery-column-gap: ' . $block['attrs']['ghubHorizontalGap'] . ';';
		}
		if (array_key_exists("ghubVerticalGap", $block['attrs'])) {
			$style .= '--ghub--gallery-row-gap:' . $block['attrs']['ghubVerticalGap'] . ';';
		}

		$block_elements  = explode('<figure', $block_content);


		if (strpos($block_elements[1], 'style=') !== false) {
			$block_elements[1] = preg_replace('/style="(.*?)"/', 'style="$1; ' . $style . '"', $block_elements[1]);
		} else {
			$block_elements[1] = ' style="' . $style . '"' . $block_elements[1];
		}
		$block_content = implode('<figure', $block_elements);
	}
	return $block_content;
}, 10, 2);
