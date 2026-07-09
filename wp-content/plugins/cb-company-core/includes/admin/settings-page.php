<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_register_settings_pages()
{
    add_menu_page('Company Settings', 'Company Settings', 'manage_options', 'cb-company-settings', 'cb_render_theme_options_page', 'dashicons-admin-customizer', 58);
    add_submenu_page('cb-company-settings', 'Theme Options', 'Theme Options', 'manage_options', 'cb-company-settings', 'cb_render_theme_options_page');
    add_submenu_page('cb-company-settings', 'Homepage Builder', 'Homepage Builder', 'manage_options', 'cb-homepage-sections', 'cb_render_homepage_sections_page');
    add_submenu_page('cb-company-settings', 'String Translations', 'String Translations', 'manage_options', 'cb-string-translations', 'cb_render_string_translations_page');
}

function cb_register_settings()
{
    register_setting('cb_theme_options_group', 'cb_theme_options', ['sanitize_callback' => 'cb_sanitize_theme_options']);
    register_setting('cb_homepage_sections_group', 'cb_homepage_sections', ['sanitize_callback' => 'cb_sanitize_homepage_sections']);
    register_setting('cb_string_translations_group', 'cb_string_translations', ['sanitize_callback' => 'cb_sanitize_string_translations']);
}

function cb_theme_option_schema()
{
    return [
        'brand' => [
            'label' => 'Brand',
            'fields' => [
                ['logo', 'image_pair', 'Logo image', 'Choose a logo from the Media Library.'],
                ['mobile_logo', 'image_pair', 'Mobile logo', 'Optional logo for small screens.'],
                ['favicon', 'image_pair', 'Favicon', 'Optional favicon image.'],
                ['logo_text', 'text', 'Logo text'],
                ['logo_subtext', 'text', 'Logo subtext'],
                ['brand_mark_text', 'text', 'Brand mark text'],
                ['show_logo_text', 'checkbox', 'Show logo text'],
            ],
        ],
        'colors' => [
            'label' => 'Colors',
            'fields' => [
                ['primary_color', 'color', 'Primary color'],
                ['primary_dark_color', 'color', 'Primary dark'],
                ['primary_light_color', 'color', 'Primary light'],
                ['secondary_color', 'color', 'Secondary color'],
                ['accent_color', 'color', 'Accent color'],
                ['heading_color', 'color', 'Heading color'],
                ['body_color', 'color', 'Body color'],
                ['muted_color', 'color', 'Muted color'],
                ['border_color', 'color', 'Border color'],
                ['background_color', 'color', 'Background color'],
                ['section_soft_bg', 'color', 'Soft section background'],
                ['header_bg_color', 'color', 'Header background'],
                ['header_text_color', 'color', 'Header text'],
                ['footer_bg_color', 'color', 'Footer background'],
                ['footer_text_color', 'color', 'Footer text'],
                ['footer_heading_color', 'color', 'Footer headings'],
            ],
        ],
        'typography' => [
            'label' => 'Typography',
            'fields' => [
                ['font_body', 'select', 'Body font', '', ['system' => 'System UI', 'serif' => 'Serif', 'mono' => 'Monospace']],
                ['font_heading', 'select', 'Heading font', '', ['system' => 'System UI', 'serif' => 'Serif', 'mono' => 'Monospace']],
                ['base_font_size', 'dimension', 'Base font size'],
                ['body_line_height', 'dimension', 'Body line height'],
                ['heading_line_height', 'dimension', 'Heading line height'],
                ['h1_size_desktop', 'dimension', 'H1 desktop'],
                ['h1_size_mobile', 'dimension', 'H1 mobile'],
                ['h2_size_desktop', 'dimension', 'H2 desktop'],
                ['h2_size_mobile', 'dimension', 'H2 mobile'],
                ['font_weight_heading', 'number', 'Heading weight', '', ['min' => 100, 'max' => 950, 'step' => 50]],
                ['font_weight_button', 'number', 'Button weight', '', ['min' => 100, 'max' => 950, 'step' => 50]],
            ],
        ],
        'layout' => [
            'label' => 'Layout',
            'fields' => [
                ['container_width', 'dimension', 'Container width'],
                ['content_width', 'dimension', 'Content width'],
                ['section_padding_y', 'dimension', 'Section padding Y'],
                ['section_padding_y_mobile', 'dimension', 'Mobile section padding Y'],
                ['grid_gap', 'dimension', 'Grid gap'],
                ['page_hero_padding_y', 'dimension', 'Page hero padding Y'],
                ['border_radius_sm', 'dimension', 'Small radius'],
                ['border_radius_md', 'dimension', 'Medium radius'],
                ['border_radius_lg', 'dimension', 'Large radius'],
            ],
        ],
        'header' => [
            'label' => 'Header',
            'fields' => [
                ['header_layout', 'select', 'Header layout', '', ['logo_left_menu_center_cta_right' => 'Logo left, menu center, CTA right', 'logo_left_menu_right' => 'Logo left, menu right', 'logo_center_menu_below' => 'Centered logo, menu below', 'minimal_logo_cta' => 'Minimal logo + CTA']],
                ['header_style', 'select', 'Header style', '', ['white' => 'White', 'transparent' => 'Transparent', 'dark' => 'Dark']],
                ['header_height', 'dimension', 'Header height'],
                ['header_sticky', 'checkbox', 'Sticky header'],
                ['header_blur', 'checkbox', 'Header blur'],
                ['header_shadow', 'checkbox', 'Header shadow'],
                ['header_full_width', 'checkbox', 'Full-width header'],
                ['show_search', 'checkbox', 'Show search'],
                ['show_language_switcher', 'checkbox', 'Show language switcher'],
                ['show_header_cta', 'checkbox', 'Show CTA'],
                ['header_cta_text', 'text', 'CTA text'],
                ['header_cta_url', 'text', 'CTA URL'],
                ['mobile_header_style', 'select', 'Mobile header style', '', ['offcanvas' => 'Offcanvas', 'dropdown' => 'Dropdown']],
            ],
        ],
        'footer' => [
            'label' => 'Footer',
            'fields' => [
                ['footer_layout', 'select', 'Footer layout', '', ['four_columns' => 'Four columns', 'three_columns' => 'Three columns', 'centered' => 'Centered', 'minimal' => 'Minimal']],
                ['show_footer_logo', 'checkbox', 'Show logo'],
                ['show_footer_products', 'checkbox', 'Show products column'],
                ['show_footer_links', 'checkbox', 'Show quick links'],
                ['show_footer_contact', 'checkbox', 'Show contact'],
                ['show_footer_social', 'checkbox', 'Show social'],
                ['show_footer_subscribe', 'checkbox', 'Show subscribe'],
                ['footer_description', 'textarea', 'Footer description'],
                ['copyright_text', 'text', 'Copyright text'],
            ],
        ],
        'buttons' => [
            'label' => 'Buttons',
            'fields' => [
                ['button_style', 'select', 'Button style', '', ['rounded' => 'Rounded', 'pill' => 'Pill', 'square' => 'Square', 'soft' => 'Soft']],
                ['button_radius', 'dimension', 'Button radius'],
                ['button_height', 'dimension', 'Button height'],
                ['button_padding_x', 'dimension', 'Horizontal padding'],
                ['button_shadow', 'checkbox', 'Button shadow'],
                ['button_hover_effect', 'select', 'Hover effect', '', ['none' => 'None', 'lift' => 'Lift', 'fade' => 'Fade', 'outline' => 'Outline']],
            ],
        ],
        'cards' => [
            'label' => 'Cards',
            'fields' => [
                ['card_radius', 'dimension', 'Card radius'],
                ['card_shadow', 'select', 'Card shadow', '', ['none' => 'None', 'soft' => 'Soft', 'medium' => 'Medium', 'strong' => 'Strong']],
                ['card_border', 'checkbox', 'Card border'],
                ['card_hover_effect', 'select', 'Card hover', '', ['none' => 'None', 'lift' => 'Lift', 'image_zoom' => 'Image zoom', 'border_highlight' => 'Border highlight']],
                ['product_card_style', 'select', 'Product card style', '', ['clean' => 'Clean', 'soft' => 'Soft', 'bordered' => 'Bordered', 'overlay' => 'Overlay']],
                ['category_card_style', 'select', 'Category card style', '', ['image_top' => 'Image top', 'soft' => 'Soft', 'bordered' => 'Bordered']],
                ['news_card_style', 'select', 'News card style', '', ['image_left' => 'Image left', 'image_top' => 'Image top', 'clean' => 'Clean']],
            ],
        ],
        'animation' => [
            'label' => 'Animation',
            'fields' => [
                ['enable_animation', 'checkbox', 'Enable animation'],
                ['animation_style', 'select', 'Animation style', '', ['none' => 'None', 'fade' => 'Fade', 'fade_up' => 'Fade up', 'slide_up' => 'Slide up', 'scale' => 'Scale']],
                ['animation_duration', 'dimension', 'Animation duration'],
                ['animation_delay_step', 'dimension', 'Delay step'],
                ['enable_counter_anim', 'checkbox', 'Counter animation'],
                ['enable_hover_anim', 'checkbox', 'Hover animation'],
            ],
        ],
        'mobile' => [
            'label' => 'Mobile',
            'fields' => [
                ['mobile_breakpoint', 'dimension', 'Mobile breakpoint'],
                ['mobile_menu_style', 'select', 'Mobile menu style', '', ['offcanvas' => 'Offcanvas', 'dropdown' => 'Dropdown']],
                ['mobile_show_cta', 'checkbox', 'Show CTA on mobile'],
                ['mobile_show_language', 'checkbox', 'Show language on mobile'],
                ['mobile_hero_compact', 'checkbox', 'Compact hero on mobile'],
                ['mobile_product_columns', 'number', 'Mobile product columns', '', ['min' => 1, 'max' => 2]],
                ['tablet_product_columns', 'number', 'Tablet product columns', '', ['min' => 1, 'max' => 4]],
                ['desktop_product_columns', 'number', 'Desktop product columns', '', ['min' => 2, 'max' => 6]],
            ],
        ],
        'contact' => [
            'label' => 'Contact',
            'fields' => [
                ['contact_phone', 'text', 'Phone'],
                ['contact_email', 'text', 'Email'],
                ['company_address', 'textarea', 'Company address'],
                ['floating_contact', 'checkbox', 'Floating quote button'],
                ['social_links', 'textarea', 'Social links', 'One per line: Label|URL'],
            ],
        ],
    ];
}

