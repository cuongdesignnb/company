<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_import_modes()
{
    return ['upsert', 'create_only', 'overwrite'];
}

function cb_transfer_acquire_import_lock($job_id, $token)
{
    $lock = get_transient('cb_transfer_import_lock');
    if (is_array($lock) && ($lock['job_id'] ?? '') !== $job_id) {
        return new WP_Error('import_locked', __('Một import khác đang chạy.', 'cb-site-transfer'));
    }
    set_transient('cb_transfer_import_lock', ['job_id' => $job_id, 'token' => $token], 30 * MINUTE_IN_SECONDS);
    return true;
}

function cb_transfer_release_import_lock($job_id)
{
    $lock = get_transient('cb_transfer_import_lock');
    if (is_array($lock) && ($lock['job_id'] ?? '') === $job_id) delete_transient('cb_transfer_import_lock');
}

function cb_transfer_start_import($job_id, $mode = 'upsert', $dry_run = false)
{
    $job = cb_transfer_get_job($job_id);
    if (!$job || $job['type'] !== 'import') return new WP_Error('job_missing', __('Import job không tồn tại.', 'cb-site-transfer'));
    if (!empty($job['preflight']['blocking_errors'])) return new WP_Error('preflight_blocked', implode(' ', $job['preflight']['blocking_errors']));
    $mode = in_array($mode, cb_transfer_import_modes(), true) ? $mode : 'upsert';
    if ($dry_run) {
        $plan = $job['preflight'];
        $counts = (array) ($plan['counts'] ?? []);
        $conflicts = array_sum(array_map('absint', (array) ($plan['conflicts'] ?? [])));
        $total = absint($counts['posts'] ?? 0) + absint($counts['terms'] ?? 0) + absint($counts['attachments'] ?? 0) + absint($counts['menus'] ?? 0);
        $plan['import_plan'] = [
            'created' => max(0, $total - $conflicts),
            'updated' => $mode === 'create_only' ? 0 : $conflicts,
            'skipped' => $mode === 'create_only' ? $conflicts : 0,
        ];
        return cb_transfer_update_job($job_id, [
            'status' => 'pending', 'step' => 'preflight', 'progress' => 10, 'mode' => $mode, 'dry_run' => true, 'dry_run_completed' => true,
            'preflight' => $plan,
            'report' => ['dry_run' => true, 'plan' => $plan['import_plan'], 'message' => __('Dry run hoàn tất, không ghi dữ liệu.', 'cb-site-transfer')],
        ]);
    }
    $locked = cb_transfer_acquire_import_lock($job_id, $job['lock_token']);
    if (is_wp_error($locked)) return $locked;
    $snapshot = cb_transfer_create_rollback($job_id);
    return cb_transfer_update_job($job_id, [
        'status' => 'running', 'step' => 'terms', 'progress' => 12, 'mode' => $mode, 'dry_run' => false,
        'rollback_id' => $snapshot['rollback_id'], 'offsets' => ['terms' => 0, 'attachments' => 0, 'posts' => 0, 'relationships' => 0],
        'report' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
    ]);
}

function cb_transfer_load_job_data(array $job, $name)
{
    $path = trailingslashit($job['workspace']) . 'data/' . sanitize_file_name($name) . '.json';
    return cb_transfer_read_json($path);
}

function cb_transfer_record_result(array &$job, $result)
{
    $key = in_array($result, ['created', 'updated', 'skipped'], true) ? $result : 'skipped';
    $job['report'][$key] = absint($job['report'][$key] ?? 0) + 1;
}

