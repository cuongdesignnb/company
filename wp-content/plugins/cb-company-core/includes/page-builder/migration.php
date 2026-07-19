<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_core_maybe_migrate()
{
    $version = (string) get_option('cb_core_db_version', '0');
    if (version_compare($version, '1.1.0', '<')) {
        cb_core_run_migration_110();
        $version = '1.1.0';
    }
    if (version_compare($version, '1.3.0', '<')) {
        cb_core_run_migration_130();
        $version = '1.3.0';
    }
    if (version_compare($version, '1.3.1', '<')) {
        cb_core_run_migration_131();
        $version = '1.3.1';
    }
    if (version_compare($version, '1.4.0', '<')) {
        cb_core_run_migration_140();
        $version = '1.4.0';
    }
    if (version_compare($version, '1.5.0', '<')) {
        cb_core_run_migration_150();
        $version = '1.5.0';
    }
    if (version_compare($version, '1.6.0', '<')) {
        cb_core_run_migration_160();
        $version = '1.6.0';
    }
    if (version_compare($version, '1.7.0', '<')) {
        cb_core_run_migration_170();
    }
}

function cb_core_run_migration_170()
{
    if (get_option('cb_video_module_backup_170', null) === null) {
        update_option('cb_video_module_backup_170', [
            'created_at' => current_time('mysql'),
            'menu_locations' => get_theme_mod('nav_menu_locations', []),
            'existing_video_ids' => get_posts([
                'post_type' => 'video',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ]),
        ], false);
    }

    $images = function_exists('cb_install_demo_images') ? cb_install_demo_images(false) : [];
    if (function_exists('cb_install_catalog_videos')) {
        cb_install_catalog_videos($images);
    }
    if (function_exists('cb_install_demo_menus')) {
        cb_install_demo_menus();
    }
    update_option('cb_video_module_170_applied', current_time('mysql'), false);
    update_option('cb_core_db_version', '1.7.0');
    flush_rewrite_rules(false);
}

function cb_core_run_migration_160()
{
    if (get_option('cb_catalog_subpages_backup_160', null) === null) {
        $backup = [
            'created_at' => current_time('mysql'),
            'menu_locations' => get_theme_mod('nav_menu_locations', []),
            'posts' => [],
        ];
        $post_ids = get_posts([
            'post_type' => ['page', 'factory_showcase'],
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [['key' => '_cb_catalog_seed_key', 'compare' => 'EXISTS']],
        ]);
        foreach ($post_ids as $post_id) {
            $backup['posts'][$post_id] = [
                'seed_key' => get_post_meta($post_id, '_cb_catalog_seed_key', true),
                'sections' => get_post_meta($post_id, '_cb_page_sections', true),
                'page_ui' => get_post_meta($post_id, '_cb_page_ui', true),
                'render_mode' => get_post_meta($post_id, '_cb_page_render_mode', true),
                'gallery' => get_post_meta($post_id, '_cb_gallery', true),
            ];
        }
        update_option('cb_catalog_subpages_backup_160', $backup, false);
    }

    $images = function_exists('cb_install_demo_images') ? cb_install_demo_images(false) : [];
    if (function_exists('cb_install_catalog_content')) {
        cb_install_catalog_content($images);
    }
    if (function_exists('cb_install_demo_menus')) {
        cb_install_demo_menus();
    }
    update_option('cb_catalog_subpages_160_applied', current_time('mysql'), false);
    update_option('cb_core_db_version', '1.6.0');
    flush_rewrite_rules(false);
}

