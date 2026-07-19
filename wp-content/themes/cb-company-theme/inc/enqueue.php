<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_theme_enqueue_assets()
{
    wp_enqueue_style('cb-company-theme', get_template_directory_uri() . '/assets/css/main.css', [], '1.6.0');
    wp_enqueue_script('cb-company-theme', get_template_directory_uri() . '/assets/js/main.js', [], '1.6.0', true);
    if (cb_theme_has_multiple_hero_slides()) {
        wp_enqueue_script('cb-company-hero-slider', get_template_directory_uri() . '/assets/js/hero-slider.js', [], '1.6.0', true);
    }
}

function cb_theme_has_multiple_hero_slides()
{
    $post_id = get_queried_object_id();
    if (!$post_id || !function_exists('cb_get_page_sections')) {
        return false;
    }
    foreach (cb_get_page_sections($post_id) as $section) {
        if (($section['type'] ?? '') !== 'hero_slider' || ($section['enable'] ?? '1') !== '1') {
            continue;
        }
        $enabled = array_filter((array) ($section['slides'] ?? []), static fn($slide) => ($slide['enable'] ?? '1') === '1');
        if (count($enabled) > 1) {
            return true;
        }
    }
    return false;
}
