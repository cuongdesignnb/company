<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_register_post_types()
{
    $types = [
        'product' => ['Products', 'Product', 'dashicons-products'],
        'factory_showcase' => ['Factory Showcase', 'Factory Item', 'dashicons-building'],
        'case_study' => ['Case Studies', 'Case Study', 'dashicons-portfolio'],
        'video' => ['Videos', 'Video', 'dashicons-video-alt3'],
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

    register_post_type('inquiry', [
        'labels' => ['name' => 'Inquiries', 'singular_name' => 'Inquiry'],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-email-alt2',
        'supports' => ['title'],
        'capability_type' => 'post',
    ]);
}
