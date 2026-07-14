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
            if ($field['type'] === 'section_repeater') {
                $row[$key] = cb_sanitize_section_items($type, $value);
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
        if ($type === 'hero_slider') {
            $row = array_merge($row, cb_sanitize_hero_settings($section));
            $row['slides'] = cb_sanitize_hero_slides($section['slides'] ?? []);
        }
        $clean[] = $row;
    }
    return $clean;
}

function cb_sanitize_hero_settings($section)
{
    $defaults = cb_hero_section_defaults();
    return [
        'min_height_desktop' => cb_sanitize_css_size($section['min_height_desktop'] ?? $defaults['min_height_desktop'], $defaults['min_height_desktop']),
        'min_height_mobile' => cb_sanitize_css_size($section['min_height_mobile'] ?? $defaults['min_height_mobile'], $defaults['min_height_mobile']),
        'content_width' => cb_sanitize_css_size($section['content_width'] ?? $defaults['content_width'], $defaults['content_width']),
        'autoplay' => (string) ($section['autoplay'] ?? '0') === '1' ? '1' : '0',
        'autoplay_delay' => (string) max(2000, absint($section['autoplay_delay'] ?? $defaults['autoplay_delay'])),
        'transition_speed' => (string) max(100, min(2000, absint($section['transition_speed'] ?? $defaults['transition_speed']))),
        'show_arrows' => (string) ($section['show_arrows'] ?? '0') === '1' ? '1' : '0',
        'show_dots' => (string) ($section['show_dots'] ?? '0') === '1' ? '1' : '0',
        'pause_on_hover' => (string) ($section['pause_on_hover'] ?? '0') === '1' ? '1' : '0',
    ];
}

function cb_sanitize_hero_slides($slides)
{
    $clean = [];
    foreach ((array) $slides as $slide) {
        $slide = wp_parse_args((array) $slide, cb_hero_slide_defaults());
        $clean[] = [
            'enable' => (string) $slide['enable'] === '1' ? '1' : '0',
            'admin_label' => sanitize_text_field($slide['admin_label']),
            'image_id' => absint($slide['image_id']),
            'image_url' => esc_url_raw($slide['image_url']),
            'mobile_image_id' => absint($slide['mobile_image_id']),
            'mobile_image_url' => esc_url_raw($slide['mobile_image_url']),
            'image_alt' => sanitize_text_field($slide['image_alt']),
            'eyebrow' => sanitize_text_field($slide['eyebrow']),
            'title' => sanitize_text_field($slide['title']),
            'highlight_text' => sanitize_text_field($slide['highlight_text']),
            'description' => sanitize_textarea_field($slide['description']),
            'primary_button_text' => sanitize_text_field($slide['primary_button_text']),
            'primary_button_url' => esc_url_raw($slide['primary_button_url']),
            'primary_button_style' => cb_sanitize_choice($slide['primary_button_style'], ['primary', 'outline', 'soft'], 'primary'),
            'secondary_button_text' => sanitize_text_field($slide['secondary_button_text']),
            'secondary_button_url' => esc_url_raw($slide['secondary_button_url']),
            'secondary_button_style' => cb_sanitize_choice($slide['secondary_button_style'], ['primary', 'outline', 'soft'], 'outline'),
            'text_alignment' => cb_sanitize_choice($slide['text_alignment'], ['left', 'center', 'right'], 'left'),
            'text_position' => cb_sanitize_choice($slide['text_position'], ['left', 'center', 'right'], 'left'),
            'overlay_enable' => (string) $slide['overlay_enable'] === '1' ? '1' : '0',
            'overlay_color' => sanitize_hex_color($slide['overlay_color']) ?: '#ffffff',
            'overlay_opacity' => (string) max(0, min(100, absint($slide['overlay_opacity']))),
            'trust_badges' => cb_sanitize_trust_badges($slide['trust_badges']),
        ];
    }
    return $clean;
}

function cb_sanitize_trust_badges($badges)
{
    $clean = [];
    foreach ((array) $badges as $badge) {
        $badge = (array) $badge;
        $text = sanitize_text_field($badge['text'] ?? '');
        if ($text !== '') {
            $clean[] = ['icon' => sanitize_text_field($badge['icon'] ?? ''), 'text' => $text];
        }
    }
    return $clean;
}

