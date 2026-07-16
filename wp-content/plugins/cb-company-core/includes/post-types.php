<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_register_post_types()
{
    $types = [
        'product' => [__('Sản phẩm', 'cb-company-core'), __('Sản phẩm', 'cb-company-core'), 'dashicons-products'],
        'factory_showcase' => [__('Nhà máy', 'cb-company-core'), __('Hạng mục nhà máy', 'cb-company-core'), 'dashicons-building'],
        'case_study' => [__('Dự án', 'cb-company-core'), __('Dự án', 'cb-company-core'), 'dashicons-portfolio'],
        'video' => [__('Video', 'cb-company-core'), __('Video', 'cb-company-core'), 'dashicons-video-alt3'],
    ];

    foreach ($types as $type => $data) {
        register_post_type($type, [
            'labels' => ['name' => $data[0], 'singular_name' => $data[1]],
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => $data[2],
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'],
            'has_archive' => true,
            'rewrite' => ['slug' => $type === 'product' ? 'product' : str_replace('_', '-', $type), 'with_front' => false],
        ]);
    }

    register_post_type('certificate', [
        'labels' => [
            'name' => __('Chứng nhận', 'cb-company-core'),
            'singular_name' => __('Chứng nhận', 'cb-company-core'),
            'add_new_item' => __('Thêm chứng nhận', 'cb-company-core'),
            'edit_item' => __('Chỉnh sửa chứng nhận', 'cb-company-core'),
            'view_item' => __('Xem chứng nhận', 'cb-company-core'),
            'search_items' => __('Tìm chứng nhận', 'cb-company-core'),
            'not_found' => __('Không tìm thấy chứng nhận.', 'cb-company-core'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-awards',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'],
        'has_archive' => true,
        'rewrite' => false,
    ]);

    register_post_type('inquiry', [
        'labels' => ['name' => __('Yêu cầu báo giá', 'cb-company-core'), 'singular_name' => __('Yêu cầu', 'cb-company-core')],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-email-alt2',
        'supports' => ['title'],
        'capability_type' => 'post',
    ]);
}
