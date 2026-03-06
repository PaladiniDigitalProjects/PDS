<?php
/*
Plugin Name: Cookie Bar
Plugin URI: https://www.brontobytes.com/blog/cookie-bar-free-wordpress-plugin/
Description: Cookie Bar allows you to discreetly inform visitors that your website uses cookies.
Author: Brontobytes
Author URI: https://www.brontobytes.com/
Version: 2.1
License: GPLv2
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

function cookie_bar_menu() {
	add_options_page('Cookie Bar Settings', 'Cookie Bar', 'administrator', 'cookie-bar-settings', 'cookie_bar_settings_page');
}
add_action('admin_menu', 'cookie_bar_menu');


add_filter( 'plugin_action_links', 'cookie_bar_settings_plugin_link', 10, 2 );

function cookie_bar_settings_plugin_link( $links, $file )
{
    if ( $file == plugin_basename(dirname(__FILE__) . '/cookie-bar.php') )
    {
        /*
         * Insert the link at the beginning
         */
     //   $in = '<a href="options-general.php?page=cookie-bar-settings">' . __('Settings','mtt') . '</a>';
     //   array_unshift($links, $in);

        /*
         * Insert at the end
         */
         $links[] = '<a href="options-general.php?page=cookie-bar-settings">'.__('Settings','mtt').'</a>';
    }
    return $links;
}

