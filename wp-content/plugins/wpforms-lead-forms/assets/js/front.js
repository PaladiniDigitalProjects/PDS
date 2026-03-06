'use strict';

/**
 * WPForms Lead Forms frontend part.
 *
 * @since 1.0.0
 */
const WPFormsLeadForms = window.WPFormsLeadForms || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	const app = {

		/**
		 * List of frontend selectors.
		 *
		 * @since 1.0.0
		 */
		selectors: {
			container:      '.wpforms-lead-forms-container',
			progressBar:    '.wpforms-lead-forms-progress-bar',
			navButtons:     '.wpforms-page-button',
			title:          '.wpforms-lead-forms-title',
			dropdownFields: 'select:not(.choicesjs-select):not([multiple])',
			rangeFields:    'input[type=range]',
		},

		/**
		 * Progress bar related code.
		 *
		 * @since 1.0.0
		 */
		progressBar: {

			/**
			 * Init.
			 *
			 * @since 1.0.0
			 */
			init: function() {

				$( app.selectors.container )
					.on( 'wpformsPageChange', app.selectors.navButtons, app.progressBar.changePage );
			},

			/**
			 * Update progress bar value.
			 *
			 * @since 1.0.0
			 *
			 * @param {jQuery} $progressBar Progress bar element.
			 * @param {int} stage Current stage.
			 */
			update: function( $progressBar, stage ) {

				const total = +$progressBar.attr( 'aria-valuemax' );
				const width = ( stage / total * 100 ).toFixed( 2 ) + '%';

				$progressBar.attr( 'aria-valuenow', stage ).width( width );
			},

			/**
			 * Updating progress bar on page changing.
			 *
			 * @since 1.0.0
			 *
			 * @param {Event} event Event.
			 * @param {int} currentPage Current page.
			 */
			changePage: function( event, currentPage ) {

				const $progressBar = $( this ).closest( app.selectors.container ).find( app.selectors.progressBar );

				app.progressBar.update( $progressBar, currentPage );
			},
		},

		/**
		 * Form title related code.
		 *
		 * @since 1.0.0
		 */
		formTitle: {

			/**
			 * Init.
			 *
			 * @since 1.0.0
			 */
			init: function() {

				$( app.selectors.container )
					.on( 'wpformsPageChange', app.selectors.navButtons, app.formTitle.hide );
			},

			/**
			 * Hide form title.
			 *
			 * @since 1.0.0
			 */
			hide: function() {

				$( app.selectors.title ).hide();
			},
		},

		/**
		 * Range fields related code.
		 *
		 * @since 1.0.0
		 */
		rangeFields: {

			/**
			 * Init.
			 *
			 * @since 1.0.0
			 */
			init: function() {

				$( app.selectors.container )
					.on( 'input', app.selectors.rangeFields, app.rangeFields.change );
			},

			/**
			 * Change range field.
			 *
			 * @since 1.0.0
			 */
			change: function() {

				const $this = $( this );
				const $form = $this.closest( app.selectors.container );
				const value = $this.val();
				const min = $this.attr( 'min' );
				const max = $this.attr( 'max' );
				const position = ( value - min ) / ( max - min ) * 100;
				const accentColor = getComputedStyle( $form.get( 0 ) ).getPropertyValue( '--wpforms-lead-forms-accent-color' );
				const gradientPattern = 'linear-gradient(to right, {accent} 0%, {accent} {position}%, {accent020} {position}%, {accent020} 100%)';

				const background = gradientPattern
					.replaceAll( '{accent}', 'rgba(' + accentColor + ', 1 )' )
					.replaceAll( '{accent020}', 'rgba(' + accentColor + ', 0.2 )' )
					.replaceAll( '{position}', position.toString() );

				$this.css( 'background', background );
			},
		},

		/**
		 * CSS variables related code.
		 *
		 * @since 1.0.0
		 */
		CSSVariables: {

			/**
			 * Update form variables.
			 *
			 * @since 1.0.0
			 *
			 * @param {int} index Form index.
			 * @param {Element} formContainer Form DOM element.
			 */
			updateFormVariables: function( index, formContainer ) {

				const background = window.getComputedStyle( formContainer ).getPropertyValue( '--wpforms-lead-forms-container-background' ).toString().trim();

				if ( background !== 'transparent' ) {
					return;
				}

				const bgColor = app.CSSVariables.getElementBackgroundColor( formContainer.parentElement );

				formContainer.style.setProperty( '--wpforms-lead-forms-container-background-color', bgColor );
			},

			/**
			 * Get recursively background element.
			 *
			 * @since 1.0.0
			 *
			 * @param {Element} el DOM element.
			 *
			 * @returns {string} Background color of the element.
			 */
			getElementBackgroundColor: function( el ) {

				const bgColor = window.getComputedStyle( el ).backgroundColor;

				if ( bgColor !== 'rgba(0, 0, 0, 0)' ) {
					return bgColor;
				}

				return app.CSSVariables.getElementBackgroundColor( el.parentElement );
			},
		},

		/**
		 * Dropdowns related code.
		 *
		 * @since 1.0.0
		 */
		dropdownFields: {

			/**
			 * Update form variables.
			 *
			 * @since 1.0.0
			 *
			 * @param {int} index Form index.
			 * @param {Element} field Form DOM element.
			 */
			init: function( index, field ) {

				const $field = $( field );
				const $selected = $( field ).find( 'option[selected]' );
				let classes = 'wpforms-lead-forms-select';

				if ( $selected.length && $selected.hasClass( 'placeholder' ) ) {
					classes += ' wpforms-lead-forms-select-placeholder';
				}

				$field.wrap( '<div class="' + classes + '"/>' );

				$field.on( 'change', app.dropdownFields.change );
			},

			/**
			 * Change dropdown event.
			 *
			 * @since 1.0.0
			 */
			change: function() {

				const placeholderClass = 'wpforms-lead-forms-select-placeholder';
				const $wrapper = $( this ).closest( '.wpforms-lead-forms-select' );

				if ( $wrapper.hasClass( placeholderClass ) ) {
					$wrapper.removeClass( placeholderClass );
				}
			},
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( app.ready );

			$( window ).on( 'load', function() {

				// In the case of jQuery 3.+, we need to wait for a ready event first.
				if ( typeof $.ready.then === 'function' ) {
					$.ready.then( app.load );

					return;
				}

				app.load();
			} );
		},

		/**
		 * Initialized once the DOM is fully loaded.
		 *
		 * @since 1.0.0
		 */
		ready() {
			// Elementor plugin popup template compatibility.
			$( document )
				.on( 'wpforms_elementor_form_fields_initialized', app.progressBar.init );

			app.formTitle.init();
			app.progressBar.init();
			app.dropdownFields.init();
			app.rangeFields.init();
		},

		/**
		 * Page load.
		 *
		 * @since 1.0.0
		 */
		load: function() {

			$( app.selectors.container ).each( app.CSSVariables.updateFormVariables );
			$( app.selectors.container ).find( app.selectors.dropdownFields ).each( app.dropdownFields.init );
		},
	};

	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsLeadForms.init();
