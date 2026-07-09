<?php get_header(); ?>
<?php if (get_query_var('cb_lang') || is_front_page() || is_home()) : ?>
    <?php cb_render_page_sections(get_queried_object_id()); ?>
<?php else : ?>
    <section class="cb-page-band"><div class="cb-container cb-card-grid">
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('template-parts/cards/news-card'); ?>
        <?php endwhile; ?>
    </div></section>
<?php endif; ?>
<?php get_footer(); ?>
