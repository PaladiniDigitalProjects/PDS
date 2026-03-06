<?php

namespace WPFormsMailchimp\Provider;

/**
 * Class Template.
 *
 * @since 2.0.0
 */
class Template {

	/**
	 * Init hooks.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		add_filter( 'wpforms_helpers_templates_include_html_located', [ $this, 'register' ], 10, 2 );
	}

	/**
	 * Register addon location.
	 *
	 * @since 2.0.0
	 *
	 * @param string $located  Template location.
	 * @param string $template Template.
	 *
	 * @return string
	 */
	public function register( $located, $template ) {

		// Checking if `$template` is an absolute path and passed from this plugin.
		if (
			strpos( $template, WPFORMS_MAILCHIMP_DIR ) === 0 &&
			is_readable( $template )
		) {
			return $template;
		}

		return $located;
	}

	/**
	 * Get a template for the Settings page.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Template name.
	 * @param array  $args List of arguments.
	 *
	 * @return string
	 */
	public function get_settings_template( $name, $args = [] ) {

		return wpforms_render(
			WPFORMS_MAILCHIMP_DIR . "templates/settings/{$name}",
			$args,
			! empty( $args )
		);
	}

	/**
	 * Get a template for the Builder.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Template name.
	 * @param array  $args List of arguments.
	 *
	 * @return string
	 */
	public function get_builder_template( $name, $args = [] ) {

		return wpforms_render(
			WPFORMS_MAILCHIMP_DIR . "templates/builder/{$name}",
			$args,
			! empty( $args )
		);
	}
}
