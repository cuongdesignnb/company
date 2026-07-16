<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_content_meta_schemas()
{
    $language = [
        '_cb_language' => [__('Ngôn ngữ nội dung', 'cb-company-core'), 'select', ['en' => 'English', 'zh' => '中文']],
        '_cb_translation_group' => [__('Nhóm bản dịch', 'cb-company-core'), 'text'],
    ];
    $seo = [
        '_cb_seo_title' => [__('SEO title', 'cb-company-core'), 'text'],
        '_cb_seo_description' => [__('SEO description', 'cb-company-core'), 'textarea'],
        '_cb_seo_image' => [__('Open Graph image', 'cb-company-core'), 'image'],
        '_cb_canonical' => [__('Canonical URL', 'cb-company-core'), 'url'],
        '_cb_noindex' => [__('Không lập chỉ mục', 'cb-company-core'), 'checkbox'],
    ];
    return [
        'page' => [
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
        ],
        'post' => [
            'content' => [__('Nội dung', 'cb-company-core'), ['_cb_short_description' => [__('Mô tả ngắn', 'cb-company-core'), 'textarea'], '_cb_featured' => [__('Bài viết nổi bật', 'cb-company-core'), 'checkbox']]],
            'images' => [__('Hình ảnh', 'cb-company-core'), ['_cb_gallery' => [__('Thư viện hình ảnh', 'cb-company-core'), 'repeater']]],
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
            'seo' => [__('SEO', 'cb-company-core'), $seo],
        ],
        'product' => [
            'general' => [__('Thông tin chung', 'cb-company-core'), [
                '_cb_model' => [__('Mã sản phẩm', 'cb-company-core'), 'text'], '_cb_brand' => [__('Thương hiệu', 'cb-company-core'), 'text'],
                '_cb_origin' => [__('Xuất xứ', 'cb-company-core'), 'text'], '_cb_short_description' => [__('Mô tả ngắn', 'cb-company-core'), 'textarea'],
                '_cb_featured' => [__('Sản phẩm nổi bật', 'cb-company-core'), 'checkbox'], '_cb_display_order' => [__('Thứ tự hiển thị', 'cb-company-core'), 'number'],
            ]],
            'specs' => [__('Thông số kỹ thuật', 'cb-company-core'), [
                '_cb_material' => [__('Chất liệu', 'cb-company-core'), 'text'], '_cb_voltage' => [__('Điện áp', 'cb-company-core'), 'text'],
                '_cb_power' => [__('Công suất', 'cb-company-core'), 'text'], '_cb_certification' => [__('Chứng nhận', 'cb-company-core'), 'text'],
                '_cb_moq' => [__('MOQ', 'cb-company-core'), 'text'], '_cb_lead_time' => [__('Thời gian giao hàng', 'cb-company-core'), 'text'],
                '_cb_specs' => [__('Bảng thông số', 'cb-company-core'), 'repeater'],
            ]],
            'gallery' => [__('Thư viện', 'cb-company-core'), ['_cb_gallery' => [__('Hình ảnh sản phẩm', 'cb-company-core'), 'repeater']]],
            'documents' => [__('Tài liệu và video', 'cb-company-core'), ['_cb_catalog_url' => [__('Catalog PDF URL', 'cb-company-core'), 'url'], '_cb_video_url' => [__('Video URL', 'cb-company-core'), 'url']]],
            'inquiry' => [__('Inquiry', 'cb-company-core'), ['_cb_inquiry_enabled' => [__('Hiện form gửi yêu cầu', 'cb-company-core'), 'checkbox']]],
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
            'seo' => [__('SEO', 'cb-company-core'), $seo],
        ],
        'case_study' => [
            'general' => [__('Thông tin chung', 'cb-company-core'), ['_cb_client_market' => [__('Khách hàng / Thị trường', 'cb-company-core'), 'text'], '_cb_country' => [__('Quốc gia', 'cb-company-core'), 'text'], '_cb_short_description' => [__('Mô tả ngắn', 'cb-company-core'), 'textarea']]],
            'problem' => [__('Vấn đề', 'cb-company-core'), ['_cb_problem' => [__('Vấn đề cần giải quyết', 'cb-company-core'), 'richtext']]],
            'solution' => [__('Giải pháp', 'cb-company-core'), ['_cb_solution' => [__('Giải pháp thực hiện', 'cb-company-core'), 'richtext']]],
            'result' => [__('Kết quả', 'cb-company-core'), ['_cb_result' => [__('Kết quả đạt được', 'cb-company-core'), 'richtext']]],
            'gallery' => [__('Thư viện', 'cb-company-core'), ['_cb_gallery' => [__('Hình ảnh dự án', 'cb-company-core'), 'repeater']]],
            'products' => [__('Sản phẩm liên quan', 'cb-company-core'), ['_cb_related_products' => [__('ID sản phẩm, cách nhau bằng dấu phẩy', 'cb-company-core'), 'text']]],
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
            'seo' => [__('SEO', 'cb-company-core'), $seo],
        ],
        'factory_showcase' => [
            'general' => [__('Thông tin chung', 'cb-company-core'), ['_cb_short_description' => [__('Mô tả ngắn', 'cb-company-core'), 'textarea'], '_cb_featured' => [__('Nội dung nổi bật', 'cb-company-core'), 'checkbox'], '_cb_display_order' => [__('Thứ tự hiển thị', 'cb-company-core'), 'number']]],
            'media' => [__('Hình ảnh và video', 'cb-company-core'), ['_cb_gallery' => [__('Thư viện hình ảnh', 'cb-company-core'), 'repeater'], '_cb_video_url' => [__('Video URL', 'cb-company-core'), 'url']]],
            'category' => [__('Danh mục', 'cb-company-core'), ['_cb_factory_category_note' => [__('Ghi chú danh mục', 'cb-company-core'), 'text']]],
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
            'seo' => [__('SEO', 'cb-company-core'), $seo],
        ],
        'video' => [
            'general' => [__('Thông tin chung', 'cb-company-core'), ['_cb_video_url' => [__('Video URL', 'cb-company-core'), 'url'], '_cb_short_description' => [__('Mô tả ngắn', 'cb-company-core'), 'textarea']]],
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
            'seo' => [__('SEO', 'cb-company-core'), $seo],
        ],
        'certificate' => [
            'general' => [__('Thông tin chung', 'cb-company-core'), [
                '_cb_issuer' => [__('Đơn vị cấp', 'cb-company-core'), 'text'],
                '_cb_standard' => [__('Tiêu chuẩn / mã chứng nhận', 'cb-company-core'), 'text'],
                '_cb_certificate_number' => [__('Số chứng nhận', 'cb-company-core'), 'text'],
                '_cb_issue_date' => [__('Ngày cấp', 'cb-company-core'), 'date'],
                '_cb_expiry_date' => [__('Ngày hết hạn', 'cb-company-core'), 'date'],
                '_cb_verification_url' => [__('Liên kết xác minh', 'cb-company-core'), 'url'],
                '_cb_featured' => [__('Hiển thị nổi bật trên trang Giới thiệu', 'cb-company-core'), 'checkbox'],
                '_cb_display_order' => [__('Thứ tự hiển thị', 'cb-company-core'), 'number'],
                '_cb_needs_content_review' => [__('Nội dung cần xác minh', 'cb-company-core'), 'checkbox'],
            ]],
            'document' => [__('Tài liệu', 'cb-company-core'), [
                '_cb_pdf_url' => [__('Tệp PDF chứng nhận', 'cb-company-core'), 'file'],
            ]],
            'language' => [__('Ngôn ngữ', 'cb-company-core'), $language],
            'seo' => [__('SEO', 'cb-company-core'), $seo],
        ],
        'inquiry' => [
            'customer' => [__('Thông tin khách hàng', 'cb-company-core'), ['_cb_full_name' => [__('Họ tên', 'cb-company-core'), 'text'], '_cb_company_name' => [__('Công ty', 'cb-company-core'), 'text'], '_cb_email' => [__('Email', 'cb-company-core'), 'email'], '_cb_phone' => [__('Điện thoại', 'cb-company-core'), 'text'], '_cb_country' => [__('Quốc gia', 'cb-company-core'), 'text']]],
            'request' => [__('Nội dung yêu cầu', 'cb-company-core'), ['_cb_interested_product' => [__('Sản phẩm quan tâm', 'cb-company-core'), 'text'], '_cb_quantity' => [__('Số lượng', 'cb-company-core'), 'text'], '_cb_message' => [__('Nội dung', 'cb-company-core'), 'textarea']]],
            'source' => [__('Nguồn truy cập', 'cb-company-core'), ['_cb_source_url' => [__('URL nguồn', 'cb-company-core'), 'url'], '_cb_ip_address' => [__('Địa chỉ IP', 'cb-company-core'), 'text'], '_cb_user_agent' => [__('User agent', 'cb-company-core'), 'textarea'], '_cb_language' => [__('Ngôn ngữ', 'cb-company-core'), 'select', ['en' => 'English', 'zh' => '中文']]]],
            'status' => [__('Trạng thái xử lý', 'cb-company-core'), ['_cb_inquiry_status' => [__('Trạng thái', 'cb-company-core'), 'select', ['new' => __('Mới', 'cb-company-core'), 'contacted' => __('Đã liên hệ', 'cb-company-core'), 'quoted' => __('Đã báo giá', 'cb-company-core'), 'closed' => __('Đã hoàn tất', 'cb-company-core'), 'spam' => 'Spam']]]],
            'notes' => [__('Ghi chú nội bộ', 'cb-company-core'), ['_cb_internal_note' => [__('Ghi chú', 'cb-company-core'), 'textarea']]],
        ],
    ];
}

