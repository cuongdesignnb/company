<section <?php echo cb_theme_section_attrs($section, 'gallery'); ?>>
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-card-grid cb-gallery-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <?php if (($item['enable'] ?? '1') !== '1' || empty($item['image'])) continue; ?>
                <figure class="cb-media-card"><?php echo cb_theme_image($item['image'], $item['image_alt'] ?? $item['label'], '', 1200, 760); ?><figcaption><?php if ($item['label']) : ?><strong><?php echo esc_html($item['label']); ?></strong><?php endif; ?><?php if ($item['value']) : ?><p><?php echo esc_html($item['value']); ?></p><?php endif; ?></figcaption></figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
