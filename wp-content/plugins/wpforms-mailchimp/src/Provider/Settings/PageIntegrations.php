<?php

namespace WPFormsMailchimp\Provider\Settings;

use WPFormsMailchimp\Provider\Api;
use WPForms\Providers\Provider\Settings\PageIntegrations as PageIntegrationsAbstract;
use WPFormsMailchimp\Provider\Helpers;

/**
 * Class PageIntegrations handles functionality inside the Settings > Integrations page.
 *
 * @since 2.0.0
 */
class PageIntegrations extends PageIntegrationsAbstract {

	/**
	 * Get the Core loader class of a provider.
	 *
	 * @since 2.0.0
	 *
	 * @var \WPFormsMailchimp\Provider\Core
	 */
	protected $core;

	/**
	 * AJAX to add a provider from the Settings > Integrations tab.
	 *
	 * @since 2.0.0
	 */
	public function ajax_connect() {

		parent::ajax_connect();

		$creds = wp_parse_args( wp_unslash( $_POST['data'] ), [ 'apikey' => '' ] ); // phpcs:ignore WordPress.Security

		if ( empty( $creds['apikey'] ) ) {
			wp_send_json_error( [ 'error_msg' => esc_html__( 'Please provide a valid API Key.', 'wpforms-mailchimp' ) ] );
		}

		$api_key = trim( $creds['apikey'] );

		if ( $this->is_already_exist( $api_key ) ) {
			wp_send_json_error( [ 'error_msg' => esc_html__( 'Account with those credentials has been already added.', 'wpforms-mailchimp' ) ] );
		}

		// API call.
		try {
			$api = new Api( $api_key );
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'error_msg' => Helpers::get_api_credentials_error_message() ] );
		}

		// Retrieve a response data.
		$response = $api->get_account();

		if ( empty( $response['account_id'] ) ) {
			$details = ! empty( $response['detail'] ) ? $response['detail'] : '';

			/* translators: %s - error details. */
			$error_msg = sprintf( esc_html__( 'Mailchimp API error: %s', 'wpforms-mailchimp' ), $details );

			// If status code is 401, it means that credentials are invalid or details are missing.
			// See https://mailchimp.com/developer/release-notes/added-support-for-401-unauthorized-response-code/.
			if ( empty( $details ) || $response['status'] === 401 ) {
				$error_msg = Helpers::get_api_credentials_error_message();
			}

			wp_send_json_error( [ 'error_msg' => $error_msg ] );
		}

		// Success.
		wp_send_json_success( [ 'html' => $this->prepare_result_html_list( $creds, $response ) ] );
	}

	/**
	 * Check if account with those credentials already exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $api_key API key for check.
	 *
	 * @return bool True if account already exists, false otherwise.
	 */
	protected function is_already_exist( $api_key ) {

		$keys = array_column( $this->core->get_provider_options(), 'api' );

		return in_array( $api_key, $keys, true );
	}

	/**
	 * Prepare HTML for a new account.
	 *
	 * @since 2.0.0
	 *
	 * @param array $creds   Array with user credentials.
	 * @param array $account Account data.
	 *
	 * @return string
	 */
	protected function prepare_result_html_list( $creds, $account ) {

		$option_key = sanitize_key( $account['account_id'] );
		$label      = ! empty( $creds['label'] ) ? sanitize_text_field( $creds['label'] ) : sanitize_email( $account['email'] );
		$date       = time();

		// Save this account.
		wpforms_update_providers_options(
			$this->core->slug,
			[
				'api'   => $creds['apikey'],
				'label' => $label,
				'date'  => $date,
			],
			$option_key
		);

		return wpforms_mailchimp()->get( 'template' )->get_settings_template(
			'connected-account',
			[
				'key'           => $option_key,
				'label'         => $label,
				'date'          => $date,
				'provider_slug' => $this->core->slug,
			]
		);
	}

	/**
	 * Display fields that will store Mailchimp account details.
	 *
	 * @since 2.0.0
	 */
	protected function display_add_new_connection_fields() {

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_mailchimp()->get( 'template' )->get_settings_template(
			'new-account-connection',
			[ 'provider_name' => $this->core->name ]
		);
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
