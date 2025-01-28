<?php
/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
    /**
     * Scripts and Styles loaded by the parent theme can be unloaded if needed
     * using wp_deregister_script or wp_deregister_style.
     *
     * See the WordPress Codex for more information about those functions:
     * http://codex.wordpress.org/Function_Reference/wp_deregister_script
     * http://codex.wordpress.org/Function_Reference/wp_deregister_style
     **/

    // Styles
    wp_enqueue_style('buddyboss-child-css', get_stylesheet_directory_uri() . '/assets/css/custom.css');

    // Javascript
    wp_enqueue_script('buddyboss-child-js', get_stylesheet_directory_uri() . '/assets/js/custom.js');
}
add_action('wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999);

function my_acf_enqueue_scripts()
{
    acf_enqueue_scripts();
}
add_action('wp_enqueue_scripts', 'my_acf_enqueue_scripts');

function child_enqueue_profile_loop_css() {
    // Check if we are on a BuddyPress user profile page
    if ( function_exists('bp_is_user_profile') && bp_is_user_profile() ) {
        wp_enqueue_style(
            'child-profile-loop-css',
            get_stylesheet_directory_uri() . '/assets/css/profile-loop.css',
            array(), // Dependencies, if any
            '1.0.0', // Version
            'all'    // Media
        );
    }
}
add_action('wp_enqueue_scripts', 'child_enqueue_profile_loop_css');


function enqueue_page_grades_assets()
{
    // MAKE SURE IN THE PAGE WORDPRESS EDITOR, THAT THE TEMPLATE NAME IS PICKED(LISTED AT THE START OF A PAGE WE CREATE)
    if (is_page_template('page-grades.php')) {
        wp_enqueue_style('page-grades-style', get_stylesheet_directory_uri() . '/assets/css/page-grades.css', array(), '1.0.0');
        wp_enqueue_script('page-grades-script', get_stylesheet_directory_uri() . '/assets/js/page-grades.js', array(), '1.0.0', true);
        wp_dequeue_script('jquery-migrate'); // Optional: Remove migrate as well
        wp_dequeue_script('acf-input');
        wp_dequeue_script('acf-timepicker');
        wp_dequeue_style('select2');
        global $current_user;
        wp_get_current_user();
        wp_localize_script('page-grades-script', 'userInfo', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'userId' => $current_user->ID,
            'stylesheetDirUri' => get_stylesheet_directory_uri()
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_page_grades_assets');

function enqueue_page_tickets_assets()
{
    // MAKE SURE IN THE PAGE WORDPRESS EDITOR, THAT THE TEMPLATE NAME IS PICKED(LISTED AT THE START OF A PAGE WE CREATE)
    if (is_page_template('page-tickets.php')) {
        wp_enqueue_style('page-tickets-style', get_stylesheet_directory_uri() . '/assets/css/page-tickets.css', array(), '1.0.0');
        wp_enqueue_script('page-tickets-script', get_stylesheet_directory_uri() . '/assets/js/page-tickets.js', array(), '1.0.0', true);

    }
}
add_action('wp_enqueue_scripts', 'enqueue_page_tickets_assets');

function enqueue_page_technical_support_assets()
{
    if (is_page_template('page-technical-support.php')) {
        wp_enqueue_style('page-technical-support-style', get_stylesheet_directory_uri() . '/assets/css/page-technical-support.css', array(), '1.0.0');
        wp_enqueue_script('page-technical-support-script', get_stylesheet_directory_uri() . '/assets/js/page-technical-support.js', array(), '1.0.0', true);

    }
}
add_action('wp_enqueue_scripts', 'enqueue_page_technical_support_assets');

function enqueue_page_placement_assets()
{
    
    if (is_page_template('page-placement.php')) {
        acf_enqueue_scripts();
        wp_enqueue_style('page-placement-style', get_stylesheet_directory_uri() . '/assets/css/page-placement.css', array(), '1.0.0');
        wp_enqueue_script('page-placement-script', get_stylesheet_directory_uri() . '/assets/js/page-placement.js', array(), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_page_placement_assets');

function enqueue_page_reviews_assets(){
    if (is_page_template('page-reviews.php')) {
        wp_enqueue_style('page-reviews-style', get_stylesheet_directory_uri() . '/assets/css/page-reviews.css', array(), '1.0.0');
        wp_enqueue_script('page-reviews-script', get_stylesheet_directory_uri() . '/assets/js/page-reviews.js', array(), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_page_reviews_assets');

function enqueue_page_custom_login()
{
    if(is_page_template('page-custom-login.php')){
        wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@^2.0/dist/tailwind.min.css');
        wp_enqueue_style( 'page-customlogin-style', get_stylesheet_directory_uri() . '/assets/css/page-customlogin.css',[], '1.0.0');
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    
        // Enqueue the Google Fonts stylesheet
        wp_enqueue_style('heebo-font', 'https://fonts.googleapis.com/css2?family=Heebo:wght@100..900&display=swap', array(), null);

    }
}
add_action('wp_enqueue_scripts', 'enqueue_page_custom_login');

// function enqueue_tailwind_for_profiles() {
//     if ( function_exists('bp_is_user') && bp_is_user() ) {
//         wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@^2.0/dist/tailwind.min.css');
//         wp_enqueue_style('transition-style', 'https://unpkg.com/transition-style'); // Enqueue the transition-style CSS

//     }
// }
// add_action('wp_enqueue_scripts', 'enqueue_tailwind_for_profiles');
function enqueue_admin_resume_management_scripts($hook_suffix)
{
    // Check if we're on the Resume Management admin page
    if ('toplevel_page_resume-management' === $hook_suffix) {
        // Enqueue admin CSS for Resume Management
        wp_enqueue_style('admin-resume-management-style', get_stylesheet_directory_uri() . '/assets/css/admin-resume-management.css');

        // Enqueue admin JavaScript for Resume Management
        wp_enqueue_script('admin-resume-management-script', get_stylesheet_directory_uri() . '/assets/js/admin-resume-management.js', array(), '1.0.0', true);

        // Localize script for passing the AJAX URL to JavaScript
        wp_localize_script('admin-resume-management-script', 'adminAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));
    }
}
function enqueue_admin_resume_management_scripts_refactored($hook_suffix)
{
    if ('toplevel_page_resume-management' === $hook_suffix) {
       
        wp_enqueue_style('admin-resume-management-style', get_stylesheet_directory_uri() . '/assets/css/admin-resume-management.css');
        wp_enqueue_script('admin-resume-management-main', get_stylesheet_directory_uri() . '/assets/js/admin-resume-management/main.js', array(), '1.0.0', true);
        
        wp_localize_script('admin-resume-management-main', 'adminAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));

        // Inline script to handle module imports
        add_action('print_footer_scripts', function () {
            echo '
            <script type="module">
                import { initStatusManagement } from "' . get_stylesheet_directory_uri() . '/assets/js/admin-resume-management/statusManagement.js";
                import { initEmailManagement } from "' . get_stylesheet_directory_uri() . '/assets/js/admin-resume-management/emailManagement.js";
                import { attachEventListeners } from "' . get_stylesheet_directory_uri() . '/assets/js/admin-resume-management/eventHandlers.js";

                document.addEventListener("DOMContentLoaded", function () {
                    initStatusManagement();
                    initEmailManagement();
                    attachEventListeners();
                });
            </script>';
        });
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_resume_management_scripts_refactored');



function enqueue_admin_ticket_management_scripts($hook_suffix)
{
    // Check if we're on the Ticket Management admin page
    if ('toplevel_page_ticket-management' === $hook_suffix) {
        // Enqueue admin CSS for Ticket Management
        wp_enqueue_style('admin-ticket-management-style', get_stylesheet_directory_uri() . '/assets/css/admin-ticket-management.css');

        // // Enqueue admin JavaScript for Ticket Management
        wp_enqueue_script('admin-helper-script', get_stylesheet_directory_uri() . '/assets/js/AdminHelper.js', array(), '1.0.0', true);
        wp_enqueue_script('admin-ticket-management-script', get_stylesheet_directory_uri() . '/assets/js/admin-ticket-management.js', array('admin-helper-script'), '1.0.0', true);

        // Localize script for passing the AJAX URL to JavaScript
        wp_localize_script('admin-ticket-management-script', 'adminAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));
        wp_enqueue_editor();
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_ticket_management_scripts');

function enqueue_admin_grades_management_scripts($hook_suffix)
{
    // Check if we're on the Grades Management admin page
    if ('toplevel_page_grades-management' === $hook_suffix) {
        // Enqueue admin CSS for Grades Management C:\Users\User\AppData\Local\Temp\fz3temp-3\admin-grades-management.js
        wp_enqueue_style('admin-grades-management-style', get_stylesheet_directory_uri() . '/assets/css/admin-grades-management.css');

        // Enqueue admin JavaScript for Grades Management
        wp_enqueue_script('admin-grades-management-script', get_stylesheet_directory_uri() . '/assets/js/admin-grades-management.js', array(), '1.0.0', true);
        wp_enqueue_script('sheetjs', 'https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js', array(), '0.17.0', true);

        // Localize script for passing the AJAX URL to JavaScript
        wp_localize_script('admin-grades-management-script', 'adminAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_grades_management_scripts');