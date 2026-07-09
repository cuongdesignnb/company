<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_register_settings_pages()
{
    add_menu_page('Company Settings', 'Company Settings', 'manage_options', 'cb-company-settings', 'cb_render_theme_options_page', 'dashicons-admin-customizer', 58);
    add_submenu_page('cb-company-settings', 'Theme Options', 'Theme Options', 'manage_options', 'cb-company-settings', 'cb_render_theme_options_page');
    add_submenu_page('cb-company-settings', 'Homepage Sections', 'Homepage Sections', 'manage_options', 'cb-homepage-sections', 'cb_render_homepage_sections_page');
    add_submenu_page('cb-company-settings', 'String Translations', 'String Translations', 'manage_options', 'cb-string-translations', 'cb_render_string_translations_page');
}

function cb_register_settings()
{
    register_setting('cb_theme_options_group', 'cb_theme_options', ['sanitize_callback' => 'cb_sanitize_theme_options']);
    register_setting('cb_homepage_sections_group', 'cb_homepage_sections', ['sanitize_callback' => 'cb_sanitize_homepage_sections']);
    register_setting('cb_string_translations_group', 'cb_string_translations', ['sanitize_callback' => 'cb_sanitize_string_translations']);
}

function cb_sanitize_theme_options($input)
{
    $defaults = cb_default_theme_options();
    $clean = [];
    foreach ($defaults as $key => $default) {
        $value = $input[$key] ?? $default;
        if (str_contains($key, 'color')) {
            $clean[$key] = sanitize_hex_color($value) ?: $default;
        } elseif (str_contains($key, 'url') || $key === 'logo_url' || $key === 'favicon_url') {
            $clean[$key] = esc_url_raw($value);
        } elseif ($key === 'social_links') {
            $clean[$key] = cb_sanitize_textarea_lines($value);
        } else {
            $clean[$key] = sanitize_text_field($value);
        }
    }
    return $clean;
}

function cb_sanitize_homepage_sections($input)
{
    $sections = is_array($input) ? $input : [];
    foreach ($sections as &$section) {
        foreach ($section as $key => $value) {
            if (is_array($value)) {
                $section[$key] = array_map('sanitize_textarea_field', $value);
            } elseif (str_contains($key, 'url') || str_contains($key, 'image')) {
                $section[$key] = esc_url_raw($value);
            } elseif (str_contains($key, 'description') || $key === 'items') {
                $section[$key] = sanitize_textarea_field($value);
            } else {
                $section[$key] = sanitize_text_field($value);
            }
        }
    }
    return $sections;
}

function cb_sanitize_string_translations($input)
{
    $clean = [];
    foreach ((array) $input as $key => $langs) {
        $clean[sanitize_key($key)] = [
            'en' => sanitize_text_field($langs['en'] ?? ''),
            'zh' => sanitize_text_field($langs['zh'] ?? ''),
        ];
    }
    return $clean;
}

function cb_render_theme_options_page()
{
    $options = cb_get_options();
    echo '<div class="wrap"><h1>Company Theme Options</h1><form method="post" action="options.php">';
    settings_fields('cb_theme_options_group');
    echo '<table class="form-table">';
    foreach (cb_default_theme_options() as $key => $default) {
        $value = $options[$key] ?? $default;
        echo '<tr><th><label for="' . esc_attr($key) . '">' . esc_html(ucwords(str_replace('_', ' ', $key))) . '</label></th><td>';
        if ($key === 'social_links' || $key === 'footer_description') {
            echo '<textarea class="large-text" rows="4" id="' . esc_attr($key) . '" name="cb_theme_options[' . esc_attr($key) . ']">' . esc_textarea($value) . '</textarea>';
        } else {
            $type = str_contains($key, 'color') ? 'text' : 'text';
            echo '<input class="regular-text" type="' . esc_attr($type) . '" id="' . esc_attr($key) . '" name="cb_theme_options[' . esc_attr($key) . ']" value="' . esc_attr($value) . '">';
        }
        echo '</td></tr>';
    }
    echo '</table>';
    submit_button();
    echo '</form></div>';
}

function cb_render_homepage_sections_page()
{
    $sections = get_option('cb_homepage_sections', cb_default_homepage_sections());
    $types = ['hero_slider', 'company_intro', 'product_categories', 'featured_products', 'why_choose_us', 'factory_capability', 'oem_odm_process', 'case_studies', 'certificates', 'news_section', 'inquiry_cta'];
    echo '<div class="wrap"><h1>Homepage Sections</h1><p>Each section supports pipe-delimited repeaters. Example item line: Title|Description|Image URL.</p><form method="post" action="options.php">';
    settings_fields('cb_homepage_sections_group');
    foreach ($sections as $i => $section) {
        echo '<div style="background:#fff;border:1px solid #ccd0d4;padding:16px;margin:14px 0"><h2>Section ' . esc_html((string) ($i + 1)) . '</h2>';
        echo '<p><label><input type="checkbox" name="cb_homepage_sections[' . esc_attr($i) . '][enable]" value="1" ' . checked($section['enable'] ?? '1', '1', false) . '> Enable</label></p>';
        echo '<p><label>Type<br><select name="cb_homepage_sections[' . esc_attr($i) . '][type]">';
        foreach ($types as $type) {
            echo '<option value="' . esc_attr($type) . '" ' . selected($section['type'] ?? '', $type, false) . '>' . esc_html($type) . '</option>';
        }
        echo '</select></label></p>';
        foreach (['eyebrow', 'title', 'subtitle', 'description', 'button_text', 'button_url', 'image', 'items'] as $key) {
            $value = $section[$key] ?? '';
            echo '<p><label>' . esc_html(ucwords(str_replace('_', ' ', $key))) . '<br>';
            if (in_array($key, ['description', 'items'], true)) {
                echo '<textarea class="large-text" rows="4" name="cb_homepage_sections[' . esc_attr($i) . '][' . esc_attr($key) . ']">' . esc_textarea($value) . '</textarea>';
            } else {
                echo '<input class="large-text" name="cb_homepage_sections[' . esc_attr($i) . '][' . esc_attr($key) . ']" value="' . esc_attr($value) . '">';
            }
            echo '</label></p>';
        }
        echo '</div>';
    }
    submit_button('Save Sections');
    echo '</form></div>';
}

function cb_render_string_translations_page()
{
    $strings = wp_parse_args((array) get_option('cb_string_translations', []), cb_default_string_translations());
    echo '<div class="wrap"><h1>String Translations</h1><form method="post" action="options.php">';
    settings_fields('cb_string_translations_group');
    echo '<table class="widefat striped"><thead><tr><th>Key</th><th>English</th><th>Chinese</th></tr></thead><tbody>';
    foreach ($strings as $key => $langs) {
        echo '<tr><td><code>' . esc_html($key) . '</code></td><td><input class="regular-text" name="cb_string_translations[' . esc_attr($key) . '][en]" value="' . esc_attr($langs['en'] ?? '') . '"></td><td><input class="regular-text" name="cb_string_translations[' . esc_attr($key) . '][zh]" value="' . esc_attr($langs['zh'] ?? '') . '"></td></tr>';
    }
    echo '</tbody></table>';
    submit_button();
    echo '</form></div>';
}
