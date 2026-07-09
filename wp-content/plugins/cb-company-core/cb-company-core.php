<?php
/**
 * Plugin Name: CB Company Core
 * Description: Company profile data, multilingual tools, inquiry forms, SEO and homepage settings.
 * Version: 1.0.0
 * Author: CB
 * Text Domain: cb-company-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CB_CORE_PATH', plugin_dir_path(__FILE__));
define('CB_CORE_URL', plugin_dir_url(__FILE__));

require_once CB_CORE_PATH . 'includes/helpers/options.php';
require_once CB_CORE_PATH . 'includes/post-types.php';
require_once CB_CORE_PATH . 'includes/taxonomies.php';
require_once CB_CORE_PATH . 'includes/meta-boxes.php';
require_once CB_CORE_PATH . 'includes/multilingual/language-manager.php';
require_once CB_CORE_PATH . 'includes/admin/settings-page.php';
require_once CB_CORE_PATH . 'includes/inquiry/inquiry-form.php';
require_once CB_CORE_PATH . 'includes/seo/meta-tags.php';
require_once CB_CORE_PATH . 'includes/seed.php';

register_activation_hook(__FILE__, 'cb_core_activate');
function cb_core_activate()
{
    cb_register_post_types();
    cb_register_taxonomies();
    cb_seed_default_content();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

add_action('init', 'cb_register_post_types');
add_action('init', 'cb_register_taxonomies');
add_action('init', 'cb_register_language_rewrites');
add_action('init', 'cb_register_string_shortcodes');

add_filter('query_vars', 'cb_register_language_query_var');
add_action('template_redirect', 'cb_redirect_root_to_language');
add_action('pre_get_posts', 'cb_filter_main_query_language');

add_action('add_meta_boxes', 'cb_register_meta_boxes');
add_action('save_post', 'cb_save_common_meta_boxes');
add_action('admin_menu', 'cb_register_settings_pages');
add_action('admin_init', 'cb_register_settings');

add_shortcode('cb_inquiry_form', 'cb_render_inquiry_form');
add_action('admin_post_nopriv_cb_submit_inquiry', 'cb_handle_inquiry_submission');
add_action('admin_post_cb_submit_inquiry', 'cb_handle_inquiry_submission');

add_action('wp_head', 'cb_render_seo_meta', 1);
add_action('wp_head', 'cb_render_hreflang');
add_action('wp_head', 'cb_render_schema');
