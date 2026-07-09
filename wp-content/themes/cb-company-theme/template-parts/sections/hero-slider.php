<?php
$slide = !empty($section['hero_slides'][0]) && is_array($section['hero_slides'][0]) ? $section['hero_slides'][0] : [];
$hero_image = $slide['image_url'] ?? ($section['image_url'] ?: ($section['image'] ?? ''));
$hero_title = $slide['title'] ?? ($section['title'] ?? '');
$hero_eyebrow = $slide['eyebrow'] ?? ($section['eyebrow'] ?? '');
$hero_description = $slide['description'] ?? ($section['description'] ?? '');
$button_1_text = $slide['button_1_text'] ?? ($section['button_text'] ?? cb_theme_t('learn_more'));
$button_1_url = $slide['button_1_url'] ?? ($section['button_url'] ?? '#');
?>
<section <?php echo cb_theme_section_attrs($section, 'hero_slider', 'cb-hero'); ?>>
    <div class="cb-container cb-hero-inner">
        <div class="cb-hero-copy">
            <?php if ($hero_eyebrow) : ?><p class="cb-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p><?php endif; ?>
            <h1><?php echo esc_html($hero_title); ?></h1>
            <p><?php echo esc_html($hero_description); ?></p>
            <div class="cb-hero-actions">
                <a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($button_1_url); ?>"><?php echo esc_html($button_1_text); ?></a>
                <a class="<?php echo esc_attr(cb_theme_button_classes('outline')); ?>" href="#about"><?php echo esc_html__('View Our Factory', 'cb-company-theme'); ?></a>
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
