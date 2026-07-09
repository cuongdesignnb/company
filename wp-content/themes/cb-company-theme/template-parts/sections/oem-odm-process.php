<section class="cb-section cb-process">
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-process-row">
            <?php $i = 1; foreach (cb_theme_items($section) as $item) : ?>
                <article><span><?php echo esc_html(sprintf('%02d', $i++)); ?></span><h3><?php echo esc_html($item['label']); ?></h3><p><?php echo esc_html($item['value']); ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