function cb_core_run_migration_150()
{
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    if (get_option('cb_about_certificate_backup_150', null) === null) {
        $backup = ['created_at' => current_time('mysql'), 'special_pages' => $special, 'pages' => []];
        foreach (['en', 'zh'] as $language) {
            $post_id = absint($special[$language]['about'] ?? 0);
            if (!$post_id) {
                continue;
            }
            $backup['pages'][$post_id] = [
                'sections' => get_post_meta($post_id, '_cb_page_sections', true),
                'page_ui' => get_post_meta($post_id, '_cb_page_ui', true),
                'render_mode' => get_post_meta($post_id, '_cb_page_render_mode', true),
            ];
        }
        update_option('cb_about_certificate_backup_150', $backup, false);
    }

    cb_install_certificate_categories_150();
    foreach (['en', 'zh'] as $language) {
        $post_id = absint($special[$language]['about'] ?? 0);
        if (!$post_id) {
            continue;
        }
        $sections = get_post_meta($post_id, '_cb_page_sections', true);
        update_post_meta($post_id, '_cb_page_sections', cb_sanitize_page_sections(cb_migrate_about_sections_150((array) $sections, $language)));
        update_post_meta($post_id, '_cb_page_render_mode', 'builder');
        update_post_meta($post_id, '_cb_about_layout_version', '1.5.0');

        $page_ui = get_post_meta($post_id, '_cb_page_ui', true);
        $page_ui = is_array($page_ui) ? $page_ui : [];
        $page_ui += [
            'page_layout' => 'sidebar',
            'show_banner' => '1',
            'show_breadcrumb' => '1',
            'banner_height_desktop' => '280px',
            'banner_height_mobile' => '220px',
        ];
        update_post_meta($post_id, '_cb_page_ui', $page_ui);
    }

    update_option('cb_about_certificate_150_applied', current_time('mysql'), false);
    update_option('cb_core_db_version', '1.5.0');
    flush_rewrite_rules(false);
}

function cb_install_certificate_categories_150()
{
    $groups = [
        'quality_systems' => ['en' => ['Quality Systems', 'quality-systems'], 'zh' => ['质量体系', 'quality-systems-zh']],
        'product_compliance' => ['en' => ['Product Compliance', 'product-compliance'], 'zh' => ['产品合规', 'product-compliance-zh']],
        'patents_design' => ['en' => ['Patents and Design', 'patents-design'], 'zh' => ['专利与设计', 'patents-design-zh']],
        'awards_qualifications' => ['en' => ['Awards and Qualifications', 'awards-qualifications'], 'zh' => ['奖项与资质', 'awards-qualifications-zh']],
    ];
    foreach ($groups as $group => $translations) {
        foreach ($translations as $language => $term_data) {
            $term = term_exists($term_data[1], 'certificate_category');
            if (!$term) {
                $term = wp_insert_term($term_data[0], 'certificate_category', ['slug' => $term_data[1]]);
            }
            if (!is_wp_error($term)) {
                $term_id = absint(is_array($term) ? $term['term_id'] : $term);
                update_term_meta($term_id, '_cb_language', $language);
                update_term_meta($term_id, '_cb_translation_group', 'certificate_category_' . $group);
            }
        }
    }
}

