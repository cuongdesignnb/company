<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_render_inquiry_form($args = [])
{
    $product = is_singular('product') ? get_the_title() : '';
    ob_start();
    if (isset($_GET['cb_inquiry']) && $_GET['cb_inquiry'] === 'sent') {
        echo '<div class="cb-form-success">' . esc_html__('Thank you. Your inquiry has been received.', 'cb-company-core') . '</div>';
    }
    ?>
    <form class="cb-inquiry-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="cb_submit_inquiry">
        <input type="hidden" name="source_url" value="<?php echo esc_url(home_url(add_query_arg([]))); ?>">
        <input type="hidden" name="language" value="<?php echo esc_attr(cb_get_current_language()); ?>">
        <?php wp_nonce_field('cb_submit_inquiry', 'cb_inquiry_nonce'); ?>
        <p class="cb-hp"><label>Website<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
        <label><?php echo esc_html(cb_t('name')); ?><input required name="full_name" type="text"></label>
        <label><?php echo esc_html(cb_t('company')); ?><input name="company_name" type="text"></label>
        <label><?php echo esc_html(cb_t('email')); ?><input required name="email" type="email"></label>
        <label><?php echo esc_html(cb_t('phone')); ?><input name="phone" type="text"></label>
        <label><?php echo esc_html(cb_t('country')); ?><input name="country" type="text"></label>
        <label><?php echo esc_html(cb_t('interested_product')); ?><input name="interested_product" type="text" value="<?php echo esc_attr($product); ?>"></label>
        <label><?php echo esc_html(cb_t('quantity')); ?><input name="quantity" type="text"></label>
        <label class="wide"><?php echo esc_html(cb_t('message')); ?><textarea required name="message" rows="5"></textarea></label>
        <label class="wide cb-consent"><input required type="checkbox" name="consent" value="1"> <?php echo esc_html(cb_t('inquiry_consent')); ?></label>
        <button class="cb-btn cb-btn-primary" type="submit"><?php echo esc_html(cb_t('submit')); ?></button>
    </form>
    <?php
    return ob_get_clean();
}

function cb_handle_inquiry_submission()
{
    if (!isset($_POST['cb_inquiry_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cb_inquiry_nonce'])), 'cb_submit_inquiry')) {
        wp_die(esc_html__('Security check failed.', 'cb-company-core'), 403);
    }
    if (!empty($_POST['website_url'])) {
        wp_safe_redirect(home_url('/'));
        exit;
    }

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $name = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    if (!$email || !$name || !$message || !is_email($email)) {
        wp_die(esc_html__('Please complete required fields with a valid email.', 'cb-company-core'), 400);
    }

    $fields = [
        '_cb_full_name' => $name,
        '_cb_company_name' => sanitize_text_field(wp_unslash($_POST['company_name'] ?? '')),
        '_cb_email' => $email,
        '_cb_phone' => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
        '_cb_country' => sanitize_text_field(wp_unslash($_POST['country'] ?? '')),
        '_cb_interested_product' => sanitize_text_field(wp_unslash($_POST['interested_product'] ?? '')),
        '_cb_quantity' => sanitize_text_field(wp_unslash($_POST['quantity'] ?? '')),
        '_cb_message' => $message,
        '_cb_source_url' => esc_url_raw(wp_unslash($_POST['source_url'] ?? home_url('/'))),
        '_cb_language' => sanitize_key(wp_unslash($_POST['language'] ?? 'en')),
        '_cb_inquiry_status' => 'new',
        '_cb_ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
        '_cb_user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ];

    $post_id = wp_insert_post([
        'post_type' => 'inquiry',
        'post_status' => 'publish',
        'post_title' => sprintf('Inquiry - %s - %s', $name, current_time('Y-m-d H:i')),
    ]);

    if ($post_id && !is_wp_error($post_id)) {
        foreach ($fields as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
        $mail_settings = cb_get_group_options('cb_form_settings', cb_default_form_settings());
        $admin_email = sanitize_email($mail_settings['admin_email'] ?: get_option('admin_email'));
        $language = $fields['_cb_language'] === 'zh' ? 'zh' : 'en';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $admin_subject = $language === 'zh' ? $mail_settings['subject_zh'] : $mail_settings['subject_en'];
        $admin_body = '<p><strong>' . esc_html__('Name', 'cb-company-core') . ':</strong> ' . esc_html($name) . '</p><p><strong>Email:</strong> ' . esc_html($email) . '</p><p><strong>' . esc_html__('Message', 'cb-company-core') . ':</strong><br>' . nl2br(esc_html($message)) . '</p>';
        wp_mail($admin_email, $admin_subject, $admin_body, $headers);
        if (($mail_settings['auto_reply'] ?? '1') === '1') {
            $reply_subject = $language === 'zh' ? '我们已收到您的询价' : 'We received your inquiry';
            $reply_body = $language === 'zh' ? '<p>感谢您的联系。我们的团队会尽快回复。</p>' : '<p>Thank you for contacting us. Our team will reply soon.</p>';
            wp_mail($email, $reply_subject, $reply_body, $headers);
        }
    }

    $redirect = add_query_arg('cb_inquiry', 'sent', esc_url_raw(wp_unslash($_POST['source_url'] ?? home_url('/'))));
    wp_safe_redirect($redirect . '#inquiry');
    exit;
}
