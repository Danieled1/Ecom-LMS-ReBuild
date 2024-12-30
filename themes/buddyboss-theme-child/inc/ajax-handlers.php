<?php
require_once 'utility-functions.php';

// Admin-Resume ajax actions
function changeSubmittedOutputEmailAddress()
{

    $new_email = sanitize_email($_POST['email']);
    if (is_email($new_email)) {
        update_option('placement_custom_email', $new_email);
        wp_send_json_success(['message' => 'Email updated successfully.']);
    } else {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_update_custom_email', 'changeSubmittedOutputEmailAddress');

function jobStatusChangedMailTrigger()
{
    if (!isset($_POST['user_id'])) {
        return sendErrorResponse('Invalid request. User ID not provided.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES) && empty($_POST)) {
        return sendErrorResponse('UserID: ' . $_POST['user_id'] . ' = POST content length exceeded the limit, upload a smaller file');
    }

    $userId = intval($_POST['user_id']);
    $requestedStatus = sanitize_text_field($_POST['status'] ?? '');
    $currentStatus = getCurrentUserJobStatus($userId);

    $updates = [];
    $fileUploadResult = handleFileUpload('placement_notes_file', $userId);

    if ($fileUploadResult['error']) {
        return sendErrorResponse($fileUploadResult['message']);
    }

    if ($fileUploadResult['success']) {
        $updates['notesUpdate'] = "A new placement note has been uploaded.";
    }

    $statusUpdateResult = updateJobStatusIfChanged($userId, $requestedStatus, $currentStatus);

    if ($statusUpdateResult['updated']) {
        $updates['statusUpdate'] = "Your job status has been updated to: {$requestedStatus}.";
    } elseif (!$statusUpdateResult['changed']) {
        return sendSuccessResponse('No update needed. The status is already set to the requested one.', true);
    }

    if (!empty($updates)) {
        sendUpdateJobNotifications($userId, $updates, $fileUploadResult['fileUrl'] ?? '');
        return sendSuccessResponse('Updates applied successfully.', false, $fileUploadResult['fileUrl'] ?? '');
    }

    return sendErrorResponse('No updates were applied. Please check your inputs.');
}
add_action('wp_ajax_update_user_job_status', 'jobStatusChangedMailTrigger');

// Admin-Ticket ajax actions
function handleTicketFormSubmission($post_id)
{
    error_log("TEST TEST NEW FORM OF STICKET");
    // Check if the form was submitted and if the post type is 'ticket'
    if (isset($_POST['_acf_post_id']) && get_post_type($_POST['_acf_post_id']) === 'ticket') {
        // Get the post ID of the newly created 'ticket' post
        $ticket_post_id = intval($_POST['_acf_post_id']);

        // Update the post status to 'publish'
        wp_update_post(array(
            'ID' => $ticket_post_id,
            'post_status' => 'publish'
        ));
    }
}
add_action('acf/save_post', 'handleTicketFormSubmission', 20);

function fetchSectorEmails()
{
    global $wpdb; // This gives us access to the database via WordPress's wpdb class

    // Prepare the SQL query to select all options that start with 'sector_email_'
    $query = "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'sector_email_%'";

    // Execute the query
    $results = $wpdb->get_results($query);

    // Initialize an array to hold the sector data
    $sectors = [];

    // Loop through the query results and add each sector to the $sectors array
    foreach ($results as $row) {
        // Extract the sector name from the option name
        $sector_name = str_replace('sector_email_', '', $row->option_name);
        $sector_name = str_replace('_', ' ', $sector_name);
        $sector_name = ucwords($sector_name); // Optionally capitalize the sector name for display

        $sectors[] = [
            'name' => $sector_name,
            'email' => $row->option_value
        ];
    }

    // Use wp_send_json_success to return the sectors in a JSON response
    wp_send_json_success(['sectors' => $sectors]);
}
add_action('wp_ajax_fetch_sector_emails', 'fetchSectorEmails');

function handleUpdateSectorEmails()
{
    // Ensure user has the capability to manage options
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    // Get all current sector options
    global $wpdb;
    $current_sectors = $wpdb->get_col("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'sector_email_%'");

    error_log("Received Post Data: " . print_r($_POST, true));
    $sectorNames = json_decode(stripslashes($_POST['sectorNames']), true);
    $sectorEmails = json_decode(stripslashes($_POST['sectorEmails']), true);

    $updated_sectors = [];

    // Loop through the submitted sectors and emails to update them
    foreach ($sectorNames as $index => $name) {
        $option_name = "sector_email_" . strtolower(str_replace(' ', '_', $name));
        $updated_sectors[] = $option_name; // Add to the list of updated sectors
        if (isset($sectorEmails[$index]) && is_email($sectorEmails[$index])) {
            update_option($option_name, sanitize_email($sectorEmails[$index]));
        }
    }
    // Find which sectors have been removed and delete them
    $sectors_to_delete = array_diff($current_sectors, $updated_sectors);
    foreach ($sectors_to_delete as $sector_to_delete) {
        delete_option($sector_to_delete);
    }

    wp_send_json_success(['message' => 'Sector emails updated successfully']);
}
add_action('wp_ajax_update_sector_emails', 'handleUpdateSectorEmails');

function ticketStatusChangedMailTrigger()
{
    error_log("GOT INTO ticketStatusChangedMailTrigger");
    if (!isset($_POST['ticket_id'])) {
        return sendErrorResponse('Invalid request. Ticket ID not provided.');
    }

    $ticketId = intval($_POST['ticket_id']);
    $requestedStatus = sanitize_text_field($_POST['status'] ?? '');
    $currentStatus = getCurrentUserTicketStatus($ticketId);

    $requestedFeedback = isset($_POST['feedback']) ? wp_kses_post($_POST['feedback']) : null; // Sanitize feedback
    $previousFeedback = get_post_meta($ticketId, 'sector_feedback', true);

    error_log("PREV FEEDBACK: " . $previousFeedback);
    error_log("JUST RECIVED FEEDBACK: " . $requestedFeedback);

    $updates = [];

    // Update Status
    $statusUpdateResult = updateTicketStatusIfChanged($ticketId, $requestedStatus, $currentStatus);
    if ($statusUpdateResult['updated']) {
        $updates['statusUpdate'] = "Your ticket status has been updated to: {$requestedStatus}.";
    } elseif (!$statusUpdateResult['changed']) {
        return sendSuccessResponse('No update needed. The status is already set to the requested one.', true);
    }
    // Update Feedback
    $requestedFeedbackUpdateResult = updateTicketFeedbackIfChanged($ticketId, $requestedFeedback, $previousFeedback);
    if ($requestedFeedbackUpdateResult) {
        $updates['feedbackUpdate'] = "Feedback has been updated. to {$requestedFeedback}";
    } elseif (!$statusUpdateResult['changed']) {
        return sendSuccessResponse('No update needed. The feedback is the same.', true);
    }
    if (!empty($updates)) {
        wp_update_post(array(
            'ID' => $ticketId,
            'post_modified' => current_time('mysql', false),
            'post_modified_gmt' => current_time('mysql', true)
        ));
        sendUpdateTicketNotifications($ticketId, $updates, $requestedFeedback ?? '');
        return sendSuccessResponse('Updates applied successfully.', false, $requestedFeedback ?? '');
    }

    return sendErrorResponse('No updates were applied. Please check your inputs.');
}
add_action('wp_ajax_update_ticket_status', 'ticketStatusChangedMailTrigger');

// Admin-Grades ajax actions

function fetch_admin_user_grades()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $emails = isset($_POST['emails']) ? json_decode(stripslashes($_POST['emails']), true) : [];
    $test_name = isset($_POST['test_name']) ? sanitize_text_field($_POST['test_name']) : '';

    if (empty($emails) || empty($test_name)) {
        wp_send_json_error('Missing required parameters');
        return;
    }

    $results = [];
    foreach ($emails as $email) {
        $user = get_user_by('email', $email);
        if (!$user) {
            $results[] = [
                'email' => $email,
                'name' => 'User not found',
                'current_grade' => 'N/A'
            ];
            continue;
        }

        $grades_post_id = get_user_meta($user->ID, 'associated_grade_post_id', true);
        if (!$grades_post_id) {
            $results[] = [
                'email' => $email,
                'name' => $user->display_name,
                'current_grade' => 'Not enrolled in any main course',
                'user_id' => $user->ID
            ];
            continue;
        }

        $grade_items = get_post_meta($grades_post_id, 'grade_items', true);
        $current_grade = 'N/A'; // Default to 'N/A' if no matching grade item is found

        foreach ($grade_items as $item) {
            if ($item['grade_name'] === $test_name) {
                $current_grade = $item['grade_score'];
                break;
            }
        }

        $results[] = [
            'email' => $email,
            'name' => $user->display_name,
            'current_grade' => $current_grade,
            'user_id' => $user->ID
        ];
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_fetch_admin_user_grades', 'fetch_admin_user_grades');


function save_user_grades()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $grades_data = isset($_POST['grades_data']) ? json_decode(stripslashes($_POST['grades_data']), true) : [];
    if (empty($grades_data)) {
        wp_send_json_error('Missing grade data');
        return;
    }

    $results = [];
    foreach ($grades_data as $data) {
        $user = get_user_by('email', $data['email']);
        if (!$user) {
            continue; // Skip if user not found
        }

        $grades_post_id = get_user_meta($user->ID, 'associated_grade_post_id', true);
        if (!$grades_post_id) {
            continue; // Skip if no grades post associated
        }

        $grade_items = get_post_meta($grades_post_id, 'grade_items', true);
        if (!is_array($grade_items)) {
            continue; // Skip if grade items are not set up properly
        }
        $gmtTime = current_time('mysql', 1); // Get GMT time in MySQL format
        $formattedTime = date('d/m/Y H:i:s', strtotime($gmtTime));
        // Find the specific grade item and update it
        $updated = false;
        foreach ($grade_items as &$item) {
            if ($item['grade_name'] === $data['test_name']) {
                $item['grade_score'] = $data['new_grade'];
                $item['grade_feedback'] = 'Good Job!';
                $item['grade_status'] = 'Complete';
                $item['grade_deadline'] = chr(ord($item['grade_deadline']) + 1);
                $item['last_modified'] = $formattedTime;

                $updated = true;
                break;
            }
        }

        if ($updated) {
            update_post_meta($grades_post_id, 'grade_items', $grade_items); // Save the updated grade items back to the post
            $results[] = [
                'email' => $data['email'],
                'name' => $user->display_name,
                'new_grade' => $data['new_grade'],
                'current_grade' => $item['grade_score'] // or any other relevant data
            ];
        }
    }

    wp_send_json_success(['message' => 'Grades updated successfully', 'results' => $results]);
}
add_action('wp_ajax_save_user_grades', 'save_user_grades');

// !!!!!NEED TO ADD TO THE COURSE SETUP A MUST DO STEP: set up start and end dates!!!!!!!
function get_active_courses()
{
    $active_courses = [];
    $groups = learndash_get_groups();
    foreach ($groups as $group) {
        $group_id = $group->ID;
        $start_date = get_post_meta($group_id, 'start_date', true);
        $end_date = get_post_meta($group_id, 'end_date', true);
        $status = get_post_meta($group_id, 'status', true);

        $current_date = current_time('Y-m-d');
        if ($status !== 'completed' && strtotime($current_date) >= strtotime($start_date) && strtotime($current_date) <= strtotime($end_date)) {
            $active_courses[] = [
                'id' => $group_id,
                'name' => get_the_title($group_id),
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];
        }
    }
    return $active_courses;
}

// function fetch_client_grades()
// {
//     if (!isset($_POST['user_id'])) {
//         wp_send_json_error('User ID not provided');
//         return;
//     }
//     $user_id = intval($_POST['user_id']);

//     $grade_post_id = get_user_meta($user_id, 'associated_grade_post_id', true);
//     if (!$grade_post_id) {
//         wp_send_json_error('No associated grade post ID found for user ID: ' . $user_id);
//         return;
//     }

//     $grade_items = get_post_meta($grade_post_id, 'grade_items', true);  // Direct retrieval
//     if (!$grade_items) {
//         wp_send_json_error('No grade items found for post ID: ' . $grade_post_id);
//         return;
//     }
//     wp_send_json_success($grade_items);
// }
function fetch_client_grades()
{
    if (!isset($_POST['user_id'])) {
        wp_send_json_error('User ID not provided');
        return;
    }

    $user_id = intval($_POST['user_id']);
    global $wpdb;

    // Start timing for performance logging
    $start_time = microtime(true);

    // Fetch the associated grade post ID
    $grade_post_id = get_user_meta($user_id, 'associated_grade_post_id', true);
    if (!$grade_post_id) {
        wp_send_json_error('No associated grade post ID found for user ID: ' . $user_id);
        return;
    }

    // Optimize the retrieval of grade items
    $query = $wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'grade_items'",
        $grade_post_id
    );
    $grade_items_serialized = $wpdb->get_var($query);

    if (!$grade_items_serialized) {
        wp_send_json_error('No grade items found for post ID: ' . $grade_post_id);
        return;
    }

    // Unserialize grade items
    $grade_items = maybe_unserialize($grade_items_serialized);

    if (!is_array($grade_items)) {
        wp_send_json_error('Invalid grade items data for post ID: ' . $grade_post_id);
        return;
    }

    // End timing and log performance
    $end_time = microtime(true);
    $elapsed_time = round(($end_time - $start_time) * 1000, 2); // Time in milliseconds
    error_log("fetch_client_grades execution time: {$elapsed_time}ms");

    // Send the grade items as the response
    wp_send_json_success($grade_items);
}
add_action('wp_ajax_fetch_client_grades', 'fetch_client_grades');

function handleSaveGrades()
{
    // Check if the correct action and all parameters are set
    if (!isset($_POST['action'], $_POST['user_id'], $_POST['index'], $_POST['status'], $_POST['score'], $_POST['feedback'], $_POST['deadline']) || $_POST['action'] !== 'save_grades') {
        error_log('Missing parameters or incorrect action');
        wp_send_json_error('Missing parameters or incorrect action');
        return;
    }

    $user_id = intval($_POST['user_id']);
    $index = intval($_POST['index']); // Ensure index is treated as an integer
    $status = sanitize_text_field($_POST['status']);
    $score = sanitize_text_field($_POST['score']);
    $feedback = sanitize_textarea_field($_POST['feedback']);
    $deadline = sanitize_text_field($_POST['deadline']);

    // Authorization check 
    if (!current_user_can('edit_user', $user_id)) {
        error_log('Insufficient permissions');
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $grades_post_id = get_user_meta($user_id, 'associated_grade_post_id', true);
    if (!$grades_post_id) {
        error_log('Grade post not found');
        wp_send_json_error('Grade post not found');
        return;
    }

    $grades = get_post_meta($grades_post_id, 'grade_items', true);
    if (!isset($grades[$index])) {
        error_log('Grade not found at index ' . $index);
        wp_send_json_error('Grade not found');
        return;
    }

    $gmtTime = current_time('mysql', 1); // Get GMT time in MySQL format
    $formattedTime = date('d/m/Y H:i:s', strtotime($gmtTime));

    // Update the grade at the specified index
    $grades[$index]['grade_status'] = $status;
    $grades[$index]['grade_score'] = $score;
    $grades[$index]['grade_deadline'] = $deadline;
    $grades[$index]['grade_feedback'] = $feedback;
    $grades[$index]['last_modified'] = $formattedTime;

    wp_update_post(array('ID' => $grades_post_id));

    // Save the modified grades back to the database
    if (update_post_meta($grades_post_id, 'grade_items', $grades)) {
        error_log('Grade updated successfully at index ' . $index);
        wp_send_json_success([
            'message' => 'Grade updated successfully',
            'updated_grade' => $grades[$index]
        ]);
    } else {
        error_log('Failed to update grades');
        wp_send_json_error('Failed to update grades');
    }
}
add_action('wp_ajax_save_grades', 'handleSaveGrades');

function fetchUsersAdminGradesPage()
{
    $search_term = get_sanitized_input('s');
    $search_type = get_sanitized_input('type');

    error_log('GET data: ' . print_r($_GET, true));

    $results = [
        'courses' => [],
        'users' => [],
    ];

    if (!empty($search_term)) {
        if ($search_type === 'courses') {
            fetchCoursesToSetupResults($search_term, $results);
        } elseif ($search_type === 'students') {
            fetchUsersToSetupResults($search_term, $results);
        } else {
            // Default display: Fetch active courses
            $results['courses'] = get_active_courses();
        }
    }

    if (empty($search_term)) {
        error_log('No search term provided, not fetching users.');
        wp_send_json_success(['users' => []]);
        return;
    }
    wp_send_json_success($results);
}
add_action('wp_ajax_fetch_users_admin_grades_page', 'fetchUsersAdminGradesPage');

function fetchCoursesToSetupResults($search_term, &$results)
{
    $args = [
        'post_type' => 'groups',
        's' => $search_term,
        'posts_per_page' => -1,
    ];

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $course_id = get_the_ID();
            $results['courses'][] = [
                'id' => $course_id,
                'name' => get_the_title(),
                'start_date' => get_post_meta($course_id, 'start_date', true),
                'end_date' => get_post_meta($course_id, 'end_date', true),
            ];
        }
        wp_reset_postdata();
    }
}

function fetchUsersToSetupResults($search_term, &$results)
{
    $args = [
        'number' => 50,
        'orderby' => 'display_name',
        'order' => 'ASC',
    ];

    if (!empty($search_term)) {
        $args['search'] = '*' . $search_term . '*';
        $args['search_columns'] = ['user_login', 'user_nicename', 'user_email', 'display_name'];
    }

    $user_query = new WP_User_Query($args);
    error_log('SQL Query: ' . $user_query->request);

    $results['users'] = process_user_query_results($user_query, null, 'grades');
}

function fetchStudentsByCourse()
{

    if (!isset($_POST['course_id'])) {
        error_log('No course ID provided in fetchStudentsByCourse');
        wp_send_json_error('No course ID provided');
        return;
    }

    $course_id = sanitize_text_field($_POST['course_id']);
    $results = [
        'users' => [],
    ];

    if (!empty($course_id)) {
        $args = [
            'number' => -1,
            'orderby' => 'display_name',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => 'learndash_group_users_' . $course_id,
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $user_query = new WP_User_Query($args);
        if (empty($user_query->results)) {
            error_log('No users found for course ID: ' . $course_id);
            wp_send_json_success(['users' => []]);  // Send an empty array if no users found
        } else {
            error_log('Found ' . count($user_query->results) . ' users for course ID: ' . $course_id);
            $results = process_user_query_results($user_query, null, 'grades');
            wp_send_json_success($results);
        }

        $results['users'] = process_user_query_results($user_query, null, 'grades');
    } else {
        error_log('No course ID provided');
        $results = ['users' => []];
    }
    wp_send_json_success($results);
}
add_action('wp_ajax_fetch_students_by_course', 'fetchStudentsByCourse');


function fetchUserDataResumeNew()
{
    $search_term = get_sanitized_input('s');
    $job_status = get_sanitized_input('job_status');
    $placement_notes_filter = get_sanitized_input('placement_notes');
    $specific_date = get_sanitized_input('specific_date');
    $start_date = get_sanitized_input('start_date');
    $end_date = get_sanitized_input('end_date');
    error_log('GET data: ' . print_r($_GET, true));

    $meta_query = [['key' => 'resume_file', 'compare' => 'EXISTS']];

    if (!empty($job_status)) {
        apply_job_status_filter($meta_query, $job_status);
    }
    if (!empty($placement_notes_filter)) {
        apply_placement_notes_filter($meta_query, $placement_notes_filter);
    }
    if (!empty($specific_date) || (!empty($start_date) && !empty($end_date))) {
        apply_date_filters($meta_query, $specific_date, $start_date, $end_date);
    }

    $args = [
        'number' => 50,         // Need to provide pagaination in the future
        'orderby' => 'meta_value',
        'meta_key' => 'resume_last_updated',   //The logic behind this default meta_key is any user who havent reached the point of working on his resumem shouldnt be visible to the placement team.
        'order' => 'DESC',
        'meta_query' => $meta_query
    ];

    if (!empty($search_term)) {
        $args['search'] = '*' . $search_term . '*';
        $args['search_columns'] = ['user_login', 'user_nicename', 'user_email', 'display_name'];
    }

    $user_query = new WP_User_Query($args);
    $users = process_user_query_results($user_query, $placement_notes_filter, 'resume');
    return_json_response($users);
}
add_action('wp_ajax_fetch_user_data_resume', 'fetchUserDataResumeNew');

function get_sanitized_input($input_key, $default = '')
{
    return isset($_GET[$input_key]) ? sanitize_text_field($_GET[$input_key]) : $default;
}

function apply_job_status_filter(&$meta_query, $job_status)
{
    if (!empty($job_status)) {
        $meta_query[] = [
            'key' => 'job_status',
            'value' => $job_status,
            'compare' => '='
        ];
    }
}

function apply_placement_notes_filter(&$meta_query, $placement_notes_filter)
{
    if ($placement_notes_filter === 'exists') {
        $meta_query[] = [
            'key' => 'placement_notes',
            'value' => '',
            'compare' => '!='
        ];
    } elseif ($placement_notes_filter === 'not_exists') {
        $meta_query[] = [
            'key' => 'placement_notes',
            'value' => '',
            'compare' => '='
        ];
    }
}

function apply_date_filters(&$meta_query, $specific_date, $start_date, $end_date)
{
    if (!empty($specific_date)) {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key' => 'resume_last_updated',
                'value' => $specific_date,
                'compare' => '=',
                'type' => 'DATE'
            ],
            [
                'key' => 'job_status_last_updated',
                'value' => $specific_date,
                'compare' => '=',
                'type' => 'DATE'
            ],
            [
                'key' => 'placement_notes_last_updated',
                'value' => $specific_date,
                'compare' => '=',
                'type' => 'DATE'
            ]
        ];
    } elseif (!empty($start_date) && !empty($end_date)) {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key' => 'resume_last_updated',
                'value' => [$start_date, $end_date],
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ],
            [
                'key' => 'job_status_last_updated',
                'value' => [$start_date, $end_date],
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ],
            [
                'key' => 'placement_notes_last_updated',
                'value' => [$start_date, $end_date],
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ]
        ];
    }
}

