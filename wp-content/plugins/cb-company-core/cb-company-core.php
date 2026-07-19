<?php
/**
 * Plugin Name: CB Company Core
 * Description: Dữ liệu doanh nghiệp, đa ngôn ngữ, biểu mẫu, SEO và trình dựng trang.
 * Version: 1.6.0
 * Author: CB
 * Text Domain: cb-company-core
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CB_CORE_FILE', __FILE__);
define('CB_CORE_VERSION', '1.6.0');
define('CB_CORE_DB_VERSION', '1.6.0');
define('CB_CORE_PATH', plugin_dir_path(__FILE__));
define('CB_CORE_URL', plugin_dir_url(__FILE__));

require_once CB_CORE_PATH . 'includes/helpers/options.php';
require_once CB_CORE_PATH . 'includes/post-types.php';
require_once CB_CORE_PATH . 'includes/taxonomies.php';
require_once CB_CORE_PATH . 'includes/meta-boxes.php';
require_once CB_CORE_PATH . 'includes/certificates.php';
require_once CB_CORE_PATH . 'includes/multilingual/language-manager.php';
require_once CB_CORE_PATH . 'includes/admin/field-renderer.php';
require_once CB_CORE_PATH . 'includes/page-ui/resolver.php';
require_once CB_CORE_PATH . 'includes/page-builder/registry.php';
require_once CB_CORE_PATH . 'includes/page-builder/storage.php';
require_once CB_CORE_PATH . 'includes/page-builder/migration.php';
require_once CB_CORE_PATH . 'includes/admin/settings-page.php';
require_once CB_CORE_PATH . 'includes/admin/rest.php';
require_once CB_CORE_PATH . 'includes/admin/homepage-builder.php';
require_once CB_CORE_PATH . 'includes/admin/content-pages.php';
require_once CB_CORE_PATH . 'includes/inquiry/inquiry-form.php';
require_once CB_CORE_PATH . 'includes/seo/meta-tags.php';
require_once CB_CORE_PATH . 'includes/seed.php';

register_activation_hook(__FILE__, 'cb_core_activate');
function cb_core_activate()
{
    cb_register_post_types();
    cb_register_taxonomies();
    cb_core_maybe_migrate();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

add_action('init', 'cb_register_post_types');
add_action('init', 'cb_register_taxonomies');
add_action('init', 'cb_register_language_rewrites');
add_action('init', 'cb_register_string_shortcodes');
add_action('plugins_loaded', 'cb_core_load_textdomain');
add_action('admin_init', 'cb_core_maybe_migrate');

add_filter('query_vars', 'cb_register_language_query_var');
add_filter('request', 'cb_resolve_special_home_request');
add_filter('redirect_canonical', 'cb_disable_canonical_for_special_home', 10, 2);
add_action('template_redirect', 'cb_redirect_root_to_language');
add_action('pre_get_posts', 'cb_filter_main_query_language');

add_action('add_meta_boxes', 'cb_register_meta_boxes');
add_action('save_post', 'cb_save_common_meta_boxes');
add_action('save_post', 'cb_validate_certificate_publish', 40, 3);
add_action('save_post_page', 'cb_save_page_builder_meta');
add_action('admin_menu', 'cb_register_settings_pages');
add_action('admin_init', 'cb_register_settings');
add_action('rest_api_init', 'cb_register_admin_rest_routes');
add_action('admin_enqueue_scripts', 'cb_admin_enqueue_assets');
add_action('admin_post_cb_export_page_json', 'cb_export_page_json');
add_action('admin_post_cb_export_inquiries_csv', 'cb_export_inquiries_csv');
add_action('admin_post_cb_reset_settings', 'cb_handle_reset_settings');
add_action('admin_post_cb_demo_content', 'cb_handle_demo_content_action');
add_action('admin_post_cb_restore_catalog_layout', 'cb_handle_restore_catalog_layout');
add_action('admin_post_cb_restore_about_layout', 'cb_handle_restore_about_layout');

add_filter('post_type_link', 'cb_certificate_post_type_link', 10, 2);
add_filter('post_type_link', 'cb_language_post_type_link', 20, 2);
add_filter('term_link', 'cb_certificate_term_link', 10, 3);
add_filter('manage_certificate_posts_columns', 'cb_certificate_admin_columns');
add_filter('use_block_editor_for_post_type', 'cb_certificate_use_block_editor', 10, 2);
add_action('manage_certificate_posts_custom_column', 'cb_certificate_admin_column_content', 10, 2);
add_action('admin_notices', 'cb_certificate_admin_notices');

add_shortcode('cb_inquiry_form', 'cb_render_inquiry_form');
add_action('admin_post_nopriv_cb_submit_inquiry', 'cb_handle_inquiry_submission');
add_action('admin_post_cb_submit_inquiry', 'cb_handle_inquiry_submission');

add_action('wp_head', 'cb_render_seo_meta', 1);
add_filter('pre_get_document_title', 'cb_filter_document_title');
remove_action('wp_head', 'rel_canonical');
add_action('wp_head', 'cb_render_dynamic_css_variables', 5);
add_action('wp_head', 'cb_render_hreflang');
add_action('wp_head', 'cb_render_schema');

function cb_core_load_textdomain()
{
    load_plugin_textdomain('cb-company-core', false, dirname(plugin_basename(CB_CORE_FILE)) . '/languages');
}
