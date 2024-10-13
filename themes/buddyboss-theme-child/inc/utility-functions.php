<?php
// Resume Handling 
function handleFileUpload($fieldName, $userId)
{
    if (empty($_FILES[$fieldName]['name'])) {
        return ['success' => false, 'error' => false, 'message' => 'No file uploaded']; // No file uploaded
    }

    $allowedFileTypes = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png']; // Add any other required types

    // Check file extension
    $fileExtension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileExtension), $allowedFileTypes)) {
        return ['success' => false, 'error' => true, 'message' => 'Invalid file type. Only ' . implode(', ', $allowedFileTypes) . ' files are allowed.'];
    }
    
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $fileUpload = wp_handle_upload($_FILES[$fieldName], ['test_form' => false]);

    if (isset($fileUpload['error'])) {
        return ['success' => false, 'error' => true, 'message' => 'File upload failed: ' . $fileUpload['error']];
    }

    $filePath = $fileUpload['file'];
    $fileUrl = $fileUpload['url'];
    updatePlacementNotesField($userId, $filePath);

    return ['success' => true, 'error' => false, 'fileUrl' => $fileUrl, 'message' => 'File uploaded successfully'];
}

function updateJobStatusIfChanged($userId, $requestedStatus, $currentStatus)
{
    if ($requestedStatus === $currentStatus['value']) {
        return ['updated' => false, 'changed' => false];
    }

    $updateResult = update_field('job_status', $requestedStatus, 'user_' . $userId);
    if ($updateResult) {
        update_user_meta($userId, 'job_status_last_updated', current_time('mysql'));
        return ['updated' => true, 'changed' => true];
    }

    return ['updated' => false, 'changed' => true];
}

function updatePlacementNotesField($userId, $filePath)
{
    $fileType = wp_check_filetype(basename($filePath), null);
    $attachmentData = [
        'guid'           => $filePath,
        'post_mime_type' => $fileType['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filePath)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    // Insert the attachment.
    $attachId = wp_insert_attachment($attachmentData, $filePath, 0);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attachData = wp_generate_attachment_metadata($attachId, $filePath);
    wp_update_attachment_metadata($attachId, $attachData);

    // Update ACF field
    update_field('placement_notes', $attachId, 'user_' . $userId);
    update_user_meta($userId, 'placement_notes_last_updated', current_time('mysql'));
}

function sendUpdateJobNotifications($userId, $updates, $fileUrl)
{
    $userInfo = get_userdata($userId);
    $emailToStudent = $userInfo->user_email;
    $emailToPlacementTeam = get_option('placement_custom_email');
    $subjectToStudent = "Updates to Your Profile";
    $subjectToPlacementTeam = "User Profile Update Notification";
    $messageToStudent = "Hello " . $userInfo->first_name . ",\n\nHere are the updates to your profile:\n\n";
    $messageToPlacementTeam = "The following updates were made to the user profile of " . $userInfo->user_login . ":\n\n";
    error_log('Student Email: ' . $emailToStudent);
    foreach ($updates as $update) {
        $messageToStudent .= $update . "\n";
        $messageToPlacementTeam .= $update . "\n";
    }

    if ($fileUrl) {
        $messageToStudent .= "\nYou can view the updated placement notes here: " . $fileUrl;
        $messageToPlacementTeam .= "\nUpdated placement notes can be found here: " . $fileUrl;
    }

    $messageToStudent .= "\n\nBest Regards,\nYour Team";
    $messageToPlacementTeam .= "\n\nBest Regards,\nAdmin Team";

    // Send emails
    wp_mail($emailToStudent, $subjectToStudent, $messageToStudent);
    wp_mail($emailToPlacementTeam, $subjectToPlacementTeam, $messageToPlacementTeam);
}

function getCurrentUserJobStatus($userId)
{
    return get_field('job_status', 'user_' . $userId);
}

// Ticket Handling
function updateTicketFeedbackIfChanged($ticketId, $requestedFeedback, $previousFeedback)
{
    if ($requestedFeedback !== $previousFeedback) {
        // Update the ticket status using update_post_meta
        update_post_meta($ticketId, 'sector_feedback', $requestedFeedback);
        // Return an array indicating that the status was updated
        return array('updated' => true, 'changed' => true);
    } else {
        // Return an array indicating that no changes were made
        return array('updated' => false, 'changed' => false);
    }
}

function updateTicketStatusIfChanged($ticketId, $requestedStatus, $currentStatus)
{
    $status_options = array(
        'review' => 'Waiting For Review',
        'documents' => 'Missing Documents',
        'done' => 'Done',
    );

    if (array_key_exists($requestedStatus, $status_options)) {
        $newStatusArray = array(
            'value' => $requestedStatus,
            'label' => $status_options[$requestedStatus],
        );

        // Update the ticket status using update_post_meta
        update_post_meta($ticketId, 'ticket_status', $newStatusArray);
        error_log("Statuses Changed from: " . $currentStatus['value'] . " To: " . $requestedStatus);
        return array('updated' => true, 'changed' => true);
    } else {
        // Return an array indicating that no changes were made
        return array('updated' => false, 'changed' => false);
    }
}

function getCurrentUserTicketStatus($ticketId)
{
    $currentStatus = get_post_meta($ticketId, 'ticket_status', true);
    return $currentStatus;
}

function sendUpdateTicketNotifications($ticketId, $updates, $feedback)
{
    $ticket_author_id = get_post_field('post_author', $ticketId);
    $ticket_author_info = get_userdata($ticket_author_id);

    if (!$ticket_author_id) {
        error_log("User ID not found for ticket ID: $ticketId");
        return; // Exit if user ID is not found
    }
    $ticket_author_info = get_userdata($ticket_author_id);
    if (!is_object($ticket_author_info)) {
        error_log("User data not found for user ID: $ticket_author_id");
        return; // Exit if user data is not available
    }
    $emailToStudent = $ticket_author_info->user_email;
    $emailToPlacementTeam = get_option('placement_custom_email');
    $subjectToStudent = "Updates to Your Profile";
    $subjectToPlacementTeam = "User Profile Update Notification";
    $messageToStudent = "Hello " . $ticket_author_info->first_name . ",\n\nHere are the updates to your profile:\n\n";
    $messageToPlacementTeam = "The following updates were made to the user profile of " . $ticket_author_info->user_login . ":\n\n";
    error_log('Student Email: ' . $emailToStudent);
    foreach ($updates as $update) {
        $messageToStudent .= $update . "\n";
        $messageToPlacementTeam .= $update . "\n";
    }

    if ($feedback !== '') {
        $messageToStudent .= "\nFeedback: " . $feedback . "\n";
        $messageToPlacementTeam .= "\nFeedback: " . $feedback . "\n";
    }

    $messageToStudent .= "\n\nBest Regards,\nYour Team";
    $messageToPlacementTeam .= "\n\nBest Regards,\nAdmin Team";

    // Send emails
    wp_mail($emailToStudent, $subjectToStudent, $messageToStudent);
    wp_mail($emailToPlacementTeam, $subjectToPlacementTeam, $messageToPlacementTeam);
}

// Responses for ajax handlers
function sendErrorResponse($message)
{
    wp_send_json_error(['message' => $message]);
}
function sendSuccessResponse($message, $noChange = false, $fileUrl = '')
{
    $response = [
        'message' => $message,
        'no_change' => $noChange,
    ];
    if ($fileUrl) {
        $response['file_url'] = $fileUrl;
    }
    wp_send_json_success($response);
}
