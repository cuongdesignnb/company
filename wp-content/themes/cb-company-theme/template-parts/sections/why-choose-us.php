<section <?php echo cb_theme_section_attrs($section, 'why_choose_us', 'cb-why'); ?>>
    <div class="cb-container cb-why-grid">
        <div class="cb-why-copy">
            <?php cb_theme_section_header($section); ?>
            <?php if (!empty($section['button_text'])) : ?><a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($section['button_url']); ?>"><?php echo esc_html($section['button_text']); ?></a><?php endif; ?>
        </div>
        <?php foreach (cb_theme_items($section) as $item) : ?>
            <article class="cb-icon-card"><span>◎</span><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p></article>
        <?php endforeach; ?>
    </div>
</section>
