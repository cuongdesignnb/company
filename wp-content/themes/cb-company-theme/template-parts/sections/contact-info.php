<section <?php echo cb_theme_section_attrs($section, 'contact_info', 'cb-soft-band'); ?>>
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-card-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?><article class="cb-icon-card"><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p><?php if ($item['url']) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html(cb_theme_t('view_details')); ?></a><?php endif; ?></article><?php endforeach; ?>
        </div>
    </div>
</section>