function cb_transfer_import_term_record(array $record, array &$job, array &$snapshot)
{
    $taxonomy = sanitize_key($record['taxonomy'] ?? '');
    if (!taxonomy_exists($taxonomy)) return new WP_Error('taxonomy_missing', sprintf(__('Taxonomy chưa đăng ký: %s', 'cb-site-transfer'), $taxonomy));
    $uuid = sanitize_text_field($record['source_uuid'] ?? '');
    $existing = cb_transfer_find_term_by_uuid($uuid, $taxonomy);
    if (!$existing) {
        $slug_match = get_term_by('slug', sanitize_title($record['slug'] ?? ''), $taxonomy);
        if ($slug_match) $existing = absint($slug_match->term_id);
    }
    $mode = $job['mode'];
    if ($existing && $mode === 'create_only') {
        $term_id = $existing;
        $result = 'skipped';
    } elseif ($existing) {
        cb_transfer_snapshot_term($snapshot, $existing, $taxonomy);
        $updated = wp_update_term($existing, $taxonomy, [
            'name' => sanitize_text_field($record['name'] ?? ''),
            'slug' => sanitize_title($record['slug'] ?? ''),
            'description' => wp_kses_post($record['description'] ?? ''),
        ]);
        if (is_wp_error($updated)) return $updated;
        $term_id = absint($updated['term_id']);
        $result = 'updated';
    } else {
        $inserted = wp_insert_term(sanitize_text_field($record['name'] ?? ''), $taxonomy, [
            'slug' => sanitize_title($record['slug'] ?? ''),
            'description' => wp_kses_post($record['description'] ?? ''),
        ]);
        if (is_wp_error($inserted)) return $inserted;
        $term_id = absint($inserted['term_id']);
        $snapshot['created_terms'][] = ['term_id' => $term_id, 'taxonomy' => $taxonomy];
        $result = 'created';
    }
    if ($result !== 'skipped') {
        update_term_meta($term_id, '_cb_transfer_uuid', $uuid);
        foreach ((array) ($record['meta'] ?? []) as $key => $value) {
            if (str_starts_with($key, '_cb_')) update_term_meta($term_id, $key, cb_transfer_remap_data($value, $job, $key));
        }
    }
    $job['mapping']['terms'][$uuid] = $term_id;
    $job['mapping']['term_ids'][(string) absint($record['source_id'] ?? 0)] = $term_id;
    $job['mapping']['term_actions'][$uuid] = $result;
    cb_transfer_save_rollback($snapshot);
    return $result;
}

function cb_transfer_find_attachment_by_sha($sha)
{
    if (!$sha) return 0;
    $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_cb_transfer_sha256', 'meta_value' => $sha, 'no_found_rows' => true]);
    return absint($ids[0] ?? 0);
}

