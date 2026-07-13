<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_theme_setup()
{
    load_theme_textdomain('cb-company-theme', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus([
        'primary_en' => 'Primary Menu English',
        'primary_zh' => 'Primary Menu Chinese',
        'footer_en' => 'Footer Menu English',
        'footer_zh' => 'Footer Menu Chinese',
        'mobile_en' => 'Mobile Menu English',
        'mobile_zh' => 'Mobile Menu Chinese',
    ]);
}
