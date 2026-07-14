<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_content_page_roles()
{
    return [
        'home' => __('Trang chủ', 'cb-company-core'),
        'about' => __('Giới thiệu', 'cb-company-core'),
        'contact' => __('Liên hệ', 'cb-company-core'),
        'other' => __('Các Page khác', 'cb-company-core'),
    ];
}

function cb_content_module_url($role, $language, $post_id = 0)
{
    $args = ['page' => 'cb-company-content', 'content_role' => $role, 'language' => $language];
    if ($post_id) {
        $args['post_id'] = absint($post_id);
    }
    return add_query_arg($args, admin_url('admin.php'));
}

function cb_render_content_pages_page()
{
    $roles = cb_content_page_roles();
    $role = sanitize_key(wp_unslash($_GET['content_role'] ?? 'home'));
    $language = sanitize_key(wp_unslash($_GET['language'] ?? 'en'));
    $role = isset($roles[$role]) ? $role : 'home';
    $language = isset(cb_languages()[$language]) ? $language : 'en';
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $post_id = $role === 'other' ? absint($_GET['post_id'] ?? 0) : absint($special[$language][$role] ?? 0);
    $post = $post_id ? get_post($post_id) : null;

    cb_admin_shell_start(__('Nội dung trang', 'cb-company-core'), 'cb-company-content');
    echo '<div class="cb-content-module">';
    echo '<div class="cb-content-heading"><div><h1>' . esc_html__('Nội dung trang', 'cb-company-core') . '</h1><p>' . esc_html__('Chỉnh text, hình ảnh, section và giao diện riêng của từng Page EN/ZH.', 'cb-company-core') . '</p></div></div>';
    echo '<nav class="cb-content-role-tabs" aria-label="' . esc_attr__('Loại trang', 'cb-company-core') . '">';
    foreach ($roles as $key => $label) {
        echo '<a class="' . esc_attr($key === $role ? 'is-active' : '') . '" href="' . esc_url(cb_content_module_url($key, $language)) . '">' . esc_html($label) . '</a>';
    }
    echo '</nav><nav class="cb-content-language-tabs" aria-label="' . esc_attr__('Ngôn ngữ nội dung', 'cb-company-core') . '">';
    foreach (cb_languages() as $code => $config) {
        echo '<a class="' . esc_attr($code === $language ? 'is-active' : '') . '" href="' . esc_url(cb_content_module_url($role, $code, $role === 'other' ? $post_id : 0)) . '">' . esc_html($config['native']) . '</a>';
    }
    echo '</nav>';

    if ($role === 'other') {
        cb_render_other_page_picker($language, $post_id);
    }
    if (!$post || $post->post_type !== 'page') {
        echo '<div class="notice notice-warning inline"><p>' . esc_html__('Chưa gán Page cho lựa chọn này. Hãy gán tại Trang đặc biệt hoặc chọn một Page khác.', 'cb-company-core') . '</p></div></div>';
        cb_admin_shell_end();
        return;
    }
    cb_render_content_page_editor($post, $role, $language);
    echo '</div>';
    cb_admin_shell_end();
}

function cb_render_other_page_picker($language, $selected_id)
{
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => ['publish', 'draft', 'private'],
        'posts_per_page' => 50,
        'orderby' => 'modified',
        'order' => 'DESC',
        'meta_query' => [['key' => '_cb_language', 'value' => $language]],
    ]);
    echo '<form class="cb-other-page-picker" method="get"><input type="hidden" name="page" value="cb-company-content"><input type="hidden" name="content_role" value="other"><input type="hidden" name="language" value="' . esc_attr($language) . '"><label>' . esc_html__('Chọn Page', 'cb-company-core') . '<select name="post_id"><option value="0">' . esc_html__('Chọn một Page', 'cb-company-core') . '</option>';
    foreach ($pages as $page) {
        echo '<option value="' . esc_attr((string) $page->ID) . '" ' . selected($selected_id, $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
    }
    echo '</select></label><button class="button" type="submit">' . esc_html__('Mở nội dung', 'cb-company-core') . '</button></form>';
}

function cb_render_content_page_editor($post, $role, $language)
{
    $preview_url = get_preview_post_link($post, ['cb_content_preview' => wp_create_nonce('cb_preview_page_' . $post->ID)]);
    $revisions = get_post_meta($post->ID, '_cb_page_builder_revisions', true);
    $revisions = is_array($revisions) ? $revisions : [];
    echo '<form class="cb-content-page-form" data-cb-content-form data-post-id="' . esc_attr((string) $post->ID) . '" data-role="' . esc_attr($role) . '" data-language="' . esc_attr($language) . '" novalidate>';
    echo '<div class="cb-content-toolbar"><div><h2>' . esc_html($post->post_title) . ' <small>[' . esc_html(strtoupper($language)) . ']</small></h2><span class="cb-content-save-status is-saved" aria-live="polite">' . esc_html__('Đã lưu', 'cb-company-core') . '</span></div><div class="cb-content-toolbar-actions">';
    echo '<select class="cb-content-revision"><option value="">' . esc_html__('Khôi phục revision', 'cb-company-core') . '</option>';
    foreach ($revisions as $revision) {
        echo '<option value="' . esc_attr($revision['id'] ?? '') . '">' . esc_html(($revision['time'] ?? '') . ' · ' . get_the_author_meta('display_name', absint($revision['user_id'] ?? 0))) . '</option>';
    }
    echo '</select><button type="button" class="button cb-restore-content-revision" disabled>' . esc_html__('Khôi phục', 'cb-company-core') . '</button>';
    echo '<a class="button" href="' . esc_url($preview_url) . '" target="_blank" rel="noopener"><span class="dashicons dashicons-visibility" aria-hidden="true"></span> ' . esc_html__('Xem trước trang', 'cb-company-core') . '</a>';
    echo '<a class="button" href="' . esc_url(get_edit_post_link($post->ID)) . '"><span class="dashicons dashicons-edit" aria-hidden="true"></span> ' . esc_html__('Mở trình chỉnh sửa WordPress', 'cb-company-core') . '</a>';
    echo '<button type="submit" class="button button-primary cb-save-content"><span class="dashicons dashicons-saved" aria-hidden="true"></span> ' . esc_html__('Lưu thay đổi', 'cb-company-core') . '</button></div></div>';
    cb_render_builder_sync_tools($post);
    echo '<div class="cb-content-editor-panel"><h2>' . esc_html__('Sections của trang', 'cb-company-core') . '</h2>';
    cb_render_page_builder_editor($post, ['show_sync_tools' => false]);
    echo '</div><div class="cb-content-editor-panel"><h2>' . esc_html__('Tùy chỉnh giao diện trang', 'cb-company-core') . '</h2>';
    cb_render_page_ui_meta_box($post);
    echo '</div></form>';
}
