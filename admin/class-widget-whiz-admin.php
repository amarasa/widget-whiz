<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Widget_Whiz_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('add_meta_boxes', array($this, 'add_sidebar_meta_box'));
        add_action('save_post', array($this, 'save_sidebar_meta_box'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Widget Whiz',
            'Widget Whiz',
            'manage_options',
            'widget-whiz',
            array($this, 'create_admin_page'),
            'dashicons-screenoptions'
        );
    }

    public function register_settings()
    {
        register_setting('widget_whiz_group', 'widget_whiz_sidebars', array($this, 'sanitize_sidebars'));
    }

    public function sanitize_sidebars($input)
    {
        $output = array();
        foreach ($input as $key => $sidebar) {
            if (isset($sidebar['name'])) {
                $output[$key]['name'] = sanitize_text_field($sidebar['name']);
                $output[$key]['description'] = sanitize_textarea_field($sidebar['description']);
            }
        }
        return $output;
    }

    public function create_admin_page()
    {
        // Your existing admin page code here.
    }

    // Meta box for sidebar selector
    public function add_sidebar_meta_box()
    {
        add_meta_box(
            'widget_whiz_sidebar_selector',
            __('Sidebar Selector', 'widget-whiz'),
            array($this, 'render_sidebar_meta_box'),
            array('post', 'page'),
            'side',
            'high'
        );
    }

    public function render_sidebar_meta_box($post)
    {
        $sidebars = get_option('widget_whiz_sidebars', array());
        $selected_sidebar = get_post_meta($post->ID, '_widget_whiz_selected_sidebar', true);

        echo '<label for="widget_whiz_sidebar_selector">' . __('Select a Sidebar:', 'widget-whiz') . '</label>';
        echo '<select name="widget_whiz_sidebar_selector" id="widget_whiz_sidebar_selector">';
        echo '<option value="">' . __('No Sidebar', 'widget-whiz') . '</option>';

        foreach ($sidebars as $sidebar) {
            $selected = selected($selected_sidebar, $sidebar['name'], false);
            echo '<option value="' . esc_attr($sidebar['name']) . '"' . $selected . '>' . esc_html($sidebar['name']) . '</option>';
        }
        echo '</select>';

        wp_nonce_field('widget_whiz_save_sidebar_meta_box', 'widget_whiz_sidebar_meta_box_nonce');
    }

    public function save_sidebar_meta_box($post_id)
    {
        if (
            !isset($_POST['widget_whiz_sidebar_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['widget_whiz_sidebar_meta_box_nonce'], 'widget_whiz_save_sidebar_meta_box')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $selected_sidebar = sanitize_text_field($_POST['widget_whiz_sidebar_selector']);
        update_post_meta($post_id, '_widget_whiz_selected_sidebar', $selected_sidebar);
    }
}
