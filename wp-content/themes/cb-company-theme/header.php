<?php
$header_classes = [
    'cb-site-header',
    'cb-header-layout-' . sanitize_html_class(cb_theme_option('header_layout', 'logo_left_menu_center_cta_right')),
    'cb-header-style-' . sanitize_html_class(cb_theme_option('header_style', 'white')),
    cb_theme_option_enabled('header_sticky') ? 'is-sticky' : '',
    cb_theme_option_enabled('header_shadow') ? 'has-shadow' : '',
    cb_theme_option_enabled('header_blur') ? 'has-blur' : '',
];
$container_class = cb_theme_option_enabled('header_full_width', '0') ? 'cb-header-fluid' : 'cb-container';
$current_page_id = get_queried_object_id();
$special_pages = function_exists('cb_get_group_options')
    ? cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []])
    : [];
$special_home_ids = array_filter(array_map(
    static fn($pages) => absint($pages['home'] ?? 0),
    (array) $special_pages
));
$main_class = is_front_page() || in_array($current_page_id, $special_home_ids, true) ? 'cb-home-main' : '';
?><!doctype html>
<html <?php function_exists('cb_html_language_attributes') ? cb_html_language_attributes() : language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (cb_theme_option('favicon_url')) : ?><link rel="icon" href="<?php echo esc_url(cb_theme_option('favicon_url')); ?>"><?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if (!cb_theme_page_ui_enabled('hide_header')) : ?>
<header class="<?php echo esc_attr(implode(' ', array_filter($header_classes))); ?>">
    <div class="<?php echo esc_attr($container_class); ?> cb-header-inner">
        <?php cb_theme_logo('header'); ?>
        <button class="cb-menu-toggle" type="button" aria-expanded="false" aria-controls="cb-primary-menu" aria-label="<?php echo esc_attr__('Toggle menu', 'cb-company-theme'); ?>">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <nav id="cb-primary-menu" class="cb-main-nav cb-mobile-<?php echo esc_attr(cb_theme_option('mobile_menu_style', 'offcanvas')); ?>" aria-label="<?php echo esc_attr__('Primary navigation', 'cb-company-theme'); ?>">
            <?php
            $location = 'primary_' . cb_theme_lang();
            if (has_nav_menu($location)) {
                wp_nav_menu(['theme_location' => $location, 'container' => false, 'menu_class' => 'cb-menu']);
            } else {
                echo '<ul class="cb-menu">';
                echo '<li><a href="' . esc_url(home_url('/' . cb_theme_lang() . '/')) . '">' . esc_html(cb_theme_t('home')) . '</a></li>';
                echo '<li><a href="#about">' . esc_html(cb_theme_t('about_us')) . '</a></li>';
                echo '<li><a href="' . esc_url(home_url('/' . cb_theme_lang() . '/products/')) . '">' . esc_html(cb_theme_t('products')) . '</a></li>';
                echo '<li><a href="#capabilities">' . esc_html(cb_theme_t('capabilities')) . '</a></li>';
                echo '<li><a href="' . esc_url(home_url('/' . cb_theme_lang() . '/news/')) . '">' . esc_html(cb_theme_t('news')) . '</a></li>';
                echo '<li><a href="#inquiry">' . esc_html(cb_theme_t('contact_us')) . '</a></li></ul>';
            }
            ?>
        </nav>
        <div class="cb-header-actions">
            <?php if (cb_theme_option_enabled('show_search')) : ?>
                <a class="cb-header-icon" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php echo esc_attr__('Search', 'cb-company-theme'); ?>">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </a>
            <?php endif; ?>
            <?php if (function_exists('cb_language_switcher') && cb_theme_option_enabled('show_language_switcher')) cb_language_switcher(); ?>
            <?php if (cb_theme_option_enabled('show_header_cta')) : ?><a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url(cb_theme_option('header_cta_url', '#inquiry')); ?>"><?php echo esc_html(cb_theme_option('header_cta_text', cb_theme_t('get_quote'))); ?></a><?php endif; ?>
        </div>
    </div>
</header>
<?php endif; ?>
<main id="content"<?php echo $main_class ? ' class="' . esc_attr($main_class) . '"' : ''; ?>>
