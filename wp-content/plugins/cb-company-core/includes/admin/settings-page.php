<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_admin_menu_items()
{
    return [
        'cb-company' => [__('Tổng quan', 'cb-company-core'), 'dashicons-dashboard'],
        'cb-company-design' => [__('Thiết kế chung', 'cb-company-core'), 'dashicons-art'],
        'cb-company-header' => [__('Header', 'cb-company-core'), 'dashicons-align-wide'],
        'cb-company-footer' => [__('Footer', 'cb-company-core'), 'dashicons-align-full-width'],
        'cb-company-templates' => [__('Mẫu trang', 'cb-company-core'), 'dashicons-layout'],
        'cb-company-special-pages' => [__('Trang đặc biệt', 'cb-company-core'), 'dashicons-admin-page'],
        'cb-company-content' => [__('Nội dung trang', 'cb-company-core'), 'dashicons-edit-page'],
        'cb-company-page-builder' => [__('Trình dựng trang', 'cb-company-core'), 'dashicons-screenoptions'],
        'cb-company-strings' => [__('Chuỗi giao diện', 'cb-company-core'), 'dashicons-translation'],
        'cb-company-multilingual' => [__('Đa ngôn ngữ', 'cb-company-core'), 'dashicons-admin-site-alt3'],
        'cb-company-forms' => [__('Biểu mẫu và email', 'cb-company-core'), 'dashicons-email-alt2'],
        'cb-company-seo' => [__('SEO', 'cb-company-core'), 'dashicons-chart-line'],
        'cb-company-performance' => [__('Hiệu năng', 'cb-company-core'), 'dashicons-performance'],
        'cb-company-tools' => [__('Công cụ', 'cb-company-core'), 'dashicons-admin-tools'],
    ];
}

function cb_register_settings_pages()
{
    add_menu_page(__('CB Company', 'cb-company-core'), __('CB Company', 'cb-company-core'), 'manage_options', 'cb-company', 'cb_render_dashboard_page', 'dashicons-building', 58);
    $callbacks = [
        'cb-company' => 'cb_render_dashboard_page', 'cb-company-design' => 'cb_render_design_settings_page',
        'cb-company-header' => 'cb_render_header_settings_page', 'cb-company-footer' => 'cb_render_footer_settings_page',
        'cb-company-templates' => 'cb_render_template_settings_page', 'cb-company-special-pages' => 'cb_render_special_pages_page',
        'cb-company-content' => 'cb_render_content_pages_page',
        'cb-company-page-builder' => 'cb_render_page_builder_index_page', 'cb-company-strings' => 'cb_render_string_translations_page',
        'cb-company-multilingual' => 'cb_render_multilingual_settings_page', 'cb-company-forms' => 'cb_render_form_settings_page',
        'cb-company-seo' => 'cb_render_seo_settings_page', 'cb-company-performance' => 'cb_render_performance_settings_page',
        'cb-company-tools' => 'cb_render_tools_page',
    ];
    foreach (cb_admin_menu_items() as $slug => $item) {
        add_submenu_page('cb-company', $item[0], $item[0], 'manage_options', $slug, $callbacks[$slug]);
    }
    add_submenu_page('cb-company', __('Chứng nhận', 'cb-company-core'), __('Chứng nhận', 'cb-company-core'), 'edit_posts', 'edit.php?post_type=certificate');
    add_submenu_page('cb-company', __('Nhóm chứng nhận', 'cb-company-core'), __('Nhóm chứng nhận', 'cb-company-core'), 'manage_categories', 'edit-tags.php?taxonomy=certificate_category&post_type=certificate');
}

function cb_register_settings()
{
    register_setting('cb_design_settings_group', 'cb_design_settings', ['sanitize_callback' => 'cb_sanitize_design_settings']);
    register_setting('cb_header_settings_group', 'cb_header_settings', ['sanitize_callback' => 'cb_sanitize_header_settings']);
    register_setting('cb_footer_settings_group', 'cb_footer_settings', ['sanitize_callback' => 'cb_sanitize_footer_settings']);
    register_setting('cb_template_settings_group', 'cb_template_settings', ['sanitize_callback' => 'cb_sanitize_template_settings']);
    register_setting('cb_special_pages_group', 'cb_special_pages', ['sanitize_callback' => 'cb_sanitize_special_pages']);
    register_setting('cb_string_translations_group', 'cb_string_translations', ['sanitize_callback' => 'cb_sanitize_string_translations']);
    register_setting('cb_form_settings_group', 'cb_form_settings', ['sanitize_callback' => 'cb_sanitize_form_settings']);
    register_setting('cb_seo_settings_group', 'cb_seo_settings', ['sanitize_callback' => 'cb_sanitize_seo_settings']);
    register_setting('cb_performance_settings_group', 'cb_performance_settings', ['sanitize_callback' => 'cb_sanitize_performance_settings']);
}

