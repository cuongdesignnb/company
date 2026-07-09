<section class="cb-section">
    <div class="cb-container">
        <div class="cb-section-heading"><div><?php cb_theme_section_header($section); ?></div><a href="<?php echo esc_url($section['button_url'] ?? '#'); ?>"><?php echo esc_html($section['button_text'] ?? cb_theme_t('read_more')); ?> →</a></div>
        <div class="cb-card-grid">
            <?php
            $news = new WP_Query(['post_type' => 'post', 'posts_per_page' => 3, 'meta_query' => [['key' => '_cb_language', 'value' => cb_theme_lang()]]]);
            while ($news->have_posts()) : $news->the_post();
                get_template_part('template-parts/cards/news-card');
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