function cb_migrate_about_sections_150($sections, $language)
{
    $is_zh = $language === 'zh';
    $by_type = [];
    foreach ($sections as $section) {
        $type = $section['type'] ?? '';
        $by_type[$type][] = (array) $section;
    }
    $take = static function ($type, $index = 0) use (&$by_type) {
        return $by_type[$type][$index] ?? cb_default_builder_section($type);
    };

    $intro = $take('company_intro');
    $intro['section_id'] = 'overview';
    $intro['layout_style'] = $intro['layout_style'] ?: 'story_collage';

    $stats = $take('company_stats');
    $stats['section_id'] = $stats['section_id'] ?: 'capabilities';

    $timeline = $take('company_timeline');
    $timeline['section_id'] = 'milestones';

    $gallery = $take('showroom_gallery');
    $gallery['section_id'] = 'factory';
    $gallery['layout_style'] = $gallery['layout_style'] ?: 'immersive';

    $certificate = $take('certificates');
    $certificate_source = !empty($certificate['items']) && empty($certificate['certificate_source'])
        ? 'manual'
        : ($certificate['certificate_source'] ?? 'certificate_posts');
    $certificate = array_merge($certificate, [
        'section_id' => 'certificates',
        'layout_style' => 'document_grid',
        'certificate_source' => $certificate_source,
        'limit' => $certificate['limit'] ?: '6',
        'columns_desktop' => $certificate['columns_desktop'] ?: '3',
        'columns_tablet' => $certificate['columns_tablet'] ?: '2',
        'columns_mobile' => $certificate['columns_mobile'] ?: '1',
        'eyebrow' => $certificate['eyebrow'] ?: ($is_zh ? '质量与合规' : 'Quality & Compliance'),
        'title' => $certificate['title'] ?: ($is_zh ? '资质证书' : 'Certificate Showcase'),
        'description' => $certificate['description'] ?: ($is_zh ? '集中展示质量、工程与合规文件；演示记录须在正式上线前替换。' : 'A structured library for quality, engineering and compliance documents. Replace demo records before production launch.'),
        'button_text' => $certificate['button_text'] ?: ($is_zh ? '查看全部证书' : 'View all certificates'),
    ]);

    $quality = $take('why_choose_us');
    $quality['section_id'] = 'quality';
    $quality['layout_style'] = $quality['layout_style'] ?: 'minimal_matrix';
    if (empty($quality['title'])) {
        $quality['eyebrow'] = $is_zh ? '质量与研发' : 'Quality & R&D';
        $quality['title'] = $is_zh ? '从工程设计到量产的质量控制' : 'Quality Control from Engineering to Mass Production';
    }

    $services = $take('why_choose_us', 1);
    $services = array_merge($services, [
        'section_id' => 'services',
        'layout_style' => 'service_matrix',
        'eyebrow' => $services['eyebrow'] ?: ($is_zh ? '服务承诺' : 'Service Commitments'),
        'title' => $services['title'] ?: ($is_zh ? '支持品牌产品全周期落地' : 'Support across the Product Development Cycle'),
        'description' => $services['description'] ?: ($is_zh ? '为国际品牌提供从产品定义到交付的协同服务。' : 'Coordinated support from product definition through delivery for international brands.'),
    ]);
    if (empty($services['items'])) {
        $service_items = $is_zh
            ? [['需求与合规咨询', '梳理目标市场、功能和合规要求。'], ['工业设计支持', '协调外观、结构与可制造性。'], ['模具与工程验证', '支持模具开发、样机与测试反馈。'], ['试产与质量确认', '在量产前验证工艺和质量控制点。'], ['柔性批量生产', '根据项目阶段协调产能和交付计划。'], ['出口与售后支持', '协助文件、物流和售后问题跟进。']]
            : [['Requirement & Compliance Review', 'Align target markets, functions and compliance requirements.'], ['Industrial Design Support', 'Coordinate appearance, structure and manufacturability.'], ['Tooling & Engineering Validation', 'Support tooling, prototypes and test feedback.'], ['Pilot Run & Quality Approval', 'Validate processes and quality gates before mass production.'], ['Flexible Mass Production', 'Coordinate capacity and delivery plans by project stage.'], ['Export & After-sales Support', 'Assist with documentation, logistics and issue follow-up.']];
        $services['items'] = array_map(static fn($item) => ['enable' => '1', 'icon' => '', 'title' => $item[0], 'description' => $item[1], 'url' => ''], $service_items);
    }

    $cta = $take('inquiry_cta');
    $cta = array_merge($cta, [
        'section_id' => 'contact',
        'layout_style' => 'compact_band',
        'title' => $cta['title'] ?: ($is_zh ? '准备开始您的 OEM/ODM 项目？' : 'Ready to Start Your OEM/ODM Project?'),
        'description' => $cta['description'] ?: ($is_zh ? '发送产品需求，我们的团队将与您联系。' : 'Send your product brief and our team will follow up with you.'),
    ]);
    if (empty($cta['items'])) {
        $cta['items'] = [['text' => $is_zh ? '提交产品需求' : 'Send product brief', 'url' => home_url('/' . $language . '/#inquiry'), 'style' => 'primary']];
    }

    return [$intro, $stats, $timeline, $gallery, $certificate, $quality, $services, $cta];
}

