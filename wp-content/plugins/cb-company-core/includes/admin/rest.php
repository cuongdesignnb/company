<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_admin_settings_modules()
{
    return [
        'design' => ['option_name' => 'cb_design_settings', 'sanitize_callback' => 'cb_sanitize_design_settings'],
        'header' => ['option_name' => 'cb_header_settings', 'sanitize_callback' => 'cb_sanitize_header_settings'],
        'footer' => ['option_name' => 'cb_footer_settings', 'sanitize_callback' => 'cb_sanitize_footer_settings'],
        'templates' => ['option_name' => 'cb_template_settings', 'sanitize_callback' => 'cb_sanitize_template_settings'],
        'special-pages' => ['option_name' => 'cb_special_pages', 'sanitize_callback' => 'cb_sanitize_special_pages'],
        'strings' => ['option_name' => 'cb_string_translations', 'sanitize_callback' => 'cb_sanitize_string_translations'],
        'forms' => ['option_name' => 'cb_form_settings', 'sanitize_callback' => 'cb_sanitize_form_settings'],
        'seo' => ['option_name' => 'cb_seo_settings', 'sanitize_callback' => 'cb_sanitize_seo_settings'],
        'performance' => ['option_name' => 'cb_performance_settings', 'sanitize_callback' => 'cb_sanitize_performance_settings'],
    ];
}

function cb_register_admin_rest_routes()
{
    $permission = static function () {
        return current_user_can('manage_options');
    };
    register_rest_route('cb-company/v1', '/admin/panel', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'cb_rest_get_admin_panel',
        'permission_callback' => $permission,
        'args' => [
            'module' => ['required' => true, 'sanitize_callback' => 'sanitize_key'],
            'type' => ['sanitize_callback' => 'sanitize_key'],
            'context' => ['sanitize_callback' => 'sanitize_key'],
            'tab' => ['sanitize_callback' => 'sanitize_key'],
        ],
    ]);
    register_rest_route('cb-company/v1', '/admin/settings', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_rest_save_admin_settings',
        'permission_callback' => $permission,
    ]);
    register_rest_route('cb-company/v1', '/admin/pages', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'cb_rest_search_admin_pages',
        'permission_callback' => $permission,
        'args' => [
            'search' => ['sanitize_callback' => 'sanitize_text_field'],
            'language' => ['sanitize_callback' => 'sanitize_key'],
        ],
    ]);
    register_rest_route('cb-company/v1', '/admin/page-content/(?P<id>\d+)', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_rest_save_page_content',
        'permission_callback' => 'cb_rest_can_edit_page_content',
    ]);
    register_rest_route('cb-company/v1', '/admin/page-content/(?P<id>\d+)/revision', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_rest_restore_page_content_revision',
        'permission_callback' => 'cb_rest_can_edit_page_content',
    ]);
    register_rest_route('cb-company/v1', '/admin/page-builder/(?P<id>\d+)/sections', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'cb_rest_get_page_builder_sections',
        'permission_callback' => 'cb_rest_can_edit_page_content',
    ]);
    register_rest_route('cb-company/v1', '/admin/page-builder/(?P<id>\d+)/section/(?P<index>\d+)', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'cb_rest_get_page_builder_section',
            'permission_callback' => 'cb_rest_can_edit_page_content',
        ],
        [
            'methods' => 'PATCH',
            'callback' => 'cb_rest_patch_page_builder_section',
            'permission_callback' => 'cb_rest_can_edit_page_content',
        ],
        [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'cb_rest_delete_page_builder_section',
            'permission_callback' => 'cb_rest_can_edit_page_content',
        ],
    ]);
    register_rest_route('cb-company/v1', '/admin/page-builder/(?P<id>\d+)/section/(?P<index>\d+)/duplicate', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_rest_duplicate_page_builder_section',
        'permission_callback' => 'cb_rest_can_edit_page_content',
    ]);
}

function cb_rest_can_edit_page_content(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    return $post_id && get_post_type($post_id) === 'page' && current_user_can('edit_post', $post_id);
}

