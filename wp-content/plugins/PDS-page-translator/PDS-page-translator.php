<?php
/**
 * Plugin Name:       PDS Page Translator
 * Plugin URI:        paladinidigital.com
 * Description:       Adds controls via shortcode to translate page content using the Google Gemini API.
 * Version:           1.8.0
 * Author:            Ricard PDS
 * Author URI:        paladinidigital.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pds-page-translator
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) { exit; }

define('PDS_DEV_MODE', false);
define('PDS_OPTION_GROUP', 'pds_options_main');
define('PDS_UI_SHORTCODE_TAG', 'pds_translator_ui');
define('PDS_WORD_LIST_SHORTCODE_TAG', 'pds_translator_word_list');

function pds_get_all_possible_languages() {
    $languages = [ 'Arabic' => __('Arabic', 'pds-page-translator'), 'Chinese (Simplified)' => __('Chinese (Simplified)', 'pds-page-translator'), 'English' => __('English', 'pds-page-translator'), 'French' => __('French', 'pds-page-translator'), 'German' => __('German', 'pds-page-translator'), 'Hindi' => __('Hindi', 'pds-page-translator'), 'Italian' => __('Italian', 'pds-page-translator'), 'Japanese' => __('Japanese', 'pds-page-translator'), 'Korean' => __('Korean', 'pds-page-translator'), 'Portuguese' => __('Portuguese', 'pds-page-translator'), 'Russian' => __('Russian', 'pds-page-translator'), 'Spanish' => __('Spanish', 'pds-page-translator'), ];
    uasort($languages, function($a, $b) { return strcmp($a, $b); });
    return $languages;
}

function pds_get_default_options() {
    $all_langs = pds_get_all_possible_languages(); $default_lang = 'English';
    if (!isset($all_langs[$default_lang])) { $default_lang = !empty($all_langs) ? key($all_langs) : ''; }
    $default_words = [ 'English' => 'Translate', 'Spanish' => 'Traducir', 'French' => 'Traduire', 'German' => 'Übersetzen', 'Italian' => 'Traduci', 'Japanese' => '翻訳する', 'Portuguese' => 'Traduzir', 'Russian' => 'Перевести', 'Chinese (Simplified)' => '翻译', 'Korean' => '번역하다', 'Arabic' => 'ترجمة', 'Hindi' => 'अनुवाद करना', ];
    return [ 'pds_api_key' => '', 'pds_content_selector' => 'article, .entry-content, .post-content, #main, #primary', 'pds_default_language' => $default_lang, 'pds_available_languages' => [$default_lang => '1'], 'pds_language_words' => $default_words, ];
}

function pds_add_admin_menu() { add_options_page( __('PDS Page Translator Settings', 'pds-page-translator'), __('PDS Translator', 'pds-page-translator'), 'manage_options', 'pds_page_translator', 'pds_options_page_html' ); }
add_action('admin_menu', 'pds_add_admin_menu');

function pds_settings_init() {
    register_setting(PDS_OPTION_GROUP, PDS_OPTION_GROUP, 'pds_sanitize_options');
    add_settings_section('pds_settings_section_api', __('API Configuration', 'pds-page-translator'), null, 'pds_page_translator');
    add_settings_field( 'pds_api_key', __('Gemini API Key', 'pds-page-translator'), 'pds_render_field', 'pds_page_translator', 'pds_settings_section_api', ['type' => 'password', 'name' => 'pds_api_key', 'desc' => __('Enter your Google Gemini API Key. Keep confidential.', 'pds-page-translator') . ' <a href="https://aistudio.google.com/app/apikey" target="_blank">' . __('Get Key', 'pds-page-translator') . '</a>'] );
    add_settings_section('pds_settings_section_frontend', __('Frontend Settings', 'pds-page-translator'), null, 'pds_page_translator');
    add_settings_field( 'pds_content_selector', __('Content CSS Selector(s)', 'pds-page-translator'), 'pds_render_field', 'pds_page_translator', 'pds_settings_section_frontend', ['type' => 'text', 'name' => 'pds_content_selector', 'desc' => __('Comma-separated selectors for main content area(s) to translate.', 'pds-page-translator') . ' Ex: <code>article, .main-content</code>', 'size' => 70] );
    add_settings_field( 'pds_default_language', __('Default Target Language (for UI)', 'pds-page-translator'), 'pds_render_field', 'pds_page_translator', 'pds_settings_section_frontend', ['type' => 'select', 'name' => 'pds_default_language', 'options' => pds_get_all_possible_languages(), 'desc' => __('The language pre-selected in the dropdown UI.', 'pds-page-translator')] );
    add_settings_field( 'pds_available_languages', __('Available Frontend Languages', 'pds-page-translator'), 'pds_render_field', 'pds_page_translator', 'pds_settings_section_frontend', ['type' => 'checkboxes', 'name' => 'pds_available_languages', 'options' => pds_get_all_possible_languages(), 'desc' => __('Select languages users can choose from.', 'pds-page-translator')] );
    add_settings_section( 'pds_settings_section_words', __('Language Word Customization (for Slider Shortcode)', 'pds-page-translator'), 'pds_settings_section_words_callback', 'pds_page_translator' );
    add_settings_field( 'pds_language_words', __('Display Words', 'pds-page-translator'), 'pds_render_language_words_field', 'pds_page_translator', 'pds_settings_section_words' );
}
add_action('admin_init', 'pds_settings_init');

function pds_settings_section_words_callback() { echo '<p>' . __('Enter the word (e.g., "Translate", "Traducir") you want displayed for each language in the `[pds_translator_word_list]` slider shortcode. These only appear for languages marked as "Available".', 'pds-page-translator') . '</p>'; }

function pds_render_language_words_field() {
    $options = get_option(PDS_OPTION_GROUP, []); $defaults = pds_get_default_options(); $available_languages = isset($options['pds_available_languages']) && is_array($options['pds_available_languages']) ? $options['pds_available_languages'] : $defaults['pds_available_languages']; $current_words = isset($options['pds_language_words']) && is_array($options['pds_language_words']) ? $options['pds_language_words'] : $defaults['pds_language_words']; $all_possible_languages = pds_get_all_possible_languages();
    echo '<fieldset>'; $has_available = false;
    foreach ($all_possible_languages as $lang_key => $lang_label) { if (isset($available_languages[$lang_key]) && $available_languages[$lang_key] === '1') { $has_available = true; $current_value = isset($current_words[$lang_key]) ? $current_words[$lang_key] : ($defaults['pds_language_words'][$lang_key] ?? $lang_key); printf( '<div style="margin-bottom: 10px;"><label for="pds_word_%1$s" style="display: none; width: 180px; font-weight: bold;">%2$s:</label> <input type="text" id="pds_word_%1$s" name="%3$s[pds_language_words][%1$s]" value="%4$s" size="30"></div>', esc_attr($lang_key), esc_html($lang_label), PDS_OPTION_GROUP, esc_attr($current_value) ); } }
    if (!$has_available) { echo '<p><em>' . __('No languages are currently marked as available in the "Frontend Settings" section above. Please enable languages to customize their words.', 'pds-page-translator') . '</em></p>'; } echo '</fieldset>';
}

function pds_render_field($args) {
    $options = get_option(PDS_OPTION_GROUP, []); $name = $args['name']; $defaults = pds_get_default_options(); $value = isset($options[$name]) ? $options[$name] : ($defaults[$name] ?? '');
    if ($args['type'] === 'checkboxes' && !is_array($value)) { $value = $defaults[$name] ?? []; }
    switch ($args['type']) {
        case 'password': case 'text': printf( '<input type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" size="%5$s"%6$s>', esc_attr($args['type']), esc_attr($name), PDS_OPTION_GROUP, esc_attr($value), isset($args['size']) ? intval($args['size']) : 50, $args['type'] === 'password' ? ' autocomplete="new-password"' : '' ); break;
        case 'select': printf('<select id="%1$s" name="%2$s[%1$s]">', esc_attr($name), PDS_OPTION_GROUP); $current_selection = !empty($value) ? $value : ($defaults[$name] ?? ''); if (!isset($args['options'][$current_selection])) { $current_selection = !empty($args['options']) ? key($args['options']) : ''; } foreach (($args['options'] ?? []) as $key => $label) { printf( '<option value="%s" %s>%s</option>', esc_attr($key), selected($current_selection, $key, false), esc_html($label) ); } echo '</select>'; break;
        case 'checkboxes': echo '<fieldset>'; $checked_options = is_array($value) ? $value : []; foreach (($args['options'] ?? []) as $key => $label) { printf( '<label style="margin-right: 15px; display: inline-block;"><input type="checkbox" name="%1$s[%2$s][%3$s]" value="1" %4$s> %5$s</label><br>', PDS_OPTION_GROUP, esc_attr($name), esc_attr($key), checked(isset($checked_options[$key]), true, false), esc_html($label) ); } echo '</fieldset>'; break;
    }
    if (!empty($args['desc'])) { printf('<p class="description">%s</p>', wp_kses_post($args['desc'])); }
}

function pds_sanitize_options($input) {
    $output = []; $defaults = pds_get_default_options(); $all_possible_keys = array_keys(pds_get_all_possible_languages());
    $output['pds_api_key'] = isset($input['pds_api_key']) ? sanitize_text_field($input['pds_api_key']) : ''; $output['pds_content_selector'] = isset($input['pds_content_selector']) ? sanitize_text_field($input['pds_content_selector']) : $defaults['pds_content_selector']; $output['pds_default_language'] = isset($input['pds_default_language']) && in_array($input['pds_default_language'], $all_possible_keys) ? $input['pds_default_language'] : $defaults['pds_default_language'];
    $output['pds_available_languages'] = []; if (isset($input['pds_available_languages']) && is_array($input['pds_available_languages'])) { foreach ($input['pds_available_languages'] as $lang_key => $checked) { if ($checked === '1' && in_array($lang_key, $all_possible_keys)) { $output['pds_available_languages'][$lang_key] = '1'; } } }
    if (empty($output['pds_available_languages'])) { $output['pds_available_languages'][$output['pds_default_language']] = '1'; }
    $output['pds_language_words'] = $defaults['pds_language_words']; if (isset($input['pds_language_words']) && is_array($input['pds_language_words'])) { foreach ($input['pds_language_words'] as $lang_key => $word) { if (isset($output['pds_available_languages'][$lang_key])) { $output['pds_language_words'][$lang_key] = sanitize_text_field(trim($word)); } } }
    return $output;
}

function pds_get_option($key) { $options = get_option(PDS_OPTION_GROUP, pds_get_default_options()); $defaults = pds_get_default_options(); return isset($options[$key]) ? $options[$key] : ($defaults[$key] ?? null); }

function pds_options_page_html() { if (!current_user_can('manage_options')) return; ?> <div class="wrap"> <h1><?php echo esc_html(get_admin_page_title()); ?></h1> <?php settings_errors(); ?> <?php if (defined('PDS_DEV_MODE') && PDS_DEV_MODE) : ?> <div class="notice notice-warning is-dismissible" style="margin-top: 10px;"> <p><strong><?php _e('Development Mode is ON.', 'pds-page-translator'); ?></strong> <?php _e('The plugin will use mock data and not make real API calls.', 'pds-page-translator'); ?></p> </div> <?php endif; ?> <form action="options.php" method="post"> <?php settings_fields(PDS_OPTION_GROUP); do_settings_sections('pds_page_translator'); submit_button(__('Save Settings', 'pds-page-translator')); ?> </form> </div> <?php }

function pds_register_shortcodes() { add_shortcode(PDS_UI_SHORTCODE_TAG, 'pds_render_translator_ui_shortcode'); add_shortcode(PDS_WORD_LIST_SHORTCODE_TAG, 'pds_render_v_slider_shortcode'); }
add_action('init', 'pds_register_shortcodes');

function pds_enqueue_assets_if_needed() {
    static $scripts_enqueued = false;
    if (!$scripts_enqueued) {
        wp_enqueue_style('pds-styles', plugin_dir_url(__FILE__) . 'pds-styles.css', [], '1.8.0');
        wp_enqueue_script('pds-gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', [], '3.12.5', true);
        wp_enqueue_script('pds-scripts', plugin_dir_url(__FILE__) . 'pds-scripts.js', ['jquery', 'pds-gsap'], '1.8.0', true);
        $default_language = pds_get_option('pds_default_language'); $available_languages_saved = pds_get_option('pds_available_languages'); $all_possible_languages = pds_get_all_possible_languages(); $frontend_languages = [];
        if (is_array($available_languages_saved)) { foreach ($available_languages_saved as $lang_key => $is_enabled) { if ($is_enabled === '1' && isset($all_possible_languages[$lang_key])) { $frontend_languages[$lang_key] = $all_possible_languages[$lang_key]; } } }
        if (!empty($default_language) && !isset($frontend_languages[$default_language]) && isset($all_possible_languages[$default_language])) { $frontend_languages[$default_language] = $all_possible_languages[$default_language]; uasort($frontend_languages, function($a, $b) { return strcmp($a, $b); }); }
        $api_key_present = !empty(pds_get_option('pds_api_key')); $is_admin = current_user_can('manage_options');
        wp_localize_script('pds-scripts', 'pds_params', [ 'ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('pds_translate_nonce'), 'content_selector' => pds_get_option('pds_content_selector'), 'available_languages' => $frontend_languages, 'default_language' => $default_language, 'is_dev_mode' => (defined('PDS_DEV_MODE') && PDS_DEV_MODE), 'api_key_missing' => !$api_key_present, 'is_admin' => $is_admin, 'text_select_language' => __('Select Language:', 'pds-page-translator'), 'text_translate_button' => __('', 'pds-page-translator'), 'text_translating' => __('Translating...', 'pds-page-translator'), 'text_translating_progress' => __('Translating %d/%d...', 'pds-page-translator'), 'text_show_original' => __('< Back', 'pds-page-translator'), 'text_error_general' => __('Translation Error. Please try again.', 'pds-page-translator'), 'text_error_no_content' => __('Could not find content to translate.', 'pds-page-translator'), 'text_error_no_key_admin' => __('API Key missing in settings.', 'pds-page-translator'), 'text_error_no_key_user' => __('Translation service not configured.', 'pds-page-translator'), 'text_error_timeout' => __('Translation request timed out.', 'pds-page-translator'), 'text_error_server' => __('Server error during translation.', 'pds-page-translator'), 'text_dev_mode_notice' => __('(Dev Mode)', 'pds-page-translator'), 'text_error_partial_fail' => __('Some parts could not be translated.', 'pds-page-translator'), ]);
        $scripts_enqueued = true;
    }
}

function pds_render_translator_ui_shortcode($atts = [], $content = null) { pds_enqueue_assets_if_needed(); return '<div id="pds-translate-ui-container"></div>'; }

function pds_render_v_slider_shortcode($atts = [], $content = null) {
     pds_enqueue_assets_if_needed();
     $available_languages = pds_get_option('pds_available_languages'); $language_words = pds_get_option('pds_language_words'); $defaults = pds_get_default_options(); $all_possible_languages = pds_get_all_possible_languages();
     if (empty($available_languages) || !is_array($available_languages)) { return ''; }
     $list_items = ''; $count = 0;
     foreach($all_possible_languages as $lang_key => $lang_label) {
         if (isset($available_languages[$lang_key]) && $available_languages[$lang_key] === '1') {
             $count++; $word = isset($language_words[$lang_key]) && !empty(trim($language_words[$lang_key])) ? trim($language_words[$lang_key]) : ($defaults['pds_language_words'][$lang_key] ?? $lang_key);
             $list_items .= sprintf('<li class="pds-v-slide pds-v-slide-trigger" data-target-language="%s">%s</li>', esc_attr($lang_key), esc_html($word) );
         }
     }
     if ($count === 0) { return ''; }
     $output = '<div class="pds-v-slider-container">'; $output .= '<div class="pds-v-slider-frame">'; $output .= sprintf('<ul class="pds-v-slides">%s</ul>', $list_items); $output .= '</div>';  $output .= '</div>';
     return $output;
}

function pds_handle_translation_request() {
    check_ajax_referer('pds_translate_nonce', 'nonce');
    if (defined('PDS_DEV_MODE') && PDS_DEV_MODE) {
        sleep(1); $content_html = isset($_POST['content']) ? wp_kses_post($_POST['content']) : ''; $target_language = isset($_POST['target_language']) ? sanitize_text_field($_POST['target_language']) : 'English';
        if (empty($content_html)) wp_send_json_error(['message' => 'Mock Error: No content received.'], 400);
        $fake_translation = preg_replace_callback('/(?<=>)([^<]+)(?=<)/s', function ($matches) use ($target_language) { $trimmed_text = trim($matches[1]); return empty($trimmed_text) ? $matches[1] : ' [' . esc_html($target_language) . ':] ' . $trimmed_text . ' '; }, $content_html);
        wp_send_json_success(['translated_html' => wp_kses_post($fake_translation)]);
    }
    $api_key = pds_get_option('pds_api_key'); if (empty($api_key)) { $error_msg = current_user_can('manage_options') ? __('Error: Gemini API Key is missing.', 'pds-page-translator') : __('Translation service unavailable.', 'pds-page-translator'); wp_send_json_error(['message' => $error_msg], 400); }
    $content_html = isset($_POST['content']) ? wp_kses_post($_POST['content']) : ''; $target_language = isset($_POST['target_language']) ? sanitize_text_field($_POST['target_language']) : pds_get_option('pds_default_language');
    $all_languages = array_keys(pds_get_all_possible_languages()); if (!in_array($target_language, $all_languages)) { wp_send_json_error(['message' => __('Invalid target language specified.', 'pds-page-translator')], 400); }
    if (empty($content_html)) wp_send_json_error(['message' => __('Error: No content received.', 'pds-page-translator')], 400);
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;
    $prompt = "Translate the following HTML content into $target_language. RULES: 1. Translate ONLY the visible text content for a user (inside tags like p, h1-h6, li, a, span, figcaption, td, th, etc., and image alt attributes). 2. PRESERVE ALL original HTML tags, attributes (href, src, class, id, style, data-*, etc.), and structure EXACTLY. 3. DO NOT translate HTML tag names or attribute names. 4. DO NOT translate content inside <script>, <style>, or <![CDATA[...]]> sections. 5. Handle HTML entities (like &, <, >,  ) correctly in the output. 6. Your response MUST contain ONLY the translated HTML snippet. 7. START the response directly with the first translated HTML tag (e.g., `<p>`). 8. END the response directly with the last translated HTML tag (e.g., `</p>`). 9. DO NOT include any introductory text, explanations, apologies, summaries, or markdown code fences (like ```html ... ```) before or after the translated HTML.\n\nOriginal HTML Content:\n```html\n$content_html\n```";
    $request_body = json_encode(['contents' => [['parts' => [['text' => $prompt]]]], 'safetySettings' => [['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'], ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'], ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'], ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']], 'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 8192, 'responseMimeType' => 'text/plain'] ]);
    $args = ['method' => 'POST', 'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'], 'body' => $request_body, 'timeout' => 60, 'sslverify' => true, ];
    $response = wp_remote_post($api_url, $args);
    if (is_wp_error($response)) { $error_code = $response->get_error_code(); $error_message = $response->get_error_message(); error_log("PDS Translator WP HTTP Error: [$error_code] $error_message"); wp_send_json_error(['message' => __('Network error contacting translation service.', 'pds-page-translator')], 503); }
    $response_code = wp_remote_retrieve_response_code($response); $response_body = wp_remote_retrieve_body($response); $result_data = json_decode($response_body, true);
    if ($response_code !== 200) { $error_message = __('API Error', 'pds-page-translator') . ' (' . $response_code . ')'; $log_message = "Gemini API Error ($response_code): "; if (isset($result_data['error']['message'])) { $api_error = $result_data['error']['message']; $log_message .= $api_error; if (strpos($api_error, 'API key not valid') !== false) { $error_message = __('Invalid API Key configured.', 'pds-page-translator'); } elseif (strpos($api_error, 'quota') !== false) { $error_message = __('Translation quota exceeded.', 'pds-page-translator'); } else { $error_message = __('Translation service failed.', 'pds-page-translator'); } } else { $log_message .= $response_body; $error_message = __('Unknown API error.', 'pds-page-translator'); } error_log($log_message); wp_send_json_error(['message' => $error_message, 'details' => (WP_DEBUG ? $result_data : null)], $response_code); }
    $translated_html = ''; $finish_reason = $result_data['candidates'][0]['finishReason'] ?? 'UNKNOWN'; error_log("Gemini Finish Reason: " . $finish_reason);
    if ($finish_reason === 'STOP' && isset($result_data['candidates'][0]['content']['parts'][0]['text'])) { $translated_html = $result_data['candidates'][0]['content']['parts'][0]['text']; $translated_html = trim($translated_html); if (str_starts_with($translated_html, '```html')) { $translated_html = substr($translated_html, 7); $translated_html = rtrim($translated_html, '`'); } elseif (str_starts_with($translated_html, '```')) { $translated_html = substr($translated_html, 3); $translated_html = rtrim($translated_html, '`'); } $translated_html = trim($translated_html); $sanitized_html = wp_kses_post($translated_html); wp_send_json_success(['translated_html' => $sanitized_html]); }
    else { $error_message = __('Translation failed or blocked.', 'pds-page-translator'); $log_message = 'PDS translation did not finish correctly. Reason: ' . $finish_reason; if ($finish_reason === 'SAFETY') { $log_message .= ' (Safety settings triggered)'; $error_message = __('Translation blocked due to safety settings.', 'pds-page-translator'); } elseif ($finish_reason === 'MAX_TOKENS') { $log_message .= ' (Max output tokens reached)'; $error_message = __('Translation truncated (too long).', 'pds-page-translator'); } error_log($log_message); wp_send_json_error(['message' => $error_message, 'reason' => $finish_reason], 500); }
}

add_action('wp_ajax_pds_translate_page', 'pds_handle_translation_request');
add_action('wp_ajax_nopriv_pds_translate_page', 'pds_handle_translation_request');