function process_user_query_results($user_query, $placement_notes_filter, $context = 'default')
{
    $processed_results = array_filter(array_map(function ($user) use ($context, $placement_notes_filter) {
        return extract_user_data($user, $context, $placement_notes_filter);
    }, $user_query->get_results()));
    error_log('Processed Results: ' . print_r($processed_results, true));
    return $processed_results;
}
function extract_user_data($user, $context, $placement_notes_filter = null)
{
    $userId = $user->ID;

    switch ($context) {
        case 'grades':
            $main_group = get_main_student_group($userId);

            $path = get_user_meta($userId, 'path', true) ?: 'No Path';

            return [
                'id' => $userId,
                'groups' => $main_group,
                'display_name' => $user->display_name,
                'email' => $user->user_email,
                'path' => $path
            ];

        case 'resume':
            $userEmail = $user->user_login;
            $main_student_group = get_main_student_group($userId);
            $most_recent_update = get_most_recent_last_update($userId);
            $job_status = get_field('job_status', 'user_' . $userId);
            $job_status_label = isset($job_status['label']) ? $job_status['label'] : 'No Status';
            $job_status_updated_at = $job_status ? get_last_updated_at($userId, 'job_status_last_updated') : 'No Status';

            $feedback = get_field('feedback', 'user_' . $userId);

            $resume_file = get_field('resume_file', 'user_' . $userId);
            $resume_url = $resume_file ? esc_url($resume_file['url']) : '';
            $resume_filename = is_array($resume_file) && isset($resume_file['filename']) ? $resume_file['filename'] : '';
            $resume_updated_at = $resume_file ? get_last_updated_at($userId, 'resume_last_updated') : 'Never updated';

            $placement_notes = get_field('placement_notes', 'user_' . $userId);
            $placement_notes_url = $placement_notes ? esc_url($placement_notes) : '';
            $placement_notes_filename = basename($placement_notes_url);
            $placement_notes_updated_at = $placement_notes ? get_last_updated_at($userId, 'placement_notes_last_updated') : 'Never updated';

            // Applying filter criteria for placement notes existence
            if ($placement_notes_filter === 'exists' && empty($placement_notes_url)) {
                return null; // Exclude this user if expecting notes to exist but they don't
            } elseif ($placement_notes_filter === 'not_exists' && !empty($placement_notes_url)) {
                return null; // Exclude this user if expecting no notes but they exist
            }

            return [
                'id' => $userId,
                'email' => $userEmail,
                'group' => $main_student_group,
                'display_name' => $user->display_name,
                'email' => $user->user_email,
                'job_status' => [
                    'label' => $job_status_label,
                    'updated_at' => $job_status_updated_at,
                ],
                'resume' => [
                    'url' => $resume_url,
                    'filename' => $resume_filename,
                    'updated_at' => $resume_updated_at,
                ],
                'placement_notes' => [
                    'url' => $placement_notes_url,
                    'filename' => $placement_notes_filename,
                    'updated_at' => $placement_notes_updated_at,
                ],
                'last_updated' => $most_recent_update,
                'feedback' => $feedback ?: 'No Feedback'
            ];
        default:
            throw new Exception('Invalid context provided for user data extraction.');
    }
}

