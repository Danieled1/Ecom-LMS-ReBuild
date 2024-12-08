<?php

/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/
require get_stylesheet_directory() . '/inc/theme-setup.php';


/****************************** CUSTOM FUNCTIONS ******************************/
/****************************** BuddyPress and LearnDash Integration ******************************/
require get_stylesheet_directory() . '/inc/buddypress-learndash-integration.php';

/****************************** Custom Post Types and Taxonomies ******************************/
require get_stylesheet_directory() . '/inc/custom-post-types.php';

/****************************** Enqueue Scripts and Styles ******************************/
require get_stylesheet_directory() . '/inc/enqueue-scripts.php';

/****************************** Admin-Specific Functions and Hooks ******************************/
require get_stylesheet_directory() . '/inc/admin-functions.php';

/****************************** AJAX Actions and Handlers ******************************/
require get_stylesheet_directory() . '/inc/ajax-handlers.php';

/****************************** User Profile Customizations ******************************/
require get_stylesheet_directory() . '/inc/user-profile-functions.php';

/****************************** Email Notifications and Handlers ******************************/
require get_stylesheet_directory() . '/inc/email-functions.php';


add_filter(
    'learndash_status_bubble',
    function( $bubble, $status ) {
        // Check if we are **NOT** on the single course page (for example, using the 'sfwd-courses' post type)
        if ( !is_singular('sfwd-courses') ) {
            // Custom bubble modification outside the course template
            switch ( $status ) {
                case 'In Progress':
                case 'progress':
                case 'incomplete':
                    $bubble = '<div class="card-course-status-in-progress card-course-status ld-status ld-status-progress">' . esc_html_x( 'בתהליך', 'In Progress item status', 'learndash' ) . '</div>';
                    break;

                case 'complete':
                case 'completed':
                case 'Completed':
                    $bubble = '<div class="card-course-status-completed card-course-status ld-status ld-status-complete">' . esc_html_x( 'הושלם', 'Completed item status', 'learndash' ) . '</div>';
                    break;

                default:
                    // Leave unchanged
                    break;
            }
        }

        // Always return the bubble markup.
        return $bubble;
    },
    10,
    2
);


add_filter('learndash_content_tabs', 'customize_learndash_content_tabs', 10, 4);
function customize_learndash_content_tabs($tabs, $context, $course_id, $user_id) {
    // Loop through the tabs and modify their labels
    foreach ($tabs as &$tab) {
        if ($tab['label'] === LearnDash_Custom_Label::get_label('course')) {
            $tab['label'] = __('תוכן הקורס', 'text-domain');
        }

        if ($tab['label'] === 'Reviews') {
            $tab['label'] = __('ביקורות', 'text-domain');
        }
    }

    return $tabs;
}

function add_loading_spinner()
{
    ?>
    <div id="loading-spinner" style="display: none; opacity: 0; transition: opacity 0.5s ease;">
        <div class="spinner"></div>
    </div>
    <style>
        #loading-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, #f4fffe, rgba(244, 255, 254, 0));
            z-index: 9999;
            /* Ensure it is on top of other elements */
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            /* Start with hidden opacity */
            transition: opacity 0.5s ease;
            /* Smooth transition for opacity */
        }

        .spinner {
            border: 8px solid #6836FF;
            border-top: 8px solid #fcb72b;
            border-radius: 50%;
            width: 60px;
            /* Size of the spinner */
            height: 60px;
            /* Size of the spinner */
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <script>
        // document.addEventListener("DOMContentLoaded", function () {
        //     let currentURL = window.location.href;

        //     // Function to detect AJAX requests
        //     function isAjaxRequest(event) {
        //         return event.target instanceof XMLHttpRequest;
        //     }

        //     // Show the loading spinner only if it's a full page reload
        //     document.querySelectorAll('a').forEach(function (link) {
        //         link.addEventListener('click', function (event) {
        //             // Check if the clicked link is triggering a full page load and not an AJAX request
        //             let targetURL = link.href;
        //             if (targetURL !== currentURL && !isAjaxRequest(event)) {
        //                 const spinner = document.getElementById("loading-spinner");
        //                 spinner.style.display = "flex"; // Show the spinner
        //                 setTimeout(() => {
        //                     spinner.style.opacity = 1; // Fade in
        //                 }, 10);
        //             }
        //         });
        //     });

        //     // Hide the spinner after content is loaded
        //     window.addEventListener("load", function () {
        //         const spinner = document.getElementById("loading-spinner");
        //         spinner.style.opacity = 0; // Fade out
        //         setTimeout(() => {
        //             spinner.style.display = "none"; // Hide after fade out
        //         }, 500);
        //     });

        //     // Optionally listen for AJAX completion to hide the spinner if needed
        //     document.addEventListener('ajaxComplete', function () {
        //         const spinner = document.getElementById("loading-spinner");
        //         spinner.style.opacity = 0; // Fade out
        //         setTimeout(() => {
        //             spinner.style.display = "none"; // Hide after fade out
        //         }, 500);
        //     });
        // });
    </script>
    <?php
}
add_action('wp_footer', 'add_loading_spinner');

