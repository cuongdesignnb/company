<?php if (empty($section['section_id'])) $section['section_id'] = 'about'; ?>
<section <?php echo cb_theme_section_attrs($section, 'company_intro', 'cb-intro'); ?>>
    <div class="cb-container cb-intro-layout">
        <div class="cb-intro-copy">
            <?php cb_theme_section_header($section); ?>
            <?php if (!empty($section['button_text']) && !empty($section['button_url'])) : ?>
                <a class="cb-text-link" href="<?php echo esc_url($section['button_url']); ?>"><?php echo esc_html($section['button_text']); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            <?php endif; ?>
        </div>
        <div class="cb-intro-collage">
            <?php echo cb_theme_image($section['image_url'] ?? '', $section['title'] ?? '', 'cb-intro-image-main', 900, 680); ?>
            <?php echo cb_theme_image($section['secondary_image_url'] ?? '', $section['title'] ?? '', 'cb-intro-image-secondary', 560, 680); ?>
            <?php echo cb_theme_image($section['tertiary_image_url'] ?? '', $section['title'] ?? '', 'cb-intro-image-tertiary', 560, 680); ?>
        </div>
    </div>
</section>
