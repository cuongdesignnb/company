<?php get_header(); ?>
<section class="cb-page-band"><div class="cb-container cb-content"><?php while (have_posts()) : the_post(); if (function_exists('cb_breadcrumb')) { cb_breadcrumb(); } ?><h1><?php the_title(); ?></h1><?php $video = get_post_meta(get_the_ID(), '_cb_video_url', true); if ($video) : ?><p><a class="cb-btn cb-btn-primary" href="<?php echo esc_url($video); ?>"><?php echo esc_html__('Watch Video', 'cb-company-theme'); ?></a></p><?php endif; ?><?php the_content(); endwhile; ?></div></section>
<?php get_footer(); ?>
