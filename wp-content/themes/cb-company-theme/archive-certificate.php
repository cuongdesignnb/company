<?php
get_header();
$language = cb_theme_lang();
$is_zh = $language === 'zh';
$current_term = is_tax('certificate_category') ? get_queried_object() : null;
$terms = get_terms([
    'taxonomy' => 'certificate_category',
    'hide_empty' => true,
    'meta_query' => [
        'relation' => 'OR',
        ['key' => '_cb_language', 'value' => $language],
        ['key' => '_cb_language', 'compare' => 'NOT EXISTS'],
    ],
]);
$terms = is_wp_error($terms) ? [] : $terms;
?>
<section class="cb-inner-hero cb-certificate-archive-hero">
    <div class="cb-container">
        <?php if (function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
        <p class="cb-eyebrow"><?php echo esc_html($is_zh ? '质量与合规' : 'Quality & Compliance'); ?></p>
        <h1><?php echo esc_html($current_term instanceof WP_Term ? $current_term->name : ($is_zh ? '资质证书' : 'Certificates')); ?></h1>
        <p><?php echo esc_html($is_zh ? '浏览 Aurelia 已发布的质量体系、产品合规及企业资质文件。' : 'Browse Aurelia quality systems, product compliance and corporate qualification documents.'); ?></p>
    </div>
</section>

<section class="cb-certificate-archive" data-cb-certificate-browser>
    <div class="cb-container">
        <?php if ($terms) : ?>
            <nav class="cb-certificate-filters" aria-label="<?php echo esc_attr($is_zh ? '证书分类' : 'Certificate categories'); ?>">
                <a class="<?php echo $current_term ? '' : 'is-active'; ?>" href="<?php echo esc_url(cb_theme_certificate_archive_url()); ?>" data-cb-certificate-filter><?php echo esc_html($is_zh ? '全部' : 'All'); ?></a>
                <?php foreach ($terms as $term) : ?>
                    <a class="<?php echo $current_term && (int) $current_term->term_id === (int) $term->term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url(cb_theme_certificate_archive_url($term)); ?>" data-cb-certificate-filter><?php echo esc_html($term->name); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (have_posts()) : ?>
            <div class="cb-certificate-grid cb-certificate-archive-grid" aria-live="polite">
                <?php while (have_posts()) : the_post(); get_template_part('template-parts/cards/certificate', null, ['certificate_id' => get_the_ID()]); endwhile; ?>
            </div>
            <nav class="cb-pagination" aria-label="<?php echo esc_attr($is_zh ? '证书分页' : 'Certificate pagination'); ?>">
                <?php echo wp_kses_post(paginate_links(['type' => 'list', 'prev_text' => '&lsaquo;', 'next_text' => '&rsaquo;'])); ?>
            </nav>
        <?php else : ?>
            <div class="cb-empty-state">
                <svg aria-hidden="true" viewBox="0 0 48 60"><path d="M7 2h23l11 11v45H7zM30 2v12h11M14 28h20M14 36h20M14 44h13" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                <h2><?php echo esc_html($is_zh ? '暂无已发布证书' : 'No published certificates yet'); ?></h2>
                <p><?php echo esc_html($is_zh ? '正式文件通过审核后将在此显示。' : 'Verified documents will appear here after publication.'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