function cb_transfer_import_attachment_record(array $record, array &$job, array &$snapshot)
{
    $uuid = sanitize_text_field($record['source_uuid'] ?? '');
    $existing = cb_transfer_find_post_by_uuid($uuid, 'attachment');
    if (!$existing) $existing = cb_transfer_find_attachment_by_sha(sanitize_text_field($record['original_sha256'] ?? ''));
    if ($existing) {
        if ($job['mode'] !== 'create_only') {
            cb_transfer_snapshot_post($snapshot, $existing);
            wp_update_post(wp_slash([
                'ID' => $existing,
                'post_title' => sanitize_text_field($record['post_title'] ?? ''),
                'post_excerpt' => sanitize_textarea_field($record['post_excerpt'] ?? ''),
                'post_content' => wp_kses_post($record['post_content'] ?? ''),
            ]));
            update_post_meta($existing, '_wp_attachment_image_alt', sanitize_text_field($record['alt_text'] ?? ''));
            $result = 'updated';
        } else {
            $result = 'skipped';
        }
        $attachment_id = $existing;
    } else {
        $relative = cb_transfer_safe_relative_path($record['relative_path'] ?? '');
        $files = (array) ($record['files'] ?? []);
        $original = $files[0] ?? [];
        $package_relative = cb_transfer_safe_relative_path($original['package_path'] ?? '');
        $source = $package_relative ? trailingslashit($job['workspace']) . $package_relative : '';
        if (!$relative || !$source || !is_file($source)) return new WP_Error('media_missing', __('Thiếu file media gốc.', 'cb-site-transfer'));
        $mime = cb_transfer_allowed_mime($source);
        if (is_wp_error($mime)) return $mime;
        if (!hash_equals(strtolower((string) ($original['sha256'] ?? '')), hash_file('sha256', $source))) return new WP_Error('media_checksum', __('Checksum media không hợp lệ.', 'cb-site-transfer'));

        $upload = wp_get_upload_dir();
        $target_dir = trailingslashit($upload['basedir']) . dirname($relative);
        if (!cb_transfer_assert_inside($target_dir, $upload['basedir']) || !wp_mkdir_p($target_dir)) return new WP_Error('media_target', __('Không tạo được thư mục media đích.', 'cb-site-transfer'));
        $filename = basename($relative);
        if (file_exists($target_dir . '/' . $filename)) $filename = wp_unique_filename($target_dir, $filename);
        $target = trailingslashit($target_dir) . $filename;
        if (!copy($source, $target)) return new WP_Error('media_copy', __('Không copy được media.', 'cb-site-transfer'));
        $snapshot['created_files'][] = $target;

        $attachment_id = wp_insert_attachment(wp_slash([
            'post_mime_type' => $mime['type'],
            'post_title' => sanitize_text_field($record['post_title'] ?? ''),
            'post_excerpt' => sanitize_textarea_field($record['post_excerpt'] ?? ''),
            'post_content' => wp_kses_post($record['post_content'] ?? ''),
            'post_status' => 'inherit',
            'post_date' => sanitize_text_field($record['post_date'] ?? current_time('mysql')),
            'post_date_gmt' => sanitize_text_field($record['post_date_gmt'] ?? current_time('mysql', true)),
        ]), $target);
        if (!$attachment_id || is_wp_error($attachment_id)) return is_wp_error($attachment_id) ? $attachment_id : new WP_Error('attachment_insert', __('Không tạo được attachment.', 'cb-site-transfer'));
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($attachment_id, $target);
        if (is_array($metadata)) wp_update_attachment_metadata($attachment_id, $metadata);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($record['alt_text'] ?? ''));
        update_post_meta($attachment_id, '_cb_transfer_uuid', $uuid);
        update_post_meta($attachment_id, '_cb_transfer_sha256', sanitize_text_field($record['original_sha256'] ?? ''));
        $snapshot['created_posts'][] = $attachment_id;
        cb_transfer_save_rollback($snapshot);
        $result = 'created';
    }
    $job['mapping']['attachments'][$uuid] = $attachment_id;
    $job['mapping']['attachment_ids'][(string) absint($record['source_id'] ?? 0)] = $attachment_id;
    return $result;
}

function cb_transfer_valid_post_status($status)
{
    return in_array($status, ['publish', 'draft', 'pending', 'private', 'future'], true) ? $status : 'draft';
}

