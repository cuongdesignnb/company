<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_render_page_builder_meta_box($post)
{
    wp_nonce_field('cb_save_page_builder', 'cb_page_builder_nonce');
    cb_render_page_builder_editor($post);
}

function cb_render_page_builder_editor($post, $args = [])
{
    $args = wp_parse_args($args, ['show_sync_tools' => true]);
    $mode = get_post_meta($post->ID, '_cb_page_render_mode', true) ?: 'editor';
    $sections = cb_get_page_sections($post->ID);
    echo '<div class="cb-page-builder" data-post-id="' . esc_attr((string) $post->ID) . '">';
    echo '<div class="cb-builder-mode"><label for="cb-page-render-mode-' . esc_attr((string) $post->ID) . '"><strong>' . esc_html__('Chế độ hiển thị', 'cb-company-core') . '</strong></label><select id="cb-page-render-mode-' . esc_attr((string) $post->ID) . '" name="_cb_page_render_mode">';
    foreach (['editor' => __('Dùng trình soạn thảo WordPress', 'cb-company-core'), 'builder' => __('Dùng trình dựng section', 'cb-company-core'), 'editor_and_builder' => __('Nội dung WordPress phía trên và section phía dưới', 'cb-company-core')] as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($mode, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></div>';
    if ($args['show_sync_tools']) {
        cb_render_builder_sync_tools($post);
    }
    echo '<div class="cb-builder-toolbar"><p>' . esc_html__('Kéo thả để sắp xếp. Mỗi khu vực được thu gọn để dễ quản lý.', 'cb-company-core') . '</p><button type="button" class="button button-primary cb-add-section"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ' . esc_html__('Thêm khu vực', 'cb-company-core') . '</button></div>';
    if (!$sections) {
        echo '<div class="cb-builder-empty"><p>' . esc_html__('Trang này chưa có section.', 'cb-company-core') . '</p><button type="button" class="button cb-initialize-builder">' . esc_html__('Khởi tạo bố cục trang', 'cb-company-core') . '</button></div>';
    }
    echo '<div class="cb-sections-list">';
    foreach ($sections as $index => $section) {
        cb_render_builder_section_card($index, $section, $post->ID);
    }
    echo '</div><template id="cb-section-template">';
    cb_render_builder_section_card('__new__', cb_default_builder_section('hero_slider'), $post->ID);
    echo '</template></div>';
}

function cb_render_builder_sync_tools($post)
{
    $pages = get_posts(['post_type' => 'page', 'post_status' => ['publish', 'draft', 'private'], 'posts_per_page' => -1, 'exclude' => [$post->ID], 'orderby' => 'title', 'order' => 'ASC']);
    $export_url = wp_nonce_url(admin_url('admin-post.php?action=cb_export_page_json&post_id=' . $post->ID), 'cb_export_page_' . $post->ID);
    echo '<details class="cb-builder-tools"><summary>' . esc_html__('Công cụ dữ liệu và bản dịch', 'cb-company-core') . '</summary><div class="cb-builder-tools-grid">';
    echo '<label>' . esc_html__('Sao chép bố cục từ bản dịch', 'cb-company-core') . '<select name="_cb_sync_source"><option value="0">' . esc_html__('Không sao chép', 'cb-company-core') . '</option>';
    foreach ($pages as $page) {
        $lang = get_post_meta($page->ID, '_cb_language', true) ?: 'en';
        echo '<option value="' . esc_attr((string) $page->ID) . '">[' . esc_html(strtoupper($lang)) . '] ' . esc_html($page->post_title) . '</option>';
    }
    echo '</select></label><label>' . esc_html__('Phạm vi sao chép', 'cb-company-core') . '<select name="_cb_sync_scope"><option value="layout">' . esc_html__('Chỉ giao diện và thứ tự section', 'cb-company-core') . '</option><option value="style">' . esc_html__('Chỉ màu, nền và responsive', 'cb-company-core') . '</option><option value="all">' . esc_html__('Sao chép cả nội dung', 'cb-company-core') . '</option></select></label>';
    echo '<label class="cb-wide">' . esc_html__('Nhập JSON', 'cb-company-core') . '<textarea rows="4" name="_cb_builder_import_json" placeholder="{ &quot;sections&quot;: [...] }"></textarea></label>';
    echo '<p><a class="button" href="' . esc_url($export_url) . '"><span class="dashicons dashicons-download" aria-hidden="true"></span> ' . esc_html__('Xuất JSON', 'cb-company-core') . '</a></p></div></details>';
}

function cb_render_builder_section_card($index, $section, $post_id = 0)
{
    $section = cb_normalize_homepage_section($section);
    $types = cb_section_types();
    $type = $section['type'];
    $title = $section['admin_label'] ?: ($section['title'] ?: $types[$type]);
    $number = is_numeric($index) ? (int) $index + 1 : 1;
    $summary = $type === 'hero_slider'
        ? sprintf(_n('%d slide', '%d slide', count($section['slides']), 'cb-company-core'), count($section['slides']))
        : ($types[$type] ?? $type);
    $image_selected = $type === 'hero_slider' && !empty($section['slides'][0]['image_url']);
    echo '<article class="cb-section-card is-collapsed" data-section-type="' . esc_attr($type) . '">';
    echo '<div class="cb-section-head"><button type="button" class="cb-icon-button cb-drag-handle" aria-label="' . esc_attr__('Kéo để sắp xếp', 'cb-company-core') . '"><span class="dashicons dashicons-move" aria-hidden="true"></span></button>';
    echo '<span class="cb-section-title"><span class="cb-section-number">' . esc_html((string) $number) . '</span>. <span class="cb-section-title-text">' . esc_html($title) . '</span><small><span class="cb-section-summary">' . esc_html($summary) . '</span> · <span class="cb-enable-summary">' . (($section['enable'] ?? '1') === '1' ? esc_html__('Đang bật', 'cb-company-core') : esc_html__('Đang tắt', 'cb-company-core')) . '</span>';
    if ($type === 'hero_slider') {
        echo ' · <span class="cb-image-summary">' . ($image_selected ? esc_html__('Đã chọn ảnh desktop', 'cb-company-core') : esc_html__('Chưa chọn ảnh desktop', 'cb-company-core')) . '</span>';
    }
    if ($post_id) {
        echo ' · ' . esc_html(sprintf(__('Cập nhật %s', 'cb-company-core'), get_the_modified_date('d/m/Y H:i', $post_id)));
    }
    echo '</small></span>';
    echo '<label class="cb-inline-toggle"><input type="hidden" name="_cb_page_sections[' . esc_attr((string) $index) . '][enable]" value="0"><input type="checkbox" name="_cb_page_sections[' . esc_attr((string) $index) . '][enable]" value="1" ' . checked($section['enable'], '1', false) . '> ' . esc_html__('Bật', 'cb-company-core') . '</label>';
    echo '<div class="cb-section-actions"><button type="button" class="button cb-collapse-section"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span><span class="cb-collapse-label">' . esc_html__('Mở rộng', 'cb-company-core') . '</span></button><button type="button" class="button cb-duplicate-section"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span> ' . esc_html__('Nhân bản', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-section">' . esc_html__('Xóa', 'cb-company-core') . '</button></div></div>';
    echo '<div class="cb-section-body"><div class="cb-section-type-row"><label>' . esc_html__('Loại khu vực', 'cb-company-core') . '<select class="cb-section-type-select" name="_cb_page_sections[' . esc_attr((string) $index) . '][type]">';
    foreach ($types as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($type, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></label></div>';
    $tabs = ['content' => __('Nội dung', 'cb-company-core'), 'images' => __('Hình ảnh', 'cb-company-core'), 'design' => __('Giao diện', 'cb-company-core'), 'responsive' => __('Responsive', 'cb-company-core'), 'advanced' => __('Nâng cao', 'cb-company-core')];
    echo '<nav class="cb-section-tabs" role="tablist">';
    foreach ($tabs as $group => $label) {
        echo '<button type="button" class="cb-section-tab ' . ($group === 'content' ? 'is-active' : '') . '" data-tab="' . esc_attr($group) . '">' . esc_html($label) . '</button>';
    }
    echo '</nav><div class="cb-section-fields">';
    if ($type === 'hero_slider') {
        cb_render_hero_builder_fields($index, $section);
    }
    foreach (cb_builder_field_registry() as $key => $field) {
        if ($key === 'items') {
            continue;
        }
        cb_render_builder_field($index, $key, $field, $section);
    }
    foreach (cb_section_item_schemas() as $schema_type => $schema) {
        echo '<div class="cb-builder-field cb-wide" data-field-group="content" data-visible-for="' . esc_attr($schema_type) . '"><label>' . esc_html__('Danh sách nội dung', 'cb-company-core') . '</label>';
        cb_render_section_repeater($schema_type, '_cb_page_sections[' . $index . '][items]', $schema_type === $type ? (array) ($section['items'] ?? []) : []);
        echo '</div>';
    }
    echo '</div></div></article>';
}

function cb_render_hero_builder_fields($index, $section)
{
    $base = '_cb_page_sections[' . $index . ']';
    echo '<div class="cb-builder-field cb-wide cb-hero-slides-field" data-field-group="hero" data-visible-for="hero_slider"><div class="cb-field-heading"><strong>' . esc_html__('Slides', 'cb-company-core') . '</strong><button type="button" class="button cb-add-hero-slide"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ' . esc_html__('Thêm slide', 'cb-company-core') . '</button></div><div class="cb-hero-slides">';
    foreach ((array) $section['slides'] as $slide_index => $slide) {
        cb_render_hero_slide($base, $slide_index, $slide);
    }
    echo '</div><template class="cb-hero-slide-template">';
    cb_render_hero_slide($base, '__slide__', cb_hero_slide_defaults());
    echo '</template></div>';
    $fields = [
        ['layout_style', 'select', __('Kiểu bố cục', 'cb-company-core'), 'design', ['default' => __('Bố cục mặc định', 'cb-company-core'), 'full_width' => __('Toàn chiều rộng', 'cb-company-core'), 'centered' => __('Căn giữa', 'cb-company-core')]],
        ['background_color', 'color', __('Màu nền dự phòng', 'cb-company-core'), 'design'],
        ['min_height_desktop', 'text', __('Chiều cao tối thiểu desktop', 'cb-company-core'), 'design'],
        ['content_width', 'text', __('Chiều rộng nội dung', 'cb-company-core'), 'design'],
        ['min_height_mobile', 'text', __('Chiều cao tối thiểu mobile', 'cb-company-core'), 'responsive'],
        ['autoplay', 'checkbox', __('Tự động chuyển slide', 'cb-company-core'), 'advanced'],
        ['autoplay_delay', 'number', __('Thời gian giữ slide (ms)', 'cb-company-core'), 'advanced'],
        ['transition_speed', 'number', __('Tốc độ chuyển cảnh (ms)', 'cb-company-core'), 'advanced'],
        ['show_arrows', 'checkbox', __('Hiện nút điều hướng', 'cb-company-core'), 'advanced'],
        ['show_dots', 'checkbox', __('Hiện chấm điều hướng', 'cb-company-core'), 'advanced'],
        ['pause_on_hover', 'checkbox', __('Tạm dừng khi hover hoặc focus', 'cb-company-core'), 'advanced'],
    ];
    foreach ($fields as $field) {
        cb_render_compact_builder_control($base, $field[0], $field[1], $field[2], $field[3], $section[$field[0]] ?? '', $field[4] ?? []);
    }
}

function cb_render_hero_slide($section_base, $index, $slide)
{
    $slide = wp_parse_args((array) $slide, cb_hero_slide_defaults());
    $base = $section_base . '[slides][' . $index . ']';
    $number = is_numeric($index) ? (int) $index + 1 : 1;
    echo '<article class="cb-hero-slide"><header class="cb-repeater-head"><span class="dashicons dashicons-move cb-hero-slide-handle" aria-hidden="true"></span><strong>' . esc_html__('Slide', 'cb-company-core') . ' <span class="cb-hero-slide-number">' . esc_html((string) $number) . '</span></strong><span class="cb-repeater-spacer"></span><label><input type="hidden" name="' . esc_attr($base . '[enable]') . '" value="0"><input type="checkbox" name="' . esc_attr($base . '[enable]') . '" value="1" ' . checked($slide['enable'], '1', false) . '> ' . esc_html__('Bật', 'cb-company-core') . '</label><button type="button" class="button-link cb-duplicate-hero-slide">' . esc_html__('Nhân bản', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-hero-slide">' . esc_html__('Xóa', 'cb-company-core') . '</button></header><div class="cb-hero-slide-fields">';
    $text_fields = [
        ['admin_label', 'text', __('Nhãn quản trị', 'cb-company-core')], ['eyebrow', 'text', __('Dòng nhấn', 'cb-company-core')],
        ['title', 'text', __('Tiêu đề', 'cb-company-core')], ['highlight_text', 'text', __('Phần text được tô màu', 'cb-company-core')],
        ['description', 'textarea', __('Mô tả', 'cb-company-core')], ['image_alt', 'text', __('Mô tả ảnh', 'cb-company-core')],
        ['primary_button_text', 'text', __('Nhãn nút chính', 'cb-company-core')], ['primary_button_url', 'url', __('Liên kết nút chính', 'cb-company-core')],
        ['secondary_button_text', 'text', __('Nhãn nút phụ', 'cb-company-core')], ['secondary_button_url', 'url', __('Liên kết nút phụ', 'cb-company-core')],
    ];
    foreach ($text_fields as $field) {
        cb_render_plain_input($base, $field[0], $field[1], $field[2], $slide[$field[0]], $field[0] === 'image_alt' ? 'images' : 'content');
    }
    cb_render_builder_image_control($base, 'image', $slide, __('Ảnh desktop', 'cb-company-core'), 'images');
    cb_render_builder_image_control($base, 'mobile_image', $slide, __('Ảnh mobile', 'cb-company-core'), 'images');
    foreach ([
        ['primary_button_style', __('Kiểu nút chính', 'cb-company-core'), ['primary' => __('Nút chính', 'cb-company-core'), 'outline' => __('Viền', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core')]],
        ['secondary_button_style', __('Kiểu nút phụ', 'cb-company-core'), ['primary' => __('Nút chính', 'cb-company-core'), 'outline' => __('Viền', 'cb-company-core'), 'soft' => __('Nền nhẹ', 'cb-company-core')]],
        ['text_alignment', __('Căn chữ', 'cb-company-core'), ['left' => __('Trái', 'cb-company-core'), 'center' => __('Giữa', 'cb-company-core'), 'right' => __('Phải', 'cb-company-core')]],
        ['text_position', __('Vị trí nội dung', 'cb-company-core'), ['left' => __('Trái', 'cb-company-core'), 'center' => __('Giữa', 'cb-company-core'), 'right' => __('Phải', 'cb-company-core')]],
    ] as $select) {
        cb_render_plain_select($base, $select[0], $select[1], $slide[$select[0]], $select[2], 'design');
    }
    cb_render_plain_checkbox($base, 'overlay_enable', __('Bật overlay', 'cb-company-core'), $slide['overlay_enable'], 'design');
    cb_render_plain_input($base, 'overlay_color', 'color', __('Màu overlay', 'cb-company-core'), $slide['overlay_color'], 'design');
    cb_render_plain_input($base, 'overlay_opacity', 'number', __('Độ mờ overlay (%)', 'cb-company-core'), $slide['overlay_opacity'], 'design');
    cb_render_trust_badges($base, $slide['trust_badges']);
    echo '</div></article>';
}

function cb_render_trust_badges($slide_base, $badges)
{
    echo '<div class="cb-trust-badges cb-wide" data-hero-group="content"><strong>' . esc_html__('Trust badges', 'cb-company-core') . '</strong><div class="cb-trust-badge-list">';
    foreach ((array) $badges as $index => $badge) {
        cb_render_trust_badge_row($slide_base, $index, $badge);
    }
    echo '</div><button type="button" class="button cb-add-trust-badge">' . esc_html__('Thêm trust badge', 'cb-company-core') . '</button><template class="cb-trust-badge-template">';
    cb_render_trust_badge_row($slide_base, '__badge__', []);
    echo '</template></div>';
}

function cb_render_trust_badge_row($slide_base, $index, $badge)
{
    $base = $slide_base . '[trust_badges][' . $index . ']';
    echo '<div class="cb-trust-badge-row"><span class="dashicons dashicons-move cb-trust-badge-handle" aria-hidden="true"></span><input type="text" name="' . esc_attr($base . '[icon]') . '" value="' . esc_attr($badge['icon'] ?? '') . '" placeholder="' . esc_attr__('Tên icon', 'cb-company-core') . '"><input type="text" name="' . esc_attr($base . '[text]') . '" value="' . esc_attr($badge['text'] ?? '') . '" placeholder="' . esc_attr__('Nội dung badge', 'cb-company-core') . '"><button type="button" class="button-link-delete cb-remove-trust-badge">' . esc_html__('Xóa', 'cb-company-core') . '</button></div>';
}

function cb_render_builder_field($index, $key, $field, $section)
{
    $base = '_cb_page_sections[' . $index . ']';
    $name = $base . '[' . $key . ']';
    $value = $section[$key] ?? '';
    $visible = implode(',', $field['for']);
    $classes = 'cb-builder-field' . (in_array($field['type'], ['textarea', 'section_repeater', 'image'], true) ? ' cb-wide' : '');
    echo '<div class="' . esc_attr($classes) . '" data-field-group="' . esc_attr($field['group']) . '" data-visible-for="' . esc_attr($visible) . '"><label>' . esc_html($field['label']);
    if ($field['type'] === 'select') {
        echo '<select name="' . esc_attr($name) . '">';
        foreach ($field['choices'] as $option => $label) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($value, $option, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    } elseif ($field['type'] === 'textarea') {
        echo '<textarea rows="4" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
    } elseif ($field['type'] === 'checkbox') {
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="0"><input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . checked($value, '1', false) . '>';
    } elseif ($field['type'] === 'image') {
        echo '</label>';
        cb_render_builder_image_control($base, $key, $section);
        echo '</div>';
        return;
    } elseif ($field['type'] === 'section_repeater') {
        echo '</label>';
        cb_render_section_repeater($section['type'], $name, is_array($value) ? $value : cb_legacy_lines_to_repeater($value));
        echo '</div>';
        return;
    } elseif ($field['type'] === 'color') {
        echo '<input class="cb-color-field" type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" data-default-color="">';
    } else {
        $input_type = $field['type'] === 'url' ? 'text' : $field['type'];
        $inputmode = $field['type'] === 'url' ? ' inputmode="url"' : '';
        echo '<input type="' . esc_attr($input_type) . '"' . $inputmode . ' name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
    }
    echo '</label></div>';
}

function cb_render_section_repeater($type, $name, $items)
{
    echo '<div class="cb-repeater cb-section-repeater" data-name-base="' . esc_attr($name) . '" data-item-schema="' . esc_attr($type) . '"><div class="cb-repeater-list">';
    foreach ($items as $index => $item) {
        cb_render_section_repeater_row($type, $name, $index, $item);
    }
    echo '</div><button type="button" class="button cb-add-repeater-item"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ' . esc_html__('Thêm mục', 'cb-company-core') . '</button><template class="cb-repeater-template">';
    cb_render_section_repeater_row($type, $name, '__item__', []);
    echo '</template></div>';
}

function cb_render_section_repeater_row($type, $name, $index, $item)
{
    $base = $name . '[' . $index . ']';
    echo '<div class="cb-repeater-row"><div class="cb-repeater-head"><span class="dashicons dashicons-move cb-repeater-handle" aria-hidden="true"></span><strong>' . esc_html__('Mục', 'cb-company-core') . ' <span class="cb-repeater-number">' . esc_html((string) ((int) $index + 1)) . '</span></strong><span class="cb-repeater-spacer"></span><button type="button" class="button-link cb-duplicate-repeater">' . esc_html__('Nhân bản', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-repeater">' . esc_html__('Xóa', 'cb-company-core') . '</button></div><div class="cb-repeater-fields">';
    foreach ((cb_section_item_schemas()[$type] ?? []) as $key => $field) {
        $field_type = $field[0];
        if ($field_type === 'image') {
            cb_render_builder_image_control($base, 'image', $item, $field[1]);
        } elseif ($field_type === 'select') {
            cb_render_plain_select($base, $key, $field[1], $item[$key] ?? '', $field[2] ?? []);
        } elseif ($field_type === 'checkbox') {
            cb_render_plain_checkbox($base, $key, $field[1], $item[$key] ?? '1');
        } else {
            cb_render_plain_input($base, $key, $field_type, $field[1], $item[$key] ?? '');
        }
    }
    echo '</div></div>';
}

function cb_render_compact_builder_control($base, $key, $type, $label, $group, $value, $choices = [])
{
    echo '<div class="cb-builder-field" data-field-group="' . esc_attr($group) . '" data-visible-for="hero_slider">';
    if ($type === 'select') {
        cb_render_plain_select($base, $key, $label, $value, $choices);
    } elseif ($type === 'checkbox') {
        cb_render_plain_checkbox($base, $key, $label, $value);
    } else {
        cb_render_plain_input($base, $key, $type, $label, $value);
    }
    echo '</div>';
}

function cb_render_plain_input($base, $key, $type, $label, $value, $hero_group = '')
{
    $wide = $type === 'textarea' ? ' class="cb-wide"' : '';
    $group_attr = $hero_group ? ' data-hero-group="' . esc_attr($hero_group) . '"' : '';
    echo '<label' . $wide . $group_attr . '>' . esc_html($label);
    if ($type === 'textarea') {
        echo '<textarea rows="3" name="' . esc_attr($base . '[' . $key . ']') . '">' . esc_textarea($value) . '</textarea>';
    } elseif ($type === 'color') {
        echo '<input class="cb-color-field" type="text" name="' . esc_attr($base . '[' . $key . ']') . '" value="' . esc_attr($value) . '" data-default-color="">';
    } else {
        $input_type = $type === 'url' ? 'text' : $type;
        $inputmode = $type === 'url' ? ' inputmode="url"' : '';
        echo '<input type="' . esc_attr($input_type) . '"' . $inputmode . ' name="' . esc_attr($base . '[' . $key . ']') . '" value="' . esc_attr($value) . '">';
    }
    echo '</label>';
}

function cb_render_plain_select($base, $key, $label, $value, $choices, $hero_group = '')
{
    $group_attr = $hero_group ? ' data-hero-group="' . esc_attr($hero_group) . '"' : '';
    echo '<label' . $group_attr . '>' . esc_html($label) . '<select name="' . esc_attr($base . '[' . $key . ']') . '">';
    foreach ($choices as $option => $option_label) {
        echo '<option value="' . esc_attr($option) . '" ' . selected($value, $option, false) . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select></label>';
}

function cb_render_plain_checkbox($base, $key, $label, $value, $hero_group = '')
{
    $group_attr = $hero_group ? ' data-hero-group="' . esc_attr($hero_group) . '"' : '';
    echo '<label' . $group_attr . '><input type="hidden" name="' . esc_attr($base . '[' . $key . ']') . '" value="0"><input type="checkbox" name="' . esc_attr($base . '[' . $key . ']') . '" value="1" ' . checked($value, '1', false) . '> ' . esc_html($label) . '</label>';
}

function cb_render_builder_image_control($base, $key, $values, $label = '', $hero_group = '')
{
    $url = $values[$key . '_url'] ?? '';
    $group_attr = $hero_group ? ' data-hero-group="' . esc_attr($hero_group) . '"' : '';
    echo '<div class="cb-image-field" data-frame-title="' . esc_attr__('Chọn hình ảnh', 'cb-company-core') . '"' . $group_attr . '>';
    if ($label) {
        echo '<span class="cb-repeater-field-label">' . esc_html($label) . '</span>';
    }
    echo '<div class="cb-image-preview">' . ($url ? '<img src="' . esc_url($url) . '" alt="">' : '<span>' . esc_html__('Chưa chọn ảnh', 'cb-company-core') . '</span>') . '</div><input class="cb-image-id" type="hidden" name="' . esc_attr($base . '[' . $key . '_id]') . '" value="' . esc_attr((string) absint($values[$key . '_id'] ?? 0)) . '"><input class="cb-image-url" type="hidden" name="' . esc_attr($base . '[' . $key . '_url]') . '" value="' . esc_attr($url) . '"><button type="button" class="button cb-pick-image">' . esc_html__('Chọn ảnh', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-image">' . esc_html__('Xóa ảnh', 'cb-company-core') . '</button></div>';
}

function cb_render_page_ui_meta_box($post)
{
    $values = get_post_meta($post->ID, '_cb_page_ui', true);
    $values = is_array($values) ? $values : [];
    echo '<div class="cb-page-ui"><nav class="cb-meta-tabs">';
    foreach (cb_page_ui_schema() as $key => $tab) {
        echo '<button type="button" class="cb-meta-tab ' . ($key === 'layout' ? 'is-active' : '') . '" data-tab="' . esc_attr($key) . '">' . esc_html($tab['label']) . '</button>';
    }
    echo '</nav>';
    foreach (cb_page_ui_schema() as $tab_key => $tab) {
        echo '<div class="cb-meta-panel ' . ($tab_key === 'layout' ? 'is-active' : '') . '" data-panel="' . esc_attr($tab_key) . '">';
        foreach ($tab['fields'] as $key => $field) {
            cb_render_page_override_field($key, $field, $values);
        }
        echo '</div>';
    }
    echo '</div>';
}

function cb_render_page_override_field($key, $field, $values)
{
    $has_override = array_key_exists($key, $values);
    $value = $has_override ? $values[$key] : '';
    $base = '_cb_page_ui[' . $key . ']';
    echo '<div class="cb-page-override"><label class="cb-override-switch"><input type="checkbox" name="' . esc_attr($base . '[override]') . '" value="1" ' . checked($has_override, true, false) . '> ' . esc_html__('Tùy chỉnh riêng', 'cb-company-core') . '</label><div class="cb-override-control"><strong>' . esc_html($field[0]) . '</strong>';
    if ($field[1] === 'select') {
        echo '<select name="' . esc_attr($base . '[value]') . '"><option value="">' . esc_html__('Sử dụng thiết lập mặc định', 'cb-company-core') . '</option>';
        foreach ($field[2] as $option => $label) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($value, $option, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    } elseif ($field[1] === 'textarea') {
        echo '<textarea rows="3" name="' . esc_attr($base . '[value]') . '">' . esc_textarea($value) . '</textarea>';
    } elseif ($field[1] === 'checkbox') {
        echo '<input type="hidden" name="' . esc_attr($base . '[value]') . '" value="0"><input type="checkbox" name="' . esc_attr($base . '[value]') . '" value="1" ' . checked($value, '1', false) . '>';
    } elseif ($field[1] === 'image') {
        echo '<div class="cb-image-field"><div class="cb-image-preview">' . ($value ? '<img src="' . esc_url($value) . '" alt="">' : '<span>' . esc_html__('Chưa chọn ảnh', 'cb-company-core') . '</span>') . '</div><input class="cb-image-id" type="hidden" name="' . esc_attr($base . '[image_id]') . '" value="' . esc_attr((string) absint($values[$key . '_id'] ?? 0)) . '"><input class="cb-image-url" type="hidden" name="' . esc_attr($base . '[value]') . '" value="' . esc_attr($value) . '"><button type="button" class="button cb-pick-image">' . esc_html__('Chọn ảnh', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-image">' . esc_html__('Xóa ảnh', 'cb-company-core') . '</button></div>';
    } else {
        echo '<input type="' . esc_attr($field[1]) . '" name="' . esc_attr($base . '[value]') . '" value="' . esc_attr($value) . '">';
    }
    echo '</div></div>';
}
