<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_theme_lang()
{
    return function_exists('cb_get_current_language') ? cb_get_current_language() : 'en';
}

function cb_theme_t($key)
{
    return function_exists('cb_t') ? cb_t($key) : ucwords(str_replace('_', ' ', $key));
}

function cb_theme_option($key, $default = '')
{
    return function_exists('cb_get_option') ? cb_get_option($key, $default) : $default;
}

function cb_theme_post_url($post_id)
{
    $url = get_permalink($post_id);
    $lang = get_post_meta($post_id, '_cb_language', true) ?: cb_theme_lang();
    if (get_post_type($post_id) === 'product') {
        return home_url('/' . $lang . '/product/' . get_post_field('post_name', $post_id) . '/');
    }
    return $url;
}

function cb_theme_image($url, $alt = '', $class = '')
{
    if (!$url) {
        $url = get_template_directory_uri() . '/assets/images/aurelia-reference.png';
    }
    return '<img class="' . esc_attr($class) . '" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="lazy">';
}

function cb_theme_items($section)
{
    return function_exists('cb_parse_lines') ? cb_parse_lines($section['items'] ?? '') : [];
}

function cb_theme_section_header($section)
{
    if (!empty($section['eyebrow'])) {
        echo '<p class="cb-eyebrow">' . esc_html($section['eyebrow']) . '</p>';
    }
    if (!empty($section['title'])) {
        echo '<h2>' . esc_html($section['title']) . '</h2>';
    }
    if (!empty($section['description'])) {
        echo '<p class="cb-section-desc">' . esc_html($section['description']) . '</p>';
    }
}

function cb_render_page_sections($post_id = 0)
{
    $sections = get_option('cb_homepage_sections');
    if (!$sections && function_exists('cb_default_homepage_sections')) {
        $sections = cb_default_homepage_sections();
    }
    foreach ((array) $sections as $section) {
        if (($section['enable'] ?? '1') !== '1') {
            continue;
        }
        $type = sanitize_key($section['type'] ?? '');
        $file = locate_template('template-parts/sections/' . str_replace('_', '-', $type) . '.php');
        if ($file) {
            include $file;
        }
    }
}
