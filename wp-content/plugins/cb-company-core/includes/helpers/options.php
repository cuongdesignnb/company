<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_default_design_settings()
{
    return [
        'logo_id' => '', 'logo_url' => '', 'mobile_logo_id' => '', 'mobile_logo_url' => '',
        'footer_logo_id' => '', 'footer_logo_url' => '', 'favicon_id' => '', 'favicon_url' => '',
        'logo_text' => '', 'logo_subtext' => '', 'brand_mark_text' => '',
        'brand_description' => '', 'show_logo_text' => '0',
        'primary_color' => '#ef6b6f', 'primary_dark_color' => '#d94c51', 'primary_light_color' => '#fff1f1',
        'secondary_color' => '#16191f', 'accent_color' => '#f8dfe1', 'heading_color' => '#17191f',
        'body_color' => '#5d6470', 'muted_color' => '#7b838f', 'border_color' => '#eef0f4',
        'background_color' => '#ffffff', 'section_soft_bg' => '#fff6f6',
        'font_body' => 'system', 'font_heading' => 'system', 'base_font_size' => '15px',
        'body_line_height' => '1.6', 'heading_line_height' => '1.14',
        'h1_size_desktop' => '58px', 'h1_size_mobile' => '34px',
        'h2_size_desktop' => '34px', 'h2_size_mobile' => '24px',
        'font_weight_heading' => '800', 'font_weight_button' => '800',
        'container_width' => '1220px', 'content_width' => '920px',
        'section_padding_y' => '72px', 'section_padding_y_mobile' => '46px', 'grid_gap' => '18px',
        'page_hero_padding_y' => '70px', 'border_radius_sm' => '6px',
        'border_radius_md' => '12px', 'border_radius_lg' => '22px',
        'button_radius' => '999px', 'button_height' => '42px', 'button_padding_x' => '20px',
        'button_style' => 'pill', 'button_shadow' => '1', 'button_hover_effect' => 'lift',
        'card_radius' => '14px', 'card_shadow' => 'soft', 'card_border' => '1',
        'card_hover_effect' => 'lift', 'product_card_style' => 'clean',
        'category_card_style' => 'image_top', 'news_card_style' => 'image_left',
        'mobile_breakpoint' => '760px', 'mobile_product_columns' => '1',
        'tablet_product_columns' => '3', 'desktop_product_columns' => '4',
    ];
}

function cb_default_header_settings()
{
    return [
        'header_layout' => 'logo_left_menu_center_cta_right', 'header_style' => 'white',
        'header_height' => '72px', 'header_sticky' => '1', 'header_blur' => '1',
        'header_shadow' => '1', 'header_full_width' => '0', 'header_bg_color' => '#ffffff',
        'header_text_color' => '#17191f', 'show_search' => '1',
        'show_language_switcher' => '1', 'show_header_cta' => '0',
        'header_cta_text' => '', 'header_cta_url' => '',
        'mobile_header_style' => 'offcanvas', 'mobile_menu_style' => 'offcanvas',
        'mobile_show_cta' => '0', 'mobile_show_language' => '1', 'mobile_hero_compact' => '1',
    ];
}

function cb_default_footer_settings()
{
    return [
        'footer_layout' => 'four_columns', 'footer_bg_color' => '#17191f', 'footer_background_image_id' => '', 'footer_background_image' => '',
        'footer_text_color' => '#cfd3dc', 'footer_heading_color' => '#ffffff',
        'show_footer_logo' => '0', 'show_footer_products' => '0', 'show_footer_links' => '0',
        'show_footer_contact' => '0', 'show_footer_social' => '0', 'show_footer_subscribe' => '0',
        'contact_phone' => '', 'contact_whatsapp' => '', 'contact_email' => '', 'contact_wechat_id' => 'wechat',
        'contact_wechat_qr_id' => '', 'contact_wechat_qr' => '', 'company_address' => '',
        'footer_description' => '', 'copyright_text' => '', 'social_links' => [],
        'floating_contact' => '0',
    ];
}