function cb_common_meta_fields($post_type = '')
{
    $fields = [];
    foreach (cb_content_meta_schemas()[$post_type] ?? [] as $tab) {
        $fields = array_merge($fields, $tab[1]);
    }
    return $fields;
}

function cb_register_meta_boxes()
{
    foreach (cb_content_meta_schemas() as $post_type => $schema) {
        $titles = ['product' => __('Dữ liệu sản phẩm', 'cb-company-core'), 'case_study' => __('Dữ liệu dự án', 'cb-company-core'), 'factory_showcase' => __('Dữ liệu nhà máy', 'cb-company-core'), 'inquiry' => __('Quản lý yêu cầu', 'cb-company-core'), 'page' => __('Ngôn ngữ nội dung', 'cb-company-core'), 'post' => __('Dữ liệu bài viết', 'cb-company-core'), 'video' => __('Dữ liệu video', 'cb-company-core'), 'certificate' => __('Dữ liệu chứng nhận', 'cb-company-core')];
        add_meta_box('cb_' . $post_type . '_fields', $titles[$post_type], 'cb_render_content_meta_box', $post_type, 'normal', 'high');
    }
    add_meta_box('cb_page_builder', __('Trình dựng trang', 'cb-company-core'), 'cb_render_page_builder_meta_box', 'page', 'normal', 'high');
    add_meta_box('cb_page_ui', __('Tùy chỉnh giao diện trang', 'cb-company-core'), 'cb_render_page_ui_meta_box', 'page', 'normal', 'default');
    add_meta_box('cb_certificate_status', __('Kiểm tra xuất bản', 'cb-company-core'), 'cb_render_certificate_status_box', 'certificate', 'side', 'high');
}

