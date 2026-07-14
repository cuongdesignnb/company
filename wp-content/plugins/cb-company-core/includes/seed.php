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

function cb_install_demo_content()
{
    $images = cb_install_demo_images(false);
    $manifest = ['posts' => [], 'terms' => [], 'home_page' => 0, 'home_sections_hash' => '', 'installed_at' => current_time('mysql')];
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
    update_option('cb_demo_content_manifest', $manifest, false);
    update_option('cb_demo_content_installed', '1', false);
    return $manifest;
}

function cb_delete_demo_content()
{
    $posts = get_posts([
        'post_type' => ['post', 'page', 'product', 'factory_showcase', 'case_study', 'video'],
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
        ['install', 'delete', 'restore', 'check', 'install_images', 'delete_images'],
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
    }
    wp_safe_redirect(add_query_arg(['page' => 'cb-company-tools', 'demo_action' => $operation], admin_url('admin.php')));
    exit;
}

function cb_seed_default_content()
{
    return cb_install_demo_content();
}
