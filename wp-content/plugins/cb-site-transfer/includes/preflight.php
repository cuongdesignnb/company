<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_stage_import_package(array $file)
{
    $name = sanitize_file_name($file['name'] ?? '');
    $tmp_name = (string) ($file['tmp_name'] ?? '');
    $size = absint($file['size'] ?? 0);
    if (isset($file['error']) && (int) $file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('package_upload_error', __('Upload package thất bại.', 'cb-site-transfer'));
    }
    if (!str_ends_with(strtolower($name), '.cbsite.zip')) {
        return new WP_Error('package_extension', __('Chỉ chấp nhận file .cbsite.zip.', 'cb-site-transfer'));
    }
    if (!$tmp_name || !is_readable($tmp_name) || !$size || $size > cb_transfer_max_package_size()) {
        return new WP_Error('package_upload', __('Package upload không hợp lệ hoặc vượt giới hạn.', 'cb-site-transfer'));
    }
    $job = cb_transfer_create_job('import', ['status' => 'validating', 'step' => 'upload', 'progress' => 2]);
    $upload_workspace = cb_transfer_create_workspace($job['job_id'] . '-upload');
    if (is_wp_error($upload_workspace)) return $upload_workspace;
    $package_path = $upload_workspace . '/company-site.cbsite.zip';
    $moved = is_uploaded_file($tmp_name) ? move_uploaded_file($tmp_name, $package_path) : copy($tmp_name, $package_path);
    if (!$moved) {
        cb_transfer_update_job($job['job_id'], ['status' => 'failed', 'errors' => [__('Không lưu được package upload.', 'cb-site-transfer')]]);
        return new WP_Error('package_move', __('Không lưu được package upload.', 'cb-site-transfer'));
    }
    $validated = cb_transfer_validate_and_extract_package($package_path, $job['job_id']);
    if (is_wp_error($validated)) {
        cb_transfer_update_job($job['job_id'], ['status' => 'failed', 'errors' => [$validated->get_error_message()]]);
        cb_transfer_remove_tree($upload_workspace);
        cb_transfer_remove_tree(trailingslashit(cb_transfer_workspace_root()) . $job['job_id'] . '-import');
        return $validated;
    }
    $preflight = cb_transfer_run_preflight($validated['workspace'], $validated['manifest'], $validated['size']);
    $status = empty($preflight['blocking_errors']) ? 'pending' : 'failed';
    $job = cb_transfer_update_job($job['job_id'], [
        'status' => $status,
        'step' => 'preflight',
        'progress' => 10,
        'package_name' => $name,
        'package_path' => $package_path,
        'workspace' => $validated['workspace'],
        'source_site_uuid' => sanitize_text_field($validated['manifest']['source_site_uuid'] ?? ''),
        'source_url' => esc_url_raw($validated['manifest']['source_url'] ?? ''),
        'manifest' => $validated['manifest'],
        'preflight' => $preflight,
        'warnings' => $preflight['warnings'],
        'errors' => $preflight['blocking_errors'],
    ]);
    return $job;
}

