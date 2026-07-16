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
}

function cb_install_demo_content()
{
    $images = cb_install_demo_images(false);
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
    delete_option('cb_demo_content_manifest');
    delete_option('cb_demo_content_installed');
}

function cb_demo_content_status()
{
    $posts = get_posts(['post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_cb_is_demo_content', 'meta_value' => '1']);
    return ['installed' => get_option('cb_demo_content_installed') === '1', 'post_count' => count($posts), 'manifest' => (array) get_option('cb_demo_content_manifest', [])];
}

function cb_handle_demo_content_action()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'cb-company-core'), 403);
    }
    check_admin_referer('cb_demo_content');
    $operation = cb_sanitize_choice(
        wp_unslash($_POST['operation'] ?? 'check'),
        ['install', 'delete', 'restore', 'check', 'install_images', 'delete_images', 'install_certificates', 'delete_certificates'],
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
    }
    wp_safe_redirect(add_query_arg(['page' => 'cb-company-tools', 'demo_action' => $operation], admin_url('admin.php')));
    exit;
}

function cb_seed_default_content()
{
    return cb_install_demo_content();
}
