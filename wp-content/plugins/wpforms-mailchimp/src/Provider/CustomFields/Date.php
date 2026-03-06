<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Date field.
 *
 * @since 2.0.0
 */
class Date extends Base {

	/**
	 * Supported date format.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $date_format = 'm/d/Y';

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

		// Apply a special formatting for `Date / Time` WPForms field.
		if ( $this->get_wpf_field_type() === 'date-time' ) {
			return $this->format_datetime( $value );
		}

		// Also the date value can be pass from any other WPForms field (e.g. Single Line Text).
		// We try to convert this string to date value.
		$date_time = date_create( $value, wp_timezone() );

		if ( $date_time ) {
			return $date_time->format( $this->date_format );
		}

		return null;
	}

	/**
	 * Special formatting for `Date / Time` WPForms field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Field value.
	 *
	 * @return string|null
	 */
	protected function format_datetime( $value ) {

		$date_format = $this->get_wpf_date_format_option();
		$date_time   = false;
		$result      = null;

		// Try to parse a value with a date string.
		if ( ! empty( $date_format ) ) {
			$date_time = date_create_from_format( $date_format, $value, wp_timezone() );
		}

		// Fallback to a timestamp value.
		if ( ! $date_time && ! empty( $this->wpf_field['unix'] ) ) {
			$date_time = date_create( '@' . $this->wpf_field['unix'], wp_timezone() );
		}

		// If we have a DateTime object - return a date formatted according to expected format.
		if ( $date_time ) {
			$result = $date_time->format( $this->date_format );
		}

		return ! empty( $result ) ? $result : null;
	}

	/**
	 * Retrieve a date format option for the WPForms field.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_wpf_date_format_option() {

		$format = '';

		// Skip for `time` format.
		if ( $this->wpf_field_props['format'] === 'time' ) {
			return $format;
		}

		if ( ! empty( $this->wpf_field_props['date_format'] ) ) {
			$format = $this->wpf_field_props['date_format'];
		}

		if (
			$this->wpf_field_props['format'] === 'date-time' &&
			! empty( $this->wpf_field_props['time_format'] ) &&
			! empty( $this->wpf_field['time'] )
		) {
			$format .= ' ' . $this->wpf_field_props['time_format'];
		}

		return trim( $format );
	}
}
