<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_admin_field_wrap($args, $control)
{
    $desc = $args['description'] ?? '';
    $default = $args['default'] ?? null;
    echo '<div class="cb-admin-field cb-admin-field-' . esc_attr($args['type'] ?? 'text') . '">';
    echo '<label class="cb-admin-label" for="' . esc_attr($args['id']) . '">' . esc_html($args['label'] ?? $args['id']) . '</label>';
    echo '<div class="cb-admin-control">' . $control;
    if ($desc) {
        echo '<p class="cb-admin-description">' . esc_html($desc) . '</p>';
    }
    $friendly_default = cb_admin_friendly_default($args, $default);
    if ($friendly_default !== '') {
        echo '<p class="cb-admin-default">' . esc_html__('Kế thừa:', 'cb-company-core') . ' <strong>' . esc_html($friendly_default) . '</strong></p>';
    }
    echo '<div class="cb-field-error" aria-live="polite"></div></div></div>';
}

function cb_admin_friendly_default($args, $default)
{
    if ($default === null || $default === '' || is_array($default)) {
        return '';
    }
    if (($args['type'] ?? '') === 'checkbox') {
        return (string) $default === '1' ? __('Đang bật', 'cb-company-core') : __('Đang tắt', 'cb-company-core');
    }
    if (isset($args['choices'][(string) $default])) {
        return (string) $args['choices'][(string) $default];
    }
    if ((string) $default === 'default') {
        return __('Bố cục mặc định', 'cb-company-core');
    }
    return (string) $default;
}

function cb_admin_text_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $type = $args['input_type'] ?? 'text';
    cb_admin_field_wrap($args + ['type' => 'text'], '<input class="regular-text" type="' . esc_attr($type) . '" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '">');
}

function cb_admin_textarea_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $control = '<textarea class="large-text" rows="' . esc_attr((string) absint($args['rows'] ?? 4)) . '" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '">' . esc_textarea($value) . '</textarea>';
    cb_admin_field_wrap($args + ['type' => 'textarea'], $control);
}

function cb_admin_select_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $control = '<select id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '">';
    foreach (($args['choices'] ?? []) as $key => $label) {
        $control .= '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $control .= '</select>';
    cb_admin_field_wrap($args + ['type' => 'select'], $control);
}

function cb_admin_checkbox_field($args)
{
    $value = (string) ($args['value'] ?? $args['default'] ?? '0');
    $control = '<input type="hidden" name="' . esc_attr($args['name']) . '" value="0">';
    $control .= '<label class="cb-toggle"><input type="checkbox" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="1" ' . checked($value, '1', false) . '><span aria-hidden="true"></span></label>';
    cb_admin_field_wrap($args + ['type' => 'checkbox'], $control);
}

function cb_admin_color_field($args)
{
    $value = sanitize_hex_color($args['value'] ?? $args['default'] ?? '') ?: ($args['default'] ?? '');
    $control = '<input class="cb-color-field" type="text" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($args['default'] ?? '') . '">';
    cb_admin_field_wrap($args + ['type' => 'color'], $control);
}

function cb_admin_number_field($args)
{
    $attrs = '';
    foreach (['min', 'max', 'step'] as $attr) {
        if (isset($args[$attr])) {
            $attrs .= ' ' . $attr . '="' . esc_attr((string) $args[$attr]) . '"';
        }
    }
    $value = $args['value'] ?? $args['default'] ?? '';
    cb_admin_field_wrap($args + ['type' => 'number'], '<input class="small-text" type="number" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '"' . $attrs . '>');
}

function cb_admin_range_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $control = '<input type="range" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" min="' . esc_attr((string) ($args['min'] ?? 0)) . '" max="' . esc_attr((string) ($args['max'] ?? 100)) . '" step="' . esc_attr((string) ($args['step'] ?? 1)) . '"> <output>' . esc_html((string) $value) . '</output>';
    cb_admin_field_wrap($args + ['type' => 'range'], $control);
}

function cb_admin_image_field($args)
{
    $id_value = absint($args['id_value'] ?? 0);
    $url_value = esc_url($args['url_value'] ?? '');
    $preview = $url_value ? '<img src="' . esc_url($url_value) . '" alt="">' : '';
    $control = '<div class="cb-image-field" data-frame-title="' . esc_attr($args['frame_title'] ?? __('Chọn hình ảnh', 'cb-company-core')) . '">';
    $control .= '<div class="cb-image-preview">' . $preview . '</div>';
    $control .= '<input type="hidden" class="cb-image-id" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name_base'] . '[' . $args['id_key'] . ']') . '" value="' . esc_attr((string) $id_value) . '">';
    $control .= '<input type="hidden" class="cb-image-url" name="' . esc_attr($args['name_base'] . '[' . $args['url_key'] . ']') . '" value="' . esc_attr($url_value) . '">';
    $control .= '<button type="button" class="button cb-pick-image"><span class="dashicons dashicons-format-image" aria-hidden="true"></span> ' . esc_html__('Chọn ảnh', 'cb-company-core') . '</button>';
    $control .= '<button type="button" class="button-link-delete cb-remove-image">' . esc_html__('Xóa ảnh', 'cb-company-core') . '</button></div>';
    cb_admin_field_wrap($args + ['type' => 'image'], $control);
}