function get_most_recent_last_update($userId)
{
    $last_updates = [
        get_last_updated_at($userId, 'resume_last_updated'),
        get_last_updated_at($userId, 'job_status_last_updated'),
        get_last_updated_at($userId, 'placement_notes_last_updated')
    ];
    $last_updates = array_filter($last_updates); // Remove any empty values
    usort($last_updates, function ($a, $b) { // Sort to get the latest date
        return strtotime($b) - strtotime($a);
    });
    $last_updated = $last_updates ? reset($last_updates) : 'Never';
    return $last_updated;
}

function get_main_student_group($userId)
{
    $user_groups = learndash_get_users_group_ids($userId);

    // Fetch group titles and filter by keyword "מחזור"
    $group_names = array_map(function ($id) {
        $title = get_the_title($id);
        if (strpos($title, "מחזור") !== false) {
            $formatted_title = str_replace("לימוד קורס", "", $title);
            return trim($formatted_title);
        }
        return null;
    }, $user_groups);

    $group_names = array_filter($group_names); // Remove null entries
    $group_names_string = implode(', ', $group_names); // Create a comma-separated string of group names
    return $group_names_string;
}

function return_json_response($users)
{
    if (empty($users)) {
        error_log('No users matched the query.');
        wp_send_json_success(['users' => []]);
    } else {
        error_log('Users data prepared for return.');
        // Re-index the array to ensure it's sequential
        $users = array_values($users);

        wp_send_json_success(['users' => $users]);
    }
}
