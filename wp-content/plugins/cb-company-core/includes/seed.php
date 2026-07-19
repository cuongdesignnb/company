<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_default_homepage_sections()
{
    return [];
}

function cb_demo_image_definitions()
{
    return [
        'hero' => [
            'file' => 'hero-kitchen-appliances.webp',
            'title' => 'Kitchen appliance manufacturing hero',
            'alt' => 'Kitchen appliances in front of a modern manufacturing facility',
        ],
        'factory' => [
            'file' => 'factory-production-line.webp',
            'title' => 'Kitchen appliance production line',
            'alt' => 'Workers assembling kitchen appliances on a modern production line',
        ],
        'hero_campus' => [
            'file' => 'hero-campus.webp',
            'title' => 'Aurelia manufacturing campus',
            'alt' => 'Kitchen appliances displayed in front of a modern manufacturing campus',
        ],
        'hero_assembly' => [
            'file' => 'hero-assembly.webp',
            'title' => 'Aurelia appliance assembly hall',
            'alt' => 'Modern kitchen appliance assembly hall with organized production lines',
        ],
        'hero_showroom' => [
            'file' => 'hero-showroom.webp',
            'title' => 'Aurelia appliance showroom',
            'alt' => 'Premium kitchen appliance showroom inside a manufacturing headquarters',
        ],
        'factory_closeup' => [
            'file' => 'factory-assembly-closeup.webp',
            'title' => 'Appliance assembly team',
            'alt' => 'Technicians assembling stainless steel kitchen appliances',
        ],
        'factory_detail' => [
            'file' => 'factory-assembly-detail.webp',
            'title' => 'Appliance assembly detail',
            'alt' => 'Detailed view of a modern appliance production line',
        ],
        'quality_lab' => [
            'file' => 'quality-lab.webp',
            'title' => 'Appliance quality laboratory',
            'alt' => 'Quality engineer testing an espresso machine in a laboratory',
        ],
        'quality_detail' => [
            'file' => 'quality-testing-detail.webp',
            'title' => 'Quality testing detail',
            'alt' => 'Detailed appliance performance testing equipment',
        ],
        'warehouse' => [
            'file' => 'warehouse-logistics.webp',
            'title' => 'Global fulfillment warehouse',
            'alt' => 'Organized kitchen appliance warehouse and logistics operation',
        ],
        'warehouse_detail' => [
            'file' => 'warehouse-scanning-detail.webp',
            'title' => 'Warehouse scanning operation',
            'alt' => 'Logistics worker scanning appliance shipments',
        ],
        'showroom_detail' => [
            'file' => 'showroom-gallery-detail.webp',
            'title' => 'Showroom product display',
            'alt' => 'Detailed kitchen appliance showroom display',
        ],
        'case_hospitality' => [
            'file' => 'case-hospitality.webp',
            'title' => 'Hospitality appliance program',
            'alt' => 'Coordinated countertop appliances in a contemporary hotel breakfast bar',
        ],
        'case_hospitality_detail' => [
            'file' => 'case-hospitality-detail.webp',
            'title' => 'Hospitality appliance detail',
            'alt' => 'Coffee and breakfast appliances used in a hospitality setting',
        ],
        'news_rd' => [
            'file' => 'news-rd-team.webp',
            'title' => 'Aurelia product development team',
            'alt' => 'Engineering team reviewing a countertop blender prototype',
        ],
        'news_detail' => [
            'file' => 'news-prototype-detail.webp',
            'title' => 'Blender prototype review',
            'alt' => 'Detailed review of a new kitchen appliance prototype',
        ],
        'air_fryer' => [
            'file' => 'product-air-fryer.webp',
            'title' => 'Air fryer product',
            'alt' => 'Modern black air fryer on a clean studio background',
        ],
        'espresso' => [
            'file' => 'product-espresso-machine.webp',
            'title' => 'Espresso machine product',
            'alt' => 'Stainless steel espresso machine on a clean studio background',
        ],
        'stand_mixer' => [
            'file' => 'product-stand-mixer.webp',
            'title' => 'Stand mixer product',
            'alt' => 'Silver stand mixer with a stainless steel bowl on a clean studio background',
        ],
        'blender' => [
            'file' => 'product-blender.webp',
            'title' => 'Countertop blender product',
            'alt' => 'Stainless steel countertop blender on a clean studio background',
        ],
        'multi_cooker' => [
            'file' => 'product-multi-cooker.webp',
            'title' => 'Multi cooker product',
            'alt' => 'Stainless steel electric multi cooker on a clean studio background',
        ],
        'kettle' => [
            'file' => 'product-electric-kettle.webp',
            'title' => 'Electric kettle product',
            'alt' => 'Stainless steel electric kettle on a clean studio background',
        ],
        'demo_certificate_quality' => [
            'file' => 'demo-certificate-quality-process.webp',
            'title' => 'Aurelia demo quality process review',
            'alt' => 'Demo quality process review document, not for compliance use',
        ],
        'demo_certificate_safety' => [
            'file' => 'demo-certificate-product-safety.webp',
            'title' => 'Aurelia demo product safety test summary',
            'alt' => 'Demo product safety test summary, not for compliance use',
        ],
        'demo_certificate_supplier' => [
            'file' => 'demo-certificate-supplier-audit.webp',
            'title' => 'Aurelia demo supplier audit report',
            'alt' => 'Demo supplier audit report, not for compliance use',
        ],
        'demo_certificate_rd' => [
            'file' => 'demo-certificate-rd-validation.webp',
            'title' => 'Aurelia demo research and development validation record',
            'alt' => 'Demo research and development validation record, not for compliance use',
        ],
        'demo_certificate_packaging' => [
            'file' => 'demo-certificate-packaging.webp',
            'title' => 'Aurelia demo packaging reliability review',
            'alt' => 'Demo packaging reliability review, not for compliance use',
        ],
        'demo_certificate_export' => [
            'file' => 'demo-certificate-export.webp',
            'title' => 'Aurelia demo export documentation checklist',
            'alt' => 'Demo export documentation checklist, not for compliance use',
        ],
    ];
}

function cb_demo_image_is_replaceable($value)
{
    if (empty($value)) {
        return true;
    }
    if (is_array($value)) {
        return empty(array_filter($value));
    }
    return str_contains((string) $value, 'aurelia-reference.png');
}

function cb_import_demo_image($key, array &$manifest)
{
    $definitions = cb_demo_image_definitions();
    if (empty($definitions[$key])) {
        return [];
    }

    $attachment_id = absint($manifest['attachments'][$key] ?? 0);
    if ($attachment_id && get_post_type($attachment_id) === 'attachment' && file_exists((string) get_attached_file($attachment_id))) {
        return ['id' => $attachment_id, 'url' => (string) wp_get_attachment_url($attachment_id)];
    }

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_cb_demo_asset_key',
        'meta_value' => $key,
    ]);
    if ($existing) {
        $attachment_id = absint($existing[0]);
        $manifest['attachments'][$key] = $attachment_id;
        return ['id' => $attachment_id, 'url' => (string) wp_get_attachment_url($attachment_id)];
    }

    $definition = $definitions[$key];
    $source = CB_CORE_PATH . 'assets/demo/' . $definition['file'];
    if (!is_readable($source)) {
        return [];
    }

    $upload = wp_upload_bits($definition['file'], null, file_get_contents($source));
    if (!empty($upload['error'])) {
        return [];
    }

    $filetype = wp_check_filetype($upload['file']);
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => $filetype['type'] ?: 'image/webp',
        'post_title' => $definition['title'],
        'post_content' => '',
        'post_status' => 'inherit',
    ], $upload['file']);
    if (!$attachment_id || is_wp_error($attachment_id)) {
        return [];
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    if (is_array($metadata)) {
        wp_update_attachment_metadata($attachment_id, $metadata);
    }
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $definition['alt']);
    update_post_meta($attachment_id, '_cb_is_demo_content', '1');
    update_post_meta($attachment_id, '_cb_demo_asset_key', $key);
    $manifest['attachments'][$key] = absint($attachment_id);

    return ['id' => absint($attachment_id), 'url' => (string) $upload['url']];
}

function cb_demo_product_image_key($title, $index = 0)
{
    $title = strtolower(remove_accents((string) $title));
    if (str_contains($title, 'air fryer') || str_contains($title, 'small kitchen')) {
        return 'air_fryer';
    }
    if (str_contains($title, 'espresso') || str_contains($title, 'coffee')) {
        return 'espresso';
    }
    if (str_contains($title, 'stand mixer') || str_contains($title, 'food preparation')) {
        return 'stand_mixer';
    }
    if (str_contains($title, 'blender')) {
        return 'blender';
    }
    if (str_contains($title, 'multi cooker') || str_contains($title, 'cooking appliances')) {
        return 'multi_cooker';
    }
    if (str_contains($title, 'kettle')) {
        return 'kettle';
    }
    return $index % 2 === 0 ? 'air_fryer' : 'espresso';
}

function cb_demo_gallery_uses_attachment($gallery, array $attachment_ids)
{
    if (!is_array($gallery)) {
        return false;
    }
    $first = reset($gallery);
    return is_array($first) && in_array(absint($first['image_id'] ?? 0), $attachment_ids, true);
}

function cb_assign_demo_images_to_pages(array $images, array &$manifest)
{
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    foreach (['en', 'zh'] as $language) {
        $page_id = absint($special[$language]['home'] ?? 0);
        $sections = $page_id ? cb_get_page_sections($page_id) : [];
        if (!$sections) {
            continue;
        }

        $before = $sections;
        foreach ($sections as &$section) {
            $type = $section['type'] ?? '';
            if ($type === 'hero_slider' && !empty($images['hero'])) {
                $slides = is_array($section['slides'] ?? null) ? $section['slides'] : [];
                if (!$slides) {
                    $slides[] = cb_hero_slide_defaults();
                }
                if (cb_demo_image_is_replaceable($slides[0]['image_url'] ?? '')) {
                    $slides[0]['image_id'] = $images['hero']['id'];
                    $slides[0]['image_url'] = $images['hero']['url'];
                    $slides[0]['image_alt'] = cb_demo_image_definitions()['hero']['alt'];
                }
                $section['slides'] = $slides;
            } elseif ($type === 'company_intro' && !empty($images['factory']) && cb_demo_image_is_replaceable($section['image_url'] ?? '')) {
                $section['image_id'] = $images['factory']['id'];
                $section['image_url'] = $images['factory']['url'];
            } elseif ($type === 'factory_capability' && !empty($images['factory']) && cb_demo_image_is_replaceable($section['image_url'] ?? '')) {
                $section['image_id'] = $images['factory']['id'];
                $section['image_url'] = $images['factory']['url'];
            }
        }
        unset($section);

        if ($sections !== $before) {
            if (empty($manifest['pages'][$page_id])) {
                $manifest['pages'][$page_id]['before'] = $before;
            }
            $clean = cb_sanitize_page_sections($sections);
            update_post_meta($page_id, '_cb_page_sections', $clean);
            $manifest['pages'][$page_id]['applied_hash'] = md5(serialize($clean));
        }
    }
}

