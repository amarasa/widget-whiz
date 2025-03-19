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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_add_new_sidebar', array($this, 'add_new_sidebar'));
        add_action('wp_ajax_delete_sidebar', array($this, 'ajax_delete_sidebar'));
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
        $sidebars = get_option('widget_whiz_sidebars', array());

?>
        <div class="wrap">
            <h1>Widget Whiz</h1>
            <form method="post" action="admin-post.php">
                <input type="hidden" name="action" value="add_new_sidebar">
                <?php wp_nonce_field('widget_whiz_add_sidebar', 'widget_whiz_add_sidebar_nonce'); ?>
                <h2>Add New Sidebar</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Name</th>
                        <td>
                            <input type="text" name="widget_whiz_sidebars[new][name]" required />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Description</th>
                        <td>
                            <textarea name="widget_whiz_sidebars[new][description]"></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Add Sidebar'); ?>
            </form>

            <h2>Existing Sidebars</h2>
            <form id="widget-whiz-sidebars-form" method="post" action="options.php">
                <?php settings_fields('widget_whiz_group'); ?>
                <table class="form-table" id="widget-whiz-sidebars-list">
                    <?php foreach ($sidebars as $key => $sidebar) : ?>
                        <tr valign="top" data-key="<?php echo esc_attr($key); ?>">
                            <th scope="row"><?php echo esc_html($sidebar['name']); ?></th>
                            <td>
                                <textarea name="widget_whiz_sidebars[<?php echo esc_attr($key); ?>][description]"><?php echo esc_textarea($sidebar['description']); ?></textarea>
                            </td>
                            <td>
                                <input type="hidden" name="widget_whiz_sidebars[<?php echo esc_attr($key); ?>][name]" value="<?php echo esc_attr($sidebar['name']); ?>" />
                                <button type="button" class="button button-secondary widget-whiz-delete-button">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button('Save Changes'); ?>
            </form>
        </div>
<?php
    }

    public function add_new_sidebar()
    {
        check_admin_referer('widget_whiz_add_sidebar', 'widget_whiz_add_sidebar_nonce');

        if (isset($_POST['widget_whiz_sidebars']['new'])) {
            $new_sidebar = $_POST['widget_whiz_sidebars']['new'];
            $sidebars = get_option('widget_whiz_sidebars', array());

            if (!is_array($sidebars)) {
                $sidebars = array();
            }

            $new_sidebar = array(
                'name' => sanitize_text_field($new_sidebar['name']),
                'description' => sanitize_textarea_field($new_sidebar['description']),
            );

            $sidebars[] = $new_sidebar;
            update_option('widget_whiz_sidebars', $sidebars);

            // Register the new sidebar immediately
            register_sidebar(array(
                'id' => sanitize_title($new_sidebar['name']),
                'name' => $new_sidebar['name'],
                'description' => $new_sidebar['description'],
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h6 class="side-title">',
                'after_title' => '</h6>',
            ));

            wp_redirect(admin_url('admin.php?page=widget-whiz'));
            exit;
        }
    }

    public function enqueue_admin_scripts()
    {
        wp_enqueue_script('widget-whiz-js', plugin_dir_url(__FILE__) . '../assets/js/widget-whiz.js', array('jquery'), '1.0.0', true);
        wp_localize_script('widget-whiz-js', 'WidgetWhiz', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('widget_whiz_nonce'),
        ));

        wp_enqueue_style('widget-whiz-css', plugin_dir_url(__FILE__) . '../assets/css/widget-whiz.css');
    }

    public function ajax_delete_sidebar()
    {
        check_ajax_referer('widget_whiz_nonce', 'nonce');

        $key = sanitize_text_field($_POST['key']);
        $sidebars = get_option('widget_whiz_sidebars', array());

        if (isset($sidebars[$key])) {
            unset($sidebars[$key]);
            update_option('widget_whiz_sidebars', $sidebars);

            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    // Meta box for sidebar selector
    public function add_sidebar_meta_box()
    {
        $post_types = get_post_types(array('public' => true), 'names');
    
        foreach ($post_types as $post_type) {
            add_meta_box(
                'widget_whiz_sidebar_selector',
                __('Sidebar Selector', 'widget-whiz'),
                array($this, 'render_sidebar_meta_box'),
                $post_type,
                'side',
                'high'
            );
        }
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
