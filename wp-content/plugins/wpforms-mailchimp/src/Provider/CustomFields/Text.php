<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Text field.
 *
 * @since 2.0.0
 */
class Text extends Base {

	/**
	 * Retrieve a field value to deliver to Mailchimp.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Source key.
	 *
	 * @return string|null
	 */
	public function get_value( $key ) {

		$value = $this->is_wpf_payment_field() ? wpforms_decode_string( $this->wpf_field[ $key ] ) : $this->wpf_field[ $key ];

		return str_replace( [ '\n', '\r', PHP_EOL ], ' ', $value );
	}
}