function cb_restore_about_layout_150()
{
    $backup = get_option('cb_about_certificate_backup_150', []);
    if (!is_array($backup) || empty($backup['pages'])) {
        return false;
    }
    foreach ($backup['pages'] as $post_id => $page) {
        update_post_meta(absint($post_id), '_cb_page_sections', (array) ($page['sections'] ?? []));
        update_post_meta(absint($post_id), '_cb_page_ui', (array) ($page['page_ui'] ?? []));
        update_post_meta(absint($post_id), '_cb_page_render_mode', $page['render_mode'] ?: 'editor');
        delete_post_meta(absint($post_id), '_cb_about_layout_version');
    }
    delete_option('cb_about_certificate_150_applied');
    return true;
}

function cb_handle_restore_about_layout()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_restore_about_layout');
    cb_restore_about_layout_150();
    wp_safe_redirect(add_query_arg(['page' => 'cb-company-tools', 'about_restored' => '1'], admin_url('admin.php')));
    exit;
}

function cb_core_run_migration_140()
{
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $backup = get_option('cb_catalog_layout_backup_140', null);
    if (!is_array($backup)) {
        $backup = [
            'created_at' => current_time('mysql'),
            'special_pages' => $special,
            'design_settings' => cb_get_group_options('cb_design_settings', cb_default_design_settings()),
            'header_settings' => cb_get_group_options('cb_header_settings', cb_default_header_settings()),
            'footer_settings' => cb_get_group_options('cb_footer_settings', cb_default_footer_settings()),
            'template_settings' => cb_get_group_options('cb_template_settings', cb_default_template_settings()),
            'pages' => [],
        ];
        foreach (['en', 'zh'] as $language) {
            foreach (['home', 'about', 'contact'] as $role) {
                $post_id = absint($special[$language][$role] ?? 0);
                if (!$post_id) {
                    continue;
                }
                $backup['pages'][$post_id] = [
                    'sections' => get_post_meta($post_id, '_cb_page_sections', true),
                    'page_ui' => get_post_meta($post_id, '_cb_page_ui', true),
                    'render_mode' => get_post_meta($post_id, '_cb_page_render_mode', true),
                ];
            }
        }
        update_option('cb_catalog_layout_backup_140', $backup, false);
    }

    $images = function_exists('cb_install_demo_images') ? cb_install_demo_images(false) : [];
    if (function_exists('cb_install_catalog_content')) {
        cb_install_catalog_content($images);
    }
    foreach (['en', 'zh'] as $language) {
        foreach (['home', 'about', 'contact'] as $role) {
            $post_id = absint($special[$language][$role] ?? 0);
            if (!$post_id) {
                continue;
            }
            $sections = $role === 'home'
                ? cb_catalog_homepage_sections($language, $images)
                : cb_catalog_special_page_sections($role, $language, $images);
            update_post_meta($post_id, '_cb_page_sections', cb_sanitize_page_sections($sections));
            update_post_meta($post_id, '_cb_page_render_mode', 'builder');
            update_post_meta($post_id, '_cb_catalog_layout_version', '1.4.0');
        }
    }

    $design = cb_get_group_options('cb_design_settings', cb_default_design_settings());
    $design = array_merge($design, [
        'primary_color' => '#ef3f45', 'primary_dark_color' => '#d92e35', 'primary_light_color' => '#fff2f2',
        'section_soft_bg' => '#f5f7f8', 'border_color' => '#e7eaee', 'container_width' => '1220px',
        'section_padding_y' => '84px', 'section_padding_y_mobile' => '56px', 'grid_gap' => '24px',
        'border_radius_sm' => '4px', 'border_radius_md' => '6px', 'border_radius_lg' => '8px',
        'card_radius' => '6px', 'card_shadow' => 'none', 'card_border' => '0', 'card_hover_effect' => 'image_zoom',
        'button_radius' => '4px', 'button_style' => 'square', 'button_shadow' => '0',
        'desktop_product_columns' => '3', 'tablet_product_columns' => '2', 'mobile_product_columns' => '1',
    ]);
    update_option('cb_design_settings', cb_sanitize_design_settings($design));

    $header = cb_get_group_options('cb_header_settings', cb_default_header_settings());
    update_option('cb_header_settings', cb_sanitize_header_settings(array_merge($header, [
        'header_layout' => 'logo_left_menu_center_cta_right', 'header_height' => '76px', 'header_sticky' => '1',
        'header_blur' => '0', 'header_shadow' => '0', 'header_full_width' => '0', 'show_search' => '1',
        'show_language_switcher' => '1', 'show_header_cta' => '0',
    ])));
    $templates = cb_get_group_options('cb_template_settings', cb_default_template_settings());
    foreach (['product_archive', 'product_category'] as $context) {
        $templates[$context] = array_merge($templates[$context] ?? [], ['columns_desktop' => '3', 'columns_tablet' => '2', 'columns_mobile' => '1', 'sidebar' => 'left']);
    }
    $templates['product_single'] = array_merge($templates['product_single'] ?? [], ['mobile_sticky_cta' => '1', 'show_related_products' => '1', 'show_inquiry' => '1']);
    update_option('cb_template_settings', cb_sanitize_template_settings($templates));
    $footer = cb_get_group_options('cb_footer_settings', cb_default_footer_settings());
    $footer_image = cb_catalog_image($images, 'hero_assembly');
    update_option('cb_footer_settings', cb_sanitize_footer_settings(array_merge($footer, [
        'footer_layout' => 'four_columns', 'footer_background_image' => $footer_image['url'],
        'show_footer_logo' => '1', 'show_footer_products' => '1', 'show_footer_links' => '1',
        'show_footer_contact' => '1', 'show_footer_social' => '1', 'floating_contact' => '1',
        'footer_description' => 'OEM/ODM kitchen appliance manufacturing for ambitious global brands.',
        'contact_email' => 'info@aureliamanufacturing.com', 'contact_phone' => '+86 000 0000 0000',
        'company_address' => 'Manufacturing base in China.',
        'copyright_text' => '© ' . gmdate('Y') . ' Aurelia Manufacturing. All rights reserved.',
    ])));
    update_option('cb_catalog_layout_140_applied', current_time('mysql'), false);
    update_option('cb_core_db_version', '1.4.0');
}

