<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_export_defaults()
{
    return [
        'pages' => true,
        'products' => true,
        'news' => true,
        'factory' => true,
        'cases' => true,
        'videos' => true,
        'certificates' => true,
        'media' => true,
        'menus' => true,
        'settings' => true,
        'seo' => true,
        'forms' => true,
        'inquiries' => false,
        'drafts' => false,
        'demo' => false,
    ];
}

function cb_transfer_sanitize_export_selection($input)
{
    $clean = [];
    foreach (cb_transfer_export_defaults() as $key => $default) {
        $clean[$key] = filter_var($input[$key] ?? $default, FILTER_VALIDATE_BOOLEAN);
    }
    return $clean;
}

function cb_transfer_selected_post_types(array $selection)
{
    $map = [
        'pages' => 'page',
        'products' => 'product',
        'news' => 'post',
        'factory' => 'factory_showcase',
        'cases' => 'case_study',
        'videos' => 'video',
        'certificates' => 'certificate',
    ];
    $types = [];
    foreach ($map as $option => $post_type) {
        if (!empty($selection[$option])) {
            $types[] = $post_type;
        }
    }
    if (!empty($selection['inquiries'])) {
        $types[] = 'inquiry';
    }
    return $types;
}

function cb_transfer_export_posts(array $selection)
{
    $post_types = cb_transfer_selected_post_types($selection);
    if (!$post_types) {
        return [];
    }
    $statuses = !empty($selection['drafts']) ? ['publish', 'draft', 'pending', 'private', 'future'] : ['publish'];
    $args = [
        'post_type' => $post_types,
        'post_status' => $statuses,
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
        'no_found_rows' => true,
    ];
    if (empty($selection['demo'])) {
        $args['meta_query'] = [[
            'relation' => 'OR',
            ['key' => '_cb_is_demo_content', 'compare' => 'NOT EXISTS'],
            ['key' => '_cb_is_demo_content', 'value' => '1', 'compare' => '!='],
        ]];
    }
    $records = [];
    foreach (get_posts($args) as $post) {
        $uuid = cb_transfer_entity_uuid('post', $post->ID);
        $parent_uuid = $post->post_parent ? cb_transfer_entity_uuid('post', $post->post_parent) : '';
        $featured_id = get_post_thumbnail_id($post->ID);
        $taxonomies = [];
        foreach (get_object_taxonomies($post->post_type) as $taxonomy) {
            if (!in_array($taxonomy, cb_transfer_taxonomies(), true)) continue;
            $term_ids = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);
            if (!is_wp_error($term_ids)) {
                $taxonomies[$taxonomy] = array_map(static fn($term_id) => cb_transfer_entity_uuid('term', $term_id), $term_ids);
            }
        }
        $records[] = [
            'source_id' => $post->ID,
            'source_uuid' => $uuid,
            'post_type' => $post->post_type,
            'post_status' => $post->post_status,
            'post_title' => $post->post_title,
            'post_name' => $post->post_name,
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
            'post_date' => $post->post_date,
            'post_date_gmt' => $post->post_date_gmt,
            'post_parent_uuid' => $parent_uuid,
            'menu_order' => (int) $post->menu_order,
            'comment_status' => $post->comment_status,
            'page_template' => get_page_template_slug($post->ID),
            'featured_attachment_uuid' => $featured_id ? cb_transfer_entity_uuid('attachment', $featured_id) : '',
            'featured_attachment_source_id' => absint($featured_id),
            'taxonomies' => $taxonomies,
            'meta' => cb_transfer_filter_meta(get_post_meta($post->ID)),
        ];
    }
    return $records;
}

