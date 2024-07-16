<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Widget_Whiz
{
    public function __construct()
    {
        add_action('widgets_init', array($this, 'register_dynamic_sidebars'));
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
                'before_widget' => '<div id="%1$s" class="widget %2$s ' . $id . '">',
                'after_widget' => '</div>',
                'before_title' => '<h6 class="side-title">',
                'after_title' => '</h6>',
            ));
        }
    }
}
