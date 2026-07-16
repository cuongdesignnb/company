<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_register_taxonomies()
{
    $taxes = [
        'product_category' => [__('Danh mục sản phẩm', 'cb-company-core'), __('Danh mục sản phẩm', 'cb-company-core'), ['product']],
        'product_tag' => [__('Thẻ sản phẩm', 'cb-company-core'), __('Thẻ sản phẩm', 'cb-company-core'), ['product']],
        'factory_category' => [__('Danh mục nhà máy', 'cb-company-core'), __('Danh mục nhà máy', 'cb-company-core'), ['factory_showcase']],
        'case_category' => [__('Danh mục dự án', 'cb-company-core'), __('Danh mục dự án', 'cb-company-core'), ['case_study']],
        'video_category' => [__('Danh mục video', 'cb-company-core'), __('Danh mục video', 'cb-company-core'), ['video']],
        'certificate_category' => [__('Nhóm chứng nhận', 'cb-company-core'), __('Nhóm chứng nhận', 'cb-company-core'), ['certificate']],
    ];

    foreach ($taxes as $tax => $data) {
        register_taxonomy($tax, $data[2], [
            'labels' => ['name' => $data[0], 'singular_name' => $data[1]],
            'public' => true,
            'hierarchical' => $tax !== 'product_tag',
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => $tax === 'certificate_category' ? false : ['slug' => str_replace('_', '-', $tax), 'with_front' => false],
        ]);
    }
}
