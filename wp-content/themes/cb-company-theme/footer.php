<?php
$footer_classes = [
    'cb-site-footer',
    'cb-footer-layout-' . sanitize_html_class(cb_theme_option('footer_layout', 'four_columns')),
];
?>
</main>
<?php if (!cb_theme_page_ui_enabled('hide_footer')) : ?>
<footer class="<?php echo esc_attr(implode(' ', $footer_classes)); ?>">
    <div class="cb-container cb-footer-grid">
        <?php if (cb_theme_option_enabled('show_footer_logo')) : ?>
            <section>
                <?php cb_theme_logo('footer'); ?>
                <p><?php echo esc_html(cb_theme_option('footer_description')); ?></p>
            </section>
        <?php endif; ?>
        <?php if (cb_theme_option_enabled('show_footer_products')) : ?>
            <section>
                <h3><?php echo esc_html(cb_theme_t('products')); ?></h3>
                <ul>
                    <?php foreach (get_terms(['taxonomy' => 'product_category', 'hide_empty' => false, 'number' => 5]) as $term) : ?>
                        <li><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
        <?php if (cb_theme_option_enabled('show_footer_links')) : ?>
            <section>
                <h3>Quick Links</h3>
                <?php
                $footer_location = 'footer_' . cb_theme_lang();
                if (has_nav_menu($footer_location)) {
                    wp_nav_menu(['theme_location' => $footer_location, 'container' => false, 'menu_class' => 'cb-footer-menu']);
                } else {
                    echo '<ul><li><a href="#about">' . esc_html(cb_theme_t('about_us')) . '</a></li><li><a href="#capabilities">' . esc_html(cb_theme_t('capabilities')) . '</a></li><li><a href="#inquiry">' . esc_html(cb_theme_t('contact_us')) . '</a></li></ul>';
                }
                ?>
            </section>
        <?php endif; ?>
        <?php if (cb_theme_option_enabled('show_footer_contact')) : ?>
            <section>
                <h3><?php echo esc_html(cb_theme_t('contact_us')); ?></h3>
                <p><?php echo esc_html(cb_theme_option('company_address')); ?></p>
                <p><?php echo esc_html(cb_theme_option('contact_phone')); ?><br><?php echo esc_html(cb_theme_option('contact_email')); ?></p>
                <?php if (cb_theme_option_enabled('show_footer_social')) : ?>
                    <div class="cb-footer-social">
                        <?php foreach (cb_parse_lines(cb_theme_option('social_links')) as $social) : ?>
                            <a href="<?php echo esc_url($social['url'] ?: $social['value']); ?>"><?php echo esc_html($social['label']); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
    <div class="cb-footer-bottom cb-container"><?php echo esc_html(cb_theme_option('copyright_text')); ?></div>
</footer>
<?php endif; ?>
<?php if (cb_theme_option_enabled('floating_contact')) : ?>
    <a class="cb-floating-quote" href="#inquiry"><?php echo esc_html(cb_theme_t('get_quote')); ?></a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
