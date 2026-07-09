<section id="capabilities" class="cb-section">
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-factory-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <article class="cb-media-card">
                    <?php echo cb_theme_image($item['image'] ?: ($section['image'] ?? ''), $item['label']); ?>
                    <h3><?php echo esc_html($item['label']); ?></h3>
                    <p><?php echo esc_html($item['value']); ?></p>
                </article>
            <?php endforeach; ?>
            <aside class="cb-capacity-card"><span>Monthly Capacity</span><strong>2,000,000+</strong><em>units</em><a class="cb-btn cb-btn-primary" href="<?php echo esc_url($section['button_url'] ?? '#'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('learn_more')); ?></a></aside>
        </div>
    </div>
</section>
