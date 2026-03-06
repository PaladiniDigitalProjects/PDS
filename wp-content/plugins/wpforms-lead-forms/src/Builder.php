<?php

namespace WPFormsLeadForms;

use WPForms_Builder_Panel_Settings;

/**
 * WPForms Lead Forms builder class.
 *
 * @since 1.0.0
 */
class Builder {

	/**
	 * A link to public addon documentation page.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const DOCUMENTATION_URL = 'https://wpforms.com/docs/lead-forms-addon/';

	/**
	 * Default boolean/toggle settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	const DEFAULT_FLAGS = [
		'form_container'  => 0,
		'drop_shadow'     => 0,
		'rounded_corners' => 0,
	];

	/**
	 * Default color settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	const DEFAULT_COLORS = [
		'accent_color'         => '#0299ed',
		'container_background' => '#ffffff',
		'field_borders'        => '#cccccc',
		'primary_text'         => '#444444',
		'secondary_text'       => '#777777',
	];

	/**
	 * Builder hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueue_assets' ] );
		add_filter( 'wpforms_builder_settings_sections', [ $this, 'register_settings' ], 10, 2 );
		add_action( 'wpforms_form_settings_panel_content', [ $this, 'settings_content' ], 10, 1 );
		add_action( 'wpforms_builder_print_footer_scripts', [ $this, 'warning_template' ] );
		add_filter( 'wpforms_builder_output_classes', [ $this, 'add_lead_forms_class' ], 10, 2 );
		add_action( 'wpforms_field_page_break_field_preview_after', [ $this, 'add_page_break_title' ], 10, 2 );
		add_action( 'wpforms_builder_print_footer_scripts', [ $this, 'loader_template' ] );
		add_filter( 'wpforms_admin_builder_templates_apply_to_existing_form_modify_data', [ $this, 'switch_off_addon' ], 10, 3 );
		add_filter( 'wpforms_templates_class_base_template_replace_modify_data', [ $this, 'switch_off_addon' ], 10, 3 );
		add_filter( 'wpforms_save_form_args', [ $this, 'save_internal_information_field' ], 10, 3 );
	}

	/**
	 * Register settings area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections Settings area sections.
	 *
	 * @return array
	 */
	public function register_settings( $sections ) {

		$sections['lead_forms'] = esc_html__( 'Lead Forms', 'wpforms-lead-forms' );

		return $sections;
	}

	/**
	 * Settings content.
	 *
	 * @since 1.0.0
	 *
	 * @param WPForms_Builder_Panel_Settings $instance Settings panel instance.
	 */
	public function settings_content( $instance ) {

		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-lead_forms">';

		printf(
			'<div class="wpforms-panel-content-section-title">%s</div>',
			esc_html__( 'Lead Forms', 'wpforms-lead-forms' )
		);

		if ( empty( $instance->form_data['settings']['lead_forms']['enable'] ) ) {

			echo '<div class="wpforms-lead-forms-description">';

			printf(
				wp_kses( /* translators: %s is a link to the WPForms.com documentation article. */
					__( 'Create an embeddable form with multiple page breaks. Your form will be restructured to include a page break between each field so users only see one field at a time as they progress through the form. You can publish this form in a post, page, or widget area. <a href="%s" target="_blank" rel="noopener noreferrer">Learn more about Lead Forms.</a>', 'wpforms-lead-forms' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( self::DOCUMENTATION_URL, 'Builder Settings', 'Lead Forms Documentation' ) )
			);

			echo '</div>';
		}

		wpforms_panel_field(
			'toggle',
			'lead_forms',
			'enable',
			$instance->form_data,
			esc_html__( 'Enable Lead Form Mode', 'wpforms-lead-forms' ),
			[
				'parent' => 'settings',
			]
		);

		echo '<div class="wpforms-lead-forms-sub-panel">';

			$this->settings_content_general( $instance->form_data );

			echo '<div class="wpforms-lead-forms-advanced-style-settings">';

				$this->settings_content_advanced( $instance->form_data );

				$this->settings_content_export_import( $instance->form_data );

			echo '</div>';

		echo '</div>';

		echo '</div>';
	}

	/**
	 * Settings panel content, general settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data.
	 */
	private function settings_content_general( $form_data ) {

		wpforms_panel_field(
			'text',
			'lead_forms',
			'title',
			$form_data,
			esc_html__( 'Lead Form Title', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Add a title to display above your published form.', 'wpforms-lead-forms' ),
			]
		);

		wpforms_panel_field(
			'toggle',
			'lead_forms',
			'scroll_animation',
			$form_data,
			esc_html__( 'Enable Scroll Animation', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Enable this option to scroll to the top of the form when the user clicks the next button.', 'wpforms-lead-forms' ),
			]
		);

		wpforms_panel_field(
			'color',
			'lead_forms',
			'accent_color',
			$form_data,
			esc_html__( 'Accent Color', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Change the color of your form\'s buttons, progress bar, and more.', 'wpforms-lead-forms' ),
				'data'    => [
					'fallback-color' => $this->get_color_field_value( 'accent_color', $form_data, self::DEFAULT_COLORS['accent_color'] ),
				],
			]
		);

		wpforms_panel_field(
			'toggle',
			'lead_forms',
			'advanced_style_settings',
			$form_data,
			esc_html__( 'Advanced Style Settings', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Enable form container styling and additional color options.', 'wpforms-lead-forms' ),
			]
		);
	}

