<article class="cb-product-card">
    <a href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>">
        <?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: strtok(get_post_meta(get_the_ID(), '_cb_gallery', true), "\n"), get_the_title()); ?>
    </a>
    <div>
        <h3><a href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html(get_post_meta(get_the_ID(), '_cb_model', true)); ?></p>
        <a class="cb-btn cb-btn-outline" href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>"><?php echo esc_html(cb_theme_t('learn_more')); ?></a>
    </div>
</article>
