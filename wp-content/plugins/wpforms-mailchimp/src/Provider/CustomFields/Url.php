<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Url field.
 *
 * @since 2.0.0
 */
class Url extends Base {

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

		// `wpforms_is_url()` function sets a local image URL as valid,
		// but the Mailchimp API service might throw the error like a "That is not an existing URL".
		return wpforms_is_url( $this->wpf_field[ $key ] ) ? $this->wpf_field[ $key ] : null;
	}
}