function cb_restore_catalog_layout_140()
{
    $backup = get_option('cb_catalog_layout_backup_140', []);
    if (!is_array($backup) || empty($backup['pages'])) {
        return false;
    }
    foreach ((array) $backup['pages'] as $post_id => $page) {
        update_post_meta(absint($post_id), '_cb_page_sections', (array) ($page['sections'] ?? []));
        update_post_meta(absint($post_id), '_cb_page_ui', (array) ($page['page_ui'] ?? []));
        update_post_meta(absint($post_id), '_cb_page_render_mode', $page['render_mode'] ?: 'editor');
        delete_post_meta(absint($post_id), '_cb_catalog_layout_version');
    }
    foreach (['special_pages' => 'cb_special_pages', 'design_settings' => 'cb_design_settings', 'header_settings' => 'cb_header_settings', 'footer_settings' => 'cb_footer_settings', 'template_settings' => 'cb_template_settings'] as $key => $option) {
        if (isset($backup[$key])) {
            update_option($option, $backup[$key]);
        }
    }
    delete_option('cb_catalog_layout_140_applied');
    return true;
}

function cb_handle_restore_catalog_layout()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_restore_catalog_layout');
    cb_restore_catalog_layout_140();
    wp_safe_redirect(add_query_arg(['page' => 'cb-company-tools', 'catalog_restored' => '1'], admin_url('admin.php')));
    exit;
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
    update_option('cb_string_translations', cb_repair_frontend_translations(get_option('cb_string_translations', [])));
    update_option('cb_core_db_version', '1.1.0');
}

