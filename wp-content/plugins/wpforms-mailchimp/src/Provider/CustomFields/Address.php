<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Address field.
 *
 * @since 2.0.0
 */
class Address extends Base {

	/**
	 * Retrieve a field value to deliver to Mailchimp.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Source key.
	 *
	 * @return array|null
	 */
	public function get_value( $key ) {

		// Apply a special formatting for `Address` WPForms field.
		if ( $this->get_wpf_field_type() === 'address' ) {
			return $this->format_address();
		}

		return null;
	}

	/**
	 * Special formatting for `Address` WPForms field.
	 *
	 * @since 2.0.0
	 *
	 * @return array|null
	 */
	protected function format_address() {

		$result = [];

		foreach ( $this->get_mapped_value_keys() as $wpf_key => $mc_key ) {

			if ( $wpf_key !== 'state' && empty( $this->wpf_field[ $wpf_key ] ) ) {
				continue;
			}

			$result[ $mc_key ] = $this->wpf_field[ $wpf_key ];
		}

		return empty( array_diff_key( array_flip( $this->get_required_keys() ), $result ) ) ? $result : null;
	}

	/**
	 * Retrieve mapped keys.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_mapped_value_keys() {

		return [
			// WPForms => Mailchimp.
			'address1' => 'addr1',
			'address2' => 'addr2',
			'city'     => 'city',
			'state'    => 'state',
			'postal'   => 'zip',
			'country'  => 'country',
		];
	}

	/**
	 * Retrieve required keys for a Mailchimp address.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_required_keys() {

		return [
			'addr1',
			'city',
			'state',
			'zip',
		];
	}
}
