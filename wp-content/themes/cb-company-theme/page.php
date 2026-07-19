<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
    <?php
    $post_id = get_the_ID();
    $mode = cb_theme_page_render_mode($post_id);
    $show_banner = cb_theme_page_ui_enabled('show_banner');
    $is_about = cb_theme_is_special_page('about', $post_id);
    $is_catalog_page = !$is_about && cb_theme_page_ui('page_layout') === 'sidebar';
    ?>
    <?php if (!$show_banner && $mode === 'builder') : ?><h1 class="cb-sr-only"><?php the_title(); ?></h1><?php endif; ?>
    <?php if ($show_banner) : ?>
        <section class="cb-page-hero cb-custom-page-hero<?php echo $is_about ? ' cb-about-hero' : ''; ?><?php echo cb_theme_page_ui_enabled('hide_banner_mobile') ? ' cb-hide-mobile' : ''; ?>"<?php echo cb_theme_page_banner_style($post_id); ?>>
            <div class="cb-container">
                <?php if (cb_theme_page_ui_enabled('show_breadcrumb', '1') && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
                <h1><?php echo esc_html(cb_theme_page_ui('banner_title', get_the_title())); ?></h1>
                <?php if (cb_theme_page_ui('banner_description')) : ?><p><?php echo esc_html(cb_theme_page_ui('banner_description')); ?></p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($is_about) : ?>
        <?php
        $about_labels = cb_theme_lang() === 'zh'
            ? ['title' => '关于 Aurelia', 'overview' => '公司概况', 'milestones' => '发展历程', 'factory' => '工厂与实验室', 'certificates' => '资质证书', 'quality' => '质量与研发', 'services' => '服务能力', 'contact' => '联系我们', 'all' => '查看全部证书']
            : ['title' => 'About Aurelia', 'overview' => 'Company Overview', 'milestones' => 'Milestones', 'factory' => 'Factory & Laboratory', 'certificates' => 'Certificates', 'quality' => 'Quality & R&D', 'services' => 'Service Commitments', 'contact' => 'Contact', 'all' => 'View all certificates'];
        ?>
        <div class="cb-container cb-about-shell">
            <aside class="cb-about-sidebar">
                <details class="cb-about-mobile-nav">
                    <summary><?php echo esc_html($about_labels['title']); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 9 5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></summary>
                    <nav aria-label="<?php echo esc_attr($about_labels['title']); ?>"><?php cb_theme_about_sidebar_links($about_labels); ?></nav>
                </details>
                <div class="cb-about-desktop-nav">
                    <p class="cb-eyebrow"><?php echo esc_html($about_labels['title']); ?></p>
                    <nav aria-label="<?php echo esc_attr($about_labels['title']); ?>"><?php cb_theme_about_sidebar_links($about_labels); ?></nav>
                </div>
            </aside>
            <div class="cb-about-main">
                <?php if (in_array($mode, ['editor', 'editor_and_builder'], true)) : ?>
                    <section class="cb-page-band" id="overview"><div class="cb-content">
                        <?php if (!$show_banner && cb_theme_page_ui_enabled('show_breadcrumb', '1') && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
                        <?php if (!$show_banner) : ?><h1><?php the_title(); ?></h1><?php endif; ?>
                        <?php the_content(); ?>
                    </div></section>
                <?php endif; ?>
                <?php if (in_array($mode, ['builder', 'editor_and_builder'], true)) cb_render_page_sections($post_id); ?>
            </div>
        </div>
    <?php elseif ($is_catalog_page) : ?>
        <?php $catalog_label = cb_theme_lang() === 'zh' ? '页面导航' : 'Page navigation'; ?>
        <section class="cb-page-band cb-subpage-band">
            <div class="cb-container">
                <button class="cb-filter-toggle" type="button" data-cb-filter-toggle aria-expanded="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4" fill="none" stroke="currentColor" stroke-width="2"/></svg><?php echo esc_html($catalog_label); ?></button>
                <div class="cb-catalog-layout cb-subpage-shell">
                    <aside class="cb-catalog-sidebar cb-subpage-sidebar" data-cb-filter-panel><?php cb_theme_catalog_sidebar(); ?></aside>
                    <main class="cb-subpage-main">
                        <?php if (in_array($mode, ['editor', 'editor_and_builder'], true)) : ?>
                            <div class="cb-content">
                                <?php if (!$show_banner && cb_theme_page_ui_enabled('show_breadcrumb', '1') && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
                                <?php if (!$show_banner) : ?><h1><?php the_title(); ?></h1><?php endif; ?>
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (in_array($mode, ['builder', 'editor_and_builder'], true)) cb_render_page_sections($post_id); ?>
                    </main>
                </div>
            </div>
        </section>
    <?php else : ?>
        <?php if (in_array($mode, ['editor', 'editor_and_builder'], true)) : ?>
            <section class="cb-page-band"><div class="cb-container cb-content">
                <?php if (!$show_banner && cb_theme_page_ui_enabled('show_breadcrumb', '1') && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
                <?php if (!$show_banner) : ?><h1><?php the_title(); ?></h1><?php endif; ?>
                <?php the_content(); ?>
            </div></section>
        <?php endif; ?>
        <?php if (in_array($mode, ['builder', 'editor_and_builder'], true)) cb_render_page_sections($post_id); ?>
    <?php endif; ?>
<?php endwhile; ?>
<?php get_footer(); ?>
