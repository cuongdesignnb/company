<?php
get_header();

$is_zh = cb_theme_lang() === 'zh';
$labels = $is_zh ? [
    'eyebrow' => '影像中心',
    'title' => '视频中心',
    'description' => '通过工厂参观、制造流程与质量检测影像，了解 Aurelia 如何支持厨房电器 OEM/ODM 项目。',
    'all' => '全部视频',
    'featured' => '精选视频',
    'catalog' => '浏览视频',
    'view' => '查看视频',
    'cta_eyebrow' => '项目沟通',
    'cta_title' => '希望进一步了解我们的制造能力？',
    'cta_copy' => '提交目标市场、产品方向和预计采购量，我们的团队将为您安排下一步。',
    'cta_button' => '提交产品需求',
    'empty' => '目前没有可显示的视频。',
] : [
    'eyebrow' => 'AURELIA IN MOTION',
    'title' => 'Video Center',
    'description' => 'Explore factory tours, manufacturing processes and quality testing stories from Aurelia kitchen appliance programs.',
    'all' => 'All Videos',
    'featured' => 'Featured Video',
    'catalog' => 'Explore Videos',
    'view' => 'View Video',
    'cta_eyebrow' => 'START A CONVERSATION',
    'cta_title' => 'Want a closer look at our manufacturing capabilities?',
    'cta_copy' => 'Share your target market, product direction and estimated volume. Our team will recommend the next practical step.',
    'cta_button' => 'Send Product Brief',
    'empty' => 'No videos are available yet.',
];

$archive_url = cb_theme_video_archive_url();
$active_category = sanitize_title((string) get_query_var('video_category'));
$video_posts = [];
while (have_posts()) {
    the_post();
    $video_posts[] = get_post();
}

$featured = null;
if ((int) get_query_var('paged', 1) <= 1 && $video_posts) {
    foreach ($video_posts as $candidate) {
        if (get_post_meta($candidate->ID, '_cb_featured', true) === '1') {
            $featured = $candidate;
            break;
        }
    }
    $featured = $featured ?: $video_posts[0];
    $video_posts = array_values(array_filter($video_posts, static fn($post) => $post->ID !== $featured->ID));
}
?>
<section class="cb-page-hero cb-video-archive-hero">
    <div class="cb-container">
        <?php if (function_exists('cb_breadcrumb')) { cb_breadcrumb(); } ?>
        <p class="cb-eyebrow"><?php echo esc_html($labels['eyebrow']); ?></p>
        <h1><?php echo esc_html($labels['title']); ?></h1>
        <p><?php echo esc_html($labels['description']); ?></p>
    </div>
</section>

<section class="cb-page-band cb-video-archive">
    <div class="cb-container">
        <?php $terms = cb_theme_video_terms(); if ($terms) : ?>
            <nav class="cb-video-filters" aria-label="<?php echo esc_attr($labels['catalog']); ?>">
                <a class="<?php echo $active_category === '' ? 'is-active' : ''; ?>" href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($labels['all']); ?></a>
                <?php foreach ($terms as $term) : ?>
                    <a class="<?php echo $active_category === $term->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('video_category', $term->slug, $archive_url)); ?>"><?php echo esc_html($term->name); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($featured) : setup_postdata($featured); ?>
            <article class="cb-video-featured">
                <a class="cb-video-featured-media" href="<?php echo esc_url(get_permalink($featured)); ?>">
                    <?php echo cb_theme_image(get_the_post_thumbnail_url($featured->ID, 'large'), get_the_title($featured), '', 1100, 620); ?>
                    <span class="cb-video-play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m9 7 8 5-8 5V7Z" fill="currentColor"/></svg></span>
                </a>
                <div class="cb-video-featured-copy">
                    <p class="cb-eyebrow"><?php echo esc_html($labels['featured']); ?></p>
                    <h2><a href="<?php echo esc_url(get_permalink($featured)); ?>"><?php echo esc_html(get_the_title($featured)); ?></a></h2>
                    <p><?php echo esc_html(get_post_meta($featured->ID, '_cb_short_description', true) ?: get_the_excerpt($featured)); ?></p>
                    <?php echo cb_theme_video_meta($featured->ID); ?>
                    <a class="cb-text-link" href="<?php echo esc_url(get_permalink($featured)); ?>"><?php echo esc_html($labels['view']); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                </div>
            </article>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>

        <?php if ($video_posts) : ?>
            <div class="cb-video-section-heading">
                <p class="cb-eyebrow"><?php echo esc_html($labels['catalog']); ?></p>
                <h2><?php echo esc_html($labels['catalog']); ?></h2>
            </div>
            <div class="cb-video-grid">
                <?php foreach ($video_posts as $video_post) : setup_postdata($video_post); ?>
                    <article class="cb-video-card">
                        <a class="cb-video-card-media" href="<?php echo esc_url(get_permalink($video_post)); ?>">
                            <?php echo cb_theme_image(get_the_post_thumbnail_url($video_post->ID, 'large'), get_the_title($video_post), '', 720, 405); ?>
                            <span class="cb-video-play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m9 7 8 5-8 5V7Z" fill="currentColor"/></svg></span>
                        </a>
                        <div class="cb-video-card-body">
                            <?php echo cb_theme_video_meta($video_post->ID); ?>
                            <h2><a href="<?php echo esc_url(get_permalink($video_post)); ?>"><?php echo esc_html(get_the_title($video_post)); ?></a></h2>
                            <p><?php echo esc_html(get_post_meta($video_post->ID, '_cb_short_description', true) ?: get_the_excerpt($video_post)); ?></p>
                        </div>
                    </article>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        <?php elseif (!$featured) : ?>
            <p class="cb-video-empty"><?php echo esc_html($labels['empty']); ?></p>
        <?php endif; ?>

        <div class="cb-pagination"><?php the_posts_pagination(); ?></div>
    </div>
</section>

<section class="cb-video-cta">
    <div class="cb-container cb-video-cta-inner">
        <div><p class="cb-eyebrow"><?php echo esc_html($labels['cta_eyebrow']); ?></p><h2><?php echo esc_html($labels['cta_title']); ?></h2><p><?php echo esc_html($labels['cta_copy']); ?></p></div>
        <a class="cb-btn cb-btn-primary" href="<?php echo esc_url(cb_theme_contact_page_url()); ?>"><?php echo esc_html($labels['cta_button']); ?></a>
    </div>
</section>
<?php get_footer(); ?>