function cb_transfer_import_post_record(array $record, array &$job, array &$snapshot)
{
    $post_type = sanitize_key($record['post_type'] ?? 'post');
    if (!in_array($post_type, array_merge(cb_transfer_post_types(), ['inquiry']), true)) return new WP_Error('post_type', __('Post type không được phép.', 'cb-site-transfer'));
    $uuid = sanitize_text_field($record['source_uuid'] ?? '');
    $existing = cb_transfer_find_post_by_uuid($uuid, $post_type);
    $postarr = [
        'post_type' => $post_type,
        'post_status' => cb_transfer_valid_post_status($record['post_status'] ?? 'draft'),
        'post_title' => sanitize_text_field($record['post_title'] ?? ''),
        'post_name' => sanitize_title($record['post_name'] ?? ''),
        'post_content' => wp_kses_post(cb_transfer_map_source_url($record['post_content'] ?? '', $job['source_url'], home_url())),
        'post_excerpt' => wp_kses_post(cb_transfer_map_source_url($record['post_excerpt'] ?? '', $job['source_url'], home_url())),
        'post_date' => sanitize_text_field($record['post_date'] ?? current_time('mysql')),
        'post_date_gmt' => sanitize_text_field($record['post_date_gmt'] ?? current_time('mysql', true)),
        'post_parent' => 0,
        'menu_order' => intval($record['menu_order'] ?? 0),
        'comment_status' => in_array($record['comment_status'] ?? 'closed', ['open', 'closed'], true) ? $record['comment_status'] : 'closed',
    ];
    if ($existing && $job['mode'] === 'create_only') {
        $post_id = $existing;
        $result = 'skipped';
    } elseif ($existing) {
        cb_transfer_snapshot_post($snapshot, $existing);
        $postarr['ID'] = $existing;
        $post_id = wp_update_post(wp_slash($postarr), true);
        if (is_wp_error($post_id)) return $post_id;
        if ($job['mode'] === 'overwrite') {
            foreach (array_keys(get_post_meta($post_id)) as $key) if (cb_transfer_is_allowed_meta_key($key)) delete_post_meta($post_id, $key);
        }
        $result = 'updated';
    } else {
        $post_id = wp_insert_post(wp_slash($postarr), true);
        if (is_wp_error($post_id)) return $post_id;
        $snapshot['created_posts'][] = $post_id;
        $result = 'created';
    }
    if ($result !== 'skipped') {
        update_post_meta($post_id, '_cb_transfer_uuid', $uuid);
        foreach ((array) ($record['meta'] ?? []) as $key => $value) {
            if (!cb_transfer_is_allowed_meta_key($key) || $key === '_thumbnail_id') continue;
            update_post_meta($post_id, $key, cb_transfer_remap_data($value, $job, $key));
        }
        if (!empty($record['page_template'])) update_post_meta($post_id, '_wp_page_template', sanitize_file_name($record['page_template']));
    }
    $job['mapping']['posts'][$uuid] = $post_id;
    $job['mapping']['post_ids'][(string) absint($record['source_id'] ?? 0)] = $post_id;
    $job['mapping']['post_actions'][$uuid] = $result;
    cb_transfer_save_rollback($snapshot);
    return $result;
}

function cb_transfer_link_term_relationships(array $terms, array &$job)
{
    foreach ($terms as $record) {
        $term_id = absint($job['mapping']['terms'][$record['source_uuid']] ?? 0);
        if (!$term_id) continue;
        if ($job['mode'] === 'create_only' && ($job['mapping']['term_actions'][$record['source_uuid']] ?? '') === 'skipped') continue;
        $parent_id = absint($job['mapping']['terms'][$record['parent_uuid'] ?? ''] ?? 0);
        if ($parent_id) wp_update_term($term_id, $record['taxonomy'], ['parent' => $parent_id]);
        foreach ((array) ($record['meta'] ?? []) as $key => $value) {
            if (str_starts_with($key, '_cb_')) update_term_meta($term_id, $key, cb_transfer_remap_data($value, $job, $key));
        }
    }
}

function cb_transfer_link_post_record(array $record, array &$job)
{
    $post_id = absint($job['mapping']['posts'][$record['source_uuid']] ?? 0);
    if (!$post_id) return 'skipped';
    if ($job['mode'] === 'create_only' && ($job['mapping']['post_actions'][$record['source_uuid']] ?? '') === 'skipped') return 'skipped';
    $parent_id = absint($job['mapping']['posts'][$record['post_parent_uuid'] ?? ''] ?? 0);
    if ($parent_id && (int) get_post_field('post_parent', $post_id) !== $parent_id) wp_update_post(['ID' => $post_id, 'post_parent' => $parent_id]);
    foreach ((array) ($record['taxonomies'] ?? []) as $taxonomy => $uuids) {
        if (!taxonomy_exists($taxonomy)) continue;
        $term_ids = [];
        foreach ((array) $uuids as $uuid) if (!empty($job['mapping']['terms'][$uuid])) $term_ids[] = absint($job['mapping']['terms'][$uuid]);
        wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
    }
    $featured = absint($job['mapping']['attachments'][$record['featured_attachment_uuid'] ?? ''] ?? 0);
    $featured ? set_post_thumbnail($post_id, $featured) : delete_post_thumbnail($post_id);
    foreach ((array) ($record['meta'] ?? []) as $key => $value) {
        if (cb_transfer_is_allowed_meta_key($key) && $key !== '_thumbnail_id') update_post_meta($post_id, $key, cb_transfer_remap_data($value, $job, $key));
    }
    return 'updated';
}

