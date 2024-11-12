<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Widget_Whiz
{
    public function __construct()
    {
        add_action('widgets_init', array($this, 'register_dynamic_sidebars'));
        add_action('get_sidebar', array($this, 'display_selected_sidebar'));
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

    // Display the selected sidebar on the frontend
    public function display_selected_sidebar()
    {
        if (is_singular(array('post', 'page'))) {
            global $post;
            $selected_sidebar = get_post_meta($post->ID, '_widget_whiz_selected_sidebar', true);

            if ($selected_sidebar) {
                dynamic_sidebar(sanitize_title($selected_sidebar));
            }
        }
    }
}