function cb_sanitize_section_items($type, $items)
{
    if (!is_array($items)) {
        $items = cb_legacy_lines_to_repeater($items);
    }
    $schema = cb_section_item_schemas()[$type] ?? [];
    $clean = [];
    foreach ((array) $items as $item) {
        $item = (array) $item;
        $row = [];
        foreach ($schema as $key => $field) {
            $field_type = $field[0];
            if ($field_type === 'image') {
                $row['image_id'] = absint($item['image_id'] ?? 0);
                $row['image_url'] = esc_url_raw($item['image_url'] ?? '');
            } elseif ($field_type === 'checkbox') {
                $row[$key] = (string) ($item[$key] ?? '0') === '1' ? '1' : '0';
            } elseif ($field_type === 'url') {
                $row[$key] = esc_url_raw($item[$key] ?? '');
            } elseif ($field_type === 'textarea') {
                $row[$key] = sanitize_textarea_field($item[$key] ?? '');
            } elseif ($field_type === 'select') {
                $row[$key] = cb_sanitize_choice($item[$key] ?? '', array_keys($field[2] ?? []), array_key_first($field[2] ?? ['' => '']));
            } else {
                $row[$key] = sanitize_text_field($item[$key] ?? '');
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
    return is_array($sections) ? array_map('cb_normalize_homepage_section', $sections) : [];
}

function cb_store_page_builder_data($post_id, $payload, $create_revision = true)
{
    if ($create_revision) {
        cb_create_page_builder_revision($post_id);
    }
    $mode = cb_sanitize_choice($payload['_cb_page_render_mode'] ?? 'editor', ['editor', 'builder', 'editor_and_builder'], 'editor');
    $sections = cb_sanitize_page_sections($payload['_cb_page_sections'] ?? []);
    $page_ui = cb_sanitize_page_ui($payload['_cb_page_ui'] ?? []);
    update_post_meta($post_id, '_cb_page_render_mode', $mode);
    update_post_meta($post_id, '_cb_page_sections', $sections);
    update_post_meta($post_id, '_cb_page_ui', $page_ui);
    clean_post_cache($post_id);
    return ['render_mode' => $mode, 'sections' => $sections, 'page_ui' => $page_ui];
}

function cb_create_page_builder_revision($post_id)
{
    $current = get_post_meta($post_id, '_cb_page_sections', true);
    if (!is_array($current)) {
        return;
    }
    $revisions = get_post_meta($post_id, '_cb_page_builder_revisions', true);
    $revisions = is_array($revisions) ? $revisions : [];
    array_unshift($revisions, [
        'id' => wp_generate_uuid4(),
        'time' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'render_mode' => get_post_meta($post_id, '_cb_page_render_mode', true) ?: 'editor',
        'sections' => $current,
        'page_ui' => (array) get_post_meta($post_id, '_cb_page_ui', true),
    ]);
    update_post_meta($post_id, '_cb_page_builder_revisions', array_slice($revisions, 0, 20));
}

function cb_save_page_builder_meta($post_id)
{
    if (!isset($_POST['cb_page_builder_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cb_page_builder_nonce'])), 'cb_save_page_builder')) {
        return;
    }
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
        return;
    }
    $payload = wp_unslash($_POST);
    $import = trim((string) ($payload['_cb_builder_import_json'] ?? ''));
    if ($import !== '') {
        $decoded = json_decode($import, true);
        if (is_array($decoded)) {
            $payload['_cb_page_sections'] = $decoded['sections'] ?? $decoded;
            $payload['_cb_page_render_mode'] = $decoded['render_mode'] ?? ($payload['_cb_page_render_mode'] ?? 'editor');
            $payload['_cb_page_ui'] = $decoded['page_ui'] ?? ($payload['_cb_page_ui'] ?? []);
        }
    }
    $source_id = absint($payload['_cb_sync_source'] ?? 0);
    $scope = cb_sanitize_choice($payload['_cb_sync_scope'] ?? 'layout', ['layout', 'all', 'style'], 'layout');
    if ($source_id && get_post_type($source_id) === 'page') {
        $payload['_cb_page_sections'] = cb_sync_page_sections(cb_get_page_sections($source_id), $payload['_cb_page_sections'] ?? [], $scope);
    }
    cb_store_page_builder_data($post_id, $payload);
}

function cb_sync_page_sections($source, $target, $scope = 'layout')
{
    if ($scope === 'all') {
        return cb_sanitize_page_sections($source);
    }
    $layout_keys = ['enable', 'type', 'layout_style', 'background_color', 'background_image_id', 'background_image_url', 'text_color', 'padding_top', 'padding_bottom', 'container_width', 'columns_desktop', 'columns_tablet', 'columns_mobile', 'card_style', 'hide_mobile', 'mobile_order', 'min_height_desktop', 'min_height_mobile', 'content_width', 'autoplay', 'autoplay_delay', 'transition_speed', 'show_arrows', 'show_dots', 'pause_on_hover'];
    $style_keys = ['background_color', 'background_image_id', 'background_image_url', 'text_color', 'padding_top', 'padding_bottom', 'container_width', 'card_style', 'hide_mobile', 'mobile_order', 'min_height_desktop', 'min_height_mobile', 'content_width'];
    $keys = $scope === 'style' ? $style_keys : $layout_keys;
    $result = [];
    foreach ((array) $source as $index => $source_section) {
        $target_section = (array) ($target[$index] ?? cb_default_builder_section($source_section['type'] ?? 'hero_slider'));
        foreach ($keys as $key) {
            if (array_key_exists($key, $source_section)) {
                $target_section[$key] = $source_section[$key];
            }
        }
        if ($scope === 'layout' && ($source_section['type'] ?? '') === 'hero_slider') {
            $source_slides = (array) ($source_section['slides'] ?? []);
            $target_slides = (array) ($target_section['slides'] ?? []);
            $target_section['slides'] = [];
            foreach ($source_slides as $slide_index => $source_slide) {
                $target_slide = wp_parse_args((array) ($target_slides[$slide_index] ?? []), cb_hero_slide_defaults());
                foreach (['enable', 'text_alignment', 'text_position', 'overlay_enable', 'overlay_color', 'overlay_opacity', 'primary_button_style', 'secondary_button_style'] as $key) {
                    $target_slide[$key] = $source_slide[$key] ?? $target_slide[$key];
                }
                $target_section['slides'][] = $target_slide;
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