function cb_assign_demo_images_to_products(array $images, array &$manifest)
{
    $attachment_ids = array_values(array_filter(array_map(static fn($image) => absint($image['id'] ?? 0), $images)));
    $product_ids = get_posts([
        'post_type' => 'product',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
    ]);
    foreach ($product_ids as $index => $product_id) {
        $key = cb_demo_product_image_key(get_the_title($product_id), $index);
        if (empty($images[$key])) {
            continue;
        }
        $before_thumbnail = get_post_thumbnail_id($product_id);
        $before_gallery = get_post_meta($product_id, '_cb_gallery', true);
        $changed = false;
        if (!$before_thumbnail || in_array(absint($before_thumbnail), $attachment_ids, true)) {
            set_post_thumbnail($product_id, $images[$key]['id']);
            $changed = true;
        }
        if (cb_demo_image_is_replaceable($before_gallery) || cb_demo_gallery_uses_attachment($before_gallery, $attachment_ids)) {
            update_post_meta($product_id, '_cb_gallery', [[
                'title' => get_the_title($product_id),
                'description' => '',
                'image_id' => $images[$key]['id'],
                'image_url' => $images[$key]['url'],
                'url' => '',
            ]]);
            $changed = true;
        }
        if ($changed) {
            if (empty($manifest['products'][$product_id])) {
                $manifest['products'][$product_id]['before_thumbnail'] = absint($before_thumbnail);
                $manifest['products'][$product_id]['before_gallery'] = $before_gallery;
            }
            $manifest['products'][$product_id]['applied_thumbnail'] = get_post_thumbnail_id($product_id);
            $manifest['products'][$product_id]['applied_gallery'] = get_post_meta($product_id, '_cb_gallery', true);
        }
    }
}

function cb_assign_demo_images_to_terms(array $images, array &$manifest)
{
    $image_urls = array_values(array_filter(array_map(static fn($image) => (string) ($image['url'] ?? ''), $images)));
    $terms = get_terms(['taxonomy' => 'product_category', 'hide_empty' => false]);
    if (is_wp_error($terms)) {
        return;
    }
    foreach ($terms as $index => $term) {
        $key = cb_demo_product_image_key($term->name, $index);
        if (empty($images[$key])) {
            continue;
        }
        $before = get_term_meta($term->term_id, '_cb_banner_image', true);
        if (!cb_demo_image_is_replaceable($before) && !in_array((string) $before, $image_urls, true)) {
            continue;
        }
        update_term_meta($term->term_id, '_cb_banner_image', $images[$key]['url']);
        if (empty($manifest['terms'][$term->term_id])) {
            $manifest['terms'][$term->term_id]['before'] = $before;
        }
        $manifest['terms'][$term->term_id]['applied'] = $images[$key]['url'];
    }
}

function cb_install_demo_images($assign = true)
{
    $manifest = (array) get_option('cb_demo_image_manifest', []);
    $manifest = wp_parse_args($manifest, ['attachments' => [], 'pages' => [], 'products' => [], 'terms' => []]);
    $images = [];
    foreach (array_keys(cb_demo_image_definitions()) as $key) {
        $image = cb_import_demo_image($key, $manifest);
        if ($image) {
            $images[$key] = $image;
        }
    }
    if ($assign) {
        cb_assign_demo_images_to_pages($images, $manifest);
        cb_assign_demo_images_to_products($images, $manifest);
        cb_assign_demo_images_to_terms($images, $manifest);
    }
    $manifest['installed_at'] = current_time('mysql');
    update_option('cb_demo_image_manifest', $manifest, false);
    return $images;
}

function cb_delete_demo_images()
{
    $manifest = (array) get_option('cb_demo_image_manifest', []);
    $content_manifest = (array) get_option('cb_demo_content_manifest', []);
    foreach ((array) ($content_manifest['about_pages'] ?? []) as $page_id => $snapshot) {
        $current = get_post_meta(absint($page_id), '_cb_page_ui', true);
        if (!empty($snapshot['applied_hash']) && hash_equals((string) $snapshot['applied_hash'], md5(serialize(is_array($current) ? $current : [])))) {
            update_post_meta(absint($page_id), '_cb_page_ui', (array) ($snapshot['before'] ?? []));
        }
    }
    unset($content_manifest['about_pages']);
    if ($content_manifest) {
        update_option('cb_demo_content_manifest', $content_manifest, false);
    }
    foreach ((array) ($manifest['pages'] ?? []) as $page_id => $snapshot) {
        $current = get_post_meta($page_id, '_cb_page_sections', true);
        $current_hash = md5(serialize(is_array($current) ? $current : []));
        if (!empty($snapshot['applied_hash']) && hash_equals((string) $snapshot['applied_hash'], $current_hash)) {
            update_post_meta($page_id, '_cb_page_sections', (array) ($snapshot['before'] ?? []));
        }
    }
    foreach ((array) ($manifest['products'] ?? []) as $product_id => $snapshot) {
        if (get_post_thumbnail_id($product_id) === absint($snapshot['applied_thumbnail'] ?? 0)) {
            $before_thumbnail = absint($snapshot['before_thumbnail'] ?? 0);
            $before_thumbnail ? set_post_thumbnail($product_id, $before_thumbnail) : delete_post_thumbnail($product_id);
        }
        if (get_post_meta($product_id, '_cb_gallery', true) === ($snapshot['applied_gallery'] ?? null)) {
            update_post_meta($product_id, '_cb_gallery', $snapshot['before_gallery'] ?? '');
        }
    }
    foreach ((array) ($manifest['terms'] ?? []) as $term_id => $snapshot) {
        if (get_term_meta($term_id, '_cb_banner_image', true) === ($snapshot['applied'] ?? null)) {
            update_term_meta($term_id, '_cb_banner_image', $snapshot['before'] ?? '');
        }
    }
    foreach ((array) ($manifest['attachments'] ?? []) as $attachment_id) {
        $attachment_id = absint($attachment_id);
        if ($attachment_id && get_post_meta($attachment_id, '_cb_demo_asset_key', true)) {
            wp_delete_attachment($attachment_id, true);
        }
    }
    delete_option('cb_demo_image_manifest');
}

function cb_demo_image_status()
{
    $manifest = (array) get_option('cb_demo_image_manifest', []);
    $count = 0;
    foreach ((array) ($manifest['attachments'] ?? []) as $attachment_id) {
        if (get_post_type(absint($attachment_id)) === 'attachment') {
            $count++;
        }
    }
    return ['installed' => $count > 0, 'attachment_count' => $count, 'manifest' => $manifest];
}

function cb_demo_homepage_sections($images = [])
{
    $hero = $images['hero'] ?? ['id' => 0, 'url' => ''];
    $factory = $images['factory'] ?? ['id' => 0, 'url' => ''];
    return [
        [
            'enable' => '1',
            'type' => 'hero_slider',
            'admin_label' => 'Aurelia Demo Hero',
            'layout_style' => 'full_width',
            'min_height_desktop' => '560px',
            'min_height_mobile' => '460px',
            'content_width' => '650px',
            'autoplay' => '1',
            'autoplay_delay' => '6000',
            'transition_speed' => '500',
            'show_arrows' => '1',
            'show_dots' => '1',
            'pause_on_hover' => '1',
            'slides' => [
                array_merge(cb_hero_slide_defaults(), [
                    'admin_label' => 'Manufacturing Hero',
                    'image_id' => $hero['id'],
                    'image_url' => $hero['url'],
                    'image_alt' => cb_demo_image_definitions()['hero']['alt'],
                    'eyebrow' => 'KITCHEN APPLIANCE MANUFACTURER',
                    'title' => 'Precision Manufacturing.',
                    'highlight_text' => 'Performance in Every Detail.',
                    'description' => 'Reliable OEM/ODM kitchen appliance manufacturing for international brands.',
                    'primary_button_text' => 'Explore Products',
                    'primary_button_url' => '/en/products/',
                    'secondary_button_text' => 'Contact Us',
                    'secondary_button_url' => '/en/contact-us/',
                    'overlay_enable' => '1',
                    'overlay_color' => '#ffffff',
                    'overlay_opacity' => '34',
                    'trust_badges' => [
                        ['icon' => 'quality', 'text' => 'Reliable Quality'],
                        ['icon' => 'delivery', 'text' => 'On-time Delivery'],
                        ['icon' => 'global', 'text' => 'Global Reach'],
                    ],
                ]),
            ],
        ],
        [
            'enable' => '1',
            'type' => 'company_intro',
            'layout_style' => 'split',
            'eyebrow' => 'ABOUT AURELIA',
            'title' => 'Built on Quality. Driven by Innovation.',
            'description' => 'A kitchen appliance manufacturer integrating research, production and global sales.',
            'image_id' => $factory['id'],
            'image_url' => $factory['url'],
            'items' => [
                ['number' => '14', 'suffix' => '+', 'label' => 'Years Experience', 'icon' => 'calendar'],
                ['number' => '50000', 'suffix' => 'm²', 'label' => 'Factory Area', 'icon' => 'factory'],
            ],
        ],
        ['enable' => '1', 'type' => 'featured_products', 'layout_style' => 'grid', 'title' => 'Featured Products', 'limit' => '6'],
    ];
}

function cb_catalog_image(array $images, $key)
{
    return wp_parse_args((array) ($images[$key] ?? []), ['id' => 0, 'url' => '']);
}

