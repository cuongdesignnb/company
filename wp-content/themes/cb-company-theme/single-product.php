<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
<section class="cb-page-band">
    <div class="cb-container">
        <?php if (function_exists('cb_breadcrumb')) {
            cb_breadcrumb();
        } ?>
        <div class="cb-product-detail">
            <div class="cb-product-gallery">
                <?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'large') ?: strtok(get_post_meta(get_the_ID(), '_cb_gallery', true), "\n"), get_the_title(), 'cb-product-main-image'); ?>
            </div>
            <div class="cb-product-summary">
                <p class="cb-eyebrow"><?php echo esc_html(get_post_meta(get_the_ID(), '_cb_model', true)); ?></p>
                <h1><?php the_title(); ?></h1>
                <p><?php echo esc_html(get_post_meta(get_the_ID(), '_cb_short_description', true) ?: get_the_excerpt()); ?></p>
                <dl class="cb-spec-mini">
                    <?php foreach (['_cb_brand' => 'Brand', '_cb_voltage' => 'Voltage', '_cb_power' => 'Power', '_cb_certification' => 'Certification', '_cb_moq' => 'MOQ', '_cb_lead_time' => 'Lead Time'] as $key => $label) : ?>
                        <?php if (get_post_meta(get_the_ID(), $key, true)) : ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html(get_post_meta(get_the_ID(), $key, true)); ?></dd></div><?php endif; ?>
                    <?php endforeach; ?>
                </dl>
                <a class="cb-btn cb-btn-primary" href="#inquiry"><?php echo esc_html(cb_theme_t('get_quote')); ?></a>
            </div>
        </div>
        <article class="cb-content cb-product-content"><?php the_content(); ?></article>
        <section class="cb-spec-table">
            <h2>Technical Specifications</h2>
            <table><tbody>
            <?php foreach (cb_parse_lines(get_post_meta(get_the_ID(), '_cb_specs', true)) as $row) : ?>
                <tr><th><?php echo esc_html($row['label']); ?></th><td><?php echo esc_html($row['value']); ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </section>
        <section id="inquiry" class="cb-section cb-detail-form"><?php echo do_shortcode('[cb_inquiry_form]'); ?></section>
    </div>
</section>
<?php endwhile; ?>
<?php get_footer(); ?>