function cb_rest_save_page_content(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $payload = $request->get_json_params();
    if (!is_array($payload)) {
        $payload = $request->get_body_params();
    }
    $source_id = absint($payload['_cb_sync_source'] ?? 0);
    $scope = cb_sanitize_choice($payload['_cb_sync_scope'] ?? 'layout', ['layout', 'all', 'style'], 'layout');
    if ($source_id && get_post_type($source_id) === 'page' && current_user_can('edit_post', $source_id)) {
        $payload['_cb_page_sections'] = cb_sync_page_sections(cb_get_page_sections($source_id), $payload['_cb_page_sections'] ?? [], $scope);
    }
    $saved = cb_store_page_builder_data($post_id, $payload);
    return rest_ensure_response([
        'success' => true,
        'message' => __('Đã lưu thay đổi.', 'cb-company-core'),
        'updated' => current_time('mysql'),
        'sectionCount' => count($saved['sections']),
        'revisions' => cb_rest_page_revision_list($post_id),
    ]);
}

function cb_rest_get_page_builder_sections(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $sections = cb_get_page_sections($post_id);
    return rest_ensure_response(cb_rest_page_builder_response($post_id, null, $sections));
}

function cb_rest_get_page_builder_section(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $index = absint($request['index']);
    $sections = cb_get_page_sections($post_id);
    if (!isset($sections[$index])) {
        return new WP_Error('cb_section_not_found', __('Không tìm thấy section.', 'cb-company-core'), ['status' => 404]);
    }
    return rest_ensure_response(cb_rest_page_builder_response($post_id, $index, $sections));
}

function cb_rest_patch_page_builder_section(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $index = absint($request['index']);
    $sections = cb_get_page_sections($post_id);
    if (!isset($sections[$index])) {
        return new WP_Error('cb_section_not_found', __('Không tìm thấy section.', 'cb-company-core'), ['status' => 404]);
    }
    $payload = $request->get_json_params();
    $payload = is_array($payload) ? $payload : [];
    $section_payload = is_array($payload['section'] ?? null) ? $payload['section'] : $payload;
    $sections[$index] = cb_rest_merge_quick_section($sections[$index], $section_payload);
    cb_rest_store_page_sections($post_id, $sections);
    $sections = cb_get_page_sections($post_id);
    return rest_ensure_response(cb_rest_page_builder_response($post_id, $index, $sections) + [
        'success' => true,
        'message' => __('Đã lưu thay đổi.', 'cb-company-core'),
    ]);
}

function cb_rest_duplicate_page_builder_section(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $index = absint($request['index']);
    $sections = cb_get_page_sections($post_id);
    if (!isset($sections[$index])) {
        return new WP_Error('cb_section_not_found', __('Không tìm thấy section.', 'cb-company-core'), ['status' => 404]);
    }
    $clone = $sections[$index];
    $clone['admin_label'] = trim((string) ($clone['admin_label'] ?? '')) ?: trim((string) ($clone['title'] ?? ''));
    if ($clone['admin_label'] !== '') {
        $clone['admin_label'] .= ' copy';
    }
    array_splice($sections, $index + 1, 0, [$clone]);
    cb_rest_store_page_sections($post_id, $sections);
    $sections = cb_get_page_sections($post_id);
    return rest_ensure_response(cb_rest_page_builder_response($post_id, $index + 1, $sections) + [
        'success' => true,
        'message' => __('Đã nhân bản section.', 'cb-company-core'),
    ]);
}

function cb_rest_delete_page_builder_section(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $index = absint($request['index']);
    $sections = cb_get_page_sections($post_id);
    if (!isset($sections[$index])) {
        return new WP_Error('cb_section_not_found', __('Không tìm thấy section.', 'cb-company-core'), ['status' => 404]);
    }
    array_splice($sections, $index, 1);
    cb_rest_store_page_sections($post_id, $sections);
    $sections = cb_get_page_sections($post_id);
    return rest_ensure_response(cb_rest_page_builder_response($post_id, null, $sections) + [
        'success' => true,
        'message' => __('Đã xóa section.', 'cb-company-core'),
    ]);
}

function cb_rest_store_page_sections($post_id, $sections)
{
    cb_create_page_builder_revision($post_id);
    update_post_meta($post_id, '_cb_page_sections', cb_sanitize_page_sections($sections));
    clean_post_cache($post_id);
}

