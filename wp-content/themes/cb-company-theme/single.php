<?php get_header(); ?>
<section class="cb-page-band"><div class="cb-container cb-content">
<?php while (have_posts()) : the_post(); ?>
    <?php if (function_exists('cb_breadcrumb')) {
        cb_breadcrumb();
    } ?>
    <h1><?php the_title(); ?></h1>
    <p class="cb-post-date"><?php echo esc_html(get_the_date()); ?></p>
    <?php if (has_post_thumbnail()) : ?><?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'full'), get_the_title(), 'cb-detail-hero-image', 1400, 820); ?><?php endif; ?>
    <?php the_content(); ?>
<?php endwhile; ?>
</div></section>
<?php get_footer(); ?>
