<article class="<?php echo esc_attr(cb_theme_card_classes('cb-product-card', 'product_card_style')); ?>">
    <a href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>">
        <?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: strtok(get_post_meta(get_the_ID(), '_cb_gallery', true), "\n"), get_the_title(), '', 360, 260); ?>
    </a>
    <div>
        <h3><a href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html(get_post_meta(get_the_ID(), '_cb_model', true)); ?></p>
        <a class="<?php echo esc_attr(cb_theme_button_classes('outline')); ?>" href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>"><?php echo esc_html(cb_theme_t('learn_more')); ?></a>
    </div>
</article>
