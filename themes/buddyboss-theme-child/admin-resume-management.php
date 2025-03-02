<?php
// admin-resume-management.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// // Check if the user has the 'manage_options' capability
// if (!current_user_can('manage_options')) {
//     wp_die('You do not have sufficient permissions to access this page.');
// }

// Update email address if the form has been submitted
if (isset($_POST['custom_email']) && is_email($_POST['custom_email'])) {
    update_option('placement_custom_email', sanitize_email($_POST['custom_email']));
    echo '<div class="notice notice-success"><p>Email updated successfully.</p></div>';
}

$current_email = get_option('placement_custom_email', 'jobs@ecomschool.co.il'); // Fetch the current email or use a default

function fetch_latest_users()
{
    $args = [
        'number' => 50,
        'orderby' => 'meta_value',
        'meta_key' => 'resume_last_updated',
        'order' => 'ASC',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'resume_file',
                'compare' => 'EXISTS'
            ]
        ]
    ];
    $user_query = new WP_User_Query($args);
    return $user_query->get_results();
}
// Get all users
$users = fetch_latest_users();


function render_search_form()
{
    // This form now only serves to accept input for AJAX-based search
    return '
    <form method="get" class="search-box">
        <label for="user-search-input">Search Users:</label>
        <input type="search" id="user-search-input" placeholder="Type a username or email...">
        <small>Enter at least 3 characters to search</small>
    </form>';
}

function render_email_management($current_email)
{
    if (current_user_can('manage_options')) : ?>
        <div class="emailManagement">
            <div id="emailDisplay">
                <span id="emailText"><strong>Sent Resumes Email Address: </strong><?php echo esc_html($current_email); ?></span>
                <button id="editEmailBtn" class="button">Edit</button>
            </div>
            <div id="emailEdit" class="hidden">
                <input type="email" name="emailInput" id="emailInput" value="<?php echo esc_attr($current_email); ?>">
                <button id="saveEmailBtn" class="button">Save</button>
            </div>
            <!-- Search Form -->
            <?php echo render_search_form(); ?>
        </div>

    <?php endif;
}

function render_table_body($users)
{
    ob_start();
    ?>
    <tbody id="the-list" data-wp-lists="list:user">
        <?php if (!empty($users)) : ?>
            <?php foreach ($users as $user) : ?>
                <?php echo render_user_row($user); ?>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="5" style="text-align:center;">No users found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
<?php
    return ob_get_clean();
}

function render_user_row($user)
{
    ob_start();
?>
    <tr id="user-<?php echo $user->ID; ?>">
        <?php
        echo render_username_cell2($user);
        echo render_email_cell($user);
        echo render_group_cell($user);
        echo render_job_status_cell($user);
        echo render_resume_cell($user);
        echo render_placement_notes_cell($user);
        echo render_last_updated_cell($user);
        echo render_actions_cell($user);
        ?>
    </tr>
<?php
    return ob_get_clean();
}
function render_username_cell2($user) {
    ob_start();

    // BuddyPress profile URL

    // Admin profile edit URL
    $admin_profile_url = 'https://dev.digitalschool.co.il/wp-admin/user-edit.php?user_id=' . $user->ID;

    // Check if the current user can manage options (admin or manager), adjust this capability as needed
    if (current_user_can('edit_users')) {
        echo '<td class="username column-username" data-colname="Username">';
        // Admin URL for editing user profiles
        echo '<a target="_blank" href="' . esc_url($admin_profile_url) . '">' . esc_html($user->display_name) . '</a>';
        // BuddyPress profile link
        echo '</td>';
    } else {
        // Just display the name if the current user doesn't have the capability to edit users
        echo '<td class="username column-username" data-colname="Username">' . esc_html($user->display_name) . '</td>';
    }

    return ob_get_clean();
}

function render_username_cell($user)
{
    ob_start();
?>
    <td class="username column-username" data-colname="Username">
        <?php echo esc_html($user->display_name); ?>
    </td>
<?php
    return ob_get_clean();
}

function render_email_cell($user)
{
    ob_start();
?>
    <td class="email column-email" data-colname="Email">
        <?php echo $user->user_email; ?>
    </td>
<?php
    return ob_get_clean();
}

function render_group_cell($user)
{
    // Fetch user groups
    $user_groups = learndash_get_users_group_ids($user->ID);

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

?>
    <td class="group column-group" data-colname="Group">
        <?php echo $group_names_string ?: 'No Relevant Group'; // Display group names or a default message 
        ?>
    </td>
<?php
}

function render_resume_cell($user)
{
    $resume = get_field('resume_file', 'user_' . $user->ID) ?: array('url' => '');
    $resume_url = esc_url($resume['url']);
    $https_resume_url = str_replace('http://', 'https://', $resume_url);
    $resume_file_name = basename($resume_url);
    ob_start();
?>
    <td class="resume column-resume" data-colname="Resume">
        <?php if ($resume) : ?>
            <small><?php echo "Updated: " . get_last_updated_at($user->ID, 'resume_last_updated'); ?></small>
            <br>
            <a href="<?php echo $https_resume_url; ?>" download="<?php echo $resume_file_name; ?>"><?php echo $resume_file_name; ?></a>
        <?php else : ?>
            <span>No Resume Uploaded</span>
        <?php endif; ?>
    </td>
<?php
    return ob_get_clean();
}