function cb_catalog_homepage_sections($language, array $images)
{
    $is_zh = $language === 'zh';
    $copy = $is_zh ? [
        'stats' => [
            ['number' => '14', 'suffix' => '+', 'label' => '年制造经验', 'icon' => 'calendar', 'needs_review' => '1'],
            ['number' => '50,000', 'suffix' => ' m²', 'label' => '现代化厂区', 'icon' => 'factory', 'needs_review' => '1'],
            ['number' => '70', 'suffix' => '+', 'label' => '出口国家和地区', 'icon' => 'global', 'needs_review' => '1'],
            ['number' => '600', 'suffix' => '+', 'label' => '专业团队成员', 'icon' => 'team', 'needs_review' => '1'],
        ],
        'intro_eyebrow' => '关于 AURELIA',
        'intro_title' => '以可靠制造，支持品牌长期成长。',
        'intro_description' => 'Aurelia 集产品研发、工程验证、规模生产和全球交付于一体，为国际厨房电器品牌提供灵活的 OEM/ODM 合作。我们以清晰的流程、可追溯的质量控制和稳定的交期，将产品概念转化为可持续销售的系列。',
        'timeline_title' => '持续建设的制造能力',
        'timeline_description' => '围绕产品开发、质量体系、规模生产和全球交付持续完善制造能力。',
        'timeline' => [
            ['year' => '2010', 'title' => '制造基础', 'description' => '建立小家电制造与供应链团队。', 'needs_review' => '1'],
            ['year' => '2014', 'title' => 'OEM 项目扩展', 'description' => '形成从结构设计到量产导入的协作流程。', 'needs_review' => '1'],
            ['year' => '2018', 'title' => '产能升级', 'description' => '扩建自动化装配和可靠性测试能力。', 'needs_review' => '1'],
            ['year' => '2022', 'title' => '全球质量体系', 'description' => '加强实验室验证与供应商追溯管理。', 'needs_review' => '1'],
            ['year' => '2026', 'title' => '智能制造计划', 'description' => '推进数字化生产和多品类平台开发。', 'needs_review' => '1'],
        ],
        'gallery_eyebrow' => '制造现场',
        'gallery_title' => '从工程验证到全球交付',
        'gallery_description' => '查看支持稳定质量和灵活定制的生产、测试、展示与物流环境。',
        'why_eyebrow' => '我们的优势',
        'why_title' => '为品牌项目而设计的制造体系',
        'why_description' => '跨职能团队在每个关键节点保持可见、可控和可追溯。',
        'advantages' => [
            ['enable' => '1', 'icon' => 'shield', 'title' => '质量保证', 'description' => '从来料检验到最终性能测试的全过程控制。'],
            ['enable' => '1', 'icon' => 'equipment', 'title' => '先进设备', 'description' => '标准化装配、测试和包装设施支持稳定量产。'],
            ['enable' => '1', 'icon' => 'research', 'title' => '研发协同', 'description' => '工程团队支持结构、性能和合规优化。'],
            ['enable' => '1', 'icon' => 'custom', 'title' => 'OEM/ODM 定制', 'description' => '根据市场定位灵活配置外观、功能和包装。'],
            ['enable' => '1', 'icon' => 'delivery', 'title' => '准时交付', 'description' => '透明的计划管理和关键节点跟进。'],
            ['enable' => '1', 'icon' => 'support', 'title' => '全球支持', 'description' => '为国际项目提供快速响应和持续服务。'],
        ],
        'products_eyebrow' => '产品系列',
        'products_title' => '面向全球市场的厨房电器',
        'products_description' => '成熟平台与灵活配置相结合，支持品牌快速构建产品组合。',
        'cases_eyebrow' => '项目案例',
        'cases_title' => '从产品需求到市场落地',
        'news_eyebrow' => '最新动态',
        'news_title' => '研发、制造与行业洞察',
        'cta_title' => '准备启动您的下一个产品项目？',
        'cta_description' => '告诉我们目标市场、功能方向和预计数量，我们的团队将提供下一步建议。',
        'cta_button' => '提交产品需求',
    ] : [
        'stats' => [
            ['number' => '14', 'suffix' => '+', 'label' => 'Years of Manufacturing', 'icon' => 'calendar', 'needs_review' => '1'],
            ['number' => '50,000', 'suffix' => ' m²', 'label' => 'Modern Factory Area', 'icon' => 'factory', 'needs_review' => '1'],
            ['number' => '70', 'suffix' => '+', 'label' => 'Countries and Markets', 'icon' => 'global', 'needs_review' => '1'],
            ['number' => '600', 'suffix' => '+', 'label' => 'Skilled Team Members', 'icon' => 'team', 'needs_review' => '1'],
        ],
        'intro_eyebrow' => 'ABOUT AURELIA',
        'intro_title' => 'Reliable manufacturing for brands built to grow.',
        'intro_description' => 'Aurelia brings product development, engineering validation, scalable production and global fulfillment together for international kitchen appliance brands. Clear processes, traceable quality control and dependable lead times turn product ideas into commercially ready ranges.',
        'timeline_title' => 'Manufacturing capability built over time',
        'timeline_description' => 'A focused progression across product development, quality systems, scalable production and global fulfillment.',
        'timeline' => [
            ['year' => '2010', 'title' => 'Manufacturing foundation', 'description' => 'Kitchen appliance production and supply-chain teams established.', 'needs_review' => '1'],
            ['year' => '2014', 'title' => 'OEM program growth', 'description' => 'An integrated process connected industrial design with production launch.', 'needs_review' => '1'],
            ['year' => '2018', 'title' => 'Capacity expansion', 'description' => 'Automated assembly and reliability testing capabilities expanded.', 'needs_review' => '1'],
            ['year' => '2022', 'title' => 'Global quality systems', 'description' => 'Laboratory validation and supplier traceability were strengthened.', 'needs_review' => '1'],
            ['year' => '2026', 'title' => 'Smart manufacturing program', 'description' => 'Digital production and multi-category platform development advanced.', 'needs_review' => '1'],
        ],
        'gallery_eyebrow' => 'INSIDE AURELIA',
        'gallery_title' => 'From engineering validation to global fulfillment',
        'gallery_description' => 'Explore the production, testing, showroom and logistics environments behind consistent quality and flexible customization.',
        'why_eyebrow' => 'OUR ADVANTAGE',
        'why_title' => 'A manufacturing system designed for brand programs',
        'why_description' => 'Cross-functional teams keep every critical stage visible, controlled and traceable.',
        'advantages' => [
            ['enable' => '1', 'icon' => 'shield', 'title' => 'Quality Assurance', 'description' => 'Process control from incoming materials to final performance testing.'],
            ['enable' => '1', 'icon' => 'equipment', 'title' => 'Advanced Equipment', 'description' => 'Standardized assembly, testing and packaging support stable output.'],
            ['enable' => '1', 'icon' => 'research', 'title' => 'R&D Collaboration', 'description' => 'Engineering support for structure, performance and compliance.'],
            ['enable' => '1', 'icon' => 'custom', 'title' => 'OEM/ODM Flexibility', 'description' => 'Appearance, function and packaging configured for each market.'],
            ['enable' => '1', 'icon' => 'delivery', 'title' => 'On-time Delivery', 'description' => 'Transparent planning and milestone management across each order.'],
            ['enable' => '1', 'icon' => 'support', 'title' => 'Global Support', 'description' => 'Responsive communication for international product programs.'],
        ],
        'products_eyebrow' => 'PRODUCT RANGE',
        'products_title' => 'Kitchen appliances engineered for global markets',
        'products_description' => 'Mature platforms and flexible configurations help brands build coherent product ranges faster.',
        'cases_eyebrow' => 'CASE STUDIES',
        'cases_title' => 'From product brief to market-ready program',
        'news_eyebrow' => 'LATEST NEWS',
        'news_title' => 'Product development, manufacturing and market insight',
        'cta_title' => 'Ready to start your next product program?',
        'cta_description' => 'Share your target market, feature direction and estimated volume. Our team will recommend the next practical step.',
        'cta_button' => 'Send Your Product Brief',
    ];

    $gallery_items = [];
    foreach (['hero_assembly', 'factory_closeup', 'quality_lab', 'warehouse', 'hero_showroom'] as $key) {
        $image = cb_catalog_image($images, $key);
        $definition = cb_demo_image_definitions()[$key] ?? [];
        $gallery_items[] = ['enable' => '1', 'image_id' => $image['id'], 'image_url' => $image['url'], 'image_alt' => $definition['alt'] ?? '', 'title' => '', 'description' => ''];
    }
    $cases = $is_zh ? [
        ['title' => '酒店早餐电器项目', 'description' => '统一设计语言的咖啡、加热与早餐电器组合。', 'image' => 'case_hospitality'],
        ['title' => '产线扩容项目', 'description' => '为新品上市建立可扩展的装配和质量流程。', 'image' => 'factory_closeup'],
        ['title' => '全球交付项目', 'description' => '从包装验证到多市场物流计划的一体化支持。', 'image' => 'warehouse'],
    ] : [
        ['title' => 'Hospitality breakfast program', 'description' => 'A coordinated family of coffee, heating and breakfast appliances.', 'image' => 'case_hospitality'],
        ['title' => 'Production scale-up program', 'description' => 'A scalable assembly and quality plan for a multi-product launch.', 'image' => 'factory_closeup'],
        ['title' => 'Global fulfillment program', 'description' => 'Integrated support from packaging validation to multi-market logistics.', 'image' => 'warehouse'],
    ];
    $case_items = array_map(static function ($item) use ($images) {
        $image = cb_catalog_image($images, $item['image']);
        return ['enable' => '1', 'title' => $item['title'], 'description' => $item['description'], 'image_id' => $image['id'], 'image_url' => $image['url'], 'url' => ''];
    }, $cases);
    $hero_slides = [];
    foreach (['hero_campus', 'hero_assembly', 'hero_showroom'] as $key) {
        $image = cb_catalog_image($images, $key);
        $definition = cb_demo_image_definitions()[$key];
        $hero_slides[] = array_merge(cb_hero_slide_defaults(), ['image_id' => $image['id'], 'image_url' => $image['url'], 'image_alt' => $definition['alt'], 'admin_label' => $definition['title']]);
    }
    $factory = cb_catalog_image($images, 'factory_closeup');
    $secondary = cb_catalog_image($images, 'quality_detail');
    $tertiary = cb_catalog_image($images, 'showroom_detail');
    $contact_url = home_url('/' . $language . '/' . ($is_zh ? 'contact-zh' : 'contact-us') . '/');

    return [
        array_merge(cb_default_builder_section('hero_slider'), cb_hero_section_defaults(), ['layout_style' => 'image_only_catalog', 'min_height_desktop' => '620px', 'min_height_mobile' => '380px', 'content_width' => '0px', 'autoplay_delay' => '6500', 'slides' => $hero_slides]),
        array_merge(cb_default_builder_section('company_stats'), ['layout_style' => 'minimal_matrix', 'items' => $copy['stats']]),
        array_merge(cb_default_builder_section('company_intro'), ['layout_style' => 'story_collage', 'eyebrow' => $copy['intro_eyebrow'], 'title' => $copy['intro_title'], 'description' => $copy['intro_description'], 'image_id' => $factory['id'], 'image_url' => $factory['url'], 'secondary_image_id' => $secondary['id'], 'secondary_image_url' => $secondary['url'], 'tertiary_image_id' => $tertiary['id'], 'tertiary_image_url' => $tertiary['url']]),
        array_merge(cb_default_builder_section('company_timeline'), ['layout_style' => 'full_width', 'title' => $copy['timeline_title'], 'description' => $copy['timeline_description'], 'items' => $copy['timeline']]),
        array_merge(cb_default_builder_section('showroom_gallery'), ['layout_style' => 'immersive', 'eyebrow' => $copy['gallery_eyebrow'], 'title' => $copy['gallery_title'], 'description' => $copy['gallery_description'], 'items' => $gallery_items]),
        array_merge(cb_default_builder_section('why_choose_us'), ['layout_style' => 'minimal_matrix', 'eyebrow' => $copy['why_eyebrow'], 'title' => $copy['why_title'], 'description' => $copy['why_description'], 'items' => $copy['advantages']]),
        array_merge(cb_default_builder_section('featured_products'), ['layout_style' => 'technical_catalog', 'eyebrow' => $copy['products_eyebrow'], 'title' => $copy['products_title'], 'description' => $copy['products_description'], 'limit' => '6', 'columns_desktop' => '3', 'columns_tablet' => '2', 'columns_mobile' => '1']),
        array_merge(cb_default_builder_section('case_studies'), ['layout_style' => 'editorial_grid', 'eyebrow' => $copy['cases_eyebrow'], 'title' => $copy['cases_title'], 'items' => $case_items]),
        array_merge(cb_default_builder_section('news_section'), ['layout_style' => 'spotlight', 'eyebrow' => $copy['news_eyebrow'], 'title' => $copy['news_title'], 'limit' => '3']),
        array_merge(cb_default_builder_section('inquiry_cta'), ['layout_style' => 'compact_band', 'title' => $copy['cta_title'], 'description' => $copy['cta_description'], 'items' => [['text' => $copy['cta_button'], 'url' => $contact_url, 'style' => 'primary']]]),
    ];
}