function cb_render_content_meta_box($post)
{
    wp_nonce_field('cb_save_content_meta', 'cb_content_meta_nonce');
    $schema = cb_content_meta_schemas()[$post->post_type] ?? [];
    echo '<div class="cb-meta-tabs-shell"><nav class="cb-meta-tabs">';
    $first = true;
    foreach ($schema as $key => $tab) {
        echo '<button type="button" class="cb-meta-tab ' . ($first ? 'is-active' : '') . '" data-tab="' . esc_attr($key) . '">' . esc_html($tab[0]) . '</button>';
        $first = false;
    }
    echo '</nav>';
    $first = true;
    foreach ($schema as $key => $tab) {
        echo '<div class="cb-meta-panel ' . ($first ? 'is-active' : '') . '" data-panel="' . esc_attr($key) . '">';
        foreach ($tab[1] as $meta_key => $field) {
            cb_render_content_meta_field($post->ID, $meta_key, $field);
        }
        echo '</div>';
        $first = false;
    }
    echo '</div>';
}

function cb_render_content_meta_field($post_id, $key, $field)
{
    $value = get_post_meta($post_id, $key, true);
    $args = ['id' => ltrim($key, '_'), 'name' => $key, 'label' => $field[0], 'value' => $value];
    if ($field[1] === 'textarea' || $field[1] === 'richtext') cb_admin_textarea_field($args + ['rows' => 5]);
    elseif ($field[1] === 'select') cb_admin_select_field($args + ['choices' => $field[2] ?? []]);
    elseif ($field[1] === 'checkbox') cb_admin_checkbox_field($args);
    elseif ($field[1] === 'number') cb_admin_number_field($args);
    elseif ($field[1] === 'repeater') cb_admin_repeater_field($args);
    elseif ($field[1] === 'image') cb_admin_image_field(['id' => ltrim($key, '_'), 'label' => $field[0], 'name_base' => 'cb_meta_images', 'id_key' => $key . '_id', 'url_key' => $key, 'id_value' => get_post_meta($post_id, $key . '_id', true), 'url_value' => $value]);
    elseif ($field[1] === 'file') cb_admin_file_field($post_id, $key, $field[0]);
    else cb_admin_text_field($args + ['input_type' => in_array($field[1], ['url', 'email', 'date'], true) ? $field[1] : 'text']);
}

