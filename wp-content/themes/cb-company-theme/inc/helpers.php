<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_theme_lang()
{
    return function_exists('cb_get_current_language') ? cb_get_current_language() : 'en';
}

function cb_theme_t($key)
{
    return function_exists('cb_t') ? cb_t($key) : ucwords(str_replace('_', ' ', $key));
}

function cb_theme_option($key, $default = '')
{
    return function_exists('cb_get_option') ? cb_get_option($key, $default) : $default;
}

function cb_theme_option_enabled($key, $default = '1')
{
    return cb_theme_option($key, $default) === '1';
}

function cb_theme_wechat_id()
{
    $footer = (array) get_option('cb_footer_settings', []);
    $legacy = (array) get_option('cb_theme_options', []);
    $candidates = [
        $footer['contact_wechat_id'] ?? '',
        $legacy['contact_wechat_id'] ?? '',
        cb_theme_option('contact_wechat_id', ''),
    ];

    foreach ($candidates as $candidate) {
        if (!is_scalar($candidate)) {
            continue;
        }
        $candidate = trim((string) $candidate);
        if ($candidate !== '') {
            return $candidate;
        }
    }

    return '';
}

function cb_theme_logo($context = 'header')
{
    $is_mobile = $context === 'mobile';
    $logo_url = $context === 'footer' ? cb_theme_option('footer_logo_url') : ($is_mobile ? cb_theme_option('mobile_logo_url') : cb_theme_option('logo_url'));
    if (!$logo_url) {
        $logo_url = cb_theme_option('logo_url');
    }
    $home = home_url('/' . cb_theme_lang() . '/');
    echo '<a class="cb-logo" href="' . esc_url($home) . '" aria-label="' . esc_attr(get_bloginfo('name')) . '">';
    if ($logo_url) {
        echo '<img class="cb-logo-image" src="' . esc_url($logo_url) . '" alt="' . esc_attr(cb_theme_option('logo_text', get_bloginfo('name'))) . '">';
    } else {
        $mark = cb_theme_option('brand_mark_text', '');
        if ($mark) {
            echo '<span class="cb-logo-mark">' . esc_html($mark) . '</span>';
        }
    }
    if (cb_theme_option_enabled('show_logo_text')) {
        echo '<span class="cb-logo-text"><strong>' . esc_html(cb_theme_option('logo_text', get_bloginfo('name'))) . '</strong><small>' . esc_html(cb_theme_option('logo_subtext', '')) . '</small></span>';
    }
    echo '</a>';
}

function cb_theme_post_url($post_id)
{
    $url = get_permalink($post_id);
    $lang = get_post_meta($post_id, '_cb_language', true) ?: cb_theme_lang();
    if (get_post_type($post_id) === 'product') {
        return home_url('/' . $lang . '/product/' . get_post_field('post_name', $post_id) . '/');
    }
    return $url;
}

function cb_theme_image($url, $alt = '', $class = '', $width = 640, $height = 420)
{
    if (!$url) {
        return '';
    }
    return '<img class="' . esc_attr($class) . '" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="lazy" width="' . esc_attr((string) $width) . '" height="' . esc_attr((string) $height) . '">';
}

function cb_theme_items($section)
{
    if (!empty($section['items']) && is_array($section['items'])) {
        return array_map(static function ($item) {
            $item = (array) $item;
            $item['label'] = $item['label'] ?? ($item['title'] ?? ($item['question'] ?? ''));
            $item['value'] = $item['value'] ?? ($item['description'] ?? ($item['answer'] ?? ''));
            $item['image'] = $item['image'] ?? ($item['image_url'] ?? '');
            $item['url'] = $item['url'] ?? ($item['link'] ?? '');
            return $item;
        }, $section['items']);
    }
    return function_exists('cb_parse_lines') ? cb_parse_lines($section['items'] ?? '') : [];
}

function cb_theme_product_terms($limit = 0)
{
    $terms = get_terms([
        'taxonomy' => 'product_category',
        'hide_empty' => false,
        'number' => absint($limit),
        'meta_query' => [['key' => '_cb_language', 'value' => cb_theme_lang()]],
    ]);
    return is_wp_error($terms) ? [] : $terms;
}

function cb_theme_contact_page_url()
{
    if (function_exists('cb_get_group_options')) {
        $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
        $post_id = absint($special[cb_theme_lang()]['contact'] ?? 0);
        if ($post_id) {
            return get_permalink($post_id);
        }
    }
    return home_url('/' . cb_theme_lang() . '/contact/');
}

function cb_theme_video_archive_url()
{
    return home_url('/' . cb_theme_lang() . '/videos/');
}

