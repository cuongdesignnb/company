<?php
/**
 * Plugin Name: CB Site Transfer
 * Description: Xuất và nhập dữ liệu website CB Company bằng package JSON an toàn.
 * Version: 1.1.3
 * Author: CB
 * Text Domain: cb-site-transfer
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CB_TRANSFER_FILE', __FILE__);
define('CB_TRANSFER_PATH', plugin_dir_path(__FILE__));
define('CB_TRANSFER_URL', plugin_dir_url(__FILE__));
define('CB_TRANSFER_VERSION', '1.1.3');
define('CB_TRANSFER_FORMAT_VERSION', '1.0.0');

require_once CB_TRANSFER_PATH . 'includes/mappings.php';
require_once CB_TRANSFER_PATH . 'includes/logger.php';
require_once CB_TRANSFER_PATH . 'includes/security.php';
require_once CB_TRANSFER_PATH . 'includes/package.php';
require_once CB_TRANSFER_PATH . 'includes/exporter.php';
require_once CB_TRANSFER_PATH . 'includes/preflight.php';
require_once CB_TRANSFER_PATH . 'includes/rollback.php';
require_once CB_TRANSFER_PATH . 'includes/importer.php';
require_once CB_TRANSFER_PATH . 'includes/rest-api.php';
require_once CB_TRANSFER_PATH . 'includes/admin-page.php';

register_activation_hook(__FILE__, 'cb_transfer_activate');

function cb_transfer_activate()
{
    if (!get_option('cb_transfer_site_uuid')) {
        update_option('cb_transfer_site_uuid', wp_generate_uuid4(), false);
    }
    cb_transfer_ensure_workspace();
}

add_action('plugins_loaded', 'cb_transfer_load_textdomain');
add_action('admin_menu', 'cb_transfer_register_admin_page', 30);
add_action('admin_enqueue_scripts', 'cb_transfer_enqueue_admin_assets');
add_action('rest_api_init', 'cb_transfer_register_rest_routes');
add_action('admin_post_cb_transfer_download', 'cb_transfer_download_export');
add_action('admin_post_cb_transfer_report', 'cb_transfer_download_report');
add_action('admin_init', 'cb_transfer_maybe_upgrade');
add_action('admin_notices', 'cb_transfer_menu_repair_notice');

function cb_transfer_load_textdomain()
{
    load_plugin_textdomain('cb-site-transfer', false, dirname(plugin_basename(CB_TRANSFER_FILE)) . '/languages');
}

function cb_transfer_maybe_upgrade()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $version = (string) get_option('cb_transfer_db_version', '0');
    if (version_compare($version, '1.1.3', '>=')) {
        return;
    }
    $repaired = [];
    foreach (wp_get_nav_menus() as $menu) {
        $drafted = cb_transfer_repair_menu_duplicates($menu->term_id);
        if ($drafted) {
            $repaired[$menu->term_id] = $drafted;
        }
    }
    update_option('cb_transfer_db_version', '1.1.3', false);
    update_option('cb_transfer_menu_repair_113', [
        'repaired_at' => current_time('mysql'),
        'menus' => $repaired,
    ], false);
    if ($repaired) {
        set_transient('cb_transfer_menu_repair_notice', array_sum(array_map('count', $repaired)), MINUTE_IN_SECONDS);
    }
}

function cb_transfer_menu_repair_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $count = absint(get_transient('cb_transfer_menu_repair_notice'));
    if (!$count) {
        return;
    }
    delete_transient('cb_transfer_menu_repair_notice');
    echo '<div class="notice notice-success is-dismissible"><p>'
        . esc_html(sprintf(__('CB Site Transfer đã dọn %d mục menu bị trùng do lần import trước.', 'cb-site-transfer'), $count))
        . '</p></div>';
}