function cb_save_common_meta_boxes($post_id)
{
    if (!isset($_POST['cb_content_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cb_content_meta_nonce'])), 'cb_save_content_meta')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) return;
    $post_type = get_post_type($post_id);
    foreach (cb_common_meta_fields($post_type) as $key => $field) {
        if ($field[1] === 'image') {
            $images = is_array($_POST['cb_meta_images'] ?? null) ? wp_unslash($_POST['cb_meta_images']) : [];
            update_post_meta($post_id, $key, esc_url_raw($images[$key] ?? ''));
            update_post_meta($post_id, $key . '_id', absint($images[$key . '_id'] ?? 0));
            continue;
        }
        if ($field[1] === 'file') {
            $files = is_array($_POST['cb_meta_files'] ?? null) ? wp_unslash($_POST['cb_meta_files']) : [];
            update_post_meta($post_id, $key, esc_url_raw($files[$key] ?? ''));
            update_post_meta($post_id, str_replace('_url', '_id', $key), absint($files[str_replace('_url', '_id', $key)] ?? 0));
            continue;
        }
        if (!array_key_exists($key, $_POST)) continue;
        $raw = wp_unslash($_POST[$key]);
        if ($field[1] === 'repeater') $value = cb_sanitize_repeater_items($raw);
        elseif ($field[1] === 'richtext') $value = wp_kses_post($raw);
        elseif ($field[1] === 'textarea') $value = sanitize_textarea_field($raw);
        elseif ($field[1] === 'url') $value = esc_url_raw($raw);
        elseif ($field[1] === 'email') $value = sanitize_email($raw);
        elseif ($field[1] === 'number') $value = (string) absint($raw);
        elseif ($field[1] === 'checkbox') $value = $raw === '1' ? '1' : '0';
        else $value = sanitize_text_field($raw);
        update_post_meta($post_id, $key, $value);
    }
}

function cb_admin_file_field($post_id, $key, $label)
{
    $id_key = str_replace('_url', '_id', $key);
    $file_id = absint(get_post_meta($post_id, $id_key, true));
    $file_url = (string) get_post_meta($post_id, $key, true);
    echo '<div class="cb-admin-field cb-admin-file-field">';
    echo '<label>' . esc_html($label) . '</label>';
    echo '<input type="hidden" class="cb-file-id" name="cb_meta_files[' . esc_attr($id_key) . ']" value="' . esc_attr($file_id) . '">';
    echo '<input type="url" class="regular-text cb-file-url" name="cb_meta_files[' . esc_attr($key) . ']" value="' . esc_attr($file_url) . '" placeholder="https://.../certificate.pdf">';
    echo '<div class="cb-media-actions">';
    echo '<button type="button" class="button cb-pick-file" data-media-type="application/pdf">' . esc_html__('Chọn PDF', 'cb-company-core') . '</button> ';
    echo '<button type="button" class="button-link-delete cb-remove-file">' . esc_html__('Xóa tệp', 'cb-company-core') . '</button>';
    echo '</div>';
    if ($file_url) {
        echo '<a class="cb-current-file" href="' . esc_url($file_url) . '" target="_blank" rel="noopener">' . esc_html__('Xem tệp hiện tại', 'cb-company-core') . '</a>';
    }
    echo '<p class="description">' . esc_html__('Tải PDF lên Media Library hoặc nhập URL trực tiếp.', 'cb-company-core') . '</p>';
    echo '</div>';
}
