<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Phone field.
 *
 * @since 2.0.0
 */
class Phone extends Base {

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

		$value = $this->wpf_field[ $key ];

		// Apply a special formatting for the `Phone` WPForms field.
		if (
			! empty( $value ) &&
			$this->get_wpf_field_type() === 'phone'
		) {
			return $this->format_phone( $value );
		}

		return null;
	}

	/**
	 * Special formatting for the `Phone` WPForms field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	protected function format_phone( $value ) {

		$is_mc_phone_us  = ! empty( $this->mc_field_props['options']['phone_format'] ) && strtolower( $this->mc_field_props['options']['phone_format'] ) === 'us';
		$is_wpf_phone_us = ! empty( $this->wpf_field_props['format'] ) && strtolower( $this->wpf_field_props['format'] ) === 'us';

		if ( $is_mc_phone_us && ! $is_wpf_phone_us ) {
			return null;
		}

		// We need to prepare the US phone value, without special formatting, which we applied before saving to DB.
		if ( $is_wpf_phone_us ) {
			$value = str_replace( [ ' ', '(', ')' ], [ '-', '', '' ], $value );
		}

		return $value;
	}
}