function cb_catalog_special_page_sections($role, $language, array $images)
{
    $home = cb_catalog_homepage_sections($language, $images);
    if ($role === 'about') {
        return array_values(array_filter($home, static fn($section) => in_array($section['type'], ['company_stats', 'company_intro', 'company_timeline', 'showroom_gallery', 'why_choose_us'], true)));
    }
    $is_zh = $language === 'zh';
    return [
        array_merge(cb_default_builder_section('contact_info'), [
            'layout_style' => 'split',
            'eyebrow' => $is_zh ? '联系我们' : 'CONTACT AURELIA',
            'title' => $is_zh ? '与我们的产品团队沟通' : 'Talk with our product team',
            'description' => $is_zh ? '分享产品方向、目标市场和预计采购量，我们将在一个工作日内回复。' : 'Share your product direction, target market and estimated volume. We will respond within one business day.',
            'items' => $is_zh ? [
                ['icon' => 'email', 'label' => '电子邮箱', 'value' => 'info@aureliamanufacturing.com', 'url' => 'mailto:info@aureliamanufacturing.com'],
                ['icon' => 'phone', 'label' => '电话', 'value' => '+86 000 0000 0000', 'url' => 'tel:+8600000000000'],
                ['icon' => 'location', 'label' => '地址', 'value' => '中国制造基地', 'url' => ''],
            ] : [
                ['icon' => 'email', 'label' => 'Email', 'value' => 'info@aureliamanufacturing.com', 'url' => 'mailto:info@aureliamanufacturing.com'],
                ['icon' => 'phone', 'label' => 'Phone', 'value' => '+86 000 0000 0000', 'url' => 'tel:+8600000000000'],
                ['icon' => 'location', 'label' => 'Manufacturing Base', 'value' => 'China', 'url' => ''],
            ],
        ]),
        array_merge(cb_default_builder_section('inquiry_cta'), ['layout_style' => 'default', 'title' => $is_zh ? '提交产品需求' : 'Send us your product brief', 'description' => $is_zh ? '填写以下信息，我们的团队会尽快联系您。' : 'Complete the form and our team will follow up with the right next step.']),
    ];
}

function cb_catalog_upsert_post($post_type, $seed_key, $language, array $data, array $image = [])
{
    $existing = get_posts([
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_cb_catalog_seed_key',
        'meta_value' => $seed_key . '-' . $language,
    ]);
    if (!$existing && !empty($data['post_title'])) {
        $by_title = get_posts([
            'post_type' => $post_type,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'title' => $data['post_title'],
            'meta_query' => [['key' => '_cb_language', 'value' => $language]],
        ]);
        if ($by_title) {
            $existing = [absint($by_title[0])];
        }
    }
    $post_id = absint($existing[0] ?? 0);
    if (!$post_id) {
        $post_id = wp_insert_post(array_merge([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_excerpt' => '',
            'post_content' => '',
        ], $data));
        if (!$post_id || is_wp_error($post_id)) {
            return 0;
        }
    } else {
        $update = ['ID' => $post_id];
        foreach (['post_excerpt', 'post_content'] as $field) {
            $current_value = (string) get_post_field($field, $post_id);
            $is_public_demo_notice = $field === 'post_content' && (str_contains($current_value, 'demonstration copy') || str_contains($current_value, '演示文案'));
            if ((empty($current_value) || $is_public_demo_notice) && !empty($data[$field])) {
                $update[$field] = $data[$field];
            }
        }
        if (count($update) > 1) {
            wp_update_post($update);
        }
    }
    update_post_meta($post_id, '_cb_language', $language);
    update_post_meta($post_id, '_cb_catalog_seed_key', $seed_key . '-' . $language);
    update_post_meta($post_id, '_cb_translation_group', 'aurelia-catalog-' . $seed_key);
    update_post_meta($post_id, '_cb_is_demo_content', '1');
    update_post_meta($post_id, '_cb_needs_content_review', '1');
    if (!empty($image['id']) && !get_post_thumbnail_id($post_id)) {
        set_post_thumbnail($post_id, absint($image['id']));
    }
    return $post_id;
}

function cb_install_catalog_videos(array $images)
{
    $videos = [
        [
            'key' => 'campus-tour',
            'title_en' => 'Aurelia Manufacturing Campus Tour',
            'title_zh' => 'Aurelia 制造基地参观',
            'excerpt_en' => 'Walk through the showroom, engineering areas and coordinated manufacturing environment behind Aurelia OEM/ODM programs.',
            'excerpt_zh' => '参观 Aurelia 的展厅、工程区域与协同制造环境，了解 OEM/ODM 项目的实施基础。',
            'category_en' => 'Company and Showroom',
            'category_zh' => '企业与展厅',
            'category_key' => 'company-showroom',
            'image' => 'hero_campus',
            'duration' => '02:48',
        ],
        [
            'key' => 'assembly-line',
            'title_en' => 'Flexible Assembly Line Overview',
            'title_zh' => '柔性装配线概览',
            'excerpt_en' => 'See how flexible work cells, process controls and production planning support multiple kitchen appliance platforms.',
            'excerpt_zh' => '了解柔性工作单元、过程控制与生产计划如何支持多种厨房电器平台。',
            'category_en' => 'Manufacturing',
            'category_zh' => '生产制造',
            'category_key' => 'manufacturing',
            'image' => 'hero_assembly',
            'duration' => '03:16',
        ],
        [
            'key' => 'quality-testing',
            'title_en' => 'Quality and Reliability Testing',
            'title_zh' => '质量与可靠性检测',
            'excerpt_en' => 'A practical view of performance, endurance and safety validation planned around the requirements of each target market.',
            'excerpt_zh' => '展示围绕目标市场要求开展的性能、耐久与安全验证流程。',
            'category_en' => 'Quality',
            'category_zh' => '质量检测',
            'category_key' => 'quality',
            'image' => 'quality_lab',
            'duration' => '02:35',
        ],
        [
            'key' => 'warehouse-delivery',
            'title_en' => 'Warehouse and Global Delivery',
            'title_zh' => '仓储与全球交付',
            'excerpt_en' => 'Follow the controls connecting finished-goods storage, packaging review, shipment release and international fulfillment.',
            'excerpt_zh' => '了解成品仓储、包装审核、出货放行与国际交付之间的管理流程。',
            'category_en' => 'Logistics',
            'category_zh' => '仓储物流',
            'category_key' => 'logistics',
            'image' => 'warehouse',
            'duration' => '02:12',
        ],
    ];

    foreach ($videos as $index => $video) {
        foreach (['en', 'zh'] as $language) {
            $is_zh = $language === 'zh';
            $title = $video['title_' . $language];
            $excerpt = $video['excerpt_' . $language];
            $category_name = $video['category_' . $language];
            $image = cb_catalog_image($images, $video['image']);
            $post_id = cb_catalog_upsert_post('video', 'video-' . $video['key'], $language, [
                'post_title' => $title,
                'post_name' => 'aurelia-' . $video['key'] . ($is_zh ? '-zh' : ''),
                'post_excerpt' => $excerpt,
                'post_content' => $is_zh
                    ? '<h2>影像内容概览</h2><p>' . $excerpt . '</p><h2>面向品牌项目的制造支持</h2><p>视频内容展示 Aurelia 的代表性制造环境与工作流程。具体设备、产能和项目条件应根据实际产品需求进一步确认。</p>'
                    : '<h2>What this video covers</h2><p>' . $excerpt . '</p><h2>Manufacturing support for brand programs</h2><p>This video profile presents representative Aurelia manufacturing environments and workflows. Specific equipment, capacity and program conditions are confirmed against each product brief.</p>',
                'menu_order' => $index + 1,
            ], $image);
            if (!$post_id) {
                continue;
            }
            update_post_meta($post_id, '_cb_short_description', $excerpt);
            update_post_meta($post_id, '_cb_video_duration', $video['duration']);
            update_post_meta($post_id, '_cb_display_order', (string) ($index + 1));
            update_post_meta($post_id, '_cb_featured', $index === 0 ? '1' : '0');

            $term = term_exists($category_name, 'video_category');
            if (!$term) {
                $term = wp_insert_term($category_name, 'video_category', [
                    'slug' => $video['category_key'] . ($is_zh ? '-zh' : ''),
                ]);
            }
            if (!is_wp_error($term)) {
                $term_id = absint(is_array($term) ? $term['term_id'] : $term);
                update_term_meta($term_id, '_cb_language', $language);
                update_term_meta($term_id, '_cb_translation_group', 'aurelia-video-category-' . $video['category_key']);
                update_term_meta($term_id, '_cb_is_demo_content', '1');
                wp_set_object_terms($post_id, [$term_id], 'video_category');
            }
        }
    }
}

