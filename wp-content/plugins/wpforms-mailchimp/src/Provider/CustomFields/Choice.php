<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Choice field.
 *
 * @since 2.0.0
 */
class Choice extends Base {

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

		// We need to check if a result value is available in field options.
		if (
			empty( $this->mc_field_props['options']['choices'] ) ||
			! is_array( $this->mc_field_props['options']['choices'] ) ||
			! in_array( $value, $this->mc_field_props['options']['choices'], true )
		) {
			$value = null;
		}

		return $value;
	}
}
