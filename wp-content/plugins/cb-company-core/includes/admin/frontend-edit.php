<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'cb_register_editing_guide_submenu', 20);

function cb_register_editing_guide_submenu()
{
    add_submenu_page(
        'cb-company',
        __('Hướng dẫn chỉnh sửa', 'cb-company-core'),
        __('Hướng dẫn chỉnh sửa', 'cb-company-core'),
        'manage_options',
        'cb-company-editing-guide',
        'cb_render_editing_guide_page'
    );
}

function cb_render_editing_guide_page()
{
    if (function_exists('cb_admin_shell_start')) {
        cb_admin_shell_start(__('Hướng dẫn chỉnh sửa', 'cb-company-core'), 'cb-company-editing-guide');
    } else {
        echo '<div class="wrap">';
    }
    $workflows = [
        __('Sửa text trên trang', 'cb-company-core') => __('Mở trang khi đã đăng nhập, bấm Chỉnh trang này trên thanh WordPress, hover section và chọn Sửa. Lưu xong trang sẽ reload để kiểm tra ngay.', 'cb-company-core'),
        __('Đổi ảnh hero hoặc ảnh section', 'cb-company-core') => __('Trong quick drawer chọn tab Ảnh, bấm Chọn hình ảnh để mở Media Library. Hình được lưu bằng attachment ID và URL.', 'cb-company-core'),
        __('Thêm, xóa hoặc nhân bản section', 'cb-company-core') => __('Vào CB Company > Nội dung trang, chọn Page, dùng Thư viện section để thêm preset hoặc nút Nhân bản/Xóa trong từng section.', 'cb-company-core'),
        __('Sửa menu, header và footer', 'cb-company-core') => __('Menu chính vẫn chỉnh tại Giao diện > Menu. Header/Footer chỉnh tại CB Company > Header hoặc Footer.', 'cb-company-core'),
        __('Khôi phục revision', 'cb-company-core') => __('Mỗi lần lưu Page Builder sẽ tạo revision. Vào CB Company > Nội dung trang để chọn revision và khôi phục.', 'cb-company-core'),
    ];
    echo '<div class="cb-editing-guide"><h1>' . esc_html__('Hướng dẫn chỉnh sửa Aurelia', 'cb-company-core') . '</h1>';
    echo '<p>' . esc_html__('Dùng sửa nhanh ngoài frontend cho nội dung/ảnh/màu cơ bản. Dùng trình dựng trang cho thao tác nâng cao, responsive, đồng bộ bản dịch và import/export JSON.', 'cb-company-core') . '</p>';
    echo '<div class="cb-guide-grid">';
    foreach ($workflows as $title => $description) {
        echo '<section class="cb-guide-card"><h2>' . esc_html($title) . '</h2><p>' . esc_html($description) . '</p></section>';
    }
    echo '</div></div>';
    if (function_exists('cb_admin_shell_end')) {
        cb_admin_shell_end();
    } else {
        echo '</div>';
    }
}

function cb_frontend_edit_post_id()
{
    $post_id = get_queried_object_id();
    return $post_id && get_post_type($post_id) === 'page' ? absint($post_id) : 0;
}

function cb_frontend_edit_can_edit($post_id = 0)
{
    $post_id = $post_id ?: cb_frontend_edit_post_id();
    return $post_id && is_user_logged_in() && current_user_can('edit_post', $post_id) && function_exists('cb_get_page_sections');
}

function cb_frontend_edit_admin_url($post_id = 0, $section_index = null, $tab = 'content')
{
    $post_id = absint($post_id ?: cb_frontend_edit_post_id());
    $args = [
        'page' => 'cb-company-content',
        'content_role' => 'other',
        'post_id' => $post_id,
    ];
    $language = get_post_meta($post_id, '_cb_language', true);
    if ($language && isset(cb_languages()[$language])) {
        $args['language'] = $language;
    }
    if ($section_index !== null && is_numeric($section_index)) {
        $args['section'] = absint($section_index);
        $args['tab'] = sanitize_key($tab ?: 'content');
    }
    return add_query_arg($args, admin_url('admin.php'));
}

