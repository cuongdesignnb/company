<section class="cb-hero" style="--hero-image:url('<?php echo esc_url($section['image'] ?? ''); ?>')">
    <div class="cb-container cb-hero-inner">
        <div class="cb-hero-copy">
            <?php if (!empty($section['eyebrow'])) : ?><p class="cb-eyebrow"><?php echo esc_html($section['eyebrow']); ?></p><?php endif; ?>
            <h1><?php echo esc_html($section['title'] ?? ''); ?></h1>
            <p><?php echo esc_html($section['description'] ?? ''); ?></p>
            <div class="cb-hero-actions">
                <a class="cb-btn cb-btn-primary" href="<?php echo esc_url($section['button_url'] ?? '#'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('learn_more')); ?></a>
                <a class="cb-btn cb-btn-ghost" href="#about"><?php echo esc_html__('View Our Factory', 'cb-company-theme'); ?></a>
            </div>
            <div class="cb-trust-row">
                <?php foreach (cb_theme_items($section) as $item) : ?>
                    <span><?php echo esc_html($item['label']); ?></span>
                <?php endforeach; ?>
                <span>On-time Delivery</span><span>Global Reach</span>
            </div>
        </div>
    </div>
</section>
