<?php
$items = array_values(array_filter(cb_theme_items($section), static fn($item) => ($item['enable'] ?? '1') === '1' && !empty($item['image'])));
if (!$items) {
    return;
}
?>
<section <?php echo cb_theme_section_attrs($section, 'showroom_gallery', 'cb-showroom-gallery'); ?> data-cb-gallery>
    <div class="cb-container cb-showroom-heading">
        <div><?php cb_theme_section_header($section); ?></div>
        <?php if (count($items) > 1) : ?>
            <div class="cb-gallery-controls">
                <button type="button" data-cb-gallery-prev aria-label="<?php echo esc_attr(cb_theme_t('previous_slide')); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                <button type="button" data-cb-gallery-next aria-label="<?php echo esc_attr(cb_theme_t('next_slide')); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            </div>
        <?php endif; ?>
    </div>
    <div class="cb-showroom-stage" data-cb-gallery-track>
        <?php foreach ($items as $index => $item) : ?>
            <figure class="cb-showroom-item<?php echo $index === 0 ? ' is-active' : ''; ?>">
                <?php echo cb_theme_image($item['image'], $item['image_alt'] ?? '', '', 1200, 800); ?>
                <?php if (!empty($item['title']) || !empty($item['description'])) : ?>
                    <figcaption><strong><?php echo esc_html($item['title'] ?? ''); ?></strong><span><?php echo esc_html($item['description'] ?? ''); ?></span></figcaption>
                <?php endif; ?>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