function cb_core_run_migration_130()
{
    $page_ids = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_cb_page_sections',
        'no_found_rows' => true,
    ]);
    foreach ($page_ids as $post_id) {
        $sections = get_post_meta($post_id, '_cb_page_sections', true);
        if (!is_array($sections)) {
            continue;
        }
        $backup_key = 'cb_page_sections_backup_130_' . absint($post_id);
        if (get_option($backup_key, null) === null) {
            update_option($backup_key, $sections, false);
        }
        $migrated = array_map('cb_migrate_section_130', $sections);
        update_post_meta($post_id, '_cb_page_sections', cb_sanitize_page_sections($migrated));
    }
    update_option('cb_core_db_version', '1.3.0');
}

function cb_core_run_migration_131()
{
    $image_manifest = (array) get_option('cb_demo_image_manifest', []);
    $page_ids = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_cb_page_sections',
        'no_found_rows' => true,
    ]);

    foreach ($page_ids as $post_id) {
        $sections = get_post_meta($post_id, '_cb_page_sections', true);
        if (!is_array($sections)) {
            continue;
        }
        $old_hash = md5(serialize($sections));
        $repaired = cb_repair_section_color_defaults($sections);
        if ($repaired !== $sections) {
            $backup_key = 'cb_page_sections_backup_131_' . absint($post_id);
            if (get_option($backup_key, null) === null) {
                update_option($backup_key, $sections, false);
            }
            update_post_meta($post_id, '_cb_page_sections', $repaired);
        }

        if (empty($image_manifest['pages'][$post_id])) {
            continue;
        }
        $snapshot = &$image_manifest['pages'][$post_id];
        $was_applied = !empty($snapshot['applied_hash']) && hash_equals((string) $snapshot['applied_hash'], $old_hash);
        $snapshot['before'] = cb_repair_section_color_defaults((array) ($snapshot['before'] ?? []));
        if ($was_applied) {
            $snapshot['applied_hash'] = md5(serialize($repaired));
        }
        unset($snapshot);
    }

    if ($image_manifest) {
        update_option('cb_demo_image_manifest', $image_manifest, false);
    }
    update_option('cb_core_db_version', '1.3.1');
}

function cb_repair_section_color_defaults($sections)
{
    $sections = (array) $sections;
    foreach ($sections as &$section) {
        if (!is_array($section)) {
            continue;
        }
        $background = strtolower((string) ($section['background_color'] ?? ''));
        $text = strtolower((string) ($section['text_color'] ?? ''));
        if ($background === '#000000' && $text === '#000000') {
            $section['background_color'] = '';
            $section['text_color'] = '';
        }
    }
    unset($section);
    return $sections;
}

