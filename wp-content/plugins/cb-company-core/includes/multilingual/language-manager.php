<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_languages()
{
    return [
        'en' => ['name' => 'English', 'prefix' => 'en'],
        'zh' => ['name' => '中文', 'prefix' => 'zh'],
    ];
}

function cb_get_current_language()
{
    $lang = get_query_var('cb_lang');
    if (!$lang) {
        $path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $first = strtok($path, '/');
        $lang = in_array($first, ['en', 'zh'], true) ? $first : '';
    }
    if (!$lang && is_singular()) {
        $post_lang = get_post_meta(get_queried_object_id(), '_cb_language', true);
        $lang = in_array($post_lang, ['en', 'zh'], true) ? $post_lang : '';
    }
    return in_array($lang, ['en', 'zh'], true) ? $lang : 'en';
}

function cb_register_language_query_var($vars)
{
    $vars[] = 'cb_lang';
    return $vars;
}

function cb_register_language_rewrites()
{
    add_rewrite_rule('^(en|zh)/?$', 'index.php?cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/products/?$', 'index.php?post_type=product&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/product/([^/]+)/?$', 'index.php?product=$matches[2]&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/factory/?$', 'index.php?post_type=factory_showcase&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/cases/?$', 'index.php?post_type=case_study&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/news/?$', 'index.php?post_type=post&cb_lang=$matches[1]', 'top');
}

function cb_redirect_root_to_language()
{
    if (is_admin() || wp_doing_ajax()) {
        return;
    }
    $path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if ($path === '') {
        wp_safe_redirect(home_url('/en/'));
        exit;
    }
}

function cb_filter_main_query_language($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    $type = $query->get('post_type');
    $filterable = ['post', 'page', 'product', 'factory_showcase', 'case_study', 'video'];
    if ($type === '' && ($query->is_home() || $query->is_archive() || $query->is_search())) {
        $type = 'post';
    }
    if (in_array($type, $filterable, true) || (is_array($type) && array_intersect($type, $filterable))) {
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = [
            'relation' => 'OR',
            ['key' => '_cb_language', 'value' => cb_get_current_language()],
            ['key' => '_cb_language', 'compare' => 'NOT EXISTS'],
        ];
        $query->set('meta_query', $meta_query);
    }
}

function cb_get_language_url($lang)
{
    $lang = in_array($lang, ['en', 'zh'], true) ? $lang : 'en';
    if (is_singular()) {
        $translated = cb_get_translated_post_id(get_queried_object_id(), $lang);
        if ($translated) {
            return get_permalink($translated);
        }
    }
    return home_url('/' . $lang . '/');
}

function cb_get_translated_post_id($post_id, $target_lang)
{
    $direct = get_post_meta($post_id, '_cb_translated_post_' . $target_lang, true);
    if ($direct) {
        return absint($direct);
    }
    $group = get_post_meta($post_id, '_cb_translation_group', true);
    if (!$group) {
        return 0;
    }
    $posts = get_posts([
        'post_type' => get_post_type($post_id),
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [
            ['key' => '_cb_translation_group', 'value' => $group],
            ['key' => '_cb_language', 'value' => $target_lang],
        ],
    ]);
    return $posts[0] ?? 0;
}

function cb_language_switcher()
{
    $current = cb_get_current_language();
    echo '<nav class="cb-lang" aria-label="Language switcher">';
    foreach (cb_languages() as $code => $lang) {
        $class = $code === $current ? ' is-active' : '';
        echo '<a class="' . esc_attr($class) . '" href="' . esc_url(cb_get_language_url($code)) . '">' . esc_html($code === 'en' ? 'EN' : '中文') . '</a>';
    }
    echo '</nav>';
}

function cb_render_hreflang()
{
    foreach (array_keys(cb_languages()) as $lang) {
        echo '<link rel="alternate" hreflang="' . esc_attr($lang) . '" href="' . esc_url(cb_get_language_url($lang)) . '">' . "\n";
    }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(cb_get_language_url('en')) . '">' . "\n";
}

function cb_register_string_shortcodes()
{
    add_shortcode('cb_lang_switcher', function () {
        ob_start();
        cb_language_switcher();
        return ob_get_clean();
    });
}
