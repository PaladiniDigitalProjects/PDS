<?php

namespace ProDevign\BlockMeister\Pattern_Builder;

use ProDevign\BlockMeister\Context;
use function ProDevign\BlockMeister\blockmeister_license;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
final class Pattern_Builder {
    const POST_TYPE = 'blockmeister_pattern';

    /**
     * @var Pattern_Builder
     */
    private static $instance = null;

    /**
     * Constructor for the Pattern_Builder class
     */
    private function __construct() {
    }

    /**
     * @return Pattern_Builder
     */
    public static function get_instance() {
        return Pattern_Builder::$instance;
    }

    /**
     * Runs (and initializes) Pattern_Builder
     *
     * Note: all classes make sure their methods will only be run when needed!
     *       This is done by hooking into the relevant hooks and/or by checking the (request) context
     *
     * @return void
     */
    public static function init() {
        if ( is_null( Pattern_Builder::$instance ) ) {
            $pattern_builder = Pattern_Builder::$instance = new Pattern_Builder();
            ( new Assets() )->init();
            ( new BlockMeister_Pattern_Post_Type() )->init();
            ( new BlockMeister_Pattern_Category_Taxonomy() )->init();
            ( new BlockMeister_Pattern_Keywords_Taxonomy() )->init();
            ( new BlockMeister_Pattern_Post_Meta_Fields() )->init();
            ( new Block_Pattern_Registry() )->init();
            ( new Admin\BlockMeister_Pattern_Category_List_Table() )->init();
            ( new Admin\BlockMeister_Pattern_List_Table() )->init();
            ( new Admin\BlockMeister_Pattern_Editor() )->init();
            ( new Admin\BlockMeister_Settings() )->init();
            blockmeister_license()->add_filter(
                'show_admin_notice',
                [$pattern_builder, 'show_admin_notice_only_in_blockmeister_context_filter'],
                10,
                2
            );
        }
    }

    /**
     * Filter makes sure the Freemius admin notices are only shown on:
     * - the blockmeister_pattern list table screen
     * - the blockmeister_pattern_category list table screen
     * - the plugins list table screen.
     *
     * @param bool  $show
     * @param array $msg {
     *     @var string $message The actual message.
     *     @var string $title An optional message title.
     *     @var string $type The type of the message ('success', 'update', 'warning', 'promotion').
     *     @var string $id The unique identifier of the message.
     *     @var string $manager_id The unique identifier of the notices manager. For plugins it would be the plugin's slug, for themes - `<slug>-theme`.
     *     @var string $plugin The product's title.
     *     @var string $wp_user_id An optional WP user ID that this admin notice is for.
     * }
     * @return bool
     */
    public function show_admin_notice_only_in_blockmeister_context_filter( $show, $msg ) {
        global $pagenow;
        $is_plugin_list_table_screen = $pagenow === 'plugins.php';
        return Context::is_blockmeister_pattern_list_table() || Context::is_blockmeister_pattern_category_list_table() || $is_plugin_list_table_screen;
    }

}

// Pattern_Builder