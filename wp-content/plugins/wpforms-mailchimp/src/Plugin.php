<?php

namespace WPFormsMailchimp;

use stdClass;
use WPForms\Providers\Providers;
use WPForms_Updater;
use WPFormsMailchimp\Provider\Template;

/**
 * Class Plugin that loads the whole plugin.
 *
 * @since 2.0.0
 */
final class Plugin {

	/**
	 * Provider slug.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $provider_slug;

	/**
	 * Get some addon templates.
	 *
	 * @since 2.0.0
	 *
	 * @var \WPFormsMailchimp\Provider\Template
	 */
	private $template;

	/**
	 * Plugin constructor.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {}

	/**
	 * Get a single instance of the addon.
	 *
	 * @since 2.0.0
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
	 * Get property.
	 *
	 * @since 2.0.0
	 *
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 */
	public function get( $property_name ) {

		return property_exists( $this, $property_name ) ? $this->{$property_name} : new stdClass();
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		$this->hooks();

		return $this;
	}

	/**
	 * Hooks.
	 *
	 * @since 2.0.0
	 */
	protected function hooks() {

		add_action( 'wpforms_loaded', [ $this, 'init_components' ], 20 );
		add_action( 'admin_notices', [ $this, 'upgrade_notice' ] );
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.0.0
	 * @deprecated 2.4.0
	 *
	 * @todo Remove with core 1.9.2
	 *
	 * @param string $key License key.
	 */
	public function updater( $key ) {

		_deprecated_function( __METHOD__, '2.4.0 of the WPForms Mailchimp plugin' );

		new WPForms_Updater(
			[
				'plugin_name' => 'WPForms Mailchimp',
				'plugin_slug' => 'wpforms-mailchimp',
				'plugin_path' => plugin_basename( __FILE__ ),
				'plugin_url'  => trailingslashit( WPFORMS_MAILCHIMP_URL ),
				'remote_url'  => WPFORMS_UPDATER_API,
				'version'     => WPFORMS_MAILCHIMP_VERSION,
				'key'         => $key,
			]
		);
	}

	/**
	 * Init components.
	 *
	 * @since 2.0.0
	 */
	public function init_components() {

		$provider            = Provider\Core::get_instance();
		$this->provider_slug = $provider->slug;
		$this->template      = new Template();

		$this->template->init();
		Providers::get_instance()->register( $provider );
	}

	/**
	 * Display upgrade notice for sites using the v2 API integration.
	 *
	 * @since 2.0.0
	 */
	public function upgrade_notice() {

		// Only consider showing to admin users.
		if ( ! wpforms_current_user_can() ) {
			return;
		}

		$providers = wpforms_get_providers_options();

		// Only display if site has a v2 integration configured.
		if ( empty( $providers['mailchimp'] ) ) {
			return;
		}

		?>
		<div class="notice notice-warning wpforms-mailchimp-update-notice">
			<p>
				<?php esc_html_e( 'Your forms are currently using an outdated Mailchimp integration that is no longer supported. Please update your forms to use the new integration to avoid losing subscribers.', 'wpforms-mailchimp' ); ?>
				<strong>
					<a href="https://wpforms.com/new-announcing-an-important-mailchimp-addon-update/#update" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Click here for more details.', 'wpforms-mailchimp' ); ?>
					</a>
				</strong>
			</p>
		</div>
		<?php
	}

	/**
	 * Flush a transient cache on provider data change.
	 *
	 * @since 2.0.0
	 *
	 * @depecated 2.4.0
	 */
	public function flush_cache() {

		_deprecated_function( __METHOD__, '2.4.0 of the WPForms Mailchimp addon.' );

		// Call it when an account was added or disconnected.
		if (
			did_action( "wp_ajax_wpforms_settings_provider_add_{$this->provider_slug}" ) ||
			did_action( "wp_ajax_wpforms_settings_provider_disconnect_{$this->provider_slug}" )
		) {
			delete_transient( "wpforms_providers_{$this->provider_slug}_ajax_accounts_get" );
		}
	}
}
