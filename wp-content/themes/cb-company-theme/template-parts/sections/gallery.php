<section <?php echo cb_theme_section_attrs($section, 'gallery'); ?>>
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-card-grid cb-gallery-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <figure class="cb-media-card"><?php echo cb_theme_image($item['image'], $item['label']); ?><figcaption><strong><?php echo esc_html($item['label']); ?></strong><?php if ($item['value']) : ?><p><?php echo esc_html($item['value']); ?></p><?php endif; ?></figcaption></figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
