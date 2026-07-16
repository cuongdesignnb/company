<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_certificate_archive_url($language = '', $term = '')
{
    $language = isset(cb_languages()[$language]) ? $language : cb_get_current_language();
    $path = '/' . $language . '/certificates/';
    if ($term) {
        $slug = $term instanceof WP_Term ? $term->slug : sanitize_title($term);
        $path .= 'category/' . $slug . '/';
    }
    return home_url($path);
}

function cb_certificate_use_block_editor($use_block_editor, $post_type)
{
    return $post_type === 'certificate' ? false : $use_block_editor;
}

function cb_certificate_post_type_link($url, $post)
{
    if (!$post instanceof WP_Post || $post->post_type !== 'certificate') {
        return $url;
    }
    $language = get_post_meta($post->ID, '_cb_language', true);
    $language = isset(cb_languages()[$language]) ? $language : 'en';
    return home_url('/' . $language . '/certificate/' . $post->post_name . '/');
}

function cb_certificate_term_link($url, $term, $taxonomy)
{
    if ($taxonomy !== 'certificate_category') {
        return $url;
    }
    return cb_certificate_archive_url(cb_get_current_language(), $term);
}

function cb_certificate_is_expired($post_id)
{
    $expiry = get_post_meta($post_id, '_cb_expiry_date', true);
    return $expiry && $expiry < current_time('Y-m-d');
}

function cb_certificate_is_expiring_soon($post_id, $days = 90)
{
    $expiry = get_post_meta($post_id, '_cb_expiry_date', true);
    if (!$expiry || cb_certificate_is_expired($post_id)) {
        return false;
    }
    return strtotime($expiry) <= strtotime('+' . absint($days) . ' days', current_time('timestamp'));
}

function cb_certificate_required_errors($post_id)
{
    $errors = [];
    if (!trim((string) get_the_title($post_id))) {
        $errors[] = __('tên chứng nhận', 'cb-company-core');
    }
    if (!trim((string) get_post_meta($post_id, '_cb_issuer', true))) {
        $errors[] = __('đơn vị cấp', 'cb-company-core');
    }
    if (!trim((string) get_post_meta($post_id, '_cb_standard', true))) {
        $errors[] = __('tiêu chuẩn', 'cb-company-core');
    }
    $has_document = has_post_thumbnail($post_id)
        || absint(get_post_meta($post_id, '_cb_pdf_id', true))
        || (string) get_post_meta($post_id, '_cb_pdf_url', true);
    if (!$has_document) {
        $errors[] = __('ảnh tài liệu hoặc PDF', 'cb-company-core');
    }
    return $errors;
}

function cb_validate_certificate_publish($post_id, $post, $update)
{
    static $validating = false;
    if ($validating || !$post instanceof WP_Post || $post->post_type !== 'certificate' || $post->post_status !== 'publish' || wp_is_post_revision($post_id)) {
        return;
    }
    $errors = cb_certificate_required_errors($post_id);
    if (!$errors) {
        return;
    }
    $validating = true;
    wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
    update_post_meta($post_id, '_cb_needs_content_review', '1');
    set_transient('cb_certificate_publish_error_' . get_current_user_id(), $errors, MINUTE_IN_SECONDS);
    $validating = false;
}

function cb_certificate_admin_notices()
{
    $key = 'cb_certificate_publish_error_' . get_current_user_id();
    $errors = get_transient($key);
    if (!$errors) {
        return;
    }
    delete_transient($key);
    echo '<div class="notice notice-error is-dismissible"><p>';
    echo esc_html(sprintf(__('Chứng nhận đã được chuyển về bản nháp. Vui lòng bổ sung: %s.', 'cb-company-core'), implode(', ', $errors)));
    echo '</p></div>';
}

