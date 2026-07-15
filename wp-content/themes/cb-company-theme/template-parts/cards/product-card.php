<?php $post_id = get_the_ID(); ?>
<article class="cb-product-card">
    <a href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>">
        <?php echo cb_theme_image(get_the_post_thumbnail_url($post_id, 'medium_large') ?: '', get_the_title(), '', 520, 390); ?>
    </a>
    <div>
        <h3><a href="<?php echo esc_url(cb_theme_post_url(get_the_ID())); ?>"><?php the_title(); ?></a></h3>
        <p class="cb-product-model"><?php echo esc_html(get_post_meta($post_id, '_cb_model', true)); ?></p>
        <p class="cb-product-excerpt"><?php echo esc_html(get_post_meta($post_id, '_cb_short_description', true) ?: get_the_excerpt()); ?></p>
        <dl class="cb-card-specs">
            <?php foreach (['_cb_power' => (cb_theme_lang() === 'zh' ? '功率' : 'Power'), '_cb_moq' => 'MOQ'] as $key => $label) : $value = get_post_meta($post_id, $key, true); if ($value) : ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($value); ?></dd></div><?php endif; endforeach; ?>
        </dl>
        <a class="cb-text-link" href="<?php echo esc_url(cb_theme_post_url($post_id)); ?>"><?php echo esc_html(cb_theme_t('view_details')); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2"/></svg></a>
    </div>
</article>
