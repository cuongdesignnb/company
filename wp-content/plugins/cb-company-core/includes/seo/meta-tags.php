<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_render_seo_meta()
{
    $id = get_queried_object_id();
    $title = $id ? get_post_meta($id, '_cb_seo_title', true) : '';
    $description = $id ? get_post_meta($id, '_cb_seo_description', true) : '';
    $image = $id ? get_post_meta($id, '_cb_seo_image', true) : '';
    $canonical = $id ? get_post_meta($id, '_cb_canonical', true) : '';
    $noindex = $id ? get_post_meta($id, '_cb_noindex', true) : '0';

    if ($id && is_page() && function_exists('cb_ui_get')) {
        $context = cb_page_ui_context($id);
        $title = cb_ui_get('seo_title', $context, $id, $title);
        $description = cb_ui_get('seo_description', $context, $id, $description);
        $image = cb_ui_get('og_image', $context, $id, $image);
        $canonical = cb_ui_get('canonical', $context, $id, '');
        $noindex = cb_ui_get('noindex', $context, $id, '0');
    }

    if (!$title) {
        $title = wp_get_document_title();
    }
    if (!$description) {
        $seo_settings = cb_get_group_options('cb_seo_settings', cb_default_seo_settings());
        $description = $seo_settings['default_description'] ?: (get_bloginfo('description') ?: cb_get_option('footer_description'));
    } else {
        $seo_settings = cb_get_group_options('cb_seo_settings', cb_default_seo_settings());
    }
    if (!$image && has_post_thumbnail($id)) {
        $image = get_the_post_thumbnail_url($id, 'large');
    }
    if (!$image) {
        $image = $seo_settings['default_og_image'] ?? '';
    }

    if (!$canonical && $id && is_page() && cb_page_ui_context($id) === 'home') {
        $language = get_post_meta($id, '_cb_language', true) ?: cb_get_current_language();
        $canonical = home_url('/' . $language . '/');
    }
    $canonical = $canonical ?: (is_singular() ? get_permalink($id) : home_url(add_query_arg([])));

    echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($description)) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    if ((string) $noindex === '1') {
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr(wp_strip_all_tags($description)) . '">' . "\n";
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    }
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
}

function cb_filter_document_title($title)
{
    $id = get_queried_object_id();
    if (!$id || !is_page() || !function_exists('cb_ui_get')) {
        return $title;
    }
    $custom = cb_ui_get('seo_title', cb_page_ui_context($id), $id, '');
    return $custom !== '' ? $custom : $title;
}

function cb_breadcrumb()
{
    echo '<nav class="cb-breadcrumb" aria-label="Breadcrumb"><a href="' . esc_url(home_url('/' . cb_get_current_language() . '/')) . '">' . esc_html(cb_t('home')) . '</a>';
    if (is_singular()) {
        echo '<span>/</span><span>' . esc_html(get_the_title()) . '</span>';
    } elseif (is_archive()) {
        echo '<span>/</span><span>' . esc_html(post_type_archive_title('', false) ?: single_term_title('', false)) . '</span>';
    }
    echo '</nav>';
}

function cb_render_schema()
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => cb_get_option('logo_text'),
        'url' => home_url('/'),
        'email' => cb_get_option('contact_email'),
        'telephone' => cb_get_option('contact_phone'),
        'address' => cb_get_option('company_address'),
    ];

    if (is_singular('product')) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => get_the_title(),
            'description' => wp_strip_all_tags(get_the_excerpt() ?: get_the_content()),
            'brand' => get_post_meta(get_the_ID(), '_cb_brand', true) ?: cb_get_option('logo_text'),
        ];
    } elseif (is_singular('post')) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
        ];
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