function cb_migrate_section_130($section)
{
    $section = (array) $section;
    $type = $section['type'] ?? '';
    if ($type !== 'hero_slider') {
        if (isset(cb_section_item_schemas()[$type]) && !empty($section['items'])) {
            $section['items'] = cb_migrate_section_items_130($type, $section['items']);
        }
        return $section;
    }
    if (!empty($section['slides'])) {
        return $section;
    }
    $legacy_slide = [];
    if (!empty($section['hero_slides'][0]) && is_array($section['hero_slides'][0])) {
        $legacy_slide = $section['hero_slides'][0];
    } elseif (!empty($section['items'][0]) && is_array($section['items'][0])) {
        $legacy_slide = $section['items'][0];
    }
    $slide = cb_hero_slide_defaults();
    $slide['admin_label'] = sanitize_text_field($legacy_slide['admin_label'] ?? '');
    $slide['title'] = $legacy_slide['title'] ?? ($section['title'] ?? '');
    $slide['eyebrow'] = $legacy_slide['eyebrow'] ?? ($section['eyebrow'] ?? '');
    $slide['description'] = $legacy_slide['description'] ?? ($section['description'] ?? '');
    $slide['primary_button_text'] = $legacy_slide['primary_button_text'] ?? ($legacy_slide['button_1_text'] ?? ($section['button_text'] ?? ''));
    $slide['primary_button_url'] = $legacy_slide['primary_button_url'] ?? ($legacy_slide['button_1_url'] ?? ($legacy_slide['url'] ?? ($section['button_url'] ?? '')));
    $slide['image_id'] = absint($legacy_slide['image_id'] ?? ($section['image_id'] ?? 0));
    $slide['image_url'] = $legacy_slide['image_url'] ?? ($section['image_url'] ?? ($section['image'] ?? ''));
    $section['slides'] = [$slide];
    unset($section['hero_slides'], $section['items']);
    return $section;
}

function cb_migrate_section_items_130($type, $items)
{
    $mapped = [];
    foreach ((array) $items as $index => $item) {
        $item = (array) $item;
        $title = $item['title'] ?? ($item['label'] ?? '');
        $description = $item['description'] ?? ($item['value'] ?? '');
        $image_id = absint($item['image_id'] ?? 0);
        $image_url = $item['image_url'] ?? ($item['image'] ?? '');
        $url = $item['url'] ?? '';
        if ($type === 'company_intro') {
            preg_match('/^([\d,.]+)\s*(.*)$/u', (string) $title, $matches);
            $mapped[] = ['number' => $matches[1] ?? $title, 'suffix' => $matches[2] ?? '', 'label' => $description, 'icon' => $item['icon'] ?? ''];
        } elseif ($type === 'why_choose_us') {
            $mapped[] = ['enable' => $item['enable'] ?? '1', 'icon' => $item['icon'] ?? '', 'title' => $title, 'description' => $description, 'url' => $url];
        } elseif (in_array($type, ['factory_capability', 'case_studies'], true)) {
            $mapped[] = ['enable' => $item['enable'] ?? '1', 'title' => $title, 'description' => $description, 'image_id' => $image_id, 'image_url' => $image_url, 'url' => $url];
        } elseif ($type === 'oem_odm_process') {
            $mapped[] = ['step_number' => $item['step_number'] ?? (string) ($index + 1), 'icon' => $item['icon'] ?? '', 'title' => $title, 'description' => $description];
        } elseif ($type === 'certificates') {
            $mapped[] = ['enable' => $item['enable'] ?? '1', 'title' => $title, 'description' => $description, 'image_id' => $image_id, 'image_url' => $image_url];
        } elseif ($type === 'inquiry_cta') {
            $mapped[] = ['text' => $item['text'] ?? $title, 'url' => $url, 'style' => $item['style'] ?? 'primary'];
        } elseif ($type === 'contact_info') {
            $mapped[] = ['icon' => $item['icon'] ?? '', 'label' => $title, 'value' => $description, 'url' => $url];
        } elseif ($type === 'gallery') {
            $mapped[] = ['enable' => $item['enable'] ?? '1', 'image_id' => $image_id, 'image_url' => $image_url, 'image_alt' => $item['image_alt'] ?? $title, 'caption' => $item['caption'] ?? $description];
        }
    }
    return $mapped;
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
    $patterns = array_map(static fn($hex) => pack('H*', $hex), ['c383', 'c382', 'c3a2e282ac', 'c3afc2bbc2bf']);
    $patterns[] = "\u{FFFD}";
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
