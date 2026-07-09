<?php get_header(); ?>
<section class="cb-page-band"><div class="cb-container cb-content">
<?php while (have_posts()) : the_post(); ?>
    <?php if (function_exists('cb_breadcrumb')) {
        cb_breadcrumb();
    } ?>
    <h1><?php the_title(); ?></h1>
    <p class="cb-post-date"><?php echo esc_html(get_the_date()); ?></p>
    <?php the_content(); ?>
<?php endwhile; ?>
</div></section>
<?php get_footer(); ?>
