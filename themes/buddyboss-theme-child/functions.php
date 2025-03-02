<?php

/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/
require get_stylesheet_directory() . '/inc/theme-setup.php';

// Disable LearnDash Hub API Calls
add_filter('pre_http_request', function ($response, $args, $url) {
    if (strpos($url, 'checkout.learndash.com') !== false) {
        error_log('⚠️ Blocked LearnDash API call: ' . $url);
        return new WP_Error('blocked_learndash_request', __('Blocked LearnDash API Call -ecom'), ['status' => 403]);
    }
    return $response;
}, 10, 3);
add_action('init', function () {
    remove_action('wp_ajax_ld_hub_plugin_action', ['LearnDash\Hub\Controller\Projects_Controller', 'plugin_action']);
    remove_action('wp_ajax_ld_hub_refresh_repo', ['LearnDash\Hub\Controller\Projects_Controller', 'refresh_repo_data']);
    remove_action('wp_ajax_ld_hub_bulk_action', ['LearnDash\Hub\Controller\Projects_Controller', 'bulk_action']);
    if (class_exists('LearnDash\Hub\Controller\Projects_Controller')) {
        remove_filter('http_request_args', ['LearnDash\Hub\Controller\Projects_Controller', 'maybe_append_auth_headers'], 10);
        remove_filter('site_transient_update_plugins', ['LearnDash\Hub\Controller\Projects_Controller', 'push_update'], 10);
        
    }
    remove_filter('bp_ajax_queryst', 'bb_disabled_notification_actions_by_user', 10, 2);
});

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

/****************************** Weird Bugs from Plugins | Themes ******************************/
require get_stylesheet_directory() . '/fix-deprecations.php';


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
function displayHeaderWithIcon($svg_name, $header_text)
{
	echo '<div class="status-header">';
	echo '<div class="chart-icon">';
	echo '<img src=' . get_stylesheet_directory_uri() . '/assets/vectors/' . $svg_name . '.svg"	alt="' . $svg_name . '" class="chart-simple-img"/>';
	echo '</div>';
	echo '<h3 class="resume-status">' . $header_text . '</h3>';
	echo '</div>';
}

// add_action('wp_footer', function() {
//     global $wp_roles;

//     if (!isset($wp_roles)) {
//         $wp_roles = new WP_Roles();
//     }

//     echo '<pre>';
//     foreach ($wp_roles->roles as $role_name => $role_info) {
//         echo "Role: " . $role_name . "\n";
//         echo "Capabilities:\n";
//         print_r($role_info['capabilities']);
//         echo "\n\n";
//     }
//     echo '</pre>';
// });


add_action('after_setup_theme', function () {
    remove_action('wp_footer', 'buddyboss_theme_buddypanel');
});



