<?php
get_header();
$is_zh = cb_theme_lang() === 'zh';
$labels = $is_zh ? [
    'eyebrow' => 'AURELIA 视频',
    'overview' => '视频介绍',
    'related' => '更多视频',
    'external' => '在新窗口观看视频',
    'cta_eyebrow' => 'OEM/ODM 合作',
    'cta_title' => '让我们讨论您的产品项目',
    'cta_copy' => '分享产品方向、目标市场和预计采购量，我们将提供实用的下一步建议。',
    'cta_button' => '提交产品需求',
] : [
    'eyebrow' => 'AURELIA VIDEO',
    'overview' => 'Video Overview',
    'related' => 'More Videos',
    'external' => 'Watch video in a new window',
    'cta_eyebrow' => 'OEM/ODM PARTNERSHIP',
    'cta_title' => 'Let us discuss your product program',
    'cta_copy' => 'Share your product direction, target market and estimated volume. We will recommend the next practical step.',
    'cta_button' => 'Send Product Brief',
];
?>
<section class="cb-page-band cb-video-detail">
    <div class="cb-container">
        <?php while (have_posts()) : the_post(); $post_id = get_the_ID(); ?>
            <?php if (function_exists('cb_breadcrumb')) { cb_breadcrumb(); } ?>
            <header class="cb-detail-heading cb-video-detail-heading">
                <p class="cb-eyebrow"><?php echo esc_html($labels['eyebrow']); ?></p>
                <h1><?php the_title(); ?></h1>
                <p><?php echo esc_html(get_post_meta($post_id, '_cb_short_description', true) ?: get_the_excerpt()); ?></p>
                <?php echo cb_theme_video_meta($post_id); ?>
            </header>

            <?php $media_type = ''; $media = cb_theme_video_media($post_id, $media_type); if ($media) : ?>
                <div class="cb-video-player"><?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>

            <article class="cb-content cb-longform-content cb-video-content">
                <p class="cb-eyebrow"><?php echo esc_html($labels['overview']); ?></p>
                <?php the_content(); ?>
                <?php $video_url = get_post_meta($post_id, '_cb_video_url', true); if ($video_url && $media_type === 'external') : ?>
                    <p><a class="cb-btn cb-btn-primary" href="<?php echo esc_url($video_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($labels['external']); ?></a></p>
                <?php endif; ?>
            </article>

            <?php $related = cb_theme_related_videos($post_id, 3); if ($related) : ?>
                <section class="cb-video-related">
                    <div class="cb-video-section-heading"><p class="cb-eyebrow"><?php echo esc_html($labels['related']); ?></p><h2><?php echo esc_html($labels['related']); ?></h2></div>
                    <div class="cb-video-grid">
                        <?php foreach ($related as $related_video) : ?>
                            <article class="cb-video-card">
                                <a class="cb-video-card-media" href="<?php echo esc_url(get_permalink($related_video)); ?>">
                                    <?php echo cb_theme_image(get_the_post_thumbnail_url($related_video->ID, 'large'), get_the_title($related_video), '', 720, 405); ?>
                                    <span class="cb-video-play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m9 7 8 5-8 5V7Z" fill="currentColor"/></svg></span>
                                </a>
                                <div class="cb-video-card-body"><?php echo cb_theme_video_meta($related_video->ID); ?><h3><a href="<?php echo esc_url(get_permalink($related_video)); ?>"><?php echo esc_html(get_the_title($related_video)); ?></a></h3></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</section>

<section class="cb-video-cta">
    <div class="cb-container cb-video-cta-inner">
        <div><p class="cb-eyebrow"><?php echo esc_html($labels['cta_eyebrow']); ?></p><h2><?php echo esc_html($labels['cta_title']); ?></h2><p><?php echo esc_html($labels['cta_copy']); ?></p></div>
        <a class="cb-btn cb-btn-primary" href="<?php echo esc_url(cb_theme_contact_page_url()); ?>"><?php echo esc_html($labels['cta_button']); ?></a>
    </div>
</section>
<?php get_footer(); ?>
