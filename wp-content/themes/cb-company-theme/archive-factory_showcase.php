<?php get_header(); ?>
<section class="cb-page-hero"><div class="cb-container"><h1><?php echo esc_html__('Factory Showcase', 'cb-company-theme'); ?></h1></div></section>
<section class="cb-page-band"><div class="cb-container cb-card-grid">
<?php while (have_posts()) : the_post(); ?>
    <article class="cb-media-card"><?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: strtok(get_post_meta(get_the_ID(), '_cb_gallery', true), "\n"), get_the_title()); ?><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html(get_the_excerpt()); ?></p></article>
<?php endwhile; ?>
</div></section>
<?php get_footer(); ?>
