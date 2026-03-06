<?php

namespace WPFormsLeadForms;

use WP_Post;

/**
 * WPForms Lead Forms frontend class.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Add hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'wpforms_frontend_css', [ $this, 'page_enqueue_styles' ] );
		add_action( 'wpforms_frontend_confirmation', [ $this, 'confirmation_enqueue_styles' ] );
		add_action( 'wpforms_frontend_js', [ $this, 'enqueue_scripts' ] );
		add_action( 'wpforms_frontend_output_after', [ $this, 'enqueue_inline_styles' ], 10, 2 );
		add_action( 'wpforms_frontend_confirmation_message_after', [ $this, 'confirmation_enqueue_inline_styles' ], 10, 4 );
		add_filter( 'wpforms_frontend_container_class', [ $this, 'modify_container_class' ], 30, 2 );
		add_action( 'wpforms_frontend_output', [ $this, 'print_form_title' ], 6, 5 );
		add_action( 'wpforms_field_page_break_page_fields_before', [ $this, 'print_page_break_title' ], 1, 2 );
		add_action( 'wpforms_display_submit_after', [ $this, 'print_progress_bar' ], 30, 2 );
		add_action( 'wpforms_frontend_confirmation_message_before', [ $this, 'print_confirmation_header' ], 30, 4 );
		add_action( 'wpforms_frontend_confirmation_message_after', [ $this, 'print_confirmation_progress_bar' ], 30, 4 );
		add_filter( 'wpforms_field_data', [ $this, 'change_default_signature_color' ], 10, 2 );
		add_filter( 'wpforms_field_data', [ $this, 'change_default_icon_color' ], 10, 2 );
		add_filter( 'tiny_mce_before_init', [ $this, 'change_richtext_settings' ], 10, 2 );
		add_filter( 'wpforms_field_properties', [ $this, 'field_properties' ], 5, 3 );

		// Save and Resume integration.
		add_filter( 'wpforms_save_resume_frontend_display_form_confirmation_classes', [ $this, 'modify_container_class' ], 30, 2 );
		add_filter( 'wpforms_save_resume_frontend_display_form_confirmation_id', [ $this, 'modify_container_id' ], 30, 2 );
		add_action( 'wpforms_save_resume_frontend_confirmation_message_before', [ $this, 'print_save_resume_confirmation_header' ], 10, 1 );
		add_action( 'wpforms_save_resume_frontend_display_disclaimer_before', [ $this, 'print_save_resume_disclaimer_header' ], 10, 1 );
		add_action( 'wpforms_save_resume_frontend_display_confirmation_before', [ $this, 'print_save_resume_disclaimer_header' ], 10, 1 );

		// Form Locker.
		add_action( 'wpforms_frontend_not_loaded', [ $this, 'enqueue_inline_styles' ], 20, 2 );
	}

	/**
	 * Whether Lead Forms is enabled for the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	private function is_enabled( $form_data ) {

		$settings = $this->get_settings( $form_data );

		return ! empty( $settings['enable'] );
	}

	/**
	 * Enqueue styles on a page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Data and settings of all forms on the page.
	 */
	public function page_enqueue_styles( $forms ) {

		$forms = (array) $forms;

		if ( ! $this->is_page_has_lead_forms( $forms ) ) {
			return;
		}

		$this->enqueue_styles();
	}

	/**
	 * Enqueue styles on the confirmation page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	public function confirmation_enqueue_styles( $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		$this->enqueue_styles();
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	private function enqueue_styles() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-lead-forms',
			WPFORMS_LEAD_FORMS_URL . "assets/css/front{$min}.css",
			[],
			WPFORMS_LEAD_FORMS_VERSION
		);

		/**
		 * Fires after Lead Forms styles are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpforms_lead_forms_frontend_enqueue_styles' );
	}

	/**
	 * Is page has at least one form with enabled Lead Forms.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Data and settings of all forms on the page.
	 *
	 * @return bool
	 */
	private function is_page_has_lead_forms( $forms ) {

		foreach ( $forms as $form_data ) {
			if ( $this->is_enabled( $form_data ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue inline styles.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $form_data Form data and settings.
	 * @param WP_Post $form      Form post type object.
	 */
	public function enqueue_inline_styles( $form_data, $form ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		$inline_styles = $this->get_form_inline_styles( $form_data );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<style id="wpforms-lead-forms-inline-styles">' . $inline_styles . '</style>';
	}

	/**
	 * Enqueue inline styles on confirmation page for non-AJAX forms.
	 *
	 * @since 1.0.0
	 *
	 * @param array $confirmation Current confirmation data.
	 * @param array $form_data    Form data and settings.
	 * @param array $fields       Sanitized field data.
	 * @param int   $entry_id     Entry id.
	 */
	public function confirmation_enqueue_inline_styles( $confirmation, $form_data, $fields, $entry_id ) {

		if ( empty( $form_data['id'] ) || wp_doing_ajax() ) {
			return;
		}

		$form = wpforms()->obj( 'form' )->get( $form_data['id'] );

		$this->enqueue_inline_styles( $form_data, $form );
	}

	/**
	 * Get form CSS variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	private function get_form_variables( $form_data ) {

		$defaults = [
			'accent_color'       => Builder::DEFAULT_COLORS['accent_color'],
			'drop_shadow'        => 'none',
			'rounded_corners'    => 0,
			'container_bg_color' => 'transparent',
			'field_borders'      => Builder::DEFAULT_COLORS['field_borders'],
			'primary_text'       => Builder::DEFAULT_COLORS['primary_text'],
			'secondary_text'     => Builder::DEFAULT_COLORS['secondary_text'],
		];

		$settings  = $this->get_settings( $form_data );
		$variables = $defaults;

		$variables['accent_color'] = $this->get_color( 'accent_color', $form_data, $defaults );

		if ( ! empty( $settings['advanced_style_settings'] ) ) {
			if ( ! empty( $settings['form_container'] ) ) {
				$variables['drop_shadow']        = ! empty( $settings['drop_shadow'] ) ? '0px 5px 30px rgba(0, 0, 0, 0.1)' : $variables['drop_shadow'];
				$variables['rounded_corners']    = ! empty( $settings['rounded_corners'] ) ? '10px' : $variables['rounded_corners'];
				$variables['container_bg_color'] = ! empty( $settings['container_background'] ) ? $settings['container_background'] : Builder::DEFAULT_COLORS['container_background'];
			}
			$variables['field_borders']  = $this->get_color( 'field_borders', $form_data, $defaults );
			$variables['primary_text']   = $this->get_color( 'primary_text', $form_data, $defaults );
			$variables['secondary_text'] = $this->get_color( 'secondary_text', $form_data, $defaults );
		}

		/**
		 * Allow modifying CSS variables.
		 *
		 * @since 1.0.0
		 *
		 * @param array $variables CSS variables.
		 * @param array $form_data Form data and settings.
		 */
		$variables = (array) apply_filters( 'wpforms_lead_forms_frontend_get_form_variables', $variables, $form_data );

		return wp_parse_args( $variables, $defaults );
	}

	/**
	 * Get option color.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option_name Option name.
	 * @param array  $form_data   Form data and settings.
	 * @param array  $defaults    Default variables.
	 *
	 * @return string
	 */
	private function get_color( $option_name, $form_data, $defaults ) {

		$settings      = $this->get_settings( $form_data );
		$default_value = ! empty( $defaults[ $option_name ] ) ? $defaults[ $option_name ] : 'transparent';

		if ( empty( $settings[ $option_name ] ) ) {
			return $default_value;
		}

		return sanitize_hex_color( $settings[ $option_name ] ) ? $settings[ $option_name ] : $default_value;
	}

	/**
	 * Get Lead Forms settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	private function get_settings( $form_data ) {

		return ! empty( $form_data['settings']['lead_forms'] ) ? $form_data['settings']['lead_forms'] : [];
	}

	/**
	 * Get form inline styles.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return string
	 */
	public function get_form_inline_styles( $form_data ) {

		$variables = $this->get_form_variables( $form_data );

		return sprintf(
			'#wpforms-%1$s, #wpforms-locked-%1$s {
				--wpforms-lead-forms-accent-color: %2$s;
				--wpforms-lead-forms-box-shadow: %3$s;
				--wpforms-lead-forms-border-radius: %4$s;
				--wpforms-lead-forms-container-background: %5$s;
				--wpforms-lead-forms-container-background-color: %6$s;
				--wpforms-lead-forms-field-border-color: %7$s;
				--wpforms-lead-forms-primary-text-color: %8$s;
				--wpforms-lead-forms-secondary-text-color: %9$s;
			}',
			is_numeric( $form_data['id'] ) ? absint( $form_data['id'] ) : esc_attr( $form_data['id'] ),
			esc_attr( wpforms_hex_to_rgb( $variables['accent_color'] ) ),
			esc_attr( $variables['drop_shadow'] ),
			esc_attr( $variables['rounded_corners'] ),
			$variables['container_bg_color'] === 'transparent' ? 'transparent' : sanitize_hex_color( $variables['container_bg_color'] ),
			$variables['container_bg_color'] === 'transparent' ? Builder::DEFAULT_COLORS['container_background'] : sanitize_hex_color( $variables['container_bg_color'] ),
			sanitize_hex_color( $variables['field_borders'] ),
			sanitize_hex_color( $variables['primary_text'] ),
			esc_attr( wpforms_hex_to_rgb( $variables['secondary_text'] ) )
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Data and settings of all forms on the page.
	 */
	public function enqueue_scripts( $forms ) {

		if ( ! $this->is_page_has_lead_forms( $forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-lead-forms',
			WPFORMS_LEAD_FORMS_URL . "assets/js/front{$min}.js",
			[ 'jquery' ],
			WPFORMS_LEAD_FORMS_VERSION,
			true
		);

		/**
		 * Fires after Lead Forms scripts are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpforms_lead_forms_frontend_enqueue_scripts' );
	}

	/**
	 * Modify container classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes   Form container classes.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	public function modify_container_class( $classes, $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return $classes;
		}

		$classes   = array_diff( $classes, [ 'wpforms-container-full' ] );
		$classes[] = 'wpforms-lead-forms-container';

		return $classes;
	}

	/**
	 * Modify container ID for Save & Resume addon.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id        ID.
	 * @param array  $form_data Form data and settings.
	 *
	 * @return string
	 */
	public function modify_container_id( $id, $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return $id;
		}

		$id .= 'wpforms-' . $form_data['id'];

		return $id;
	}

	/**
	 * Print form title.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data   Form data and settings.
	 * @param null  $deprecated  Deprecated.
	 * @param bool  $title       Whether to display form title.
	 * @param bool  $description Whether to display form description.
	 * @param array $errors      List of all errors filled in WPForms_Process::process().
	 */
	public function print_form_title( $form_data, $deprecated, $title, $description, $errors ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		$settings = $this->get_settings( $form_data );

		if ( ! isset( $settings['title'] ) || wpforms_is_empty_string( $settings['title'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_LEAD_FORMS_PATH . 'templates/frontend/form-title',
			[
				'title' => $settings['title'],
			],
			true
		);
	}

	/**
	 * Print page break title.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field data and settings.
	 * @param array $form_data Form data and settings.
	 */
	public function print_page_break_title( $field, $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		if ( empty( $field['title'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_LEAD_FORMS_PATH . 'templates/frontend/page-title',
			[
				'title' => $field['title'],
			],
			true
		);
	}

	/**
	 * Print progress bar.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $form_data Form data and settings.
	 * @param string $context   Button context.
	 */
	public function print_progress_bar( $form_data, $context ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		if ( empty( wpforms()->obj( 'frontend' )->pages['total'] ) ) {
			return;
		}

		$total = wpforms()->obj( 'frontend' )->pages['total'] + 1;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_LEAD_FORMS_PATH . 'templates/frontend/progress-bar',
			[
				'current' => 1,
				'total'   => $total,
			],
			true
		);
	}

	/**
	 * Add header to the confirmation page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $confirmation Current confirmation data.
	 * @param array $form_data    Form data and settings.
	 * @param array $fields       Sanitized field data.
	 * @param int   $entry_id     Entry id.
	 */
	public function print_confirmation_header( $confirmation, $form_data, $fields, $entry_id ) {

		$this->print_confirmation_header_template( $form_data );
	}

	/**
	 * Add header to the Save and Resume confirmation page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	public function print_save_resume_confirmation_header( $form_data ) {

		$this->print_confirmation_header_template( $form_data );
	}

	/**
	 * Add header to the confirmation page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	private function print_confirmation_header_template( $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( WPFORMS_LEAD_FORMS_PATH . 'templates/frontend/confirmation-header' );
	}

	/**
	 * Add header to the Save and Resume disclaimer page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	public function print_save_resume_disclaimer_header( $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		printf(
			'<div class="wpforms-save-resume-title">%s</div>',
			esc_html( $form_data['settings']['save_resume_link_text'] )
		);
	}

	/**
	 * Add progress bar to the confirmation page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $confirmation Current confirmation data.
	 * @param array $form_data    Form data and settings.
	 * @param array $fields       Sanitized field data.
	 * @param int   $entry_id     Entry id.
	 */
	public function print_confirmation_progress_bar( $confirmation, $form_data, $fields, $entry_id ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_LEAD_FORMS_PATH . 'templates/frontend/progress-bar',
			[
				'current' => 1,
				'total'   => 1,
			],
			true
		);
	}

	/**
	 * Change signature color to the accent color.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Current field.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	public function change_default_signature_color( $field, array $form_data ) {

		if ( empty( $field['type'] ) || $field['type'] !== 'signature' ) {
			return $field;
		}

		if ( ! $this->is_enabled( $form_data ) ) {
			return $field;
		}

		$variables          = $this->get_form_variables( $form_data );
		$field['ink_color'] = $variables['accent_color'];

		return $field;
	}

	/**
	 * Change icon choices color to the accent color.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Current field.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	public function change_default_icon_color( $field, array $form_data ) {

		if ( ! $this->is_enabled( $form_data ) ) {
			return $field;
		}

		if ( empty( $field['choices_icons_color'] ) ) {
			return $field;
		}

		$variables = $this->get_form_variables( $form_data );

		$field['choices_icons_color'] = $variables['accent_color'];

		return $field;
	}

	/**
	 * Change richtext field iframe background.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $mce_init  An array with TinyMCE config.
	 * @param string $editor_id Unique editor identifier, e.g. 'content'. Accepts 'classic-block'
	 *                          when called from block editor's Classic block.
	 *
	 * @return array
	 */
	public function change_richtext_settings( $mce_init, $editor_id ) {

		if ( strpos( $editor_id, 'wpforms-' ) !== 0 ) {
			return $mce_init;
		}

		static $forms = [];

		// 8 is the length of the 'wpforms-' string.
		$form_id = (int) substr( $editor_id, '8', strpos( $editor_id, '-field', 1 ) - 8 );

		if ( empty( $forms[ $form_id ] ) ) {
			$forms[ $form_id ] = wpforms()->obj( 'form' )->get( $form_id, [ 'content_only' => true ] );
		}

		if ( ! $this->is_enabled( $forms[ $form_id ] ) ) {
			return $mce_init;
		}

		$variables = $this->get_form_variables( $forms[ $form_id ] );

		$mce_init['body_class'] .= ' wpforms-lead-forms';

		$mce_init['content_style'] = sprintf(
			'.wpforms-lead-forms { background: %s; color: %s !important; }',
			'transparent',
			esc_html( $variables['secondary_text'] )
		);

		return $mce_init;
	}

	/**
	 * Hide checkbox and radio inputs for better styling.
	 *
	 * @since 1.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		// The filter should not work for Entry edit pages.
		if ( is_admin() ) {
			return $properties;
		}

		if ( ! $this->is_enabled( $form_data ) ) {
			return $properties;
		}

		if ( ! in_array( $field['type'], [ 'radio', 'checkbox', 'payment-checkbox', 'payment-multiple' ], true ) ) {
			return $properties;
		}

		// Do not process for image choices.
		if ( ! empty( $field['choices_images'] ) ) {
			return $properties;
		}

		foreach ( $properties['inputs'] as $key => $inputs ) {
			$properties['inputs'][ $key ]['class'][] = 'wpforms-screen-reader-element';
		}

		return $properties;
	}
}