function my_bb_disabled_notification_actions_by_user( $user_id = 0, $type = 'web' ) {
    if ( empty( $user_id ) || bb_enabled_legacy_email_preference() ) {
        return array();
    }

    $preferences = bb_register_notification_preferences();
    $enabled_all_notification = bp_get_option( 'bb_enabled_notification', array() );
    $all_notifications = array();
    $settings_by_admin = array();

    if ( empty( $preferences ) ) {
        return [];
    }

    $preferences = array_column( $preferences, 'fields', null );
    foreach ( $preferences as $key => $val ) {
        $all_notifications = array_merge( $all_notifications, (array) $val ); // Ensure it's always an array
    }

    $all_notifications = array_map(
        function ( $n ) use ( $type ) {
            if (
                ! empty( $n['notifications'] ) &&
                in_array( $type, array( 'web', 'app' ), true )
            ) {
                $n['key'] = $n['key'] . '_' . $type;
                return $n;
            } elseif (
                ! empty( $n['email_types'] ) &&
                'email' === $type
            ) {
                $n['key'] = $n['key'] . '_' . $type;
                return $n;
            }
        },
        $all_notifications
    );

    $all_actions = array_column( array_filter( $all_notifications ), 'notifications', 'key' );

    if ( empty( $all_actions ) ) {
        return [];
    }

    foreach ( $all_actions as $key => $val ) {
        $all_actions[ $key ] = array_column( array_filter( (array) $val ), 'component_action' ); // Ensure it's an array
    }

    $admin_excluded_actions = [];
    $all_notifications = array_column( array_filter( $all_notifications ), 'default', 'key' );

    if ( ! empty( $enabled_all_notification ) ) {
        foreach ( $enabled_all_notification as $key => $types ) {
            if ( isset( $types['main'] ) && 'no' === $types['main'] ) {
                $admin_excluded_actions = array_merge( $admin_excluded_actions, (array) $all_actions[ $key . '_' . $type ] );
            }
            if ( isset( $types[ $type ] ) ) {
                $settings_by_admin[ $key . '_' . $type ] = $types[ $type ];
            }
        }
    }

    $notifications = bp_parse_args( $settings_by_admin, $all_notifications );
    $excluded_actions = [];
    $notifications_type_key = 'enable_notification';
    if ( in_array( $type, array( 'web', 'app' ), true ) ) {
        $notifications_type_key .= '_' . $type;
    }

    foreach ( $notifications as $key => $val ) {
        $user_val = get_user_meta( $user_id, $key, true );
        if ( $user_val ) {
            $notifications[ $key ] = $user_val;
        }

        if (
            isset( $all_actions[ $key ] ) &&
            is_array( $all_actions[ $key ] ) &&
            (
                'no' === $notifications[ $key ] ||
                'no' === bp_get_user_meta( $user_id, $notifications_type_key, true )
            )
        ) {
            $excluded_actions = array_merge( $excluded_actions, (array) $all_actions[ $key ] );
        }
    }

    if ( ! empty( $admin_excluded_actions ) ) {
        $excluded_actions = array_merge( $excluded_actions, (array) $admin_excluded_actions );
    }
    return array_unique( (array) $excluded_actions ); // Ensure return is always an array
}

// Hook the function back
add_filter('bp_ajax_queryst', 'my_bb_disabled_notification_actions_by_user', 10, 2);


// function compress_large_images() {
//     $images = [
//         "/home/devdigitalschool/public_html/wp-content/uploads/2019/08/IMG_3349.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/youzer/file_5e29a45838144.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2019/08/IMG_20190702_171627.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2019/08/IMG_20190702_165130.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/backup/2021/07/obi-onyeador-oL3-V8xhqlI-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2021/07/obi-onyeador-oL3-V8xhqlI-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2019/08/IMG_20190702_164720.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/youzer/BA308AC5-472B-4E58-B571-A8F41388FC59.jpeg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/youzer/file_5e29a45838144_thumb.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/backup/2021/07/neel-aSPbuSG4rpo-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2021/07/neel-aSPbuSG4rpo-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2020/02/lukas-blazek-mcSDtbWXUZU-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/backup/2021/07/pexels-rodnae-productions-7310246.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/backup/2021/07/launchpresso-wz6SAUFIHk0-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2021/07/pexels-rodnae-productions-7310246.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2021/07/launchpresso-wz6SAUFIHk0-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2019/10/tv_PNG39242.png",
//         "/home/devdigitalschool/public_html/wp-content/uploads/backup/2021/07/ivan-shilov-ucUB9wxkPgY-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/2021/07/ivan-shilov-ucUB9wxkPgY-unsplash.jpg",
//         "/home/devdigitalschool/public_html/wp-content/uploads/youzer/BA308AC5-472B-4E58-B571-A8F41388FC59_thumb.jpg"
//     ];

//     foreach ($images as $image_path) {
//         if (!file_exists($image_path)) {
//             error_log("⚠️ Image not found: " . $image_path);
//             continue;
//         }

