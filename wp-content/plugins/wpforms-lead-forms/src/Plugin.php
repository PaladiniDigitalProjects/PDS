<?php

namespace WPFormsLeadForms;

use WPForms_Updater;

/**
 * WPForms Lead Forms main class.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Provider slug.
	 *
	 * @since 1.0.0
	 */
	const SLUG = 'lead-forms';

	/**
	 * Plugin constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Initialize plugin.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Returns Plugin instance.
	 *
	 * @return Plugin
	 */
	private function init() {

		$this->hooks();
		$this->load_dependencies();

		return $this;
	}

	/**
	 * Plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_filter( 'wpforms_helpers_templates_include_html_located', [ $this, 'register_templates' ], 10, 2 );
	}

	/**
	 * Get a single instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function get_instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();

			$instance->init();
		}

		return $instance;
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {

		if ( wpforms_is_admin_page( 'builder' ) || wp_doing_ajax() ) {
			( new Builder() )->hooks();
		}

		( new Frontend() )->hooks();
		( new Integrations() )->hooks();

		if ( ! is_admin() || wp_doing_ajax() ) {
			( new ThemesCompatibility() )->hooks();
		}
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.0.0
	 * @deprecated 1.6.0
	 *
	 * @todo Remove with core 1.9.2
	 *
	 * @param string $key License key.
	 */
	public function updater( $key ) {

		_deprecated_function( __METHOD__, '1.6.0 of the WPForms Lead Forms plugin' );

		new WPForms_Updater(
			[
				'plugin_name' => 'WPForms Lead Forms',
				'plugin_slug' => 'wpforms-lead-forms',
				'plugin_path' => plugin_basename( WPFORMS_LEAD_FORMS_FILE ),
				'plugin_url'  => trailingslashit( WPFORMS_LEAD_FORMS_URL ),
				'remote_url'  => WPFORMS_UPDATER_API,
				'version'     => WPFORMS_LEAD_FORMS_VERSION,
				'key'         => $key,
			]
		);
	}

	/**
	 * Register addon templates.
	 *
	 * @since 1.0.0
	 *
	 * @param string $located  Template location.
	 * @param string $template Template.
	 *
	 * @return string
	 */
	public function register_templates( $located, $template ) {

		// Checking if `$template` is an absolute path and passed from this plugin.
		if (
			strpos( $template, WPFORMS_LEAD_FORMS_PATH ) === 0 &&
			is_readable( $template )
		) {
			return $template;
		}

		return $located;
	}
}
