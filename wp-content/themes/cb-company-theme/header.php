<?php
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="cb-site-header">
    <div class="cb-container cb-header-inner">
        <a class="cb-logo" href="<?php echo esc_url(home_url('/' . cb_theme_lang() . '/')); ?>" aria-label="<?php bloginfo('name'); ?>">
            <span class="cb-logo-mark">A</span>
            <span><strong><?php echo esc_html(cb_theme_option('logo_text', 'AURELIA')); ?></strong><small><?php echo esc_html(cb_theme_option('logo_subtext', 'MANUFACTURING')); ?></small></span>
        </a>
        <button class="cb-menu-toggle" type="button" aria-expanded="false" aria-controls="cb-primary-menu">☰</button>
        <nav id="cb-primary-menu" class="cb-main-nav" aria-label="Primary navigation">
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
                echo '<li><a href="#inquiry">' . esc_html(cb_theme_t('contact_us')) . '</a></li>';
                echo '</ul>';
            }
            ?>
        </nav>
        <div class="cb-header-actions">
            <?php if (function_exists('cb_language_switcher') && cb_theme_option('show_language_switcher', '1') === '1') {
                cb_language_switcher();
            } ?>
            <a class="cb-btn cb-btn-primary" href="<?php echo esc_url(cb_theme_option('header_cta_url', '#inquiry')); ?>"><?php echo esc_html(cb_theme_option('header_cta_text', cb_theme_t('get_quote'))); ?></a>
        </div>
    </div>
</header>
<main id="content">