function cb_install_catalog_content(array $images)
{
    $product_definitions = [
        ['air-fryer', 'Air Fryer 5.5L', '5.5升空气炸锅', 'AF-8001', 'Small Kitchen Appliances', '小型厨房电器', 'air_fryer', '1700W'],
        ['espresso-machine', 'Espresso Machine', '意式咖啡机', 'CM-3002', 'Coffee Machines', '咖啡机', 'espresso', '1450W'],
        ['stand-mixer', 'Stand Mixer 6.5L', '6.5升厨师机', 'SM-6003', 'Food Preparation', '食物处理', 'stand_mixer', '1500W'],
        ['blender', 'Blender 1.8L', '1.8升搅拌机', 'BL-1804', 'Food Preparation', '食物处理', 'blender', '1200W'],
        ['multi-cooker', 'Multi Cooker 5L', '5升多功能锅', 'MC-5005', 'Cooking Appliances', '烹饪电器', 'multi_cooker', '1000W'],
        ['electric-kettle', 'Electric Kettle 1.7L', '1.7升电水壶', 'EK-1706', 'Small Kitchen Appliances', '小型厨房电器', 'kettle', '1850-2200W'],
    ];
    foreach ($product_definitions as $product) {
        foreach (['en', 'zh'] as $language) {
            $is_zh = $language === 'zh';
            $title = $is_zh ? $product[2] : $product[1];
            $category_name = $is_zh ? $product[5] : $product[4];
            $description = $is_zh
                ? '面向品牌项目开发的成熟产品平台，支持外观、功能、包装和认证配置。'
                : 'A mature product platform for brand programs, with configurable appearance, functions, packaging and compliance.';
            $image = cb_catalog_image($images, $product[6]);
            $post_id = cb_catalog_upsert_post('product', 'product-' . $product[0], $language, [
                'post_title' => $title,
                'post_excerpt' => $description,
                'post_content' => '<h2>' . ($is_zh ? '为品牌项目而设计' : 'Engineered for brand programs') . '</h2><p>' . $description . '</p><h2>' . ($is_zh ? '制造与质量' : 'Manufacturing and quality') . '</h2><p>' . ($is_zh ? '每个项目均可根据目标市场建立材料、性能和包装验证计划。' : 'Each program can be supported by a validation plan covering materials, performance and packaging for the target market.') . '</p>',
            ], $image);
            if (!$post_id) {
                continue;
            }
            foreach ([
                '_cb_featured' => '1', '_cb_model' => $product[3], '_cb_brand' => 'Aurelia OEM/ODM',
                '_cb_voltage' => '220-240V / 110-120V', '_cb_power' => $product[7],
                '_cb_certification' => 'CE / CB / RoHS', '_cb_moq' => '500 units', '_cb_lead_time' => '35-45 days',
                '_cb_short_description' => $description, '_cb_inquiry_enabled' => '1',
                '_cb_specs' => ($is_zh ? "型号|{$product[3]}\n电压|220-240V / 110-120V\n功率|{$product[7]}\n定制|颜色、标识、包装" : "Model|{$product[3]}\nVoltage|220-240V / 110-120V\nPower|{$product[7]}\nCustomization|Color, logo and packaging"),
            ] as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
            update_post_meta($post_id, '_cb_gallery', [['title' => $title, 'description' => '', 'image_id' => $image['id'], 'image_url' => $image['url'], 'url' => '']]);
            $term = term_exists($category_name, 'product_category');
            if (!$term) {
                $term = wp_insert_term($category_name, 'product_category');
            }
            if (!is_wp_error($term)) {
                $term_id = absint(is_array($term) ? $term['term_id'] : $term);
                update_term_meta($term_id, '_cb_language', $language);
                update_term_meta($term_id, '_cb_is_demo_content', '1');
                wp_set_object_terms($post_id, [$term_id], 'product_category');
            }
        }
    }

    $entries = [
        'factory_showcase' => [
            ['assembly', 'Automated Assembly', '自动化装配', 'Flexible production cells support stable output across multiple appliance platforms.', '柔性生产单元支持多个产品平台的稳定量产。', 'factory_closeup'],
            ['quality', 'Quality and Reliability Laboratory', '质量与可靠性实验室', 'Performance, endurance and safety checks are planned around each target market.', '根据目标市场规划性能、耐久和安全验证。', 'quality_lab'],
            ['logistics', 'Warehouse and Global Fulfillment', '仓储与全球交付', 'Organized inventory and shipment controls support dependable international delivery.', '规范的库存与出货控制支持稳定的国际交付。', 'warehouse'],
        ],
        'case_study' => [
            ['hospitality', 'Hospitality Breakfast Appliance Program', '酒店早餐电器项目', 'A coordinated appliance family developed for a hospitality service environment.', '为酒店服务场景开发的统一电器产品组合。', 'case_hospitality'],
            ['scale-up', 'Multi-product Production Scale-up', '多产品量产扩容项目', 'Assembly, testing and packaging workflows aligned for a coordinated market launch.', '为协同上市建立装配、测试和包装流程。', 'hero_assembly'],
            ['fulfillment', 'Multi-market Fulfillment Program', '多市场交付项目', 'Packaging validation and shipment planning prepared for multiple destinations.', '面向多个目的地完成包装验证和出货规划。', 'warehouse'],
        ],
        'post' => [
            ['rd-process', 'How Aurelia Develops a New Appliance Platform', 'Aurelia 如何开发新的电器平台', 'A practical look at product definition, engineering review and validation planning.', '了解产品定义、工程评审和验证规划的实际流程。', 'news_rd'],
            ['quality-plan', 'Building a Reliable Quality Plan for OEM Projects', '如何为 OEM 项目建立可靠的质量计划', 'Quality planning begins before tooling and follows the product through production.', '质量规划始于开模之前，并贯穿整个生产过程。', 'quality_lab'],
            ['delivery-readiness', 'Preparing Kitchen Appliances for Global Delivery', '厨房电器全球交付准备', 'Packaging, documentation and shipment controls that reduce launch risk.', '通过包装、文件和出货控制降低上市风险。', 'warehouse'],
        ],
    ];
    foreach ($entries as $post_type => $rows) {
        foreach ($rows as $row) {
            foreach (['en', 'zh'] as $language) {
                $is_zh = $language === 'zh';
                $title = $is_zh ? $row[2] : $row[1];
                $excerpt = $is_zh ? $row[4] : $row[3];
                cb_catalog_upsert_post($post_type, $post_type . '-' . $row[0], $language, [
                    'post_title' => $title,
                    'post_excerpt' => $excerpt,
                    'post_content' => '<h2>' . ($is_zh ? '项目概述' : 'Program overview') . '</h2><p>' . $excerpt . '</p><p>' . ($is_zh ? '跨职能团队以清晰的工程评审、质量验证和交付计划支持项目推进。' : 'Cross-functional teams support each program with clear engineering reviews, quality validation and delivery planning.') . '</p>',
                ], cb_catalog_image($images, $row[5]));
            }
        }
    }
    cb_install_catalog_videos($images);
    cb_install_catalog_subpages($images);
}

function cb_catalog_gallery_items(array $images, array $keys, $language)
{
    $definitions = cb_demo_image_definitions();
    $items = [];
    foreach ($keys as $key) {
        $image = cb_catalog_image($images, $key);
        if (empty($image['url'])) {
            continue;
        }
        $items[] = [
            'enable' => '1',
            'image_id' => absint($image['id'] ?? 0),
            'image_url' => $image['url'],
            'image_alt' => $definitions[$key]['alt'] ?? '',
            'caption' => $language === 'zh' ? 'Aurelia 制造与质量环境' : 'Aurelia manufacturing and quality environment',
        ];
    }
    return $items;
}

function cb_catalog_subpage_cta($language)
{
    $is_zh = $language === 'zh';
    return array_merge(cb_default_builder_section('inquiry_cta'), [
        'layout_style' => 'compact_band',
        'title' => $is_zh ? '准备讨论您的产品项目？' : 'Ready to discuss your product program?',
        'description' => $is_zh ? '分享目标市场、产品方向和预计采购量，我们将建议下一步。' : 'Share your target market, product direction and estimated volume. We will recommend the next practical step.',
        'items' => [[
            'text' => $is_zh ? '提交产品需求' : 'Send Product Brief',
            'url' => cb_catalog_contact_url($language),
            'style' => 'primary',
        ]],
    ]);
}

function cb_catalog_contact_url($language)
{
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $post_id = absint($special[$language]['contact'] ?? 0);
    return $post_id ? get_permalink($post_id) : home_url('/' . $language . '/contact/');
}

function cb_install_catalog_subpages(array $images)
{
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $page_records = [
        'company-style' => [
            'en' => [
                'title' => 'Company Style', 'slug' => 'company-style',
                'excerpt' => 'A closer view of Aurelia teams, manufacturing environments and product development culture.',
                'content' => '<h2>Built around practical manufacturing</h2><p>Aurelia brings product planning, engineering coordination, pilot production, quality validation and fulfillment into one collaborative workflow. Our teams work from clear project gates so international brand partners can review decisions early and keep programs moving.</p><h2>People, process and place</h2><p>From engineering workstations to organized assembly cells and inspection areas, each environment is arranged to support repeatable work and transparent communication. The gallery below presents representative demo imagery that should be replaced or approved before production launch.</p>',
                'banner' => 'hero_campus', 'gallery' => ['hero_assembly', 'factory_closeup', 'factory_detail', 'quality_lab', 'warehouse', 'hero_showroom'],
            ],
            'zh' => [
                'title' => '企业风采', 'slug' => 'company-style-zh',
                'excerpt' => '了解 Aurelia 的团队、制造环境与产品开发文化。',
                'content' => '<h2>以务实制造为核心</h2><p>Aurelia 将产品规划、工程协同、试产、质量验证与交付整合到清晰的项目流程中，帮助国际品牌在关键阶段及时评审并稳步推进项目。</p><h2>团队、流程与环境</h2><p>从工程工作区到装配单元和检验区域，每个环境都围绕标准化作业与透明沟通进行组织。以下图片为代表性演示素材，正式上线前应完成确认或替换。</p>',
                'banner' => 'hero_campus', 'gallery' => ['hero_assembly', 'factory_closeup', 'factory_detail', 'quality_lab', 'warehouse', 'hero_showroom'],
            ],
        ],
        'faq' => [
            'en' => [
                'title' => 'Frequently Asked Questions', 'slug' => 'faq',
                'excerpt' => 'Practical answers for OEM and ODM kitchen appliance programs.',
                'content' => '<h2>Planning an OEM/ODM program</h2><p>These answers cover the questions brand teams most often raise during early evaluation. Final commercial, compliance and delivery terms are confirmed for each product brief.</p>',
                'banner' => 'quality_detail',
                'items' => [
                    ['question' => 'What information should we include in a product brief?', 'answer' => 'Share the target market, product category, key functions, expected price position, estimated volume, launch timing and any required compliance standards.'],
                    ['question' => 'Can Aurelia support both OEM and ODM projects?', 'answer' => 'Yes. Programs can begin with an existing platform, a customized platform or a jointly defined product direction, subject to technical review.'],
                    ['question' => 'How are samples and prototypes managed?', 'answer' => 'The team confirms scope, review criteria and timing before each sample round, then records feedback for the next engineering decision.'],
                    ['question' => 'How is product quality planned?', 'answer' => 'Quality planning covers incoming materials, process controls, performance checks, reliability validation and final inspection for the agreed market.'],
                    ['question' => 'Can colors, branding and packaging be customized?', 'answer' => 'Available customization depends on the selected platform and order plan. Color, logo, accessories, manuals and packaging can be reviewed together.'],
                    ['question' => 'How is delivery timing confirmed?', 'answer' => 'Timing is based on engineering status, tooling, compliance, material readiness, pilot approval and production capacity. A project schedule is confirmed before order execution.'],
                ],
            ],
            'zh' => [
                'title' => '常见问题', 'slug' => 'faq-zh',
                'excerpt' => '厨房电器 OEM/ODM 项目的常见问题与实用说明。',
                'content' => '<h2>规划 OEM/ODM 项目</h2><p>以下内容涵盖品牌团队在项目评估初期最常提出的问题。最终商务、合规与交付条件将根据具体产品需求确认。</p>',
                'banner' => 'quality_detail',
                'items' => [
                    ['question' => '产品需求书应包含哪些信息？', 'answer' => '建议提供目标市场、产品类别、核心功能、价格定位、预计采购量、上市时间及所需合规标准。'],
                    ['question' => 'Aurelia 是否同时支持 OEM 和 ODM？', 'answer' => '支持。项目可基于成熟平台、定制平台或双方共同定义的产品方向开展，并需经过技术评审。'],
                    ['question' => '样机与原型如何管理？', 'answer' => '每轮样机前确认范围、评审标准和时间，并记录反馈以支持下一阶段工程决策。'],
                    ['question' => '产品质量如何规划？', 'answer' => '质量计划覆盖来料、过程控制、性能检测、可靠性验证和面向目标市场的最终检验。'],
                    ['question' => '是否可以定制颜色、品牌与包装？', 'answer' => '可定制范围取决于产品平台与订单计划，可共同评审颜色、标识、配件、说明书和包装。'],
                    ['question' => '交付周期如何确认？', 'answer' => '交期根据工程状态、模具、合规、物料、试产批准和产能综合确认，并在订单执行前建立项目计划。'],
                ],
            ],
        ],
        'service' => [
            'en' => [
                'title' => 'Service', 'slug' => 'service',
                'excerpt' => 'Coordinated support from product definition through manufacturing and delivery.',
                'content' => '<h2>Support across the product lifecycle</h2><p>Industrial product programs succeed when commercial, engineering, quality and delivery decisions stay connected. Aurelia assigns clear project ownership and organizes reviews around the milestones that matter to brand teams.</p><h2>From first brief to repeat order</h2><p>Support can include platform selection, requirement alignment, design coordination, tooling follow-up, sample review, compliance preparation, production planning and after-sales issue analysis.</p>',
                'banner' => 'hero_showroom',
                'services' => [['Requirement review', 'Align market, positioning, functions and compliance.'], ['Engineering coordination', 'Connect industrial design, structure and manufacturability.'], ['Sample management', 'Plan prototype rounds and record review decisions.'], ['Quality planning', 'Define validation and inspection gates before production.'], ['Delivery coordination', 'Connect materials, capacity, packaging and shipment plans.'], ['After-sales support', 'Track issues and coordinate corrective follow-up.']],
            ],
            'zh' => [
                'title' => '服务保障', 'slug' => 'service-zh',
                'excerpt' => '从产品定义到制造与交付的协同支持。',
                'content' => '<h2>覆盖产品全生命周期的支持</h2><p>工业产品项目需要商务、工程、质量和交付决策持续协同。Aurelia 明确项目责任，并围绕品牌团队关注的关键节点组织评审。</p><h2>从首次需求到持续订单</h2><p>服务可覆盖平台选择、需求梳理、设计协同、模具跟进、样机评审、合规准备、生产计划与售后问题分析。</p>',
                'banner' => 'hero_showroom',
                'services' => [['需求评审', '梳理市场、定位、功能与合规要求。'], ['工程协同', '协调工业设计、结构与可制造性。'], ['样机管理', '规划原型轮次并记录评审结论。'], ['质量规划', '量产前定义验证与检验节点。'], ['交付协同', '连接物料、产能、包装与出货计划。'], ['售后支持', '跟踪问题并协调纠正措施。']],
            ],
        ],
        'delivery' => [
            'en' => [
                'title' => 'Delivery', 'slug' => 'delivery',
                'excerpt' => 'A controlled path from approved order to international shipment.',
                'content' => '<h2>Delivery starts before production</h2><p>Reliable shipment depends on early alignment of materials, packaging, documentation, inspection and destination requirements. Aurelia reviews these dependencies as part of the project plan rather than waiting until goods are complete.</p><h2>Visible milestones and clear handoffs</h2><p>Order status is managed through material readiness, pilot approval, production, final inspection, packing and shipment release. Actual timing is confirmed for each program.</p>',
                'banner' => 'warehouse', 'gallery' => ['warehouse', 'warehouse_detail', 'case_hospitality_detail'],
            ],
            'zh' => [
                'title' => '准时交付', 'slug' => 'delivery-zh',
                'excerpt' => '从订单批准到国际出货的可控流程。',
                'content' => '<h2>交付管理始于生产之前</h2><p>可靠出货依赖物料、包装、文件、检验和目的地要求的提前协同。Aurelia 将这些依赖纳入项目计划，而不是等产品完成后再处理。</p><h2>清晰节点与明确交接</h2><p>订单状态按照物料准备、试产批准、生产、最终检验、包装和放行进行管理，实际周期根据具体项目确认。</p>',
                'banner' => 'warehouse', 'gallery' => ['warehouse', 'warehouse_detail', 'case_hospitality_detail'],
            ],
        ],
    ];

    foreach ($page_records as $seed_key => $translations) {
        foreach ($translations as $language => $record) {
            $parent_id = absint($special[$language]['about'] ?? 0);
            $banner = cb_catalog_image($images, $record['banner']);
            $page_id = cb_catalog_upsert_post('page', 'subpage-' . $seed_key, $language, [
                'post_title' => $record['title'],
                'post_name' => $record['slug'],
                'post_parent' => $parent_id,
                'post_excerpt' => $record['excerpt'],
                'post_content' => $record['content'],
            ], $banner);
            if (!$page_id) {
                continue;
            }
            $sections = [cb_default_builder_section('content_editor')];
            if ($seed_key === 'faq') {
                $sections[] = array_merge(cb_default_builder_section('faq_list'), ['layout_style' => 'numbered_list', 'items' => $record['items']]);
            } elseif ($seed_key === 'service') {
                $items = array_map(static fn($item) => ['enable' => '1', 'icon' => '', 'title' => $item[0], 'description' => $item[1], 'url' => ''], $record['services']);
                $sections[] = array_merge(cb_default_builder_section('why_choose_us'), ['layout_style' => 'service_matrix', 'items' => $items]);
            } elseif (!empty($record['gallery'])) {
                $sections[] = array_merge(cb_default_builder_section('gallery'), ['layout_style' => 'editorial_stack', 'items' => cb_catalog_gallery_items($images, $record['gallery'], $language)]);
            }
            $sections[] = cb_catalog_subpage_cta($language);
            if (!cb_get_page_sections($page_id)) {
                update_post_meta($page_id, '_cb_page_sections', cb_sanitize_page_sections($sections));
            }
            $page_ui = get_post_meta($page_id, '_cb_page_ui', true);
            $page_ui = is_array($page_ui) ? $page_ui : [];
            $page_ui += [
                'page_layout' => 'sidebar', 'show_banner' => '1', 'show_breadcrumb' => '1',
                'banner_title' => $record['title'], 'banner_description' => $record['excerpt'],
                'banner_image' => $banner['url'] ?? '', 'banner_image_id' => absint($banner['id'] ?? 0),
                'banner_overlay' => '58', 'banner_height_desktop' => '330px', 'banner_height_mobile' => '240px',
            ];
            update_post_meta($page_id, '_cb_page_ui', $page_ui);
            update_post_meta($page_id, '_cb_page_render_mode', 'builder');
            update_post_meta($page_id, '_cb_catalog_subpage_version', '1.6.0');
        }
    }

    cb_install_factory_subpages($images);
}

function cb_install_factory_subpages(array $images)
{
    $records = [
        'production-equipment' => ['Production Equipment', '生产设备', 'Equipment planning supports repeatable appliance manufacturing across forming, assembly and finishing processes.', '设备规划覆盖成型、装配与表面处理等环节，为稳定制造提供支持。', 'factory_detail', ['factory_detail', 'factory_closeup', 'hero_assembly']],
        'production-line' => ['Production Line', '生产线', 'Flexible assembly cells connect standardized work, process checks and visible production control.', '柔性装配单元连接标准作业、过程检查与可视化生产管理。', 'hero_assembly', ['hero_assembly', 'factory_closeup', 'factory_detail']],
        'production-workshop' => ['Production Workshop', '生产车间', 'Organized workshop zones support material flow, assembly, inspection and finished-goods handoff.', '规范的车间分区支持物料流转、装配、检验与成品交接。', 'factory_closeup', ['factory_closeup', 'factory_detail', 'warehouse']],
        'testing-equipment' => ['Testing Equipment', '检测设备', 'Performance and reliability equipment supports project-specific validation and quality decisions.', '性能与可靠性设备支持针对项目的验证与质量决策。', 'quality_lab', ['quality_lab', 'quality_detail', 'factory_detail']],
    ];
    foreach ($records as $seed_key => $record) {
        foreach (['en', 'zh'] as $language) {
            $is_zh = $language === 'zh';
            $title = $record[$is_zh ? 1 : 0];
            $excerpt = $record[$is_zh ? 3 : 2];
            $content = $is_zh
                ? '<h2>' . $title . '</h2><p>' . $excerpt . '</p><h2>面向项目的制造管理</h2><p>实际设备配置、产能与检测范围将根据产品平台和客户需求确认。Aurelia 通过工程评审、过程控制和质量记录支持项目实施。</p>'
                : '<h2>' . $title . '</h2><p>' . $excerpt . '</p><h2>Manufacturing management for each program</h2><p>Actual equipment configuration, capacity and test scope are confirmed against the selected product platform and customer requirements. Aurelia supports execution through engineering reviews, process controls and quality records.</p>';
            $image = cb_catalog_image($images, $record[4]);
            $post_id = cb_catalog_upsert_post('factory_showcase', 'factory-showcase-' . $seed_key, $language, [
                'post_title' => $title,
                'post_name' => $seed_key . ($is_zh ? '-zh' : ''),
                'post_excerpt' => $excerpt,
                'post_content' => $content,
            ], $image);
            if (!$post_id) {
                continue;
            }
            if (get_post_meta($post_id, '_cb_is_demo_content', true) === '1') {
                wp_update_post(['ID' => $post_id, 'post_name' => $seed_key . ($is_zh ? '-zh' : '')]);
            }
            if (!get_post_meta($post_id, '_cb_gallery', true)) {
                $gallery = [];
                foreach (cb_catalog_gallery_items($images, $record[5], $language) as $item) {
                    $gallery[] = ['title' => $item['caption'], 'description' => '', 'image_id' => $item['image_id'], 'image_url' => $item['image_url'], 'image_alt' => $item['image_alt'], 'url' => ''];
                }
                update_post_meta($post_id, '_cb_gallery', $gallery);
            }
            update_post_meta($post_id, '_cb_featured', '1');
            update_post_meta($post_id, '_cb_catalog_subpage_version', '1.6.0');
        }
    }
}

function cb_install_demo_content()
{
    $images = cb_install_demo_images(false);
    cb_install_catalog_content($images);
    $manifest = wp_parse_args((array) get_option('cb_demo_content_manifest', []), [
        'posts' => [], 'terms' => [], 'about_pages' => [], 'home_page' => 0,
        'home_sections_hash' => '', 'installed_at' => current_time('mysql'),
    ]);
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $home_id = absint($special['en']['home'] ?? 0);
    if ($home_id && !cb_get_page_sections($home_id)) {
        $demo_sections = cb_sanitize_page_sections(cb_demo_homepage_sections($images));
        update_post_meta($home_id, '_cb_page_sections', $demo_sections);
        update_post_meta($home_id, '_cb_page_render_mode', 'builder');
        update_post_meta($home_id, '_cb_demo_sections_installed', '1');
        $manifest['home_page'] = $home_id;
        $manifest['home_sections_hash'] = md5(serialize($demo_sections));
    }

    $categories = [
        'Small Kitchen Appliances' => 'Air fryers, blenders and kettles.',
        'Coffee Machines' => 'Coffee makers and espresso machines.',
    ];
    foreach ($categories as $name => $description) {
        if (term_exists($name, 'product_category')) {
            continue;
        }
        $term = wp_insert_term($name, 'product_category', ['description' => $description]);
        if (!is_wp_error($term)) {
            $term_id = absint($term['term_id']);
            $image_key = cb_demo_product_image_key($name, $term_id);
            update_term_meta($term_id, '_cb_language', 'en');
            update_term_meta($term_id, '_cb_is_demo_content', '1');
            update_term_meta($term_id, '_cb_banner_image', $images[$image_key]['url'] ?? '');
            $manifest['terms'][] = $term_id;
        }
    }

    $products = [
        ['Air Fryer 5.5L', 'AF-8001', 'Small Kitchen Appliances', 'air_fryer'],
        ['Espresso Machine', 'CM-3002', 'Coffee Machines', 'espresso'],
    ];
    foreach ($products as $product) {
        if (get_page_by_title($product[0], OBJECT, 'product')) {
            continue;
        }
        $post_id = wp_insert_post([
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_title' => $product[0],
            'post_excerpt' => 'Demo product for the CB Company showcase.',
            'post_content' => 'Demo content. Replace this item with your production product data.',
        ]);
        if ($post_id && !is_wp_error($post_id)) {
            $image = $images[$product[3]] ?? ['id' => 0, 'url' => ''];
            update_post_meta($post_id, '_cb_is_demo_content', '1');
            update_post_meta($post_id, '_cb_language', 'en');
            update_post_meta($post_id, '_cb_featured', '1');
            update_post_meta($post_id, '_cb_model', $product[1]);
            update_post_meta($post_id, '_cb_gallery', [[
                'title' => $product[0], 'description' => '', 'image_id' => $image['id'], 'image_url' => $image['url'], 'url' => '',
            ]]);
            if ($image['id']) {
                set_post_thumbnail($post_id, $image['id']);
            }
            wp_set_object_terms($post_id, $product[2], 'product_category');
            $manifest['posts'][] = absint($post_id);
        }
    }
    $certificate_ids = cb_install_demo_certificates($images);
    $manifest['posts'] = array_values(array_unique(array_merge((array) $manifest['posts'], $certificate_ids)));
    cb_seed_about_demo_visuals($images, $manifest);
    cb_install_demo_menus();
    $manifest['installed_at'] = current_time('mysql');
    update_option('cb_demo_content_manifest', $manifest, false);
    update_option('cb_demo_content_installed', '1', false);
    return $manifest;
}

function cb_install_demo_certificates($images = [])
{
    if (!$images) {
        $images = cb_install_demo_images(false);
    }
    $records = [
        'quality-process' => ['Quality Process Review', '质量流程评审', 'DEMO-QP-001', 'quality-systems', 'demo_certificate_quality'],
        'product-safety' => ['Product Safety Test Summary', '产品安全测试摘要', 'DEMO-PS-002', 'product-compliance', 'demo_certificate_safety'],
        'supplier-audit' => ['Supplier Audit Report', '供应商审核报告', 'DEMO-SA-003', 'quality-systems', 'demo_certificate_supplier'],
        'rd-validation' => ['R&D Validation Record', '研发验证记录', 'DEMO-RD-004', 'patents-design', 'demo_certificate_rd'],
        'packaging-review' => ['Packaging Reliability Review', '包装可靠性评审', 'DEMO-PR-005', 'product-compliance', 'demo_certificate_packaging'],
        'export-checklist' => ['Export Documentation Checklist', '出口文件检查清单', 'DEMO-ED-006', 'awards-qualifications', 'demo_certificate_export'],
    ];
    $post_ids = [];
    foreach ($records as $key => $record) {
        $translations = [];
        foreach (['en', 'zh'] as $language) {
            $existing = get_posts([
                'post_type' => 'certificate', 'post_status' => 'any', 'posts_per_page' => 1,
                'fields' => 'ids', 'meta_query' => [
                    ['key' => '_cb_demo_certificate_key', 'value' => $key],
                    ['key' => '_cb_language', 'value' => $language],
                ],
            ]);
            $is_zh = $language === 'zh';
            $title = $is_zh ? $record[1] : $record[0];
            $excerpt = $is_zh
                ? '用于演示 Aurelia 文档库界面，不代表真实认证或合规声明。'
                : 'A sample record for previewing the Aurelia document library. It is not a certification or compliance claim.';
            $post_data = [
                'post_type' => 'certificate', 'post_status' => 'draft',
                'post_title' => $title, 'post_name' => 'demo-' . $key . '-' . $language,
                'post_excerpt' => $excerpt,
                'post_content' => '<p>' . $excerpt . '</p><h2>' . ($is_zh ? '演示用途' : 'Demo purpose') . '</h2><p>' . ($is_zh ? '请在正式上线前替换为经审核的真实文件和元数据。' : 'Replace this record with an approved document and verified metadata before production publication.') . '</p>',
                'menu_order' => array_search($key, array_keys($records), true),
            ];
            if ($existing) {
                $post_data['ID'] = absint($existing[0]);
                $post_id = wp_update_post($post_data);
            } else {
                $post_id = wp_insert_post($post_data);
            }
            if (!$post_id || is_wp_error($post_id)) {
                continue;
            }
            $image = $images[$record[4]] ?? ['id' => 0, 'url' => ''];
            update_post_meta($post_id, '_cb_is_demo_content', '1');
            update_post_meta($post_id, '_cb_demo_certificate_key', $key);
            update_post_meta($post_id, '_cb_language', $language);
            update_post_meta($post_id, '_cb_translation_group', 'demo-certificate-' . $key);
            update_post_meta($post_id, '_cb_issuer', $is_zh ? 'Aurelia 内部评审团队（演示）' : 'Aurelia Internal Review Team (Demo)');
            update_post_meta($post_id, '_cb_standard', $record[2]);
            update_post_meta($post_id, '_cb_certificate_number', 'SAMPLE-' . strtoupper(substr(md5($key), 0, 8)));
            update_post_meta($post_id, '_cb_issue_date', '2026-01-15');
            update_post_meta($post_id, '_cb_expiry_date', '');
            update_post_meta($post_id, '_cb_featured', '1');
            update_post_meta($post_id, '_cb_needs_content_review', '1');
            if (!empty($image['id'])) {
                set_post_thumbnail($post_id, absint($image['id']));
            }
            $term_slug = $record[3] . ($is_zh ? '-zh' : '');
            $term = get_term_by('slug', $term_slug, 'certificate_category');
            if ($term) {
                wp_set_object_terms($post_id, [(int) $term->term_id], 'certificate_category');
            }
            wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
            $translations[$language] = absint($post_id);
            $post_ids[] = absint($post_id);
        }
        if (!empty($translations['en']) && !empty($translations['zh'])) {
            update_post_meta($translations['en'], '_cb_translated_post_zh', $translations['zh']);
            update_post_meta($translations['zh'], '_cb_translated_post_en', $translations['en']);
        }
    }
    return array_values(array_unique($post_ids));
}

function cb_install_demo_certificate_package()
{
    $images = cb_install_demo_images(false);
    $post_ids = cb_install_demo_certificates($images);
    $manifest = wp_parse_args((array) get_option('cb_demo_content_manifest', []), ['posts' => [], 'terms' => [], 'about_pages' => []]);
    $manifest['posts'] = array_values(array_unique(array_merge((array) $manifest['posts'], $post_ids)));
    cb_seed_about_demo_visuals($images, $manifest);
    $manifest['installed_at'] = current_time('mysql');
    update_option('cb_demo_content_manifest', $manifest, false);
    update_option('cb_demo_content_installed', '1', false);
    return $post_ids;
}

function cb_seed_about_demo_visuals($images, &$manifest)
{
    $banner = $images['hero_campus'] ?? [];
    if (empty($banner['url'])) {
        return;
    }
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    foreach (['en', 'zh'] as $language) {
        $page_id = absint($special[$language]['about'] ?? 0);
        if (!$page_id) {
            continue;
        }
        $sections = cb_get_page_sections($page_id);
        $section_changed = false;
        foreach ($sections as &$section) {
            if (($section['type'] ?? '') !== 'certificates') {
                continue;
            }
            $legacy_description = $language === 'zh'
                ? '已审核并正式发布的体系、产品和企业资质文件。'
                : 'Reviewed system, product and corporate qualification documents published by Aurelia.';
            if (($section['description'] ?? '') === $legacy_description || empty($section['description'])) {
                $section['description'] = $language === 'zh'
                    ? '集中展示质量、工程与合规文件；演示记录须在正式上线前替换。'
                    : 'A structured library for quality, engineering and compliance documents. Replace demo records before production launch.';
                $section_changed = true;
            }
        }
        unset($section);
        if ($section_changed) {
            update_post_meta($page_id, '_cb_page_sections', cb_sanitize_page_sections($sections));
        }
        $page_ui = get_post_meta($page_id, '_cb_page_ui', true);
        $page_ui = is_array($page_ui) ? $page_ui : [];
        if (!empty($page_ui['banner_image']) && !cb_demo_image_is_replaceable($page_ui['banner_image'])) {
            continue;
        }
        if (empty($manifest['about_pages'][$page_id])) {
            $manifest['about_pages'][$page_id]['before'] = $page_ui;
        }
        $page_ui['banner_image'] = $banner['url'];
        $page_ui['banner_image_id'] = absint($banner['id'] ?? 0);
        $page_ui['banner_overlay'] = '58';
        $page_ui['banner_height_desktop'] = '330px';
        $page_ui['banner_height_mobile'] = '240px';
        if (empty($page_ui['banner_description'])) {
            $page_ui['banner_description'] = $language === 'zh'
                ? '工程、质量和柔性制造能力，为全球品牌提供支持。'
                : 'Engineering, quality and flexible manufacturing for ambitious global brands.';
        }
        update_post_meta($page_id, '_cb_page_ui', $page_ui);
        $manifest['about_pages'][$page_id]['applied_hash'] = md5(serialize($page_ui));
    }
}

function cb_delete_demo_certificates()
{
    $posts = get_posts([
        'post_type' => 'certificate', 'post_status' => 'any', 'posts_per_page' => -1,
        'fields' => 'ids', 'meta_key' => '_cb_is_demo_content', 'meta_value' => '1',
    ]);
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }
    $manifest = (array) get_option('cb_demo_content_manifest', []);
    if (!empty($manifest['posts'])) {
        $manifest['posts'] = array_values(array_diff(array_map('absint', (array) $manifest['posts']), array_map('absint', $posts)));
        update_option('cb_demo_content_manifest', $manifest, false);
    }
    return count($posts);
}

