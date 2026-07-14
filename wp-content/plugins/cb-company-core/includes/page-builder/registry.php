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
    $types = array_keys(cb_section_types());
    $content = array_values(array_diff($types, ['hero_slider']));
    $listing = ['product_categories', 'featured_products', 'case_studies', 'news_section', 'gallery'];
    $repeaters = array_keys(cb_section_item_schemas());
    return [
        'admin_label' => ['label' => __('Nhãn quản trị', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $types],
        'eyebrow' => ['label' => __('Dòng nhấn', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => array_diff($content, ['spacer', 'content_editor'])],
        'title' => ['label' => __('Tiêu đề', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => array_diff($content, ['spacer', 'content_editor'])],
        'subtitle' => ['label' => __('Tiêu đề phụ', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => ['company_intro', 'inquiry_cta']],
        'description' => ['label' => __('Mô tả', 'cb-company-core'), 'type' => 'textarea', 'group' => 'content', 'for' => array_diff($content, ['spacer', 'content_editor'])],
        'button_text' => ['label' => __('Nhãn nút', 'cb-company-core'), 'type' => 'text', 'group' => 'content', 'for' => array_diff($content, ['spacer', 'content_editor', 'gallery'])],
        'button_url' => ['label' => __('Liên kết nút', 'cb-company-core'), 'type' => 'url', 'group' => 'content', 'for' => array_diff($content, ['spacer', 'content_editor', 'gallery'])],
        'image' => ['label' => __('Hình ảnh chính', 'cb-company-core'), 'type' => 'image', 'group' => 'images', 'for' => ['company_intro', 'factory_capability', 'inquiry_cta', 'contact_info']],
        'items' => ['label' => __('Danh sách nội dung', 'cb-company-core'), 'type' => 'section_repeater', 'group' => 'content', 'for' => $repeaters],
        'limit' => ['label' => __('Số mục hiển thị', 'cb-company-core'), 'type' => 'number', 'group' => 'content', 'for' => $listing],
        'layout_style' => ['label' => __('Kiểu bố cục', 'cb-company-core'), 'type' => 'select', 'group' => 'design', 'for' => $types, 'choices' => ['default' => __('Bố cục mặc định', 'cb-company-core'), 'split' => __('Chia hai cột', 'cb-company-core'), 'centered' => __('Căn giữa', 'cb-company-core'), 'image_left' => __('Ảnh bên trái', 'cb-company-core'), 'image_right' => __('Ảnh bên phải', 'cb-company-core'), 'grid' => __('Dạng lưới', 'cb-company-core'), 'carousel' => __('Băng chuyền', 'cb-company-core')]],
        'background_color' => ['label' => __('Màu nền', 'cb-company-core'), 'type' => 'color', 'group' => 'design', 'for' => $types],
        'background_image' => ['label' => __('Ảnh nền', 'cb-company-core'), 'type' => 'image', 'group' => 'images', 'for' => array_diff($content, ['content_editor', 'spacer'])],
        'text_color' => ['label' => __('Màu chữ', 'cb-company-core'), 'type' => 'color', 'group' => 'design', 'for' => array_diff($types, ['spacer'])],
        'card_style' => ['label' => __('Kiểu thẻ nội dung', 'cb-company-core'), 'type' => 'select', 'group' => 'design', 'for' => $listing, 'choices' => ['default' => __('Kiểu mặc định', 'cb-company-core'), 'clean' => __('Tối giản', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core'), 'bordered' => __('Có viền', 'cb-company-core')]],
        'columns_desktop' => ['label' => __('Số cột desktop', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $listing],
        'columns_tablet' => ['label' => __('Số cột tablet', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $listing],
        'columns_mobile' => ['label' => __('Số cột mobile', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $listing],
        'hide_mobile' => ['label' => __('Ẩn trên mobile', 'cb-company-core'), 'type' => 'checkbox', 'group' => 'responsive', 'for' => $types],
        'mobile_order' => ['label' => __('Thứ tự trên mobile', 'cb-company-core'), 'type' => 'number', 'group' => 'responsive', 'for' => $types],
        'padding_top' => ['label' => __('Khoảng cách phía trên', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $types],
        'padding_bottom' => ['label' => __('Khoảng cách phía dưới', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $types],
        'container_width' => ['label' => __('Chiều rộng container', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $types],
        'section_id' => ['label' => __('Anchor ID', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $types],
        'section_class' => ['label' => __('CSS class', 'cb-company-core'), 'type' => 'text', 'group' => 'advanced', 'for' => $types],
        'spacer_height' => ['label' => __('Chiều cao khoảng cách', 'cb-company-core'), 'type' => 'text', 'group' => 'design', 'for' => ['spacer']],
    ];
}

function cb_section_item_schemas()
{
    return [
        'company_intro' => [
            'number' => ['number', __('Số liệu', 'cb-company-core')],
            'suffix' => ['text', __('Hậu tố', 'cb-company-core')],
            'label' => ['text', __('Nhãn', 'cb-company-core')],
            'icon' => ['text', __('Biểu tượng', 'cb-company-core')],
        ],
        'why_choose_us' => [
            'enable' => ['checkbox', __('Bật mục', 'cb-company-core')],
            'icon' => ['text', __('Biểu tượng', 'cb-company-core')],
            'title' => ['text', __('Tiêu đề', 'cb-company-core')],
            'description' => ['textarea', __('Mô tả', 'cb-company-core')],
            'url' => ['url', __('Liên kết', 'cb-company-core')],
        ],
        'factory_capability' => [
            'enable' => ['checkbox', __('Bật mục', 'cb-company-core')],
            'title' => ['text', __('Tiêu đề', 'cb-company-core')],
            'description' => ['textarea', __('Mô tả', 'cb-company-core')],
            'image' => ['image', __('Hình ảnh', 'cb-company-core')],
            'url' => ['url', __('Liên kết', 'cb-company-core')],
        ],
        'oem_odm_process' => [
            'step_number' => ['text', __('Số bước', 'cb-company-core')],
            'icon' => ['text', __('Biểu tượng', 'cb-company-core')],
            'title' => ['text', __('Tiêu đề', 'cb-company-core')],
            'description' => ['textarea', __('Mô tả', 'cb-company-core')],
        ],
        'case_studies' => [
            'enable' => ['checkbox', __('Bật mục', 'cb-company-core')],
            'title' => ['text', __('Tiêu đề', 'cb-company-core')],
            'description' => ['textarea', __('Mô tả', 'cb-company-core')],
            'image' => ['image', __('Hình ảnh', 'cb-company-core')],
            'url' => ['url', __('Liên kết', 'cb-company-core')],
        ],
        'certificates' => [
            'enable' => ['checkbox', __('Bật mục', 'cb-company-core')],
            'title' => ['text', __('Tên chứng nhận', 'cb-company-core')],
            'description' => ['textarea', __('Mô tả', 'cb-company-core')],
            'image' => ['image', __('Logo chứng nhận', 'cb-company-core')],
        ],
        'inquiry_cta' => [
            'text' => ['text', __('Nhãn nút', 'cb-company-core')],
            'url' => ['url', __('Liên kết', 'cb-company-core')],
            'style' => ['select', __('Kiểu nút', 'cb-company-core'), ['primary' => __('Nút chính', 'cb-company-core'), 'secondary' => __('Nút phụ', 'cb-company-core')]],
        ],
        'contact_info' => [
            'icon' => ['text', __('Biểu tượng', 'cb-company-core')],
            'label' => ['text', __('Nhãn', 'cb-company-core')],
            'value' => ['text', __('Nội dung', 'cb-company-core')],
            'url' => ['url', __('Liên kết', 'cb-company-core')],
        ],
        'gallery' => [
            'enable' => ['checkbox', __('Bật mục', 'cb-company-core')],
            'image' => ['image', __('Hình ảnh', 'cb-company-core')],
            'image_alt' => ['text', __('Mô tả ảnh', 'cb-company-core')],
            'caption' => ['text', __('Chú thích', 'cb-company-core')],
        ],
    ];
}

function cb_hero_section_defaults()
{
    return [
        'min_height_desktop' => '560px',
        'min_height_mobile' => '460px',
        'content_width' => '650px',
        'autoplay' => '1',
        'autoplay_delay' => '6000',
        'transition_speed' => '500',
        'show_arrows' => '1',
        'show_dots' => '1',
        'pause_on_hover' => '1',
        'slides' => [],
    ];
}

function cb_hero_slide_defaults()
{
    return [
        'enable' => '1', 'admin_label' => '', 'image_id' => 0, 'image_url' => '',
        'mobile_image_id' => 0, 'mobile_image_url' => '', 'image_alt' => '', 'eyebrow' => '',
        'title' => '', 'highlight_text' => '', 'description' => '', 'primary_button_text' => '',
        'primary_button_url' => '', 'primary_button_style' => 'primary', 'secondary_button_text' => '',
        'secondary_button_url' => '', 'secondary_button_style' => 'outline', 'text_alignment' => 'left',
        'text_position' => 'left', 'overlay_enable' => '0', 'overlay_color' => '#ffffff',
        'overlay_opacity' => '0', 'trust_badges' => [],
    ];
}

function cb_default_builder_section($type = 'hero_slider')
{
    $section = [
        'enable' => '1', 'type' => $type, 'admin_label' => '', 'section_id' => '', 'section_class' => '',
        'layout_style' => 'default', 'background_color' => '', 'background_image_id' => 0,
        'background_image_url' => '', 'text_color' => '', 'padding_top' => '', 'padding_bottom' => '',
        'container_width' => '', 'eyebrow' => '', 'title' => '', 'subtitle' => '', 'description' => '',
        'button_text' => '', 'button_url' => '', 'image_id' => 0, 'image_url' => '', 'items' => [],
        'limit' => '', 'columns_desktop' => '', 'columns_tablet' => '', 'columns_mobile' => '',
        'card_style' => '', 'hide_mobile' => '0', 'mobile_order' => '', 'spacer_height' => '60px',
    ];
    return $type === 'hero_slider' ? array_merge($section, cb_hero_section_defaults()) : $section;
}

function cb_normalize_homepage_section($section)
{
    $section = (array) $section;
    $type = isset(cb_section_types()[$section['type'] ?? '']) ? $section['type'] : 'hero_slider';
    $section = wp_parse_args($section, cb_default_builder_section($type));
    if (empty($section['image_url']) && !empty($section['image'])) {
        $section['image_url'] = $section['image'];
    }
    if ($type === 'hero_slider') {
        $section['slides'] = array_map(static fn($slide) => wp_parse_args((array) $slide, cb_hero_slide_defaults()), (array) ($section['slides'] ?? []));
    } elseif (!is_array($section['items'])) {
        $section['items'] = cb_legacy_lines_to_repeater($section['items']);
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