function cb_rest_page_builder_response($post_id, $index, $sections)
{
    $items = [];
    foreach ($sections as $section_index => $section) {
        $section = cb_normalize_homepage_section($section);
        $items[] = [
            'index' => (int) $section_index,
            'type' => $section['type'],
            'label' => cb_rest_section_label($section),
            'enabled' => ($section['enable'] ?? '1') === '1',
            'thumbnail' => cb_rest_section_thumbnail($section),
            'itemCount' => cb_rest_section_item_count($section),
            'adminUrl' => function_exists('cb_frontend_edit_admin_url') ? cb_frontend_edit_admin_url($post_id, $section_index) : get_edit_post_link($post_id, 'raw'),
        ];
    }
    $selected = $index !== null && isset($sections[$index]) ? cb_normalize_homepage_section($sections[$index]) : null;
    return [
        'postId' => (int) $post_id,
        'updated' => current_time('mysql'),
        'sections' => $items,
        'section' => $selected,
        'schema' => $selected ? cb_rest_quick_section_schema($selected) : null,
        'sectionTypes' => cb_section_types(),
        'revisionCount' => count((array) get_post_meta($post_id, '_cb_page_builder_revisions', true)),
    ];
}

function cb_rest_section_label($section)
{
    $types = cb_section_types();
    $type = $section['type'] ?? '';
    return $section['admin_label'] ?: ($section['title'] ?: ($types[$type] ?? $type));
}

function cb_rest_section_thumbnail($section)
{
    if (($section['type'] ?? '') === 'hero_slider') {
        foreach ((array) ($section['slides'] ?? []) as $slide) {
            if (!empty($slide['image_url'])) {
                return esc_url_raw($slide['image_url']);
            }
        }
    }
    foreach (['image_url', 'background_image_url', 'secondary_image_url', 'tertiary_image_url'] as $key) {
        if (!empty($section[$key])) {
            return esc_url_raw($section[$key]);
        }
    }
    foreach ((array) ($section['items'] ?? []) as $item) {
        if (!empty($item['image_url'])) {
            return esc_url_raw($item['image_url']);
        }
    }
    return '';
}

function cb_rest_section_item_count($section)
{
    if (($section['type'] ?? '') === 'hero_slider') {
        return count((array) ($section['slides'] ?? []));
    }
    return count((array) ($section['items'] ?? []));
}

function cb_rest_quick_section_schema($section)
{
    $type = $section['type'] ?? 'hero_slider';
    $fields = [
        ['key' => 'enable', 'label' => __('Bật section', 'cb-company-core'), 'type' => 'checkbox', 'group' => 'content'],
    ];
    foreach (cb_builder_field_registry() as $key => $field) {
        if (!in_array($type, $field['for'], true)) {
            continue;
        }
        if (!in_array($key, cb_rest_quick_section_keys(), true)) {
            continue;
        }
        $fields[] = [
            'key' => $key,
            'label' => $field['label'],
            'type' => $field['type'],
            'group' => in_array($field['group'], ['images', 'design'], true) ? $field['group'] : 'content',
            'choices' => $field['choices'] ?? [],
        ];
    }
    return [
        'type' => $type,
        'fields' => $fields,
        'itemFields' => cb_rest_quick_item_schema($type),
        'heroSlideFields' => $type === 'hero_slider' ? cb_rest_quick_hero_slide_schema() : [],
    ];
}

function cb_rest_quick_section_keys()
{
    return ['eyebrow', 'title', 'subtitle', 'description', 'button_text', 'button_url', 'image', 'secondary_image', 'tertiary_image', 'whatsapp_qr', 'wechat_qr', 'background_image', 'layout_style', 'background_color', 'text_color'];
}

function cb_rest_quick_item_schema($type)
{
    $schema = [];
    foreach ((array) (cb_section_item_schemas()[$type] ?? []) as $key => $field) {
        if (!in_array($key, ['enable', 'title', 'description', 'image', 'image_alt', 'caption', 'url', 'text', 'label', 'value', 'number', 'suffix', 'year', 'issuer', 'standard', 'question', 'answer'], true)) {
            continue;
        }
        $schema[] = [
            'key' => $key,
            'type' => $field[0],
            'label' => $field[1],
            'choices' => $field[2] ?? [],
        ];
    }
    return $schema;
}