function cb_default_template_settings()
{
    $base = [
        'layout' => 'default', 'container_width' => '', 'sidebar' => 'none',
        'show_breadcrumb' => '1', 'background_color' => '', 'text_color' => '',
        'columns_desktop' => '4', 'columns_tablet' => '3', 'columns_mobile' => '1',
        'seo_title' => '', 'seo_description' => '',
    ];
    return [
        'home' => $base,
        'standard_page' => $base,
        'about_page' => $base,
        'product_archive' => $base + ['product_card_style' => 'clean'],
        'product_category' => $base + ['product_card_style' => 'clean'],
        'product_single' => $base + [
            'product_layout' => 'gallery_left', 'sticky_summary' => '1', 'column_gap' => '42px',
            'show_short_description' => '1', 'show_quick_specs' => '1', 'show_catalog' => '1',
            'show_video' => '1', 'show_related_products' => '1', 'show_inquiry' => '1',
            'show_bottom_cta' => '1', 'mobile_gallery_first' => '1', 'mobile_sticky_cta' => '0',
        ],
        'factory_archive' => $base, 'factory_single' => $base,
        'case_archive' => $base, 'case_single' => $base,
        'video_archive' => $base, 'video_single' => $base,
        'news_archive' => $base, 'news_category' => $base, 'news_single' => $base,
        'contact_page' => $base, 'search' => $base, '404' => $base,
    ];
}

function cb_default_theme_options()
{
    return array_merge(cb_default_design_settings(), cb_default_header_settings(), cb_default_footer_settings());
}

function cb_get_group_options($option, $defaults = [])
{
    $stored = get_option($option, []);
    return wp_parse_args(is_array($stored) ? $stored : [], $defaults);
}

function cb_get_options()
{
    $legacy = cb_get_group_options('cb_theme_options');
    $groups = array_merge(
        cb_get_group_options('cb_design_settings', cb_default_design_settings()),
        cb_get_group_options('cb_header_settings', cb_default_header_settings()),
        cb_get_group_options('cb_footer_settings', cb_default_footer_settings())
    );
    return wp_parse_args($groups, wp_parse_args($legacy, cb_default_theme_options()));
}

function cb_get_option($key, $default = '')
{
    $options = cb_get_options();
    return array_key_exists($key, $options) ? $options[$key] : $default;
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
        'interested_product' => ['en' => 'Interested Product', 'zh' => '感兴趣的产品'],
        'message' => ['en' => 'Message', 'zh' => '留言'],
        'inquiry_consent' => ['en' => 'I agree to be contacted about this inquiry.', 'zh' => '我同意就此询价与我联系。'],
        'submit' => ['en' => 'Submit', 'zh' => '提交'],
        'all_products' => ['en' => 'All Products', 'zh' => '所有产品'],
        'related_products' => ['en' => 'Related Products', 'zh' => '相关产品'],
        'previous_slide' => ['en' => 'Previous slide', 'zh' => '上一张'],
        'next_slide' => ['en' => 'Next slide', 'zh' => '下一张'],
        'go_to_slide' => ['en' => 'Go to slide', 'zh' => '转到幻灯片'],
        'carousel' => ['en' => 'Carousel', 'zh' => '轮播图'],
    ];
}

function cb_t($key)
{
    $lang = cb_get_current_language();
    $translations = wp_parse_args((array) get_option('cb_string_translations', []), cb_default_string_translations());
    return $translations[$key][$lang] ?? $translations[$key]['en'] ?? $key;
}

function cb_sanitize_textarea_lines($value)
{
    $lines = array_map('sanitize_text_field', preg_split('/\r\n|\r|\n/', (string) $value));
    return implode("\n", array_filter($lines));
}

