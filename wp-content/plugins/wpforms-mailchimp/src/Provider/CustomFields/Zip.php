<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Zip field.
 *
 * @since 2.0.0
 */
class Zip extends Base {

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

		// Apply a special formatting for the `Address` WPForms field - try to grab a postal code.
		if ( $this->get_wpf_field_type() === 'address' ) {
			return ! empty( $this->wpf_field['postal'] ) ? $this->wpf_field['postal'] : null;
		}

		$value = $this->wpf_field[ $key ];

		// Validation failed, provided zip/postal code is not valid.
		if ( empty( $value ) || ! preg_match( '/^\d{5}([\-]\d{4})?$/i', $value ) ) {
			return null;
		}

		return $value;
	}
}
