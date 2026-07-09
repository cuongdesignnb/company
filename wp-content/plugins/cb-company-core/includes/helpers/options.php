<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_default_theme_options()
{
    return [
        'logo_id' => '',
        'logo_url' => '',
        'mobile_logo_id' => '',
        'mobile_logo_url' => '',
        'favicon_id' => '',
        'favicon_url' => '',
        'logo_text' => 'AURELIA',
        'logo_subtext' => 'MANUFACTURING',
        'brand_mark_text' => 'A',
        'show_logo_text' => '1',
        'primary_color' => '#ef6b6f',
        'primary_dark_color' => '#d94c51',
        'primary_light_color' => '#fff1f1',
        'secondary_color' => '#16191f',
        'accent_color' => '#f8dfe1',
        'heading_color' => '#17191f',
        'body_color' => '#5d6470',
        'muted_color' => '#7b838f',
        'border_color' => '#eef0f4',
        'background_color' => '#ffffff',
        'section_soft_bg' => '#fff6f6',
        'footer_bg_color' => '#17191f',
        'footer_text_color' => '#cfd3dc',
        'footer_heading_color' => '#ffffff',
        'header_bg_color' => '#ffffff',
        'header_text_color' => '#17191f',
        'font_body' => 'system',
        'font_heading' => 'system',
        'base_font_size' => '15px',
        'body_line_height' => '1.6',
        'heading_line_height' => '1.14',
        'h1_size_desktop' => '58px',
        'h1_size_mobile' => '34px',
        'h2_size_desktop' => '34px',
        'h2_size_mobile' => '24px',
        'font_weight_heading' => '800',
        'font_weight_button' => '800',
        'container_width' => '1220px',
        'content_width' => '920px',
        'section_padding_y' => '72px',
        'section_padding_y_mobile' => '46px',
        'grid_gap' => '18px',
        'page_hero_padding_y' => '70px',
        'border_radius_sm' => '6px',
        'border_radius_md' => '12px',
        'border_radius_lg' => '22px',
        'radius' => '12px',
        'header_layout' => 'logo_left_menu_center_cta_right',
        'header_style' => 'white',
        'header_height' => '72px',
        'header_sticky' => '1',
        'header_blur' => '1',
        'header_shadow' => '1',
        'header_full_width' => '0',
        'show_search' => '1',
        'show_language_switcher' => '1',
        'show_header_cta' => '1',
        'header_cta_text' => 'Get a Quote',
        'header_cta_url' => '#inquiry',
        'mobile_header_style' => 'offcanvas',
        'button_radius' => '999px',
        'button_height' => '42px',
        'button_padding_x' => '20px',
        'button_style' => 'pill',
        'button_shadow' => '1',
        'button_hover_effect' => 'lift',
        'card_radius' => '14px',
        'card_shadow' => 'soft',
        'card_border' => '1',
        'card_hover_effect' => 'lift',
        'product_card_style' => 'clean',
        'category_card_style' => 'image_top',
        'news_card_style' => 'image_left',
        'footer_layout' => 'four_columns',
        'show_footer_logo' => '1',
        'show_footer_products' => '1',
        'show_footer_links' => '1',
        'show_footer_contact' => '1',
        'show_footer_social' => '1',
        'show_footer_subscribe' => '0',
        'contact_phone' => '+86 188 0000 8888',
        'contact_email' => 'sales@example.com',
        'company_address' => '88 Industrial Road, Guangzhou, China',
        'footer_description' => 'Manufacturer of reliable kitchen appliances for OEM and ODM brands worldwide.',
        'copyright_text' => 'Copyright (c) 2026 Aurelia Manufacturing. All rights reserved.',
        'social_links' => "LinkedIn|https://linkedin.com\nYouTube|https://youtube.com\nFacebook|https://facebook.com",
        'enable_animation' => '1',
        'animation_style' => 'fade_up',
        'animation_duration' => '600ms',
        'animation_delay_step' => '80ms',
        'enable_counter_anim' => '1',
        'enable_hover_anim' => '1',
        'mobile_breakpoint' => '760px',
        'mobile_menu_style' => 'offcanvas',
        'mobile_show_cta' => '0',
        'mobile_show_language' => '1',
        'mobile_hero_compact' => '1',
        'mobile_product_columns' => '1',
        'tablet_product_columns' => '3',
        'desktop_product_columns' => '4',
        'floating_contact' => '1',
    ];
}

function cb_get_options()
{
    return wp_parse_args((array) get_option('cb_theme_options', []), cb_default_theme_options());
}

function cb_get_option($key, $default = '')
{
    $options = cb_get_options();
    return isset($options[$key]) ? $options[$key] : $default;
}

