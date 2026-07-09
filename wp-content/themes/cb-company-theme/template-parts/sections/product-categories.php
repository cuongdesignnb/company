<section class="cb-section">
    <div class="cb-container">
        <div class="cb-section-heading">
            <div><?php cb_theme_section_header($section); ?></div>
            <a href="<?php echo esc_url($section['button_url'] ?? home_url('/' . cb_theme_lang() . '/products/')); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('all_products')); ?> →</a>
        </div>
        <div class="cb-card-grid cb-category-grid">
            <?php foreach (get_terms(['taxonomy' => 'product_category', 'hide_empty' => false, 'number' => 4]) as $term) : ?>
                <article class="cb-category-card">
                    <?php echo cb_theme_image(get_term_meta($term->term_id, '_cb_banner_image', true), $term->name); ?>
                    <div><h3><?php echo esc_html($term->name); ?></h3><p><?php echo esc_html($term->description); ?></p><a class="cb-round-link" href="<?php echo esc_url(get_term_link($term)); ?>">→</a></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
