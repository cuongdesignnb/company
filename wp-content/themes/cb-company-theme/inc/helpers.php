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

function cb_theme_option_enabled($key, $default = '1')
{
    return cb_theme_option($key, $default) === '1';
}

function cb_theme_logo($context = 'header')
{
    $is_mobile = $context === 'mobile';
    $logo_url = $context === 'footer' ? cb_theme_option('footer_logo_url') : ($is_mobile ? cb_theme_option('mobile_logo_url') : cb_theme_option('logo_url'));
    if (!$logo_url) {
        $logo_url = cb_theme_option('logo_url');
    }
    $home = home_url('/' . cb_theme_lang() . '/');
    echo '<a class="cb-logo" href="' . esc_url($home) . '" aria-label="' . esc_attr(get_bloginfo('name')) . '">';
    if ($logo_url) {
        echo '<img class="cb-logo-image" src="' . esc_url($logo_url) . '" alt="' . esc_attr(cb_theme_option('logo_text', get_bloginfo('name'))) . '">';
    } else {
        $mark = cb_theme_option('brand_mark_text', '');
        if ($mark) {
            echo '<span class="cb-logo-mark">' . esc_html($mark) . '</span>';
        }
    }
    if (cb_theme_option_enabled('show_logo_text')) {
        echo '<span class="cb-logo-text"><strong>' . esc_html(cb_theme_option('logo_text', get_bloginfo('name'))) . '</strong><small>' . esc_html(cb_theme_option('logo_subtext', '')) . '</small></span>';
    }
    echo '</a>';
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

function cb_theme_image($url, $alt = '', $class = '', $width = 640, $height = 420)
{
    if (!$url) {
        return '';
    }
    return '<img class="' . esc_attr($class) . '" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="lazy" width="' . esc_attr((string) $width) . '" height="' . esc_attr((string) $height) . '">';
}

function cb_theme_items($section)
{
    if (!empty($section['items']) && is_array($section['items'])) {
        return array_map(static function ($item) {
            $item = (array) $item;
            $item['label'] = $item['label'] ?? ($item['title'] ?? '');
            $item['value'] = $item['value'] ?? ($item['description'] ?? '');
            $item['image'] = $item['image'] ?? ($item['image_url'] ?? '');
            $item['url'] = $item['url'] ?? ($item['link'] ?? '');
            return $item;
        }, $section['items']);
    }
    return function_exists('cb_parse_lines') ? cb_parse_lines($section['items'] ?? '') : [];
}

function cb_theme_product_terms($limit = 0)
{
    $terms = get_terms([
        'taxonomy' => 'product_category',
        'hide_empty' => false,
        'number' => absint($limit),
        'meta_query' => [['key' => '_cb_language', 'value' => cb_theme_lang()]],
    ]);
    return is_wp_error($terms) ? [] : $terms;
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
    $post_id = $post_id ?: get_queried_object_id();
    $sections = function_exists('cb_get_page_sections') ? cb_get_page_sections($post_id) : [];
    foreach ((array) $sections as $section) {
        if (function_exists('cb_normalize_homepage_section')) {
            $section = cb_normalize_homepage_section($section);
        }
        if (($section['enable'] ?? '1') !== '1') {
            continue;
        }
        cb_render_page_section($section, $post_id);
    }
}

function cb_render_page_section($section, $post_id = 0)
{
    $type = sanitize_key($section['type'] ?? '');
    $file = locate_template('template-parts/sections/' . str_replace('_', '-', $type) . '.php');
    if ($file) {
        include $file;
    }
}

function cb_theme_page_ui($key, $default = '')
{
    $post_id = get_queried_object_id();
    $context = function_exists('cb_page_ui_context') ? cb_page_ui_context($post_id) : 'standard_page';
    return function_exists('cb_ui_get') ? cb_ui_get($key, $context, $post_id, $default) : $default;
}

function cb_theme_page_ui_enabled($key, $default = '0')
{
    return (string) cb_theme_page_ui($key, $default) === '1';
}

function cb_theme_page_render_mode($post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    return get_post_meta($post_id, '_cb_page_render_mode', true) ?: 'editor';
}

function cb_theme_is_special_page($role, $post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    if (!$post_id || !function_exists('cb_get_group_options')) {
        return false;
    }
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    foreach ((array) $special as $pages) {
        if ((int) ($pages[$role] ?? 0) === (int) $post_id) {
            return true;
        }
    }
    return false;
}

function cb_theme_certificate_archive_url($term = '')
{
    if (function_exists('cb_certificate_archive_url')) {
        return cb_certificate_archive_url(cb_theme_lang(), $term);
    }
    return home_url('/' . cb_theme_lang() . '/certificates/');
}

function cb_theme_about_sidebar_links($labels)
{
    $links = [
        'overview' => $labels['overview'],
        'milestones' => $labels['milestones'],
        'factory' => $labels['factory'],
        'certificates' => $labels['certificates'],
        'quality' => $labels['quality'],
        'services' => $labels['services'],
        'contact' => $labels['contact'],
    ];
    foreach ($links as $anchor => $label) {
        echo '<a href="#' . esc_attr($anchor) . '">' . esc_html($label) . '</a>';
        if ($anchor === 'certificates') {
            echo '<a class="cb-about-all-certificates" href="' . esc_url(cb_theme_certificate_archive_url()) . '">' . esc_html($labels['all']) . '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>';
        }
    }
}

function cb_theme_page_banner_style($post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    $context = function_exists('cb_page_ui_context') ? cb_page_ui_context($post_id) : 'standard_page';
    $styles = [];
    $image = cb_ui_get('banner_image', $context, $post_id, '');
    $overlay = max(0, min(90, absint(cb_ui_get('banner_overlay', $context, $post_id, '42')))) / 100;
    if ($image) $styles[] = 'background-image:linear-gradient(rgba(19,23,29,' . $overlay . '),rgba(19,23,29,' . $overlay . ')),url(' . esc_url($image) . ')';
    $height = cb_ui_get('banner_height_desktop', $context, $post_id, '');
    if ($height) $styles[] = 'min-height:' . cb_sanitize_css_size($height, '');
    $mobile_height = cb_ui_get('banner_height_mobile', $context, $post_id, '');
    if ($mobile_height) $styles[] = '--cb-page-hero-mobile-height:' . cb_sanitize_css_size($mobile_height, '');
    return $styles ? ' style="' . esc_attr(implode(';', $styles)) . '"' : '';
}

function cb_theme_section_attrs($section, $type, $extra_class = '')
{
    $classes = [
        'cb-section',
        'cb-section-' . sanitize_html_class(str_replace('_', '-', $type)),
        'cb-layout-' . sanitize_html_class($section['layout_style'] ?? 'default'),
    ];
    if (!empty($section['section_class'])) {
        $classes[] = sanitize_html_class($section['section_class']);
    }
    if ($extra_class) {
        $classes[] = $extra_class;
    }
    if (($section['hide_mobile'] ?? '0') === '1') {
        $classes[] = 'cb-hide-mobile';
    }
    $styles = [];
    if (!empty($section['background_color']) && sanitize_hex_color($section['background_color'])) {
        $styles[] = 'background-color:' . sanitize_hex_color($section['background_color']);
    }
    if (!empty($section['text_color']) && sanitize_hex_color($section['text_color'])) {
        $styles[] = 'color:' . sanitize_hex_color($section['text_color']);
    }
    foreach (['padding_top' => 'padding-top', 'padding_bottom' => 'padding-bottom'] as $key => $property) {
        if (!empty($section[$key]) && function_exists('cb_sanitize_css_size')) {
            $size = cb_sanitize_css_size($section[$key], '');
            if ($size) {
                $styles[] = $property . ':' . $size;
            }
        }
    }
    if (!empty($section['container_width'])) {
        $styles[] = '--cb-section-container:' . cb_sanitize_css_size($section['container_width'], '');
    }
    if (!empty($section['mobile_order'])) {
        $styles[] = '--cb-mobile-order:' . absint($section['mobile_order']);
    }
    if (!empty($section['background_image_url'])) {
        $styles[] = 'background-image:url(' . esc_url($section['background_image_url']) . ')';
        $styles[] = 'background-size:cover';
        $styles[] = 'background-position:center';
    }
    $id = !empty($section['section_id']) ? ' id="' . esc_attr(sanitize_title($section['section_id'])) . '"' : '';
    $style = $styles ? ' style="' . esc_attr(implode(';', $styles)) . '"' : '';
    return $id . ' class="' . esc_attr(implode(' ', array_filter($classes))) . '"' . $style;
}

function cb_theme_button_classes($variant = 'primary')
{
    $variant_class = $variant === 'primary' ? 'cb-btn-primary' : ($variant === 'soft' ? 'cb-btn-soft' : 'cb-btn-outline');
    return implode(' ', array_filter([
        'cb-btn',
        $variant_class,
        'cb-btn-' . sanitize_html_class(cb_theme_option('button_style', 'pill')),
        'cb-hover-' . sanitize_html_class(cb_theme_option('button_hover_effect', 'lift')),
    ]));
}

function cb_theme_card_classes($base, $style_key)
{
    return implode(' ', [
        $base,
        'cb-card-style-' . sanitize_html_class(cb_theme_option($style_key, 'clean')),
        'cb-shadow-' . sanitize_html_class(cb_theme_option('card_shadow', 'soft')),
        'cb-hover-' . sanitize_html_class(cb_theme_option('card_hover_effect', 'lift')),
    ]);
}
