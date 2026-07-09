<?php get_header(); ?>
<section class="cb-page-band"><div class="cb-container cb-content"><h1>404</h1><p><?php echo esc_html__('The page could not be found.', 'cb-company-theme'); ?></p><a class="cb-btn cb-btn-primary" href="<?php echo esc_url(home_url('/' . cb_theme_lang() . '/')); ?>"><?php echo esc_html(cb_theme_t('home')); ?></a></div></section>
<?php get_footer(); ?>
