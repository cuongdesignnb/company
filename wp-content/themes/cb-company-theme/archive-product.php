<?php get_header(); ?>
<section class="cb-page-hero"><div class="cb-container"><p class="cb-eyebrow"><?php echo esc_html(cb_theme_t('products')); ?></p><h1><?php echo esc_html(cb_theme_t('all_products')); ?></h1></div></section>
<section class="cb-page-band">
    <div class="cb-container">
        <?php if (function_exists('cb_breadcrumb')) {
            cb_breadcrumb();
        } ?>
        <div class="cb-filter-row">
            <?php foreach (get_terms(['taxonomy' => 'product_category', 'hide_empty' => false]) as $term) : ?>
                <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="cb-product-row cb-product-archive-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/cards/product-card'); ?>
            <?php endwhile; ?>
        </div>
        <div class="cb-pagination"><?php the_posts_pagination(); ?></div>
    </div>
</section>
<?php get_footer(); ?>
