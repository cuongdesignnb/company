<?php
?>
</main>
<footer class="cb-site-footer">
    <div class="cb-container cb-footer-grid">
        <section>
            <a class="cb-logo cb-logo-footer" href="<?php echo esc_url(home_url('/' . cb_theme_lang() . '/')); ?>">
                <span class="cb-logo-mark">A</span><span><strong><?php echo esc_html(cb_theme_option('logo_text', 'AURELIA')); ?></strong><small><?php echo esc_html(cb_theme_option('logo_subtext', 'MANUFACTURING')); ?></small></span>
            </a>
            <p><?php echo esc_html(cb_theme_option('footer_description')); ?></p>
        </section>
        <section>
            <h3><?php echo esc_html(cb_theme_t('products')); ?></h3>
            <ul>
                <?php foreach (get_terms(['taxonomy' => 'product_category', 'hide_empty' => false, 'number' => 5]) as $term) : ?>
                    <li><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section>
            <h3>Quick Links</h3>
            <ul>
                <li><a href="#about"><?php echo esc_html(cb_theme_t('about_us')); ?></a></li>
                <li><a href="#capabilities"><?php echo esc_html(cb_theme_t('capabilities')); ?></a></li>
                <li><a href="#inquiry"><?php echo esc_html(cb_theme_t('contact_us')); ?></a></li>
            </ul>
        </section>
        <section>
            <h3><?php echo esc_html(cb_theme_t('contact_us')); ?></h3>
            <p><?php echo esc_html(cb_theme_option('company_address')); ?></p>
            <p><?php echo esc_html(cb_theme_option('contact_phone')); ?><br><?php echo esc_html(cb_theme_option('contact_email')); ?></p>
        </section>
    </div>
    <div class="cb-footer-bottom cb-container"><?php echo esc_html(cb_theme_option('copyright_text')); ?></div>
</footer>
<?php if (cb_theme_option('floating_contact', '1') === '1') : ?>
    <a class="cb-floating-quote" href="#inquiry"><?php echo esc_html(cb_theme_t('get_quote')); ?></a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