add_action('bp_setup_nav', 'customize_bp_nav_items', 99);
function customize_bp_nav_items()
{
    global $bp;

    // Check if the 'members' component and nav items exist
    if (isset($bp->members->nav) && is_object($bp->members->nav)) {
        foreach ($bp->members->nav->get() as $nav_item) {
            // Access specific nav items and modify them
            if ($nav_item->slug === 'profile') {
                $nav_item->name = __('פרופיל', 'textdomain'); // Change the label to Hebrew for 'Profile'
            } elseif ($nav_item->slug === 'friends') {
                $nav_item->name = __('חיבורים', 'textdomain'); // Change the label to Hebrew for 'Settings'
            }
            // You can add more conditions to customize other navigation items
        }
    } else {
        error_log('No navigation items found.');
    }
}

/**
 * More strightfoward way to Create LearnDash courses from a JSON file. - exported from CRM
 */
function create_learndash_courses_from_json()
{
    $json_file = get_stylesheet_directory() . '/courses-jsons/cyberlivestream_digital_generations.json'; // Adjust the path as necessary
    $json_data = file_get_contents($json_file);
    $courses = json_decode($json_data, true);

    if ($courses === null) {
        error_log('Error reading JSON file');
        return;
    }

    // Process active and finished courses normally
    foreach (['activeCourses', 'finishedCourses'] as $key) {
        if (!empty($courses[$key])) {
            foreach ($courses[$key] as $course) {
                create_course_and_group($course);
            }
        }
    }

    // Special case for 'openToRegister' because it contains a nested 'generations' array
    if (!empty($courses['openToRegister']['generations'])) {
        foreach ($courses['openToRegister']['generations'] as $course) {
            create_course_and_group($course);
        }
    }
}

function redirect_user_after_login($user_login, $user) {
    // Define the whitelist of usernames
    $allowed_users = ['support@ecomschool.co.il', 'goxik48771','guy_user','instructor_testing5'];

    // Check if the user is whitelisted
    if (in_array($user->user_login, $allowed_users)) {
        // Redirect whitelisted users to the homepage or dashboard
        wp_redirect(home_url('/'));
    } else {
        // Redirect non-whitelisted users to the "Coming Soon" page
        wp_redirect(home_url('/coming-soon'));
    }

    exit;
}
add_action('wp_login', 'redirect_user_after_login', 10, 2);
function my_custom_login_url($login_url, $redirect)
{
     // Only redirect to the custom login page if not already accessing wp-login.php
     if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') === false) {
        return home_url('/custom-login/?redirect_to=' . urlencode($redirect));
    }
    return $login_url; // Allow default login page access
}
add_filter('login_url', 'my_custom_login_url', 10, 2);


function custom_logout_redirect()
{
    wp_redirect(home_url('/custom-login/')); // Redirect to custom login page
    exit();
}
add_action('wp_logout', 'custom_logout_redirect');


/**
 * Fetches and formats the last updated timestamp for a given user meta field.
 * 
 * @param int $user_id The user ID.
 * @param string $meta_key The meta key for the timestamp.
 * @return string Formatted 'Last Updated At' string or 'Never' if not set.
 */
function get_last_updated_at($user_id, $meta_key)
{
    $timestamp = get_user_meta($user_id, $meta_key, true);
    if (!$timestamp) {
        return 'Never';
    }


    return date_i18n('Y-m-d H:i', strtotime($timestamp));
}