function cb_delete_demo_content()
{
    $posts = get_posts([
        'post_type' => ['post', 'page', 'product', 'factory_showcase', 'case_study', 'video', 'certificate'],
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_cb_is_demo_content',
        'meta_value' => '1',
    ]);
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }
    $terms = get_terms(['taxonomy' => 'product_category', 'hide_empty' => false, 'meta_key' => '_cb_is_demo_content', 'meta_value' => '1']);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'product_category');
        }
    }
    $manifest = (array) get_option('cb_demo_content_manifest', []);
    foreach ((array) ($manifest['about_pages'] ?? []) as $page_id => $snapshot) {
        $current = get_post_meta(absint($page_id), '_cb_page_ui', true);
        if (!empty($snapshot['applied_hash']) && hash_equals((string) $snapshot['applied_hash'], md5(serialize(is_array($current) ? $current : [])))) {
            update_post_meta(absint($page_id), '_cb_page_ui', (array) ($snapshot['before'] ?? []));
        }
    }
    $home_id = absint($manifest['home_page'] ?? 0);
    if ($home_id && get_post_meta($home_id, '_cb_demo_sections_installed', true) === '1') {
        $current_sections = get_post_meta($home_id, '_cb_page_sections', true);
        $current_hash = md5(serialize(is_array($current_sections) ? $current_sections : []));
        if (!empty($manifest['home_sections_hash']) && hash_equals((string) $manifest['home_sections_hash'], $current_hash)) {
            delete_post_meta($home_id, '_cb_page_sections');
        }
        delete_post_meta($home_id, '_cb_demo_sections_installed');
    }
    cb_delete_demo_images();
    cb_delete_demo_menus();
    delete_option('cb_demo_content_manifest');
    delete_option('cb_demo_content_installed');
}

