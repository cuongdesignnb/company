<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_ui_get($key, $context = '', $post_id = 0, $default = null)
{
    if ($post_id) {
        $page_ui = get_post_meta($post_id, '_cb_page_ui', true);
        if (is_array($page_ui) && array_key_exists($key, $page_ui)) {
            return $page_ui[$key];
        }
    }
    $templates = cb_get_group_options('cb_template_settings', cb_default_template_settings());
    if ($context && isset($templates[$context]) && is_array($templates[$context]) && array_key_exists($key, $templates[$context])) {
        return $templates[$context][$key];
    }
    $global = cb_get_options();
    return array_key_exists($key, $global) ? $global[$key] : $default;
}

function cb_page_ui_context($post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    if (get_post_type($post_id) !== 'page') {
        return 'standard_page';
    }
    $special_pages = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $contexts = ['home' => 'home', 'about' => 'about_page', 'contact' => 'contact_page'];
    foreach ($special_pages as $pages) {
        foreach ($contexts as $role => $context) {
            if ((int) ($pages[$role] ?? 0) === (int) $post_id) {
                return $context;
            }
        }
    }
    return 'standard_page';
}

function cb_page_ui_schema()
{
    return [
        'layout' => [
            'label' => __('Bố cục', 'cb-company-core'),
            'fields' => [
                'page_layout' => [__('Bố cục trang', 'cb-company-core'), 'select', ['full_width' => __('Toàn chiều rộng', 'cb-company-core'), 'sidebar' => __('Có sidebar', 'cb-company-core'), 'no_sidebar' => __('Không sidebar', 'cb-company-core')]],
                'hide_header' => [__('Ẩn header', 'cb-company-core'), 'checkbox'],
                'hide_footer' => [__('Ẩn footer', 'cb-company-core'), 'checkbox'],
                'container_width' => [__('Chiều rộng container riêng', 'cb-company-core'), 'text'],
                'padding_top' => [__('Padding phía trên', 'cb-company-core'), 'text'],
                'padding_bottom' => [__('Padding phía dưới', 'cb-company-core'), 'text'],
            ],
        ],
        'banner' => [
            'label' => __('Banner', 'cb-company-core'),
            'fields' => [
                'show_banner' => [__('Hiện banner', 'cb-company-core'), 'checkbox'],
                'banner_title' => [__('Tiêu đề banner', 'cb-company-core'), 'text'],
                'banner_description' => [__('Mô tả banner', 'cb-company-core'), 'textarea'],
                'banner_image' => [__('Hình nền', 'cb-company-core'), 'image'],
                'banner_overlay' => [__('Độ phủ overlay (%)', 'cb-company-core'), 'number'],
                'banner_height_desktop' => [__('Chiều cao desktop', 'cb-company-core'), 'text'],
                'banner_height_mobile' => [__('Chiều cao mobile', 'cb-company-core'), 'text'],
                'show_breadcrumb' => [__('Hiện breadcrumb', 'cb-company-core'), 'checkbox'],
            ],
        ],
        'colors' => [
            'label' => __('Màu và nền', 'cb-company-core'),
            'fields' => [
                'page_background' => [__('Màu nền trang', 'cb-company-core'), 'color'],
                'page_text_color' => [__('Màu nội dung', 'cb-company-core'), 'color'],
                'page_heading_color' => [__('Màu tiêu đề', 'cb-company-core'), 'color'],
                'page_background_image' => [__('Ảnh nền trang', 'cb-company-core'), 'image'],
            ],
        ],
        'responsive' => [
            'label' => __('Responsive', 'cb-company-core'),
            'fields' => [
                'hide_banner_mobile' => [__('Ẩn banner trên mobile', 'cb-company-core'), 'checkbox'],
                'mobile_section_order' => [__('Thứ tự section trên mobile', 'cb-company-core'), 'text'],
                'mobile_spacing' => [__('Khoảng cách mobile', 'cb-company-core'), 'text'],
            ],
        ],
        'seo' => [
            'label' => __('SEO và chia sẻ', 'cb-company-core'),
            'fields' => [
                'seo_title' => [__('SEO title', 'cb-company-core'), 'text'],
                'seo_description' => [__('SEO description', 'cb-company-core'), 'textarea'],
                'og_image' => [__('Open Graph image', 'cb-company-core'), 'image'],
                'canonical' => [__('Canonical URL', 'cb-company-core'), 'url'],
                'noindex' => [__('Không lập chỉ mục', 'cb-company-core'), 'checkbox'],
            ],
        ],
        'advanced' => [
            'label' => __('Nâng cao', 'cb-company-core'),
            'fields' => ['body_class' => [__('CSS class cho trang', 'cb-company-core'), 'text']],
        ],
    ];
}

function cb_sanitize_page_ui($input)
{
    $clean = [];
    $input = is_array($input) ? wp_unslash($input) : [];
    foreach (cb_page_ui_schema() as $tab) {
        foreach ($tab['fields'] as $key => $field) {
            if (empty($input[$key]['override'])) {
                continue;
            }
            $raw = $input[$key]['value'] ?? '';
            $type = $field[1];
            if ($type === 'checkbox') {
                $clean[$key] = $raw === '1' ? '1' : '0';
            } elseif ($type === 'color') {
                $clean[$key] = sanitize_hex_color($raw) ?: '';
            } elseif ($type === 'url') {
                $clean[$key] = esc_url_raw($raw);
            } elseif ($type === 'number') {
                $clean[$key] = (string) absint($raw);
            } elseif ($type === 'textarea') {
                $clean[$key] = sanitize_textarea_field($raw);
            } elseif ($type === 'image') {
                $clean[$key . '_id'] = absint($input[$key]['image_id'] ?? 0);
                $clean[$key] = esc_url_raw($raw);
            } else {
                $clean[$key] = sanitize_text_field($raw);
            }
        }
    }
    return $clean;
}