function cb_theme_video_terms()
{
    $terms = get_terms([
        'taxonomy' => 'video_category',
        'hide_empty' => true,
        'meta_query' => [
            'relation' => 'OR',
            ['key' => '_cb_language', 'value' => cb_theme_lang()],
            ['key' => '_cb_language', 'compare' => 'NOT EXISTS'],
        ],
    ]);
    return is_wp_error($terms) ? [] : $terms;
}

function cb_theme_video_meta($post_id)
{
    $terms = get_the_terms($post_id, 'video_category');
    $term_name = !is_wp_error($terms) && $terms ? $terms[0]->name : '';
    $duration = (string) get_post_meta($post_id, '_cb_video_duration', true);
    $parts = array_filter([$term_name, $duration]);
    if (!$parts) {
        return '';
    }
    return '<p class="cb-video-meta">' . esc_html(implode(' / ', $parts)) . '</p>';
}

function cb_theme_is_direct_video_url($url)
{
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    return (bool) preg_match('/\.(mp4|webm|ogv|ogg|m4v)$/i', $path);
}

function cb_theme_video_media($post_id, &$media_type = '')
{
    $url = (string) get_post_meta($post_id, '_cb_video_url', true);
    $poster = get_the_post_thumbnail_url($post_id, 'full');
    if ($url && cb_theme_is_direct_video_url($url)) {
        $media_type = 'video';
        $mime = wp_check_filetype((string) wp_parse_url($url, PHP_URL_PATH));
        return '<video controls preload="metadata"' . ($poster ? ' poster="' . esc_url($poster) . '"' : '') . '><source src="' . esc_url($url) . '" type="' . esc_attr($mime['type'] ?: 'video/mp4') . '"></video>';
    }
    if ($url) {
        $embed = wp_oembed_get($url, ['width' => 1220]);
        if ($embed) {
            $media_type = 'embed';
            $allowed = wp_kses_allowed_html('post');
            $allowed['iframe'] = [
                'src' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'allow' => true,
                'allowfullscreen' => true,
                'loading' => true,
                'title' => true,
            ];
            return wp_kses($embed, $allowed);
        }
        $media_type = 'external';
    } elseif ($poster) {
        $media_type = 'poster';
    }
    return $poster ? cb_theme_image($poster, get_the_title($post_id), 'cb-video-poster-image', 1400, 788) : '';
}