function cookie_bar_settings_page() {

    $cookie_bar_show_for_logged_out_users_only_option = get_option('cookie_bar_show_for_logged_out_users_only');
    $cookie_bar_show_on_top = get_option('cookie_bar_show_on_top');

    ?>
<script>
jQuery(document).ready(function($){
    $(".cookie_bar_btn_bg_colour").wpColorPicker();
    $(".cookie_bar_btn_font_colour").wpColorPicker();
    $(".cookie_bar_bar_bg_colour").wpColorPicker();
    $(".cookie_bar_bar_font_colour").wpColorPicker();
});
</script>
    <style type="text/css" >
        .wrap {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
        }

        .postbox .inside h2, .wrap [class$="icon32"] + h2, .wrap h1, .wrap > h2:first-child {
            padding: 0px;
        }

        @media screen and (max-width: 768px) {
            input[type="text"] {
                max-width: 200px !important;
            ; }

            xmp {
                display: block;
                white-space: pre-wrap;
            }
        }

    </style>
<div class="wrap">
<h2><?php _e('Cookie Bar Settings', 'cookie-bar'); ?></h2>
<p><?php _e('Cookie Bar allows you to discreetly inform visitors that your website uses cookies.

This is to notify your visitors that you are using cookies and does not control which or if cookies are set. Your cookies are set in any case.', 'cookie-bar'); ?></p>
<form method="post" action="options.php">
    <?php settings_fields( 'cookie-bar-settings' ); ?>
    <?php do_settings_sections( 'cookie-bar-settings' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Expiration', 'cookie-bar'); ?></th>
            <td>
                <?php $expiration_type = "custom";
                $option_type = get_option('cookie_bar_expiration_type');
                if (isset($option_type) && $option_type === "never") {
                    $expiration_type = "never";
                }

                $expiration_custom_date = "30";
                $option_custom_date = get_option('cookie_bar_days_to_expire');
                $option_custom_date = preg_replace('/\D/', '', isset($option_custom_date) ? $option_custom_date : '');

                if (isset($option_custom_date) && (!empty($option_custom_date)) ) {
                    $expiration_custom_date = $option_custom_date;
                }
                ?>
                <input type="radio"  name="cookie_bar_expiration_type" <?php if ($expiration_type=="never") echo "checked";?> value="never">Never expire (until cookies are cleared)
                <br/>
                <input type="radio"  name="cookie_bar_expiration_type" <?php if ($expiration_type=="custom") echo "checked";?> value="custom">Custom expiration
                <div class="cookie_bar_days_to_expire">
                    <input type="text" size="4"  name="cookie_bar_days_to_expire" value="<?php echo esc_html( $expiration_custom_date ); ?>" /> days
                </div>


            </td>

        </tr>
        <tr>
            <th colspan="2" >
                <hr style="max-width: 205px; margin-left: 0;" />
            </th>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Cookie Bar message', 'cookie-bar'); ?></th>
            <td><input type="text" size="100" name="cookie_bar_message" value="<?php echo esc_html( get_option('cookie_bar_message') ); ?>" /> <small>HTML allowed - E.g.:<xmp>By continuing to browse this site, you agree to our <a href="https://aboutcookies.com/" target="_blank" rel="nofollow">use of cookies</a>.</xmp></small></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Button text', 'cookie-bar'); ?></th>
            <td><input type="text" size="20" name="cookie_bar_button" value="<?php echo esc_attr( get_option('cookie_bar_button') ); ?>" /> <small>E.g.: I understand</small></td>
        </tr>
        <tr>
            <th colspan="2" >
                <hr style="max-width: 205px; margin-left: 0;" />
            </th>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Button background colour', 'cookie-bar'); ?></th>
            <td><input type="text" name="cookie_bar_btn_bg_colour" value="<?php echo esc_attr( get_option('cookie_bar_btn_bg_colour') ); ?>" class="cookie_bar_btn_bg_colour" data-default-color="#45AE52" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Button font colour', 'cookie-bar'); ?></th>
            <td><input type="text" name="cookie_bar_btn_font_colour" value="<?php echo esc_attr( get_option('cookie_bar_btn_font_colour') ); ?>" class="cookie_bar_btn_font_colour" data-default-color="#ffffff" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Bar background colour', 'cookie-bar'); ?></th>
            <td><input type="text" name="cookie_bar_bar_bg_colour" value="<?php echo esc_attr( get_option('cookie_bar_bar_bg_colour') ); ?>" class="cookie_bar_bar_bg_colour" data-default-color="#2e363f" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Bar font colour', 'cookie-bar'); ?></th>
            <td><input type="text" name="cookie_bar_bar_font_colour" value="<?php echo esc_attr( get_option('cookie_bar_bar_font_colour') ); ?>" class="cookie_bar_bar_font_colour" data-default-color="#ffffff" /></td>
        </tr>
        <tr>
            <th colspan="2" >
                <hr style="max-width: 205px; margin-left: 0;" />
            </th>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Show for logged out users only', 'cookie-bar'); ?></th>
            <td><input type="checkbox"  name="cookie_bar_show_for_logged_out_users_only" <?php if ($cookie_bar_show_for_logged_out_users_only_option == "1") echo "checked";?> value="1" ></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e('Display at the top', 'cookie-bar'); ?></th>
            <td><input type="checkbox"  name="cookie_bar_show_on_top" <?php if ($cookie_bar_show_on_top == "1") echo "checked";?> value="1" ></td>
        </tr>

    </table>
    <script type="text/javascript" >

        function handleClick_type(type_) {

            if( type_.value == "custom")
            {

                var tracking_code_gtag = document.getElementsByClassName('cookie_bar_days_to_expire');

                for (var i = 0; i < tracking_code_gtag.length; i ++) {
                    tracking_code_gtag[i].style.display = 'block';
                }

            }
            else if( type_.value == "never")
            {
                var tracking_code_gtag = document.getElementsByClassName('cookie_bar_days_to_expire');

                for (var i = 0; i < tracking_code_gtag.length; i ++) {
                    tracking_code_gtag[i].style.display = 'none';
                }
            }
        }

    </script>

    <?php submit_button(); ?>
</form>
<p>We are very happy to be able to provide this and other <a target="_blank" href="https://www.brontobytes.com/blog/c/wordpress-plugins/">free WordPress plugins</a>.</p>
<p>Plugin developed by <a href="https://www.brontobytes.com/" target="_blank" >Brontobytes</a></p>
    <a href="https://www.brontobytes.com/" target="_blank"><img width="100" style="vertical-align:middle" src="<?php echo plugins_url( 'images/brontobytes.svg', __FILE__ ) ?>" alt="Web hosting provider"></a>
</div>
<?php }

function cookie_bar_settings() {
	register_setting( 'cookie-bar-settings', 'cookie_bar_message' );
	register_setting( 'cookie-bar-settings', 'cookie_bar_button' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_btn_bg_colour' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_btn_font_colour' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_bar_bg_colour' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_bar_font_colour' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_days_to_expire' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_expiration_type' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_show_for_logged_out_users_only' );
    register_setting( 'cookie-bar-settings', 'cookie_bar_show_on_top' );

}
add_action( 'admin_init', 'cookie_bar_settings' );

function cookie_bar_deactivation() {
    delete_option( 'cookie_bar_message' );
    delete_option( 'cookie_bar_button' );
    delete_option( 'cookie_bar_btn_bg_colour' );
    delete_option( 'cookie_bar_btn_font_colour' );
    delete_option( 'cookie_bar_bar_bg_colour' );
    delete_option( 'cookie_bar_bar_font_colour' );
    delete_option( 'cookie_bar_days_to_expire' );
    delete_option( 'cookie_bar_expiration_type' );
    delete_option( 'cookie_bar_show_for_logged_out_users_only' );
    delete_option( 'cookie_bar_show_on_top' );

}
register_deactivation_hook( __FILE__, 'cookie_bar_deactivation' );

function cookie_bar_dependencies() {
	wp_register_script( 'cookie-bar-js', plugins_url('js/cookie-bar.js', __FILE__), array('jquery'), time(), false );
	wp_enqueue_script( 'cookie-bar-js' );
	wp_register_style( 'cookie-bar-css', plugins_url('css/cookie-bar.css', __FILE__) );
	wp_enqueue_style( 'cookie-bar-css' );
}
add_action( 'wp_enqueue_scripts', 'cookie_bar_dependencies' );

class cookie_bar_languages {
    public static function loadTextDomain() {
        load_plugin_textdomain('cookie-bar', false, dirname(plugin_basename(__FILE__ )) . '/languages/');
    }
}


$allowed_html = array(
    'a'      => array(
        'href'       => array(),
        'title'      => array(),
        'target'     => array(),
        'class'      => array(),
        'id'         => array(),
        'rel'        => array(),
    ),
    'br'     => array(),
    'em'     => array(),
    'strong' => array(),
);


add_action('plugins_loaded', array('cookie_bar_languages', 'loadTextDomain'));

function cookie_bar_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'cookie-bar-color-picker', plugins_url('js/cookie-bar.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}
add_action( 'admin_enqueue_scripts', 'cookie_bar_color_picker' );

function cookie_bar() {
    global $allowed_html;

    $cookie_bar_message_output = get_option( 'cookie_bar_message' );
    $cookie_bar_button_output = esc_attr( get_option('cookie_bar_button') );
    $cookie_bar_btn_font_colour = esc_attr( get_option('cookie_bar_btn_font_colour') );
    $cookie_bar_bar_bg_colour = esc_attr( get_option('cookie_bar_bar_bg_colour') );
    $cookie_bar_bar_font_colour = esc_attr( get_option('cookie_bar_bar_font_colour') );
    $cookie_bar_show_for_logged_out_users_only = esc_attr( get_option('cookie_bar_show_for_logged_out_users_only') );
    $cookie_bar_show_on_top = esc_attr( get_option('cookie_bar_show_on_top') );

    $expiration_type = "custom";
    $option_type = get_option('cookie_bar_expiration_type');
    if (isset($option_type) && $option_type === "never") {
        $expiration_type = "never";
    }

    $expiration_custom_date = "30";
    $option_custom_date = get_option('cookie_bar_days_to_expire');
    if (isset($option_custom_date) && (!empty($option_custom_date)) ) {
        $expiration_custom_date = $option_custom_date;
    }

    if ( empty( $cookie_bar_message_output ) ) $cookie_bar_message_output = 'By continuing to browse this site, you agree to our <a href="https://aboutcookies.com/" target="_blank" rel="nofollow">use of cookies</a>.';
    if ( empty( $cookie_bar_button_output ) ) $cookie_bar_button_output = "I Understand";
?>
        <style type="text/css" >
            <?php
            if (isset($cookie_bar_btn_font_colour) && (!empty($cookie_bar_btn_font_colour))) {
            ?>
                button#euCookieAcceptWP { color : <?php echo $cookie_bar_btn_font_colour ?>; }
            <?php
            }

            if (isset($cookie_bar_bar_bg_colour) && (!empty($cookie_bar_bar_bg_colour))) {
            ?>
                #eu-cookie-bar { background-color : <?php echo $cookie_bar_bar_bg_colour ?>; }
            <?php
            }


            if (isset($cookie_bar_bar_font_colour) && (!empty($cookie_bar_bar_font_colour))) {
            ?>
                #eu-cookie-bar , #eu-cookie-bar a { color : <?php echo $cookie_bar_bar_font_colour ?>; }
            <?php
            }


              if (isset($cookie_bar_show_on_top) && (!empty($cookie_bar_show_on_top))) {
            ?>
                #eu-cookie-bar
                {
                    position: fixed;
                    top: 0;
                    height: 30px;
                    z-index: 999999999999999;
                }
            <?php
            }



            ?>




        </style>
    <?php  if($cookie_bar_show_for_logged_out_users_only) {
                if(is_user_logged_in()) {
                    return;
                }
            } ?>
<!-- Cookie Bar -->
<div id="eu-cookie-bar"><?php echo wp_kses( $cookie_bar_message_output, $allowed_html ); ?> <button id="euCookieAcceptWP" <?php if (get_option('cookie_bar_btn_bg_colour') == true) { ?> style="background:<?php echo esc_html( get_option('cookie_bar_btn_bg_colour') ); ?>;" <?php } ?> onclick="euSetCookie('euCookiesAcc', true, <?php if($expiration_type === 'never' ) {echo '4000';} if($expiration_type === "custom" ) {echo $expiration_custom_date;} ?>); euAcceptCookiesWP();"><?php echo  wp_kses($cookie_bar_button_output, $allowed_html); ?></button></div>
<!-- End Cookie Bar -->
<?php
}
add_action( 'wp_footer', 'cookie_bar', 10 );