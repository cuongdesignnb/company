<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_theme_enqueue_assets()
{
    wp_enqueue_style('cb-company-theme', get_template_directory_uri() . '/assets/css/main.css', [], '1.0.0');
    wp_enqueue_script('cb-company-theme', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true);

    $css = ':root{' .
        '--cb-primary:' . esc_html(function_exists('cb_get_option') ? cb_get_option('primary_color') : '#ef3f45') . ';' .
        '--cb-secondary:' . esc_html(function_exists('cb_get_option') ? cb_get_option('secondary_color') : '#16191f') . ';' .
        '--cb-accent:' . esc_html(function_exists('cb_get_option') ? cb_get_option('accent_color') : '#f8dfe1') . ';' .
        '--cb-heading:' . esc_html(function_exists('cb_get_option') ? cb_get_option('heading_color') : '#17191f') . ';' .
        '--cb-body:' . esc_html(function_exists('cb_get_option') ? cb_get_option('body_color') : '#5d6470') . ';' .
        '--cb-bg:' . esc_html(function_exists('cb_get_option') ? cb_get_option('background_color') : '#fff') . ';' .
        '--cb-radius:' . esc_html(function_exists('cb_get_option') ? cb_get_option('radius') : '8px') . ';' .
        '--cb-container:' . esc_html(function_exists('cb_get_option') ? cb_get_option('container_width') : '1220px') . ';' .
    '}';
    wp_add_inline_style('cb-company-theme', $css);
}
