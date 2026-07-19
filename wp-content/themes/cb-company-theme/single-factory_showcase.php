<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
    <section class="cb-page-hero cb-factory-detail-hero"<?php $image = get_the_post_thumbnail_url(get_the_ID(), 'full'); if ($image) echo ' style="background-image:linear-gradient(rgba(18,22,28,.62),rgba(18,22,28,.62)),url(' . esc_url($image) . ')"'; ?>><div class="cb-container"><?php if (function_exists('cb_breadcrumb')) cb_breadcrumb(); ?><p class="cb-eyebrow"><?php echo esc_html(cb_theme_lang() === 'zh' ? '制造能力' : 'MANUFACTURING CAPABILITY'); ?></p><h1><?php the_title(); ?></h1><p><?php echo esc_html(get_the_excerpt()); ?></p></div></section>
    <section class="cb-page-band cb-subpage-band"><div class="cb-container">
        <button class="cb-filter-toggle" type="button" data-cb-filter-toggle aria-expanded="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4" fill="none" stroke="currentColor" stroke-width="2"/></svg><?php echo esc_html(cb_theme_lang() === 'zh' ? '产品与联系' : 'Products and contact'); ?></button>
        <div class="cb-catalog-layout cb-subpage-shell">
            <aside class="cb-catalog-sidebar cb-subpage-sidebar" data-cb-filter-panel><?php cb_theme_catalog_sidebar(); ?></aside>
            <main class="cb-subpage-main"><article class="cb-content cb-longform-content"><?php the_content(); ?></article>
                <?php $gallery = get_post_meta(get_the_ID(), '_cb_gallery', true); if (is_array($gallery) && $gallery) : ?><div class="cb-factory-gallery"><?php foreach ($gallery as $item) : $url = $item['image_url'] ?? ''; if (!$url) continue; ?><figure><?php echo cb_theme_image($url, $item['image_alt'] ?? get_the_title(), '', 1100, 700); ?><?php if (!empty($item['title'])) : ?><figcaption><?php echo esc_html($item['title']); ?></figcaption><?php endif; ?></figure><?php endforeach; ?></div><?php endif; ?>
                <div class="cb-inline-cta"><div><p class="cb-eyebrow"><?php echo esc_html(cb_theme_lang() === 'zh' ? '项目咨询' : 'PROJECT INQUIRY'); ?></p><h2><?php echo esc_html(cb_theme_lang() === 'zh' ? '需要评估您的生产需求？' : 'Need to evaluate your production requirements?'); ?></h2></div><a class="cb-btn cb-btn-primary" href="<?php echo esc_url(cb_theme_contact_page_url()); ?>"><?php echo esc_html(cb_theme_lang() === 'zh' ? '联系我们' : 'Contact our team'); ?></a></div>
            </main>
        </div>
    </div></section>
<?php endwhile; ?>
<?php get_footer(); ?>