function cb_rest_quick_hero_slide_schema()
{
    return [
        ['key' => 'enable', 'type' => 'checkbox', 'label' => __('Bật slide', 'cb-company-core')],
        ['key' => 'admin_label', 'type' => 'text', 'label' => __('Nhãn quản trị', 'cb-company-core')],
        ['key' => 'eyebrow', 'type' => 'text', 'label' => __('Dòng nhấn', 'cb-company-core')],
        ['key' => 'title', 'type' => 'text', 'label' => __('Tiêu đề', 'cb-company-core')],
        ['key' => 'highlight_text', 'type' => 'text', 'label' => __('Text tô màu', 'cb-company-core')],
        ['key' => 'description', 'type' => 'textarea', 'label' => __('Mô tả', 'cb-company-core')],
        ['key' => 'primary_button_text', 'type' => 'text', 'label' => __('Nút chính', 'cb-company-core')],
        ['key' => 'primary_button_url', 'type' => 'url', 'label' => __('Link nút chính', 'cb-company-core')],
        ['key' => 'secondary_button_text', 'type' => 'text', 'label' => __('Nút phụ', 'cb-company-core')],
        ['key' => 'secondary_button_url', 'type' => 'url', 'label' => __('Link nút phụ', 'cb-company-core')],
        ['key' => 'image', 'type' => 'image', 'label' => __('Ảnh desktop', 'cb-company-core')],
        ['key' => 'mobile_image', 'type' => 'image', 'label' => __('Ảnh mobile', 'cb-company-core')],
        ['key' => 'image_alt', 'type' => 'text', 'label' => __('Mô tả ảnh', 'cb-company-core')],
    ];
}

function cb_rest_merge_quick_section($current, $payload)
{
    $current = cb_normalize_homepage_section($current);
    $payload = is_array($payload) ? $payload : [];
    $type = $current['type'];
    foreach (['enable'] as $key) {
        if (array_key_exists($key, $payload)) {
            $current[$key] = (string) $payload[$key] === '1' ? '1' : '0';
        }
    }
    foreach (cb_rest_quick_section_keys() as $key) {
        if (array_key_exists($key, $payload)) {
            if (in_array($key, ['image', 'secondary_image', 'tertiary_image', 'whatsapp_qr', 'wechat_qr', 'background_image'], true)) {
                $current[$key . '_id'] = absint($payload[$key . '_id'] ?? $current[$key . '_id'] ?? 0);
                $current[$key . '_url'] = esc_url_raw($payload[$key . '_url'] ?? $current[$key . '_url'] ?? '');
            } else {
                $current[$key] = $payload[$key];
            }
        }
    }
    if ($type === 'hero_slider' && isset($payload['slides']) && is_array($payload['slides'])) {
        $slides = (array) ($current['slides'] ?? []);
        foreach ($payload['slides'] as $slide_index => $slide_payload) {
            if (!isset($slides[$slide_index]) || !is_array($slide_payload)) {
                continue;
            }
            foreach (cb_rest_quick_hero_slide_schema() as $field) {
                $key = $field['key'];
                if (!array_key_exists($key, $slide_payload)) {
                    continue;
                }
                if (in_array($key, ['image', 'mobile_image'], true)) {
                    $slides[$slide_index][$key . '_id'] = absint($slide_payload[$key . '_id'] ?? $slides[$slide_index][$key . '_id'] ?? 0);
                    $slides[$slide_index][$key . '_url'] = esc_url_raw($slide_payload[$key . '_url'] ?? $slides[$slide_index][$key . '_url'] ?? '');
                } else {
                    $slides[$slide_index][$key] = $slide_payload[$key];
                }
            }
        }
        $current['slides'] = $slides;
    }
    if (isset($payload['items']) && is_array($payload['items'])) {
        $items = (array) ($current['items'] ?? []);
        foreach ($payload['items'] as $item_index => $item_payload) {
            if (!isset($items[$item_index]) || !is_array($item_payload)) {
                continue;
            }
            foreach (cb_rest_quick_item_schema($type) as $field) {
                $key = $field['key'];
                if (!array_key_exists($key, $item_payload)) {
                    continue;
                }
                if ($key === 'image') {
                    $items[$item_index]['image_id'] = absint($item_payload['image_id'] ?? $items[$item_index]['image_id'] ?? 0);
                    $items[$item_index]['image_url'] = esc_url_raw($item_payload['image_url'] ?? $items[$item_index]['image_url'] ?? '');
                } else {
                    $items[$item_index][$key] = $item_payload[$key];
                }
            }
        }
        $current['items'] = $items;
    }
    return cb_sanitize_page_sections([$current])[0] ?? $current;
}

