<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_webp_default_options()
{
    return [
        'enabled' => '1',
        'quality' => '85',
        'convert_thumbnails' => '1',
        'keep_original' => '1',
        'skip_larger_than' => '10',
        'log' => '',
    ];
}

function cb_webp_options()
{
    return wp_parse_args((array) get_option('cb_webp_options', []), cb_webp_default_options());
}

function cb_webp_log($message)
{
    $options = cb_webp_options();
    $line = '[' . current_time('mysql') . '] ' . sanitize_text_field($message);
    $options['log'] = trim($line . "\n" . ($options['log'] ?? ''));
    update_option('cb_webp_options', $options);
}

function cb_webp_generate_on_upload($metadata, $attachment_id)
{
    $options = cb_webp_options();
    if ($options['enabled'] !== '1') {
        return $metadata;
    }
    cb_webp_convert_attachment(absint($attachment_id));
    return $metadata;
}

function cb_webp_convert_attachment($attachment_id)
{
    if (!current_user_can('upload_files') && !wp_doing_cron()) {
        return false;
    }
    $file = get_attached_file($attachment_id);
    if (!$file || !cb_webp_path_is_in_uploads($file)) {
        cb_webp_log('Skipped invalid attachment path: ' . $attachment_id);
        return false;
    }
    $converted = [];
    $main = cb_webp_convert_file($file);
    if ($main) {
        $converted[] = $main;
    }

    $options = cb_webp_options();
    if ($options['convert_thumbnails'] === '1') {
        $meta = wp_get_attachment_metadata($attachment_id);
        $base = trailingslashit(dirname($file));
        foreach (($meta['sizes'] ?? []) as $size) {
            if (!empty($size['file'])) {
                $thumb = $base . $size['file'];
                $out = cb_webp_convert_file($thumb);
                if ($out) {
                    $converted[] = $out;
                }
            }
        }
    }

    update_post_meta($attachment_id, '_cb_webp_converted', $converted ? 'yes' : 'no');
    update_post_meta($attachment_id, '_cb_webp_files', $converted);
    return (bool) $converted;
}

function cb_webp_path_is_in_uploads($path)
{
    $uploads = wp_upload_dir();
    $real_uploads = realpath($uploads['basedir']);
    $real_path = realpath($path);
    return $real_uploads && $real_path && str_starts_with($real_path, $real_uploads);
}

function cb_webp_convert_file($path)
{
    $type = wp_check_filetype($path);
    if (!in_array($type['type'], ['image/jpeg', 'image/png'], true)) {
        return false;
    }
    $options = cb_webp_options();
    $max_bytes = max(1, absint($options['skip_larger_than'])) * 1024 * 1024;
    if (filesize($path) > $max_bytes) {
        cb_webp_log('Skipped large image: ' . basename($path));
        return false;
    }
    $target = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
    if (!$target) {
        return false;
    }
    $quality = min(100, max(1, absint($options['quality'])));

    if (extension_loaded('imagick')) {
        try {
            $image = new Imagick($path);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($quality);
            $image->writeImage($target);
            $image->clear();
            return $target;
        } catch (Throwable $e) {
            cb_webp_log($e->getMessage());
        }
    }

    if (!function_exists('imagewebp')) {
        cb_webp_log('GD WebP support is not available.');
        return false;
    }
    $source = $type['type'] === 'image/png' ? imagecreatefrompng($path) : imagecreatefromjpeg($path);
    if (!$source) {
        cb_webp_log('Could not read image: ' . basename($path));
        return false;
    }
    imagepalettetotruecolor($source);
    imagealphablending($source, true);
    imagesavealpha($source, true);
    $ok = imagewebp($source, $target, $quality);
    imagedestroy($source);
    return $ok ? $target : false;
}
