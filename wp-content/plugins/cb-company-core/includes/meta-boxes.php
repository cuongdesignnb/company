<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_common_meta_fields($post_type = '')
{
    $fields = [
        '_cb_language' => ['Language', 'select', ['en' => 'English', 'zh' => '中文']],
        '_cb_translation_group' => ['Translation Group', 'text'],
        '_cb_featured' => ['Featured', 'checkbox'],
        '_cb_short_description' => ['Short Description', 'textarea'],
        '_cb_gallery' => ['Gallery Image URLs, one per line', 'textarea'],
        '_cb_video_url' => ['Video URL', 'url'],
        '_cb_display_order' => ['Display Order', 'number'],
        '_cb_seo_title' => ['SEO Title', 'text'],
        '_cb_seo_description' => ['SEO Description', 'textarea'],
        '_cb_seo_image' => ['SEO Image URL', 'url'],
    ];

    if ($post_type === 'product') {
        $fields += [
            '_cb_model' => ['Model', 'text'],
            '_cb_brand' => ['Brand', 'text'],
            '_cb_origin' => ['Product Origin', 'text'],
            '_cb_material' => ['Material', 'text'],
            '_cb_voltage' => ['Voltage', 'text'],
            '_cb_power' => ['Power', 'text'],
            '_cb_certification' => ['Certification', 'text'],
            '_cb_moq' => ['MOQ', 'text'],
            '_cb_lead_time' => ['Lead Time', 'text'],
            '_cb_specs' => ['Technical Specs, Label|Value per line', 'textarea'],
            '_cb_catalog_url' => ['Catalog PDF URL', 'url'],
            '_cb_inquiry_enabled' => ['Enable Inquiry Form', 'checkbox'],
        ];
    }

    if ($post_type === 'case_study') {
        $fields += [
            '_cb_client_market' => ['Client / Market', 'text'],
            '_cb_country' => ['Country', 'text'],
            '_cb_problem' => ['Problem', 'textarea'],
            '_cb_solution' => ['Solution', 'textarea'],
            '_cb_result' => ['Result', 'textarea'],
        ];
    }

    if ($post_type === 'inquiry') {
        $fields = [
            '_cb_inquiry_status' => ['Status', 'select', ['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'closed' => 'Closed', 'spam' => 'Spam']],
            '_cb_full_name' => ['Full Name', 'text'],
            '_cb_company_name' => ['Company Name', 'text'],
            '_cb_email' => ['Email', 'text'],
            '_cb_phone' => ['Phone', 'text'],
            '_cb_country' => ['Country', 'text'],
            '_cb_quantity' => ['Quantity', 'text'],
            '_cb_message' => ['Message', 'textarea'],
            '_cb_source_url' => ['Source URL', 'url'],
            '_cb_language' => ['Language', 'select', ['en' => 'English', 'zh' => '中文']],
            '_cb_internal_note' => ['Internal Note', 'textarea'],
        ];
    }

    return $fields;
}

function cb_register_meta_boxes()
{
    foreach (['post', 'page', 'product', 'factory_showcase', 'case_study', 'video', 'inquiry'] as $type) {
        add_meta_box('cb_common_meta', 'Company Fields', 'cb_render_common_meta_box', $type, 'normal', 'high');
    }
}

function cb_render_common_meta_box($post)
{
    wp_nonce_field('cb_save_common_meta', 'cb_common_meta_nonce');
    echo '<div class="cb-admin-grid">';
    foreach (cb_common_meta_fields($post->post_type) as $key => $field) {
        $value = get_post_meta($post->ID, $key, true);
        cb_render_admin_field($key, $field, $value);
    }
    echo '</div>';
}

function cb_render_admin_field($key, $field, $value)
{
    [$label, $type] = $field;
    echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
    if ($type === 'textarea') {
        echo '<textarea style="width:100%;min-height:80px" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">' . esc_textarea($value) . '</textarea>';
    } elseif ($type === 'select') {
        echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
        foreach (($field[2] ?? []) as $option => $option_label) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($value, $option, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'checkbox') {
        echo '<input type="hidden" name="' . esc_attr($key) . '" value="0">';
        echo '<label><input type="checkbox" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="1" ' . checked($value, '1', false) . '> Enabled</label>';
    } else {
        $input_type = in_array($type, ['url', 'number'], true) ? $type : 'text';
        echo '<input style="width:100%" type="' . esc_attr($input_type) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
    }
    echo '</p>';
}

function cb_save_common_meta_boxes($post_id)
{
    if (!isset($_POST['cb_common_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cb_common_meta_nonce'])), 'cb_save_common_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $post_type = get_post_type($post_id);
    foreach (cb_common_meta_fields($post_type) as $key => $field) {
        if (!array_key_exists($key, $_POST)) {
            continue;
        }
        $raw = wp_unslash($_POST[$key]);
        $type = $field[1];
        if ($type === 'textarea') {
            $value = sanitize_textarea_field($raw);
        } elseif ($type === 'url') {
            $value = esc_url_raw($raw);
        } elseif ($type === 'number') {
            $value = (string) absint($raw);
        } elseif ($type === 'checkbox') {
            $value = $raw === '1' ? '1' : '0';
        } else {
            $value = sanitize_text_field($raw);
        }
        update_post_meta($post_id, $key, $value);
    }
}
