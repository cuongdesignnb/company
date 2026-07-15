<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_get_rollbacks()
{
    $items = get_option('cb_transfer_rollbacks', []);
    return is_array($items) ? $items : [];
}

function cb_transfer_get_rollback($rollback_id)
{
    $items = cb_transfer_get_rollbacks();
    return is_array($items[$rollback_id] ?? null) ? $items[$rollback_id] : [];
}

function cb_transfer_create_rollback($job_id)
{
    $rollback_id = 'rollback-' . gmdate('YmdHis') . '-' . wp_generate_password(6, false, false);
    $options = [];
    foreach (cb_transfer_option_allowlist() as $key) {
        $value = get_option($key, null);
        $options[$key] = ['exists' => $value !== null, 'value' => $value === null ? null : cb_transfer_strip_secrets($value, $key)];
    }
    $snapshot = [
        'rollback_id' => $rollback_id,
        'job_id' => $job_id,
        'created_at' => current_time('mysql'),
        'status' => 'available',
        'options' => $options,
        'nav_menu_locations' => get_theme_mod('nav_menu_locations', []),
        'posts' => [],
        'terms' => [],
        'created_posts' => [],
        'created_terms' => [],
        'created_files' => [],
    ];
    $items = cb_transfer_get_rollbacks();
    $items[$rollback_id] = $snapshot;
    uasort($items, static fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    $items = array_slice($items, 0, 3, true);
    update_option('cb_transfer_rollbacks', $items, false);
    return $snapshot;
}

function cb_transfer_save_rollback(array $snapshot)
{
    $items = cb_transfer_get_rollbacks();
    $items[$snapshot['rollback_id']] = $snapshot;
    update_option('cb_transfer_rollbacks', array_slice($items, 0, 3, true), false);
}

function cb_transfer_snapshot_post(array &$snapshot, $post_id)
{
    $post_id = absint($post_id);
    if (!$post_id || isset($snapshot['posts'][$post_id])) return;
    $post = get_post($post_id, ARRAY_A);
    if (!$post) return;
    $snapshot['posts'][$post_id] = ['post' => $post, 'meta' => cb_transfer_filter_meta(get_post_meta($post_id))];
    cb_transfer_save_rollback($snapshot);
}

function cb_transfer_snapshot_term(array &$snapshot, $term_id, $taxonomy)
{
    $term_id = absint($term_id);
    if (!$term_id || isset($snapshot['terms'][$term_id])) return;
    $term = get_term($term_id, $taxonomy, ARRAY_A);
    if (!$term || is_wp_error($term)) return;
    $meta = [];
    foreach (get_term_meta($term_id) as $key => $values) {
        if (str_starts_with($key, '_cb_')) $meta[$key] = count($values) === 1 ? maybe_unserialize($values[0]) : array_map('maybe_unserialize', $values);
    }
    $snapshot['terms'][$term_id] = ['taxonomy' => $taxonomy, 'term' => $term, 'meta' => $meta];
    cb_transfer_save_rollback($snapshot);
}

function cb_transfer_rollback_job($rollback_id)
{
    $snapshot = cb_transfer_get_rollback($rollback_id);
    if (!$snapshot || ($snapshot['status'] ?? '') !== 'available') {
        return new WP_Error('rollback_unavailable', __('Snapshot rollback không khả dụng.', 'cb-site-transfer'));
    }

    foreach (array_reverse((array) $snapshot['created_posts']) as $post_id) {
        if (get_post($post_id)) wp_delete_post($post_id, true);
    }
    foreach (array_reverse((array) $snapshot['created_terms']) as $entry) {
        $term_id = absint($entry['term_id'] ?? 0);
        $taxonomy = sanitize_key($entry['taxonomy'] ?? '');
        if ($term_id && taxonomy_exists($taxonomy)) wp_delete_term($term_id, $taxonomy);
    }
    foreach ((array) $snapshot['posts'] as $post_id => $record) {
        if (!get_post($post_id)) continue;
        wp_update_post(wp_slash($record['post']));
        foreach (array_keys(get_post_meta($post_id)) as $key) {
            if (cb_transfer_is_allowed_meta_key($key)) delete_post_meta($post_id, $key);
        }
        foreach ((array) $record['meta'] as $key => $value) update_post_meta($post_id, $key, $value);
    }
    foreach ((array) $snapshot['terms'] as $term_id => $record) {
        $taxonomy = $record['taxonomy'];
        if (!term_exists((int) $term_id, $taxonomy)) continue;
        wp_update_term((int) $term_id, $taxonomy, [
            'name' => $record['term']['name'],
            'slug' => $record['term']['slug'],
            'description' => $record['term']['description'],
            'parent' => absint($record['term']['parent']),
        ]);
        foreach (array_keys(get_term_meta($term_id)) as $key) {
            if (str_starts_with($key, '_cb_')) delete_term_meta($term_id, $key);
        }
        foreach ((array) $record['meta'] as $key => $value) update_term_meta($term_id, $key, $value);
    }
    foreach ((array) $snapshot['options'] as $key => $record) {
        if (!in_array($key, cb_transfer_option_allowlist(), true)) continue;
        !empty($record['exists']) ? update_option($key, $record['value']) : delete_option($key);
    }
    set_theme_mod('nav_menu_locations', (array) ($snapshot['nav_menu_locations'] ?? []));
    $upload = wp_get_upload_dir();
    foreach ((array) $snapshot['created_files'] as $file) {
        if (is_file($file) && cb_transfer_assert_inside($file, $upload['basedir'])) unlink($file);
    }
    flush_rewrite_rules(false);
    wp_cache_flush();
    $snapshot['status'] = 'rolled_back';
    $snapshot['rolled_back_at'] = current_time('mysql');
    cb_transfer_save_rollback($snapshot);
    cb_transfer_update_job($snapshot['job_id'], ['status' => 'rolled_back', 'step' => 'rolled_back', 'progress' => 100]);
    return $snapshot;
}
