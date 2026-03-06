<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Base field template.
 *
 * @since 2.0.0
 */
abstract class Base {

	/**
	 * Mailchimp merge field properties.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $mc_field_props = [];

	/**
	 * WPForms field properties.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $wpf_field_props = [];

	/**
	 * WPForms field.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $wpf_field = [];

	/**
	 * Base constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param array $mc_field_props  Mailchimp merge field properties.
	 * @param array $wpf_field_props WPForms field properties.
	 * @param array $wpf_field       WPForms field.
	 */
	public function __construct( $mc_field_props, $wpf_field_props, $wpf_field ) {

		$this->mc_field_props  = $mc_field_props;
		$this->wpf_field_props = $wpf_field_props;
		$this->wpf_field       = $wpf_field;
	}

	/**
	 * Retrieve a field value to deliver to Mailchimp. Used by subclasses.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Source key.
	 *
	 * @return mixed
	 */
	abstract public function get_value( $key );

	/**
	 * Retrieve a WPForms field type.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_wpf_field_type() {

		return ! empty( $this->wpf_field_props['type'] ) ? $this->wpf_field_props['type'] : 'text';
	}

	/**
	 * Determine if it a WPForms payment field.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function is_wpf_payment_field() {

		$payment_fields   = (array) wpforms_payment_fields();
		$payment_fields[] = 'payment-total';

		return in_array( $this->get_wpf_field_type(), $payment_fields, true );
	}
}
