<section id="capabilities" <?php echo cb_theme_section_attrs($section, 'factory_capability'); ?>>
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-factory-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <article class="cb-media-card">
                    <?php echo cb_theme_image($item['image_url'] ?? ($item['image'] ?? ($section['image_url'] ?: ($section['image'] ?? ''))), $item['label']); ?>
                    <h3><?php echo esc_html($item['label']); ?></h3>
                    <p><?php echo esc_html($item['value']); ?></p>
                </article>
            <?php endforeach; ?>
            <aside class="cb-capacity-card"><span><?php echo esc_html($section['capacity_label'] ?? 'Monthly Capacity'); ?></span><strong><?php echo esc_html($section['capacity_number'] ?? '2,000,000+'); ?></strong><em>units</em><a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($section['button_url'] ?? '#'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('learn_more')); ?></a></aside>
        </div>
    </div>
</section>
