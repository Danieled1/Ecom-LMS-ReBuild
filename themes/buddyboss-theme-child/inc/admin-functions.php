<?php
function register_sector_email_settings()
{
    register_setting('sector_emails', 'accounting_email');
    register_setting('sector_emails', 'customer_service_email');
}
add_action('admin_init', 'register_sector_email_settings');

function add_resume_management_page()
{
    add_menu_page(
        'Resume Management',          // Page title
        'מנהל השמה',          // Menu title
        'manage_options',             // Capability - Placement team user and support team user and admin user
        'resume-management',          // Menu slug
        'resume_management_page_html', // Function to display the page
        'dashicons-welcome-learn-more', // Menu icon
        5                               // Position

    );
}
add_action('admin_menu', 'add_resume_management_page');

function add_ticket_management_page()
{
    add_menu_page(
        'Ticket Management',          // Page title
        'מנהל פניות',          // Menu title
        'manage_options',             // Capability
        'ticket-management',          // Menu slug
        'ticket_management_page_html', // Function to display the page
        'dashicons-tickets',            // Menu icon
        6                              // Position
    );
}
add_action('admin_menu', 'add_ticket_management_page');

function add_grades_management_page()
{
    add_menu_page(
        'Grades Management',          // Page title
        'מנהל ציונים',          // Menu title
        'manage_options',             // Capability
        'grades-management',          // Menu slug
        'grades_management_page_html', // Function to display the page
        'dashicons-welcome-write-blog', // Menu icon
        7                              // Position
    );
}
add_action('admin_menu', 'add_grades_management_page');

function ticket_management_page_html()
{
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Include your 'admin-ticket-management.php' content here
    include_once(get_stylesheet_directory() . '/admin-ticket-management.php');
}

function grades_management_page_html()
{
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Include your 'admin-grades-management.php' content here
    include_once(get_stylesheet_directory() . '/admin-grades-management.php');
}


function resume_management_page_html()
{
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Include your 'admin-resume-management.php' content here
    include_once(get_stylesheet_directory() . '/admin-resume-management.php');
}
// New Part
function handle_student_group_enrollment($user_id, $group_id) {
    error_log("Starting group enrollment processing for user ID: $user_id");

    $group_ids = is_array($group_id) ? $group_id : array($group_id);
    error_log("Group IDs: " . implode(', ', $group_ids));

    $valid_path_found = false;
    $selected_path_name = '';

    foreach ($group_ids as $group_id) {
        $group_name = get_the_title($group_id);
        error_log("Checking group ID: $group_id with name: $group_name for user ID: $user_id");
        

        // Normalize strings for comparison
        $normalized_group_name = strtolower(str_replace(' ', '', $group_name));

        if (strpos($normalized_group_name, "מחזור") !== false) {
            error_log("Group name '$group_name' contains 'מחזור'");
            $path_terms = get_terms(['taxonomy' => 'path', 'hide_empty' => false]);

            foreach ($path_terms as $term) {
                $normalized_term_name = strtolower(str_replace(' ', '', $term->name));
                if (strpos($normalized_group_name, $normalized_term_name) !== false) {
                    $valid_path_found = true;
                    $selected_path_name = $term->name;
                    error_log("Matching path '$selected_path_name' found in group name for user ID: $user_id");
                    break;
                }
            }
        }
        if ($valid_path_found) break;
    }

    if ($valid_path_found) {
        update_user_meta($user_id, 'path', $selected_path_name); // Save the path name in user meta
        error_log("Valid path '$selected_path_name' confirmed for Student ID: $user_id. Proceeding to create grades post.");
        create_and_populate_grades_post($user_id, $selected_path_name);
    } else {
        error_log("No valid path found for Student ID: $user_id after checking all groups.");
    }
}
add_action('ld_added_group_access', 'handle_student_group_enrollment', 10, 2);

function create_and_populate_grades_post($user_id, $path_name) {
    error_log("Attempting to create a grades post for user ID: $user_id with path: $path_name");

    // Check for existing grades posts to avoid duplicates
    $existing_posts = get_posts([
        'post_type' => 'grades',
        'author'    => $user_id,
        'numberposts' => 1
    ]);

    if (!empty($existing_posts)) {
        error_log("Grades post already exists for user ID: $user_id. No new post created.");
        return;
    }

    // Create the grades post
    $grades_post_id = wp_insert_post([
        'post_author' => $user_id,
        'post_type'   => 'grades',
        'post_title'  => "Grades for User {$user_id} - Path: {$path_name}",
        'post_status' => 'publish',
    ]);

    if ($grades_post_id) {
        error_log("Grades post successfully created for user ID: $user_id, post ID: $grades_post_id");
        
        update_user_meta($user_id, 'associated_grade_post_id', $grades_post_id);

        $grade_items = [];
        // Retrieve the path term to get its sub terms
        $path_term = get_term_by('name', $path_name, 'path');
        if (!$path_term) {
            error_log("Could not find path term by name '$path_name'");
            return;
        }

        $sub_terms = get_terms(['taxonomy' => 'path', 'parent' => $path_term->term_id, 'hide_empty' => false]);

        foreach ($sub_terms as $term) {
            $type = determine_grade_type($term->name);
            $grade_items[] = [
                'grade_type' => $type,
                'grade_name' => $term->name,
                'grade_score' => '',
                'grade_feedback' => '',
                'grade_status' => 'Not Submitted',
                'grade_deadline' => 'A',
            ];
        }

        // save the grade items array
        update_post_meta($grades_post_id, 'grade_items', $grade_items);
        error_log("All grade items successfully added for grades post ID: {$grades_post_id}");
    } else {
        error_log("Failed to create grades post for user ID: $user_id");
    }
}

function determine_grade_type($term_name) {
    $term_name_lower = strtolower($term_name);
    if (strpos($term_name_lower, 'project') !== false) {
        return 'Project';
    } elseif (strpos($term_name_lower, 'exam') !== false) {
        return 'Exam';
    } elseif (strpos($term_name_lower, 'work') !== false) {
        return 'Work';
    } else {
        return 'Test';
    }
}