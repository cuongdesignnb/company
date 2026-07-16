<?php
$certificate_id = absint($args['certificate_id'] ?? get_the_ID());
$compact = !empty($args['compact']);
$issuer = get_post_meta($certificate_id, '_cb_issuer', true);
$standard = get_post_meta($certificate_id, '_cb_standard', true);
$issue_date = get_post_meta($certificate_id, '_cb_issue_date', true);
$year = $issue_date ? substr($issue_date, 0, 4) : '';
$expired = function_exists('cb_certificate_is_expired') && cb_certificate_is_expired($certificate_id);
$image_id = get_post_thumbnail_id($certificate_id);
$image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
?>
<article class="cb-certificate-card<?php echo $expired ? ' is-expired' : ''; ?>">
    <a class="cb-certificate-document" href="<?php echo esc_url(get_permalink($certificate_id)); ?>">
        <?php if ($image_id) : ?>
            <?php echo wp_get_attachment_image($image_id, 'large', false, ['alt' => $image_alt ?: get_the_title($certificate_id), 'loading' => 'lazy']); ?>
        <?php else : ?>
            <span class="cb-certificate-file-icon"><svg aria-hidden="true" viewBox="0 0 48 60"><path d="M7 2h23l11 11v45H7zM30 2v12h11M14 28h20M14 36h20M14 44h13" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></span>
        <?php endif; ?>
        <?php if ($expired && !$compact) : ?><span class="cb-certificate-status is-expired"><?php echo esc_html(cb_theme_lang() === 'zh' ? '已失效' : 'Expired'); ?></span><?php endif; ?>
    </a>
    <div class="cb-certificate-card-body">
        <?php if ($standard) : ?><p class="cb-certificate-standard"><?php echo esc_html($standard); ?></p><?php endif; ?>
        <h3><a href="<?php echo esc_url(get_permalink($certificate_id)); ?>"><?php echo esc_html(get_the_title($certificate_id)); ?></a></h3>
        <?php if ($issuer || $year) : ?><p class="cb-certificate-meta"><?php echo esc_html(implode(' · ', array_filter([$issuer, $year]))); ?></p><?php endif; ?>
    </div>
</article>
