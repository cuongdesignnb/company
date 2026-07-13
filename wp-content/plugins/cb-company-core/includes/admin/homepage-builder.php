<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_render_page_builder_meta_box($post)
{
    wp_nonce_field('cb_save_page_builder', 'cb_page_builder_nonce');
    $mode = get_post_meta($post->ID, '_cb_page_render_mode', true) ?: 'editor';
    $sections = cb_get_page_sections($post->ID);
    echo '<div class="cb-page-builder" data-post-id="' . esc_attr((string) $post->ID) . '">';
    echo '<div class="cb-builder-mode"><label for="cb-page-render-mode"><strong>' . esc_html__('Chế độ hiển thị', 'cb-company-core') . '</strong></label><select id="cb-page-render-mode" name="_cb_page_render_mode">';
    foreach (['editor' => __('Dùng trình soạn thảo WordPress', 'cb-company-core'), 'builder' => __('Dùng trình dựng section', 'cb-company-core'), 'editor_and_builder' => __('Nội dung WordPress phía trên và section phía dưới', 'cb-company-core')] as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($mode, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></div>';
    cb_render_builder_sync_tools($post);
    echo '<div class="cb-builder-toolbar"><p>' . esc_html__('Kéo thả để sắp xếp. Mỗi khu vực mặc định được thu gọn để dễ quản lý.', 'cb-company-core') . '</p><button type="button" class="button button-primary cb-add-section"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ' . esc_html__('Thêm khu vực', 'cb-company-core') . '</button></div>';
    echo '<div class="cb-sections-list">';
    foreach ($sections as $index => $section) {
        cb_render_builder_section_card($index, $section);
    }
    echo '</div><script type="text/html" id="cb-section-template">';
    cb_render_builder_section_card('__new__', cb_default_builder_section('hero_slider'));
    echo '</script></div>';
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

function cb_render_builder_section_card($index, $section)
{
    $section = cb_normalize_homepage_section($section);
    $types = cb_section_types();
    $type = $section['type'];
    $title = $section['admin_label'] ?: ($section['title'] ?: $types[$type]);
    $number = is_numeric($index) ? (int) $index + 1 : 1;
    echo '<article class="cb-section-card is-collapsed" data-section-type="' . esc_attr($type) . '">';
    echo '<div class="cb-section-head"><button type="button" class="cb-icon-button cb-drag-handle" aria-label="' . esc_attr__('Kéo để sắp xếp', 'cb-company-core') . '"><span class="dashicons dashicons-move" aria-hidden="true"></span></button>';
    echo '<span class="cb-section-title"><span class="cb-section-number">' . esc_html((string) $number) . '</span>. <span class="cb-section-title-text">' . esc_html($title) . '</span><small>' . esc_html__('Bố cục:', 'cb-company-core') . ' <span class="cb-layout-summary">' . esc_html($section['layout_style']) . '</span></small></span>';
    echo '<label class="cb-inline-toggle"><input type="hidden" name="_cb_page_sections[' . esc_attr((string) $index) . '][enable]" value="0"><input type="checkbox" name="_cb_page_sections[' . esc_attr((string) $index) . '][enable]" value="1" ' . checked($section['enable'], '1', false) . '> ' . esc_html__('Bật', 'cb-company-core') . '</label>';
    echo '<div class="cb-section-actions"><button type="button" class="button cb-collapse-section"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span><span class="cb-collapse-label">' . esc_html__('Mở rộng', 'cb-company-core') . '</span></button><button type="button" class="button cb-duplicate-section"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span> ' . esc_html__('Nhân bản', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-section">' . esc_html__('Xóa', 'cb-company-core') . '</button></div></div>';
    echo '<div class="cb-section-body"><div class="cb-section-type-row"><label>' . esc_html__('Loại khu vực', 'cb-company-core') . '<select class="cb-section-type-select" name="_cb_page_sections[' . esc_attr((string) $index) . '][type]">';
    foreach ($types as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($type, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></label></div>';
    echo '<nav class="cb-section-tabs" role="tablist">';
    foreach (['content' => __('Nội dung', 'cb-company-core'), 'design' => __('Giao diện', 'cb-company-core'), 'responsive' => __('Responsive', 'cb-company-core'), 'advanced' => __('Nâng cao', 'cb-company-core')] as $group => $label) {
        echo '<button type="button" class="cb-section-tab ' . ($group === 'content' ? 'is-active' : '') . '" data-tab="' . esc_attr($group) . '">' . esc_html($label) . '</button>';
    }
    echo '</nav><div class="cb-section-fields">';
    foreach (cb_builder_field_registry() as $key => $field) {
        cb_render_builder_field($index, $key, $field, $section);
    }
    echo '</div></div></article>';
}

function cb_render_builder_field($index, $key, $field, $section)
{
    $base = '_cb_page_sections[' . $index . ']';
    $name = $base . '[' . $key . ']';
    $value = $section[$key] ?? '';
    $visible = implode(',', $field['for']);
    $classes = 'cb-builder-field' . (in_array($field['type'], ['textarea', 'repeater', 'image'], true) ? ' cb-wide' : '');
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
    } elseif ($field['type'] === 'repeater') {
        echo '</label>';
        cb_admin_repeater_field(['id' => 'cb-section-' . $index . '-' . $key, 'label' => '', 'name' => $name, 'value' => is_array($value) ? $value : cb_legacy_lines_to_repeater($value)]);
        echo '</div>';
        return;
    } else {
        echo '<input type="' . esc_attr($field['type']) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
    }
    echo '</label></div>';
}

function cb_render_builder_image_control($base, $key, $section)
{
    $url = $section[$key . '_url'] ?? '';
    echo '<div class="cb-image-field" data-frame-title="' . esc_attr__('Chọn hình ảnh', 'cb-company-core') . '"><div class="cb-image-preview">' . ($url ? '<img src="' . esc_url($url) . '" alt="">' : '') . '</div><input class="cb-image-id" type="hidden" name="' . esc_attr($base . '[' . $key . '_id]') . '" value="' . esc_attr((string) absint($section[$key . '_id'] ?? 0)) . '"><input class="cb-image-url" type="hidden" name="' . esc_attr($base . '[' . $key . '_url]') . '" value="' . esc_attr($url) . '"><button type="button" class="button cb-pick-image">' . esc_html__('Chọn ảnh', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-image">' . esc_html__('Xóa ảnh', 'cb-company-core') . '</button></div>';
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
        $url = $value;
        echo '<div class="cb-image-field"><div class="cb-image-preview">' . ($url ? '<img src="' . esc_url($url) . '" alt="">' : '') . '</div><input class="cb-image-id" type="hidden" name="' . esc_attr($base . '[image_id]') . '" value="' . esc_attr((string) absint($values[$key . '_id'] ?? 0)) . '"><input class="cb-image-url" type="hidden" name="' . esc_attr($base . '[value]') . '" value="' . esc_attr($url) . '"><button type="button" class="button cb-pick-image">' . esc_html__('Chọn ảnh', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-image">' . esc_html__('Xóa ảnh', 'cb-company-core') . '</button></div>';
    } else {
        echo '<input type="' . esc_attr($field[1]) . '" name="' . esc_attr($base . '[value]') . '" value="' . esc_attr($value) . '">';
    }
    echo '</div></div>';
}
