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
