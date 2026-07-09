<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_section_types()
{
    return [
        'hero_slider' => 'Hero Slider',
        'company_intro' => 'Company Intro',
        'product_categories' => 'Product Categories',
        'featured_products' => 'Featured Products',
        'why_choose_us' => 'Why Choose Us',
        'factory_capability' => 'Factory Capability',
        'oem_odm_process' => 'OEM/ODM Process',
        'case_studies' => 'Case Studies',
        'certificates' => 'Certificates',
        'news_section' => 'News Section',
        'inquiry_cta' => 'Inquiry CTA',
    ];
}

function cb_default_builder_section($type = 'hero_slider')
{
    return [
        'enable' => '1',
        'type' => $type,
        'admin_label' => '',
        'section_id' => '',
        'section_class' => '',
        'layout_style' => 'default',
        'background_type' => 'default',
        'background_color' => '',
        'background_image_id' => '',
        'background_image_url' => '',
        'text_color' => '',
        'padding_top' => '',
        'padding_bottom' => '',
        'container_width' => '',
        'eyebrow' => '',
        'title' => '',
        'subtitle' => '',
        'description' => '',
        'button_text' => '',
        'button_url' => '',
        'image_id' => '',
        'image_url' => '',
        'image' => '',
        'items' => '',
        'limit' => '',
        'columns_desktop' => '',
        'columns_tablet' => '',
        'columns_mobile' => '',
        'card_style' => '',
        'show_view_all' => '1',
        'view_all_text' => '',
        'view_all_url' => '',
        'capacity_number' => '',
        'capacity_label' => '',
        'rating_number' => '',
        'rating_text' => '',
        'hero_slides' => [],
    ];
}

function cb_normalize_homepage_section($section)
{
    $section = wp_parse_args((array) $section, cb_default_builder_section($section['type'] ?? 'hero_slider'));
    if (!$section['image_url'] && !empty($section['image'])) {
        $section['image_url'] = $section['image'];
    }
    if (!$section['button_text'] && !empty($section['view_all_text'])) {
        $section['button_text'] = $section['view_all_text'];
    }
    if (!$section['button_url'] && !empty($section['view_all_url'])) {
        $section['button_url'] = $section['view_all_url'];
    }
    return $section;
}

function cb_sanitize_homepage_sections($input)
{
    $clean = [];
    foreach ((array) $input as $section) {
        $section = (array) $section;
        $type = cb_sanitize_choice($section['type'] ?? 'hero_slider', array_keys(cb_section_types()), 'hero_slider');
        $row = cb_default_builder_section($type);
        foreach ($row as $key => $default) {
            $value = $section[$key] ?? $default;
            if ($key === 'enable' || $key === 'show_view_all') {
                $row[$key] = $value === '1' ? '1' : '0';
            } elseif (str_ends_with($key, '_id')) {
                $row[$key] = (string) absint($value);
            } elseif (str_contains($key, 'url') || str_ends_with($key, 'image')) {
                $row[$key] = esc_url_raw($value);
            } elseif (str_contains($key, 'color')) {
                $row[$key] = sanitize_hex_color($value) ?: '';
            } elseif (str_contains($key, 'padding') || $key === 'container_width') {
                $row[$key] = cb_sanitize_css_size($value, '');
            } elseif (in_array($key, ['items', 'description'], true)) {
                $row[$key] = sanitize_textarea_field($value);
            } elseif (in_array($key, ['limit', 'columns_desktop', 'columns_tablet', 'columns_mobile'], true)) {
                $row[$key] = (string) absint($value);
            } elseif ($key === 'hero_slides' && is_array($value)) {
                $row[$key] = array_map('cb_sanitize_builder_slide', $value);
            } else {
                $row[$key] = sanitize_text_field($value);
            }
        }
        $clean[] = $row;
    }
    return $clean;
}

function cb_sanitize_builder_slide($slide)
{
    $slide = (array) $slide;
    return [
        'image_id' => (string) absint($slide['image_id'] ?? 0),
        'image_url' => esc_url_raw($slide['image_url'] ?? ''),
        'mobile_image_id' => (string) absint($slide['mobile_image_id'] ?? 0),
        'mobile_image_url' => esc_url_raw($slide['mobile_image_url'] ?? ''),
        'eyebrow' => sanitize_text_field($slide['eyebrow'] ?? ''),
        'title' => sanitize_text_field($slide['title'] ?? ''),
        'highlight_text' => sanitize_text_field($slide['highlight_text'] ?? ''),
        'description' => sanitize_textarea_field($slide['description'] ?? ''),
        'button_1_text' => sanitize_text_field($slide['button_1_text'] ?? ''),
        'button_1_url' => esc_url_raw($slide['button_1_url'] ?? ''),
        'button_1_style' => cb_sanitize_choice($slide['button_1_style'] ?? 'primary', ['primary', 'outline', 'soft'], 'primary'),
        'button_2_text' => sanitize_text_field($slide['button_2_text'] ?? ''),
        'button_2_url' => esc_url_raw($slide['button_2_url'] ?? ''),
        'button_2_style' => cb_sanitize_choice($slide['button_2_style'] ?? 'outline', ['primary', 'outline', 'soft'], 'outline'),
        'text_alignment' => cb_sanitize_choice($slide['text_alignment'] ?? 'left', ['left', 'center', 'right'], 'left'),
        'overlay_enable' => ($slide['overlay_enable'] ?? '1') === '1' ? '1' : '0',
        'overlay_opacity' => (string) min(100, max(0, absint($slide['overlay_opacity'] ?? 40))),
    ];
}

