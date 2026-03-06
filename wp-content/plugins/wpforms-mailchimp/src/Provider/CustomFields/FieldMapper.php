<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Class for initialization an instance based on Mailchimp field type.
 *
 * @since 2.0.0
 */
class FieldMapper {

	/**
	 * Initialize an instance based on Mailchimp field type.
	 *
	 * @since 2.0.0
	 *
	 * @param array $mc_field_props  Mailchimp merge field properties.
	 * @param array $wpf_field_props WPForms field properties.
	 * @param array $wpf_field       WPForms field.
	 *
	 * @return object
	 */
	public function init( $mc_field_props, $wpf_field_props, $wpf_field ) {

		// Getting the class name.
		$mc_field_type = $this->get_mc_field_type( $mc_field_props );
		$class_name    = ucfirst( $mc_field_type );
		$class_name    = '\WPFormsMailchimp\Provider\CustomFields\\' . $class_name;

		if ( ! class_exists( $class_name ) ) {
			$class_name = '\WPFormsMailchimp\Provider\CustomFields\Text';
		}

		return new $class_name( $mc_field_props, $wpf_field_props, $wpf_field );
	}

	/**
	 * Retrieve a Mailchimp field type.
	 *
	 * @since 2.0.0
	 *
	 * @param array $field_props Mailchimp field properties.
	 *
	 * @return string
	 */
	private function get_mc_field_type( $field_props = [] ) {

		// This mapping helps to avoid a DRY for some fields.
		$types = [
			'dropdown' => 'choice',
			'radio'    => 'choice',
			'imageurl' => 'url',
		];

		if ( empty( $field_props['type'] ) ) {
			return 'text';
		}

		return isset( $types[ $field_props['type'] ] ) ? $types[ $field_props['type'] ] : $field_props['type'];
	}
}
