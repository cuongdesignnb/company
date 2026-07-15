<section <?php echo cb_theme_section_attrs($section, 'news_section'); ?>>
    <div class="cb-container">
        <div class="cb-section-heading"><div><?php cb_theme_section_header($section); ?></div><?php if (!empty($section['button_url'])) : ?><a class="cb-text-link" href="<?php echo esc_url($section['button_url']); ?>"><?php echo esc_html($section['button_text'] ?: cb_theme_t('read_more')); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2"/></svg></a><?php endif; ?></div>
        <div class="cb-news-spotlight">
            <?php
            $news = new WP_Query(['post_type' => 'post', 'posts_per_page' => absint($section['limit'] ?? 3) ?: 3, 'meta_query' => [['key' => '_cb_language', 'value' => cb_theme_lang()]]]);
            while ($news->have_posts()) : $news->the_post();
                get_template_part('template-parts/cards/news-card');
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
