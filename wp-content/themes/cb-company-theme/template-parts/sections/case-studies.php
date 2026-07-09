<section <?php echo cb_theme_section_attrs($section, 'case_studies'); ?>>
    <div class="cb-container">
        <div class="cb-section-heading"><div><?php cb_theme_section_header($section); ?></div><a href="<?php echo esc_url($section['button_url'] ?? '#'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('learn_more')); ?> →</a></div>
        <div class="cb-card-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <article class="cb-market-card"><?php echo cb_theme_image($item['image'] ?? '', $item['label']); ?><div><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p></div></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