function cb_frontend_edit_admin_bar($wp_admin_bar)
{
    if (!is_admin_bar_showing() || !cb_frontend_edit_can_edit()) {
        return;
    }
    $post_id = cb_frontend_edit_post_id();
    $wp_admin_bar->add_node([
        'id' => 'cb-company-quick-edit',
        'title' => '<span class="ab-icon dashicons dashicons-edit-page" aria-hidden="true"></span><span class="ab-label">' . esc_html__('Chỉnh trang này', 'cb-company-core') . '</span>',
        'href' => cb_frontend_edit_admin_url($post_id),
        'meta' => ['class' => 'cb-company-quick-edit-admin-bar'],
    ]);
    $wp_admin_bar->add_node([
        'id' => 'cb-company-quick-edit-toggle',
        'parent' => 'cb-company-quick-edit',
        'title' => esc_html__('Bật sửa nhanh ngoài trang', 'cb-company-core'),
        'href' => '#cb-quick-edit',
        'meta' => ['class' => 'cb-company-quick-edit-toggle'],
    ]);
    $wp_admin_bar->add_node([
        'id' => 'cb-company-open-builder',
        'parent' => 'cb-company-quick-edit',
        'title' => esc_html__('Mở trình dựng trang', 'cb-company-core'),
        'href' => cb_frontend_edit_admin_url($post_id),
    ]);
}

function cb_frontend_edit_enqueue_assets()
{
    if (!cb_frontend_edit_can_edit()) {
        return;
    }
    $post_id = cb_frontend_edit_post_id();
    $sections = cb_get_page_sections($post_id);
    if (!$sections) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style('cb-company-frontend-edit', CB_CORE_URL . 'assets/frontend-edit/frontend-edit.css', [], CB_CORE_VERSION);
    wp_enqueue_script('cb-company-frontend-edit', CB_CORE_URL . 'assets/frontend-edit/frontend-edit.js', [], CB_CORE_VERSION, true);
    wp_localize_script('cb-company-frontend-edit', 'cbCompanyFrontendEdit', [
        'restUrl' => esc_url_raw(rest_url('cb-company/v1/admin/page-builder/' . $post_id)),
        'nonce' => wp_create_nonce('wp_rest'),
        'postId' => $post_id,
        'adminUrl' => cb_frontend_edit_admin_url($post_id),
        'i18n' => [
            'quickEdit' => __('Sửa nhanh', 'cb-company-core'),
            'edit' => __('Sửa', 'cb-company-core'),
            'images' => __('Ảnh', 'cb-company-core'),
            'design' => __('Giao diện', 'cb-company-core'),
            'advanced' => __('Mở nâng cao', 'cb-company-core'),
            'sections' => __('Sections', 'cb-company-core'),
            'save' => __('Lưu thay đổi', 'cb-company-core'),
            'saving' => __('Đang lưu...', 'cb-company-core'),
            'saved' => __('Đã lưu thay đổi.', 'cb-company-core'),
            'cancel' => __('Đóng', 'cb-company-core'),
            'duplicate' => __('Nhân bản', 'cb-company-core'),
            'remove' => __('Xóa', 'cb-company-core'),
            'removeConfirm' => __('Bạn chắc chắn muốn xóa section này?', 'cb-company-core'),
            'selectImage' => __('Chọn hoặc tải hình ảnh', 'cb-company-core'),
            'manualImageUrl' => __('Hoặc nhập đường dẫn ảnh', 'cb-company-core'),
            'removeImage' => __('Xóa ảnh', 'cb-company-core'),
            'enable' => __('Bật section', 'cb-company-core'),
            'content' => __('Nội dung', 'cb-company-core'),
            'loading' => __('Đang tải...', 'cb-company-core'),
            'error' => __('Không thể lưu thay đổi.', 'cb-company-core'),
            'helpTitle' => __('Sửa nhanh', 'cb-company-core'),
            'helpText' => __('Sửa text, ảnh và màu cơ bản tại đây. Các thiết lập responsive/nâng cao mở trong trình dựng trang.', 'cb-company-core'),
        ],
    ]);
}