function cb_demo_content_status()
{
    $posts = get_posts(['post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_cb_is_demo_content', 'meta_value' => '1']);
    return ['installed' => get_option('cb_demo_content_installed') === '1', 'post_count' => count($posts), 'manifest' => (array) get_option('cb_demo_content_manifest', [])];
}

function cb_demo_menu_term_item($taxonomy, $language, $title, $fallback_url)
{
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'name' => $title,
        'number' => 5,
    ]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_language = (string) get_term_meta($term->term_id, '_cb_language', true);
            if ($term_language && $term_language !== $language) {
                continue;
            }
            return [
                'title' => $title,
                'term_id' => absint($term->term_id),
                'taxonomy' => $taxonomy,
            ];
        }
    }
    return ['title' => $title, 'url' => $fallback_url];
}

function cb_demo_menu_catalog_item($post_type, $seed_key, $language, $title, $fallback_url)
{
    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_cb_catalog_seed_key',
        'meta_value' => $seed_key . '-' . $language,
    ]);
    if ($posts) {
        return ['title' => $title, 'object_id' => absint($posts[0])];
    }
    return ['title' => $title, 'url' => $fallback_url];
}

function cb_demo_menu_definitions()
{
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $definitions = [];
    foreach (['en', 'zh'] as $language) {
        $is_zh = $language === 'zh';
        $about_id = absint($special[$language]['about'] ?? 0);
        $contact_id = absint($special[$language]['contact'] ?? 0);
        $about_url = $about_id ? get_permalink($about_id) : home_url('/' . $language . '/about-us/');
        $product_url = home_url('/' . $language . '/products/');
        $factory_url = home_url('/' . $language . '/factory/');
        $product_names = $is_zh
            ? ['小型厨房电器', '咖啡机', '食物处理', '烹饪电器']
            : ['Small Kitchen Appliances', 'Coffee Machines', 'Food Preparation', 'Cooking Appliances'];
        $product_children = [];
        foreach ($product_names as $product_name) {
            $product_children[] = cb_demo_menu_term_item('product_category', $language, $product_name, $product_url);
        }
        $factory_children = [
            cb_demo_menu_catalog_item('factory_showcase', 'factory-showcase-production-equipment', $language, $is_zh ? '生产设备' : 'Production Equipment', $factory_url),
            cb_demo_menu_catalog_item('factory_showcase', 'factory-showcase-production-line', $language, $is_zh ? '生产线' : 'Production Line', $factory_url),
            cb_demo_menu_catalog_item('factory_showcase', 'factory-showcase-production-workshop', $language, $is_zh ? '生产车间' : 'Production Workshop', $factory_url),
            cb_demo_menu_catalog_item('factory_showcase', 'factory-showcase-testing-equipment', $language, $is_zh ? '检测设备' : 'Testing Equipment', $factory_url),
        ];
        $about_children = [
            ['title' => $is_zh ? '公司概况' : 'Company Overview', 'object_id' => $about_id],
            cb_demo_menu_catalog_item('page', 'subpage-company-style', $language, $is_zh ? '企业风采' : 'Company Style', $about_url),
            ['title' => $is_zh ? '资质证书' : 'Certificates', 'url' => home_url('/' . $language . '/certificates/')],
            cb_demo_menu_catalog_item('page', 'subpage-faq', $language, $is_zh ? '常见问题' : 'FAQ', $about_url),
            cb_demo_menu_catalog_item('page', 'subpage-service', $language, $is_zh ? '服务保障' : 'Service', $about_url),
            cb_demo_menu_catalog_item('page', 'subpage-delivery', $language, $is_zh ? '准时交付' : 'Delivery', $about_url),
        ];
        $primary = [
            ['title' => $is_zh ? '首页' : 'Home', 'url' => home_url('/' . $language . '/')],
            ['title' => $is_zh ? '关于我们' : 'About Us', 'object_id' => $about_id, 'children' => $about_children],
            ['title' => $is_zh ? '产品中心' : 'Products', 'url' => $product_url, 'children' => $product_children],
            ['title' => $is_zh ? '制造能力' : 'Manufacturing', 'url' => $factory_url, 'children' => $factory_children],
            ['title' => $is_zh ? '项目案例' : 'Case Studies', 'url' => home_url('/' . $language . '/cases/')],
            ['title' => $is_zh ? '资质证书' : 'Certificates', 'url' => home_url('/' . $language . '/certificates/')],
            ['title' => $is_zh ? '视频中心' : 'Videos', 'url' => home_url('/' . $language . '/videos/')],
            ['title' => $is_zh ? '新闻资讯' : 'News', 'url' => home_url('/' . $language . '/news/')],
            ['title' => $is_zh ? '联系我们' : 'Contact', 'object_id' => $contact_id],
        ];
        $footer = [
            ['title' => $is_zh ? '关于我们' : 'About Aurelia', 'object_id' => $about_id],
            ['title' => $is_zh ? '工厂与实验室' : 'Factory & Laboratory', 'url' => home_url('/' . $language . '/factory/')],
            ['title' => $is_zh ? '项目案例' : 'Case Studies', 'url' => home_url('/' . $language . '/cases/')],
            ['title' => $is_zh ? '资质证书' : 'Certificates', 'url' => home_url('/' . $language . '/certificates/')],
            ['title' => $is_zh ? '视频中心' : 'Videos', 'url' => home_url('/' . $language . '/videos/')],
            ['title' => $is_zh ? '新闻资讯' : 'News', 'url' => home_url('/' . $language . '/news/')],
            ['title' => $is_zh ? '联系我们' : 'Contact', 'object_id' => $contact_id],
        ];
        $definitions[$language] = ['primary' => $primary, 'footer' => $footer];
    }
    return $definitions;
}

