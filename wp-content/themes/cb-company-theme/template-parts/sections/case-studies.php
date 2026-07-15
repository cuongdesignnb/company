<section <?php echo cb_theme_section_attrs($section, 'case_studies'); ?>>
    <div class="cb-container">
        <div class="cb-section-heading"><div><?php cb_theme_section_header($section); ?></div><?php if (!empty($section['button_url'])) : ?><a class="cb-text-link" href="<?php echo esc_url($section['button_url']); ?>"><?php echo esc_html($section['button_text'] ?: cb_theme_t('learn_more')); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2"/></svg></a><?php endif; ?></div>
        <div class="cb-case-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <article class="cb-case-card"><?php echo cb_theme_image($item['image'] ?? '', $item['label'], '', 760, 520); ?><div><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p></div></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
