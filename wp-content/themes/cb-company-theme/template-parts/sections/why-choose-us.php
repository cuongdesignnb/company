<section <?php echo cb_theme_section_attrs($section, 'why_choose_us', 'cb-why'); ?>>
    <div class="cb-container">
        <div class="cb-why-copy">
            <?php cb_theme_section_header($section); ?>
            <?php if (!empty($section['button_text'])) : ?><a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($section['button_url']); ?>"><?php echo esc_html($section['button_text']); ?></a><?php endif; ?>
        </div>
        <div class="cb-advantage-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <article class="cb-advantage-item">
                    <span class="cb-advantage-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 4.5 6v5.5c0 4.5 3 7.7 7.5 9.5 4.5-1.8 7.5-5 7.5-9.5V6L12 3Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.5 12 2.2 2.2 4.8-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
