<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_post_types()
{
    return ['page', 'post', 'product', 'factory_showcase', 'case_study', 'video'];
}

function cb_transfer_taxonomies()
{
    return ['category', 'post_tag', 'product_category', 'product_tag', 'factory_category', 'case_category', 'video_category', 'nav_menu'];
}

function cb_transfer_option_allowlist()
{
    return [
        'cb_design_settings', 'cb_header_settings', 'cb_footer_settings', 'cb_template_settings',
        'cb_special_pages', 'cb_string_translations', 'cb_form_settings', 'cb_seo_settings',
        'cb_performance_settings', 'cb_core_db_version', 'cb_demo_content_installed',
        'show_on_front', 'page_on_front', 'page_for_posts', 'blogname', 'blogdescription',
        'permalink_structure',
    ];
}

function cb_transfer_is_allowed_meta_key($key)
{
    if (str_starts_with((string) $key, '_cb_')) {
        return true;
    }
    return in_array($key, ['_thumbnail_id', '_wp_page_template', '_wp_attachment_image_alt', '_wp_attached_file', '_wp_attachment_metadata'], true);
}

function cb_transfer_is_secret_key($key)
{
    return (bool) preg_match('/(?:password|passwd|secret|token|api[_-]?key|smtp[_-]?(?:pass|password)|auth[_-]?key)/i', (string) $key);
}

function cb_transfer_strip_secrets($value, $parent_key = '')
{
    if (!is_array($value)) {
        return cb_transfer_is_secret_key($parent_key) ? '' : $value;
    }
    $clean = [];
    foreach ($value as $key => $item) {
        if (cb_transfer_is_secret_key($key)) {
            continue;
        }
        $clean[$key] = cb_transfer_strip_secrets($item, (string) $key);
    }
    return $clean;
}

function cb_transfer_get_site_uuid()
{
    $uuid = sanitize_text_field((string) get_option('cb_transfer_site_uuid'));
    if (!$uuid) {
        $uuid = wp_generate_uuid4();
        update_option('cb_transfer_site_uuid', $uuid, false);
    }
    return $uuid;
}

function cb_transfer_entity_uuid($kind, $id, $persist = true)
{
    $id = absint($id);
    if (!$id) {
        return '';
    }
    if ($kind === 'term') {
        $uuid = (string) get_term_meta($id, '_cb_transfer_uuid', true);
    } else {
        $uuid = (string) get_post_meta($id, '_cb_transfer_uuid', true);
    }
    if ($uuid) {
        return $uuid;
    }
    $uuid = sprintf('cbsite:%s:%s:%d', cb_transfer_get_site_uuid(), sanitize_key($kind), $id);
    if ($persist) {
        if ($kind === 'term') {
            update_term_meta($id, '_cb_transfer_uuid', $uuid);
        } else {
            update_post_meta($id, '_cb_transfer_uuid', $uuid);
        }
    }
    return $uuid;
}

function cb_transfer_find_post_by_uuid($uuid, $post_type = 'any')
{
    $ids = get_posts([
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_cb_transfer_uuid',
        'meta_value' => sanitize_text_field($uuid),
        'no_found_rows' => true,
    ]);
    return absint($ids[0] ?? 0);
}

function cb_transfer_find_term_by_uuid($uuid, $taxonomy = '')
{
    $args = [
        'taxonomy' => $taxonomy ?: cb_transfer_taxonomies(),
        'hide_empty' => false,
        'number' => 1,
        'fields' => 'ids',
        'meta_key' => '_cb_transfer_uuid',
        'meta_value' => sanitize_text_field($uuid),
    ];
    $ids = get_terms($args);
    return is_wp_error($ids) ? 0 : absint($ids[0] ?? 0);
}

function cb_transfer_map_source_url($value, $source_url, $target_url)
{
    if (!is_string($value) || !$source_url || !$target_url) {
        return $value;
    }
    $source = untrailingslashit($source_url);
    $target = untrailingslashit($target_url);
    if ($value === $source) {
        return $target;
    }
    return str_replace($source . '/', $target . '/', $value);
}

function cb_transfer_remap_data($value, array $job, $field_key = '')
{
    if (is_array($value)) {
        $clean = [];
        foreach ($value as $key => $item) {
            $context = is_string($key) ? $key : $field_key;
            $clean[$key] = cb_transfer_remap_data($item, $job, $context);
        }
        return $clean;
    }

    $mapping = (array) ($job['mapping'] ?? []);
    $post_reference_fields = ['_cb_related_products', '_cb_translated_post_en', '_cb_translated_post_zh', 'page_on_front', 'page_for_posts', 'home', 'about', 'contact'];
    if (is_string($value) && in_array($field_key, $post_reference_fields, true) && preg_match('/^\d+(?:\s*,\s*\d+)+$/', $value)) {
        $mapped_ids = [];
        foreach (preg_split('/\s*,\s*/', $value) as $source_id) {
            $mapped_ids[] = absint($mapping['post_ids'][(string) absint($source_id)] ?? $source_id);
        }
        return implode(',', $mapped_ids);
    }
    if (is_numeric($value)) {
        $source_id = (string) absint($value);
        $attachment_field = (bool) preg_match('/(?:image_id|mobile_image_id|background_image_id|logo_id|favicon_id|footer_logo_id|seo_image_id|attachment_id|_thumbnail_id)$/', $field_key);
        $post_field = in_array($field_key, $post_reference_fields, true);
        if ($attachment_field && isset($mapping['attachment_ids'][$source_id])) {
            return absint($mapping['attachment_ids'][$source_id]);
        }
        if ($post_field && isset($mapping['post_ids'][$source_id])) {
            return absint($mapping['post_ids'][$source_id]);
        }
    }

    return cb_transfer_map_source_url(
        $value,
        (string) ($job['source_url'] ?? ''),
        home_url()
    );
}

function cb_transfer_filter_meta(array $all_meta)
{
    $clean = [];
    foreach ($all_meta as $key => $values) {
        if (!cb_transfer_is_allowed_meta_key($key) || in_array($key, ['_edit_lock', '_edit_last'], true)) {
            continue;
        }
        $value = count($values) === 1 ? maybe_unserialize($values[0]) : array_map('maybe_unserialize', $values);
        $clean[$key] = cb_transfer_strip_secrets($value, $key);
    }
    return $clean;
}
