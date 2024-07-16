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
        add_action('wp_ajax_import_sidebars', array($this, 'ajax_import_sidebars'));
        add_action('wp_ajax_delete_sidebar', array($this, 'ajax_delete_sidebar'));
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
        global $wp_registered_sidebars;
        $sidebars = get_option('widget_whiz_sidebars', array());

        // Check for existing sidebars that haven't been imported yet.
        $unimported_sidebars = array();
        foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
            $exists = false;
            foreach ($sidebars as $widget_whiz_sidebar) {
                if ($widget_whiz_sidebar['name'] == $sidebar['name']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $unimported_sidebars[] = $sidebar;
            }
        }
?>
        <div class="wrap">
            <h1>Widget Whiz</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('widget_whiz_group');
                ?>
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

            <?php if (!empty($unimported_sidebars)) : ?>
                <h2>Import Existing Sidebars</h2>
                <form id="widget-whiz-import-form">
                    <ul>
                        <?php foreach ($unimported_sidebars as $sidebar) : ?>
                            <li><?php echo esc_html($sidebar['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" id="widget-whiz-import-button" class="button button-primary">Import Sidebars</button>
                </form>
            <?php endif; ?>

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

    public function import_existing_sidebars()
    {
        global $wp_registered_sidebars;
        $widget_whiz_sidebars = get_option('widget_whiz_sidebars', array());

        foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
            $exists = false;
            foreach ($widget_whiz_sidebars as $widget_whiz_sidebar) {
                if ($widget_whiz_sidebar['name'] == $sidebar['name']) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $widget_whiz_sidebars[] = array(
                    'name' => $sidebar['name'],
                    'description' => $sidebar['description'],
                );
            }
        }

        update_option('widget_whiz_sidebars', $widget_whiz_sidebars);

        return !empty($widget_whiz_sidebars);
    }

    public function ajax_import_sidebars()
    {
        check_ajax_referer('widget_whiz_nonce', 'nonce');

        $result = $this->import_existing_sidebars();
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
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

    public function enqueue_admin_scripts()
    {
        wp_enqueue_script('widget-whiz-js', plugin_dir_url(__FILE__) . '../assets/js/widget-whiz.js', array('jquery'), '1.0.0', true);
        wp_localize_script('widget-whiz-js', 'WidgetWhiz', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('widget_whiz_nonce'),
        ));

        wp_enqueue_style('widget-whiz-css', plugin_dir_url(__FILE__) . '../assets/css/widget-whiz.css');
    }
}
