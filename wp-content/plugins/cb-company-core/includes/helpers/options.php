<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_default_theme_options()
{
    return [
        'logo_text' => 'AURELIA',
        'logo_subtext' => 'MANUFACTURING',
        'primary_color' => '#ef3f45',
        'secondary_color' => '#16191f',
        'accent_color' => '#f8dfe1',
        'heading_color' => '#17191f',
        'body_color' => '#5d6470',
        'background_color' => '#ffffff',
        'container_width' => '1220px',
        'radius' => '8px',
        'header_cta_text' => 'Get a Quote',
        'header_cta_url' => '#inquiry',
        'contact_phone' => '+86 188 0000 8888',
        'contact_email' => 'sales@example.com',
        'company_address' => '88 Industrial Road, Guangzhou, China',
        'footer_description' => 'Manufacturer of reliable kitchen appliances for OEM and ODM brands worldwide.',
        'copyright_text' => 'Copyright © 2026 Aurelia Manufacturing. All rights reserved.',
        'social_links' => "LinkedIn|https://linkedin.com\nYouTube|https://youtube.com\nFacebook|https://facebook.com",
        'floating_contact' => '1',
        'show_language_switcher' => '1',
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
