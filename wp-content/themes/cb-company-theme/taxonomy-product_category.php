<?php get_header(); $term = get_queried_object(); ?>
<section class="cb-page-hero"><div class="cb-container"><p class="cb-eyebrow"><?php echo esc_html(cb_theme_t('products')); ?></p><h1><?php echo esc_html($term->name); ?></h1><p><?php echo esc_html($term->description); ?></p></div></section>
<section class="cb-page-band">
    <div class="cb-container">
        <?php if (function_exists('cb_breadcrumb')) {
            cb_breadcrumb();
        } ?>
        <button class="cb-filter-toggle" type="button" data-cb-filter-toggle aria-expanded="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4" fill="none" stroke="currentColor" stroke-width="2"/></svg><?php echo esc_html(cb_theme_lang() === 'zh' ? '产品分类' : 'Product Categories'); ?></button>
        <div class="cb-catalog-layout">
            <aside class="cb-catalog-sidebar" data-cb-filter-panel><h2><?php echo esc_html(cb_theme_lang() === 'zh' ? '产品分类' : 'Product Categories'); ?></h2><nav><?php foreach (cb_theme_product_terms() as $category) : ?><a class="<?php echo $category->term_id === $term->term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a><?php endforeach; ?></nav></aside>
            <div class="cb-catalog-main"><div class="cb-product-row cb-product-archive-grid">
                <?php while (have_posts()) : the_post(); ?><?php get_template_part('template-parts/cards/product-card'); ?><?php endwhile; ?>
            </div><div class="cb-pagination"><?php the_posts_pagination(); ?></div></div>
        </div>
    </div>
</section>
<section id="inquiry" class="cb-section cb-archive-inquiry"><div class="cb-container cb-cta-grid"><div><p class="cb-eyebrow"><?php echo esc_html(cb_theme_t('send_inquiry')); ?></p><h2><?php echo esc_html(cb_theme_lang() === 'zh' ? '需要定制建议？' : 'Need a customized product direction?'); ?></h2></div><?php echo do_shortcode('[cb_inquiry_form]'); ?></div></section>
<?php get_footer(); ?>
