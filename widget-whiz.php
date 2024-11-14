<?php
/*
Plugin Name: Widget Whiz
Description: Easily manage sidebars from the WordPress dashboard.
Version: 1.0.5
Plugin URI: https://github.com/amarasa/widget-whiz
Author: Angelo Marasa
*/

require 'puc/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/amarasa/widget-whiz',
    __FILE__,
    'widget-whiz-plugin'
);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include the admin class.
require_once plugin_dir_path(__FILE__) . 'admin/class-widget-whiz-admin.php';

// Include the core class.
require_once plugin_dir_path(__FILE__) . 'includes/class-widget-whiz.php';

// Initialize the plugin.
function widget_whiz_init()
{
    $widget_whiz_admin = new Widget_Whiz_Admin();
    $widget_whiz = new Widget_Whiz();
}
add_action('plugins_loaded', 'widget_whiz_init');
