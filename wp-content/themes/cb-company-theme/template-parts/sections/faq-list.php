<section <?php echo cb_theme_section_attrs($section, 'faq_list', 'cb-faq-section'); ?>>
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-faq-list">
            <?php foreach (cb_theme_items($section) as $index => $item) : ?>
                <article class="cb-faq-item">
                    <span class="cb-faq-number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    <div><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