function cb_transfer_export_terms(array $selection)
{
    $records = [];
    foreach (cb_transfer_taxonomies() as $taxonomy) {
        if ($taxonomy === 'nav_menu' && empty($selection['menus'])) continue;
        if (!taxonomy_exists($taxonomy)) continue;
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        if (is_wp_error($terms)) continue;
        foreach ($terms as $term) {
            $meta = [];
            foreach (get_term_meta($term->term_id) as $key => $values) {
                if (!str_starts_with($key, '_cb_')) continue;
                $meta[$key] = count($values) === 1 ? maybe_unserialize($values[0]) : array_map('maybe_unserialize', $values);
            }
            $records[] = [
                'source_id' => $term->term_id,
                'source_uuid' => cb_transfer_entity_uuid('term', $term->term_id),
                'taxonomy' => $taxonomy,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'parent_uuid' => $term->parent ? cb_transfer_entity_uuid('term', $term->parent) : '',
                'meta' => cb_transfer_strip_secrets($meta),
            ];
        }
    }
    return $records;
}

function cb_transfer_attachment_files($attachment_id)
{
    $upload = wp_get_upload_dir();
    $relative = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $safe = cb_transfer_safe_relative_path($relative);
    if (!$safe) return [];
    $paths = [$safe];
    $metadata = wp_get_attachment_metadata($attachment_id);
    $directory = dirname($safe);
    foreach ((array) ($metadata['sizes'] ?? []) as $size) {
        if (!empty($size['file'])) {
            $paths[] = ($directory === '.' ? '' : $directory . '/') . basename($size['file']);
        }
    }
    $files = [];
    foreach (array_unique($paths) as $path) {
        $absolute = trailingslashit($upload['basedir']) . $path;
        if (!is_file($absolute) || !cb_transfer_assert_inside($absolute, $upload['basedir'])) continue;
        $files[] = [
            'relative_path' => $path,
            'package_path' => 'media/' . $path,
            'sha256' => hash_file('sha256', $absolute),
            'size' => filesize($absolute),
        ];
    }
    return $files;
}

function cb_transfer_export_attachments(array $selection, $workspace, array &$warnings)
{
    if (empty($selection['media'])) {
        return [];
    }
    $records = [];
    $attachments = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
        'no_found_rows' => true,
    ]);
    $upload = wp_get_upload_dir();
    foreach ($attachments as $attachment) {
        $files = cb_transfer_attachment_files($attachment->ID);
        if (!$files) {
            $warnings[] = sprintf(__('Attachment #%d không có file hợp lệ.', 'cb-site-transfer'), $attachment->ID);
            continue;
        }
        foreach ($files as $file) {
            $source = trailingslashit($upload['basedir']) . $file['relative_path'];
            $target = $workspace . '/' . $file['package_path'];
            wp_mkdir_p(dirname($target));
            if (!copy($source, $target)) {
                $warnings[] = sprintf(__('Không copy được media %s.', 'cb-site-transfer'), $file['relative_path']);
            }
        }
        $records[] = [
            'source_id' => $attachment->ID,
            'source_uuid' => cb_transfer_entity_uuid('attachment', $attachment->ID),
            'post_title' => $attachment->post_title,
            'post_excerpt' => $attachment->post_excerpt,
            'post_content' => $attachment->post_content,
            'post_date' => $attachment->post_date,
            'post_date_gmt' => $attachment->post_date_gmt,
            'mime_type' => $attachment->post_mime_type,
            'alt_text' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'relative_path' => (string) get_post_meta($attachment->ID, '_wp_attached_file', true),
            'metadata' => wp_get_attachment_metadata($attachment->ID),
            'original_sha256' => $files[0]['sha256'],
            'files' => $files,
        ];
    }
    return $records;
}

