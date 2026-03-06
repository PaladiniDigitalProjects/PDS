<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Number field.
 *
 * @since 2.0.0
 */
class Number extends Base {

	/**
	 * Retrieve a field value to deliver to Mailchimp.
	 *
	 * @param string $key Source key.
	 *
	 * @since 2.0.0
	 *
	 * @return int|null
	 */
	public function get_value( $key ) {

		$value = $this->wpf_field[ $key ];

		// Adjustment for payment fields.
		if (
			$this->is_wpf_payment_field() &&
			isset( $this->wpf_field['amount_raw'] ) &&
			! wpforms_is_empty_string( $this->wpf_field['amount_raw'] )
		) {
			$value = $this->wpf_field['amount_raw'];
		}

		return is_numeric( $value ) ? (float) $value : null;
	}
}
