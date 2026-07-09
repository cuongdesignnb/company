<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_theme_enqueue_assets()
{
    wp_enqueue_style('cb-company-theme', get_template_directory_uri() . '/assets/css/main.css', [], '1.0.0');
    wp_enqueue_script('cb-company-theme', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true);
}
