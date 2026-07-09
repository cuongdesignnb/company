<section <?php echo cb_theme_section_attrs($section, 'certificates', 'cb-cert-band'); ?>>
    <div class="cb-container cb-cert-grid">
        <div><?php cb_theme_section_header($section); ?></div>
        <?php foreach (cb_theme_items($section) as $item) : ?>
            <article><strong><?php echo esc_html($item['label']); ?></strong><span><?php echo esc_html($item['value']); ?></span></article>
        <?php endforeach; ?>
        <aside><strong><?php echo esc_html($section['rating_number'] ?: '4.9/5'); ?></strong><span><?php echo esc_html($section['rating_text'] ?: 'Customer Rating'); ?></span></aside>
    </div>
</section>