function cb_parse_lines($value)
{
    if (is_array($value)) {
        return array_map(static function ($item) {
            $item = (array) $item;
            return [
                'label' => $item['title'] ?? $item['label'] ?? '',
                'value' => $item['description'] ?? $item['value'] ?? '',
                'image' => $item['image_url'] ?? $item['image'] ?? '',
                'url' => $item['url'] ?? '',
            ];
        }, $value);
    }
    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
        $parts = array_map('trim', explode('|', $line, 3));
        if (!empty($parts[0])) {
            $items[] = ['label' => $parts[0], 'value' => $parts[1] ?? '', 'image' => $parts[2] ?? '', 'url' => ''];
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
    return preg_match('/^\d+(\.\d+)?(px|rem|em|%|vh|vw|ms|s)?$/', $value) ? $value : $default;
}

function cb_sanitize_choice($value, $allowed, $default)
{
    $value = sanitize_key((string) $value);
    return in_array($value, $allowed, true) ? $value : $default;
}

function cb_sanitize_settings_group($input, $defaults)
{
    $input = is_array($input) ? wp_unslash($input) : [];
    $clean = [];
    foreach ($defaults as $key => $default) {
        $value = $input[$key] ?? $default;
        if (is_array($default)) {
            $clean[$key] = cb_sanitize_repeater_items($value);
        } elseif (str_ends_with($key, '_id')) {
            $clean[$key] = (string) absint($value);
        } elseif (str_contains($key, 'url') || in_array($key, ['footer_background_image', 'contact_wechat_qr', 'default_og_image'], true)) {
            $clean[$key] = esc_url_raw($value);
        } elseif (str_contains($key, 'color')) {
            $clean[$key] = sanitize_hex_color($value) ?: '';
        } elseif (preg_match('/^(show_|enable_|mobile_show_|header_|footer_|floating_)/', $key) && in_array((string) $default, ['0', '1'], true)) {
            $clean[$key] = $value === '1' ? '1' : '0';
        } elseif (preg_match('/(width|height|size|padding|radius|gap|duration|line_height)$/', $key)) {
            $clean[$key] = cb_sanitize_css_size($value, $default);
        } elseif (str_contains($key, 'email')) {
            $clean[$key] = sanitize_email($value);
        } elseif (str_contains($key, 'description') || str_contains($key, 'address')) {
            $clean[$key] = sanitize_textarea_field($value);
        } else {
            $clean[$key] = sanitize_text_field($value);
        }
    }
    return $clean;
}

function cb_sanitize_repeater_items($items)
{
    $clean = [];
    foreach ((array) $items as $item) {
        $item = (array) $item;
        $clean[] = [
            'title' => sanitize_text_field($item['title'] ?? ''),
            'description' => sanitize_textarea_field($item['description'] ?? ''),
            'image_id' => absint($item['image_id'] ?? 0),
            'image_url' => esc_url_raw($item['image_url'] ?? ''),
            'url' => esc_url_raw($item['url'] ?? ''),
        ];
    }
    return array_values(array_filter($clean, static fn($item) => implode('', array_map('strval', $item)) !== '0'));
}

function cb_render_dynamic_css_variables()
{
    $map = [
        '--cb-primary' => 'primary_color', '--cb-primary-dark' => 'primary_dark_color',
        '--cb-primary-light' => 'primary_light_color', '--cb-secondary' => 'secondary_color',
        '--cb-accent' => 'accent_color', '--cb-heading' => 'heading_color', '--cb-body' => 'body_color',
        '--cb-muted' => 'muted_color', '--cb-border' => 'border_color', '--cb-bg' => 'background_color',
        '--cb-soft-bg' => 'section_soft_bg', '--cb-footer-bg' => 'footer_bg_color',
        '--cb-footer-text' => 'footer_text_color', '--cb-footer-heading' => 'footer_heading_color',
        '--cb-header-bg' => 'header_bg_color', '--cb-header-text' => 'header_text_color',
        '--cb-container' => 'container_width', '--cb-content' => 'content_width',
        '--cb-section-padding-y' => 'section_padding_y', '--cb-section-padding-y-mobile' => 'section_padding_y_mobile',
        '--cb-grid-gap' => 'grid_gap', '--cb-page-hero-padding-y' => 'page_hero_padding_y',
        '--cb-radius-sm' => 'border_radius_sm', '--cb-radius-md' => 'border_radius_md',
        '--cb-radius-lg' => 'border_radius_lg', '--cb-radius' => 'card_radius',
        '--cb-card-radius' => 'card_radius', '--cb-button-radius' => 'button_radius',
        '--cb-button-height' => 'button_height', '--cb-button-padding-x' => 'button_padding_x',
        '--cb-base-font-size' => 'base_font_size', '--cb-body-line-height' => 'body_line_height',
        '--cb-heading-line-height' => 'heading_line_height', '--cb-h1-desktop' => 'h1_size_desktop',
        '--cb-h1-mobile' => 'h1_size_mobile', '--cb-h2-desktop' => 'h2_size_desktop',
        '--cb-h2-mobile' => 'h2_size_mobile', '--cb-heading-weight' => 'font_weight_heading',
        '--cb-button-weight' => 'font_weight_button', '--cb-header-height' => 'header_height',
        '--cb-mobile-product-columns' => 'mobile_product_columns',
        '--cb-tablet-product-columns' => 'tablet_product_columns',
        '--cb-desktop-product-columns' => 'desktop_product_columns',
    ];
    $css = ':root{--cb-font-body:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI","Noto Sans",Arial,sans-serif;';
    $css .= '--cb-font-heading:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI","Noto Sans",Arial,sans-serif;';
    foreach ($map as $variable => $key) {
        $css .= $variable . ':' . esc_html((string) cb_get_option($key)) . ';';
    }
    $css .= '}html:lang(zh-CN){--cb-font-body:"PingFang SC","Microsoft YaHei","Noto Sans SC","Noto Sans CJK SC",system-ui,sans-serif;--cb-font-heading:var(--cb-font-body);}';
    echo '<style id="cb-dynamic-theme-vars">' . $css . '</style>' . "\n";
}
