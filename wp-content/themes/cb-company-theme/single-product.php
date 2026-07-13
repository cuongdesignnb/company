<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
<?php
$post_id = get_the_ID();
$setting = static fn($key, $default = '1') => function_exists('cb_ui_get') ? cb_ui_get($key, 'product_single', 0, $default) : $default;
$gallery = get_post_meta($post_id, '_cb_gallery', true);
$gallery_items = is_array($gallery) ? $gallery : cb_legacy_lines_to_repeater($gallery);
$gallery_url = get_the_post_thumbnail_url($post_id, 'large') ?: ($gallery_items[0]['image_url'] ?? '');
$layout = $setting('product_layout', 'gallery_left');
$labels = cb_theme_lang() === 'zh' ? ['brand' => '品牌', 'voltage' => '电压', 'power' => '功率', 'certification' => '认证', 'lead' => '交货时间', 'specs' => '技术参数', 'catalog' => '下载产品目录'] : ['brand' => 'Brand', 'voltage' => 'Voltage', 'power' => 'Power', 'certification' => 'Certification', 'lead' => 'Lead Time', 'specs' => 'Technical Specifications', 'catalog' => 'Download Catalog'];
?>
<section class="cb-page-band">
    <div class="cb-container">
        <?php if ($setting('show_breadcrumb', '1') === '1' && function_exists('cb_breadcrumb')) cb_breadcrumb(); ?>
        <div class="cb-product-detail cb-product-layout-<?php echo esc_attr($layout); ?>" style="--cb-product-gap:<?php echo esc_attr(cb_sanitize_css_size($setting('column_gap', '42px'), '42px')); ?>">
            <div class="cb-product-gallery"><?php echo cb_theme_image($gallery_url, get_the_title(), 'cb-product-main-image'); ?></div>
            <div class="cb-product-summary<?php echo $setting('sticky_summary', '1') === '1' ? ' is-sticky' : ''; ?>">
                <p class="cb-eyebrow"><?php echo esc_html(get_post_meta($post_id, '_cb_model', true)); ?></p>
                <h1><?php the_title(); ?></h1>
                <?php if ($setting('show_short_description', '1') === '1') : ?><p><?php echo esc_html(get_post_meta($post_id, '_cb_short_description', true) ?: get_the_excerpt()); ?></p><?php endif; ?>
                <?php if ($setting('show_quick_specs', '1') === '1') : ?><dl class="cb-spec-mini">
                    <?php foreach (['_cb_brand' => $labels['brand'], '_cb_voltage' => $labels['voltage'], '_cb_power' => $labels['power'], '_cb_certification' => $labels['certification'], '_cb_moq' => 'MOQ', '_cb_lead_time' => $labels['lead']] as $key => $label) : $value = get_post_meta($post_id, $key, true); if ($value) : ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($value); ?></dd></div><?php endif; endforeach; ?>
                </dl><?php endif; ?>
                <a class="<?php echo esc_attr(cb_theme_button_classes('primary')); ?>" href="#inquiry"><?php echo esc_html(cb_theme_t('get_quote')); ?></a>
                <?php if ($setting('show_catalog', '1') === '1' && get_post_meta($post_id, '_cb_catalog_url', true)) : ?><a class="<?php echo esc_attr(cb_theme_button_classes('outline')); ?>" href="<?php echo esc_url(get_post_meta($post_id, '_cb_catalog_url', true)); ?>"><?php echo esc_html($labels['catalog']); ?></a><?php endif; ?>
            </div>
        </div>
        <article class="cb-content cb-product-content"><?php the_content(); ?></article>
        <?php $specs = cb_parse_lines(get_post_meta($post_id, '_cb_specs', true)); if ($specs) : ?><section class="cb-spec-table"><h2><?php echo esc_html($labels['specs']); ?></h2><table><tbody><?php foreach ($specs as $row) : ?><tr><th><?php echo esc_html($row['label']); ?></th><td><?php echo esc_html($row['value']); ?></td></tr><?php endforeach; ?></tbody></table></section><?php endif; ?>
        <?php if ($setting('show_inquiry', '1') === '1' && get_post_meta($post_id, '_cb_inquiry_enabled', true) !== '0') : ?><section id="inquiry" class="cb-section cb-detail-form"><?php echo do_shortcode('[cb_inquiry_form]'); ?></section><?php endif; ?>
    </div>
</section>
<?php endwhile; ?>
<?php get_footer(); ?>
