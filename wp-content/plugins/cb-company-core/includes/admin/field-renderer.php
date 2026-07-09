<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_admin_field_wrap($args, $control)
{
    $desc = $args['description'] ?? '';
    echo '<div class="cb-admin-field cb-admin-field-' . esc_attr($args['type'] ?? 'text') . '">';
    echo '<label class="cb-admin-label" for="' . esc_attr($args['id']) . '">' . esc_html($args['label'] ?? $args['id']) . '</label>';
    echo '<div class="cb-admin-control">' . $control;
    if ($desc) {
        echo '<p class="cb-admin-description">' . esc_html($desc) . '</p>';
    }
    echo '</div></div>';
}

function cb_admin_text_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $control = '<input class="regular-text" type="text" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '">';
    cb_admin_field_wrap($args + ['type' => 'text'], $control);
}

function cb_admin_textarea_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $rows = absint($args['rows'] ?? 4);
    $control = '<textarea class="large-text" rows="' . esc_attr((string) $rows) . '" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '">' . esc_textarea($value) . '</textarea>';
    cb_admin_field_wrap($args + ['type' => 'textarea'], $control);
}

function cb_admin_select_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $control = '<select id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '">';
    foreach (($args['choices'] ?? []) as $key => $label) {
        $control .= '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $control .= '</select>';
    cb_admin_field_wrap($args + ['type' => 'select'], $control);
}

function cb_admin_checkbox_field($args)
{
    $value = (string) ($args['value'] ?? $args['default'] ?? '0');
    $control = '<input type="hidden" name="' . esc_attr($args['name']) . '" value="0">';
    $control .= '<label class="cb-toggle"><input type="checkbox" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="1" ' . checked($value, '1', false) . '><span></span></label>';
    cb_admin_field_wrap($args + ['type' => 'checkbox'], $control);
}

function cb_admin_color_field($args)
{
    $value = sanitize_hex_color($args['value'] ?? $args['default'] ?? '') ?: ($args['default'] ?? '#ffffff');
    $control = '<input class="cb-color-field" type="text" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($args['default'] ?? '') . '">';
    cb_admin_field_wrap($args + ['type' => 'color'], $control);
}

function cb_admin_number_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $attrs = '';
    foreach (['min', 'max', 'step'] as $attr) {
        if (isset($args[$attr])) {
            $attrs .= ' ' . $attr . '="' . esc_attr((string) $args[$attr]) . '"';
        }
    }
    $control = '<input class="small-text" type="number" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '"' . $attrs . '>';
    cb_admin_field_wrap($args + ['type' => 'number'], $control);
}

function cb_admin_range_field($args)
{
    $value = $args['value'] ?? $args['default'] ?? '';
    $control = '<input type="range" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" min="' . esc_attr((string) ($args['min'] ?? 0)) . '" max="' . esc_attr((string) ($args['max'] ?? 100)) . '" step="' . esc_attr((string) ($args['step'] ?? 1)) . '"> <output>' . esc_html((string) $value) . '</output>';
    cb_admin_field_wrap($args + ['type' => 'range'], $control);
}

function cb_admin_image_field($args)
{
    $id_value = absint($args['id_value'] ?? 0);
    $url_value = esc_url($args['url_value'] ?? '');
    $name_base = $args['name_base'];
    $id_key = $args['id_key'];
    $url_key = $args['url_key'];
    $preview = $url_value ? '<img src="' . esc_url($url_value) . '" alt="">' : '';
    $control = '<div class="cb-image-field" data-frame-title="' . esc_attr($args['frame_title'] ?? 'Select image') . '">';
    $control .= '<div class="cb-image-preview">' . $preview . '</div>';
    $control .= '<input type="hidden" class="cb-image-id" id="' . esc_attr($args['id']) . '" name="' . esc_attr($name_base . '[' . $id_key . ']') . '" value="' . esc_attr((string) $id_value) . '">';
    $control .= '<input type="hidden" class="cb-image-url" name="' . esc_attr($name_base . '[' . $url_key . ']') . '" value="' . esc_attr($url_value) . '">';
    $control .= '<button type="button" class="button cb-pick-image">Select</button> ';
    $control .= '<button type="button" class="button cb-remove-image">Remove</button></div>';
    cb_admin_field_wrap($args + ['type' => 'image'], $control);
}

function cb_admin_dimension_field($args)
{
    cb_admin_text_field($args + ['description' => ($args['description'] ?? '') . ' Use px, rem, %, vh or vw.']);
}

function cb_admin_repeater_field($args)
{
    $value = $args['value'] ?? '';
    cb_admin_textarea_field($args + [
        'value' => is_array($value) ? wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $value,
        'description' => $args['description'] ?? 'One item per line, using Title|Description|Image URL.',
        'rows' => $args['rows'] ?? 5,
    ]);
}

function cb_admin_enqueue_assets($hook)
{
    if (!str_contains((string) $hook, 'cb-company') && !str_contains((string) $hook, 'cb-homepage')) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style('cb-company-admin', CB_CORE_URL . 'assets/admin/admin.css', [], '1.0.0');
    wp_enqueue_script('cb-company-admin', CB_CORE_URL . 'assets/admin/admin.js', ['jquery', 'wp-color-picker', 'jquery-ui-sortable'], '1.0.0', true);
}