	/**
	 * Settings panel content, advanced settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data.
	 */
	private function settings_content_advanced( $form_data ) {

		wpforms_panel_field(
			'toggle',
			'lead_forms',
			'form_container',
			$form_data,
			esc_html__( 'Form Container', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Show the form container to make your lead form stand out from the page.', 'wpforms-lead-forms' ),
			]
		);

		wpforms_panel_field(
			'toggle',
			'lead_forms',
			'drop_shadow',
			$form_data,
			esc_html__( 'Drop Shadow', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Add a drop shadow to the form container.', 'wpforms-lead-forms' ),
			]
		);

		wpforms_panel_field(
			'toggle',
			'lead_forms',
			'rounded_corners',
			$form_data,
			esc_html__( 'Rounded Corners', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'tooltip' => esc_html__( 'Display the form container with rounded corners.', 'wpforms-lead-forms' ),
			]
		);

		echo '<div class="wpforms-flex-container wpforms-flex-container-end">';

		wpforms_panel_field(
			'color',
			'lead_forms',
			'container_background',
			$form_data,
			esc_html__( 'Container Background', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'class'   => 'wpforms-panel-field-half',
				'tooltip' => esc_html__( 'Change the color of the form container background.', 'wpforms-lead-forms' ),
				'data'    => [
					'fallback-color' => $this->get_color_field_value( 'container_background', $form_data, self::DEFAULT_COLORS['container_background'] ),
				],
			]
		);

		wpforms_panel_field(
			'color',
			'lead_forms',
			'field_borders',
			$form_data,
			esc_html__( 'Field Borders', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'class'   => 'wpforms-panel-field-half',
				'tooltip' => esc_html__( 'Change the color of the field borders.', 'wpforms-lead-forms' ),
				'data'    => [
					'fallback-color' => $this->get_color_field_value( 'field_borders', $form_data, self::DEFAULT_COLORS['field_borders'] ),
				],
			]
		);

		echo '</div>';

		echo '<div class="wpforms-flex-container wpforms-flex-container-end">';

		wpforms_panel_field(
			'color',
			'lead_forms',
			'primary_text',
			$form_data,
			esc_html__( 'Primary Text', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'class'   => 'wpforms-panel-field-half',
				'tooltip' => esc_html__( 'Change the color of the primary text (e.g., page titles, field labels).', 'wpforms-lead-forms' ),
				'data'    => [
					'fallback-color' => $this->get_color_field_value( 'primary_text', $form_data, self::DEFAULT_COLORS['primary_text'] ),
				],
			]
		);

		wpforms_panel_field(
			'color',
			'lead_forms',
			'secondary_text',
			$form_data,
			esc_html__( 'Secondary Text', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'class'   => 'wpforms-panel-field-half',
				'tooltip' => esc_html__( 'Change the color of the secondary text (e.g., input text, descriptions).', 'wpforms-lead-forms' ),
				'data'    => [
					'fallback-color' => $this->get_color_field_value( 'secondary_text', $form_data, self::DEFAULT_COLORS['secondary_text'] ),
				],
			]
		);

		echo '</div>';
	}

