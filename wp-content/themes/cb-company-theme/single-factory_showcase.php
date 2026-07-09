<?php get_header(); ?>
<section class="cb-page-band"><div class="cb-container cb-content"><?php while (have_posts()) : the_post(); if (function_exists('cb_breadcrumb')) { cb_breadcrumb(); } ?><h1><?php the_title(); ?></h1><?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'large') ?: strtok(get_post_meta(get_the_ID(), '_cb_gallery', true), "\n"), get_the_title(), 'cb-rounded-image'); ?><?php the_content(); endwhile; ?></div></section>
<?php get_footer(); ?>
