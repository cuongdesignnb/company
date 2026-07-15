<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_get_jobs()
{
    $jobs = get_option('cb_transfer_jobs', []);
    return is_array($jobs) ? $jobs : [];
}

function cb_transfer_get_job($job_id)
{
    $jobs = cb_transfer_get_jobs();
    return is_array($jobs[$job_id] ?? null) ? $jobs[$job_id] : [];
}

function cb_transfer_create_job($type, array $data = [])
{
    $job_id = 'cbt-' . gmdate('YmdHis') . '-' . wp_generate_password(8, false, false);
    $now = current_time('mysql');
    $job = wp_parse_args($data, [
        'job_id' => $job_id,
        'type' => $type,
        'status' => 'pending',
        'step' => 'pending',
        'progress' => 0,
        'created_at' => $now,
        'updated_at' => $now,
        'user_id' => get_current_user_id(),
        'package_name' => '',
        'package_path' => '',
        'workspace' => '',
        'source_site_uuid' => '',
        'source_url' => '',
        'mapping' => ['posts' => [], 'terms' => [], 'attachments' => [], 'menus' => [], 'post_ids' => [], 'term_ids' => [], 'attachment_ids' => []],
        'offsets' => [],
        'errors' => [],
        'warnings' => [],
        'report' => [],
        'rollback_id' => '',
        'lock_token' => wp_generate_password(32, false, false),
    ]);
    $jobs = cb_transfer_get_jobs();
    $jobs[$job_id] = $job;
    uasort($jobs, static fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    $jobs = array_slice($jobs, 0, 50, true);
    update_option('cb_transfer_jobs', $jobs, false);
    return $job;
}

function cb_transfer_update_job($job_id, array $changes)
{
    $jobs = cb_transfer_get_jobs();
    if (empty($jobs[$job_id])) {
        return [];
    }
    $changes['updated_at'] = current_time('mysql');
    $jobs[$job_id] = array_replace_recursive($jobs[$job_id], $changes);
    update_option('cb_transfer_jobs', $jobs, false);
    return $jobs[$job_id];
}

function cb_transfer_job_message($job_id, $level, $message)
{
    $job = cb_transfer_get_job($job_id);
    if (!$job) {
        return;
    }
    $key = $level === 'error' ? 'errors' : 'warnings';
    $messages = (array) ($job[$key] ?? []);
    $messages[] = sanitize_text_field($message);
    cb_transfer_update_job($job_id, [$key => array_slice($messages, -100)]);
}

function cb_transfer_public_job(array $job)
{
    unset($job['package_path'], $job['workspace'], $job['lock_token'], $job['rollback_snapshot'], $job['mapping']);
    if (!empty($job['download_url'])) {
        $job['download_url'] = esc_url_raw($job['download_url']);
    }
    return $job;
}
