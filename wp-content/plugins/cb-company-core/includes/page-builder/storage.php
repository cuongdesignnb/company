<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_sanitize_page_sections($input)
{
    $clean = [];
    $registry = cb_builder_field_registry();
    foreach ((array) $input as $section) {
        $section = is_array($section) ? wp_unslash($section) : [];
        $type = cb_sanitize_choice($section['type'] ?? 'hero_slider', array_keys(cb_section_types()), 'hero_slider');
        $row = cb_default_builder_section($type);
        $row['enable'] = (string) ($section['enable'] ?? '0') === '1' ? '1' : '0';
        foreach ($registry as $key => $field) {
            if (!in_array($type, $field['for'], true)) {
                continue;
            }
            $value = $section[$key] ?? $row[$key] ?? '';
            if ($field['type'] === 'repeater') {
                $row[$key] = cb_sanitize_repeater_items($value);
            } elseif ($field['type'] === 'image') {
                $row[$key . '_id'] = absint($section[$key . '_id'] ?? 0);
                $row[$key . '_url'] = esc_url_raw($section[$key . '_url'] ?? '');
            } elseif ($field['type'] === 'checkbox') {
                $row[$key] = (string) $value === '1' ? '1' : '0';
            } elseif ($field['type'] === 'color') {
                $row[$key] = sanitize_hex_color($value) ?: '';
            } elseif ($field['type'] === 'url') {
                $row[$key] = esc_url_raw($value);
            } elseif ($field['type'] === 'number') {
                $row[$key] = $value === '' ? '' : (string) absint($value);
            } elseif ($field['type'] === 'textarea') {
                $row[$key] = sanitize_textarea_field($value);
            } elseif (in_array($key, ['padding_top', 'padding_bottom', 'container_width', 'spacer_height'], true)) {
                $row[$key] = cb_sanitize_css_size($value, '');
            } else {
                $row[$key] = sanitize_text_field($value);
            }
        }
        $clean[] = $row;
    }
    return $clean;
}

function cb_sanitize_homepage_sections($input)
{
    return cb_sanitize_page_sections($input);
}

function cb_get_page_sections($post_id)
{
    $sections = get_post_meta($post_id, '_cb_page_sections', true);
    if (is_array($sections) && $sections) {
        return array_map('cb_normalize_homepage_section', $sections);
    }
    if ((int) get_option('page_on_front') === (int) $post_id) {
        $legacy = get_option('cb_homepage_sections', []);
        if (is_array($legacy)) {
            return array_map('cb_normalize_homepage_section', $legacy);
        }
    }
    return [];
}

function cb_save_page_builder_meta($post_id)
{
    if (!isset($_POST['cb_page_builder_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cb_page_builder_nonce'])), 'cb_save_page_builder')) {
        return;
    }
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
        return;
    }
    $mode = cb_sanitize_choice(wp_unslash($_POST['_cb_page_render_mode'] ?? 'editor'), ['editor', 'builder', 'editor_and_builder'], 'editor');
    $sections = cb_sanitize_page_sections($_POST['_cb_page_sections'] ?? []);
    $page_ui = cb_sanitize_page_ui($_POST['_cb_page_ui'] ?? []);
    $import = trim((string) wp_unslash($_POST['_cb_builder_import_json'] ?? ''));
    if ($import !== '') {
        $decoded = json_decode($import, true);
        if (is_array($decoded)) {
            $sections = cb_sanitize_page_sections($decoded['sections'] ?? $decoded);
            if (isset($decoded['render_mode'])) {
                $mode = cb_sanitize_choice($decoded['render_mode'], ['editor', 'builder', 'editor_and_builder'], $mode);
            }
            if (isset($decoded['page_ui']) && is_array($decoded['page_ui'])) {
                $page_ui = cb_sanitize_page_ui($decoded['page_ui']);
            }
        }
    }
    $source_id = absint($_POST['_cb_sync_source'] ?? 0);
    $scope = cb_sanitize_choice(wp_unslash($_POST['_cb_sync_scope'] ?? 'layout'), ['layout', 'all', 'style'], 'layout');
    if ($source_id && get_post_type($source_id) === 'page') {
        $sections = cb_sync_page_sections(cb_get_page_sections($source_id), $sections, $scope);
    }
    update_post_meta($post_id, '_cb_page_render_mode', $mode);
    update_post_meta($post_id, '_cb_page_sections', $sections);
    update_post_meta($post_id, '_cb_page_ui', $page_ui);
}

function cb_sync_page_sections($source, $target, $scope = 'layout')
{
    if ($scope === 'all') {
        return cb_sanitize_page_sections($source);
    }
    $layout_keys = ['enable', 'type', 'layout_style', 'background_color', 'background_image_id', 'background_image_url', 'text_color', 'padding_top', 'padding_bottom', 'container_width', 'columns_desktop', 'columns_tablet', 'columns_mobile', 'card_style', 'hide_mobile', 'mobile_order'];
    $style_keys = ['background_color', 'background_image_id', 'background_image_url', 'text_color', 'padding_top', 'padding_bottom', 'container_width', 'card_style', 'hide_mobile', 'mobile_order'];
    $keys = $scope === 'style' ? $style_keys : $layout_keys;
    $result = [];
    foreach ((array) $source as $index => $source_section) {
        $target_section = (array) ($target[$index] ?? cb_default_builder_section($source_section['type'] ?? 'hero_slider'));
        foreach ($keys as $key) {
            if (array_key_exists($key, $source_section)) {
                $target_section[$key] = $source_section[$key];
            }
        }
        $result[] = $target_section;
    }
    return cb_sanitize_page_sections($result);
}

function cb_export_page_json()
{
    $post_id = absint($_GET['post_id'] ?? 0);
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_die(esc_html__('Bạn không có quyền xuất dữ liệu trang này.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_export_page_' . $post_id);
    $data = [
        'version' => CB_CORE_VERSION,
        'post_id' => $post_id,
        'render_mode' => get_post_meta($post_id, '_cb_page_render_mode', true) ?: 'editor',
        'sections' => cb_get_page_sections($post_id),
        'page_ui' => get_post_meta($post_id, '_cb_page_ui', true),
    ];
    nocache_headers();
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="page-builder-' . $post_id . '.json"');
    echo wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function cb_export_inquiries_csv()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền xuất yêu cầu.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_export_inquiries');
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="inquiries-' . gmdate('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    $stream = fopen('php://output', 'w');
    fputcsv($stream, ['ID', 'Họ tên', 'Công ty', 'Email', 'Điện thoại', 'Quốc gia', 'Nội dung', 'Trạng thái', 'Ngày']);
    $posts = get_posts(['post_type' => 'inquiry', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC']);
    foreach ($posts as $post) {
        fputcsv($stream, [$post->ID, get_post_meta($post->ID, '_cb_full_name', true), get_post_meta($post->ID, '_cb_company_name', true), get_post_meta($post->ID, '_cb_email', true), get_post_meta($post->ID, '_cb_phone', true), get_post_meta($post->ID, '_cb_country', true), get_post_meta($post->ID, '_cb_message', true), get_post_meta($post->ID, '_cb_inquiry_status', true), $post->post_date]);
    }
    fclose($stream);
    exit;
}
