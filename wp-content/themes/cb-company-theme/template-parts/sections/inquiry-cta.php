<section id="inquiry" class="cb-section cb-cta">
    <div class="cb-container cb-cta-grid">
        <div><?php cb_theme_section_header($section); ?><a class="cb-btn cb-btn-primary" href="<?php echo esc_url($section['button_url'] ?? '#inquiry'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('get_quote')); ?></a></div>
        <?php echo do_shortcode('[cb_inquiry_form]'); ?>
    </div>
</section>
