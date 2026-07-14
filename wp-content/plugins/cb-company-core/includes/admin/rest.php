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