function cb_seed_menu_item_tree($menu_id, $items, $parent_id, &$position, &$created)
{
    foreach ($items as $item) {
        $args = [
            'menu-item-title' => sanitize_text_field($item['title'] ?? ''),
            'menu-item-status' => 'publish',
            'menu-item-position' => ++$position,
            'menu-item-parent-id' => absint($parent_id),
        ];
        $object_id = absint($item['object_id'] ?? 0);
        $term_id = absint($item['term_id'] ?? 0);
        $taxonomy = sanitize_key($item['taxonomy'] ?? '');
        $post_type = $object_id ? get_post_type($object_id) : '';
        if ($object_id && $post_type) {
            $args += ['menu-item-type' => 'post_type', 'menu-item-object' => $post_type, 'menu-item-object-id' => $object_id];
        } elseif ($term_id && $taxonomy && taxonomy_exists($taxonomy)) {
            $args += ['menu-item-type' => 'taxonomy', 'menu-item-object' => $taxonomy, 'menu-item-object-id' => $term_id];
        } else {
            $args += ['menu-item-type' => 'custom', 'menu-item-url' => esc_url_raw($item['url'] ?? home_url('/'))];
        }
        $item_id = wp_update_nav_menu_item($menu_id, 0, $args);
        if (!$item_id || is_wp_error($item_id)) {
            continue;
        }
        update_post_meta($item_id, '_cb_is_demo_content', '1');
        $created[] = absint($item_id);
        if (!empty($item['children'])) {
            cb_seed_menu_item_tree($menu_id, (array) $item['children'], $item_id, $position, $created);
        }
    }
}

function cb_seed_menu_items($menu_id, $items)
{
    foreach ((array) wp_get_nav_menu_items($menu_id, ['post_status' => 'any']) as $item) {
        wp_delete_post($item->ID, true);
    }
    $created = [];
    $position = 0;
    cb_seed_menu_item_tree($menu_id, $items, 0, $position, $created);
    return $created;
}

function cb_install_demo_menus()
{
    $manifest = (array) get_option('cb_demo_menu_manifest', []);
    if (empty($manifest['before_locations'])) {
        $manifest['before_locations'] = (array) get_theme_mod('nav_menu_locations', []);
    }
    $locations = (array) get_theme_mod('nav_menu_locations', []);
    $definitions = cb_demo_menu_definitions();
    foreach ($definitions as $language => $groups) {
        foreach ($groups as $group => $items) {
            $name = 'Aurelia ' . ucfirst($group) . ' ' . strtoupper($language);
            $menu = wp_get_nav_menu_object($name);
            if ($menu && get_term_meta($menu->term_id, '_cb_is_demo_content', true) !== '1') {
                continue;
            }
            if (!$menu) {
                $menu_id = wp_create_nav_menu($name);
                if (is_wp_error($menu_id)) {
                    continue;
                }
            } else {
                $menu_id = absint($menu->term_id);
            }
            update_term_meta($menu_id, '_cb_is_demo_content', '1');
            update_term_meta($menu_id, '_cb_language', $language);
            $item_ids = cb_seed_menu_items($menu_id, $items);
            $manifest['menus'][$language][$group] = ['menu_id' => $menu_id, 'item_ids' => $item_ids];
            $locations[$group . '_' . $language] = $menu_id;
            if ($group === 'primary') {
                $locations['mobile_' . $language] = $menu_id;
            }
        }
    }
    set_theme_mod('nav_menu_locations', $locations);
    $manifest['applied_locations'] = $locations;
    $manifest['installed_at'] = current_time('mysql');
    update_option('cb_demo_menu_manifest', $manifest, false);
    return $manifest;
}

function cb_delete_demo_menus()
{
    $manifest = (array) get_option('cb_demo_menu_manifest', []);
    $current_locations = (array) get_theme_mod('nav_menu_locations', []);
    if (!empty($manifest['applied_locations']) && $current_locations === (array) $manifest['applied_locations']) {
        set_theme_mod('nav_menu_locations', (array) ($manifest['before_locations'] ?? []));
    }
    foreach ((array) ($manifest['menus'] ?? []) as $groups) {
        foreach ((array) $groups as $menu) {
            $menu_id = absint($menu['menu_id'] ?? 0);
            if ($menu_id && get_term_meta($menu_id, '_cb_is_demo_content', true) === '1') {
                wp_delete_nav_menu($menu_id);
            }
        }
    }
    delete_option('cb_demo_menu_manifest');
}

function cb_handle_demo_content_action()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_demo_content');
    $operation = cb_sanitize_choice(
        wp_unslash($_POST['operation'] ?? 'check'),
        ['install', 'delete', 'restore', 'check', 'install_images', 'delete_images', 'install_certificates', 'delete_certificates', 'install_menus', 'delete_menus'],
        'check'
    );
    if ($operation === 'install') {
        cb_install_demo_content();
    } elseif ($operation === 'delete') {
        cb_delete_demo_content();
    } elseif ($operation === 'restore') {
        cb_delete_demo_content();
        cb_install_demo_content();
    } elseif ($operation === 'install_images') {
        cb_install_demo_images();
    } elseif ($operation === 'delete_images') {
        cb_delete_demo_images();
    } elseif ($operation === 'install_certificates') {
        cb_install_demo_certificate_package();
    } elseif ($operation === 'delete_certificates') {
        cb_delete_demo_certificates();
    } elseif ($operation === 'install_menus') {
        cb_install_demo_menus();
    } elseif ($operation === 'delete_menus') {
        cb_delete_demo_menus();
    }
    wp_safe_redirect(add_query_arg(['page' => 'cb-company-tools', 'demo_action' => $operation], admin_url('admin.php')));
    exit;
}

function cb_seed_default_content()
{
    return cb_install_demo_content();
}
