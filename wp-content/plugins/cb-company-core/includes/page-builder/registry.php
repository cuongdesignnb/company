<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_section_types()
{
    return [
        'hero_slider' => __('Banner chính', 'cb-company-core'),
        'company_intro' => __('Giới thiệu công ty', 'cb-company-core'),
        'product_categories' => __('Danh mục sản phẩm', 'cb-company-core'),
        'featured_products' => __('Sản phẩm nổi bật', 'cb-company-core'),
        'why_choose_us' => __('Lý do chọn chúng tôi', 'cb-company-core'),
        'factory_capability' => __('Năng lực nhà máy', 'cb-company-core'),
        'oem_odm_process' => __('Quy trình OEM/ODM', 'cb-company-core'),
        'case_studies' => __('Dự án tiêu biểu', 'cb-company-core'),
        'certificates' => __('Chứng nhận', 'cb-company-core'),
        'news_section' => __('Tin tức', 'cb-company-core'),
        'inquiry_cta' => __('Kêu gọi gửi yêu cầu', 'cb-company-core'),
        'contact_info' => __('Thông tin liên hệ', 'cb-company-core'),
        'content_editor' => __('Nội dung từ trình soạn thảo', 'cb-company-core'),
        'gallery' => __('Thư viện hình ảnh', 'cb-company-core'),
        'spacer' => __('Khoảng cách', 'cb-company-core'),
    ];
}

