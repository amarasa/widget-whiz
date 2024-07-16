<?php
/*
Plugin Name: Widget Whiz
Description: Easily manage sidebars from the WordPress dashboard.
Version: 1.0.0
Plugin URI: https://github.com/amarasa/widget-whiz
Author: Angelo Marasa
*/

require_once plugin_dir_path(__FILE__) . 'includes/updater.php';


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

add_action('widgets_init', function () {
    global $wp_registered_sidebars;
    $deleted_sidebars = get_option('widget_whiz_deleted_sidebars', array());

    foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
        if (in_array($sidebar['name'], $deleted_sidebars)) {
            unregister_sidebar($sidebar_id);
        }
    }
}, 100);
