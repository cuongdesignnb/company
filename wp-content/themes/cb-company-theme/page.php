<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
    <?php $post_id = get_the_ID(); $mode = cb_theme_page_render_mode($post_id); $show_banner = cb_theme_page_ui_enabled('show_banner'); ?>
    <?php if ($show_banner) : ?>
        <section class="cb-page-hero cb-custom-page-hero<?php echo cb_theme_page_ui_enabled('hide_banner_mobile') ? ' cb-hide-mobile' : ''; ?>"<?php echo cb_theme_page_banner_style($post_id); ?>>
            <div class="cb-container">
                <?php if (cb_theme_page_ui_enabled('show_breadcrumb', '1') && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
                <h1><?php echo esc_html(cb_theme_page_ui('banner_title', get_the_title())); ?></h1>
                <?php if (cb_theme_page_ui('banner_description')) : ?><p><?php echo esc_html(cb_theme_page_ui('banner_description')); ?></p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php if (in_array($mode, ['editor', 'editor_and_builder'], true)) : ?>
        <section class="cb-page-band"><div class="cb-container cb-content">
            <?php if (!$show_banner && cb_theme_page_ui_enabled('show_breadcrumb', '1') && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
            <?php if (!$show_banner) : ?><h1><?php the_title(); ?></h1><?php endif; ?>
            <?php the_content(); ?>
        </div></section>
    <?php endif; ?>
    <?php if (in_array($mode, ['builder', 'editor_and_builder'], true)) cb_render_page_sections($post_id); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
