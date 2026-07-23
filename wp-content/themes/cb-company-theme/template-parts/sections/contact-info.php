<?php
$whatsapp_qr = $section['whatsapp_qr_url'] ?? '';
$wechat_qr = $section['wechat_qr_url'] ?? '';
$whatsapp_qr = $whatsapp_qr ?: cb_theme_option('contact_whatsapp_qr');
$wechat_qr = $wechat_qr ?: cb_theme_option('contact_wechat_qr');
$whatsapp_number = cb_theme_option('contact_whatsapp') ?: cb_theme_option('contact_phone');
$whatsapp_digits = preg_replace('/[^\d]/', '', $whatsapp_number);
$whatsapp_url = $whatsapp_digits ? 'https://wa.me/' . $whatsapp_digits : '';
$wechat_id = trim((string) cb_theme_option('contact_wechat_id', 'wechat'));
$qr_labels = cb_theme_lang() === 'zh'
    ? ['title' => '通过微信联系我们', 'whatsapp' => 'WhatsApp 联系方式', 'wechat' => '微信联系方式', 'wechat_id' => '微信 ID', 'open_whatsapp' => '打开 WhatsApp', 'open_wechat' => '打开微信']
    : ['title' => 'Contact via WeChat', 'whatsapp' => 'WhatsApp contact', 'wechat' => 'WeChat contact', 'wechat_id' => 'WeChat ID', 'open_whatsapp' => 'Open WhatsApp', 'open_wechat' => 'Open WeChat'];
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
                            <figcaption><strong>WhatsApp</strong><span><?php echo esc_html($whatsapp_number); ?></span><?php if ($whatsapp_url) : ?><a class="cb-contact-qr-link" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($qr_labels['open_whatsapp']); ?></a><?php endif; ?></figcaption>
                        </figure>
                    <?php endif; ?>
                    <?php if ($wechat_qr) : ?>
                        <figure class="cb-contact-qr-item">
                            <img src="<?php echo esc_url($wechat_qr); ?>" alt="<?php echo esc_attr($qr_labels['wechat']); ?>">
                            <figcaption>
                                <?php if ($wechat_id) : ?>
                                    <strong><?php echo esc_html($qr_labels['wechat_id']); ?></strong>
                                    <span class="cb-contact-id-value"><?php echo esc_html($wechat_id); ?></span>
                                <?php endif; ?>
                                <a class="cb-contact-qr-link" href="weixin://"><?php echo esc_html($qr_labels['open_wechat']); ?></a>
                            </figcaption>
                        </figure>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