function cb_render_certificate_status_box($post)
{
    $errors = cb_certificate_required_errors($post->ID);
    if ($errors) {
        echo '<p class="cb-status-bad"><strong>' . esc_html__('Chưa thể xuất bản', 'cb-company-core') . '</strong></p><ul class="ul-disc">';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="cb-status-good"><strong>' . esc_html__('Đủ dữ liệu bắt buộc', 'cb-company-core') . '</strong></p>';
    }
    if (get_post_meta($post->ID, '_cb_needs_content_review', true) === '1') {
        echo '<p class="cb-status-warning">' . esc_html__('Nội dung đang được đánh dấu cần xác minh.', 'cb-company-core') . '</p>';
    }
    if (cb_certificate_is_expired($post->ID)) {
        echo '<p class="cb-status-bad">' . esc_html__('Chứng nhận đã hết hiệu lực và sẽ không xuất hiện trên About.', 'cb-company-core') . '</p>';
    } elseif (cb_certificate_is_expiring_soon($post->ID)) {
        echo '<p class="cb-status-warning">' . esc_html__('Chứng nhận sẽ hết hạn trong 90 ngày.', 'cb-company-core') . '</p>';
    }
}

function cb_certificate_admin_columns($columns)
{
    return [
        'cb_thumbnail' => __('Tài liệu', 'cb-company-core'),
        'title' => __('Tên chứng nhận', 'cb-company-core'),
        'cb_standard' => __('Tiêu chuẩn', 'cb-company-core'),
        'cb_issuer' => __('Đơn vị cấp', 'cb-company-core'),
        'cb_language' => __('Ngôn ngữ', 'cb-company-core'),
        'cb_expiry' => __('Hiệu lực', 'cb-company-core'),
        'cb_featured' => __('Nổi bật', 'cb-company-core'),
        'date' => __('Ngày đăng', 'cb-company-core'),
    ];
}

function cb_certificate_admin_column_content($column, $post_id)
{
    if ($column === 'cb_thumbnail') {
        echo get_the_post_thumbnail($post_id, [52, 70]) ?: '<span class="dashicons dashicons-media-document" aria-hidden="true"></span>';
    } elseif ($column === 'cb_standard') {
        echo esc_html(get_post_meta($post_id, '_cb_standard', true));
    } elseif ($column === 'cb_issuer') {
        echo esc_html(get_post_meta($post_id, '_cb_issuer', true));
    } elseif ($column === 'cb_language') {
        echo esc_html(get_post_meta($post_id, '_cb_language', true) === 'zh' ? '中文' : 'English');
    } elseif ($column === 'cb_featured') {
        echo get_post_meta($post_id, '_cb_featured', true) === '1' ? '<span class="dashicons dashicons-star-filled" aria-label="' . esc_attr__('Nổi bật', 'cb-company-core') . '"></span>' : '<span aria-hidden="true">-</span>';
    } elseif ($column === 'cb_expiry') {
        $expiry = get_post_meta($post_id, '_cb_expiry_date', true);
        if (cb_certificate_is_expired($post_id)) {
            echo '<strong class="cb-status-bad">' . esc_html__('Hết hiệu lực', 'cb-company-core') . '</strong>';
        } elseif (cb_certificate_is_expiring_soon($post_id)) {
            echo '<strong class="cb-status-warning">' . esc_html__('Sắp hết hạn', 'cb-company-core') . '</strong>';
        } elseif ($expiry) {
            echo '<span class="cb-status-good">' . esc_html__('Còn hiệu lực', 'cb-company-core') . '</span>';
        } else {
            echo '<span>' . esc_html__('Không thời hạn', 'cb-company-core') . '</span>';
        }
        if ($expiry) {
            echo '<br><small>' . esc_html($expiry) . '</small>';
        }
        if (get_post_meta($post_id, '_cb_needs_content_review', true) === '1') {
            echo '<br><strong class="cb-status-warning">' . esc_html__('Cần xác minh', 'cb-company-core') . '</strong>';
        }
    }
}

function cb_certificate_query_settings($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->get('post_type') === 'certificate' || $query->is_tax('certificate_category')) {
        $query->set('posts_per_page', 12);
        $query->set('orderby', ['menu_order' => 'ASC', 'date' => 'DESC']);
    }
}
add_action('pre_get_posts', 'cb_certificate_query_settings', 20);
