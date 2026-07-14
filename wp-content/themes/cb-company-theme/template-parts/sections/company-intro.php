<section id="about" <?php echo cb_theme_section_attrs($section, 'company_intro', 'cb-intro'); ?>>
    <div class="cb-container cb-two-col">
        <div><?php echo cb_theme_image($section['image_url'] ?: ($section['image'] ?? ''), $section['title'] ?? '', 'cb-rounded-image'); ?></div>
        <div>
            <?php cb_theme_section_header($section); ?>
            <div class="cb-stats-row">
                <?php foreach (cb_theme_items($section) as $stat) : ?>
                    <div><strong><?php echo esc_html(($stat['number'] ?? $stat['label'] ?? '') . ($stat['suffix'] ?? '')); ?></strong><span><?php echo esc_html(isset($stat['number']) ? ($stat['label'] ?? '') : ($stat['value'] ?? '')); ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
