<?php

namespace ProDevign\BlockMeister\Admin;

class Admin_Menu {

	/**
	 * Menu constructor.
	 */
	public function __construct() {
	}

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
	}

	/**
	 * Register our menu page
	 * @private
	 * @return void
	 */
	public function add_admin_menu() {
		$menu_slug  = 'blockmeister';
		$capability = 'publish_blockmeister_patterns';

		$hook = add_menu_page(
			null,
			'BlockMeister',
			$capability,
			$menu_slug,
			[ $this, 'render_page' ],
			'dashicons-welcome-learn-more' // TODO: create and set custom icon (based on graduate hat from BM logo)
		);

		//add_action( 'load-' . $hook, [ $this, 'init_hooks' ] );
	}

	public function render_page() {
		echo "<h1>BlockMeister</h1>";
	}

}
