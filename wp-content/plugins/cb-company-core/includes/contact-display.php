<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_contact_display_wechat_id()
{
    $footer = (array) get_option('cb_footer_settings', []);
    $wechat_id = trim((string) ($footer['contact_wechat_id'] ?? ''));
    $wechat_id = trim((string) preg_replace('/^(?:(?:wechat|微信)\s*)?id\s*[:：]\s*/iu', '', $wechat_id));
    if (in_array(strtolower($wechat_id), ['', 'wechat', '0'], true)) {
        return '';
    }
    return sanitize_text_field($wechat_id);
}

function cb_contact_display_enqueue_assets()
{
    wp_enqueue_script(
        'cb-company-contact-display',
        CB_CORE_URL . 'assets/frontend/contact-display.js',
        [],
        CB_CORE_VERSION,
        true
    );
    wp_localize_script('cb-company-contact-display', 'cbCompanyContactDisplay', [
        'restUrl' => esc_url_raw(rest_url('cb-company/v1/contact-display')),
    ]);
}

function cb_rest_get_contact_display()
{
    $response = rest_ensure_response([
        'wechatId' => cb_contact_display_wechat_id(),
    ]);
    $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    return $response;
}
