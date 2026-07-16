<?php
$source = $section['certificate_source'] ?? 'certificate_posts';
$limit = max(1, min(12, absint($section['limit'] ?? 6)));
$certificate_posts = [];
$manual_items = [];

if ($source === 'manual') {
    $manual_items = array_values(array_filter(cb_theme_items($section), static function ($item) {
        return ($item['enable'] ?? '1') === '1' && (!empty($item['label']) || !empty($item['image']));
    }));
    $manual_items = array_slice($manual_items, 0, $limit);
} else {
    $meta_query = [
        ['key' => '_cb_featured', 'value' => '1'],
        [
            'relation' => 'OR',
            ['key' => '_cb_language', 'value' => cb_theme_lang()],
            ['key' => '_cb_language', 'compare' => 'NOT EXISTS'],
        ],
        [
            'relation' => 'OR',
            ['key' => '_cb_expiry_date', 'compare' => 'NOT EXISTS'],
            ['key' => '_cb_expiry_date', 'value' => ''],
            ['key' => '_cb_expiry_date', 'value' => current_time('Y-m-d'), 'compare' => '>=', 'type' => 'DATE'],
        ],
    ];
    $args = [
        'post_type' => 'certificate',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_query' => $meta_query,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ];
    if (!empty($section['certificate_category'])) {
        $args['tax_query'] = [[
            'taxonomy' => 'certificate_category',
            'field' => 'slug',
            'terms' => sanitize_title($section['certificate_category']),
        ]];
    }
    $query = new WP_Query($args);
    $certificate_posts = $query->posts;
}

if (!$certificate_posts && !$manual_items) {
    return;
}
?>
<section <?php echo cb_theme_section_attrs($section, 'certificates', 'cb-certificate-showcase'); ?>>
    <div class="cb-container">
        <div class="cb-section-heading-row">
            <div><?php cb_theme_section_header($section); ?></div>
            <a class="cb-text-link" href="<?php echo esc_url($section['button_url'] ?: cb_theme_certificate_archive_url()); ?>">
                <?php echo esc_html($section['button_text'] ?: (cb_theme_lang() === 'zh' ? '查看全部证书' : 'View all certificates')); ?>
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
        <div class="cb-certificate-grid" style="--cb-columns:<?php echo esc_attr((string) max(1, min(6, absint($section['columns_desktop'] ?? 3)))); ?>">
            <?php foreach ($certificate_posts as $certificate_post) : $certificate_id = $certificate_post->ID; get_template_part('template-parts/cards/certificate', null, ['certificate_id' => $certificate_id, 'compact' => true]); endforeach; ?>
            <?php foreach ($manual_items as $item) : ?>
                <article class="cb-certificate-card is-manual">
                    <?php if ($item['image']) : ?><a class="cb-certificate-document" href="<?php echo esc_url($item['url'] ?: $item['image']); ?>"><?php echo cb_theme_image($item['image'], $item['label'], '', 560, 760); ?></a><?php endif; ?>
                    <div class="cb-certificate-card-body">
                        <h3><?php echo esc_html($item['label']); ?></h3>
                        <?php if (!empty($item['standard'])) : ?><p class="cb-certificate-standard"><?php echo esc_html($item['standard']); ?></p><?php endif; ?>
                        <?php if (!empty($item['issuer'])) : ?><p><?php echo esc_html($item['issuer']); ?></p><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
