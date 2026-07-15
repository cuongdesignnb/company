<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_workspace_root()
{
    return trailingslashit(get_temp_dir()) . 'cb-site-transfer';
}

function cb_transfer_ensure_workspace()
{
    $root = cb_transfer_workspace_root();
    if (!wp_mkdir_p($root)) {
        return new WP_Error('workspace_unwritable', __('Không thể tạo thư mục tạm cho Site Transfer.', 'cb-site-transfer'));
    }
    return $root;
}

function cb_transfer_create_workspace($job_id)
{
    $root = cb_transfer_ensure_workspace();
    if (is_wp_error($root)) {
        return $root;
    }
    $path = trailingslashit($root) . sanitize_file_name($job_id);
    if (!wp_mkdir_p($path . '/data') || !wp_mkdir_p($path . '/media')) {
        return new WP_Error('workspace_unwritable', __('Không thể tạo workspace cho job.', 'cb-site-transfer'));
    }
    return $path;
}

function cb_transfer_write_json($path, $data)
{
    try {
        $json = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return new WP_Error('json_encode_failed', $exception->getMessage());
    }
    if (file_put_contents($path, $json, LOCK_EX) === false) {
        return new WP_Error('write_failed', sprintf(__('Không thể ghi file %s.', 'cb-site-transfer'), basename($path)));
    }
    return true;
}

function cb_transfer_read_json($path)
{
    if (!is_readable($path)) {
        return new WP_Error('json_missing', sprintf(__('Không đọc được file %s.', 'cb-site-transfer'), basename($path)));
    }
    try {
        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return new WP_Error('json_invalid', $exception->getMessage());
    }
}

function cb_transfer_build_checksums($workspace)
{
    $checksums = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workspace, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $relative = ltrim(str_replace(wp_normalize_path($workspace), '', wp_normalize_path($file->getPathname())), '/');
        if ($relative === 'checksums.json' || str_ends_with($relative, '.cbsite.zip')) {
            continue;
        }
        $checksums[$relative] = hash_file('sha256', $file->getPathname());
    }
    ksort($checksums);
    return $checksums;
}

function cb_transfer_create_zip($workspace, $destination)
{
    if (!class_exists('ZipArchive')) {
        return new WP_Error('zip_missing', __('Máy chủ chưa bật ZipArchive.', 'cb-site-transfer'));
    }
    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return new WP_Error('zip_create_failed', __('Không thể tạo package ZIP.', 'cb-site-transfer'));
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workspace, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || wp_normalize_path($file->getPathname()) === wp_normalize_path($destination)) {
            continue;
        }
        $relative = ltrim(str_replace(wp_normalize_path($workspace), '', wp_normalize_path($file->getPathname())), '/');
        $zip->addFile($file->getPathname(), $relative);
    }
    $zip->close();
    return is_file($destination) && filesize($destination) > 0 ? true : new WP_Error('zip_empty', __('Package ZIP rỗng.', 'cb-site-transfer'));
}