function cb_rest_restore_page_content_revision(WP_REST_Request $request)
{
    $post_id = absint($request['id']);
    $revision_id = sanitize_text_field((string) $request->get_param('revision_id'));
    $revisions = get_post_meta($post_id, '_cb_page_builder_revisions', true);
    foreach ((array) $revisions as $revision) {
        if (hash_equals((string) ($revision['id'] ?? ''), $revision_id)) {
            cb_create_page_builder_revision($post_id);
            cb_store_page_builder_data($post_id, [
                '_cb_page_render_mode' => $revision['render_mode'] ?? 'editor',
                '_cb_page_sections' => $revision['sections'] ?? [],
                '_cb_page_ui' => $revision['page_ui'] ?? [],
            ], false);
            return rest_ensure_response(['success' => true, 'message' => __('Đã khôi phục revision.', 'cb-company-core')]);
        }
    }
    return new WP_Error('cb_revision_not_found', __('Không tìm thấy revision.', 'cb-company-core'), ['status' => 404]);
}

function cb_rest_page_revision_list($post_id)
{
    return array_map(static function ($revision) {
        return [
            'id' => $revision['id'] ?? '',
            'label' => ($revision['time'] ?? '') . ' · ' . get_the_author_meta('display_name', absint($revision['user_id'] ?? 0)),
        ];
    }, (array) get_post_meta($post_id, '_cb_page_builder_revisions', true));
}

function cb_rest_get_admin_panel(WP_REST_Request $request)
{
    if ($request->get_param('module') !== 'templates') {
        return new WP_Error('cb_invalid_module', __('Module không hợp lệ.', 'cb-company-core'), ['status' => 400]);
    }
    $route = cb_normalize_template_route(
        $request->get_param('type'),
        $request->get_param('context'),
        $request->get_param('tab')
    );
    ob_start();
    cb_render_template_panel_fragment($route['context'], $route['tab']);
    $html = ob_get_clean();
    ob_start();
    cb_render_template_type_tabs($route);
    $type_nav = ob_get_clean();
    ob_start();
    cb_render_template_context_tabs($route);
    $context_nav = ob_get_clean();
    ob_start();
    cb_render_template_field_tabs($route);
    $tab_nav = ob_get_clean();

    $tabs = array_keys(cb_template_field_tabs($route['context']));
    $index = array_search($route['tab'], $tabs, true);
    $prefetch = [];
    foreach ([$index + 1, $index + 2] as $next) {
        if (isset($tabs[$next])) {
            $prefetch[] = ['type' => $route['type'], 'context' => $route['context'], 'tab' => $tabs[$next]];
        }
    }
    return rest_ensure_response([
        'html' => $html,
        'typeNav' => $type_nav,
        'contextNav' => $context_nav,
        'tabNav' => $tab_nav,
        'route' => $route,
        'prefetch' => $prefetch,
    ]);
}

function cb_rest_save_admin_settings(WP_REST_Request $request)
{
    $module = sanitize_key((string) $request->get_param('module'));
    $config = cb_admin_settings_modules()[$module] ?? null;
    if (!$config) {
        return new WP_Error('cb_invalid_module', __('Module không hợp lệ.', 'cb-company-core'), ['status' => 400]);
    }
    $values = $request->get_param('values');
    $values = is_array($values) ? $values : [];
    if ($module === 'templates') {
        $stored = cb_get_group_options($config['option_name'], cb_default_template_settings());
        $values = array_replace_recursive($stored, $values);
    }
    $clean = call_user_func($config['sanitize_callback'], $values);
    update_option($config['option_name'], $clean, false);
    return rest_ensure_response([
        'success' => true,
        'message' => __('Đã lưu thay đổi.', 'cb-company-core'),
        'module' => $module,
    ]);
}

function cb_rest_search_admin_pages(WP_REST_Request $request)
{
    $language = sanitize_key((string) $request->get_param('language'));
    $args = [
        'post_type' => 'page',
        'post_status' => ['publish', 'draft', 'private'],
        'posts_per_page' => 20,
        'orderby' => 'modified',
        'order' => 'DESC',
        's' => sanitize_text_field((string) $request->get_param('search')),
        'fields' => 'ids',
        'no_found_rows' => true,
    ];
    if (isset(cb_languages()[$language])) {
        $args['meta_query'] = [[
            'key' => '_cb_language',
            'value' => $language,
        ]];
    }
    $query = new WP_Query($args);
    $items = array_map(static function ($post_id) {
        return [
            'id' => (int) $post_id,
            'title' => get_the_title($post_id),
            'status' => get_post_status($post_id),
            'language' => get_post_meta($post_id, '_cb_language', true) ?: 'en',
        ];
    }, $query->posts);
    return rest_ensure_response(['items' => $items]);
}