function cb_theme_related_videos($post_id, $limit = 3)
{
    return get_posts([
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => absint($limit),
        'post__not_in' => [absint($post_id)],
        'meta_query' => [['key' => '_cb_language', 'value' => cb_theme_lang()]],
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
}

function cb_theme_featured_products($limit = 3)
{
    return get_posts([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => absint($limit),
        'meta_query' => [
            'relation' => 'AND',
            ['key' => '_cb_language', 'value' => cb_theme_lang()],
            ['key' => '_cb_featured', 'value' => '1'],
        ],
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);
}

function cb_theme_catalog_sidebar($active_term_id = 0)
{
    $is_zh = cb_theme_lang() === 'zh';
    $labels = $is_zh
        ? ['products' => '产品分类', 'featured' => '推荐产品', 'contact' => '联系我们', 'contact_copy' => '告诉我们您的目标市场、产品方向和预计采购量。', 'brief' => '提交产品需求']
        : ['products' => 'Product Categories', 'featured' => 'Featured Products', 'contact' => 'Contact Us', 'contact_copy' => 'Tell us your target market, product direction and estimated volume.', 'brief' => 'Send Product Brief'];
    ?>
    <div class="cb-catalog-sidebar-inner">
        <section class="cb-sidebar-section cb-sidebar-categories">
            <h2><?php echo esc_html($labels['products']); ?></h2>
            <nav aria-label="<?php echo esc_attr($labels['products']); ?>">
                <?php foreach (cb_theme_product_terms() as $category) : ?>
                    <a class="<?php echo (int) $category->term_id === (int) $active_term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url(get_term_link($category)); ?>">
                        <span><?php echo esc_html($category->name); ?></span>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                <?php endforeach; ?>
            </nav>
        </section>
        <?php $products = cb_theme_featured_products(); if ($products) : ?>
            <section class="cb-sidebar-section cb-sidebar-featured">
                <h2><?php echo esc_html($labels['featured']); ?></h2>
                <div class="cb-sidebar-product-list">
                    <?php foreach ($products as $product) : ?>
                        <a class="cb-sidebar-product" href="<?php echo esc_url(cb_theme_post_url($product->ID)); ?>">
                            <?php echo cb_theme_image(get_the_post_thumbnail_url($product->ID, 'thumbnail'), get_the_title($product), '', 96, 96); ?>
                            <span><strong><?php echo esc_html(get_the_title($product)); ?></strong><small><?php echo esc_html(get_post_meta($product->ID, '_cb_model', true)); ?></small></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        <section class="cb-sidebar-contact">
            <p class="cb-eyebrow"><?php echo esc_html($labels['contact']); ?></p>
            <h3><?php echo esc_html($is_zh ? '启动您的 OEM/ODM 项目' : 'Start Your OEM/ODM Project'); ?></h3>
            <p><?php echo esc_html($labels['contact_copy']); ?></p>
            <?php if (cb_theme_option('contact_email')) : ?><a href="mailto:<?php echo esc_attr(cb_theme_option('contact_email')); ?>"><?php echo esc_html(cb_theme_option('contact_email')); ?></a><?php endif; ?>
            <?php if (cb_theme_option('contact_phone')) : ?><a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', cb_theme_option('contact_phone'))); ?>"><?php echo esc_html(cb_theme_option('contact_phone')); ?></a><?php endif; ?>
            <a class="cb-btn cb-btn-primary" href="<?php echo esc_url(cb_theme_contact_page_url()); ?>"><?php echo esc_html($labels['brief']); ?></a>
        </section>
    </div>
    <?php
}

function cb_theme_section_header($section)
{
    if (!empty($section['eyebrow'])) {
        echo '<p class="cb-eyebrow">' . esc_html($section['eyebrow']) . '</p>';
    }
    if (!empty($section['title'])) {
        echo '<h2>' . esc_html($section['title']) . '</h2>';
    }
    if (!empty($section['description'])) {
        echo '<p class="cb-section-desc">' . esc_html($section['description']) . '</p>';
    }
}

function cb_render_page_sections($post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    $sections = function_exists('cb_get_page_sections') ? cb_get_page_sections($post_id) : [];
    foreach ((array) $sections as $index => $section) {
        if (function_exists('cb_normalize_homepage_section')) {
            $section = cb_normalize_homepage_section($section);
        }
        if (($section['enable'] ?? '1') !== '1') {
            continue;
        }
        cb_render_page_section($section, $post_id, $index);
    }
}

function cb_render_page_section($section, $post_id = 0, $index = null)
{
    $type = sanitize_key($section['type'] ?? '');
    $file = locate_template('template-parts/sections/' . str_replace('_', '-', $type) . '.php');
    if ($file) {
        $GLOBALS['cb_current_section_context'] = [
            'post_id' => absint($post_id),
            'index' => is_numeric($index) ? (int) $index : null,
            'type' => $type,
        ];
        include $file;
        unset($GLOBALS['cb_current_section_context']);
    }
}

function cb_theme_page_ui($key, $default = '')
{
    $post_id = get_queried_object_id();
    $context = function_exists('cb_page_ui_context') ? cb_page_ui_context($post_id) : 'standard_page';
    return function_exists('cb_ui_get') ? cb_ui_get($key, $context, $post_id, $default) : $default;
}

function cb_theme_page_ui_enabled($key, $default = '0')
{
    return (string) cb_theme_page_ui($key, $default) === '1';
}

function cb_theme_page_render_mode($post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    return get_post_meta($post_id, '_cb_page_render_mode', true) ?: 'editor';
}

function cb_theme_is_special_page($role, $post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    if (!$post_id || !function_exists('cb_get_group_options')) {
        return false;
    }
    $special = cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []]);
    foreach ((array) $special as $pages) {
        if ((int) ($pages[$role] ?? 0) === (int) $post_id) {
            return true;
        }
    }
    return false;
}

function cb_theme_certificate_archive_url($term = '')
{
    if (function_exists('cb_certificate_archive_url')) {
        return cb_certificate_archive_url(cb_theme_lang(), $term);
    }
    return home_url('/' . cb_theme_lang() . '/certificates/');
}

function cb_theme_about_sidebar_links($labels)
{
    $links = [
        'overview' => $labels['overview'],
        'milestones' => $labels['milestones'],
        'factory' => $labels['factory'],
        'certificates' => $labels['certificates'],
        'quality' => $labels['quality'],
        'services' => $labels['services'],
        'contact' => $labels['contact'],
    ];
    foreach ($links as $anchor => $label) {
        echo '<a href="#' . esc_attr($anchor) . '">' . esc_html($label) . '</a>';
        if ($anchor === 'certificates') {
            echo '<a class="cb-about-all-certificates" href="' . esc_url(cb_theme_certificate_archive_url()) . '">' . esc_html($labels['all']) . '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>';
        }
    }
}

function cb_theme_page_banner_style($post_id = 0)
{
    $post_id = $post_id ?: get_queried_object_id();
    $context = function_exists('cb_page_ui_context') ? cb_page_ui_context($post_id) : 'standard_page';
    $styles = [];
    $image = cb_ui_get('banner_image', $context, $post_id, '');
    $overlay = max(0, min(90, absint(cb_ui_get('banner_overlay', $context, $post_id, '42')))) / 100;
    if ($image) $styles[] = 'background-image:linear-gradient(rgba(19,23,29,' . $overlay . '),rgba(19,23,29,' . $overlay . ')),url(' . esc_url($image) . ')';
    $height = cb_ui_get('banner_height_desktop', $context, $post_id, '');
    if ($height) $styles[] = 'min-height:' . cb_sanitize_css_size($height, '');
    $mobile_height = cb_ui_get('banner_height_mobile', $context, $post_id, '');
    if ($mobile_height) $styles[] = '--cb-page-hero-mobile-height:' . cb_sanitize_css_size($mobile_height, '');
    return $styles ? ' style="' . esc_attr(implode(';', $styles)) . '"' : '';
}

function cb_theme_section_attrs($section, $type, $extra_class = '')
{
    $classes = [
        'cb-section',
        'cb-section-' . sanitize_html_class(str_replace('_', '-', $type)),
        'cb-layout-' . sanitize_html_class($section['layout_style'] ?? 'default'),
    ];
    if (!empty($section['section_class'])) {
        $classes[] = sanitize_html_class($section['section_class']);
    }
    if ($extra_class) {
        $classes[] = $extra_class;
    }
    if (($section['hide_mobile'] ?? '0') === '1') {
        $classes[] = 'cb-hide-mobile';
    }
    $styles = [];
    if (!empty($section['background_color']) && sanitize_hex_color($section['background_color'])) {
        $styles[] = 'background-color:' . sanitize_hex_color($section['background_color']);
    }
    if (!empty($section['text_color']) && sanitize_hex_color($section['text_color'])) {
        $styles[] = 'color:' . sanitize_hex_color($section['text_color']);
    }
    foreach (['padding_top' => 'padding-top', 'padding_bottom' => 'padding-bottom'] as $key => $property) {
        if (!empty($section[$key]) && function_exists('cb_sanitize_css_size')) {
            $size = cb_sanitize_css_size($section[$key], '');
            if ($size) {
                $styles[] = $property . ':' . $size;
            }
        }
    }
    if (!empty($section['container_width'])) {
        $styles[] = '--cb-section-container:' . cb_sanitize_css_size($section['container_width'], '');
    }
    if (!empty($section['mobile_order'])) {
        $styles[] = '--cb-mobile-order:' . absint($section['mobile_order']);
    }
    if (!empty($section['background_image_url'])) {
        $styles[] = 'background-image:url(' . esc_url($section['background_image_url']) . ')';
        $styles[] = 'background-size:cover';
        $styles[] = 'background-position:center';
    }
    $id = !empty($section['section_id']) ? ' id="' . esc_attr(sanitize_title($section['section_id'])) . '"' : '';
    $style = $styles ? ' style="' . esc_attr(implode(';', $styles)) . '"' : '';
    $edit_attrs = '';
    $context = $GLOBALS['cb_current_section_context'] ?? [];
    $post_id = absint($context['post_id'] ?? get_queried_object_id());
    $index = $context['index'] ?? null;
    if (is_user_logged_in() && $post_id && is_numeric($index) && current_user_can('edit_post', $post_id)) {
        $types = function_exists('cb_section_types') ? cb_section_types() : [];
        $label = $section['admin_label'] ?: ($section['title'] ?: ($types[$type] ?? $type));
        $edit_attrs = ' data-cb-editable-section="1" data-cb-post-id="' . esc_attr((string) $post_id) . '" data-cb-section-index="' . esc_attr((string) $index) . '" data-cb-section-type="' . esc_attr($type) . '" data-cb-section-label="' . esc_attr(wp_strip_all_tags($label)) . '"';
    }
    return $id . ' class="' . esc_attr(implode(' ', array_filter($classes))) . '"' . $style . $edit_attrs;
}

function cb_theme_button_classes($variant = 'primary')
{
    $variant_class = $variant === 'primary' ? 'cb-btn-primary' : ($variant === 'soft' ? 'cb-btn-soft' : 'cb-btn-outline');
    return implode(' ', array_filter([
        'cb-btn',
        $variant_class,
        'cb-btn-' . sanitize_html_class(cb_theme_option('button_style', 'pill')),
        'cb-hover-' . sanitize_html_class(cb_theme_option('button_hover_effect', 'lift')),
    ]));
}

function cb_theme_card_classes($base, $style_key)
{
    return implode(' ', [
        $base,
        'cb-card-style-' . sanitize_html_class(cb_theme_option($style_key, 'clean')),
        'cb-shadow-' . sanitize_html_class(cb_theme_option('card_shadow', 'soft')),
        'cb-hover-' . sanitize_html_class(cb_theme_option('card_hover_effect', 'lift')),
    ]);
}
