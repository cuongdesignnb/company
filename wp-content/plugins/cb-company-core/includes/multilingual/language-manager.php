<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_languages()
{
    return [
        'en' => ['name' => 'English', 'native' => 'English', 'prefix' => 'en', 'html_lang' => 'en', 'locale' => 'en_US'],
        'zh' => ['name' => 'Chinese', 'native' => '中文', 'prefix' => 'zh', 'html_lang' => 'zh-CN', 'locale' => 'zh_CN'],
    ];
}

function cb_get_current_language()
{
    $lang = get_query_var('cb_lang');
    if (!$lang) {
        $path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $first = strtok($path, '/');
        $lang = isset(cb_languages()[$first]) ? $first : '';
    }
    if (!$lang && is_singular()) {
        $lang = get_post_meta(get_queried_object_id(), '_cb_language', true);
    }
    return isset(cb_languages()[$lang]) ? $lang : 'en';
}

function cb_register_language_query_var($vars)
{
    $vars[] = 'cb_lang';
    $vars[] = 'cb_special_home';
    return $vars;
}

function cb_register_language_rewrites()
{
    add_rewrite_rule('^(en|zh)/?$', 'index.php?cb_lang=$matches[1]&cb_special_home=1', 'top');
    add_rewrite_rule('^(en|zh)/products/?$', 'index.php?post_type=product&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/product/([^/]+)/?$', 'index.php?product=$matches[2]&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/factory/?$', 'index.php?post_type=factory_showcase&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/factory/([^/]+)/?$', 'index.php?factory_showcase=$matches[2]&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/cases/?$', 'index.php?post_type=case_study&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/videos/?$', 'index.php?post_type=video&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/news/?$', 'index.php?post_type=post&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/certificates/page/([0-9]+)/?$', 'index.php?post_type=certificate&cb_lang=$matches[1]&paged=$matches[2]', 'top');
    add_rewrite_rule('^(en|zh)/certificates/category/([^/]+)/page/([0-9]+)/?$', 'index.php?certificate_category=$matches[2]&cb_lang=$matches[1]&paged=$matches[3]', 'top');
    add_rewrite_rule('^(en|zh)/certificates/category/([^/]+)/?$', 'index.php?certificate_category=$matches[2]&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/certificates/?$', 'index.php?post_type=certificate&cb_lang=$matches[1]', 'top');
    add_rewrite_rule('^(en|zh)/certificate/([^/]+)/?$', 'index.php?certificate=$matches[2]&cb_lang=$matches[1]', 'top');
}

function cb_language_post_type_link($url, $post)
{
    if (!($post instanceof WP_Post) || $post->post_type !== 'factory_showcase') {
        return $url;
    }
    $language = get_post_meta($post->ID, '_cb_language', true);
    $language = isset(cb_languages()[$language]) ? $language : 'en';
    return home_url('/' . $language . '/factory/' . $post->post_name . '/');
}

function cb_resolve_special_home_request($vars)
{
    if (empty($vars['cb_special_home'])) {
        return $vars;
    }
    $lang = isset(cb_languages()[$vars['cb_lang'] ?? '']) ? $vars['cb_lang'] : 'en';
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $page_id = absint($special[$lang]['home'] ?? 0);
    if ($page_id) {
        unset($vars['cb_special_home']);
        $vars['page_id'] = $page_id;
    }
    return $vars;
}

function cb_disable_canonical_for_special_home($redirect_url, $requested_url)
{
    $path = trim((string) wp_parse_url($requested_url, PHP_URL_PATH), '/');
    if (in_array($path, ['en', 'zh'], true)) {
        return false;
    }
    return $redirect_url;
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
    if (is_admin() || !$query->is_main_query() || $query->get('page_id')) {
        return;
    }
    $type = $query->get('post_type');
    $filterable = ['post', 'page', 'product', 'factory_showcase', 'case_study', 'video', 'certificate'];
    if ($type === '' && $query->is_search()) {
        $type = ['post', 'product', 'factory_showcase', 'case_study', 'video', 'certificate'];
        $query->set('post_type', $type);
    } elseif ($type === '' && ($query->is_home() || $query->is_archive())) {
        $type = 'post';
    }
    if (in_array($type, $filterable, true) || (is_array($type) && array_intersect($type, $filterable))) {
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = ['relation' => 'OR', ['key' => '_cb_language', 'value' => cb_get_current_language()], ['key' => '_cb_language', 'compare' => 'NOT EXISTS']];
        $query->set('meta_query', $meta_query);
    }
}

function cb_get_language_url($lang)
{
    $lang = isset(cb_languages()[$lang]) ? $lang : 'en';
    if (is_post_type_archive('certificate')) {
        return cb_certificate_archive_url($lang);
    }
    if (is_tax('certificate_category')) {
        $term = get_queried_object();
        $group = $term instanceof WP_Term ? get_term_meta($term->term_id, '_cb_translation_group', true) : '';
        if ($group) {
            $terms = get_terms([
                'taxonomy' => 'certificate_category',
                'hide_empty' => false,
                'number' => 1,
                'meta_query' => [
                    ['key' => '_cb_translation_group', 'value' => $group],
                    ['key' => '_cb_language', 'value' => $lang],
                ],
            ]);
            if (!is_wp_error($terms) && !empty($terms[0])) {
                return cb_certificate_archive_url($lang, $terms[0]);
            }
        }
        return cb_certificate_archive_url($lang);
    }
    if (is_singular()) {
        $translated = cb_get_translated_post_id(get_queried_object_id(), $lang);
        if ($translated) {
            $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
            if ((int) ($special[$lang]['home'] ?? 0) === (int) $translated) {
                return home_url('/' . $lang . '/');
            }
            return get_permalink($translated);
        }
    }
    return home_url('/' . $lang . '/');
}

function cb_get_translated_post_id($post_id, $target_lang)
{
    $direct = get_post_meta($post_id, '_cb_translated_post_' . $target_lang, true);
    if ($direct) return absint($direct);
    $group = get_post_meta($post_id, '_cb_translation_group', true);
    if (!$group) return 0;
    $posts = get_posts(['post_type' => get_post_type($post_id), 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => [['key' => '_cb_translation_group', 'value' => $group], ['key' => '_cb_language', 'value' => $target_lang]]]);
    return $posts[0] ?? 0;
}

function cb_language_switcher()
{
    $current = cb_get_current_language();
    echo '<nav class="cb-lang" aria-label="' . esc_attr__('Language switcher', 'cb-company-core') . '">';
    foreach (cb_languages() as $code => $lang) {
        echo '<a class="' . esc_attr($code === $current ? 'is-active' : '') . '" href="' . esc_url(cb_get_language_url($code)) . '">' . esc_html($code === 'en' ? 'EN' : $lang['native']) . '</a>';
    }
    echo '</nav>';
}

function cb_html_language_attributes()
{
    $config = cb_languages()[cb_get_current_language()] ?? cb_languages()['en'];
    printf('lang="%s" dir="ltr"', esc_attr($config['html_lang']));
}

function cb_render_hreflang()
{
    foreach (cb_languages() as $lang => $config) {
        echo '<link rel="alternate" hreflang="' . esc_attr($config['html_lang']) . '" href="' . esc_url(cb_get_language_url($lang)) . '">' . "\n";
    }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(cb_get_language_url('en')) . '">' . "\n";
}

function cb_register_string_shortcodes()
{
    add_shortcode('cb_lang_switcher', static function () {
        ob_start(); cb_language_switcher(); return ob_get_clean();
    });
}
