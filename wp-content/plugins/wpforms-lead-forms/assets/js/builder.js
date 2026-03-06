/* global wpforms_builder, WPFormsBuilder, wpforms_lead_forms_admin_builder, wpf */

'use strict';

/**
 * WPForms Lead Forms builder.
 *
 * @since 1.0.0
 */
let WPFormsBuilderLeadForms = window.WPFormsBuilderLeadForms || ( function( document, window, $ ) {

	/**
	 * Elements holder.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	const el = {};

	/**
	 * Runtime variables.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	const vars = {
		toggles: wpforms_lead_forms_admin_builder.default_flags,
		colors: wpforms_lead_forms_admin_builder.default_colors,
		blockedOptions: [
			'choices_icons_color',
			'choices_icons_style',
			'choices_images_style',
			'icon_color',
			'indicator',
			'indicator_color',
			'ink_color',
			'input_columns',
			'nav_align',
			'prev_toggle',
			'scroll_disabled',
			'size',
			'style',
		],
		blockedFields: [ 'entry-preview', 'layout' ],
		ignoredFields: [ 'html', 'hidden', 'unavailable' ],
		showConfirmationBeforeEnabling: false,
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 */
	const app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			app.setup();
			app.bindEvents();

			if ( app.isLeadFormsEnabled() ) {
				app.fieldsPanel.toggleOptionsAvailability();
			}

			app.fieldsPanel.forceOptionsValues();
			app.togglePanels();
			app.export.updateField();
			app.togglePanelAdvancedStyleSettings();
			app.togglePanelFormContainer();
			vars.showConfirmationBeforeEnabling = ! app.isLeadFormsEnabled();
		},

		/**
		 * Setup. Prepare some variables.
		 *
		 * @since 1.0.0
		 */
		setup: function() {

			// Cache DOM elements.
			el.$builder                = $( '#wpforms-builder' );
			el.$addonToggle            = $( '#wpforms-panel-field-lead_forms-enable' );
			el.$scrollAnimationToggle  = $( '#wpforms-panel-field-lead_forms-scroll_animation' );
			el.$advancedSettings       = $( '.wpforms-lead-forms-advanced-style-settings' );
			el.$subPanel               = $( '.wpforms-lead-forms-sub-panel' );
			el.$advancedSettingsToggle = $( '#wpforms-panel-field-lead_forms-advanced_style_settings' );
			el.$formContainer          = $( '#wpforms-panel-field-lead_forms-form_container' );
			el.$shortcodeInput         = $( '#wpforms-panel-field-settings-lead_forms_export_your_style_settings' );
			el.$shortcodeCopy          = $( '#wpforms-builder-lead-forms-copy' );
			el.$importStyles           = $( '#wpforms-panel-field-lead_forms-import' );
			el.$loader                 = $( '#wpforms-builder-lead-forms-overlay' );
			el.$informationFieldRef    = $( '#wpforms-panel-field-lead_forms-iif_id_ref' );
		},

		/**
		 * Bind events.
		 *
		 * @since 1.0.0
		 */
		bindEvents: function() {

			el.$builder
				.on( 'change', '#wpforms-panel-field-lead_forms-enable', app.toggleLeadForms )
				.on( 'change', '#wpforms-panel-field-lead_forms-scroll_animation', app.fieldsPanel.forceScrollAnimation )
				.on( 'input', '#wpforms-panel-field-lead_forms-accent_color', app.fieldsPanel.forceAccentColor )
				.on( 'change', '#wpforms-panel-field-lead_forms-advanced_style_settings', app.togglePanelAdvancedStyleSettings )
				.on( 'change', '#wpforms-panel-field-lead_forms-form_container', app.togglePanelFormContainer )
				.on( 'change', '#wpforms-panel-field-lead_forms-form_container, #wpforms-panel-field-lead_forms-drop_shadow, #wpforms-panel-field-lead_forms-rounded_corners, #wpforms-panel-field-lead_forms-accent_color, #wpforms-panel-field-lead_forms-container_background, #wpforms-panel-field-lead_forms-field_borders, #wpforms-panel-field-lead_forms-primary_text, #wpforms-panel-field-lead_forms-secondary_text', app.export.updateField )
				.on( 'change', '.wpforms-field-option-row-choices_icons input, .wpforms-field-option-row-choices_images input', app.fieldsPanel.forceInlineLayout )
				.on( 'click', '#wpforms-lead-forms-import-styles', app.import.process )
				.on( 'click', '#wpforms-builder-lead-forms-copy', app.export.copyStylesToClipboard )
				.on( 'wpformsFieldAddDragStart wpformsBeforeFieldAddOnClick', app.fieldsPanel.addBlockedFieldModal )
				.on( 'wpformsFieldAdd', app.fieldsPanel.toggleOptionsAvailability )
				.on( 'wpformsFieldAdd', app.fieldsPanel.forceOptionsValues )
				.on( 'click', '.wpforms-field', app.fieldsPanel.toggleOptionsAvailability )
				.on( 'click', '.wpforms-field', app.fieldsPanel.forceOptionsValues )
				.on( 'click', '.wpforms-dyk.wpforms-lead-forms-warning a', app.fieldsPanel.switchOn )
				.on( 'click', '.wpforms-lead-forms-disabled-option', app.fieldsPanel.changeOptionBlockedModal )
				.on( 'click', '#wpforms-panel-field-settings-conversational_forms_enable, #wpforms-panel-field-settings-form_pages_enable', app.isOtherAddonsAvailable );

			$( document )
				.on( 'click', '.wpforms-lead-forms-enable-popup a', app.revisionsPanel.switchOn );
		},

		/**
		 * Show confirmation message before enabling addon.
		 *
		 * @since 1.0.0
		 */
		toggleLeadForms: function() {

			if ( ! app.isLeadFormsEnabled() ) {
				app.turnOffLeadForms();

				return;
			}

			const $this = $( this );

			// Do not turn the addon on when certain conditions are not met.
			if ( app.isLeadFormsAvailable( $this ) === false ) {
				return;
			}

			// Do not show confirmation popup if user already confirmed the reformatting during current session.
			if ( ! vars.showConfirmationBeforeEnabling ) {

				app.turnOnLeadForms();
				return;
			}

			// Confirmation message before enabling the feature.
			$.confirm( {
				title: wpforms_builder.heads_up,
				content: wpforms_lead_forms_admin_builder.enable_warning,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_lead_forms_admin_builder.enable_button,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action: function() {

							app.turnOnLeadForms();
							vars.showConfirmationBeforeEnabling = false;
						},
					},
					cancel: {
						text: wpforms_builder.cancel,
						action: app.turnOffLeadForms,
					},
				},
				onOpenBefore: function() {

					this.$body.addClass( 'wpforms-lead-forms-enable-popup' );
				},
			} );
		},

		/**
		 * Turn off the lead forms.
		 *
		 * @since 1.0.0
		 */
		turnOffLeadForms: function() {

			const fieldId = el.$informationFieldRef.val();

			el.$addonToggle.prop( 'checked', false );

			if ( fieldId ) {
				WPFormsBuilder.fieldDeleteById( fieldId, 'internal-information', 50 );
				el.$informationFieldRef.val( '' );
			}

			app.togglePanels();
		},

		/**
		 * Turn on the lead forms.
		 *
		 * @since 1.0.0
		 */
		turnOnLeadForms: function() {

			const fieldIndex            = app.fieldsPanel.getFieldIndexWithoutPageBreak();
			const convertingFieldsTotal = app.loader.getTotal();

			// Add internal information field if there is no fields or only one field without page break.
			if ( fieldIndex === false || convertingFieldsTotal === 1 ) {
				app.fieldsPanel.addInternalInformationField();
				app.togglePanels();
				app.fieldsPanel.removePreviousButton();

				return;
			}

			app.loader.showLoadingOverlay();
			app.fieldsPanel.addPageBreaks();
			app.fieldsPanel.removePreviousButton();
			app.togglePanels();
		},

		/**
		 * Return error message when LF can't be enabled.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} $this Object.
		 *
		 * @returns {boolean} Check result.
		 */
		isLeadFormsAvailable: function( $this ) {

			const errorMessage = app.getValidationErrorMessage();

			if ( ! errorMessage ) {
				return true;
			}

			app.showModal( errorMessage, 'warning' );

			$this.prop( 'checked', false );

			return false;
		},

		/**
		 * Return error message when other addons can't be enabled because of LF.
		 *
		 * @since 1.0.0
		 *
		 * @returns {boolean} Check result.
		 */
		isOtherAddonsAvailable: function() {

			if ( ! app.isLeadFormsEnabled() ) {
				return true;
			}

			let errorMessage = '';

			// Whether the Conversational Forms is enabled.
			if ( $( '#wpforms-panel-field-settings-form_pages_enable' ).is( ':checked' ) ) {
				errorMessage = wpforms_lead_forms_admin_builder.form_pages_lead_forms_alert;
			}

			// Whether the Form Pages is enabled.
			if ( $( '#wpforms-panel-field-settings-conversational_forms_enable' ).is( ':checked' ) ) {
				errorMessage = wpforms_lead_forms_admin_builder.conversational_forms_lead_forms_alert;
			}

			if ( ! errorMessage ) {
				return true;
			}

			app.showModal( errorMessage, 'warning' );

			$( this ).prop( 'checked', false );

			return false;
		},

		/**
		 * Check if addon is enabled.
		 *
		 * @since 1.0.0
		 *
		 * @returns {boolean} Lead Forms is enabled.
		 */
		isLeadFormsEnabled: function() {

			return $( '#wpforms-panel-field-lead_forms-enable' ).is( ':checked' );
		},

		/**
		 * Toggle all panels.
		 *
		 * @since 1.0.0
		 */
		togglePanels: function() {

			app.togglePanel();
			app.fieldsPanel.toggleOptionsAvailability();
			app.toggleBuilderClass();
			app.togglePanelAdvancedStyleSettings();
		},

		/**
		 * Toggle lead form settings.
		 *
		 * @since 1.0.0
		 */
		togglePanel: function() {

			if ( app.isLeadFormsEnabled() ) {
				el.$subPanel.show();
				app.renderWarningTemplate();

				return;
			}

			$( '.wpforms-lead-forms-warning' ).remove();
			el.$subPanel.hide();
		},

		/**
		 * Toggle Advanced Settings visibility.
		 *
		 * @since 1.0.0
		 */
		togglePanelAdvancedStyleSettings: function() {

			el.$advancedSettingsToggle.is( ':checked' ) ?
				el.$advancedSettings.show() :
				el.$advancedSettings.hide();
		},

		/**
		 * Show/hide Drop Shadow and Rounded Corners.
		 *
		 * @since 1.0.0
		 */
		togglePanelFormContainer: function() {

			const $block               = $( '#wpforms-panel-field-lead_forms-drop_shadow-wrap, #wpforms-panel-field-lead_forms-rounded_corners-wrap' );
			const $containerBackground = $( '#wpforms-panel-field-lead_forms-container_background' );

			if ( el.$formContainer.is( ':checked' ) ) {
				$block.show();
				$containerBackground.prop( 'disabled', false );

				return;
			}

			$block.hide();
			$containerBackground.prop( 'disabled', true );
		},

		/**
		 * Render warning message.
		 *
		 * @since 1.0.0
		 */
		renderWarningTemplate: function() {

			if ( $( '#tmpl-wpforms-lead-forms-warning-template' ).length === 0 ) {
				return;
			}

			const warningTmpl = wp.template( 'wpforms-lead-forms-warning-template' );

			$( warningTmpl() ).insertBefore( '#wpforms-panel-field-lead_forms-enable-wrap' );
		},

		/**
		 * Toggle class for changing builder fields styles when the Lead Forms is enabled.
		 *
		 * @since 1.0.0
		 */
		toggleBuilderClass: function() {

			el.$builder.toggleClass( 'wpforms-lead-forms-preview', app.isLeadFormsEnabled() );
		},

		/**
		 * Get validation error message.
		 *
		 * @since 1.0.0
		 *
		 * @returns {string} Message.
		 */
		getValidationErrorMessage: function() {

			// Whether the Conversational Forms is enabled.
			if ( $( '#wpforms-panel-field-settings-form_pages_enable' ).is( ':checked' ) ) {
				return wpforms_lead_forms_admin_builder.form_pages_alert;
			}

			// Whether the Form Pages is enabled.
			if ( $( '#wpforms-panel-field-settings-conversational_forms_enable' ).is( ':checked' ) ) {
				return wpforms_lead_forms_admin_builder.conversational_forms_alert;
			}

			// Whether the Layout field is used.
			if ( $( '#wpforms-field-options .wpforms-field-option-layout' ).length ) {
				return wpforms_lead_forms_admin_builder.field_alert.layout;
			}

			// Whether the Entry field is used.
			if ( $( '#wpforms-field-options .wpforms-field-option-entry-preview' ).length ) {
				return wpforms_lead_forms_admin_builder.field_alert['entry-preview'];
			}

			return '';
		},

		/**
		 * Show error popup.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} message Popup content.
		 * @param {string} type Popup type.
		 */
		showModal: function( message, type ) {

			const color = type === 'error' ? 'red' : 'orange';

			$.alert( {
				title: wpforms_builder.heads_up,
				content: message,
				icon: 'fa fa-info-circle',
				type: color,
				buttons: {
					confirm: {
						text: wpforms_builder.close,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * Revisions builder tab related code.
		 *
		 * @since 1.0.0
		 */
		revisionsPanel: {

			/**
			 * Switch panel without reload.
			 *
			 * @since 1.0.0
			 *
			 * @param {Event} e Event.
			 */
			switchOn: function( e ) {

				e.preventDefault();
				$( '.jconfirm' ).remove();
				WPFormsBuilder.panelSwitch( 'revisions' );
				app.turnOffLeadForms();
			},
		},

		/**
		 * Fields builder tab related code.
		 *
		 * @since 1.0.0
		 */
		fieldsPanel: {

			/**
			 * Switch panel without reload.
			 *
			 * @since 1.0.0
			 *
			 * @param {Event} e Event.
			 */
			switchOn: function( e ) {

				e.preventDefault();
				WPFormsBuilder.fieldTabToggle( 'add-fields' );
				WPFormsBuilder.panelSwitch( 'fields' );
			},

			/**
			 * Show modal if user can't add the field.
			 *
			 * @since 1.0.0
			 *
			 * @param {object} e    Event object.
			 * @param {string} type Field type.
			 * @param {object} ui   UI sortable object.
			 */
			addBlockedFieldModal: function( e, type, ui ) {

				if ( ! vars.blockedFields.includes( type ) ) {
					return;
				}

				if ( ! app.isLeadFormsEnabled() ) {
					return;
				}

				const errorMessage = wpforms_lead_forms_admin_builder.field_alert[type] ?
					wpforms_lead_forms_admin_builder.field_alert[type] :
					wpforms_lead_forms_admin_builder.field_alert.default;

				e.preventDefault();

				$.confirm( {
					title: wpforms_builder.heads_up,
					content: errorMessage,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_builder.ok,
							btnClass: 'btn-confirm',
							keys: [ 'enter' ],
						},
					},
				} );
			},

			/**
			 * Show modal if user can't select an option.
			 *
			 * @since 1.0.0
			 */
			changeOptionBlockedModal: function() {

				if ( ! app.isLeadFormsEnabled() ) {
					return;
				}

				$.confirm( {
					title: wpforms_builder.heads_up,
					content: wpforms_lead_forms_admin_builder.disabled_option,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_builder.ok,
							btnClass: 'btn-confirm',
							keys: [ 'enter' ],
						},
					},
				} );
			},

			/**
			 * Force some field options that are required for the addon.
			 *
			 * @since 1.0.0
			 */
			forceOptionsValues: function() {

				if ( ! app.isLeadFormsEnabled() ) {
					return;
				}

				app.fieldsPanel.forceAccentColor();
				app.fieldsPanel.forceInlineLayout();
				app.fieldsPanel.forceModernStyles();
				app.fieldsPanel.forceDefaultStyle();
				app.fieldsPanel.forceLargeSize();
				app.fieldsPanel.forceDefaultPositionNextBtn();
				app.fieldsPanel.forceScrollAnimation();
			},

			/**
			 * Force inline layout for input columns.
			 *
			 * @since 1.0.0
			 */
			forceInlineLayout: function() {

				if ( ! app.isLeadFormsEnabled() ) {
					return;
				}

				const selects = $( '.wpforms-field-option-row-input_columns' ).find( 'select' );

				selects.each( function() {

					const $this = $( this );

					if ( $this.val() !== 'inline' ) {
						$this.val( 'inline' ).trigger( 'change' );
					}
				} );
			},

			/**
			 * Force accent color to some color options.
			 *
			 * @since 1.0.0
			 */
			forceAccentColor: function() {

				const $rows        = $( '.wpforms-field-option-row-icon_color, .wpforms-field-option-row-choices_icons_color, .wpforms-field-option-row-indicator_color, .wpforms-field-option-row-ink_color' );
				const $accentColor = $( '#wpforms-panel-field-lead_forms-accent_color' );

				if ( ! $accentColor.length ) {
					return;
				}

				const accentColor = WPFormsBuilder.getValidColorPickerValue( $accentColor );

				$rows.each( function() {

					const $this  = $( this );
					const $field = $this.find( '.wpforms-color-picker' );

					if ( $field.hasClass( 'minicolors-input' ) && $field.data( 'minicolors-initialized' ) ) {
						$field.minicolors( 'value', accentColor );
					} else {
						$field.val( accentColor );
					}
				} );
			},

			/**
			 * Force set all field styles to modern.
			 *
			 * @since 1.0.0
			 */
			forceModernStyles: function() {

				const $selects = $( '.wpforms-field-option-row-style, .wpforms-field-option-row-choices_images_style' ).find( 'select' );

				$selects.each( function() {

					const $this = $( this );

					if ( $this.val() !== 'modern' && $this.find( 'option[value="modern"]' ).length > 0 ) {
						$this.val( 'modern' ).trigger( 'change' );
					}
				} );
			},

			/**
			 * Force default style for Icon choices.
			 *
			 * @since 1.0.0
			 */
			forceDefaultStyle: function() {

				const $selects = $( '.wpforms-field-option-row-choices_icons_style' ).find( 'select' );

				$selects.each( function() {

					const $this = $( this );

					if ( $this.val() !== 'default' ) {
						$this.val( 'default' ).trigger( 'change' );
					}
				} );
			},

			/**
			 * Force large field size for all fields.
			 *
			 * @since 1.0.0
			 */
			forceLargeSize: function() {

				// Exclude Textarea and Rich text fields options.
				const $selects = $( '.wpforms-field-option:not(.wpforms-field-option-textarea):not(.wpforms-field-option-richtext) .wpforms-field-option-row-size select' );

				$selects.each( function() {

					const $this = $( this );

					$this.val( 'large' ).trigger( 'change' );
				} );
			},

			/**
			 * Force Page Navigation Alignment to be set to left (default) value.
			 *
			 * @since 1.0.0
			 */
			forceDefaultPositionNextBtn: function() {

				const $selects = $( '.wpforms-field-option-row-nav_align select' );

				$selects.each( function() {

					const $this = $( this );

					$this.val( 'left' ).trigger( 'change' );
				} );
			},

			/**
			 * Force scroll animation setting for Progress Bar.
			 *
			 * @since 1.1.0
			 */
			forceScrollAnimation: function() {

				const isDisabled   = ! el.$scrollAnimationToggle.prop( 'checked' );
				const scrollOption = $( '.wpforms-field-option-row-scroll_disabled' ).find( 'input' );

				if ( scrollOption.length ) {
					scrollOption.prop( 'checked', isDisabled ).trigger( 'change' );
				}
			},

			/**
			 * Remove Previous buttons for LF mode.
			 *
			 * @since 1.0.0
			 */
			removePreviousButton: function() {

				$( '.wpforms-field-option-row-prev_toggle' ).find( 'input' ).prop( 'checked', false ).trigger( 'change' );
			},

			/**
			 * Lock or unlock blocked options for Lead Forms addon.
			 *
			 * @since 1.0.0
			 */
			toggleOptionsAvailability() {
				const isEnabled = app.isLeadFormsEnabled();

				vars.blockedOptions.forEach( function( option ) {
					// Exclude Textarea and Rich text fields options.
					const $row = $( '.wpforms-field-option:not(.wpforms-field-option-textarea):not(.wpforms-field-option-richtext) .wpforms-field-option-row-' + option );
					const $options = $row.find( 'select, input, label' );

					$options.each( function() {
						const $option = $( this );
						const fieldId = $option.closest( '.wpforms-field-option' ).data( 'field-id' );
						const $field = $( `#wpforms-field-${ fieldId }` );
						const isFieldInColumn = $field.closest( '.wpforms-layout-column' ).length;

						if ( ! isFieldInColumn ) {
							$option.toggleClass( 'wpforms-disabled', isEnabled );
						}
					} );

					$row.toggleClass( 'wpforms-lead-forms-disabled-option', isEnabled );
				} );
			},

			/**
			 * Render informational box.
			 *
			 * @since 1.0.0
			 */
			addInternalInformationField: function() {

				if ( el.$informationFieldRef.val() !== '' && $( '#wpforms-field-' + el.$informationFieldRef.val() ).length ) {
					return;
				}

				const options = {
					position: app.fieldsPanel.hasPageBreak() ? 1 : 0,
					scroll: false,
					defaults: {
						label: wpforms_lead_forms_admin_builder.internal_field_label,
						description: 'placeholder',
						'expanded-description': 'placeholder',
						addon: 'lead-forms',
					},
				};

				WPFormsBuilder.fieldAdd( 'internal-information', options ).done( function( res ) {

					const fieldId = res.data.field.id;

					el.$informationFieldRef.val( fieldId );

					$( '#wpforms-field-option-' + fieldId + '-description' ).val( wpforms_lead_forms_admin_builder.internal_field_description ).trigger( 'input' );
					$( '#wpforms-field-option-' + fieldId + '-expanded-description' ).val( wpforms_lead_forms_admin_builder.internal_field_extended_description ).trigger( 'input' );
					WPFormsBuilder.fieldTabToggle( 'add-fields' );
				} );
			},

			/**
			 * Add page breaks after switching to Lead forms.
			 *
			 * @since 1.0.0
			 */
			addPageBreaks: function() {

				const fieldIndex = app.fieldsPanel.getFieldIndexWithoutPageBreak();

				if ( fieldIndex === false ) {
					app.fieldsPanel.forceOptionsValues();
					app.loader.hideLoadingOverlay();
					app.fieldsPanel.addInternalInformationField();

					return;
				}

				if ( app.fieldsPanel.hasPageBreak() ) {
					WPFormsBuilder.fieldAdd( 'pagebreak', { 'position': fieldIndex } ).done( function( res ) {

						app.loader.updateProgressBar();
						app.fieldsPanel.addPageBreaks();
					} );

					return;
				}

				// The first page break adds three fields we should wait for. For it, we check
				// these fields every 100 milliseconds and process the script further.
				WPFormsBuilder.fieldAdd( 'pagebreak', { 'position': fieldIndex + 1 } );

				const checkExist = setInterval( function() {

					if ( app.fieldsPanel.hasPageBreak() ) {

						clearInterval( checkExist );

						app.loader.updateProgressBar();
						app.fieldsPanel.addPageBreaks();
					}
				}, 100 );
			},

			/**
			 * Whether the form has at least one page break.
			 * The first page break consist of 3 elements.
			 *
			 * @since 1.0.0
			 *
			 * @returns {boolean} If the form has at least one page break field.
			 */
			hasPageBreak: function() {

				// These options are: field ID when it exists, false when the fields are missing, true when the fields are adding.
				return ! isNaN( WPFormsBuilder.settings.pagebreakTop ) &&
					WPFormsBuilder.settings.pagebreakTop > 0 &&
					! isNaN( WPFormsBuilder.settings.pagebreakBottom ) &&
					WPFormsBuilder.settings.pagebreakBottom > 0;
			},

			/**
			 * Get field index without own page.
			 *
			 * @since 1.0.0
			 *
			 * @returns {int|boolean} Field index without page break before.
			 */
			getFieldIndexWithoutPageBreak: function() {

				const fieldSelector = '#wpforms-panel-fields .wpforms-field-wrap div[data-field-id]';
				const $fields       = $( fieldSelector );
				let fieldIndex      = false;
				let skipNext        = false;

				$fields.each( function() {

					const $this     = $( this );
					const fieldType = $this.data( 'field-type' );

					// If skipNext is true that means the previous field was a page break.
					if ( skipNext ) {
						// If the current field is not divider or internal information field, then we shouldn't skip the next field.
						if ( ! [ 'divider', 'internal-information' ].includes( fieldType ) ) {
							skipNext = false;
						}

						// Skip current iteration.
						// Remember that the current field is not a page break. If the next field is not a page break, ignored or
						// blocked then we get the index of the current field.
						return;
					}

					// Skip next field if the current field is a page break.
					if ( [ 'pagebreak' ].includes( fieldType ) ) {
						skipNext = true;

						return;
					}

					// Skip current field if it is blocked or ignored, don't change skipNext value.
					if ( vars.blockedFields.includes( fieldType ) || vars.ignoredFields.includes( fieldType ) ) {
						return;
					}

					// Get index of the current field.
					fieldIndex = $this.index( fieldSelector );

					return false;
				} );

				// If after the loop fieldIndex is still false, then we should finish the process.
				return fieldIndex;
			},
		},

		/**
		 * Loader related code.
		 *
		 * @since 1.0.0
		 */
		loader: {

			/**
			 * Add and display loading overlay.
			 *
			 * @since 1.0.0
			 */
			showLoadingOverlay: function() {

				const loaderTmpl = wp.template( 'wpforms-lead-forms-loader-template' );

				$( '#wpforms-builder' ).append( loaderTmpl( {
					total: app.loader.getTotal(),
				} ) );

				$( '#wpforms-builder-lead-forms-overlay' ).removeClass( 'fade-out' ).show();
			},

			/**
			 * Hide and remove loading overlay.
			 *
			 * @since 1.0.0
			 */
			hideLoadingOverlay: function() {

				const $overlay = $( '#wpforms-builder-lead-forms-overlay' );

				$overlay.addClass( 'fade-out' );

				setTimeout( function() {

					$overlay.hide( 250, function() {

						$overlay.remove();
					} );
				}, 250 );
			},

			/**
			 * Calculate fields that require page break before.
			 *
			 * @since 1.0.0
			 *
			 * @returns {number} Amount of page break fields we have to add for the form.
			 */
			getTotal: function() {

				const $fields = $( '#wpforms-panel-fields .wpforms-field-wrap div[data-field-id]' );
				let total     = 0;
				let skipNext  = false;

				$fields.each( function() {

					const $this     = $( this );
					const fieldType = $this.data( 'field-type' );

					if ( skipNext ) {
						skipNext = false;

						return;
					}

					if ( [ 'divider', 'internal-information' ].includes( fieldType ) ) {
						skipNext = true;

						total++;
						return;
					}

					if ( [ 'pagebreak' ].includes( fieldType ) ) {
						skipNext = true;

						return;
					}

					if ( vars.blockedFields.includes( fieldType ) || vars.ignoredFields.includes( fieldType ) ) {
						return;
					}

					total++;
				} );

				return total;
			},

			/**
			 * Update progress bar.
			 *
			 * @since 1.0.0
			 *
			 * @returns {void}
			 */
			updateProgressBar: function() {

				const $current      = $( '.wpforms-lead-forms-progress-indicator-steps-current' );
				const $total        = $( '.wpforms-lead-forms-progress-indicator-steps-all' );
				const $progress     = $( '.wpforms-lead-forms-page-progress' );
				const currentValue  = +$current.text();
				const totalValue    = +$total.text();
				const resultValue   = currentValue + 1;
				const progressValue = ( resultValue / totalValue * 100 ).toFixed( 2 );

				wpf.debug( resultValue + ' / ' + totalValue );

				$progress.css( 'width', progressValue + '%' );
				$current.text( resultValue );
			},
		},

		/**
		 * Import related code.
		 *
		 * @since 1.0.0
		 */
		import: {

			/**
			 * Import style settings.
			 *
			 * @since 1.0.0
			 *
			 * @param {Event} e Event.
			 */
			process: function( e ) {

				e.preventDefault();

				const $this  = $( this );
				const styles = el.$importStyles.val();

				if ( styles.length <= 0 ) {

					app.showModal( wpforms_lead_forms_admin_builder.failed_import, 'error' );

					return;
				}

				const stylesArray = styles.split( ',' );

				if ( app.import.isValidFormat() === false ) {
					return;
				}

				$.confirm( {
					title: wpforms_builder.heads_up,
					content: wpforms_lead_forms_admin_builder.import_confirmation,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_lead_forms_admin_builder.confirm_import_button,
							btnClass: 'btn-confirm',
							keys: [ 'enter' ],
							action: function() {

								vars.toggles.forEach( function( param, index ) {

									app.import.updateToggle( '#wpforms-panel-field-lead_forms-' + param, stylesArray[index] );
								} );

								vars.colors.forEach( function( param, index ) {

									app.import.updateColor( '#wpforms-panel-field-lead_forms-' + param, stylesArray[index + 3] );
								} );

								el.$importStyles.val( '' );
							},
						},
						cancel: {
							text: wpforms_builder.cancel,
							action: function() {

								$this.prop( 'checked', false );
							},
						},
					},
				} );
			},

			/**
			 * Validate import styles.
			 *
			 * @since 1.0.0
			 *
			 * @returns {boolean} Return if valid setting.
			 */
			isValidFormat: function() {

				if ( app.import.isValidToggleValues() && app.import.isValidColorValues() ) {
					return true;
				}

				app.showModal( wpforms_lead_forms_admin_builder.failed_import, 'error' );

				return false;
			},

			/**
			 * Validate checkboxes.
			 *
			 * @since 1.0.0
			 *
			 * @returns {string[]} Values.
			 */
			isValidToggleValues: function() {

				const styles      = el.$importStyles.val();
				const stylesArray = styles.split( ',' );
				const values      = stylesArray.splice( 0, 3 );

				let isValid = true;

				$.each( values, function( index, val ) {

					if ( ! [ 0, 1 ].includes( parseInt( val, 10 ) ) ) {
						isValid = false;

						return false;
					}
				} );

				return isValid;
			},

			/**
			 * Validate colors.
			 *
			 * @since 1.0.0
			 *
			 * @returns {string[]} Values.
			 */
			isValidColorValues: function() {

				const styles      = el.$importStyles.val();
				const stylesArray = styles.split( ',' );
				const values      = stylesArray.slice( 3 );

				let isValid = true;

				$.each( values, function( index, val ) {

					const reg = /^#([0-9a-f]{3}){1,2}$/i;

					if ( val !== 'transparent' && ! reg.test( val ) ) {
						isValid = false;

						return false;
					}
				} );

				return isValid;
			},

			/**
			 * Update color.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} $selector Selector.
			 * @param {string} $color    Value.
			 */
			updateColor: function( $selector, $color ) {

				$( $selector ).val( $color ).minicolors( 'value', $color );
			},

			/**
			 * Update toggle.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} $selector Selector.
			 * @param {string} $value    Value.
			 */
			updateToggle: function( $selector, $value ) {

				$( $selector ).prop( 'checked', parseInt( $value, 10 ) ).trigger( 'change' );
			},
		},

		/**
		 * Export related code.
		 *
		 * @since 1.0.0
		 */
		export: {

			/**
			 * Load styles form export.
			 *
			 * @since 1.0.0
			 */
			updateField: function() {

				if ( ! app.isLeadFormsEnabled() ) {
					return;
				}

				let exportString = '';

				vars.toggles.forEach( function( param ) {

					const value = $( '#wpforms-panel-field-lead_forms-' + param ).prop( 'checked' );

					exportString += +value + ',';
				} );

				vars.colors.forEach( function( param ) {

					const element = $( '#wpforms-panel-field-lead_forms-' + param );

					exportString += WPFormsBuilder.getValidColorPickerValue( element ) + ',';
				} );

				$( '#wpforms-panel-field-settings-lead_forms_export_your_style_settings' ).val( exportString.slice( 0, -1 ) );
			},

			/**
			 * Copies styles settings string to clipboard.
			 *
			 * @since 1.0.0
			 */
			copyStylesToClipboard: function() {

				el.$shortcodeInput
					.prop( 'disabled', false )
					.select()
					.prop( 'disabled', true );

				// Copy it.
				document.execCommand( 'copy' );

				const $icon = el.$shortcodeCopy.find( 'i' );

				// Add visual feedback to copy command.
				$icon.removeClass( 'fa-files-o' ).addClass( 'fa-check' );

				// Reset visual confirmation back to default state after 2.5 sec.
				window.setTimeout( function() {

					$icon.removeClass( 'fa-check' ).addClass( 'fa-files-o' );
				}, 2500 );
			},
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsBuilderLeadForms.init();