function cb_default_string_translations()
{
    return [
        'home' => ['en' => 'Home', 'zh' => '首页'],
        'about_us' => ['en' => 'About Us', 'zh' => '关于我们'],
        'products' => ['en' => 'Products', 'zh' => '产品'],
        'capabilities' => ['en' => 'Capabilities', 'zh' => '制造能力'],
        'news' => ['en' => 'News', 'zh' => '新闻'],
        'contact_us' => ['en' => 'Contact Us', 'zh' => '联系我们'],
        'get_quote' => ['en' => 'Get a Quote', 'zh' => '获取报价'],
        'learn_more' => ['en' => 'Learn More', 'zh' => '了解更多'],
        'view_details' => ['en' => 'View Details', 'zh' => '查看详情'],
        'read_more' => ['en' => 'Read More', 'zh' => '阅读更多'],
        'send_inquiry' => ['en' => 'Send Inquiry', 'zh' => '发送询盘'],
        'name' => ['en' => 'Full Name', 'zh' => '姓名'],
        'company' => ['en' => 'Company Name', 'zh' => '公司名称'],
        'email' => ['en' => 'Email', 'zh' => '邮箱'],
        'phone' => ['en' => 'Phone / WhatsApp', 'zh' => '电话 / WhatsApp'],
        'country' => ['en' => 'Country', 'zh' => '国家'],
        'quantity' => ['en' => 'Quantity', 'zh' => '数量'],
        'message' => ['en' => 'Message', 'zh' => '留言'],
        'submit' => ['en' => 'Submit', 'zh' => '提交'],
        'all_products' => ['en' => 'All Products', 'zh' => '所有产品'],
        'related_products' => ['en' => 'Related Products', 'zh' => '相关产品'],
    ];
}

function cb_t($key)
{
    $lang = cb_get_current_language();
    $translations = wp_parse_args((array) get_option('cb_string_translations', []), cb_default_string_translations());
    if (isset($translations[$key][$lang]) && $translations[$key][$lang] !== '') {
        return $translations[$key][$lang];
    }
    return $translations[$key]['en'] ?? $key;
}

function cb_sanitize_textarea_lines($value)
{
    $lines = array_map('sanitize_text_field', preg_split('/\r\n|\r|\n/', (string) $value));
    return implode("\n", array_filter($lines));
}

function cb_parse_lines($value)
{
    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
        $parts = array_map('trim', explode('|', $line, 3));
        if (!empty($parts[0])) {
            $items[] = ['label' => $parts[0], 'value' => $parts[1] ?? '', 'image' => $parts[2] ?? ''];
        }
    }
    return $items;
}

function cb_sanitize_css_size($value, $default = '')
{
    $value = trim((string) $value);
    if ($value === '') {
        return $default;
    }
    if (preg_match('/^\d+(\.\d+)?(px|rem|em|%|vh|vw|ms|s)?$/', $value)) {
        return $value;
    }
    return $default;
}

function cb_sanitize_choice($value, $allowed, $default)
{
    $value = sanitize_key((string) $value);
    return in_array($value, $allowed, true) ? $value : $default;
}

function cb_render_dynamic_css_variables()
{
    $defaults = cb_default_theme_options();
    $options = cb_get_options();
    $colors = [
        '--cb-primary' => 'primary_color',
        '--cb-primary-dark' => 'primary_dark_color',
        '--cb-primary-light' => 'primary_light_color',
        '--cb-secondary' => 'secondary_color',
        '--cb-accent' => 'accent_color',
        '--cb-heading' => 'heading_color',
        '--cb-body' => 'body_color',
        '--cb-muted' => 'muted_color',
        '--cb-border' => 'border_color',
        '--cb-bg' => 'background_color',
        '--cb-soft-bg' => 'section_soft_bg',
        '--cb-footer-bg' => 'footer_bg_color',
        '--cb-footer-text' => 'footer_text_color',
        '--cb-footer-heading' => 'footer_heading_color',
        '--cb-header-bg' => 'header_bg_color',
        '--cb-header-text' => 'header_text_color',
    ];
    $sizes = [
        '--cb-container' => 'container_width',
        '--cb-content' => 'content_width',
        '--cb-section-padding-y' => 'section_padding_y',
        '--cb-section-padding-y-mobile' => 'section_padding_y_mobile',
        '--cb-grid-gap' => 'grid_gap',
        '--cb-page-hero-padding-y' => 'page_hero_padding_y',
        '--cb-radius-sm' => 'border_radius_sm',
        '--cb-radius-md' => 'border_radius_md',
        '--cb-radius-lg' => 'border_radius_lg',
        '--cb-radius' => 'card_radius',
        '--cb-card-radius' => 'card_radius',
        '--cb-button-radius' => 'button_radius',
        '--cb-button-height' => 'button_height',
        '--cb-button-padding-x' => 'button_padding_x',
        '--cb-base-font-size' => 'base_font_size',
        '--cb-body-line-height' => 'body_line_height',
        '--cb-heading-line-height' => 'heading_line_height',
        '--cb-h1-desktop' => 'h1_size_desktop',
        '--cb-h1-mobile' => 'h1_size_mobile',
        '--cb-h2-desktop' => 'h2_size_desktop',
        '--cb-h2-mobile' => 'h2_size_mobile',
        '--cb-heading-weight' => 'font_weight_heading',
        '--cb-button-weight' => 'font_weight_button',
        '--cb-header-height' => 'header_height',
        '--cb-animation-duration' => 'animation_duration',
        '--cb-animation-delay-step' => 'animation_delay_step',
        '--cb-mobile-product-columns' => 'mobile_product_columns',
        '--cb-tablet-product-columns' => 'tablet_product_columns',
        '--cb-desktop-product-columns' => 'desktop_product_columns',
    ];

    $css = ':root{';
    foreach ($colors as $var => $key) {
        $css .= $var . ':' . (sanitize_hex_color($options[$key] ?? '') ?: $defaults[$key]) . ';';
    }
    foreach ($sizes as $var => $key) {
        $css .= $var . ':' . cb_sanitize_css_size($options[$key] ?? '', $defaults[$key] ?? '') . ';';
    }
    $css .= '}';
    echo '<style id="cb-dynamic-theme-vars">' . esc_html($css) . '</style>' . "\n";
}
