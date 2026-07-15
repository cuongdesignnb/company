<?php get_header(); ?>
<section class="cb-page-hero"><div class="cb-container"><p class="cb-eyebrow"><?php echo esc_html(cb_theme_lang() === 'zh' ? '最新动态' : 'LATEST UPDATES'); ?></p><h1><?php echo esc_html(cb_theme_lang() === 'zh' ? '新闻与洞察' : 'News and Insights'); ?></h1><p><?php echo esc_html(cb_theme_lang() === 'zh' ? '了解 Aurelia 的产品研发、制造和全球市场动态。' : 'Product development, manufacturing and global market updates from Aurelia.'); ?></p></div></section>
<section class="cb-page-band"><div class="cb-container"><div class="cb-news-archive-grid">
    <?php while (have_posts()) : the_post(); get_template_part('template-parts/cards/news-card'); endwhile; ?>
</div><div class="cb-pagination"><?php the_posts_pagination(); ?></div></div></section>
<?php get_footer(); ?>
