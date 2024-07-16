<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Widget_Whiz
{
    public function __construct()
    {
        add_action('widgets_init', array($this, 'register_dynamic_sidebars'));
        add_action('widgets_init', array($this, 'unregister_deleted_sidebars'), 100);
    }

    public function register_dynamic_sidebars()
    {
        $sidebars = get_option('widget_whiz_sidebars', array());

        foreach ($sidebars as $sidebar) {
            $id = sanitize_title($sidebar['name']);

            register_sidebar(array(
                'id' => $id,
                'name' => $sidebar['name'],
                'description' => $sidebar['description'],
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h6 class="side-title">',
                'after_title' => '</h6>',
            ));
        }
    }

    public function unregister_deleted_sidebars()
    {
        global $wp_registered_sidebars;
        $deleted_sidebars = get_option('widget_whiz_deleted_sidebars', array());

        foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
            if (in_array($sidebar['name'], $deleted_sidebars)) {
                unregister_sidebar($sidebar_id);
            }
        }
    }
}
