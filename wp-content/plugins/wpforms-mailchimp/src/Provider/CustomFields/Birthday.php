<?php

namespace WPFormsMailchimp\Provider\CustomFields;

/**
 * Birthday field.
 *
 * @since 2.0.0
 */
class Birthday extends Date {

	/**
	 * Supported date format.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $date_format = 'm/d';
}