function cb_transfer_menu_item_identity($item)
{
    $item = wp_setup_nav_menu_item($item);
    $type = sanitize_key($item->type ?? 'custom');
    $object = sanitize_key($item->object ?? 'custom');
    $object_id = in_array($type, ['post_type', 'taxonomy'], true) ? absint($item->object_id ?? 0) : 0;
    $url = $type === 'custom' ? strtolower(untrailingslashit(esc_url_raw($item->url ?? ''))) : '';
    $title = strtolower(trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($item->title ?? ''))));
    return implode('|', [$type, $object, $object_id, $url, $title]);
}

function cb_transfer_draft_menu_subtree($item_id, array $children, array &$drafted, &$snapshot = null)
{
    $item_id = absint($item_id);
    if (!$item_id || isset($drafted[$item_id])) {
        return;
    }
    foreach ((array) ($children[$item_id] ?? []) as $child_id) {
        cb_transfer_draft_menu_subtree($child_id, $children, $drafted, $snapshot);
    }
    if (is_array($snapshot) && !empty($snapshot['rollback_id'])) {
        cb_transfer_snapshot_post($snapshot, $item_id);
    }
    if (get_post_status($item_id) === 'publish') {
        wp_update_post(['ID' => $item_id, 'post_status' => 'draft']);
    }
    $drafted[$item_id] = true;
}

function cb_transfer_repair_menu_duplicates($menu_id, array $incoming_uuids = [], &$snapshot = null)
{
    $items = wp_get_nav_menu_items(absint($menu_id), ['post_status' => 'any']);
    if (!$items) {
        return [];
    }
    $children = [];
    $siblings = [];
    foreach ($items as $item) {
        if (get_post_status($item->ID) !== 'publish') {
            continue;
        }
        $parent_id = absint($item->menu_item_parent);
        $children[$parent_id][] = absint($item->ID);
        $siblings[$parent_id][] = $item;
    }
    $drafted = [];
    foreach ($siblings as $group) {
        $seen = [];
        foreach ($group as $item) {
            if (isset($drafted[$item->ID])) {
                continue;
            }
            $identity = cb_transfer_menu_item_identity($item);
            if (!isset($seen[$identity])) {
                $seen[$identity] = $item;
                continue;
            }
            $kept = $seen[$identity];
            $kept_uuid = sanitize_text_field(get_post_meta($kept->ID, '_cb_transfer_uuid', true));
            $item_uuid = sanitize_text_field(get_post_meta($item->ID, '_cb_transfer_uuid', true));
            $kept_is_incoming = $kept_uuid && isset($incoming_uuids[$kept_uuid]);
            $item_is_incoming = $item_uuid && isset($incoming_uuids[$item_uuid]);
            $draft_id = $item->ID;
            if ($item_is_incoming && !$kept_is_incoming) {
                $draft_id = $kept->ID;
                $seen[$identity] = $item;
            } elseif (!$kept_is_incoming && !$item_is_incoming && $kept_uuid && !$item_uuid) {
                $draft_id = $kept->ID;
                $seen[$identity] = $item;
            }
            cb_transfer_draft_menu_subtree($draft_id, $children, $drafted, $snapshot);
        }
    }
    return array_map('absint', array_keys($drafted));
}

