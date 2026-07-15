<?php get_header(); ?>
<section class="cb-page-hero"><div class="cb-container"><p class="cb-eyebrow"><?php echo esc_html(cb_theme_lang() === 'zh' ? '项目案例' : 'PROJECT EXPERIENCE'); ?></p><h1><?php echo esc_html(cb_theme_lang() === 'zh' ? '案例研究' : 'Case Studies'); ?></h1></div></section>
<section class="cb-page-band"><div class="cb-container cb-case-archive-grid">
<?php while (have_posts()) : the_post(); ?>
    <article class="cb-case-card"><a href="<?php the_permalink(); ?>"><?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'large'), get_the_title(), '', 900, 620); ?></a><div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(get_the_excerpt()); ?></p></div></article>
<?php endwhile; ?>
</div><div class="cb-container cb-pagination"><?php the_posts_pagination(); ?></div></section>
<?php get_footer(); ?>
