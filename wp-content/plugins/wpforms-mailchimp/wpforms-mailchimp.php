<?php
/**
 * Plugin Name:       WPForms Mailchimp
 * Plugin URI:        https://wpforms.com
 * Description:       Mailchimp integration with WPForms.
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           2.5.1
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpforms-mailchimp
 * Domain Path:       /languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

use WPFormsMailchimp\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @since 2.4.0
 */
const WPFORMS_MAILCHIMP_VERSION = '2.5.1';

/**
 * Plugin file.
 *
 * @since 2.4.0
 */
const WPFORMS_MAILCHIMP_FILE = __FILE__;

/**
 * Plugin URL.
 *
 * @since 2.4.0
 */
define( 'WPFORMS_MAILCHIMP_URL', plugin_dir_url( WPFORMS_MAILCHIMP_FILE ) );

/**
 * Plugin path.
 *
 * @since 2.4.0
 */
define( 'WPFORMS_MAILCHIMP_DIR', plugin_dir_path( WPFORMS_MAILCHIMP_FILE ) );

/**
 * Check addon requirements.
 *
 * @since 1.0.0
 * @since 2.4.0 Uses requirements feature.
 */
function wpforms_mailchimp_load() {

	$requirements = [
		'file'    => WPFORMS_MAILCHIMP_FILE,
		'wpforms' => '1.9.6',
		'ext'     => 'curl',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_mailchimp();
}

add_action( 'wpforms_loaded', 'wpforms_mailchimp_load' );

/**
 * Get the instance of the addon main class.
 *
 * @since 2.0.0
 *
 * @return Plugin
 */
function wpforms_mailchimp() {

	// Load the Mailchimp addon.
	require_once WPFORMS_MAILCHIMP_DIR . '/vendor/autoload.php';

	// Get all active integrations.
	$providers = wpforms_get_providers_options();

	// Load v2 API integration if the user currently has it set up.
	if ( ! empty( $providers['mailchimp'] ) ) {
		require_once WPFORMS_MAILCHIMP_DIR . 'deprecated/class-mailchimp.php';
	}

	return Plugin::get_instance();
}
