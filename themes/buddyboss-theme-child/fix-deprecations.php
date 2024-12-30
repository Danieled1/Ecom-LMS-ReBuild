<?php
// learndash Instructor-role plugin, didnt validate post before using it 
add_action('init', 'override_instructor_role_dashboard_modal');

function override_instructor_role_dashboard_modal()
{
    if (class_exists('Instructor_Role_Dashboard_Block')) {
        $instance = Instructor_Role_Dashboard_Block::get_instance();
        if (method_exists($instance, 'handle_dashboard_launch_modal')) {
            remove_filter('the_content', [$instance, 'handle_dashboard_launch_modal']);
            add_filter('the_content', 'custom_handle_dashboard_launch_modal');
            error_log('Original handle_dashboard_launch_modal unhooked, custom version applied.');
        }
    }
}

function custom_handle_dashboard_launch_modal($content)
{
    error_log('custom_handle_dashboard_launch_modal called by: ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5));

    static $executed = false;
    if ($executed) {
        return $content;
    }
    $executed = true;
    global $post;
    error_log('Running custom handle');
    // Validate $post
    if (empty($post) || !isset($post->ID)) {
        error_log('custom_handle_dashboard_launch_modal: $post is null or missing ID.');
        return $content;
    }

    $auto_generated_page = get_option('ir_frontend_dashboard_page', 0);
    $visited_dashboard = get_option('ir_frontend_dashboard_launched', false);
    $fd_page_id = get_option('ir_frontend_dashboard_page', false);

    if (is_admin() || intval($auto_generated_page) !== $post->ID || defined('REST_REQUEST') || !current_user_can('manage_options')) {
        return $content;
    }

    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        if (false === $visited_dashboard) {
            wp_enqueue_style(
                'ir_frontend_dashboard_launch_style',
                plugins_url('css/frontend-dashboard/ir-frontend-dashboard-launch.css', __DIR__),
                array(),
                filemtime(plugin_dir_path(__DIR__) . '/css/frontend-dashboard/ir-frontend-dashboard-launch.css')
            );

            wp_enqueue_script(
                'ir_frontend_dashboard_launch_script',
                plugins_url('js/frontend-dashboard/ir-frontend-dashboard-launch.js', __DIR__),
                array('jquery'),
                filemtime(plugin_dir_path(__DIR__) . '/js/frontend-dashboard/ir-frontend-dashboard-launch.js'),
                true
            );

            wp_localize_script(
                'ir_frontend_dashboard_launch_script',
                'ir_fd_data',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('ir_complete_dashboard_launch'),
                    'frontend_dashboard_edit_link' => get_edit_post_link($fd_page_id, 'edit'),
                )
            );

            return $content . ir_get_template(
                INSTRUCTOR_ROLE_ABSPATH . '/modules/templates/frontend-dashboard/ir-frontend-dashboard-launch-modal.template.php',
                array(
                    'background_img' => plugins_url('/css/images/new_modal_bg.png', __DIR__),
                    'center_img' => plugins_url('/images/dashboard-created.png', __DIR__),
                ),
                true
            );
        }
    }

    return $content;
}

