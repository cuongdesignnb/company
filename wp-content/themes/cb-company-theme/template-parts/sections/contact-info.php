<?php
$whatsapp_qr = $section['whatsapp_qr_url'] ?? '';
$wechat_qr = $section['wechat_qr_url'] ?? '';
$whatsapp_qr = $whatsapp_qr ?: cb_theme_option('contact_whatsapp_qr');
$wechat_qr = $wechat_qr ?: cb_theme_option('contact_wechat_qr');
$whatsapp_number = cb_theme_option('contact_whatsapp') ?: cb_theme_option('contact_phone');
$wechat_id = cb_theme_option('contact_wechat_id', 'wechat') ?: 'wechat';
$qr_labels = cb_theme_lang() === 'zh'
    ? ['title' => '扫码联系我们', 'whatsapp' => 'WhatsApp 联系方式', 'wechat' => '微信联系方式']
    : ['title' => 'Scan to connect', 'whatsapp' => 'WhatsApp contact', 'wechat' => 'WeChat contact'];
?>
<section <?php echo cb_theme_section_attrs($section, 'contact_info', 'cb-soft-band'); ?>>
    <div class="cb-container">
        <?php cb_theme_section_header($section); ?>
        <div class="cb-card-grid">
            <?php foreach (cb_theme_items($section) as $item) : ?>
                <article class="cb-icon-card">
                    <h3><?php echo esc_html($item['label']); ?></h3>
                    <p><?php echo esc_html($item['value']); ?></p>
                    <?php if ($item['url']) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html(cb_theme_t('view_details')); ?></a><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($whatsapp_qr || $wechat_qr) : ?>
            <div class="cb-contact-qr-block">
                <h3><?php echo esc_html($qr_labels['title']); ?></h3>
                <div class="cb-contact-qr-grid">
                    <?php if ($whatsapp_qr) : ?>
                        <figure class="cb-contact-qr-item">
                            <img src="<?php echo esc_url($whatsapp_qr); ?>" alt="<?php echo esc_attr($qr_labels['whatsapp']); ?>">
                            <figcaption><strong>WhatsApp</strong><span><?php echo esc_html($whatsapp_number); ?></span></figcaption>
                        </figure>
                    <?php endif; ?>
                    <?php if ($wechat_qr) : ?>
                        <figure class="cb-contact-qr-item">
                            <img src="<?php echo esc_url($wechat_qr); ?>" alt="<?php echo esc_attr($qr_labels['wechat']); ?>">
                            <figcaption><strong>WeChat</strong><span><?php echo esc_html($wechat_id); ?></span></figcaption>
                        </figure>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