function render_job_status_cell($user)
{
    $job_status = get_field('job_status', 'user_' . $user->ID) ?: array('label' => 'ממתין להגשת קורות חיים');
    ob_start();
?>
    <td class="job_status column-job_status" data-colname="Job Status">
        <small><?php echo "Updated: " . get_last_updated_at($user->ID, 'job_status_last_updated'); ?></small>
        <br>
        <span class="status-text" data-user-id="<?php echo $user->ID; ?>">
            <?php echo esc_html($job_status['label']); ?>
        </span>
        <select class="status-select hidden" data-user-id="<?php echo $user->ID; ?>">
            <option value="review">Review</option>
            <option value="published">Published</option>
            <option value="interview">Interview</option>
            <option value="hired">Hired</option>
        </select>
    </td>
<?php
    return ob_get_clean();
}

function render_placement_notes_cell($user)
{
    $userID = $user->ID;
    $notes_file = get_field('placement_notes', 'user_' . $userID) ?: '';
    $notes_file_url = esc_url($notes_file);
    $notes_file_name = basename($notes_file_url);
    ob_start();
?>
    <td class="placement_notes column-placement_notes" data-colname="Placement Notes">
        <div id="notesDisplay-<?php echo $userID; ?>" class="notes-display">
            <?php if ($notes_file) : ?>
                <small><?php echo "Updated: " . get_last_updated_at($userID, 'placement_notes_last_updated'); ?></small>
                <br>
                <a href="<?php echo $notes_file_url; ?>" download="<?php echo $notes_file_name; ?>"><?php echo $notes_file_name; ?></a>
            <?php else : ?>
                <span>No Notes Uploaded</span>
            <?php endif; ?>
        </div>
        <div id="notesEdit-<?php echo $userID; ?>" class="notes-edit hidden">
            <input type="file" class="placement-notes-input" data-user-id="<?php echo $userID; ?>">
        </div>
    </td>
<?php
    return ob_get_clean();
}

function render_last_updated_cell($user)
{
    ob_start();
    $last_updated = get_most_recent_update($user->ID);
?>
    <td class="last_updated column-last_updated" data-colname="Last Updated">
        <?php echo esc_html($last_updated); ?>
    </td>
<?php
    return ob_get_clean();
}
function get_most_recent_update($userId)
{

    $resume_updated = get_user_meta($userId, 'resume_last_updated', true);
    $job_status_updated = get_user_meta($userId, 'job_status_last_updated', true);
    $placement_notes_updated = get_user_meta($userId, 'placement_notes_last_updated', true);

    // Convert all timestamps to Unix time for comparison
    $times = [
        strtotime($resume_updated),
        strtotime($job_status_updated),
        strtotime($placement_notes_updated)
    ];

    // Remove any false or null values which represent missing data
    $times = array_filter($times);

    // Get the latest time
    if (!empty($times)) {
        $most_recent_time = max($times);
        return date_i18n('Y-m-d H:i:s', $most_recent_time); // Format as per WordPress settings
    }

    return 'No updates'; // Default message if no timestamps are available
}


function render_actions_cell($user)
{
    ob_start();
?>
    <td class="actions column-actions" data-colname="Actions">
        <button class="button edit-status" data-user-id="<?php echo $user->ID; ?>">Edit Status</button>
        <button class="button save-status hidden" data-user-id="<?php echo $user->ID; ?>">Save</button>
    </td>
<?php
    return ob_get_clean();
}

function render_filters()
{
    ob_start();
?>
    <div class="filter-container" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <select id="job-status-filter">
            <option value="">All Statuses</option>
            <option value="review">Review</option>
            <option value="published">Published</option>
            <option value="interview">Interview</option>
            <option value="hired">Hired</option>
        </select>

        <select id="placement-notes-filter">
            <option value="">All Placement Notes</option>
            <option value="exists">Notes Exist</option>
            <option value="not_exists">No Notes</option>
        </select>

        <div class="date-filter" style="display: flex; align-items: center; gap: 10px;">
            <select id="date-filter-type">
                <option value="">Select Date Filter</option>
                <option value="specific-date">Specific Date</option>
                <option value="date-range">Date Range</option>
            </select>
            <input type="date" id="specific-date-input" style="display:none;">
            <input type="date" id="start-date-input" style="display:none;" placeholder="Start Date">
            <input type="date" id="end-date-input" style="display:none;" placeholder="End Date">
        </div>
    </div>
<?php
    return ob_get_clean();
}

function render_page_content($current_email, $users)
{

    echo '<div class="wrap">
    <h1 class="wp-heading-inline">Resume Management</h1>'
        .  render_email_management($current_email) .
        '<div id="usersTableContainer">'
        . render_filters() . '
            <table class="wp-list-table widefat fixed striped users">
                <thead>
                    <tr>
                        <th scope="col" id="username" class="manage-column column-username column-primary">Username</th>
                        <th scope="col" id="email" class="manage-column column-email">Email</th>
                        <th scope="col" id="group" class="manage-column column-group">Group</th>
                        <th scope="col" id="job_status" class="manage-column column-job_status">Job Status</th>
                        <th scope="col" id="resume" class="manage-column column-resume">Resume</th>
                        <th scope="col" id="placement_notes" class="manage-column column-placement_notes">Placement Notes</th>
                        <th scope="col" id="last_updated" class="manage-column column-last_updated">Last Updated</th>
                        <th scope="col" id="actions" class="manage-column column-actions">Actions</th>
                    </tr>
                </thead>'
        . render_table_body($users) .
        '</table>
    </div>';
}
render_page_content($current_email, $users);

echo "NEW PAGE1";
?>