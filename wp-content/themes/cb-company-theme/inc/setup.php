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

function cb_theme_primary_submenu_toggle($item_output, $item, $depth, $args)
{
    $location = isset($args->theme_location) ? (string) $args->theme_location : '';
    if ($depth !== 0 || !str_starts_with($location, 'primary_') || !in_array('menu-item-has-children', (array) $item->classes, true)) {
        return $item_output;
    }
    $is_zh = str_ends_with($location, '_zh');
    $label = sprintf(
        $is_zh ? '展开%s的子菜单' : 'Open submenu for %s',
        wp_strip_all_tags($item->title)
    );
    return $item_output
        . '<button class="cb-submenu-toggle" type="button" aria-expanded="false" aria-label="' . esc_attr($label) . '">'
        . '<svg aria-hidden="true" viewBox="0 0 20 20"><path d="m5 7.5 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        . '</button>';
}
add_filter('walker_nav_menu_start_el', 'cb_theme_primary_submenu_toggle', 10, 4);
