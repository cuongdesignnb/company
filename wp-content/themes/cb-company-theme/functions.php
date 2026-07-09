<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/helpers.php';

add_action('after_setup_theme', 'cb_theme_setup');
add_action('wp_enqueue_scripts', 'cb_theme_enqueue_assets');