function cb_transfer_export_menus(array $selection)
{
    if (empty($selection['menus'])) return ['menus' => [], 'locations' => []];
    $menus = [];
    $menu_id_to_uuid = [];
    foreach (wp_get_nav_menus() as $menu) {
        $menu_uuid = cb_transfer_entity_uuid('term', $menu->term_id);
        $menu_id_to_uuid[$menu->term_id] = $menu_uuid;
        $items = [];
        foreach ((array) wp_get_nav_menu_items($menu->term_id, ['post_status' => 'publish']) as $item) {
            $object_uuid = '';
            if ($item->type === 'post_type' && $item->object_id) {
                $object_uuid = cb_transfer_entity_uuid('post', $item->object_id);
            } elseif ($item->type === 'taxonomy' && $item->object_id) {
                $object_uuid = cb_transfer_entity_uuid('term', $item->object_id);
            }
            $items[] = [
                'source_id' => $item->ID,
                'source_uuid' => cb_transfer_entity_uuid('post', $item->ID),
                'parent_source_id' => absint($item->menu_item_parent),
                'type' => $item->type,
                'object' => $item->object,
                'object_uuid' => $object_uuid,
                'url' => $item->url,
                'title' => $item->title,
                'target' => $item->target,
                'classes' => array_values(array_filter((array) $item->classes)),
                'xfn' => $item->xfn,
                'description' => $item->description,
                'menu_order' => (int) $item->menu_order,
            ];
        }
        $menus[] = ['source_id' => $menu->term_id, 'source_uuid' => $menu_uuid, 'name' => $menu->name, 'slug' => $menu->slug, 'items' => $items];
    }
    $locations = [];
    foreach (get_nav_menu_locations() as $location => $menu_id) {
        if (isset($menu_id_to_uuid[$menu_id])) $locations[$location] = $menu_id_to_uuid[$menu_id];
    }
    return ['menus' => $menus, 'locations' => $locations];
}

function cb_transfer_export_options(array $selection)
{
    $allowed = [];
    if (!empty($selection['settings'])) {
        $allowed = array_merge($allowed, ['cb_design_settings', 'cb_header_settings', 'cb_footer_settings', 'cb_template_settings', 'cb_special_pages', 'cb_string_translations', 'cb_performance_settings', 'cb_core_db_version', 'cb_demo_content_installed', 'show_on_front', 'page_on_front', 'page_for_posts', 'blogname', 'blogdescription', 'permalink_structure']);
    }
    if (!empty($selection['seo'])) $allowed[] = 'cb_seo_settings';
    if (!empty($selection['forms'])) $allowed[] = 'cb_form_settings';
    $options = [];
    foreach (array_unique($allowed) as $key) {
        if (!in_array($key, cb_transfer_option_allowlist(), true)) continue;
        $value = get_option($key, null);
        if ($value !== null) $options[$key] = cb_transfer_strip_secrets($value, $key);
    }
    return $options;
}

function cb_transfer_requirement_versions()
{
    $theme = wp_get_theme('cb-company-theme');
    return [
        'cb_company_core' => defined('CB_CORE_VERSION') ? CB_CORE_VERSION : '',
        'cb_company_theme' => $theme->exists() ? $theme->get('Version') : '',
        'cb_webp_converter' => defined('CB_WEBP_PATH') ? '1.0.0' : '',
    ];
}

