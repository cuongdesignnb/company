<?php
$items = cb_theme_items($section);
if (!$items) {
    return;
}
?>
<section <?php echo cb_theme_section_attrs($section, 'company_timeline', 'cb-company-timeline'); ?>>
    <div class="cb-container">
        <div class="cb-section-heading cb-heading-centered"><div><?php cb_theme_section_header($section); ?></div></div>
        <div class="cb-timeline-track">
            <?php foreach ($items as $item) : ?>
                <article class="cb-timeline-item">
                    <span class="cb-timeline-dot" aria-hidden="true"></span>
                    <strong><?php echo esc_html($item['year'] ?? ''); ?></strong>
                    <h3><?php echo esc_html($item['title'] ?? ''); ?></h3>
                    <p><?php echo esc_html($item['description'] ?? ''); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
