<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_max_package_size()
{
    return (int) apply_filters('cb_transfer_max_package_size', 512 * MB_IN_BYTES);
}

function cb_transfer_max_zip_entries()
{
    return (int) apply_filters('cb_transfer_max_zip_entries', 10000);
}

function cb_transfer_max_uncompressed_size()
{
    return (int) apply_filters('cb_transfer_max_uncompressed_size', 2 * GB_IN_BYTES);
}

function cb_transfer_allowed_media_extensions()
{
    return (array) apply_filters('cb_transfer_allowed_media_extensions', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'mp4']);
}

function cb_transfer_dangerous_extensions()
{
    return ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'cgi', 'pl', 'py', 'sh', 'bash', 'exe', 'dll', 'com', 'bat', 'cmd', 'js', 'mjs'];
}

function cb_transfer_safe_relative_path($path)
{
    $path = str_replace('\\', '/', (string) $path);
    if ($path === '' || str_contains($path, "\0") || str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:/', $path)) {
        return false;
    }
    foreach (explode('/', $path) as $part) {
        if ($part === '..' || $part === '') {
            return false;
        }
    }
    return $path;
}

function cb_transfer_validate_package_entry($path)
{
    $safe = cb_transfer_safe_relative_path($path);
    if (!$safe) {
        return new WP_Error('unsafe_path', __('Package chứa đường dẫn không an toàn.', 'cb-site-transfer'));
    }
    $extension = strtolower(pathinfo($safe, PATHINFO_EXTENSION));
    if (in_array($extension, cb_transfer_dangerous_extensions(), true)) {
        return new WP_Error('dangerous_file', sprintf(__('Package chứa file nguy hiểm: %s', 'cb-site-transfer'), $safe));
    }
    $json_files = ['manifest.json', 'checksums.json', 'data/site.json', 'data/options.json', 'data/posts.json', 'data/terms.json', 'data/menus.json', 'data/attachments.json', 'data/relationships.json'];
    if (in_array($safe, $json_files, true)) {
        return true;
    }
    if (!str_starts_with($safe, 'media/') || !in_array($extension, cb_transfer_allowed_media_extensions(), true)) {
        return new WP_Error('unsupported_file', sprintf(__('Package chứa file không được phép: %s', 'cb-site-transfer'), $safe));
    }
    return true;
}

function cb_transfer_verify_rest_request(WP_REST_Request $request)
{
    if (!current_user_can('manage_options')) {
        return new WP_Error('forbidden', __('Bạn không có quyền thực hiện thao tác này.', 'cb-site-transfer'), ['status' => 403]);
    }
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('invalid_nonce', __('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 'cb-site-transfer'), ['status' => 403]);
    }
    return true;
}

function cb_transfer_rate_limit($action, $seconds = 2)
{
    $key = 'cb_transfer_rate_' . get_current_user_id() . '_' . sanitize_key($action);
    if (get_transient($key)) {
        return new WP_Error('rate_limited', __('Thao tác quá nhanh. Vui lòng thử lại sau.', 'cb-site-transfer'), ['status' => 429]);
    }
    set_transient($key, 1, max(1, absint($seconds)));
    return true;
}

function cb_transfer_assert_inside($path, $base)
{
    $path = wp_normalize_path($path);
    $base = trailingslashit(wp_normalize_path($base));
    return str_starts_with($path, $base);
}

function cb_transfer_allowed_mime($path)
{
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($extension, cb_transfer_allowed_media_extensions(), true)) {
        return new WP_Error('invalid_extension', __('Định dạng media không được phép.', 'cb-site-transfer'));
    }
    $type = wp_check_filetype($path);
    if (empty($type['type'])) {
        return new WP_Error('invalid_mime', __('Không xác định được MIME của media.', 'cb-site-transfer'));
    }
    return $type;
}
