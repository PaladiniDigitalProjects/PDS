<?php

namespace WPFormsLeadForms;

use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement

/**
 * Class Integrations.
 *
 * @since 1.0.0
 */
class Integrations {

	/**
	 * A name of `wp_register_styles` handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private const HANDLE = 'wpforms-lead-forms';

	/**
	 * Init hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {

		// Set editor style for block type editor. Must run at priority 20 in add-ons.
		add_filter( 'register_block_type_args', [ $this, 'register_block_type_args' ], 20, 2 );

		// Elementor.
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'elementor_enqueue_styles' ] );

		// Divi.
		add_action( 'wp_enqueue_scripts', [ $this, 'divi_enqueue_styles' ], 12 );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	private function enqueue_styles(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE,
			WPFORMS_LEAD_FORMS_URL . "assets/css/front{$min}.css",
			[],
			WPFORMS_LEAD_FORMS_VERSION
		);
	}

	/**
	 * Enqueue styles for the Gutenberg editor.
	 *
	 * @since 1.0.0
	 * @deprecated 1.4.0
	 */
	public function gutenberg_enqueue_styles(): void {

		_deprecated_function( __METHOD__, '1.4.0 of the WPForms Lead Forms addon.' );

		$this->enqueue_styles();
	}

	/**
	 * Set editor style for block type editor.
	 *
	 * @since 1.0.0
	 *
	 * @see   FileUploadField::register_block_type_args
	 *
	 * @param array|mixed $args       Array of arguments for registering a block type.
	 * @param string      $block_type Block type name including namespace.
	 */
	public function register_block_type_args( $args, $block_type ) {

		if ( $block_type !== 'wpforms/form-selector' || ! is_admin() ) {
			return $args;
		}

		$min = wpforms_get_min_suffix();

		wp_register_style(
			self::HANDLE,
			WPFORMS_LEAD_FORMS_URL . "assets/css/front{$min}.css",
			[ $args['editor_style'] ],
			WPFORMS_LEAD_FORMS_VERSION
		);

		$args['editor_style'] = self::HANDLE;

		return $args;
	}

	/**
	 * Register styles for the Gutenberg editor.
	 *
	 * @since 1.0.0
	 */
	public function register_gutenberg_styles(): void {

		$min = wpforms_get_min_suffix();

		// CSS.
		wp_register_style(
			self::HANDLE,
			WPFORMS_LEAD_FORMS_URL . "assets/css/front{$min}.css",
			[],
			WPFORMS_LEAD_FORMS_VERSION
		);
	}

	/**
	 * Enqueue styles for the Elementor Builder.
	 *
	 * @since 1.0.0
	 */
	public function elementor_enqueue_styles(): void {

		$this->enqueue_styles();
	}

	/**
	 * Enqueue styles for the Divi Builder.
	 *
	 * @since 1.0.0
	 */
	public function divi_enqueue_styles(): void {

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['et_fb'] ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-lead-forms-integrations',
			WPFORMS_LEAD_FORMS_URL . "assets/css/integrations/front{$min}.css",
			[],
			WPFORMS_LEAD_FORMS_VERSION
		);
	}
}
