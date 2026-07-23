<?php
$footer_classes = [
    'cb-site-footer',
    'cb-footer-layout-' . sanitize_html_class(cb_theme_option('footer_layout', 'four_columns')),
];
$footer_image = cb_theme_option('footer_background_image');
$footer_style = $footer_image ? '--cb-footer-image:url(' . esc_url($footer_image) . ')' : '';
?>
</main>
<?php if (!cb_theme_page_ui_enabled('hide_footer')) : ?>
<footer class="<?php echo esc_attr(implode(' ', $footer_classes)); ?>"<?php if ($footer_style) : ?> style="<?php echo esc_attr($footer_style); ?>"<?php endif; ?>>
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
                    <?php foreach (cb_theme_product_terms(5) as $term) : ?>
                        <li><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
        <?php if (cb_theme_option_enabled('show_footer_links')) : ?>
            <section>
                <h3><?php echo esc_html(cb_theme_lang() === 'zh' ? '快捷链接' : 'Quick Links'); ?></h3>
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
    <?php
    $whatsapp_number = cb_theme_option('contact_whatsapp') ?: cb_theme_option('contact_phone');
    $whatsapp_digits = preg_replace('/[^\d]/', '', $whatsapp_number);
    $whatsapp_url = 'https://wa.me/' . $whatsapp_digits;
    $whatsapp_qr = cb_theme_option('contact_whatsapp_qr');
    $wechat_id = cb_theme_option('contact_wechat_id', 'wechat') ?: 'wechat';
    $wechat_qr = cb_theme_option('contact_wechat_qr');
    $contact_labels = cb_theme_lang() === 'zh'
        ? ['phone' => '电话号码', 'wechat_id' => '微信 ID', 'scan' => '扫描二维码联系']
        : ['phone' => 'Phone number', 'wechat_id' => 'WeChat ID', 'scan' => 'Scan to connect'];
    ?>
    <nav class="cb-contact-rail" aria-label="<?php echo esc_attr(cb_theme_t('contact_us')); ?>">
        <?php if ($whatsapp_number && $whatsapp_digits) : ?>
            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr('WhatsApp ' . $whatsapp_number); ?>"<?php if ($whatsapp_qr) : ?> data-cb-contact-qr aria-haspopup="true" aria-expanded="false" aria-controls="cb-whatsapp-qr"<?php endif; ?>>
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6.3 19.1 3.7 20l.9-2.5a8 8 0 1 1 1.7 1.6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.8 8.5c.2-.5.4-.5.7-.5h.5c.2 0 .4 0 .5.4l.6 1.4c.1.3.1.5-.1.7l-.4.5c-.1.1-.2.3-.1.5.4.8 1.1 1.6 2.1 2 .2.1.4.1.5-.1l.6-.7c.2-.2.4-.2.7-.1l1.5.7c.3.1.4.3.4.5 0 .6-.5 1.4-1.1 1.6-.6.3-2 .1-3.6-.8-1.9-1-3.2-2.7-3.6-3.8-.5-1.2 0-2 .3-2.3Z" fill="currentColor"/></svg>
                <span>WhatsApp</span>
                <em class="cb-contact-popover<?php echo $whatsapp_qr ? ' cb-contact-popover-qr' : ''; ?>" id="cb-whatsapp-qr" role="tooltip">
                    <?php if ($whatsapp_qr) : ?><img src="<?php echo esc_url($whatsapp_qr); ?>" alt="<?php echo esc_attr('WhatsApp QR ' . $whatsapp_number); ?>"><?php endif; ?>
                    <?php if ($whatsapp_qr) : ?><strong>WhatsApp</strong><small><?php echo esc_html($contact_labels['scan']); ?></small><?php endif; ?>
                    <small><?php echo esc_html($contact_labels['phone']); ?></small>
                    <code><?php echo esc_html($whatsapp_number); ?></code>
                    <small class="cb-contact-route"><?php echo esc_html($whatsapp_url); ?></small>
                </em>
            </a>
        <?php endif; ?>
        <?php if (cb_theme_option('contact_email')) : ?>
            <a href="mailto:<?php echo esc_attr(cb_theme_option('contact_email')); ?>" aria-label="<?php echo esc_attr(cb_theme_option('contact_email')); ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>E-mail</span>
                <em class="cb-contact-popover" role="tooltip"><?php echo esc_html(cb_theme_option('contact_email')); ?></em>
            </a>
        <?php endif; ?>
        <a class="is-primary" href="weixin://" aria-label="<?php echo esc_attr('WeChat ' . $wechat_id); ?>"<?php if ($wechat_qr) : ?> data-cb-contact-qr aria-haspopup="true" aria-expanded="false" aria-controls="cb-wechat-qr"<?php endif; ?>>
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9.8 5.2C5.9 5.2 3 7.6 3 10.6c0 1.8 1 3.3 2.6 4.3l-.5 1.8 2.1-1a8.8 8.8 0 0 0 2.6.4c3.8 0 6.8-2.4 6.8-5.5s-3-5.4-6.8-5.4Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M14.2 10.8c3.1.3 5.4 2.2 5.4 4.6 0 1.5-.9 2.8-2.2 3.6l.4 1.4-1.7-.8a7.4 7.4 0 0 1-2.1.3c-2.4 0-4.4-1.1-5.2-2.8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="7.5" cy="9.9" r=".7" fill="currentColor"/><circle cx="12" cy="9.9" r=".7" fill="currentColor"/></svg>
            <span>WeChat</span>
            <em class="cb-contact-popover cb-contact-popover-qr" id="cb-wechat-qr" role="tooltip">
                <?php if ($wechat_qr) : ?><img src="<?php echo esc_url($wechat_qr); ?>" alt="<?php echo esc_attr('WeChat QR ' . $wechat_id); ?>"><?php endif; ?>
                <strong>WeChat</strong>
                <?php if ($wechat_qr) : ?><small><?php echo esc_html($contact_labels['scan']); ?></small><?php endif; ?>
                <small><?php echo esc_html($contact_labels['wechat_id']); ?></small>
                <code><?php echo esc_html($wechat_id); ?></code>
                <small class="cb-contact-route">weixin://</small>
            </em>
        </a>
    </nav>
<?php endif; ?>
<?php if (false && cb_theme_option_enabled('floating_contact')) : ?>
    <nav class="cb-contact-rail" aria-label="<?php echo esc_attr(cb_theme_t('contact_us')); ?>">
        <?php if (cb_theme_option('contact_phone')) : ?><a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', cb_theme_option('contact_phone'))); ?>" aria-label="<?php echo esc_attr(cb_theme_option('contact_phone')); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6.7 3.5 9.2 3l2 4.8-2.1 1.4a14 14 0 0 0 5.7 5.7l1.4-2.1 4.8 2-.5 2.5c-.3 1.6-1.7 2.8-3.4 2.7A14 14 0 0 1 4 6.9c-.1-1.7 1.1-3.1 2.7-3.4Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><span><?php echo esc_html(cb_theme_lang() === 'zh' ? '电话' : 'Call'); ?></span></a><?php endif; ?>
        <?php if (cb_theme_option('contact_email')) : ?><a href="mailto:<?php echo esc_attr(cb_theme_option('contact_email')); ?>" aria-label="<?php echo esc_attr(cb_theme_option('contact_email')); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg><span>Email</span></a><?php endif; ?>
        <a class="is-primary" href="#inquiry"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h14v12H8l-3 3V4Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg><span><?php echo esc_html(cb_theme_t('get_quote')); ?></span></a>
    </nav>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
