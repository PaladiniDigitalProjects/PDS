<?php

/**
 * Plugin Name: BlockMeister
 * Plugin URI: https://wpblockmeister.com/
 * Description: Block pattern builder. Lets you easily and visually create custom block patterns.
 * Version: 3.1.12
 * Requires at least: 6.0
 * Requires PHP: 5.6
 * Author: BlockMeister
 * Author URI: https://wpblockmeister.com/
 * License: GNU General Public License v2 or later
 * License URI: https://opensource.org/licenses/GPL-2.0
 * Text Domain: blockmeister
 * Domain Path: /languages
 *
 * @package    BlockMeister
 * @author     ProDevign <info@prodevign.com>
 * @copyright  Copyright © 2020 ProDevign.
 * @link       https://wpblockmeister.com
 * @license    https://opensource.org/licenses/GPL-2.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */
namespace ProDevign\BlockMeister;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Register autoloader for classes in our package:
spl_autoload_register( function ( $class ) {
    $is_blockmeister_class = substr( $class, 0, strlen( __NAMESPACE__ ) ) === __NAMESPACE__;
    if ( $is_blockmeister_class ) {
        $class_sans_ns = str_replace( __NAMESPACE__, '', $class );
        $class_sans_ns_with_forward_slashes = str_replace( '\\', '/', $class_sans_ns );
        $class_file = $class_sans_ns_with_forward_slashes . '.php';
        $class_path = __DIR__ . '/includes' . $class_file;
        include $class_path;
    }
} );
// Add activation hook before the freemius one:
register_activation_hook( plugin_basename( __FILE__ ), [new Activator(), 'activate'] );
// setup freemius licensing SDK:
if ( !function_exists( 'ProDevign\\BlockMeister\\blockmeister_license' ) ) {
    // Create a helper function for easy SDK access.
    function blockmeister_license() {
        global $blockmeister_license;
        if ( !isset( $blockmeister_license ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_6729_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_6729_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $blockmeister_license = fs_dynamic_init( array(
                'id'             => '6729',
                'slug'           => 'blockmeister',
                'type'           => 'plugin',
                'public_key'     => 'pk_7a6e554f85755b24ba8e396f2a9c0',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'    => 'edit.php?post_type=blockmeister_pattern',
                    'network' => true,
                ),
                'is_live'        => true,
            ) );
        }
        return $blockmeister_license;
    }

    // Init Freemius.
    blockmeister_license();
    // Signal that SDK was initiated.
    do_action( 'blockmeister_license_loaded' );
}
/**
 * Filter blockmeister admin menu's submenus (Contact|Forum) visibility
 * Always show 'Block Patterns -> Contact Us',
 * but show 'Block Patterns -> Support Forum' only to free, not paying, not trial users
 *
 * @param $is_visible
 * @param $id
 *
 * @return bool|mixed
 */
blockmeister_license()->add_filter(
    'is_submenu_visible',
    function ( $is_visible, $id ) {
        if ( 'support' === $id ) {
            $is_free = blockmeister_license()->is_free_plan();
            $is_not_paying_and_not_trial = !blockmeister_license()->is_paying_or_trial();
            $is_visible = $is_free || $is_not_paying_and_not_trial;
        }
        return $is_visible;
    },
    10,
    2
);
// Init the plugin:
BlockMeister::init( __FILE__ );