function cb_render_homepage_sections_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'cb-company-core'));
    }
    $sections = get_option('cb_homepage_sections', function_exists('cb_default_homepage_sections') ? cb_default_homepage_sections() : []);
    echo '<div class="wrap cb-admin-shell"><h1>Homepage Builder</h1><form method="post" action="options.php">';
    settings_fields('cb_homepage_sections_group');
    echo '<div class="cb-builder-toolbar"><p>Drag sections to reorder. Use media picker buttons for section images.</p><button type="button" class="button button-primary cb-add-section">Add Section</button></div><div class="cb-sections-list">';
    foreach ((array) $sections as $index => $section) {
        cb_render_builder_section_card($index, cb_normalize_homepage_section($section));
    }
    echo '</div>';
    echo '<script type="text/html" id="cb-section-template">';
    cb_render_builder_section_card('__new__', cb_default_builder_section('hero_slider'));
    echo '</script>';
    submit_button('Save Homepage Builder');
    echo '</form></div>';
}

function cb_render_builder_section_card($index, $section)
{
    $section = cb_normalize_homepage_section($section);
    $title = $section['admin_label'] ?: ($section['title'] ?: cb_section_types()[$section['type']]);
    echo '<article class="cb-section-card"><div class="cb-section-head"><span class="cb-drag-handle">↕</span><span class="cb-section-title"><span class="cb-section-number">' . esc_html((string) ((int) $index + 1)) . '</span>. ' . esc_html($title) . '</span><div class="cb-section-actions"><button type="button" class="button cb-collapse-section">Collapse</button><button type="button" class="button cb-duplicate-section">Duplicate</button><button type="button" class="button cb-remove-section">Remove</button></div></div>';
    echo '<div class="cb-section-body"><div class="cb-section-fields">';
    cb_builder_input($index, 'enable', $section, 'checkbox', 'Enabled');
    cb_builder_input($index, 'type', $section, 'select', 'Section type', cb_section_types());
    cb_builder_input($index, 'admin_label', $section, 'text', 'Admin label');
    cb_builder_input($index, 'layout_style', $section, 'select', 'Layout preset', ['default' => 'Default', 'split' => 'Split', 'centered' => 'Centered', 'image_left' => 'Image left', 'image_right' => 'Image right', 'grid' => 'Grid', 'carousel' => 'Carousel']);
    cb_builder_input($index, 'section_id', $section, 'text', 'Anchor ID');
    cb_builder_input($index, 'section_class', $section, 'text', 'Custom class');
    cb_builder_input($index, 'background_color', $section, 'color', 'Background color');
    cb_builder_input($index, 'text_color', $section, 'color', 'Text color');
    cb_builder_input($index, 'padding_top', $section, 'text', 'Padding top');
    cb_builder_input($index, 'padding_bottom', $section, 'text', 'Padding bottom');
    cb_builder_image($index, 'background_image', $section, 'Background image');
    cb_builder_image($index, 'image', $section, 'Main image');
    cb_builder_input($index, 'eyebrow', $section, 'text', 'Eyebrow');
    cb_builder_input($index, 'title', $section, 'text', 'Title');
    cb_builder_input($index, 'subtitle', $section, 'text', 'Subtitle');
    cb_builder_input($index, 'description', $section, 'textarea', 'Description');
    cb_builder_input($index, 'button_text', $section, 'text', 'Button text');
    cb_builder_input($index, 'button_url', $section, 'text', 'Button URL');
    cb_builder_input($index, 'limit', $section, 'number', 'Item limit');
    cb_builder_input($index, 'columns_desktop', $section, 'number', 'Desktop columns');
    cb_builder_input($index, 'columns_tablet', $section, 'number', 'Tablet columns');
    cb_builder_input($index, 'columns_mobile', $section, 'number', 'Mobile columns');
    cb_builder_input($index, 'items', $section, 'textarea', 'Repeater items', [], 'Title|Description|Image URL, one item per line.');
    echo '</div></div></article>';
}

function cb_builder_input($index, $key, $section, $type, $label, $choices = [], $help = '')
{
    $name = 'cb_homepage_sections[' . $index . '][' . $key . ']';
    $value = $section[$key] ?? '';
    $wide = in_array($type, ['textarea'], true) ? ' cb-wide' : '';
    echo '<label class="' . esc_attr($wide) . '">' . esc_html($label);
    if ($type === 'checkbox') {
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="0"><br><input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . checked($value, '1', false) . '>';
    } elseif ($type === 'select') {
        echo '<select name="' . esc_attr($name) . '">';
        foreach ($choices as $choice => $choice_label) {
            echo '<option value="' . esc_attr($choice) . '" ' . selected($value, $choice, false) . '>' . esc_html($choice_label) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'textarea') {
        echo '<textarea rows="4" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
    } else {
        $class = $type === 'color' ? ' class="cb-color-field"' : '';
        echo '<input' . $class . ' type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
    }
    if ($help) {
        echo '<span class="cb-repeater-help">' . esc_html($help) . '</span>';
    }
    echo '</label>';
}

function cb_builder_image($index, $base, $section, $label)
{
    $id_key = $base . '_id';
    $url_key = $base . '_url';
    cb_admin_image_field([
        'id' => 'cb_section_' . $index . '_' . $base,
        'label' => $label,
        'name_base' => 'cb_homepage_sections[' . $index . ']',
        'id_key' => $id_key,
        'url_key' => $url_key,
        'id_value' => $section[$id_key] ?? '',
        'url_value' => $section[$url_key] ?? ($base === 'image' ? ($section['image'] ?? '') : ''),
    ]);
}
