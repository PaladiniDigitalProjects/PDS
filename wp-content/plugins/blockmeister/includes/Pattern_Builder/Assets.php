<?php

namespace ProDevign\BlockMeister\Pattern_Builder;

use ProDevign\BlockMeister\BlockMeister;
use ProDevign\BlockMeister\Context;
use ProDevign\BlockMeister\Utils;
use function ProDevign\BlockMeister\blockmeister_license;
/**
 * Scripts and Styles Class
 */
class Assets {
    private $module = "pattern-builder";

    /**
     * Assets constructor.
     */
    function __construct() {
    }

    public function init() {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles_and_scripts'], 5 );
        add_filter(
            'pre_load_script_translations',
            [$this, 'load_domain_script_translations_filter'],
            10,
            4
        );
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function enqueue_styles_and_scripts() {
        global $current_screen;
        // needs to be enqueued for *any* post type using the block editor or from within site editor
        if ( Context::is_block_editor() || Context::is_site_editor() ) {
            // enqueue free scripts:
            $this->enqueue_script( 'pattern-builder' );
            // enqueue styles:
            $this->enqueue_style( 'pattern-builder' );
        }
    }

    private function enqueue_script( $script_name ) {
        $asset_data = $this->get_asset_file_data( $script_name );
        //$handle     = "{$script_name}-js";
        $src = BlockMeister::get_build_url() . "{$this->module}/{$script_name}.js";
        wp_enqueue_script(
            $script_name,
            $src,
            $asset_data->dependencies,
            $asset_data->version,
            true
        );
        // Note:  wp_set_script_translations works in tandem with our pre_load_script_translations
        //        filter (to support multiple json translations files with divergent md5 hashes in the file name).
        wp_set_script_translations( $script_name, 'blockmeister', BlockMeister::get_languages_path() );
    }

    /**
     * WP generates the dependencies and puts them in an x.asset.php file, but for
     * some reason (bug?) it camelCases handles with dashes, this method correct that.
     *
     * @param $script_name
     *
     * @return object
     */
    private function get_asset_file_data( $script_name ) {
        $build_path = BlockMeister::get_build_path();
        $assets_file_path = $build_path . "{$this->module}/{$script_name}.asset.php";
        $assets = (include $assets_file_path);
        $dep = $assets['dependencies'];
        $dep = array_map( [Utils::class, 'camel2dashed'], $dep );
        return (object) [
            'dependencies' => $dep,
            'version'      => $assets['version'],
        ];
    }

    private function enqueue_style( $style_name ) {
        $handle = "{$style_name}";
        // note: -css is appended by wordpress
        $src = BlockMeister::get_build_url() . "{$this->module}/{$style_name}.css";
        $ver = filemtime( BlockMeister::get_build_path() . "{$this->module}/{$style_name}.css" );
        wp_enqueue_style(
            $handle,
            $src,
            [],
            $ver
        );
    }

    /**
     * Pre-filters script translations for the given file, script handle and text domain.
     * The core system can only load one json translation file based on the md5 of the src folder.
     * While in our situations there will be several json files (based on src folder) which need to be
     * loaded with a single bundled js file from the builds folder.
     * Therefore we need to load the translation files our selves via this filter method.
     *
     * @param string|false|null $translations JSON-encoded translation data. Default null.
     * @param string|false $file Path to the translation file to load. False if there isn't one.
     * @param string $handle Name of the script to register a translation domain to.
     * @param string $domain The text domain.
     *
     * @return false|string|null
     */
    public function load_domain_script_translations_filter(
        $translations,
        $file,
        $handle,
        $domain
    ) {
        if ( $domain === 'blockmeister' ) {
            $has_json_translations = false;
            $locale = determine_locale();
            $all_messages = new \stdClass();
            // load and parse all json files where the filename pattern is: '[domain]-[locale]-[md5].json':
            $files = scandir( BlockMeister::get_languages_path() );
            foreach ( $files as $file ) {
                if ( preg_match( "/^{$domain}-{$locale}-.+?\\.json\$/", $file ) === 1 ) {
                    $has_json_translations = true;
                    $translation_json = file_get_contents( BlockMeister::get_languages_path() . $file );
                    $translations = json_decode( $translation_json );
                    $messages = $translations->locale_data->messages;
                    foreach ( $messages as $key => $message ) {
                        $all_messages->{$key} = $message;
                    }
                }
            }
            if ( $has_json_translations ) {
                //$translations->domain                = $domain;
                $translations->generator = 'BlockMeister';
                $translations->source = 'multiple from src';
                $translations->locale_data->messages = $all_messages;
                $translations = wp_json_encode( $translations );
                $translations = str_replace( '_empty_', "", $translations );
                // PHP <7.1 does insert unwanted __empty__ prop for ""-key; undo that
            }
        }
        return $translations;
    }

}
