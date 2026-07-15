<?php get_header(); ?>
<?php
$columns = function_exists('cb_ui_get') ? cb_ui_get('columns_desktop', 'product_archive', 0, '4') : '4';
$show_breadcrumb = !function_exists('cb_ui_get') || cb_ui_get('show_breadcrumb', 'product_archive', 0, '1') === '1';
?>
<section class="cb-page-hero"><div class="cb-container"><p class="cb-eyebrow"><?php echo esc_html(cb_theme_t('products')); ?></p><h1><?php echo esc_html(cb_theme_t('all_products')); ?></h1></div></section>
<section class="cb-page-band">
    <div class="cb-container">
        <?php if ($show_breadcrumb && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
        <button class="cb-filter-toggle" type="button" data-cb-filter-toggle aria-expanded="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><?php echo esc_html(cb_theme_lang() === 'zh' ? '产品分类' : 'Product Categories'); ?></button>
        <div class="cb-catalog-layout">
            <aside class="cb-catalog-sidebar" data-cb-filter-panel>
                <h2><?php echo esc_html(cb_theme_lang() === 'zh' ? '产品分类' : 'Product Categories'); ?></h2>
                <nav><?php foreach (cb_theme_product_terms() as $term) : ?><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg></a><?php endforeach; ?></nav>
                <div class="cb-sidebar-contact"><p class="cb-eyebrow"><?php echo esc_html(cb_theme_t('contact_us')); ?></p><h3><?php echo esc_html(cb_theme_lang() === 'zh' ? '需要选型或定制建议？' : 'Need product or customization guidance?'); ?></h3><a class="cb-text-link" href="#inquiry"><?php echo esc_html(cb_theme_t('send_inquiry')); ?></a></div>
            </aside>
            <div class="cb-catalog-main">
                <div class="cb-product-row cb-product-archive-grid" style="--cb-archive-columns:<?php echo esc_attr((string) max(1, absint($columns))); ?>">
                    <?php while (have_posts()) : the_post(); get_template_part('template-parts/cards/product-card'); endwhile; ?>
                </div>
                <div class="cb-pagination"><?php the_posts_pagination(); ?></div>
            </div>
        </div>
    </div>
</section>
<section id="inquiry" class="cb-section cb-archive-inquiry"><div class="cb-container cb-cta-grid"><div><p class="cb-eyebrow"><?php echo esc_html(cb_theme_t('send_inquiry')); ?></p><h2><?php echo esc_html(cb_theme_lang() === 'zh' ? '告诉我们您的产品需求' : 'Tell us what you want to build'); ?></h2><p><?php echo esc_html(cb_theme_lang() === 'zh' ? '我们的产品团队将根据目标市场、规格和数量提供建议。' : 'Our product team will respond with practical options based on your market, specifications and volume.'); ?></p></div><?php echo do_shortcode('[cb_inquiry_form]'); ?></div></section>
<?php get_footer(); ?>
