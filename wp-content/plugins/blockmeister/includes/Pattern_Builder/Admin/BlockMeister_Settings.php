<?php

/**
 * Note: base settings page was generated via https://wp-skills.com/tools/settings-options-page-generator
 * Note: Retrieving values: get_option( 'blockmeister_*' )
 */
namespace ProDevign\BlockMeister\Pattern_Builder\Admin;

use function ProDevign\BlockMeister\blockmeister_license;
class BlockMeister_Settings {
    public function __construct() {
    }

    public function init() {
        add_action( 'admin_menu', array($this, 'create_settings') );
        add_action( 'admin_init', array($this, 'setup_sections') );
        add_action( 'admin_init', array($this, 'setup_fields') );
    }

    public function create_settings() {
        $page_title = 'BlockMeister Settings';
        $menu_title = esc_html__( 'Settings', 'blockmeister' );
        $capability = 'manage_options';
        $slug = 'Settings';
        $callback = array($this, 'settings_content');
        add_submenu_page(
            'edit.php?post_type=blockmeister_pattern',
            $page_title,
            $menu_title,
            $capability,
            $slug,
            $callback
        );
    }

    public function settings_content() {
        ?>
        <div class="wrap">
            <h1><?php 
        esc_html_e( 'Settings', 'blockmeister' );
        ?></h1>
			<?php 
        settings_errors();
        ?>
            <form method="POST" action="options.php">
				<?php 
        settings_fields( 'Settings' );
        do_settings_sections( 'Settings' );
        submit_button();
        ?>
            </form>
        </div> <?php 
    }

    public function setup_sections() {
        add_settings_section(
            'uninstall_settings_section',
            esc_html__( 'BlockMeister Plugin Uninstall Settings', 'blockmeister' ),
            array(),
            'Settings'
        );
    }

    public function setup_fields() {
        $fields = [[
            'section' => 'uninstall_settings_section',
            'label'   => esc_html__( 'Delete all data when I uninstall this plugin', 'blockmeister' ),
            'id'      => 'blockmeister_delete_data_on_uninstall',
            'desc'    => esc_html__( 'Be aware: if selected, when BlockMeister is being uninstalled, all data created through BlockMeister will be deleted, definitely! This does not affect block patterns inserted in posts or pages though.', 'blockmeister' ),
            'type'    => 'checkbox',
            'default' => '',
        ]];
        foreach ( $fields as $field ) {
            add_settings_field(
                $field['id'],
                $field['label'],
                array($this, 'field_callback'),
                'Settings',
                $field['section'],
                $field
            );
            register_setting( 'Settings', $field['id'] );
        }
    }

    public function field_callback( $field ) {
        $value = get_option( $field['id'], $field['default'] );
        $placeholder = '';
        if ( isset( $field['placeholder'] ) ) {
            $placeholder = $field['placeholder'];
        }
        switch ( $field['type'] ) {
            case 'checkbox':
                printf( '<input %1$s id="%2$s" name="%2$s" type="checkbox" value="1">', ( $value === '1' ? 'checked' : '' ), $field['id'] );
                break;
            case 'number':
                printf(
                    '<input name="%1$s" id="%1$s" type="number" min="%2$s" max="%3$s" value="%4$s" />',
                    $field['id'],
                    $field['min'],
                    $field['max'],
                    $value
                );
                break;
            default:
                printf(
                    '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
                    $field['id'],
                    $field['type'],
                    $placeholder,
                    $value
                );
        }
        if ( isset( $field['desc'] ) ) {
            if ( $desc = $field['desc'] ) {
                printf( '&nbsp;<label class="description" for="%s">%s </label>', $field['id'], $desc );
            }
        }
    }

}
