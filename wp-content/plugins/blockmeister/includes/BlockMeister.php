<?php

namespace ProDevign\BlockMeister;

use  ProDevign\BlockMeister\Pattern_Builder\Pattern_Builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BlockMeister {

	/**
	 * Plugin version
	 */
	const  VERSION = '3.1.12';

	/**
	 * The minimum PHP version that is required to run this plugin
	 */
	const  REQUIRED_PHP_VERSION = '5.6';

	/**
	 * The minimum WordPress version that is required to run this plugin
	 */
	const  REQUIRED_WP_VERSION = '6.0';

	/**
	 * Full plugin path and filename
	 */
	private static $plugin_full_path;

	/**
	 * dir and filename
	 */
	private static $plugin_basename;

	/**
	 * base dir
	 */
	private static $plugin_basedir;

	/**
	 * Full plugin path (without filename)
	 */
	private static $plugin_dir;

	/**
	 * @var BlockMeister
	 */
	private static $instance = null;

	/**
	 * @var string
	 */
	private static $default_block_namespace;

	/**
	 * Constructor for the BlockMeister class
	 */
	private function __construct() {
	}

	/**
	 * Runs (and initializes) BlockMeister
	 *
	 * @param $plugin_full_path string The full dir/file path to this plugin.
	 *
	 * @return void
	 */
	public static function init( $plugin_full_path ) {

		if ( is_null( BlockMeister::$instance ) ) {
			$blockmeister      = BlockMeister::$instance = new BlockMeister();
			self::$plugin_full_path = $plugin_full_path;
			self::$plugin_dir  = dirname( $plugin_full_path );
			self::$plugin_basename = plugin_basename ( $plugin_full_path );
			self::$plugin_basedir = dirname ( self::$plugin_basename );


			//register_activation_hook( self::$plugin_basename, [ new Activator(), 'activate' ] );
			blockmeister_license()->add_action( 'after_uninstall', [
				Uninstaller::class,
				'uninstall'
			] ); // required by Freemius so it can add to hook to the uninstall hook

			add_action( 'plugins_loaded', function () {
				$plugin_languages_path =  self::$plugin_basedir . '/languages/';
				load_plugin_textdomain( 'blockmeister', false, $plugin_languages_path );
			} );

			if ( $blockmeister->meets_requirements() ) {
				Pattern_Builder::init();
			}
		}

	}

	/**
	 * Check for the required versions of WP an PHP
	 */
	private function meets_requirements() {

		// check PHP version
		if ( version_compare( phpversion(), self::REQUIRED_PHP_VERSION, '<' ) ) {
			$notice = wp_sprintf( 'BlockMeister requires PHP %s or higher to run.', self::REQUIRED_PHP_VERSION );
			$this->add_admin_error_notice_on_plugins_page( $notice );

			return false;
		}

		// check WP version
		if ( version_compare( $GLOBALS['wp_version'], self::REQUIRED_WP_VERSION, '<' ) ) {
			$notice = wp_sprintf( 'BlockMeister requires WordPress %s or later to run.', self::REQUIRED_WP_VERSION );
			$this->add_admin_error_notice_on_plugins_page( $notice );

			return false;
		}

		return true;
	}

	private function add_admin_error_notice_on_plugins_page( $notice ) {
		global $pagenow;
		if ( 'plugins.php' === $pagenow ) {
			add_action( 'admin_notices', function () use ( $notice ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( $notice ) . '</p></div>';
			} );
		}
	}

	public static function get_default_block_namespace() {
		if ( ! self::$default_block_namespace ) {
			$site_name                     = get_bloginfo( 'name', 'display' ); // note: is escaped by WP
			$site_slug                     = sanitize_title( $site_name ); // convert name to a valid slug
			self::$default_block_namespace = $site_slug;
		}

		return self::$default_block_namespace;
	}

	/**
	 * @return mixed E.g. 'my-plugin/my-plugin.php'
	 */
	public static function get_plugin_file() {
		return self::$plugin_basename;
	}

	public static function get_build_path() {
		return self::get_path( 'build' );
	}

	public static function get_path( $sub_path = "" ) {
		return wp_normalize_path( self::$plugin_dir . '/' . ( ( ! empty( $sub_path ) ? $sub_path . '/' : '' ) ) );
	}

	public static function get_languages_path() {
		return self::get_path( 'languages' );
	}

	public static function get_includes_path() {
		return self::get_path( 'includes' );
	}

	public static function get_build_url() {
		return self::get_url( 'build' );
	}

	public static function get_url( $sub_path = "" ) {
		return plugins_url( $sub_path, self::$plugin_full_path ) . '/';
	}

} // BlockMeister