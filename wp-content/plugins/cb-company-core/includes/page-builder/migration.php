<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_core_maybe_migrate()
{
    if (version_compare((string) get_option('cb_core_db_version', '0'), CB_CORE_VERSION, '<')) {
        cb_core_run_migration_110();
    }
}

function cb_core_run_migration_110()
{
    $legacy_options = cb_get_group_options('cb_theme_options');
    if (!get_option('cb_design_settings')) {
        update_option('cb_design_settings', array_intersect_key($legacy_options, cb_default_design_settings()) + cb_default_design_settings());
    }
    if (!get_option('cb_header_settings')) {
        update_option('cb_header_settings', array_intersect_key($legacy_options, cb_default_header_settings()) + cb_default_header_settings());
    }
    if (!get_option('cb_footer_settings')) {
        $footer = array_intersect_key($legacy_options, cb_default_footer_settings()) + cb_default_footer_settings();
        if (isset($footer['social_links']) && !is_array($footer['social_links'])) {
            $footer['social_links'] = cb_legacy_lines_to_repeater($footer['social_links']);
        }
        update_option('cb_footer_settings', $footer);
    }
    if (!get_option('cb_template_settings')) {
        update_option('cb_template_settings', cb_default_template_settings());
    }

    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $front_id = absint(get_option('page_on_front'));
    if (!$front_id) {
        $front_id = cb_find_or_create_special_page('Home', 'home', 'en');
        if ($front_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $front_id);
        }
    }
    $special['en']['home'] = absint($special['en']['home'] ?? $front_id) ?: $front_id;
    $special['zh']['home'] = absint($special['zh']['home'] ?? 0) ?: cb_find_or_create_special_page('首页', 'home-zh', 'zh');
    foreach (['en' => ['about' => ['About Us', 'about-us'], 'contact' => ['Contact Us', 'contact-us']], 'zh' => ['about' => ['关于我们', 'about-zh'], 'contact' => ['联系我们', 'contact-zh']]] as $lang => $pages) {
        foreach ($pages as $role => $page) {
            $special[$lang][$role] = absint($special[$lang][$role] ?? 0) ?: cb_find_or_create_special_page($page[0], $page[1], $lang);
        }
    }
    update_option('cb_special_pages', $special);

    $legacy_sections = get_option('cb_homepage_sections', []);
    if (is_array($legacy_sections)) {
        if (get_option('cb_homepage_sections_backup_110', null) === null) {
            update_option('cb_homepage_sections_backup_110', $legacy_sections, false);
        }
        $home_id = absint($special['en']['home'] ?? 0);
        if ($home_id && !get_post_meta($home_id, '_cb_page_sections', true)) {
            update_post_meta($home_id, '_cb_page_sections', cb_sanitize_page_sections(cb_repair_known_mojibake($legacy_sections)));
            update_post_meta($home_id, '_cb_page_render_mode', 'builder');
        }
    }
    $zh_home_id = absint($special['zh']['home'] ?? 0);
    if ($zh_home_id && !get_post_meta($zh_home_id, '_cb_page_sections', true)) {
        update_post_meta($zh_home_id, '_cb_page_sections', cb_sanitize_page_sections(cb_default_chinese_home_sections()));
        update_post_meta($zh_home_id, '_cb_page_render_mode', 'builder');
    }
    update_option('cb_string_translations', cb_repair_frontend_translations(get_option('cb_string_translations', [])));
    update_option('cb_core_db_version', '1.1.0');
}

function cb_find_or_create_special_page($title, $slug, $lang)
{
    $page = get_page_by_path($slug, OBJECT, 'page');
    if (!$page) {
        $id = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug]);
        if (is_wp_error($id)) {
            return 0;
        }
    } else {
        $id = $page->ID;
        if (cb_text_has_mojibake($page->post_title)) {
            wp_update_post(['ID' => $id, 'post_title' => $title]);
        }
    }
    update_post_meta($id, '_cb_language', $lang);
    return absint($id);
}

function cb_default_chinese_home_sections()
{
    return [
        [
            'enable' => 1,
            'type' => 'hero_slider',
            'layout_style' => 'full_width',
            'title' => '专业厨房电器制造商',
            'subtitle' => '支持 OEM/ODM 定制服务',
            'description' => '严格的质量控制和准时交付',
            'items' => [
                [
                    'title' => '专业厨房电器制造商',
                    'description' => '支持 OEM/ODM 定制服务',
                    'image_id' => 0,
                    'image_url' => '',
                    'url' => '/zh/contact-zh/',
                ],
            ],
        ],
        [
            'enable' => 1,
            'type' => 'company_intro',
            'layout_style' => 'split',
            'title' => '质量驱动，创新引领',
            'description' => '我们为全球品牌提供研发、制造和出口服务。',
        ],
        [
            'enable' => 1,
            'type' => 'featured_products',
            'layout_style' => 'grid',
            'title' => '精选产品',
            'description' => '高品质厨房电器，支持灵活定制。',
            'product_limit' => 6,
        ],
        [
            'enable' => 1,
            'type' => 'inquiry_cta',
            'layout_style' => 'centered',
            'title' => '联系我们获取产品报价',
            'button_text' => '联系我们',
            'button_url' => '/zh/contact-zh/',
        ],
    ];
}

function cb_repair_frontend_translations($stored)
{
    $stored = is_array($stored) ? $stored : [];
    foreach (cb_default_string_translations() as $key => $defaults) {
        if (empty($stored[$key]['zh']) || cb_text_has_mojibake($stored[$key]['zh'])) {
            $stored[$key]['zh'] = $defaults['zh'];
        }
        $stored[$key]['en'] = $stored[$key]['en'] ?? $defaults['en'];
    }
    return $stored;
}

function cb_text_has_mojibake($value)
{
    $patterns = array_map(static fn($hex) => pack('H*', $hex), ['c383', 'c382', 'c3a2e282ac', 'c3afc2bbc2bf', 'c3a4c2b8', 'c3a9c2a6', 'c3a5c2a6']);
    foreach ($patterns as $pattern) {
        if (str_contains((string) $value, $pattern)) {
            return true;
        }
    }
    return false;
}

function cb_repair_known_mojibake($value)
{
    if (is_array($value)) {
        return array_map('cb_repair_known_mojibake', $value);
    }
    return is_string($value) ? str_replace(pack('H*', '6dc382c2b2'), 'm²', $value) : $value;
}