function cb_sanitize_theme_options($input)
{
    $input = (array) $input;
    $defaults = cb_default_theme_options();
    $clean = [];
    $choice_fields = [
        'header_layout' => ['logo_left_menu_center_cta_right', 'logo_left_menu_right', 'logo_center_menu_below', 'minimal_logo_cta'],
        'header_style' => ['white', 'transparent', 'dark'],
        'mobile_header_style' => ['offcanvas', 'dropdown'],
        'button_style' => ['rounded', 'pill', 'square', 'soft'],
        'button_hover_effect' => ['none', 'lift', 'fade', 'outline'],
        'card_shadow' => ['none', 'soft', 'medium', 'strong'],
        'card_hover_effect' => ['none', 'lift', 'image_zoom', 'border_highlight'],
        'product_card_style' => ['clean', 'soft', 'bordered', 'overlay'],
        'category_card_style' => ['image_top', 'soft', 'bordered'],
        'news_card_style' => ['image_left', 'image_top', 'clean'],
        'footer_layout' => ['four_columns', 'three_columns', 'centered', 'minimal'],
        'animation_style' => ['none', 'fade', 'fade_up', 'slide_up', 'scale'],
        'mobile_menu_style' => ['offcanvas', 'dropdown'],
        'font_body' => ['system', 'serif', 'mono'],
        'font_heading' => ['system', 'serif', 'mono'],
    ];
    $checkboxes = array_filter(array_keys($defaults), fn($key) => str_starts_with($key, 'show_') || str_starts_with($key, 'enable_') || in_array($key, ['header_sticky', 'header_blur', 'header_shadow', 'header_full_width', 'button_shadow', 'card_border', 'floating_contact', 'mobile_show_cta', 'mobile_show_language', 'mobile_hero_compact'], true));

    foreach ($defaults as $key => $default) {
        $value = $input[$key] ?? $default;
        if (in_array($key, $checkboxes, true)) {
            $clean[$key] = $value === '1' ? '1' : '0';
        } elseif (str_ends_with($key, '_id')) {
            $clean[$key] = (string) absint($value);
        } elseif (str_contains($key, 'color')) {
            $clean[$key] = sanitize_hex_color($value) ?: $default;
        } elseif (isset($choice_fields[$key])) {
            $clean[$key] = cb_sanitize_choice($value, $choice_fields[$key], $default);
        } elseif (str_contains($key, 'url')) {
            $clean[$key] = esc_url_raw($value);
        } elseif (in_array($key, ['social_links', 'company_address', 'footer_description'], true)) {
            $clean[$key] = sanitize_textarea_field($value);
        } elseif (preg_match('/(width|height|size|padding|radius|gap|duration|step|line_height|breakpoint)$/', $key)) {
            $clean[$key] = cb_sanitize_css_size($value, $default);
        } elseif (str_contains($key, 'columns') || str_contains($key, 'weight')) {
            $clean[$key] = (string) absint($value);
        } elseif ($key === 'contact_email') {
            $clean[$key] = sanitize_email($value);
        } else {
            $clean[$key] = sanitize_text_field($value);
        }
    }
    return $clean;
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
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'cb-company-core'));
    }
    $schema = cb_theme_option_schema();
    $active = sanitize_key($_GET['tab'] ?? 'brand');
    if (!isset($schema[$active])) {
        $active = 'brand';
    }
    $options = cb_get_options();
    echo '<div class="wrap cb-admin-shell"><h1>Company Settings</h1><nav class="cb-admin-tabs">';
    foreach ($schema as $tab => $group) {
        $url = add_query_arg(['page' => 'cb-company-settings', 'tab' => $tab], admin_url('admin.php'));
        echo '<a class="' . esc_attr($tab === $active ? 'is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($group['label']) . '</a>';
    }
    echo '</nav><form method="post" action="options.php" class="cb-admin-panel">';
    settings_fields('cb_theme_options_group');
    echo '<div class="cb-admin-grid">';
    foreach ($schema[$active]['fields'] as $field) {
        cb_render_theme_option_field($field, $options);
    }
    echo '</div>';
    submit_button('Save ' . $schema[$active]['label']);
    echo '</form></div>';
}