function cb_builder_field_registry()
{
    $content_types = array_keys(cb_section_types());
    $listing_types = ['product_categories', 'featured_products', 'case_studies', 'news_section', 'gallery'];
    $repeater_types = ['hero_slider', 'company_intro', 'why_choose_us', 'factory_capability', 'oem_odm_process', 'case_studies', 'certificates', 'inquiry_cta', 'contact_info', 'gallery'];
    return [
        'admin_label' => ['label' => __('Nhãn quản trị', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $content_types],
        'eyebrow' => ['label' => __('Dòng nhấn', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => array_diff($content_types, ['spacer', 'content_editor'])],
        'title' => ['label' => __('Tiêu đề', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => array_diff($content_types, ['spacer', 'content_editor'])],
        'subtitle' => ['label' => __('Tiêu đề phụ', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => ['hero_slider', 'company_intro', 'inquiry_cta']],
        'description' => ['label' => __('Mô tả', 'cb-company-core'), 'type' => 'textarea', 'group' => 'content', 'for' => array_diff($content_types, ['spacer', 'content_editor'])],
        'button_text' => ['label' => __('Nhãn nút', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => array_diff($content_types, ['spacer', 'content_editor', 'gallery'])],
        'button_url' => ['label' => __('Liên kết nút', 'cb-company-core'), 'type' => 'url', 'group' => 'content', 'for' => array_diff($content_types, ['spacer', 'content_editor', 'gallery'])],
        'image' => ['label' => __('Hình ảnh chính', 'cb-company-core'), 'type' => 'image', 'group' => 'content', 'for' => ['hero_slider', 'company_intro', 'factory_capability', 'inquiry_cta', 'contact_info']],
        'items' => ['label' => __('Danh sách nội dung', 'cb-company-core'), 'type' => 'repeater', 'group' => 'content', 'for' => $repeater_types],
        'limit' => ['label' => __('Số mục hiển thị', 'cb-company-core'), 'type' => 'number', 'group' => 'content', 'for' => $listing_types],
        'layout_style' => ['label' => __('Kiểu bố cục', 'cb-company-core'), 'type' => 'select', 'group' => 'design', 'for' => $content_types, 'choices' => ['default' => __('Mặc định', 'cb-company-core'), 'split' => __('Chia hai cột', 'cb-company-core'), 'centered' => __('Căn giữa', 'cb-company-core'), 'image_left' => __('Ảnh bên trái', 'cb-company-core'), 'image_right' => __('Ảnh bên phải', 'cb-company-core'), 'grid' => __('Dạng lưới', 'cb-company-core'), 'carousel' => __('Băng chuyền', 'cb-company-core')]],
        'background_color' => ['label' => __('Màu nền', 'cb-company-core'), 'type' => 'color', 'group' => 'design', 'for' => $content_types],
        'background_image' => ['label' => __('Ảnh nền', 'cb-company-core'), 'type' => 'image', 'group' => 'design', 'for' => array_diff($content_types, ['content_editor', 'spacer'])],
        'text_color' => ['label' => __('Màu chữ', 'cb-company-core'), 'type' => 'color', 'group' => 'design', 'for' => array_diff($content_types, ['spacer'])],
        'card_style' => ['label' => __('Kiểu thẻ nội dung', 'cb-company-core'), 'type' => 'select', 'group' => 'design', 'for' => $listing_types, 'choices' => ['default' => __('Mặc định', 'cb-company-core'), 'clean' => __('Tối giản', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core'), 'bordered' => __('Có viền', 'cb-company-core')]],
        'columns_desktop' => ['label' => __('Số cột desktop', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $listing_types],
        'columns_tablet' => ['label' => __('Số cột tablet', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $listing_types],
        'columns_mobile' => ['label' => __('Số cột mobile', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $listing_types],
        'hide_mobile' => ['label' => __('Ẩn trên mobile', 'cb-company-core'), 'type' => 'checkbox', 'group' => 'responsive', 'for' => $content_types],
        'mobile_order' => ['label' => __('Thứ tự trên mobile', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $content_types],
        'padding_top' => ['label' => __('Padding phía trên', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $content_types],
        'padding_bottom' => ['label' => __('Padding phía dưới', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $content_types],
        'container_width' => ['label' => __('Chiều rộng container', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $content_types],
        'section_id' => ['label' => __('Anchor ID', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $content_types],
        'section_class' => ['label' => __('CSS class', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $content_types],
        'spacer_height' => ['label' => __('Chiều cao khoảng cách', 'cb-company-core'), 'type' => 'text', 'group' => 'design', 'for' => ['spacer']],
    ];
}

function cb_default_builder_section($type = 'hero_slider')
{
    return [
        'enable' => '1', 'type' => $type, 'admin_label' => '', 'section_id' => '', 'section_class' => '',
        'layout_style' => 'default', 'background_color' => '', 'background_image_id' => 0,
        'background_image_url' => '', 'text_color' => '', 'padding_top' => '', 'padding_bottom' => '',
        'container_width' => '', 'eyebrow' => '', 'title' => '', 'subtitle' => '', 'description' => '',
        'button_text' => '', 'button_url' => '', 'image_id' => 0, 'image_url' => '', 'items' => [],
        'limit' => '', 'columns_desktop' => '', 'columns_tablet' => '', 'columns_mobile' => '',
        'card_style' => '', 'hide_mobile' => '0', 'mobile_order' => '', 'spacer_height' => '60px',
    ];
}

function cb_normalize_homepage_section($section)
{
    $section = (array) $section;
    $type = isset(cb_section_types()[$section['type'] ?? '']) ? $section['type'] : 'hero_slider';
    $section = wp_parse_args($section, cb_default_builder_section($type));
    if (empty($section['image_url']) && !empty($section['image'])) {
        $section['image_url'] = $section['image'];
    }
    if (!is_array($section['items'])) {
        $section['items'] = cb_legacy_lines_to_repeater($section['items']);
    }
    if (!empty($section['hero_slides']) && is_array($section['hero_slides']) && empty($section['items'])) {
        $section['items'] = array_map(static function ($slide) {
            return [
                'title' => $slide['title'] ?? '', 'description' => $slide['description'] ?? '',
                'image_id' => absint($slide['image_id'] ?? 0), 'image_url' => $slide['image_url'] ?? '',
                'url' => $slide['button_1_url'] ?? '',
            ];
        }, $section['hero_slides']);
    }
    return $section;
}

function cb_legacy_lines_to_repeater($value)
{
    $items = [];
    foreach (cb_parse_lines((string) $value) as $item) {
        $items[] = [
            'title' => $item['label'], 'description' => $item['value'], 'image_id' => 0,
            'image_url' => $item['image'], 'url' => $item['url'] ?? '',
        ];
    }
    return $items;
}