function cb_transfer_import_menus(array $menu_data, array &$job, array &$snapshot)
{
    foreach ((array) ($menu_data['menus'] ?? []) as $menu) {
        $menu_id = absint($job['mapping']['terms'][$menu['source_uuid']] ?? 0);
        if (!$menu_id) {
            $created = wp_create_nav_menu(sanitize_text_field($menu['name'] ?? 'Menu'));
            if (is_wp_error($created)) continue;
            $menu_id = absint($created);
            update_term_meta($menu_id, '_cb_transfer_uuid', sanitize_text_field($menu['source_uuid'] ?? ''));
            $snapshot['created_terms'][] = ['term_id' => $menu_id, 'taxonomy' => 'nav_menu'];
        }
        $incoming_item_uuids = [];
        foreach ((array) ($menu['items'] ?? []) as $item) {
            $source_uuid = sanitize_text_field($item['source_uuid'] ?? '');
            if ($source_uuid) {
                $incoming_item_uuids[$source_uuid] = true;
            }
        }
        $previous_items = wp_get_nav_menu_items($menu_id, ['post_status' => 'any']);
        $item_map = [];
        $skipped_items = [];
        foreach ((array) ($menu['items'] ?? []) as $item) {
            $existing = cb_transfer_find_post_by_uuid($item['source_uuid'] ?? '', 'nav_menu_item');
            if ($existing && $job['mode'] === 'create_only') {
                $item_map[(string) absint($item['source_id'] ?? 0)] = $existing;
                $skipped_items[(string) absint($item['source_id'] ?? 0)] = true;
                continue;
            }
            if ($existing) cb_transfer_snapshot_post($snapshot, $existing);
            $object_id = 0;
            if (($item['type'] ?? '') === 'post_type') $object_id = absint($job['mapping']['posts'][$item['object_uuid'] ?? ''] ?? 0);
            if (($item['type'] ?? '') === 'taxonomy') $object_id = absint($job['mapping']['terms'][$item['object_uuid'] ?? ''] ?? 0);
            $item_id = wp_update_nav_menu_item($menu_id, $existing, [
                'menu-item-title' => sanitize_text_field($item['title'] ?? ''),
                'menu-item-url' => esc_url_raw(cb_transfer_map_source_url($item['url'] ?? '', $job['source_url'], home_url())),
                'menu-item-status' => 'publish',
                'menu-item-type' => in_array($item['type'] ?? 'custom', ['custom', 'post_type', 'taxonomy'], true) ? $item['type'] : 'custom',
                'menu-item-object' => sanitize_key($item['object'] ?? 'custom'),
                'menu-item-object-id' => $object_id,
                'menu-item-target' => sanitize_text_field($item['target'] ?? ''),
                'menu-item-classes' => implode(' ', array_map('sanitize_html_class', (array) ($item['classes'] ?? []))),
                'menu-item-xfn' => sanitize_text_field($item['xfn'] ?? ''),
                'menu-item-description' => sanitize_textarea_field($item['description'] ?? ''),
                'menu-item-position' => intval($item['menu_order'] ?? 0),
            ]);
            if (is_wp_error($item_id)) continue;
            update_post_meta($item_id, '_cb_transfer_uuid', sanitize_text_field($item['source_uuid'] ?? ''));
            if (!$existing) $snapshot['created_posts'][] = $item_id;
            $item_map[(string) absint($item['source_id'] ?? 0)] = $item_id;
        }
        foreach ((array) ($menu['items'] ?? []) as $item) {
            $item_id = absint($item_map[(string) absint($item['source_id'] ?? 0)] ?? 0);
            if (!empty($skipped_items[(string) absint($item['source_id'] ?? 0)])) continue;
            $parent_id = absint($item_map[(string) absint($item['parent_source_id'] ?? 0)] ?? 0);
            if ($item_id) update_post_meta($item_id, '_menu_item_menu_item_parent', (string) $parent_id);
        }
        if ($job['mode'] !== 'create_only') {
            foreach ((array) $previous_items as $previous_item) {
                $previous_uuid = sanitize_text_field(get_post_meta($previous_item->ID, '_cb_transfer_uuid', true));
                if (!$previous_uuid || isset($incoming_item_uuids[$previous_uuid])) {
                    continue;
                }
                cb_transfer_snapshot_post($snapshot, $previous_item->ID);
                wp_update_post(['ID' => $previous_item->ID, 'post_status' => 'draft']);
            }
            cb_transfer_repair_menu_duplicates($menu_id, $incoming_item_uuids, $snapshot);
        }
        $job['mapping']['menus'][$menu['source_uuid']] = $menu_id;
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $registered = get_registered_nav_menus();
    foreach ((array) ($menu_data['locations'] ?? []) as $location => $menu_uuid) {
        if (isset($registered[$location], $job['mapping']['menus'][$menu_uuid]) && !($job['mode'] === 'create_only' && !empty($locations[$location]))) $locations[$location] = absint($job['mapping']['menus'][$menu_uuid]);
    }
    set_theme_mod('nav_menu_locations', $locations);
    cb_transfer_save_rollback($snapshot);
}

function cb_transfer_import_options(array $options, array &$job)
{
    foreach ($options as $key => $value) {
        if (!in_array($key, cb_transfer_option_allowlist(), true) || cb_transfer_is_secret_key($key)) continue;
        if ($job['mode'] === 'create_only' && get_option($key, null) !== null) continue;
        update_option($key, cb_transfer_remap_data(cb_transfer_strip_secrets($value, $key), $job, $key));
    }
}

function cb_transfer_record_warning(array &$job, $message)
{
    $job['warnings'][] = sanitize_text_field($message);
    $job['warnings'] = array_slice(array_values(array_unique($job['warnings'])), -50);
}

function cb_transfer_process_import($job_id)
{
    $job = cb_transfer_get_job($job_id);
    if (!$job || $job['status'] !== 'running') return new WP_Error('job_not_running', __('Job không ở trạng thái running.', 'cb-site-transfer'));
    $locked = cb_transfer_acquire_import_lock($job_id, $job['lock_token']);
    if (is_wp_error($locked)) return $locked;
    $snapshot = cb_transfer_get_rollback($job['rollback_id']);
    if (!$snapshot) return new WP_Error('rollback_missing', __('Không tìm thấy rollback snapshot.', 'cb-site-transfer'));

    $terms = cb_transfer_load_job_data($job, 'terms');
    $attachments = cb_transfer_load_job_data($job, 'attachments');
    $posts = cb_transfer_load_job_data($job, 'posts');
    if (is_wp_error($terms) || is_wp_error($attachments) || is_wp_error($posts)) return new WP_Error('job_data', __('Dữ liệu job không đọc được.', 'cb-site-transfer'));

    $step = $job['step'];
    if ($step === 'terms') {
        $offset = absint($job['offsets']['terms'] ?? 0);
        $batch = (int) apply_filters('cb_transfer_term_batch_size', 50);
        foreach (array_slice($terms, $offset, $batch) as $record) {
            $result = cb_transfer_import_term_record($record, $job, $snapshot);
            is_wp_error($result) ? cb_transfer_record_warning($job, $result->get_error_message()) : cb_transfer_record_result($job, $result);
            $offset++;
        }
        $job['offsets']['terms'] = $offset;
        if ($offset >= count($terms)) { $job['step'] = 'attachments'; $job['progress'] = 25; }
    } elseif ($step === 'attachments') {
        $offset = absint($job['offsets']['attachments'] ?? 0);
        $batch = (int) apply_filters('cb_transfer_attachment_batch_size', 15);
        foreach (array_slice($attachments, $offset, $batch) as $record) {
            $result = cb_transfer_import_attachment_record($record, $job, $snapshot);
            is_wp_error($result) ? cb_transfer_record_warning($job, $result->get_error_message()) : cb_transfer_record_result($job, $result);
            $offset++;
        }
        $job['offsets']['attachments'] = $offset;
        if ($offset >= count($attachments)) { $job['step'] = 'posts'; $job['progress'] = 50; }
    } elseif ($step === 'posts') {
        $offset = absint($job['offsets']['posts'] ?? 0);
        $batch = (int) apply_filters('cb_transfer_post_batch_size', 30);
        foreach (array_slice($posts, $offset, $batch) as $record) {
            $result = cb_transfer_import_post_record($record, $job, $snapshot);
            is_wp_error($result) ? cb_transfer_record_warning($job, $result->get_error_message()) : cb_transfer_record_result($job, $result);
            $offset++;
        }
        $job['offsets']['posts'] = $offset;
        if ($offset >= count($posts)) { cb_transfer_link_term_relationships($terms, $job); $job['step'] = 'relationships'; $job['progress'] = 70; }
    } elseif ($step === 'relationships') {
        $offset = absint($job['offsets']['relationships'] ?? 0);
        $batch = (int) apply_filters('cb_transfer_relationship_batch_size', 30);
        foreach (array_slice($posts, $offset, $batch) as $record) { cb_transfer_link_post_record($record, $job); $offset++; }
        $job['offsets']['relationships'] = $offset;
        if ($offset >= count($posts)) { $job['step'] = 'menus'; $job['progress'] = 82; }
    } elseif ($step === 'menus') {
        $menus = cb_transfer_load_job_data($job, 'menus');
        if (!is_wp_error($menus)) cb_transfer_import_menus($menus, $job, $snapshot);
        $job['step'] = 'options'; $job['progress'] = 90;
    } elseif ($step === 'options') {
        $options = cb_transfer_load_job_data($job, 'options');
        if (!is_wp_error($options)) cb_transfer_import_options($options, $job);
        $job['step'] = 'finalize'; $job['progress'] = 96;
    } elseif ($step === 'finalize') {
        if (function_exists('cb_core_maybe_migrate')) cb_core_maybe_migrate();
        flush_rewrite_rules(false);
        wp_cache_flush();
        cb_transfer_release_import_lock($job_id);
        $job['status'] = 'completed'; $job['step'] = 'completed'; $job['progress'] = 100;
        $workspace = $job['workspace'];
        $package_parent = dirname($job['package_path']);
        cb_transfer_remove_tree($workspace);
        cb_transfer_remove_tree($package_parent);
        $job['workspace'] = ''; $job['package_path'] = '';
        $job['report']['completed_at'] = current_time('mysql');
    }
    cb_transfer_save_rollback($snapshot);
    return cb_transfer_update_job($job_id, $job);
}

function cb_transfer_pause_import($job_id)
{
    $job = cb_transfer_get_job($job_id);
    if (!$job || $job['status'] !== 'running') return new WP_Error('job_not_running', __('Job không thể pause.', 'cb-site-transfer'));
    cb_transfer_release_import_lock($job_id);
    return cb_transfer_update_job($job_id, ['status' => 'paused']);
}

function cb_transfer_resume_import($job_id)
{
    $job = cb_transfer_get_job($job_id);
    if (!$job || $job['status'] !== 'paused') return new WP_Error('job_not_paused', __('Job không thể resume.', 'cb-site-transfer'));
    $locked = cb_transfer_acquire_import_lock($job_id, $job['lock_token']);
    if (is_wp_error($locked)) return $locked;
    return cb_transfer_update_job($job_id, ['status' => 'running']);
}

function cb_transfer_cancel_import($job_id)
{
    $job = cb_transfer_get_job($job_id);
    if (!$job || !in_array($job['status'], ['running', 'paused', 'pending'], true)) return new WP_Error('job_not_cancelable', __('Job không thể hủy.', 'cb-site-transfer'));
    cb_transfer_release_import_lock($job_id);
    cb_transfer_remove_tree((string) ($job['workspace'] ?? ''));
    cb_transfer_remove_tree(dirname((string) ($job['package_path'] ?? '')));
    return cb_transfer_update_job($job_id, ['status' => 'failed', 'step' => 'cancelled', 'workspace' => '', 'package_path' => '', 'errors' => array_merge((array) ($job['errors'] ?? []), [__('Job đã bị hủy bởi quản trị viên.', 'cb-site-transfer')])]);
}