function cb_transfer_validate_and_extract_package($package_path, $job_id)
{
    if (!class_exists('ZipArchive')) {
        return new WP_Error('zip_missing', __('Máy chủ chưa bật ZipArchive.', 'cb-site-transfer'));
    }
    if (!is_readable($package_path) || filesize($package_path) > cb_transfer_max_package_size()) {
        return new WP_Error('package_size', __('Package không tồn tại hoặc vượt giới hạn dung lượng.', 'cb-site-transfer'));
    }
    $workspace = cb_transfer_create_workspace($job_id . '-import');
    if (is_wp_error($workspace)) {
        return $workspace;
    }
    $zip = new ZipArchive();
    if ($zip->open($package_path) !== true) {
        return new WP_Error('zip_invalid', __('ZIP không hợp lệ.', 'cb-site-transfer'));
    }
    if ($zip->numFiles > cb_transfer_max_zip_entries()) {
        $zip->close();
        return new WP_Error('zip_entries', __('Package có quá nhiều file.', 'cb-site-transfer'));
    }
    $total_size = 0;
    $seen_entries = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        $name = str_replace('\\', '/', (string) ($stat['name'] ?? ''));
        if (str_ends_with($name, '/')) {
            continue;
        }
        if (isset($seen_entries[$name])) {
            $zip->close();
            return new WP_Error('zip_duplicate_entry', __('Package chứa file trùng tên.', 'cb-site-transfer'));
        }
        $seen_entries[$name] = true;
        $validation = cb_transfer_validate_package_entry($name);
        if (is_wp_error($validation)) {
            $zip->close();
            return $validation;
        }
        $total_size += (int) ($stat['size'] ?? 0);
        if ($total_size > cb_transfer_max_uncompressed_size()) {
            $zip->close();
            return new WP_Error('zip_uncompressed_size', __('Package giải nén vượt giới hạn.', 'cb-site-transfer'));
        }
        $attributes = 0;
        if ($zip->getExternalAttributesIndex($index, $opsys, $attributes) && (($attributes >> 16) & 0170000) === 0120000) {
            $zip->close();
            return new WP_Error('zip_symlink', __('Package không được chứa symbolic link.', 'cb-site-transfer'));
        }
        $target = $workspace . '/' . $name;
        if (!cb_transfer_assert_inside($target, $workspace)) {
            $zip->close();
            return new WP_Error('unsafe_target', __('Đường dẫn giải nén không an toàn.', 'cb-site-transfer'));
        }
        wp_mkdir_p(dirname($target));
        $source = $zip->getStream($name);
        $destination = fopen($target, 'wb');
        if (!$source || !$destination) {
            if (is_resource($source)) fclose($source);
            if (is_resource($destination)) fclose($destination);
            $zip->close();
            return new WP_Error('extract_failed', __('Không thể giải nén package.', 'cb-site-transfer'));
        }
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);
    }
    $zip->close();

    $manifest = cb_transfer_read_json($workspace . '/manifest.json');
    $checksums = cb_transfer_read_json($workspace . '/checksums.json');
    if (is_wp_error($manifest) || is_wp_error($checksums)) {
        return is_wp_error($manifest) ? $manifest : $checksums;
    }
    if (($manifest['format'] ?? '') !== 'cb-site-package' || ($manifest['format_version'] ?? '') !== CB_TRANSFER_FORMAT_VERSION) {
        return new WP_Error('format_version', __('Phiên bản package không được hỗ trợ.', 'cb-site-transfer'));
    }
    $required_files = [
        'manifest.json',
        'data/site.json',
        'data/options.json',
        'data/posts.json',
        'data/terms.json',
        'data/menus.json',
        'data/attachments.json',
        'data/relationships.json',
    ];
    foreach ($required_files as $required_file) {
        if (!is_file($workspace . '/' . $required_file)) {
            return new WP_Error('package_incomplete', sprintf(__('Package thiếu file bắt buộc: %s', 'cb-site-transfer'), $required_file));
        }
    }
    if (!is_array($checksums)) {
        return new WP_Error('checksums_invalid', __('Danh sách checksum không hợp lệ.', 'cb-site-transfer'));
    }
    foreach ($checksums as $relative => $expected) {
        $safe = cb_transfer_safe_relative_path($relative);
        $path = $safe ? $workspace . '/' . $safe : '';
        $expected = strtolower((string) $expected);
        if (!$safe || !preg_match('/^[a-f0-9]{64}$/', $expected) || !is_file($path) || !hash_equals($expected, hash_file('sha256', $path))) {
            return new WP_Error('checksum_failed', sprintf(__('Checksum không hợp lệ: %s', 'cb-site-transfer'), $relative));
        }
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workspace, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $relative = ltrim(str_replace(wp_normalize_path($workspace), '', wp_normalize_path($file->getPathname())), '/');
        if ($relative !== 'checksums.json' && !isset($checksums[$relative])) {
            return new WP_Error('checksum_missing', sprintf(__('Thiếu checksum cho file %s.', 'cb-site-transfer'), $relative));
        }
    }
    return ['workspace' => $workspace, 'manifest' => $manifest, 'checksums' => $checksums, 'size' => filesize($package_path)];
}

function cb_transfer_remove_tree($path)
{
    if (!$path || !is_dir($path) || !cb_transfer_assert_inside($path, cb_transfer_workspace_root())) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}
