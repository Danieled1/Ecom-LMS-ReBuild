<?php 
function resumeUploadedMailTrigger($postId)
{
    // Exit if it's not the user form
    if (strpos($postId, 'user_') === false) return;

    $currentUser = wp_get_current_user();
    $resumeFile = get_field('resume_file', $postId);
    if ($resumeFile && isset($resumeFile['url'])) {
        // Prepare and send email to the placement team and the student
        $placementTeamEmailContent = preparePlacementTeamEmailContent($currentUser, $resumeFile['url']);
        sendEmailNotification($placementTeamEmailContent);

        $studentEmailContent = prepareStudentEmailContent($currentUser, $resumeFile['url']);
        sendEmailNotification($studentEmailContent);
    }
    update_user_meta($currentUser->ID, 'resume_last_updated', current_time('mysql'));
}
add_action('acf/save_post', 'resumeUploadedMailTrigger', 20);

function ticketUploadedMailTrigger($postId)
{
    error_log("Started the ticket Mail Trigger");
    if (wp_is_post_revision($postId) || strpos($postId, 'user_') !== false) {
        return;
    }

    // Make sure this is a 'ticket' post type
    if (get_post_type($postId) !== 'ticket') {
        return;
    }
    $currentUser = wp_get_current_user();
    $sector = get_field('ticket_sector', $postId); // Assuming 'ticket_sector' is the ACF field name
    // error_log("Ticket Sector: " . $sector);
    if ($sector) {
        $teamEmailTicketContent = prepareTeamEmailTicketContent($currentUser, $sector, $postId);
        sendEmailNotification($teamEmailTicketContent);

        $studentEmailTicketContent = prepareStudentEmailTicketContent($currentUser, $sector, $postId);
        sendEmailNotification($studentEmailTicketContent);
    }
    error_log("Here is the User who submitted the ticket: {$currentUser->user_email}\n Here is the post " . get_permalink($postId));
}
add_action('acf/save_post', 'ticketUploadedMailTrigger', 20);

function prepareStudentEmailTicketContent($currentUser, $sector, $postId)
{
    $to = $currentUser->user_email;
    $subject = 'Your Ticket Has Been Successfully Uploaded In ' . $sector['label'];
    $message = "<html><body>";
    $message .= "<p>Hello <strong>{$currentUser->user_login}</strong>,</p>";
    $message .= "<p>We have successfully received your ticket. You can view the ticket progress on this link: </p>";
    $message .= "<p>View the ticket: " . get_permalink($postId) . "</p>";
    $message .= "<p>Thank you, we will keep you updated.</p>";
    $message .= "</body></html>";

    error_log("Ticket Email Crafted To: {$to} | Subject: {$subject}");
    return ['to' => $to, 'subject' => $subject, 'message' => $message, 'headers' => ['Content-Type: text/html; charset=UTF-8']];
}
function prepareTeamEmailTicketContent($currentUser, $sector, $postId)
{
    $sectorEmail = get_option("sector_email_" . strtolower(str_replace(' ', '_', $sector['value'])), '');
    error_log("Sector Email: " . $sectorEmail);
    // Send to sector-specific email if it exists, or a default otherwise
    $to = !empty($sectorEmail) ? $sectorEmail : 'support@ecomschool.co.il';
    $subject = 'New Ticket Submitted in ' . $sector['label'];
    $message = "<html><body>";
    $message .= "<p>A new ticket has been submitted by <strong>" . $currentUser->display_name . "</strong> in the sector: <strong>" . $sector['label'] . "</strong>.</p>";
    $message .= "<p>View the ticket: " . get_permalink($postId) . "</p>";
    $message .= "</body></html>";

    // The headers to set the email content type to HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');
    return ['to' => $to, 'subject' => $subject, 'message' => $message, 'headers' => $headers];
}
function preparePlacementTeamEmailContent($currentUser, $resumeFileUrl)
{
    // Prepare email content of resumes for the placement team
    $to = get_option('placement_custom_email', 'jobs@ecomschool.co.il');
    $subject = 'New Resume Uploaded by ' . $currentUser->user_login;
    $message = "<html><body>";
    $message .= "<p>A new resume has been uploaded by <strong>" . $currentUser->user_login . "</strong>.</p>";
    $message .= "<p>You can view the resume at the following link:</p>";
    $message .= "<p><a href='" . $resumeFileUrl . "'>" . $resumeFileUrl . "</a></p>";
    $message .= "</body></html>";

    error_log("Ticket Email Crafted To: {$to} | Subject: {$subject}");
    return ['to' => $to, 'subject' => $subject, 'message' => $message, 'headers' => ['Content-Type: text/html; charset=UTF-8']];
}
function prepareStudentEmailContent($currentUser, $resumeFileUrl)
{
    // Prepare email content of resume for the student
    $to = $currentUser->user_email;
    $subject = "Your Resume Has Been Successfully Uploaded";
    $message = "<html><body>";
    $message .= "<p>Hello <strong>{$currentUser->user_login}</strong>,</p>";
    $message .= "<p>We have successfully received your resume. You can view the uploaded resume at the following link:</p>";
    $message .= "<p><a href='{$resumeFileUrl}'>View Resume</a></p>";
    $message .= "<p>Thank you for keeping your profile updated.</p>";
    $message .= "</body></html>";

    return ['to' => $to, 'subject' => $subject, 'message' => $message, 'headers' => ['Content-Type: text/html; charset=UTF-8']];
}
function sendEmailNotification($emailContent)
{
    wp_mail($emailContent['to'], $emailContent['subject'], $emailContent['message'], $emailContent['headers']);
}

?>