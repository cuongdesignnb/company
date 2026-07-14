<section id="inquiry" <?php echo cb_theme_section_attrs($section, 'inquiry_cta', 'cb-cta'); ?>>
    <div class="cb-container cb-cta-grid">
        <div><?php cb_theme_section_header($section); ?><div class="cb-hero-actions">
            <?php if (!empty($section['items'])) : foreach ((array) $section['items'] as $button) : ?>
                <?php if (!empty($button['text']) && !empty($button['url'])) : ?><a class="<?php echo esc_attr(cb_theme_button_classes($button['style'] ?? 'primary')); ?>" href="<?php echo esc_url($button['url']); ?>"><?php echo esc_html($button['text']); ?></a><?php endif; ?>
            <?php endforeach; elseif (!empty($section['button_text']) && !empty($section['button_url'])) : ?>
                <a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($section['button_url']); ?>"><?php echo esc_html($section['button_text']); ?></a>
            <?php endif; ?>
        </div></div>
        <?php echo do_shortcode('[cb_inquiry_form]'); ?>
    </div>
</section>
