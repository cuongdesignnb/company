<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_register_taxonomies()
{
    $taxes = [
        'product_category' => ['Product Categories', 'Product Category', ['product']],
        'product_tag' => ['Product Tags', 'Product Tag', ['product']],
        'factory_category' => ['Factory Categories', 'Factory Category', ['factory_showcase']],
        'case_category' => ['Case Categories', 'Case Category', ['case_study']],
        'video_category' => ['Video Categories', 'Video Category', ['video']],
    ];

    foreach ($taxes as $tax => $data) {
        register_taxonomy($tax, $data[2], [
            'labels' => ['name' => $data[0], 'singular_name' => $data[1]],
            'public' => true,
            'hierarchical' => $tax !== 'product_tag',
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => str_replace('_', '-', $tax), 'with_front' => false],
        ]);
    }
}
