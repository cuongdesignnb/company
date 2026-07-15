<?php
$items = cb_theme_items($section);
if (!$items) {
    return;
}
?>
<section <?php echo cb_theme_section_attrs($section, 'company_stats', 'cb-company-stats'); ?>>
    <div class="cb-container cb-company-stats-grid">
        <?php foreach ($items as $item) : ?>
            <article>
                <strong><?php echo esc_html(($item['number'] ?? '') . ($item['suffix'] ?? '')); ?></strong>
                <span><?php echo esc_html($item['label'] ?? ''); ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>
