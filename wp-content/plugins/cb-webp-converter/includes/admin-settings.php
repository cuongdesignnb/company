<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_webp_register_settings_page()
{
    add_options_page(__('Chuyển đổi WebP', 'cb-webp-converter'), __('Chuyển đổi WebP', 'cb-webp-converter'), 'manage_options', 'cb-webp-converter', 'cb_webp_render_settings_page');
}

function cb_webp_register_settings()
{
    register_setting('cb_webp_options_group', 'cb_webp_options', ['sanitize_callback' => 'cb_webp_sanitize_options']);
}

function cb_webp_sanitize_options($input)
{
    $defaults = cb_webp_default_options();
    return [
        'enabled' => !empty($input['enabled']) ? '1' : '0',
        'quality' => (string) min(100, max(1, absint($input['quality'] ?? $defaults['quality']))),
        'convert_thumbnails' => !empty($input['convert_thumbnails']) ? '1' : '0',
        'keep_original' => !empty($input['keep_original']) ? '1' : '0',
        'skip_larger_than' => (string) max(1, absint($input['skip_larger_than'] ?? $defaults['skip_larger_than'])),
        'log' => sanitize_textarea_field($input['log'] ?? ''),
    ];
}

function cb_webp_render_settings_page()
{
    $options = cb_webp_options();
    echo '<div class="wrap"><h1>' . esc_html__('Chuyển đổi WebP', 'cb-webp-converter') . '</h1><form method="post" action="options.php">';
    settings_fields('cb_webp_options_group');
    echo '<table class="form-table">';
    foreach (['enabled' => __('Tự động chuyển đổi', 'cb-webp-converter'), 'convert_thumbnails' => __('Chuyển đổi ảnh thumbnail', 'cb-webp-converter'), 'keep_original' => __('Giữ file gốc', 'cb-webp-converter')] as $key => $label) {
        echo '<tr><th>' . esc_html($label) . '</th><td><label><input type="checkbox" name="cb_webp_options[' . esc_attr($key) . ']" value="1" ' . checked($options[$key], '1', false) . '> ' . esc_html__('Bật', 'cb-webp-converter') . '</label></td></tr>';
    }
    echo '<tr><th>' . esc_html__('Chất lượng WebP', 'cb-webp-converter') . '</th><td><input type="number" min="1" max="100" name="cb_webp_options[quality]" value="' . esc_attr($options['quality']) . '"></td></tr>';
    echo '<tr><th>' . esc_html__('Bỏ qua ảnh lớn hơn (MB)', 'cb-webp-converter') . '</th><td><input type="number" min="1" name="cb_webp_options[skip_larger_than]" value="' . esc_attr($options['skip_larger_than']) . '"></td></tr>';
    echo '<tr><th>' . esc_html__('Nhật ký chuyển đổi', 'cb-webp-converter') . '</th><td><textarea readonly class="large-text" rows="8" name="cb_webp_options[log]">' . esc_textarea($options['log']) . '</textarea></td></tr>';
    echo '</table>';
    submit_button();
    echo '</form><hr><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('cb_webp_bulk_convert', 'cb_webp_nonce');
    echo '<input type="hidden" name="action" value="cb_webp_bulk_convert">';
    submit_button(__('Chuyển đổi hàng loạt ảnh hiện có', 'cb-webp-converter'));
    echo '</form></div>';
}

function cb_webp_media_column($columns)
{
    $columns['cb_webp'] = 'WebP';
    return $columns;
}

function cb_webp_media_column_value($column, $post_id)
{
    if ($column === 'cb_webp') {
        echo esc_html(get_post_meta($post_id, '_cb_webp_converted', true) ?: 'no');
    }
}

function cb_webp_handle_bulk_convert()
{
    if (!current_user_can('upload_files') || !isset($_POST['cb_webp_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cb_webp_nonce'])), 'cb_webp_bulk_convert')) {
        wp_die(esc_html__('Not allowed.', 'cb-webp-converter'), 403);
    }
    $ids = get_posts([
        'post_type' => 'attachment',
        'post_mime_type' => ['image/jpeg', 'image/png'],
        'posts_per_page' => 100,
        'fields' => 'ids',
    ]);
    foreach ($ids as $id) {
        cb_webp_convert_attachment($id);
    }
    wp_safe_redirect(admin_url('options-general.php?page=cb-webp-converter&converted=' . count($ids)));
    exit;
}
