<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_default_homepage_sections()
{
    return [];
}

function cb_demo_homepage_sections()
{
    $asset = content_url('/themes/cb-company-theme/assets/images/aurelia-reference.png');
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
                    'image_url' => $asset,
                    'image_alt' => 'Aurelia kitchen appliance manufacturing',
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
            'image_url' => $asset,
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
    $manifest = ['posts' => [], 'terms' => [], 'home_page' => 0, 'home_sections_hash' => '', 'installed_at' => current_time('mysql')];
    $asset = content_url('/themes/cb-company-theme/assets/images/aurelia-reference.png');
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    $home_id = absint($special['en']['home'] ?? 0);
    if ($home_id && !cb_get_page_sections($home_id)) {
        $demo_sections = cb_sanitize_page_sections(cb_demo_homepage_sections());
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
            update_term_meta($term_id, '_cb_language', 'en');
            update_term_meta($term_id, '_cb_is_demo_content', '1');
            $manifest['terms'][] = $term_id;
        }
    }

    $products = [
        ['Air Fryer 5.5L', 'AF-8001', 'Small Kitchen Appliances'],
        ['Espresso Machine', 'CM-3002', 'Coffee Machines'],
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
            update_post_meta($post_id, '_cb_is_demo_content', '1');
            update_post_meta($post_id, '_cb_language', 'en');
            update_post_meta($post_id, '_cb_featured', '1');
            update_post_meta($post_id, '_cb_model', $product[1]);
            update_post_meta($post_id, '_cb_gallery', $asset);
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
    $operation = cb_sanitize_choice(wp_unslash($_POST['operation'] ?? 'check'), ['install', 'delete', 'restore', 'check'], 'check');
    if ($operation === 'install') {
        cb_install_demo_content();
    } elseif ($operation === 'delete') {
        cb_delete_demo_content();
    } elseif ($operation === 'restore') {
        cb_delete_demo_content();
        cb_install_demo_content();
    }
    wp_safe_redirect(add_query_arg(['page' => 'cb-company-tools', 'demo_action' => $operation], admin_url('admin.php')));
    exit;
}

function cb_seed_default_content()
{
    return cb_install_demo_content();
}
