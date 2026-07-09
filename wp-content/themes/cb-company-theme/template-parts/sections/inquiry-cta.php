<section id="inquiry" <?php echo cb_theme_section_attrs($section, 'inquiry_cta', 'cb-cta'); ?>>
    <div class="cb-container cb-cta-grid">
        <div><?php cb_theme_section_header($section); ?><a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($section['button_url'] ?? '#inquiry'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('get_quote')); ?></a></div>
        <?php echo do_shortcode('[cb_inquiry_form]'); ?>
    </div>
</section>