function cb_transfer_create_export_package(array $selection = [])
{
    if (!class_exists('ZipArchive')) {
        return new WP_Error('zip_missing', __('Máy chủ chưa bật ZipArchive.', 'cb-site-transfer'));
    }
    $selection = cb_transfer_sanitize_export_selection($selection);
    $job = cb_transfer_create_job('export', ['status' => 'running', 'step' => 'collecting', 'progress' => 5]);
    $workspace = cb_transfer_create_workspace($job['job_id']);
    if (is_wp_error($workspace)) return $workspace;
    cb_transfer_update_job($job['job_id'], ['workspace' => $workspace]);

    $warnings = [];
    $posts = cb_transfer_export_posts($selection);
    $terms = cb_transfer_export_terms($selection);
    $attachments = cb_transfer_export_attachments($selection, $workspace, $warnings);
    $menus = cb_transfer_export_menus($selection);
    $options = cb_transfer_export_options($selection);
    $relationships = [];
    foreach ($posts as $post) {
        $relationships[$post['source_uuid']] = [
            'parent' => $post['post_parent_uuid'],
            'featured_attachment' => $post['featured_attachment_uuid'],
            'taxonomies' => $post['taxonomies'],
        ];
    }

    $site = [
        'site_uuid' => cb_transfer_get_site_uuid(),
        'source_url' => home_url(),
        'site_url' => site_url(),
        'blogname' => get_bloginfo('name'),
        'charset' => get_bloginfo('charset'),
    ];
    $manifest = [
        'format' => 'cb-site-package',
        'format_version' => CB_TRANSFER_FORMAT_VERSION,
        'plugin_version' => CB_TRANSFER_VERSION,
        'exported_at' => current_time('c'),
        'source_site_uuid' => $site['site_uuid'],
        'source_url' => $site['source_url'],
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'language_codes' => ['en', 'zh'],
        'counts' => [
            'posts' => count($posts),
            'terms' => count($terms),
            'attachments' => count($attachments),
            'menus' => count($menus['menus']),
        ],
        'requirements' => cb_transfer_requirement_versions(),
        'selection' => $selection,
    ];

    $files = [
        'manifest.json' => $manifest,
        'data/site.json' => $site,
        'data/options.json' => $options,
        'data/posts.json' => $posts,
        'data/terms.json' => $terms,
        'data/menus.json' => $menus,
        'data/attachments.json' => $attachments,
        'data/relationships.json' => $relationships,
    ];
    foreach ($files as $relative => $data) {
        $result = cb_transfer_write_json($workspace . '/' . $relative, $data);
        if (is_wp_error($result)) return $result;
    }
    $checksums = cb_transfer_build_checksums($workspace);
    $result = cb_transfer_write_json($workspace . '/checksums.json', $checksums);
    if (is_wp_error($result)) return $result;

    $package_path = $workspace . '/company-site.cbsite.zip';
    $result = cb_transfer_create_zip($workspace, $package_path);
    if (is_wp_error($result)) return $result;
    $download_url = wp_nonce_url(admin_url('admin-post.php?action=cb_transfer_download&job_id=' . rawurlencode($job['job_id'])), 'cb_transfer_download_' . $job['job_id']);
    $job = cb_transfer_update_job($job['job_id'], [
        'status' => 'completed',
        'step' => 'completed',
        'progress' => 100,
        'package_name' => 'company-site.cbsite.zip',
        'package_path' => $package_path,
        'source_site_uuid' => $site['site_uuid'],
        'source_url' => $site['source_url'],
        'warnings' => $warnings,
        'download_url' => $download_url,
        'report' => [
            'counts' => $manifest['counts'],
            'package_size' => filesize($package_path),
            'package_sha256' => hash_file('sha256', $package_path),
        ],
    ]);
    return $job;
}

function cb_transfer_download_export()
{
    if (!current_user_can('manage_options')) wp_die(esc_html__('Bạn không có quyền tải package.', 'cb-site-transfer'), 403);
    $job_id = sanitize_text_field(wp_unslash($_GET['job_id'] ?? ''));
    check_admin_referer('cb_transfer_download_' . $job_id);
    $job = cb_transfer_get_job($job_id);
    $path = (string) ($job['package_path'] ?? '');
    if (!$path || !is_file($path) || !cb_transfer_assert_inside($path, cb_transfer_workspace_root())) wp_die(esc_html__('Package không tồn tại.', 'cb-site-transfer'), 404);
    nocache_headers();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="company-site.cbsite.zip"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

function cb_transfer_download_report()
{
    if (!current_user_can('manage_options')) wp_die(esc_html__('Bạn không có quyền tải report.', 'cb-site-transfer'), 403);
    $job_id = sanitize_text_field(wp_unslash($_GET['job_id'] ?? ''));
    check_admin_referer('cb_transfer_report_' . $job_id);
    $job = cb_transfer_get_job($job_id);
    if (!$job) wp_die(esc_html__('Job không tồn tại.', 'cb-site-transfer'), 404);
    nocache_headers();
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="cb-transfer-report-' . sanitize_file_name($job_id) . '.json"');
    echo wp_json_encode(cb_transfer_public_job($job), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}
