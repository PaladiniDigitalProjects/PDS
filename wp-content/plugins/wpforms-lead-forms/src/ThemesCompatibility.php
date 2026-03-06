<?php

namespace WPFormsLeadForms;

/**
 * WPForms Lead Forms themes compatibility class.
 *
 * @since 1.0.0
 */
class ThemesCompatibility {

	/**
	 * Add hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'wpforms_lead_forms_frontend_enqueue_styles', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data.
	 */
	public function enqueue_styles( $form_data ) {

		$template_name = wp_get_theme()->get_template();

		// Some theme names contain dashes, but dashes aren't supported in method names.
		$template_name = str_replace( '-', '_', $template_name );

		// Some themes are in sentence case, which violates out coding standards.
		$template_name = strtolower( $template_name );

		if ( ! method_exists( __CLASS__, $template_name ) ) {
			return;
		}

		// Call a method named with a theme/template name.
		wp_add_inline_style( 'wpforms-lead-forms', $this->$template_name() );
	}

	/**
	 * Provide compatibility with Twenty Twenty-Three theme.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection CssUnresolvedCustomProperty
	 * @noinspection CssUnusedSymbol
	 */
	private function twentytwentythree() {

		return /* @lang CSS */ '
			body .is-layout-constrained > .wpforms-lead-forms-container:where(:not(.alignleft):not(.alignright):not(.alignfull)) {
				max-width: 700px;
			}
			
			.wp-block-post-content .wpforms-lead-forms-container a:where(:not(.wp-element-button)) {
				color: rgba(var(--wpforms-lead-forms-secondary-text-color), 1);
			}';
	}

	/**
	 * Provide compatibility with Twenty Twenty-Two theme.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection CssUnusedSymbol
	 */
	private function twentytwentytwo() {

		return /* @lang CSS */ '
			body .is-layout-constrained > .wpforms-lead-forms-container:where(:not(.alignleft):not(.alignright):not(.alignfull)) {
				max-width: 700px;
			}';
	}

	/**
	 * Provide compatibility with Twenty Twenty-One theme.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection CssUnresolvedCustomProperty
	 * @noinspection CssUnusedSymbol
	 */
	private function twentytwentyone() {

		return /* @lang CSS */ '
			.entry-content > .wpforms-lead-forms-container:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator) {
					max-width: 700px;
			}
		
			.wpforms-lead-forms-container input[type=checkbox]:after,
			.wpforms-lead-forms-container input[type=radio]:after {
				content: none;
			}
		
			.wpforms-lead-forms-container input[type=checkbox] + label, input[type=radio] + label {
				padding: 0;
			}
		
			.wpforms-lead-forms-container button:not(:hover):not(:active):not(.has-background) {
				background-color: rgba(var(--wpforms-lead-forms-accent-color), 1);
			}';
	}

	/**
	 * Provide compatibility with Twenty Twenty theme.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @noinspection CssUnusedSymbol
	 */
	private function twentytwenty() {

		return /* @lang CSS */ '
			.wpforms-field-radio:not(.wpforms-list-2-columns):not(.wpforms-list-3-columns):not(.wpforms-list-inline) label {
				display: inline;
			}';
	}

	/**
	 * Provide compatibility with Divi theme.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function divi() {

		return /* @lang CSS */ '
			#left-area .wpforms-lead-forms-container ul,
			.entry-content.wpforms-lead-forms-container ul,
			.et-l--body .wpforms-lead-forms-container ul,
			.et-l--footer .wpforms-lead-forms-container ul,
			.et-l--header .wpforms-lead-forms-container ul {
				list-style: none;
				padding: 0;
				line-height: 1.5;
			}';
	}
}