function cb_admin_dimension_field($args)
{
    cb_admin_text_field($args + ['description' => trim(($args['description'] ?? '') . ' ' . __('Đơn vị hỗ trợ: px, rem, %, vh, vw.', 'cb-company-core'))]);
}

function cb_admin_repeater_field($args)
{
    $items = is_array($args['value'] ?? null) ? $args['value'] : cb_legacy_lines_to_repeater($args['value'] ?? '');
    ob_start();
    echo '<div class="cb-repeater" data-name-base="' . esc_attr($args['name']) . '"><div class="cb-repeater-list">';
    foreach ($items as $index => $item) {
        cb_render_repeater_row($args['name'], $index, $item);
    }
    echo '</div><button type="button" class="button cb-add-repeater-item"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ' . esc_html__('Thêm mục', 'cb-company-core') . '</button>';
    echo '<template class="cb-repeater-template">';
    cb_render_repeater_row($args['name'], '__item__', []);
    echo '</template></div>';
    cb_admin_field_wrap($args + ['type' => 'repeater'], ob_get_clean());
}

function cb_render_repeater_row($name, $index, $item)
{
    $item = wp_parse_args((array) $item, ['title' => '', 'description' => '', 'image_id' => 0, 'image_url' => '', 'url' => '']);
    $base = $name . '[' . $index . ']';
    echo '<div class="cb-repeater-row">';
    echo '<div class="cb-repeater-head"><span class="dashicons dashicons-move cb-repeater-handle" aria-hidden="true"></span><strong>' . esc_html__('Mục', 'cb-company-core') . ' <span class="cb-repeater-number">' . esc_html((string) ((int) $index + 1)) . '</span></strong><span class="cb-repeater-spacer"></span><button type="button" class="button-link cb-duplicate-repeater">' . esc_html__('Nhân bản', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-repeater">' . esc_html__('Xóa', 'cb-company-core') . '</button></div>';
    echo '<div class="cb-repeater-fields"><label>' . esc_html__('Tiêu đề', 'cb-company-core') . '<input type="text" name="' . esc_attr($base . '[title]') . '" value="' . esc_attr($item['title']) . '"></label>';
    echo '<label class="cb-wide">' . esc_html__('Mô tả', 'cb-company-core') . '<textarea rows="3" name="' . esc_attr($base . '[description]') . '">' . esc_textarea($item['description']) . '</textarea></label>';
    echo '<label>' . esc_html__('Liên kết', 'cb-company-core') . '<input type="url" name="' . esc_attr($base . '[url]') . '" value="' . esc_attr($item['url']) . '"></label>';
    $preview = $item['image_url'] ? '<img src="' . esc_url($item['image_url']) . '" alt="">' : '';
    echo '<div class="cb-image-field cb-repeater-image" data-frame-title="' . esc_attr__('Chọn hình ảnh', 'cb-company-core') . '"><span class="cb-repeater-field-label">' . esc_html__('Hình ảnh', 'cb-company-core') . '</span><div class="cb-image-preview">' . $preview . '</div><input class="cb-image-id" type="hidden" name="' . esc_attr($base . '[image_id]') . '" value="' . esc_attr((string) absint($item['image_id'])) . '"><input class="cb-image-url" type="hidden" name="' . esc_attr($base . '[image_url]') . '" value="' . esc_attr($item['image_url']) . '"><button type="button" class="button cb-pick-image">' . esc_html__('Chọn ảnh', 'cb-company-core') . '</button><button type="button" class="button-link-delete cb-remove-image">' . esc_html__('Xóa ảnh', 'cb-company-core') . '</button></div></div></div>';
}

function cb_admin_enqueue_assets($hook)
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $page = sanitize_key(wp_unslash($_GET['page'] ?? ''));
    $cb_pages = array_keys(cb_admin_menu_items());
    $is_cb_page = in_array($page, $cb_pages, true) || $page === 'cb-company-editing-guide';
    $editable_types = ['page', 'post', 'product', 'factory_showcase', 'case_study', 'video', 'certificate', 'inquiry'];
    $is_edit_screen = $screen && $screen->base === 'post' && in_array($screen->post_type, $editable_types, true);
    if (!$is_cb_page && !$is_edit_screen) {
        return;
    }
    wp_enqueue_style('cb-company-admin', CB_CORE_URL . 'assets/admin/admin.css', [], CB_CORE_VERSION);
    wp_enqueue_script('cb-company-admin-core', CB_CORE_URL . 'assets/admin/tabs.js', [], CB_CORE_VERSION, true);

    if ($is_cb_page) {
        wp_enqueue_script('cb-company-admin-settings', CB_CORE_URL . 'assets/admin/settings.js', ['cb-company-admin-core'], CB_CORE_VERSION, true);
    }
    if (in_array($page, ['cb-company-design', 'cb-company-header', 'cb-company-footer', 'cb-company-templates', 'cb-company-content'], true) || ($is_edit_screen && $screen->post_type === 'page')) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
    if (in_array($page, ['cb-company-design', 'cb-company-footer'], true)) {
        wp_enqueue_script('cb-company-admin-media', CB_CORE_URL . 'assets/admin/media.js', [], CB_CORE_VERSION, true);
    }
    if ($page === 'cb-company-footer') {
        wp_enqueue_script('cb-company-admin-repeater', CB_CORE_URL . 'assets/admin/repeater.js', [], CB_CORE_VERSION, true);
    }
    if ($is_edit_screen || $page === 'cb-company-content') {
        $has_structured_media = $screen->post_type !== 'inquiry';
        if ($has_structured_media) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
        }
        wp_enqueue_script('cb-company-admin', CB_CORE_URL . 'assets/admin/admin.js', ['jquery'], CB_CORE_VERSION, true);
    }
    if ($page === 'cb-company-content') {
        wp_enqueue_script('cb-company-content', CB_CORE_URL . 'assets/admin/content.js', ['cb-company-admin'], CB_CORE_VERSION, true);
    }
    wp_localize_script('cb-company-admin-core', 'cbCompanyAdmin', [
        'restUrl' => esc_url_raw(rest_url('cb-company/v1/')),
        'mediaRestUrl' => esc_url_raw(rest_url('wp/v2/media')),
        'nonce' => wp_create_nonce('wp_rest'),
        'version' => CB_CORE_VERSION,
        'page' => $page,
        'i18n' => [
        'addSection' => __('Thêm khu vực', 'cb-company-core'), 'duplicate' => __('Nhân bản', 'cb-company-core'),
        'remove' => __('Xóa', 'cb-company-core'), 'removeConfirm' => __('Bạn chắc chắn muốn xóa khu vực này?', 'cb-company-core'),
        'collapse' => __('Thu gọn', 'cb-company-core'), 'expand' => __('Mở rộng', 'cb-company-core'),
        'selectImage' => __('Chọn hình ảnh', 'cb-company-core'), 'removeImage' => __('Xóa hình ảnh', 'cb-company-core'),
        'selectFile' => __('Chọn tệp PDF', 'cb-company-core'), 'useFile' => __('Dùng tệp này', 'cb-company-core'),
        'unsavedChanges' => __('Bạn có thay đổi chưa được lưu.', 'cb-company-core'),
        'resetConfirm' => __('Bạn chắc chắn muốn khôi phục cài đặt?', 'cb-company-core'),
        'loading' => __('Đang tải dữ liệu...', 'cb-company-core'),
        'loadError' => __('Không thể tải khu vực này.', 'cb-company-core'),
        'saving' => __('Đang lưu...', 'cb-company-core'),
        'saved' => __('Đã lưu thay đổi.', 'cb-company-core'),
        'saveError' => __('Không thể lưu thay đổi.', 'cb-company-core'),
        'searching' => __('Đang tìm Page...', 'cb-company-core'),
        'noResults' => __('Không tìm thấy Page phù hợp.', 'cb-company-core'),
        'unassigned' => __('Chưa gán', 'cb-company-core'),
        'chooseImage' => __('Chọn ảnh này', 'cb-company-core'),
        'uploadImage' => __('Tải ảnh mới', 'cb-company-core'),
        'searchImages' => __('Tìm hình ảnh...', 'cb-company-core'),
        'noImages' => __('Không tìm thấy hình ảnh.', 'cb-company-core'),
        'close' => __('Đóng', 'cb-company-core'),
        'imageSelected' => __('Đã chọn ảnh desktop', 'cb-company-core'),
        'imageMissing' => __('Chưa chọn ảnh desktop', 'cb-company-core'),
        'revisionLabel' => __('Khôi phục revision', 'cb-company-core'),
        'restoreConfirm' => __('Khôi phục revision này? Nội dung hiện tại sẽ được lưu thành một revision mới.', 'cb-company-core'),
        'restoreError' => __('Không thể khôi phục revision.', 'cb-company-core'),
    ]]);
}