function cb_transfer_run_preflight($workspace, array $manifest, $package_size)
{
    $posts = cb_transfer_read_json($workspace . '/data/posts.json');
    $terms = cb_transfer_read_json($workspace . '/data/terms.json');
    $attachments = cb_transfer_read_json($workspace . '/data/attachments.json');
    $menus = cb_transfer_read_json($workspace . '/data/menus.json');
    $blocking = [];
    $warnings = [];
    foreach ([$posts, $terms, $attachments, $menus] as $data) {
        if (is_wp_error($data)) $blocking[] = $data->get_error_message();
    }
    if ($blocking) {
        return ['blocking_errors' => $blocking, 'warnings' => $warnings];
    }

    $upload = wp_get_upload_dir();
    if (!empty($upload['error']) || !wp_is_writable($upload['basedir'])) {
        $blocking[] = __('Thư mục uploads không ghi được.', 'cb-site-transfer');
    }
    if (!class_exists('ZipArchive')) {
        $blocking[] = __('ZipArchive chưa được bật.', 'cb-site-transfer');
    }
    $free_space = @disk_free_space($upload['basedir']);
    $uncompressed_media = 0;
    foreach ($attachments as $attachment) {
        foreach ((array) ($attachment['files'] ?? []) as $file) $uncompressed_media += absint($file['size'] ?? 0);
    }
    if ($free_space !== false && $free_space < ($uncompressed_media * 1.2)) {
        $blocking[] = __('Không đủ dung lượng trống để nhập media.', 'cb-site-transfer');
    }

    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $missing = [];
    if (!is_plugin_active('cb-company-core/cb-company-core.php')) $missing[] = 'cb-company-core';
    if (!wp_get_theme('cb-company-theme')->exists()) $missing[] = 'cb-company-theme';
    if (!is_plugin_active('cb-webp-converter/cb-webp-converter.php')) $warnings[] = __('CB WebP Converter chưa active.', 'cb-site-transfer');
    if ($missing) $blocking[] = sprintf(__('Thiếu dependency: %s', 'cb-site-transfer'), implode(', ', $missing));

    $conflicts = ['posts' => 0, 'terms' => 0, 'attachments' => 0, 'menus' => 0];
    $post_types = [];
    foreach ($posts as $post) {
        $post_types[$post['post_type']] = ($post_types[$post['post_type']] ?? 0) + 1;
        if (cb_transfer_find_post_by_uuid($post['source_uuid'] ?? '', $post['post_type'] ?? 'any')) $conflicts['posts']++;
    }
    foreach ($terms as $term) {
        if (cb_transfer_find_term_by_uuid($term['source_uuid'] ?? '', $term['taxonomy'] ?? '')) $conflicts['terms']++;
    }
    foreach ($attachments as $attachment) {
        if (cb_transfer_find_post_by_uuid($attachment['source_uuid'] ?? '', 'attachment')) $conflicts['attachments']++;
    }
    foreach ((array) ($menus['menus'] ?? []) as $menu) {
        if (cb_transfer_find_term_by_uuid($menu['source_uuid'] ?? '', 'nav_menu')) $conflicts['menus']++;
    }

    return [
        'package_version' => sanitize_text_field($manifest['format_version'] ?? ''),
        'exported_at' => sanitize_text_field($manifest['exported_at'] ?? ''),
        'source_url' => esc_url_raw($manifest['source_url'] ?? ''),
        'target_url' => home_url(),
        'package_size' => absint($package_size),
        'counts' => [
            'posts' => count($posts),
            'pages' => absint($post_types['page'] ?? 0),
            'products' => absint($post_types['product'] ?? 0),
            'attachments' => count($attachments),
            'terms' => count($terms),
            'menus' => count((array) ($menus['menus'] ?? [])),
        ],
        'languages' => array_values(array_map('sanitize_key', (array) ($manifest['language_codes'] ?? []))),
        'source_versions' => [
            'wordpress' => sanitize_text_field($manifest['wordpress_version'] ?? ''),
            'php' => sanitize_text_field($manifest['php_version'] ?? ''),
            'requirements' => (array) ($manifest['requirements'] ?? []),
        ],
        'target_versions' => ['wordpress' => get_bloginfo('version'), 'php' => PHP_VERSION],
        'uploads_writable' => empty($upload['error']) && wp_is_writable($upload['basedir']),
        'zip_available' => class_exists('ZipArchive'),
        'webp_supported' => wp_image_editor_supports(['mime_type' => 'image/webp']),
        'free_space' => $free_space === false ? null : $free_space,
        'media_uncompressed_size' => $uncompressed_media,
        'conflicts' => $conflicts,
        'missing_dependencies' => $missing,
        'blocking_errors' => $blocking,
        'warnings' => $warnings,
    ];
}
