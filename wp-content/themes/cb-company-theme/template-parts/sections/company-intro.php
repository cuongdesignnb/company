<section id="about" <?php echo cb_theme_section_attrs($section, 'company_intro', 'cb-intro'); ?>>
    <div class="cb-container cb-two-col">
        <div><?php echo cb_theme_image($section['image_url'] ?: ($section['image'] ?? ''), $section['title'] ?? '', 'cb-rounded-image'); ?></div>
        <div>
            <?php cb_theme_section_header($section); ?>
            <div class="cb-stats-row">
                <?php $stats = cb_theme_items($section); ?>
                <?php for ($i = 0; $i < count($stats); $i += 2) : ?>
                    <div><strong><?php echo esc_html($stats[$i]['label'] ?? ''); ?></strong><span><?php echo esc_html($stats[$i]['value'] ?? ''); ?></span></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>
