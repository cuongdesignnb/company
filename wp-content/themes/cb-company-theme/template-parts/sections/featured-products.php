<section class="cb-section cb-soft-band">
    <div class="cb-container">
        <div class="cb-section-heading">
            <div><?php cb_theme_section_header($section); ?></div>
        </div>
        <div class="cb-product-row">
            <?php
            $q = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => 6,
                'meta_query' => [
                    ['key' => '_cb_featured', 'value' => '1'],
                    ['key' => '_cb_language', 'value' => cb_theme_lang()],
                ],
            ]);
            while ($q->have_posts()) : $q->the_post();
                get_template_part('template-parts/cards/product-card');
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
