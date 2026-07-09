<?php
/**
 * Plugin Name: CB WebP Converter
 * Description: Converts uploaded JPEG and PNG images to WebP without external plugins.
 * Version: 1.0.0
 * Author: CB
 * Text Domain: cb-webp-converter
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CB_WEBP_PATH', plugin_dir_path(__FILE__));

require_once CB_WEBP_PATH . 'includes/converter.php';
require_once CB_WEBP_PATH . 'includes/admin-settings.php';

add_action('admin_menu', 'cb_webp_register_settings_page');
add_action('admin_init', 'cb_webp_register_settings');
add_filter('wp_generate_attachment_metadata', 'cb_webp_generate_on_upload', 10, 2);
add_filter('manage_media_columns', 'cb_webp_media_column');
add_action('manage_media_custom_column', 'cb_webp_media_column_value', 10, 2);
add_action('admin_post_cb_webp_bulk_convert', 'cb_webp_handle_bulk_convert');
