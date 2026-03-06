<?php

namespace WPFormsMailchimp\Provider;

/**
 * Class Helpers.
 *
 * @since 2.4.0
 */
class Helpers {

	/**
	 * Retrieve an error message for invalid API credentials.
	 *
	 * @since 2.4.0
	 *
	 * @return string
	 */
	public static function get_api_credentials_error_message(): string {

		return esc_html__( 'Invalid Mailchimp API credentials. Please check your information and try again.', 'wpforms-mailchimp' );
	}
}
