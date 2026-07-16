<?php
get_header();
while (have_posts()) : the_post();
    $certificate_id = get_the_ID();
    $is_zh = cb_theme_lang() === 'zh';
    $issuer = get_post_meta($certificate_id, '_cb_issuer', true);
    $standard = get_post_meta($certificate_id, '_cb_standard', true);
    $number = get_post_meta($certificate_id, '_cb_certificate_number', true);
    $issue_date = get_post_meta($certificate_id, '_cb_issue_date', true);
    $expiry_date = get_post_meta($certificate_id, '_cb_expiry_date', true);
    $verification_url = get_post_meta($certificate_id, '_cb_verification_url', true);
    $pdf_url = get_post_meta($certificate_id, '_cb_pdf_url', true);
    $expired = function_exists('cb_certificate_is_expired') && cb_certificate_is_expired($certificate_id);
    $image_id = get_post_thumbnail_id($certificate_id);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
    $image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
    $meta_rows = [
        $is_zh ? '标准' : 'Standard' => $standard,
        $is_zh ? '签发机构' : 'Issuer' => $issuer,
        $is_zh ? '证书编号' : 'Certificate number' => $number,
        $is_zh ? '签发日期' : 'Issue date' => $issue_date,
        $is_zh ? '有效期至' : 'Expiry date' => $expiry_date ?: ($is_zh ? '长期有效' : 'No expiry date'),
    ];
    ?>
    <section class="cb-inner-hero cb-certificate-detail-hero"><div class="cb-container">
        <?php if (function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
        <p class="cb-eyebrow"><?php echo esc_html($is_zh ? '资质文件' : 'Certificate document'); ?></p>
        <h1><?php the_title(); ?></h1>
    </div></section>

    <article class="cb-certificate-detail cb-container">
        <div class="cb-certificate-detail-grid">
            <div class="cb-certificate-viewer">
                <?php if ($image_url) : ?>
                    <button type="button" class="cb-certificate-lightbox-trigger" data-cb-lightbox-open aria-label="<?php echo esc_attr($is_zh ? '放大查看证书' : 'Enlarge certificate'); ?>">
                        <?php echo wp_get_attachment_image($image_id, 'large', false, ['alt' => $image_alt ?: get_the_title(), 'loading' => 'eager']); ?>
                        <span><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4 4M11 8v6M8 11h6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                    </button>
                <?php else : ?>
                    <div class="cb-certificate-document-placeholder"><svg aria-hidden="true" viewBox="0 0 48 60"><path d="M7 2h23l11 11v45H7zM30 2v12h11M14 28h20M14 36h20M14 44h13" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></div>
                <?php endif; ?>
            </div>
            <div class="cb-certificate-summary">
                <span class="cb-certificate-validity <?php echo $expired ? 'is-expired' : 'is-current'; ?>"><?php echo esc_html($expired ? ($is_zh ? '已失效' : 'Expired') : ($is_zh ? '有效文件' : 'Valid document')); ?></span>
                <h2><?php echo esc_html($is_zh ? '证书信息' : 'Document information'); ?></h2>
                <dl>
                    <?php foreach ($meta_rows as $label => $value) : if (!$value) continue; ?>
                        <div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($value); ?></dd></div>
                    <?php endforeach; ?>
                </dl>
                <div class="cb-certificate-actions">
                    <?php if ($pdf_url) : ?><a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7zM14 3v5h5M10 13h5M10 17h5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg><?php echo esc_html($is_zh ? '查看 PDF' : 'View PDF'); ?></a><?php endif; ?>
                    <?php if ($verification_url) : ?><a class="<?php echo esc_attr(cb_theme_button_classes('outline')); ?>" href="<?php echo esc_url($verification_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($is_zh ? '在线验证' : 'Verify online'); ?></a><?php endif; ?>
                </div>
                <?php if (get_the_content()) : ?><div class="cb-content cb-certificate-description"><?php the_content(); ?></div><?php endif; ?>
            </div>
        </div>
    </article>

    <?php
    $term_ids = wp_get_post_terms($certificate_id, 'certificate_category', ['fields' => 'ids']);
    $related = new WP_Query([
        'post_type' => 'certificate', 'post_status' => 'publish', 'posts_per_page' => 3,
        'post__not_in' => [$certificate_id],
        'tax_query' => $term_ids && !is_wp_error($term_ids) ? [['taxonomy' => 'certificate_category', 'field' => 'term_id', 'terms' => $term_ids]] : [],
        'meta_query' => [['key' => '_cb_language', 'value' => cb_theme_lang()]],
    ]);
    if ($related->have_posts()) : ?>
        <section class="cb-related-certificates"><div class="cb-container">
            <div class="cb-section-heading-row"><h2><?php echo esc_html($is_zh ? '相关证书' : 'Related certificates'); ?></h2><a class="cb-text-link" href="<?php echo esc_url(cb_theme_certificate_archive_url()); ?>"><?php echo esc_html($is_zh ? '查看全部' : 'View all'); ?></a></div>
            <div class="cb-certificate-grid"><?php while ($related->have_posts()) : $related->the_post(); get_template_part('template-parts/cards/certificate', null, ['certificate_id' => get_the_ID(), 'compact' => true]); endwhile; ?></div>
        </div></section>
    <?php endif; wp_reset_postdata(); ?>

    <section class="cb-certificate-inquiry" id="inquiry"><div class="cb-container">
        <div><p class="cb-eyebrow"><?php echo esc_html($is_zh ? 'OEM/ODM 合作' : 'OEM/ODM partnership'); ?></p><h2><?php echo esc_html($is_zh ? '需要更多合规文件？' : 'Need additional compliance documents?'); ?></h2><p><?php echo esc_html($is_zh ? '请发送产品需求，我们的团队将提供相应的资质和测试资料。' : 'Send your product brief and our team will provide the relevant qualification and testing materials.'); ?></p></div>
        <a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="<?php echo esc_url(home_url('/' . cb_theme_lang() . '/#inquiry')); ?>"><?php echo esc_html($is_zh ? '提交产品需求' : 'Send product brief'); ?></a>
    </div></section>

    <?php if ($image_url) : ?>
        <dialog class="cb-certificate-lightbox" data-cb-lightbox>
            <button type="button" class="cb-lightbox-close" data-cb-lightbox-close aria-label="<?php echo esc_attr($is_zh ? '关闭' : 'Close'); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt ?: get_the_title()); ?>">
        </dialog>
    <?php endif; ?>
<?php endwhile; ?>
<?php get_footer(); ?>
