<?php get_header(); ?>
<section class="cb-page-hero"><div class="cb-container"><h1><?php echo esc_html__('Search', 'cb-company-theme'); ?></h1></div></section>
<section class="cb-page-band"><div class="cb-container cb-card-grid">
    <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part(get_post_type() === 'product' ? 'template-parts/cards/product-card' : 'template-parts/cards/news-card'); ?>
    <?php endwhile; ?>
</div></section>
<?php get_footer(); ?>