	/**
	 * Settings panel content, import-export settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data.
	 */
	private function settings_content_export_import( $form_data ) {

		$export = $this->export_import_styles();

		$note = sprintf(
			wp_kses( /* translators: %s is a link for documentation. */
				__( ' If you\'ve copied style settings from another lead form, you can paste them here to add the same styling to this form. Any current style settings will be overwritten. See our <a href="%s" target="_blank" rel="noopener noreferrer">lead form styling documentation</a> for details.', 'wpforms-lead-forms' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( wpforms_utm_link( self::DOCUMENTATION_URL . '#import-export-styles', 'Builder Settings', 'Lead Form Design Examples' ) )
		);

		$import = wpforms_panel_field(
			'text',
			'lead_forms',
			'import',
			$form_data,
			esc_html__( 'Import New Style Settings', 'wpforms-lead-forms' ),
			[
				'parent'  => 'settings',
				'after'   => "<p class='wpforms-lead-forms-note note'>{$note}</p>",
				'tooltip' => esc_html__( 'Paste style settings from another lead form.', 'wpforms-lead-forms' ),
			],
			false
		);

		$button = '<button class="wpforms-btn wpforms-btn-sm wpforms-btn-blue" id="wpforms-lead-forms-import-styles">Import Style Settings</button>';

		// Wrap advanced settings to the unfoldable group.
		wpforms_panel_fields_group(
			$export . $import . $button,
			[
				'borders'    => [ 'top' ],
				'title'      => esc_html__( 'Export / Import Style Settings', 'wpforms-lead-forms' ),
				'unfoldable' => true,
			]
		);

		// Store IIF field ID reference. Required to be able to delete it when disabling Lead Forms.
		wpforms_panel_field(
			'text',
			'lead_forms',
			'iif_id_ref',
			$form_data,
			'',
			[
				'class'   => 'wpforms-hidden',
				'default' => ! empty( $form_data['meta']['lead_forms']['iif_id_ref'] ) ? $form_data['meta']['lead_forms']['iif_id_ref'] : '',
			]
		);
	}

	/**
	 * Export block.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function export_import_styles() {

		return sprintf(
			'<div class="wpforms-panel-field  wpforms-panel-field-text wpforms-panel-field-lead-form-export">
				<label>%s<i class="fa fa-question-circle-o wpforms-help-tooltip" title="%s"></i></label>
				<input type="text" id="wpforms-panel-field-settings-lead_forms_export_your_style_settings" name="settings[lead_forms][export]" value="%s" disabled>
				<span class="wpforms-lead-forms-copy" id="wpforms-builder-lead-forms-copy">
					<i class="fa fa-files-o" aria-hidden="true"></i>
				</span>
			</div>',
			esc_html__( 'Export Your Style Settings', 'wpforms-lead-forms' ),
			esc_html__( 'Copy this form\'s styles so you can use them in another lead form.', 'wpforms-lead-forms' ),
			esc_attr( implode( ',', self::DEFAULT_FLAGS + self::DEFAULT_COLORS ) )
		);
	}

	/**
	 * Enqueue a JavaScript file and inline CSS styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-lead-forms-admin-builder',
			WPFORMS_LEAD_FORMS_URL . "assets/js/builder{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_LEAD_FORMS_VERSION,
			true
		);

		wp_enqueue_style(
			'wpforms-lead-forms-admin-builder',
			WPFORMS_LEAD_FORMS_URL . "assets/css/builder{$min}.css",
			[],
			WPFORMS_LEAD_FORMS_VERSION
		);

		wp_localize_script(
			'wpforms-lead-forms-admin-builder',
			'wpforms_lead_forms_admin_builder',
			$this->get_strings()
		);
	}

	/**
	 * Get strings to supply to the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_strings() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		return [
			'nonce'                                 => wp_create_nonce( 'wpforms_admin_builder_conversational_forms_nonce' ),
			'enable_warning'                        => sprintf(
				wp_kses( /* translators: %s is a link to Revisions page. */
					__(
						'Enabling Lead Forms will restructure your form by adding page breaks between each field. You can revert this change using <a href="%s">Revisions</a>.',
						'wpforms-lead-forms'
					),
					[
						'a' => [
							'href' => [],
						],
					]
				),
				esc_url(
					add_query_arg(
						[
							'view'    => 'revisions',
							'form_id' => $form_id,
						],
						admin_url( 'admin.php?page=wpforms-builder' )
					)
				)
			),
			'internal_field_extended_description'   => sprintf(
				wp_kses( /* translators: %1$s is a link to documentation. */
					__( 'If you scroll down and look at your form, you\'ll notice that page breaks have been inserted after each field. Lead forms typically have <a href="%1$s" target="_blank" rel="noopener noreferrer">1–3 fields per page</a>, so this will help you get your new lead form up and running quickly. Follow the tips below to optimize your lead form. These are completely optional, but will yield the best results!', 'wpforms-lead-forms' ) . "\n" .
					'[] ' . __( '<strong>Create big, bold headings.</strong> Instead of using standard field labels, try using page titles. Click on a Page Break to add a page title. Be sure to hide each field\'s label in its field options as well.', 'wpforms-lead-forms' ) . "\n" .
					'[] ' . __( '<strong>Consolidate pages, if necessary.</strong> Each field is now on its own page, but you may want to group similar fields on the same page. Drag and drop fields to reorganize them, then delete any Page Breaks you no longer need.', 'wpforms-lead-forms' ) . "\n" .
					'[] ' . __( '<strong>Use icon choices for radio buttons.</strong> Upgrade your standard Multiple Choice field radio buttons to fancy icon choices that look great and convert.', 'wpforms-lead-forms' ) . "\n" .
					'[] ' . __( '<strong>Use placeholders instead of sublabels.</strong> Some fields (Name, Address, etc.) have sublabels. Trying using placeholder text instead.', 'wpforms-lead-forms' ) . "\n" .
					/* translators: %1$s is a link to documentation. */
					__( 'For more lead forms tips and best practices, check out our <a href="%1$s" target="_blank" rel="noopener noreferrer">Complete Guide to Lead Forms.</a>', 'wpforms-lead-forms' ),
					[
						'a'      => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
						'strong' => [],
					]
				),
				esc_url( wpforms_utm_link( self::DOCUMENTATION_URL . '#best-practices', 'Internal Information Field', 'Lead Forms Documentation' ) )
			),
			'internal_field_label'                  => esc_html__( 'Lead Forms Enabled', 'wpforms-lead-forms' ),
			'internal_field_description'            => esc_html__( 'Your form has been converted to a lead form. Learn how to format it for maximum impact.', 'wpforms-lead-forms' ),
			'enable_button'                         => esc_html__( 'Enable Lead Forms', 'wpforms-lead-forms' ),
			'default_flags'                         => array_keys( self::DEFAULT_FLAGS ),
			'default_colors'                        => array_keys( self::DEFAULT_COLORS ),
			'confirm_import_button'                 => esc_html__( 'Yes, Import', 'wpforms-lead-forms' ),
			'import_confirmation'                   => esc_html__( 'You\'re about to overwrite your style settings. Are you sure you want to proceed?', 'wpforms-lead-forms' ),
			'failed_import'                         => esc_html__( 'Your import string is invalid. Please check the string and try again.', 'wpforms-lead-forms' ),
			'conversational_forms_alert'            => esc_html__( 'Lead Forms cannot be enabled because you are in Conversational Forms mode.', 'wpforms-lead-forms' ),
			'form_pages_alert'                      => esc_html__( 'Lead Forms cannot be enabled because you are in Form Pages mode.', 'wpforms-lead-forms' ),
			'conversational_forms_lead_forms_alert' => esc_html__( 'You cannot use Conversational Forms in Lead Forms mode.', 'wpforms-lead-forms' ),
			'form_pages_lead_forms_alert'           => esc_html__( 'You cannot use Form Pages in Lead Forms mode.', 'wpforms-lead-forms' ),
			'field_alert'                           => [
				'default'       => esc_html__( 'You cannot use this field in Lead Forms mode.', 'wpforms-lead-forms' ),
				'entry-preview' => esc_html__( 'You cannot use the Entry Preview field in Lead Forms mode.', 'wpforms-lead-forms' ),
				'layout'        => esc_html__( 'You cannot use the Layout field in Lead Forms mode.', 'wpforms-lead-forms' ),
			],
			'disabled_option'                       => esc_html__( 'You cannot use this option because you are in Lead Forms mode.', 'wpforms-lead-forms' ),
		];
	}

	/**
	 * Keywords warning message block template.
	 *
	 * @since 1.0.0
	 */
	public function warning_template() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id   = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		$form_data = wpforms()->obj( 'form' )->get( $form_id, [ 'content_only' => true ] );

		// Show template only once after first time installation.
		if ( ! empty( $form_data['settings']['lead_forms']['enable'] ) ) {
			return;
		}

		?>
		<script type="text/html" id="tmpl-wpforms-lead-forms-warning-template">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render(
				WPFORMS_LEAD_FORMS_PATH . 'templates/builder/warning',
                [
					'form_id' => $form_id,
                ],
				true
			);
			?>
		</script>
		<?php
	}

	/**
	 * Loader template.
	 *
	 * @since 1.0.0
	 */
	public function loader_template() {

		?>
		<script type="text/html" id="tmpl-wpforms-lead-forms-loader-template">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render(
				WPFORMS_LEAD_FORMS_PATH . 'templates/builder/loader',
				[
					'title'       => __( 'Just a Minute!', 'wpforms-lead-forms' ),
					'description' => __( 'We\'re converting your form to a lead form. This could take a little while if your form has lots of fields. Please don\'t close or reload your browser window.', 'wpforms-lead-forms' ),
				],
				true
			);
			?>
		</script>
		<?php
	}

	/**
	 * Switch off the addon when user changes the template.
	 * Do not switch off if new template has LF enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new       Updated form data.
	 * @param array $form_data Current form data.
	 * @param array $template  Template data.
	 */
	public function switch_off_addon( $new, $form_data, $template ) {

		$template = (array) $template;

		if ( ! empty( $template['data']['settings']['lead_forms'] ) && ! empty( $template['data']['settings']['lead_forms']['enable'] ) ) {

			$new['settings']['lead_forms']['enable'] = '1';

			return $new;
		}

		if ( empty( $new['settings']['lead_forms']['enable'] ) ) {
			return $new;
		}

		unset( $new['settings']['lead_forms']['enable'] );

		if ( ! empty( $new['meta']['lead_forms']['iif_id_ref'] ) ) {
			unset( $new['meta']['lead_forms']['iif_id_ref'] );
		}

		return $new;
	}

	/**
	 * Add .wpforms-lead-forms-preview class to builder container.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes   Classes array.
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	public function add_lead_forms_class( $classes, $form_data ) {

		if ( ! empty( $form_data['settings']['lead_forms']['enable'] ) ) {
			$classes[] = 'wpforms-lead-forms-preview';
		}

		return $classes;
	}

	/**
	 * Add page break title after page break preview field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 * @param array $field     Field data.
	 */
	public function add_page_break_title( $form_data, $field ) {

		$title = ! empty( $field['title'] ) ? $field['title'] : '';

		echo '<div class="wpforms-pagebreak-title wpforms-lead-forms-pagebreak-title">' . esc_html( $title ) . '</div>';
	}

	/**
	 * Get a valid HEX color value for a color field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_slug    Color field slug.
	 * @param array  $form_data     Form data.
	 * @param string $default_value Default color value.
	 *
	 * @return string Valid HEX color value.
	 */
	private function get_color_field_value( $field_slug, $form_data, $default_value ) {

		$color_value = isset( $form_data['settings']['lead_forms'][ $field_slug ] ) ? wpforms_sanitize_hex_color( $form_data['settings']['lead_forms'][ $field_slug ] ) : '';

		return empty( $color_value ) ? $default_value : $color_value;
	}

	/**
	 * Save Lead Forms Internal Information field ID to permanent storage.
	 * When addon is disabled the settings['lead_forms']['iif_id_ref'] is removed after the next form update.
	 *
	 * @since 1.3.0
	 *
	 * @param array $form Form array, usable with wp_update_post.
	 * @param array $data Data retrieved from $_POST and processed.
	 * @param array $args Update form arguments.
	 *
	 * @return array
	 */
	public function save_internal_information_field( $form, $data, $args ) {

		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		if ( empty( $form_data['settings']['lead_forms']['enable'] ) ) {
			return $form;
		}

		if ( empty( $form_data['fields'] ) ) {
			return $form;
		}

		$form_data['meta']['lead_forms']['iif_id_ref'] = isset( $form_data['lead_forms']['iif_id_ref'] ) ? $form_data['lead_forms']['iif_id_ref'] : '';

		$form['post_content'] = wpforms_encode( $form_data );

		return $form;
	}
}