function cb_design_settings_schema()
{
    return [
        'identity' => ['label' => __('Nhận diện', 'cb-company-core'), 'fields' => [
            ['logo', 'image_pair', __('Logo chính', 'cb-company-core'), __('Logo dùng trên header desktop.', 'cb-company-core')],
            ['mobile_logo', 'image_pair', __('Logo mobile', 'cb-company-core')],
            ['footer_logo', 'image_pair', __('Logo footer', 'cb-company-core')],
            ['favicon', 'image_pair', __('Favicon', 'cb-company-core')],
            ['logo_text', 'text', __('Tên thương hiệu', 'cb-company-core')],
            ['logo_subtext', 'text', __('Dòng mô tả thương hiệu', 'cb-company-core')],
            ['show_logo_text', 'checkbox', __('Hiện chữ cạnh logo', 'cb-company-core')],
        ]],
        'colors' => ['label' => __('Màu sắc', 'cb-company-core'), 'fields' => [
            ['primary_color', 'color', __('Màu chủ đạo', 'cb-company-core')], ['primary_dark_color', 'color', __('Màu chủ đạo đậm', 'cb-company-core')],
            ['primary_light_color', 'color', __('Màu chủ đạo nhạt', 'cb-company-core')], ['secondary_color', 'color', __('Màu phụ', 'cb-company-core')],
            ['accent_color', 'color', __('Màu nhấn', 'cb-company-core')], ['heading_color', 'color', __('Màu tiêu đề', 'cb-company-core')],
            ['body_color', 'color', __('Màu nội dung', 'cb-company-core')], ['muted_color', 'color', __('Màu muted', 'cb-company-core')],
            ['border_color', 'color', __('Màu đường viền', 'cb-company-core')], ['background_color', 'color', __('Màu nền website', 'cb-company-core')],
            ['section_soft_bg', 'color', __('Màu nền section nhẹ', 'cb-company-core')],
        ]],
        'typography' => ['label' => __('Typography', 'cb-company-core'), 'fields' => [
            ['font_body', 'select', __('Font nội dung', 'cb-company-core'), '', ['system' => __('Font hệ thống đa ngôn ngữ', 'cb-company-core'), 'serif' => __('Serif', 'cb-company-core')]],
            ['font_heading', 'select', __('Font tiêu đề', 'cb-company-core'), '', ['system' => __('Font hệ thống đa ngôn ngữ', 'cb-company-core'), 'serif' => __('Serif', 'cb-company-core')]],
            ['base_font_size', 'dimension', __('Cỡ chữ cơ bản', 'cb-company-core')], ['body_line_height', 'dimension', __('Line height', 'cb-company-core')],
            ['h1_size_desktop', 'dimension', __('Cỡ H1 desktop', 'cb-company-core')], ['h1_size_mobile', 'dimension', __('Cỡ H1 mobile', 'cb-company-core')],
            ['h2_size_desktop', 'dimension', __('Cỡ H2 desktop', 'cb-company-core')], ['h2_size_mobile', 'dimension', __('Cỡ H2 mobile', 'cb-company-core')],
            ['font_weight_heading', 'number', __('Font weight tiêu đề', 'cb-company-core'), '', ['min' => 100, 'max' => 900, 'step' => 100]],
            ['font_weight_button', 'number', __('Font weight button', 'cb-company-core'), '', ['min' => 100, 'max' => 900, 'step' => 100]],
        ]],
        'layout' => ['label' => __('Bố cục', 'cb-company-core'), 'fields' => [
            ['container_width', 'dimension', __('Chiều rộng container', 'cb-company-core')], ['content_width', 'dimension', __('Chiều rộng nội dung bài viết', 'cb-company-core')],
            ['section_padding_y', 'dimension', __('Khoảng cách section desktop', 'cb-company-core')], ['section_padding_y_mobile', 'dimension', __('Khoảng cách section mobile', 'cb-company-core')],
            ['grid_gap', 'dimension', __('Khoảng cách grid', 'cb-company-core')], ['border_radius_sm', 'dimension', __('Border radius nhỏ', 'cb-company-core')],
            ['border_radius_md', 'dimension', __('Border radius vừa', 'cb-company-core')], ['border_radius_lg', 'dimension', __('Border radius lớn', 'cb-company-core')],
        ]],
        'buttons' => ['label' => __('Nút', 'cb-company-core'), 'fields' => [
            ['button_style', 'select', __('Kiểu nút', 'cb-company-core'), '', ['rounded' => __('Bo góc', 'cb-company-core'), 'pill' => __('Dạng viên thuốc', 'cb-company-core'), 'square' => __('Vuông', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core')]],
            ['button_height', 'dimension', __('Chiều cao', 'cb-company-core')], ['button_padding_x', 'dimension', __('Padding ngang', 'cb-company-core')],
            ['button_radius', 'dimension', __('Border radius', 'cb-company-core')], ['button_shadow', 'checkbox', __('Shadow', 'cb-company-core')],
            ['button_hover_effect', 'select', __('Hover effect', 'cb-company-core'), '', ['none' => __('Không', 'cb-company-core'), 'lift' => __('Nâng lên', 'cb-company-core'), 'fade' => __('Mờ dần', 'cb-company-core'), 'outline' => __('Đổi viền', 'cb-company-core')]],
        ]],
        'cards' => ['label' => __('Thẻ nội dung', 'cb-company-core'), 'fields' => [
            ['card_radius', 'dimension', __('Card radius', 'cb-company-core')], ['card_border', 'checkbox', __('Card border', 'cb-company-core')],
            ['card_shadow', 'select', __('Card shadow', 'cb-company-core'), '', ['none' => __('Không', 'cb-company-core'), 'soft' => __('Nhẹ', 'cb-company-core'), 'medium' => __('Vừa', 'cb-company-core'), 'strong' => __('Đậm', 'cb-company-core')]],
            ['card_hover_effect', 'select', __('Card hover effect', 'cb-company-core'), '', ['none' => __('Không', 'cb-company-core'), 'lift' => __('Nâng lên', 'cb-company-core'), 'image_zoom' => __('Phóng ảnh', 'cb-company-core'), 'border_highlight' => __('Nhấn viền', 'cb-company-core')]],
            ['product_card_style', 'select', __('Product card style', 'cb-company-core'), '', ['clean' => __('Tối giản', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core'), 'bordered' => __('Có viền', 'cb-company-core')]],
            ['category_card_style', 'select', __('Category card style', 'cb-company-core'), '', ['image_top' => __('Ảnh phía trên', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core'), 'bordered' => __('Có viền', 'cb-company-core')]],
            ['news_card_style', 'select', __('News card style', 'cb-company-core'), '', ['image_left' => __('Ảnh bên trái', 'cb-company-core'), 'image_top' => __('Ảnh phía trên', 'cb-company-core'), 'clean' => __('Tối giản', 'cb-company-core')]],
        ]],
        'responsive' => ['label' => __('Responsive', 'cb-company-core'), 'fields' => [
            ['mobile_breakpoint', 'dimension', __('Breakpoint mobile', 'cb-company-core')],
            ['desktop_product_columns', 'number', __('Số cột desktop', 'cb-company-core'), '', ['min' => 2, 'max' => 6]],
            ['tablet_product_columns', 'number', __('Số cột tablet', 'cb-company-core'), '', ['min' => 1, 'max' => 4]],
            ['mobile_product_columns', 'number', __('Số cột mobile', 'cb-company-core'), '', ['min' => 1, 'max' => 2]],
        ]],
    ];
}

function cb_header_settings_schema()
{
    return [
        'desktop' => ['label' => __('Desktop', 'cb-company-core'), 'fields' => [
            ['header_layout', 'select', __('Bố cục header', 'cb-company-core'), '', ['logo_left_menu_center_cta_right' => __('Logo trái, menu giữa, CTA phải', 'cb-company-core'), 'logo_left_menu_right' => __('Logo trái, menu phải', 'cb-company-core'), 'logo_center_menu_below' => __('Logo giữa, menu dưới', 'cb-company-core'), 'minimal_logo_cta' => __('Tối giản: logo và CTA', 'cb-company-core')]],
            ['header_style', 'select', __('Kiểu nền', 'cb-company-core'), '', ['white' => __('Trắng', 'cb-company-core'), 'transparent' => __('Trong suốt', 'cb-company-core'), 'dark' => __('Tối', 'cb-company-core')]],
            ['header_height', 'dimension', __('Chiều cao header', 'cb-company-core')], ['header_sticky', 'checkbox', __('Header sticky', 'cb-company-core')],
            ['header_blur', 'checkbox', __('Hiệu ứng blur', 'cb-company-core')], ['header_shadow', 'checkbox', __('Đổ bóng', 'cb-company-core')],
            ['header_full_width', 'checkbox', __('Toàn chiều rộng', 'cb-company-core')], ['header_bg_color', 'color', __('Màu nền', 'cb-company-core')],
            ['header_text_color', 'color', __('Màu chữ', 'cb-company-core')],
        ]],
        'components' => ['label' => __('Thành phần', 'cb-company-core'), 'fields' => [
            ['show_search', 'checkbox', __('Hiện tìm kiếm', 'cb-company-core')], ['show_language_switcher', 'checkbox', __('Hiện chuyển ngôn ngữ', 'cb-company-core')],
            ['show_header_cta', 'checkbox', __('Hiện nút CTA', 'cb-company-core')], ['header_cta_text', 'text', __('Nhãn CTA', 'cb-company-core')],
            ['header_cta_url', 'text', __('Liên kết CTA', 'cb-company-core')],
        ]],
        'mobile' => ['label' => __('Mobile', 'cb-company-core'), 'fields' => [
            ['mobile_header_style', 'select', __('Kiểu menu mobile', 'cb-company-core'), '', ['offcanvas' => __('Trượt bên', 'cb-company-core'), 'dropdown' => __('Thả xuống', 'cb-company-core')]],
            ['mobile_show_cta', 'checkbox', __('Hiện CTA trên mobile', 'cb-company-core')], ['mobile_show_language', 'checkbox', __('Hiện ngôn ngữ trên mobile', 'cb-company-core')],
            ['mobile_hero_compact', 'checkbox', __('Hero gọn trên mobile', 'cb-company-core')],
        ]],
    ];
}

function cb_footer_settings_schema()
{
    return [
        'layout' => ['label' => __('Bố cục', 'cb-company-core'), 'fields' => [
            ['footer_layout', 'select', __('Bố cục footer', 'cb-company-core'), '', ['four_columns' => __('Bốn cột', 'cb-company-core'), 'three_columns' => __('Ba cột', 'cb-company-core'), 'centered' => __('Căn giữa', 'cb-company-core'), 'minimal' => __('Tối giản', 'cb-company-core')]],
            ['footer_bg_color', 'color', __('Màu nền', 'cb-company-core')], ['footer_text_color', 'color', __('Màu nội dung', 'cb-company-core')],
            ['footer_background_image', 'image', __('Ảnh nền footer', 'cb-company-core'), __('Chọn từ Media Library hoặc nhập đường dẫn ảnh.', 'cb-company-core')],
            ['footer_heading_color', 'color', __('Màu tiêu đề', 'cb-company-core')], ['footer_description', 'textarea', __('Mô tả footer', 'cb-company-core')],
            ['copyright_text', 'text', __('Bản quyền', 'cb-company-core')],
        ]],
        'columns' => ['label' => __('Các cột', 'cb-company-core'), 'fields' => [
            ['show_footer_logo', 'checkbox', __('Hiện logo', 'cb-company-core')], ['show_footer_products', 'checkbox', __('Hiện cột sản phẩm', 'cb-company-core')],
            ['show_footer_links', 'checkbox', __('Hiện liên kết nhanh', 'cb-company-core')], ['show_footer_contact', 'checkbox', __('Hiện liên hệ', 'cb-company-core')],
            ['show_footer_social', 'checkbox', __('Hiện mạng xã hội', 'cb-company-core')], ['show_footer_subscribe', 'checkbox', __('Hiện đăng ký nhận tin', 'cb-company-core')],
        ]],
        'contact' => ['label' => __('Liên hệ và mạng xã hội', 'cb-company-core'), 'fields' => [
            ['contact_phone', 'text', __('Điện thoại', 'cb-company-core')], ['contact_email', 'text', __('Email', 'cb-company-core')],
            ['company_address', 'textarea', __('Địa chỉ', 'cb-company-core')], ['social_links', 'repeater', __('Mạng xã hội', 'cb-company-core')],
            ['floating_contact', 'checkbox', __('Hiện nút báo giá nổi', 'cb-company-core')],
        ]],
    ];
}

function cb_sanitize_design_settings($input) { return cb_sanitize_settings_group($input, cb_default_design_settings()); }
function cb_sanitize_header_settings($input) { return cb_sanitize_settings_group($input, cb_default_header_settings()); }
function cb_sanitize_footer_settings($input) { return cb_sanitize_settings_group($input, cb_default_footer_settings()); }

function cb_footer_contact_settings_schema()
{
    $schema = cb_footer_settings_schema();
    if (isset($schema['contact']['fields']) && is_array($schema['contact']['fields'])) {
        $schema['contact']['fields'][] = ['contact_whatsapp', 'text', __('WhatsApp', 'cb-company-core')];
        $schema['contact']['fields'][] = ['contact_whatsapp_qr', 'image', __('Ảnh QR WhatsApp', 'cb-company-core'), __('Dùng trên thanh liên hệ và trang Contact. Có thể ghi đè trong section Thông tin liên hệ.', 'cb-company-core')];
        $schema['contact']['fields'][] = ['contact_wechat_id', 'text', __('WeChat ID (văn bản)', 'cb-company-core'), __('Nhập ID riêng để hiển thị thành chữ ngay bên dưới ảnh QR. Trường này không dùng để tạo mã QR.', 'cb-company-core')];
        $schema['contact']['fields'][] = ['contact_wechat_qr', 'image', __('Ảnh mã QR WeChat (độc lập)', 'cb-company-core'), __('Chọn hoặc tải lên ảnh QR riêng từ Media Library. Ảnh này không được tạo từ WeChat ID và có thể ghi đè trong section Thông tin liên hệ.', 'cb-company-core')];
    }
    return $schema;
}

function cb_sanitize_template_settings($input)
{
    $input = is_array($input) ? wp_unslash($input) : [];
    $clean = [];
    foreach (cb_default_template_settings() as $context => $defaults) {
        $clean[$context] = cb_sanitize_settings_group($input[$context] ?? [], $defaults);
    }
    return $clean;
}

function cb_sanitize_special_pages($input)
{
    $clean = [];
    foreach (array_keys(cb_languages()) as $lang) {
        foreach (['home', 'about', 'contact'] as $role) {
            $clean[$lang][$role] = absint($input[$lang][$role] ?? 0);
        }
    }
    return $clean;
}

function cb_sanitize_string_translations($input)
{
    $clean = [];
    foreach ((array) $input as $key => $langs) {
        $clean[sanitize_key($key)] = ['en' => sanitize_text_field(wp_unslash($langs['en'] ?? '')), 'zh' => sanitize_text_field(wp_unslash($langs['zh'] ?? ''))];
    }
    return $clean;
}

function cb_default_form_settings() { return ['admin_email' => get_option('admin_email'), 'sender_name' => get_bloginfo('name'), 'subject_en' => 'New website inquiry', 'subject_zh' => '新的产品询价', 'auto_reply' => '1']; }
function cb_default_seo_settings() { return ['title_suffix' => get_bloginfo('name'), 'default_description' => '', 'default_og_image_id' => '', 'default_og_image' => '', 'enable_schema' => '1']; }
function cb_default_performance_settings() { return ['lazy_images' => '1', 'disable_emojis' => '1', 'preload_logo' => '1', 'revision_limit' => '10']; }
function cb_sanitize_form_settings($input) { return cb_sanitize_settings_group($input, cb_default_form_settings()); }
function cb_sanitize_seo_settings($input) { return cb_sanitize_settings_group($input, cb_default_seo_settings()); }
function cb_sanitize_performance_settings($input) { return cb_sanitize_settings_group($input, cb_default_performance_settings()); }

function cb_admin_shell_start($title, $active_slug)
{
    echo '<div class="wrap cb-admin-shell"><div class="cb-admin-layout"><aside class="cb-admin-sidebar">';
    foreach (cb_admin_menu_items() as $slug => $item) {
        echo '<a class="' . esc_attr($slug === $active_slug ? 'is-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=' . $slug)) . '"><span class="dashicons ' . esc_attr($item[1]) . '" aria-hidden="true"></span>' . esc_html($item[0]) . '</a>';
    }
    echo '</aside><main class="cb-admin-main"><h1 class="screen-reader-text">' . esc_html($title) . '</h1>';
}

function cb_admin_shell_end() { echo '</main></div></div>'; }

function cb_render_settings_page($title, $slug, $option, $group, $schema, $defaults)
{
    $active = sanitize_key(wp_unslash($_GET['tab'] ?? array_key_first($schema)));
    if (!isset($schema[$active])) {
        $active = array_key_first($schema);
    }
    $values = cb_get_group_options($option, $defaults);
    $module = str_replace('cb-company-', '', $slug);
    cb_admin_shell_start($title, $slug);
    echo '<form method="post" action="options.php" class="cb-settings-form" data-cb-settings-form data-cb-module="' . esc_attr($module) . '" data-cb-option="' . esc_attr($option) . '">';
    settings_fields($group);
    cb_render_save_bar($title, $option, $active);
    echo '<div class="cb-admin-panel" data-cb-tabs-root data-cb-module="' . esc_attr($module) . '"><nav class="cb-admin-tabs" role="tablist">';
    foreach ($schema as $key => $tab) {
        $panel_id = 'cb-panel-' . $slug . '-' . $key;
        echo '<a class="cb-admin-tab ' . esc_attr($key === $active ? 'is-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=' . $slug . '&tab=' . $key)) . '" role="tab" aria-selected="' . esc_attr($key === $active ? 'true' : 'false') . '" aria-controls="' . esc_attr($panel_id) . '" tabindex="' . esc_attr($key === $active ? '0' : '-1') . '" data-cb-tab="' . esc_attr($key) . '">' . esc_html($tab['label']) . '</a>';
    }
    echo '</nav><div class="cb-tab-panels">';
    foreach ($schema as $key => $tab) {
        $is_active = $key === $active;
        echo '<section id="cb-panel-' . esc_attr($slug . '-' . $key) . '" class="cb-tab-panel' . esc_attr($is_active ? ' is-active' : '') . '" role="tabpanel" data-cb-panel="' . esc_attr($key) . '"' . ($is_active ? '' : ' hidden') . '><div class="cb-admin-grid">';
        foreach ($tab['fields'] as $field) {
            cb_render_settings_field($option, $field, $values, $defaults);
        }
        echo '</div></section>';
    }
    echo '</div></div></form>';
    cb_render_danger_zone($option);
    cb_admin_shell_end();
}

function cb_render_save_bar($title, $option = '', $tab = '')
{
    echo '<div class="cb-save-bar"><div><h1>' . esc_html($title) . '</h1><span class="cb-unsaved-status" aria-live="polite"></span></div><div class="cb-save-actions">';
    if ($option) {
        $url = wp_nonce_url(admin_url('admin-post.php?action=cb_reset_settings&option=' . rawurlencode($option) . '&tab=' . rawurlencode($tab)), 'cb_reset_settings');
        echo '<a class="button cb-reset-link" href="' . esc_url($url) . '">' . esc_html__('Khôi phục tab này', 'cb-company-core') . '</a>';
    }
    echo '<button type="submit" class="button button-primary">' . esc_html__('Lưu thay đổi', 'cb-company-core') . '</button></div></div>';
}

function cb_render_settings_field($option, $field, $values, $defaults, $id_prefix = 'cb')
{
    [$key, $type, $label] = $field;
    $description = $field[3] ?? '';
    $extra = $field[4] ?? [];
    if ($type === 'image_pair') {
        cb_admin_image_field(['id' => $id_prefix . '-' . $key, 'label' => $label, 'description' => $description, 'name_base' => $option, 'id_key' => $key . '_id', 'url_key' => $key . '_url', 'id_value' => $values[$key . '_id'] ?? 0, 'url_value' => $values[$key . '_url'] ?? '']);
        return;
    }
    if ($type === 'image') {
        cb_admin_image_field(['id' => $id_prefix . '-' . $key, 'label' => $label, 'description' => $description, 'name_base' => $option, 'id_key' => $key . '_id', 'url_key' => $key, 'id_value' => $values[$key . '_id'] ?? 0, 'url_value' => $values[$key] ?? '']);
        return;
    }
    $args = ['id' => $id_prefix . '-' . $key, 'name' => $option . '[' . $key . ']', 'label' => $label, 'description' => $description, 'value' => $values[$key] ?? '', 'default' => $defaults[$key] ?? '', 'choices' => $type === 'select' ? $extra : []];
    if ($type === 'color') cb_admin_color_field($args);
    elseif ($type === 'textarea') cb_admin_textarea_field($args);
    elseif ($type === 'select') cb_admin_select_field($args + ['choices' => $extra]);
    elseif ($type === 'checkbox') cb_admin_checkbox_field($args);
    elseif ($type === 'number') cb_admin_number_field($args + $extra);
    elseif ($type === 'dimension') cb_admin_dimension_field($args);
    elseif ($type === 'repeater') cb_admin_repeater_field($args);
    else cb_admin_text_field($args);
}

function cb_render_danger_zone($option)
{
    $url = wp_nonce_url(admin_url('admin-post.php?action=cb_reset_settings&option=' . rawurlencode($option) . '&all=1'), 'cb_reset_settings');
    echo '<div class="cb-danger-zone"><h2>' . esc_html__('Khu vực nguy hiểm', 'cb-company-core') . '</h2><p>' . esc_html__('Khôi phục toàn bộ nhóm cài đặt này về giá trị mặc định trong code.', 'cb-company-core') . '</p><a class="button button-secondary cb-reset-link" href="' . esc_url($url) . '">' . esc_html__('Khôi phục toàn bộ', 'cb-company-core') . '</a></div>';
}

function cb_render_dashboard_page()
{
    cb_admin_shell_start(__('Tổng quan', 'cb-company-core'), 'cb-company');
    echo '<div class="cb-save-bar"><h1>' . esc_html__('Tổng quan CB Company', 'cb-company-core') . '</h1></div><div class="cb-dashboard-grid">';
    $cards = [
        ['cb-company-design', __('Thiết kế chung', 'cb-company-core'), __('Màu sắc, typography, bố cục, nút và thẻ nội dung.', 'cb-company-core')],
        ['cb-company-templates', __('Mẫu trang', 'cb-company-core'), __('Thiết lập riêng cho archive, taxonomy và trang chi tiết.', 'cb-company-core')],
        ['cb-company-page-builder', __('Trình dựng trang', 'cb-company-core'), __('Mở từng Page để dựng section và tùy chỉnh giao diện.', 'cb-company-core')],
    ];
    foreach ($cards as $card) {
        echo '<article class="cb-dashboard-card"><h2>' . esc_html($card[1]) . '</h2><p>' . esc_html($card[2]) . '</p><a class="button" href="' . esc_url(admin_url('admin.php?page=' . $card[0])) . '">' . esc_html__('Mở module', 'cb-company-core') . '</a></article>';
    }
    echo '</div>';
    cb_admin_shell_end();
}

function cb_render_design_settings_page() { cb_render_settings_page(__('Thiết kế chung', 'cb-company-core'), 'cb-company-design', 'cb_design_settings', 'cb_design_settings_group', cb_design_settings_schema(), cb_default_design_settings()); }
function cb_render_header_settings_page() { cb_render_settings_page(__('Header', 'cb-company-core'), 'cb-company-header', 'cb_header_settings', 'cb_header_settings_group', cb_header_settings_schema(), cb_default_header_settings()); }
function cb_render_footer_settings_page() { cb_render_settings_page(__('Footer', 'cb-company-core'), 'cb-company-footer', 'cb_footer_settings', 'cb_footer_settings_group', cb_footer_contact_settings_schema(), cb_default_footer_settings()); }

function cb_template_context_groups()
{
    return [
        'home' => [__('Trang chủ', 'cb-company-core'), ['home' => __('Trang chủ', 'cb-company-core')]],
        'standard_page' => [__('Trang thường', 'cb-company-core'), ['standard_page' => __('Trang thường', 'cb-company-core')]],
        'about' => [__('Giới thiệu', 'cb-company-core'), ['about_page' => __('Trang giới thiệu', 'cb-company-core')]],
        'product' => [__('Sản phẩm', 'cb-company-core'), ['product_archive' => __('Danh sách sản phẩm', 'cb-company-core'), 'product_category' => __('Danh mục sản phẩm', 'cb-company-core'), 'product_single' => __('Chi tiết sản phẩm', 'cb-company-core')]],
        'factory' => [__('Nhà máy', 'cb-company-core'), ['factory_archive' => __('Danh sách showcase', 'cb-company-core'), 'factory_single' => __('Chi tiết showcase', 'cb-company-core')]],
        'case' => [__('Dự án', 'cb-company-core'), ['case_archive' => __('Danh sách dự án', 'cb-company-core'), 'case_single' => __('Chi tiết dự án', 'cb-company-core')]],
        'video' => [__('Video', 'cb-company-core'), ['video_archive' => __('Danh sách video', 'cb-company-core'), 'video_single' => __('Chi tiết video', 'cb-company-core')]],
        'news' => [__('Tin tức', 'cb-company-core'), ['news_archive' => __('Danh sách bài viết', 'cb-company-core'), 'news_category' => __('Danh mục bài viết', 'cb-company-core'), 'news_single' => __('Chi tiết bài viết', 'cb-company-core')]],
        'contact' => [__('Liên hệ', 'cb-company-core'), ['contact_page' => __('Trang liên hệ', 'cb-company-core')]],
        'search' => [__('Tìm kiếm', 'cb-company-core'), ['search' => __('Trang tìm kiếm', 'cb-company-core')]],
        '404' => [__('404', 'cb-company-core'), ['404' => __('Trang 404', 'cb-company-core')]],
    ];
}

function cb_template_field_tabs($context)
{
    $layout = [
        ['layout', 'select', __('Kiểu bố cục', 'cb-company-core'), '', ['default' => __('Mặc định', 'cb-company-core'), 'full_width' => __('Toàn chiều rộng', 'cb-company-core'), 'sidebar' => __('Có sidebar', 'cb-company-core')]],
        ['container_width', 'dimension', __('Chiều rộng nội dung', 'cb-company-core')],
    ];
    if ($context === 'product_single') {
        $layout = array_merge($layout, [
            ['product_layout', 'select', __('Vị trí gallery', 'cb-company-core'), '', ['gallery_left' => __('Gallery trái / nội dung phải', 'cb-company-core'), 'gallery_right' => __('Gallery phải / nội dung trái', 'cb-company-core')]],
            ['sticky_summary', 'checkbox', __('Sticky product summary', 'cb-company-core')],
            ['column_gap', 'dimension', __('Khoảng cách giữa hai cột', 'cb-company-core')],
        ]);
    }
    $components = [['show_breadcrumb', 'checkbox', __('Hiện breadcrumb', 'cb-company-core')]];
    if ($context === 'product_single') {
        $components = array_merge($components, [
            ['show_short_description', 'checkbox', __('Hiện mô tả ngắn', 'cb-company-core')], ['show_quick_specs', 'checkbox', __('Hiện thông số nhanh', 'cb-company-core')],
            ['show_catalog', 'checkbox', __('Hiện catalog PDF', 'cb-company-core')], ['show_video', 'checkbox', __('Hiện video', 'cb-company-core')],
            ['show_related_products', 'checkbox', __('Hiện sản phẩm liên quan', 'cb-company-core')], ['show_inquiry', 'checkbox', __('Hiện form inquiry', 'cb-company-core')],
            ['show_bottom_cta', 'checkbox', __('Hiện CTA cuối trang', 'cb-company-core')],
        ]);
    }
    return [
        'layout' => [__('Bố cục', 'cb-company-core'), $layout],
        'components' => [__('Thành phần', 'cb-company-core'), $components],
        'colors' => [__('Màu và nền', 'cb-company-core'), [['background_color', 'color', __('Màu nền', 'cb-company-core')], ['text_color', 'color', __('Màu chữ', 'cb-company-core')]]],
        'responsive' => [__('Responsive', 'cb-company-core'), [['columns_tablet', 'number', __('Số cột tablet', 'cb-company-core')], ['columns_mobile', 'number', __('Số cột mobile', 'cb-company-core')], ['mobile_gallery_first', 'checkbox', __('Gallery hiển thị trước trên mobile', 'cb-company-core')], ['mobile_sticky_cta', 'checkbox', __('Sticky CTA mobile', 'cb-company-core')]]],
        'seo' => [__('SEO mặc định', 'cb-company-core'), [['seo_title', 'text', __('SEO title mặc định', 'cb-company-core')], ['seo_description', 'textarea', __('SEO description mặc định', 'cb-company-core')]]],
    ];
}

function cb_normalize_template_route($type = '', $context = '', $tab = '')
{
    $groups = cb_template_context_groups();
    $type = sanitize_key($type ?: 'product');
    if (!isset($groups[$type])) {
        $type = 'product';
    }
    $contexts = $groups[$type][1];
    $context = sanitize_key($context ?: array_key_first($contexts));
    if (!isset($contexts[$context])) {
        $context = array_key_first($contexts);
    }
    $tabs = cb_template_field_tabs($context);
    $tab = sanitize_key($tab ?: 'layout');
    if (!isset($tabs[$tab])) {
        $tab = array_key_first($tabs);
    }
    return ['type' => $type, 'context' => $context, 'tab' => $tab];
}

function cb_template_route_url($route)
{
    return admin_url('admin.php?page=cb-company-templates&type=' . $route['type'] . '&context=' . $route['context'] . '&tab=' . $route['tab']);
}

function cb_render_template_type_tabs($route)
{
    echo '<nav class="cb-admin-tabs" aria-label="' . esc_attr__('Loại trang', 'cb-company-core') . '">';
    foreach (cb_template_context_groups() as $type => $data) {
        $context = array_key_first($data[1]);
        $target = ['type' => $type, 'context' => $context, 'tab' => 'layout'];
        echo '<a class="cb-template-route ' . esc_attr($type === $route['type'] ? 'is-active' : '') . '" href="' . esc_url(cb_template_route_url($target)) . '" data-cb-template-route data-type="' . esc_attr($type) . '" data-context="' . esc_attr($context) . '" data-tab="layout">' . esc_html($data[0]) . '</a>';
    }
    echo '</nav>';
}

function cb_render_template_context_tabs($route)
{
    $contexts = cb_template_context_groups()[$route['type']][1];
    echo '<nav class="cb-admin-tabs cb-sub-tabs" aria-label="' . esc_attr__('Ngữ cảnh template', 'cb-company-core') . '">';
    foreach ($contexts as $context => $label) {
        $target = ['type' => $route['type'], 'context' => $context, 'tab' => 'layout'];
        echo '<a class="cb-template-route ' . esc_attr($context === $route['context'] ? 'is-active' : '') . '" href="' . esc_url(cb_template_route_url($target)) . '" data-cb-template-route data-type="' . esc_attr($route['type']) . '" data-context="' . esc_attr($context) . '" data-tab="layout">' . esc_html($label) . '</a>';
    }
    echo '</nav>';
}

function cb_render_template_field_tabs($route)
{
    echo '<nav class="cb-admin-tabs cb-sub-tabs" aria-label="' . esc_attr__('Nhóm thiết lập', 'cb-company-core') . '">';
    foreach (cb_template_field_tabs($route['context']) as $tab => $data) {
        $target = ['type' => $route['type'], 'context' => $route['context'], 'tab' => $tab];
        echo '<a class="cb-template-route ' . esc_attr($tab === $route['tab'] ? 'is-active' : '') . '" href="' . esc_url(cb_template_route_url($target)) . '" data-cb-template-route data-type="' . esc_attr($route['type']) . '" data-context="' . esc_attr($route['context']) . '" data-tab="' . esc_attr($tab) . '">' . esc_html($data[0]) . '</a>';
    }
    echo '</nav>';
}

function cb_render_template_panel_fragment($context, $tab)
{
    $tabs = cb_template_field_tabs($context);
    if (!isset($tabs[$tab])) {
        $tab = array_key_first($tabs);
    }
    $all = cb_get_group_options('cb_template_settings', cb_default_template_settings());
    $values = $all[$context] ?? [];
    $defaults = cb_default_template_settings()[$context] ?? [];
    echo '<section class="cb-template-panel is-active" data-cb-template-panel data-context="' . esc_attr($context) . '" data-tab="' . esc_attr($tab) . '"><div class="cb-admin-grid">';
    foreach ($tabs[$tab][1] as $field) {
        cb_render_settings_field('cb_template_settings[' . $context . ']', $field, $values, $defaults, 'cb-template-' . $context . '-' . $tab);
    }
    echo '</div></section>';
}

function cb_render_template_settings_page()
{
    $route = cb_normalize_template_route(
        wp_unslash($_GET['type'] ?? ''),
        wp_unslash($_GET['context'] ?? ''),
        wp_unslash($_GET['tab'] ?? '')
    );
    $all = cb_get_group_options('cb_template_settings', cb_default_template_settings());
    cb_admin_shell_start(__('Mẫu trang', 'cb-company-core'), 'cb-company-templates');
    echo '<form method="post" action="options.php" class="cb-settings-form" data-cb-settings-form data-cb-module="templates" data-cb-option="cb_template_settings" data-cb-save-scope="active-panel">';
    settings_fields('cb_template_settings_group');
    foreach ($all as $stored_context => $stored_values) {
        foreach ($stored_values as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            echo '<input type="hidden" name="cb_template_settings[' . esc_attr($stored_context) . '][' . esc_attr($key) . ']" value="' . esc_attr((string) $value) . '">';
        }
    }
    cb_render_save_bar(__('Mẫu trang', 'cb-company-core'));
    echo '<div class="cb-admin-panel cb-template-app" data-cb-template-app data-type="' . esc_attr($route['type']) . '" data-context="' . esc_attr($route['context']) . '" data-tab="' . esc_attr($route['tab']) . '">';
    echo '<div class="cb-template-content-notice"><div><strong>' . esc_html__('Đây là thiết lập mặc định giao diện.', 'cb-company-core') . '</strong><p>' . esc_html__('Mẫu trang chỉ quản lý bố cục và thành phần mặc định. Text, hình ảnh và section được chỉnh trong Nội dung trang.', 'cb-company-core') . '</p></div><div><a class="button button-primary" href="' . esc_url(cb_content_module_url('home', 'en')) . '">' . esc_html__('Sửa nội dung Trang chủ English', 'cb-company-core') . '</a> <a class="button" href="' . esc_url(cb_content_module_url('home', 'zh')) . '">' . esc_html__('Sửa nội dung Trang chủ 中文', 'cb-company-core') . '</a></div></div>';
    echo '<div data-cb-template-type-nav>'; cb_render_template_type_tabs($route); echo '</div>';
    echo '<div data-cb-template-context-nav>'; cb_render_template_context_tabs($route); echo '</div>';
    echo '<div data-cb-template-tab-nav>'; cb_render_template_field_tabs($route); echo '</div>';
    echo '<div class="cb-template-panel-host" data-cb-template-panel-host>';
    cb_render_template_panel_fragment($route['context'], $route['tab']);
    echo '</div></div></form>';
    cb_admin_shell_end();
}

function cb_render_special_pages_page()
{
    $values = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    cb_admin_shell_start(__('Trang đặc biệt', 'cb-company-core'), 'cb-company-special-pages');
    echo '<form method="post" action="options.php" class="cb-settings-form" data-cb-settings-form data-cb-module="special-pages" data-cb-option="cb_special_pages">'; settings_fields('cb_special_pages_group'); cb_render_save_bar(__('Trang đặc biệt', 'cb-company-core'));
    echo '<div class="cb-admin-panel"><div class="cb-admin-grid">';
    foreach (cb_languages() as $lang => $config) {
        echo '<section><h2>' . esc_html($config['native']) . '</h2>';
        foreach (['home' => __('Trang chủ', 'cb-company-core'), 'about' => __('Trang giới thiệu', 'cb-company-core'), 'contact' => __('Trang liên hệ', 'cb-company-core')] as $role => $label) {
            $selected_id = absint($values[$lang][$role] ?? 0);
            $selected_title = $selected_id ? get_the_title($selected_id) : '';
            $select_id = 'cb-special-' . $lang . '-' . $role;
            echo '<div class="cb-admin-field cb-page-search-field"><label class="cb-admin-label" for="' . esc_attr($select_id) . '">' . esc_html($label) . '</label><div class="cb-admin-control">';
            echo '<input type="search" class="regular-text cb-page-search" placeholder="' . esc_attr__('Nhập tên trang để tìm kiếm...', 'cb-company-core') . '" data-language="' . esc_attr($lang) . '" data-target="' . esc_attr($select_id) . '">';
            echo '<select id="' . esc_attr($select_id) . '" name="cb_special_pages[' . esc_attr($lang) . '][' . esc_attr($role) . ']"><option value="0">' . esc_html__('Chưa gán', 'cb-company-core') . '</option>';
            if ($selected_id) {
                echo '<option value="' . esc_attr((string) $selected_id) . '" selected>' . esc_html($selected_title) . '</option>';
            }
            echo '</select><p class="cb-admin-description">' . esc_html__('Tối đa 20 kết quả theo đúng ngôn ngữ nội dung.', 'cb-company-core') . '</p></div></div>';
        }
        echo '</section>';
    }
    echo '</div></div></form>';
    cb_admin_shell_end();
}

function cb_render_page_builder_index_page()
{
    $paged = max(1, absint($_GET['paged'] ?? 1));
    $search = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));
    $language = sanitize_key(wp_unslash($_GET['language'] ?? ''));
    $status = sanitize_key(wp_unslash($_GET['status'] ?? ''));
    $allowed_statuses = ['publish', 'draft', 'private'];
    $args = [
        'post_type' => 'page',
        'post_status' => in_array($status, $allowed_statuses, true) ? [$status] : $allowed_statuses,
        'posts_per_page' => 20,
        'paged' => $paged,
        'orderby' => 'modified',
        'order' => 'DESC',
        's' => $search,
        'no_found_rows' => false,
    ];
    if (isset(cb_languages()[$language])) {
        $args['meta_query'] = [['key' => '_cb_language', 'value' => $language]];
    }
    $query = new WP_Query($args);
    cb_admin_shell_start(__('Trình dựng trang', 'cb-company-core'), 'cb-company-page-builder');
    echo '<div class="cb-save-bar"><h1>' . esc_html__('Trình dựng trang', 'cb-company-core') . '</h1><a class="button button-primary" href="' . esc_url(admin_url('post-new.php?post_type=page')) . '">' . esc_html__('Thêm Page', 'cb-company-core') . '</a></div><div class="cb-admin-panel">';
    echo '<form method="get" class="cb-page-builder-filter"><input type="hidden" name="page" value="cb-company-page-builder"><input type="search" name="s" value="' . esc_attr($search) . '" placeholder="' . esc_attr__('Tìm theo tên Page', 'cb-company-core') . '"><select name="language"><option value="">' . esc_html__('Tất cả ngôn ngữ', 'cb-company-core') . '</option>';
    foreach (cb_languages() as $code => $config) echo '<option value="' . esc_attr($code) . '" ' . selected($language, $code, false) . '>' . esc_html($config['native']) . '</option>';
    echo '</select><select name="status"><option value="">' . esc_html__('Tất cả trạng thái', 'cb-company-core') . '</option>';
    foreach (['publish' => __('Đã xuất bản', 'cb-company-core'), 'draft' => __('Bản nháp', 'cb-company-core'), 'private' => __('Riêng tư', 'cb-company-core')] as $key => $label) echo '<option value="' . esc_attr($key) . '" ' . selected($status, $key, false) . '>' . esc_html($label) . '</option>';
    echo '</select><button class="button" type="submit">' . esc_html__('Lọc', 'cb-company-core') . '</button></form>';
    echo '<table class="widefat striped"><thead><tr><th>' . esc_html__('Trang', 'cb-company-core') . '</th><th>' . esc_html__('Ngôn ngữ', 'cb-company-core') . '</th><th>' . esc_html__('Chế độ', 'cb-company-core') . '</th><th></th></tr></thead><tbody>';
    if (!$query->posts) echo '<tr><td colspan="4">' . esc_html__('Không tìm thấy Page phù hợp.', 'cb-company-core') . '</td></tr>';
    foreach ($query->posts as $page) echo '<tr><td><strong>' . esc_html($page->post_title) . '</strong></td><td>' . esc_html(strtoupper(get_post_meta($page->ID, '_cb_language', true) ?: 'en')) . '</td><td>' . esc_html(get_post_meta($page->ID, '_cb_page_render_mode', true) ?: 'editor') . '</td><td><a class="button" href="' . esc_url(get_edit_post_link($page->ID)) . '">' . esc_html__('Mở trình dựng', 'cb-company-core') . '</a></td></tr>';
    echo '</tbody></table>';
    $pagination = paginate_links(['base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => max(1, (int) $query->max_num_pages), 'type' => 'list']);
    if ($pagination) echo '<nav class="cb-admin-pagination" aria-label="' . esc_attr__('Phân trang', 'cb-company-core') . '">' . wp_kses_post($pagination) . '</nav>';
    echo '</div>';
    cb_admin_shell_end();
}

function cb_render_string_translations_page()
{
    $strings = wp_parse_args((array) get_option('cb_string_translations', []), cb_default_string_translations());
    cb_admin_shell_start(__('Chuỗi giao diện', 'cb-company-core'), 'cb-company-strings');
    echo '<form method="post" action="options.php" class="cb-settings-form" data-cb-settings-form data-cb-module="strings" data-cb-option="cb_string_translations">'; settings_fields('cb_string_translations_group'); cb_render_save_bar(__('Chuỗi giao diện', 'cb-company-core'));
    echo '<div class="cb-admin-panel"><table class="widefat striped"><thead><tr><th>' . esc_html__('Khóa', 'cb-company-core') . '</th><th>English</th><th>中文</th></tr></thead><tbody>';
    foreach ($strings as $key => $langs) echo '<tr><td><code>' . esc_html($key) . '</code></td><td><input class="regular-text" name="cb_string_translations[' . esc_attr($key) . '][en]" value="' . esc_attr($langs['en'] ?? '') . '"></td><td><input class="regular-text" name="cb_string_translations[' . esc_attr($key) . '][zh]" value="' . esc_attr($langs['zh'] ?? '') . '"></td></tr>';
    echo '</tbody></table></div></form>';
    cb_admin_shell_end();
}

function cb_render_multilingual_settings_page()
{
    cb_admin_shell_start(__('Đa ngôn ngữ', 'cb-company-core'), 'cb-company-multilingual');
    echo '<div class="cb-save-bar"><h1>' . esc_html__('Đa ngôn ngữ nội dung', 'cb-company-core') . '</h1></div><div class="cb-admin-panel"><div class="cb-admin-grid"><div><h2>English</h2><p><code>en</code> · <code>en_US</code> · <code>lang=en</code></p></div><div><h2>中文</h2><p><code>zh</code> · <code>zh_CN</code> · <code>lang=zh-CN</code></p></div><p class="cb-wide">' . esc_html__('Ngôn ngữ quản trị do từng tài khoản chọn trong Hồ sơ người dùng và không thay đổi ngôn ngữ nội dung frontend.', 'cb-company-core') . '</p></div></div>';
    cb_admin_shell_end();
}

function cb_render_simple_settings_page($title, $slug, $option, $group, $defaults, $fields)
{
    cb_render_settings_page($title, $slug, $option, $group, ['settings' => ['label' => __('Cài đặt', 'cb-company-core'), 'fields' => $fields]], $defaults);
}
function cb_render_form_settings_page() { cb_render_simple_settings_page(__('Biểu mẫu và email', 'cb-company-core'), 'cb-company-forms', 'cb_form_settings', 'cb_form_settings_group', cb_default_form_settings(), [['admin_email', 'text', __('Email nhận yêu cầu', 'cb-company-core')], ['sender_name', 'text', __('Tên người gửi', 'cb-company-core')], ['subject_en', 'text', __('Tiêu đề email English', 'cb-company-core')], ['subject_zh', 'text', __('Tiêu đề email 中文', 'cb-company-core')], ['auto_reply', 'checkbox', __('Gửi email phản hồi tự động', 'cb-company-core')]]); }
function cb_render_seo_settings_page() { cb_render_simple_settings_page(__('SEO', 'cb-company-core'), 'cb-company-seo', 'cb_seo_settings', 'cb_seo_settings_group', cb_default_seo_settings(), [['title_suffix', 'text', __('Hậu tố tiêu đề', 'cb-company-core')], ['default_description', 'textarea', __('Mô tả mặc định', 'cb-company-core')], ['default_og_image', 'image', __('Open Graph image mặc định', 'cb-company-core'), __('Chọn từ Media Library hoặc nhập đường dẫn ảnh.', 'cb-company-core')], ['enable_schema', 'checkbox', __('Bật schema', 'cb-company-core')]]); }
function cb_render_performance_settings_page() { cb_render_simple_settings_page(__('Hiệu năng', 'cb-company-core'), 'cb-company-performance', 'cb_performance_settings', 'cb_performance_settings_group', cb_default_performance_settings(), [['lazy_images', 'checkbox', __('Lazy load hình ảnh', 'cb-company-core')], ['disable_emojis', 'checkbox', __('Tắt tài nguyên emoji WordPress', 'cb-company-core')], ['preload_logo', 'checkbox', __('Preload logo', 'cb-company-core')], ['revision_limit', 'number', __('Số revision tối đa', 'cb-company-core')]]); }

function cb_render_tools_page()
{
    $demo = cb_demo_content_status();
    $demo_images = cb_demo_image_status();
    cb_admin_shell_start(__('Công cụ', 'cb-company-core'), 'cb-company-tools');
    echo '<div class="cb-save-bar"><h1>' . esc_html__('Công cụ', 'cb-company-core') . '</h1></div><div class="cb-dashboard-grid">';
    $csv_url = wp_nonce_url(admin_url('admin-post.php?action=cb_export_inquiries_csv'), 'cb_export_inquiries');
    echo '<article class="cb-dashboard-card"><h2>' . esc_html__('Xuất yêu cầu CSV', 'cb-company-core') . '</h2><p>' . esc_html__('CSV có UTF-8 BOM để Excel đọc đúng tiếng Việt và tiếng Trung.', 'cb-company-core') . '</p><a class="button" href="' . esc_url($csv_url) . '">' . esc_html__('Tải CSV', 'cb-company-core') . '</a></article>';
    echo '<article class="cb-dashboard-card"><h2>' . esc_html__('Phiên bản dữ liệu', 'cb-company-core') . '</h2><p><code>' . esc_html((string) get_option('cb_core_db_version', '0')) . '</code></p></article>';
    if (get_option('cb_catalog_layout_backup_140')) {
        echo '<article class="cb-dashboard-card"><h2>' . esc_html__('Bố cục catalogue Aurelia', 'cb-company-core') . '</h2><p>' . esc_html__('Có bản sao lưu Home EN/ZH và thiết lập giao diện trước lần nâng cấp 1.4.0.', 'cb-company-core') . '</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="cb_restore_catalog_layout">';
        wp_nonce_field('cb_restore_catalog_layout');
        submit_button(__('Khôi phục bố cục trước nâng cấp', 'cb-company-core'), 'secondary', 'submit', false);
        echo '</form></article>';
    }
    if (get_option('cb_about_certificate_backup_150')) {
        echo '<article class="cb-dashboard-card"><h2>' . esc_html__('About và Chứng nhận', 'cb-company-core') . '</h2><p>' . esc_html__('Có bản sao lưu About EN/ZH trước lần nâng cấp 1.5.0.', 'cb-company-core') . '</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="cb_restore_about_layout">';
        wp_nonce_field('cb_restore_about_layout');
        submit_button(__('Khôi phục bố cục About trước 1.5.0', 'cb-company-core'), 'secondary', 'submit', false);
        echo '</form></article>';
    }
    echo '<article class="cb-dashboard-card"><h2>' . esc_html__('Backup trang chủ cũ', 'cb-company-core') . '</h2><p>' . (get_option('cb_homepage_sections_backup_110', null) !== null ? esc_html__('Đã tạo backup.', 'cb-company-core') : esc_html__('Chưa có dữ liệu cần backup.', 'cb-company-core')) . '</p></article></div>';
    echo '<div class="cb-admin-panel cb-demo-tools"><h2>' . esc_html__('Dữ liệu mẫu', 'cb-company-core') . '</h2><p>' . esc_html__('Bộ hình ảnh mẫu được nhập vào Media Library và chỉ điền vào vị trí đang trống hoặc còn dùng ảnh demo cũ.', 'cb-company-core') . '</p><p>' . esc_html__('Tài liệu chứng nhận demo có nhãn “Không dùng cho mục đích tuân thủ”; hãy thay bằng hồ sơ thật đã xác minh trước khi triển khai production.', 'cb-company-core') . '</p><p><strong>' . esc_html__('Nội dung:', 'cb-company-core') . '</strong> ' . ($demo['installed'] ? esc_html(sprintf(__('Đã cài, %d nội dung demo.', 'cb-company-core'), $demo['post_count'])) : esc_html__('Chưa cài dữ liệu mẫu.', 'cb-company-core')) . ' <strong>' . esc_html__('Hình ảnh:', 'cb-company-core') . '</strong> ' . esc_html(sprintf(__('%d ảnh trong Media Library.', 'cb-company-core'), $demo_images['attachment_count'])) . '</p><div class="cb-demo-actions">';
    foreach (['install_images' => __('Cài bộ hình ảnh mẫu', 'cb-company-core'), 'install_certificates' => __('Cài chứng nhận demo EN/ZH', 'cb-company-core'), 'install_menus' => __('Cài menu EN/ZH', 'cb-company-core'), 'delete_menus' => __('Xóa menu demo', 'cb-company-core'), 'delete_certificates' => __('Xóa chứng nhận demo', 'cb-company-core'), 'delete_images' => __('Xóa bộ hình ảnh mẫu', 'cb-company-core'), 'install' => __('Cài toàn bộ dữ liệu mẫu', 'cb-company-core'), 'delete' => __('Xóa dữ liệu mẫu', 'cb-company-core'), 'restore' => __('Khôi phục dữ liệu mẫu', 'cb-company-core'), 'check' => __('Kiểm tra dữ liệu mẫu', 'cb-company-core')] as $operation => $label) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="cb_demo_content"><input type="hidden" name="operation" value="' . esc_attr($operation) . '">';
        wp_nonce_field('cb_demo_content');
        echo '<button type="submit" class="button' . ($operation === 'install_images' ? ' button-primary' : '') . '">' . esc_html($label) . '</button></form>';
    }
    echo '</div></div>';
    cb_admin_shell_end();
}

function cb_handle_reset_settings()
{
    if (!current_user_can('manage_options')) wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'cb-company-core'), 403);
    check_admin_referer('cb_reset_settings');
    $option = sanitize_key(wp_unslash($_GET['option'] ?? ''));
    $defaults_map = ['cb_design_settings' => cb_default_design_settings(), 'cb_header_settings' => cb_default_header_settings(), 'cb_footer_settings' => cb_default_footer_settings(), 'cb_template_settings' => cb_default_template_settings(), 'cb_form_settings' => cb_default_form_settings(), 'cb_seo_settings' => cb_default_seo_settings(), 'cb_performance_settings' => cb_default_performance_settings()];
    if (!isset($defaults_map[$option])) wp_die(esc_html__('Nhóm cài đặt không hợp lệ.', 'cb-company-core'), 400);
    $defaults = $defaults_map[$option];
    $tab = sanitize_key(wp_unslash($_GET['tab'] ?? ''));
    if (empty($_GET['all']) && $tab && in_array($option, ['cb_design_settings', 'cb_header_settings', 'cb_footer_settings'], true)) {
        $schemas = ['cb_design_settings' => cb_design_settings_schema(), 'cb_header_settings' => cb_header_settings_schema(), 'cb_footer_settings' => cb_footer_settings_schema()];
        $current = cb_get_group_options($option, $defaults);
        foreach (($schemas[$option][$tab]['fields'] ?? []) as $field) {
            $key = $field[0];
            if ($field[1] === 'image_pair') {
                $current[$key . '_id'] = $defaults[$key . '_id'] ?? 0; $current[$key . '_url'] = $defaults[$key . '_url'] ?? '';
            } elseif ($field[1] === 'image') {
                $current[$key . '_id'] = $defaults[$key . '_id'] ?? 0; $current[$key] = $defaults[$key] ?? '';
            } else $current[$key] = $defaults[$key] ?? '';
        }
        update_option($option, $current);
    } else update_option($option, $defaults);
    wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=cb-company'));
    exit;
}

function cb_render_theme_options_page() { cb_render_design_settings_page(); }