function cb_render_theme_option_field($field, $options)
{
    [$key, $type, $label] = $field;
    $description = $field[3] ?? '';
    $extra = $field[4] ?? [];
    $defaults = cb_default_theme_options();
    if ($type === 'image_pair') {
        cb_admin_image_field([
            'id' => 'cb_' . $key . '_id',
            'label' => $label,
            'description' => $description,
            'name_base' => 'cb_theme_options',
            'id_key' => $key . '_id',
            'url_key' => $key . '_url',
            'id_value' => $options[$key . '_id'] ?? '',
            'url_value' => $options[$key . '_url'] ?? '',
        ]);
        return;
    }
    $args = [
        'id' => 'cb_' . $key,
        'name' => 'cb_theme_options[' . $key . ']',
        'label' => $label,
        'description' => $description,
        'value' => $options[$key] ?? $defaults[$key] ?? '',
        'default' => $defaults[$key] ?? '',
    ];
    if ($type === 'color') {
        cb_admin_color_field($args);
    } elseif ($type === 'textarea') {
        cb_admin_textarea_field($args);
    } elseif ($type === 'select') {
        cb_admin_select_field($args + ['choices' => $extra]);
    } elseif ($type === 'checkbox') {
        cb_admin_checkbox_field($args);
    } elseif ($type === 'number') {
        cb_admin_number_field($args + $extra);
    } elseif ($type === 'dimension') {
        cb_admin_dimension_field($args);
    } else {
        cb_admin_text_field($args);
    }
}

function cb_render_string_translations_page()
{
    $strings = wp_parse_args((array) get_option('cb_string_translations', []), cb_default_string_translations());
    echo '<div class="wrap cb-admin-shell"><h1>String Translations</h1><form method="post" action="options.php" class="cb-admin-panel">';
    settings_fields('cb_string_translations_group');
    echo '<table class="widefat striped"><thead><tr><th>Key</th><th>English</th><th>Chinese</th></tr></thead><tbody>';
    foreach ($strings as $key => $langs) {
        echo '<tr><td><code>' . esc_html($key) . '</code></td><td><input class="regular-text" name="cb_string_translations[' . esc_attr($key) . '][en]" value="' . esc_attr($langs['en'] ?? '') . '"></td><td><input class="regular-text" name="cb_string_translations[' . esc_attr($key) . '][zh]" value="' . esc_attr($langs['zh'] ?? '') . '"></td></tr>';
    }
    echo '</tbody></table>';
    submit_button();
    echo '</form></div>';
}
