<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_cache_control_disable_full_page_cache()
{
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }
    if (!headers_sent()) {
        nocache_headers();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
        header('X-Accel-Expires: 0', true);
        header('Surrogate-Control: no-store', true);
    }
}
add_action('template_redirect', 'cb_cache_control_disable_full_page_cache', -1000);
add_action('send_headers', 'cb_cache_control_disable_full_page_cache', 0);

function cb_cache_control_base_paths()
{
    return [
        '/', '/en/', '/zh/',
        '/en/products/', '/zh/products/',
        '/en/news/', '/zh/news/',
        '/en/videos/', '/zh/videos/',
        '/en/certificates/', '/zh/certificates/',
    ];
}

function cb_cache_control_path_from_url($url)
{
    $path = (string) wp_parse_url((string) $url, PHP_URL_PATH);
    return '/' . ltrim($path ?: '/', '/');
}

function cb_cache_control_all_paths()
{
    $paths = cb_cache_control_base_paths();
    $post_types = get_post_types(['public' => true], 'names');
    unset($post_types['attachment']);
    $post_ids = get_posts([
        'post_type' => array_values($post_types),
        'post_status' => 'publish',
        'posts_per_page' => 500,
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);
    foreach ($post_ids as $post_id) {
        $paths[] = cb_cache_control_path_from_url(get_permalink($post_id));
    }
    return array_values(array_unique(array_filter($paths)));
}

function cb_cache_control_purge_paths(array $paths)
{
    $home_host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
    $scheme = is_ssl() ? 'https' : 'http';
    $results = ['purged' => 0, 'failed' => 0, 'checked_at' => current_time('mysql')];
    foreach (array_unique($paths) as $path) {
        $path = '/' . ltrim((string) $path, '/');
        $purge_url = $scheme . '://127.0.0.1/purge' . $path;
        $response = wp_remote_get($purge_url, [
            'timeout' => 3,
            'redirection' => 0,
            'sslverify' => false,
            'headers' => ['Host' => $home_host],
        ]);
        $code = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
        if (in_array($code, [200, 204, 404], true)) {
            $results['purged']++;
        } else {
            $results['failed']++;
        }
    }
    update_option('cb_cache_purge_status', $results, false);
    return $results;
}

function cb_cache_control_schedule_purge(array $paths = [])
{
    $GLOBALS['cb_cache_control_paths'] = array_merge(
        (array) ($GLOBALS['cb_cache_control_paths'] ?? []),
        $paths ?: cb_cache_control_base_paths()
    );
    if (!has_action('shutdown', 'cb_cache_control_run_scheduled_purge')) {
        add_action('shutdown', 'cb_cache_control_run_scheduled_purge');
    }
}

function cb_cache_control_run_scheduled_purge()
{
    $paths = (array) ($GLOBALS['cb_cache_control_paths'] ?? []);
    if ($paths) {
        cb_cache_control_purge_paths($paths);
    }
}

function cb_cache_control_option_updated($old_value, $value, $option)
{
    $watched = [
        'cb_design_settings', 'cb_header_settings', 'cb_footer_settings',
        'cb_template_settings', 'cb_special_pages', 'cb_string_translations',
        'cb_form_settings', 'cb_seo_settings', 'cb_performance_settings',
    ];
    if (in_array($option, $watched, true)) {
        cb_cache_control_schedule_purge();
    }
}
add_action('updated_option', 'cb_cache_control_option_updated', 10, 3);

function cb_cache_control_post_saved($post_id, $post, $update)
{
    unset($update);
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $post->post_status !== 'publish') {
        return;
    }
    cb_cache_control_schedule_purge(array_merge(
        cb_cache_control_base_paths(),
        [cb_cache_control_path_from_url(get_permalink($post_id))]
    ));
}
add_action('save_post', 'cb_cache_control_post_saved', 100, 3);
add_action('wp_update_nav_menu', static function () {
    cb_cache_control_schedule_purge();
});

function cb_cache_control_handle_manual_purge()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_purge_frontend_cache');
    $results = cb_cache_control_purge_paths(cb_cache_control_all_paths());
    wp_safe_redirect(add_query_arg([
        'page' => 'cb-company-tools',
        'cache_purged' => 1,
        'purged' => $results['purged'],
        'failed' => $results['failed'],
    ], admin_url('admin.php')));
    exit;
}