//         $info = getimagesize($image_path);
//         if (!$info) {
//             error_log("⚠️ Unable to read image info: " . $image_path);
//             continue;
//         }

//         $mime = $info['mime'];
//         switch ($mime) {
//             case 'image/jpeg':
//                 $image = imagecreatefromjpeg($image_path);
//                 imagejpeg($image, $image_path, 75); // Compress to 75% quality
//                 break;
//             case 'image/png':
//                 $image = imagecreatefrompng($image_path);
//                 imagepng($image, $image_path, 7); // PNG compression (0-9)
//                 break;
//             case 'image/gif':
//                 $image = imagecreatefromgif($image_path);
//                 imagegif($image, $image_path);
//                 break;
//             default:
//                 error_log("⚠️ Unsupported image format: " . $mime);
//                 continue;
//         }

//         imagedestroy($image);
//         error_log("✅ Optimized image: " . $image_path);
//     }

//     error_log("🚀 Finished compressing all large images!");
// }
function optimize_uploaded_images($file) {
    // Validate the file type
    $file_type = wp_check_filetype($file['file']);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($file_type['type'], $allowed_types)) {
        return $file; // Skip non-image files
    }

    $image_path = $file['file'];
    $info = getimagesize($image_path);

    if (!$info) {
        error_log("⚠️ Unable to read image info: " . $image_path);
        return $file;
    }

    $mime = $info['mime'];
    $optimized = false;

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($image_path);
            imagejpeg($image, $image_path, 70); // 70% quality for JPEG
            $optimized = true;
            break;
        case 'image/png':
            $image = imagecreatefrompng($image_path);
            imagepng($image, $image_path, 7); // Strong PNG compression
            $optimized = true;
            break;
        case 'image/gif':
            $image = imagecreatefromgif($image_path);
            imagegif($image, $image_path);
            $optimized = true;
            break;
    }

    if ($optimized) {
        imagedestroy($image);
        error_log("✅ Optimized new upload: " . $image_path);
    }

    // Convert to WebP for faster loading
    $webp_path = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image_path);
    $image_webp = imagecreatefromstring(file_get_contents($image_path));
    
    if ($image_webp) {
        imagewebp($image_webp, $webp_path, 75);
        imagedestroy($image_webp);
        error_log("✅ WebP created: " . $webp_path);
    }

    return $file;
}

add_filter('wp_handle_upload', 'optimize_uploaded_images');


function add_lazy_loading_to_images($content) {
    return str_replace('<img', '<img loading="lazy"', $content);
}
add_filter('the_content', 'add_lazy_loading_to_images');

function serve_webp_images($content) {
    return str_replace(['.jpg', '.png'], '.webp', $content);
}
add_filter('the_content', 'serve_webp_images');

function preload_pages() {
    echo '<script src="https://instant.page/5.1.0" type="module" defer></script>';
}
add_action('wp_head', 'preload_pages');


// IMPORTENT - disabled defualy buddypanel for our custom one 
function disable_default_buddypanel() {
    remove_action('wp_footer', 'buddyboss_buddypanel'); // Remove the default BuddyPanel
    wp_dequeue_style('buddyboss-buddypanel-style'); // Remove default BuddyPanel CSS
    wp_dequeue_script('buddyboss-buddypanel-script'); // Remove default BuddyPanel JS
}
add_action('wp_enqueue_scripts', 'disable_default_buddypanel', 5);

function load_custom_buddypanel() {
    wp_enqueue_style('buddypanel-style', get_stylesheet_directory_uri() . '/assets/css/buddypanel.css', [], '1.0', 'all');
    wp_enqueue_script('buddypanel-script', get_stylesheet_directory_uri() . '/assets/js/buddypanel.js', ['jquery'], '1.0', true);
}
add_action('wp_enqueue_scripts', 'load_custom_buddypanel', 1); // Priority 1 ensures it loads first
