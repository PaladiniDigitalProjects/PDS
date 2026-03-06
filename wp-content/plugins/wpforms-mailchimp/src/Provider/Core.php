<?php

namespace WPFormsMailchimp\Provider;

/**
 * Class Core registers all the handlers for
 * Form Builder, Settings > Integrations page, Processing, etc.
 *
 * @since 2.0.0
 */
class Core extends \WPForms\Providers\Provider\Core {

	/**
	 * Priority for a provider, that will affect loading/placement order.
	 *
	 * @since 2.0.0
	 */
	const PRIORITY = 34;

	/**
	 * Core constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'slug' => 'mailchimpv3',
				'name' => esc_html__( 'Mailchimp', 'wpforms-mailchimp' ),
				'icon' => WPFORMS_MAILCHIMP_URL . 'assets/images/addon-icon-mailchimp.png',
			]
		);
	}

	/**
	 * Provide an instance of the object, that should process the submitted entry.
	 * It will use data from an already saved entry to pass it further to a Provider.
	 *
	 * @since 2.0.0
	 *
	 * @return null|\WPFormsMailchimp\Provider\Process
	 */
	public function get_process() {

		static $process = null;

		if ( ! ( $process instanceof Process ) ) {
			$process = new Process( static::get_instance() );
		}

		return $process;
	}

	/**
	 * Provide an instance of the object, that should display provider settings
	 * on Settings > Integrations page in admin area.
	 *
	 * @since 2.0.0
	 *
	 * @return null|\WPFormsMailchimp\Provider\Settings\PageIntegrations
	 */
	public function get_page_integrations() {

		static $integration = null;

		if (
			! ( $integration instanceof Settings\PageIntegrations ) &&
			( wpforms_is_admin_page( 'settings', 'integrations' ) || wp_doing_ajax() )
		) {
			$integration = new Settings\PageIntegrations( static::get_instance() );
		}

		return $integration;
	}

	/**
	 * Provide an instance of the object, that should display provider settings in the Form Builder.
	 *
	 * @since 2.0.0
	 *
	 * @return null|\WPFormsMailchimp\Provider\Settings\FormBuilder
	 */
	public function get_form_builder() {

		static $builder = null;

		if (
			! ( $builder instanceof Settings\FormBuilder ) &&
			( wpforms_is_admin_page( 'builder' ) || wp_doing_ajax() )
		) {
			$builder = new Settings\FormBuilder( static::get_instance() );
		}

		return $builder;
	}

	/**
	 * Get provider options.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_provider_options() {

		$providers = wpforms_get_providers_options();

		return ! empty( $providers[ $this->slug ] ) ? $providers[ $this->slug ] : [];
	}
}
