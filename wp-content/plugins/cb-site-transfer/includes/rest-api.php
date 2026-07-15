<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_register_rest_routes()
{
    $namespace = 'cb-site-transfer/v1';
    register_rest_route($namespace, '/jobs', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'cb_transfer_rest_jobs',
        'permission_callback' => 'cb_transfer_verify_rest_request',
    ]);
    register_rest_route($namespace, '/rollbacks', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'cb_transfer_rest_rollbacks',
        'permission_callback' => 'cb_transfer_verify_rest_request',
    ]);
    register_rest_route($namespace, '/export', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_transfer_rest_export',
        'permission_callback' => 'cb_transfer_verify_rest_request',
    ]);
    register_rest_route($namespace, '/upload', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_transfer_rest_upload',
        'permission_callback' => 'cb_transfer_verify_rest_request',
    ]);
    foreach (['start', 'process', 'pause', 'resume', 'cancel'] as $action) {
        register_rest_route($namespace, '/jobs/(?P<job_id>[a-zA-Z0-9-]+)/' . $action, [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'cb_transfer_rest_' . $action,
            'permission_callback' => 'cb_transfer_verify_rest_request',
            'args' => ['job_id' => ['sanitize_callback' => 'sanitize_text_field']],
        ]);
    }
    register_rest_route($namespace, '/rollbacks/(?P<rollback_id>[a-zA-Z0-9-]+)', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'cb_transfer_rest_rollback',
        'permission_callback' => 'cb_transfer_verify_rest_request',
        'args' => ['rollback_id' => ['sanitize_callback' => 'sanitize_text_field']],
    ]);
}

function cb_transfer_rest_jobs()
{
    $jobs = [];
    foreach (cb_transfer_get_jobs() as $job) {
        $public = cb_transfer_public_job($job);
        $user = get_userdata(absint($job['user_id'] ?? 0));
        $public['user_name'] = $user ? $user->display_name : __('Hệ thống', 'cb-site-transfer');
        if (($job['type'] ?? '') === 'export' && is_file($job['package_path'] ?? '')) {
            $public['download_url'] = wp_nonce_url(admin_url('admin-post.php?action=cb_transfer_download&job_id=' . rawurlencode($job['job_id'])), 'cb_transfer_download_' . $job['job_id']);
        } else {
            unset($public['download_url']);
        }
        $public['report_url'] = wp_nonce_url(admin_url('admin-post.php?action=cb_transfer_report&job_id=' . rawurlencode($job['job_id'])), 'cb_transfer_report_' . $job['job_id']);
        $jobs[] = $public;
    }
    return rest_ensure_response(['jobs' => $jobs]);
}

function cb_transfer_rest_rollbacks()
{
    $items = [];
    foreach (cb_transfer_get_rollbacks() as $snapshot) {
        $items[] = [
            'rollback_id' => $snapshot['rollback_id'],
            'job_id' => $snapshot['job_id'],
            'created_at' => $snapshot['created_at'],
            'status' => $snapshot['status'],
            'created_count' => count((array) $snapshot['created_posts']) + count((array) $snapshot['created_terms']),
            'updated_count' => count((array) $snapshot['posts']) + count((array) $snapshot['terms']),
        ];
    }
    return rest_ensure_response(['rollbacks' => $items]);
}

function cb_transfer_rest_export(WP_REST_Request $request)
{
    $limited = cb_transfer_rate_limit('export', 3);
    if (is_wp_error($limited)) return $limited;
    $job = cb_transfer_create_export_package((array) $request->get_json_params());
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_upload(WP_REST_Request $request)
{
    $limited = cb_transfer_rate_limit('upload', 3);
    if (is_wp_error($limited)) return $limited;
    $files = $request->get_file_params();
    if (empty($files['package'])) return new WP_Error('package_missing', __('Vui lòng chọn package.', 'cb-site-transfer'), ['status' => 400]);
    $job = cb_transfer_stage_import_package($files['package']);
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_start(WP_REST_Request $request)
{
    $params = (array) $request->get_json_params();
    $job = cb_transfer_start_import($request['job_id'], sanitize_key($params['mode'] ?? 'upsert'), !empty($params['dry_run']));
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_process(WP_REST_Request $request)
{
    $job = cb_transfer_process_import($request['job_id']);
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_pause(WP_REST_Request $request)
{
    $job = cb_transfer_pause_import($request['job_id']);
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_resume(WP_REST_Request $request)
{
    $job = cb_transfer_resume_import($request['job_id']);
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_cancel(WP_REST_Request $request)
{
    $job = cb_transfer_cancel_import($request['job_id']);
    return is_wp_error($job) ? $job : rest_ensure_response(['job' => cb_transfer_public_job($job)]);
}

function cb_transfer_rest_rollback(WP_REST_Request $request)
{
    $params = (array) $request->get_json_params();
    if (($params['confirm'] ?? '') !== $request['rollback_id']) {
        return new WP_Error('rollback_confirmation', __('Xác nhận rollback không hợp lệ.', 'cb-site-transfer'), ['status' => 400]);
    }
    $limited = cb_transfer_rate_limit('rollback', 5);
    if (is_wp_error($limited)) return $limited;
    $snapshot = cb_transfer_rollback_job($request['rollback_id']);
    return is_wp_error($snapshot) ? $snapshot : rest_ensure_response(['rollback' => ['rollback_id' => $snapshot['rollback_id'], 'status' => $snapshot['status']]]);
}
