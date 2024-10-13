<?php
/*
Plugin Name: Vimeo Lesson Sync
Description: Sync Vimeo videos with LearnDash lessons.
Version: 1.0
*/

// Admin menu to enter Vimeo directory ID and sync hour
add_action('admin_menu', function () {
    add_menu_page('Vimeo Lesson Sync', 'Vimeo Lesson Sync', 'manage_options', 'vimeo-lesson-sync', 'vimeo_lesson_sync_page');
});

// Admin page to enter Vimeo directory ID and sync hour
function vimeo_lesson_sync_page()
{
    if (isset($_POST['vimeo_directory_id'])) {
        update_option('vimeo_directory_id', sanitize_text_field($_POST['vimeo_directory_id']));
    }

    // Call the function directly 
    create_lessons_from_vimeo_folder();

    $directoryId = get_option('vimeo_directory_id', '');
    $syncHour = get_option('vimeo_sync_hour', 2);
?>
    <div class="wrap">
        <h1>Vimeo Lesson Sync</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Vimeo Directory ID</th>
                    <td><input type="text" name="vimeo_directory_id" value="<?php echo esc_attr($directoryId); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sync Hour (24-hour format)</th>
                    <td><input type="number" name="vimeo_sync_hour" value="<?php echo esc_attr($syncHour); ?>" min="0" max="23" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function fetch_vimeo_folder_name($directoryId, $accessToken)
{
    $folderUrl = "https://api.vimeo.com/me/folders/$directoryId";
    $httpFolderOptions = array(
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json'
        )
    );
    $response = wp_remote_get($folderUrl, $httpFolderOptions);
    // change - created check_vimeo_response
    if (check_vimeo_response($response, 'folder')) {
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $folderData = json_decode($body, true);

    if (!isset($folderData['name'])) {
        error_log('Error: Folder name not found.');
        return;
    }

    $folderName = $folderData['name'];
    error_log('Vimeo Folder Name: ' . $folderName);
    return $folderName;
}
function fetch_vimeo_videos($directoryId, $accessToken)
{
    $videosUrlTemplate = "https://api.vimeo.com/me/projects/$directoryId/items?filter=video&page=";
    $allVideoDetails = [];
    $current_page = 1;
    $httpVideoOptions = array(
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/vnd.vimeo.*+json;version=3.4'
        )
    );
    do {
        $videosUrl = $videosUrlTemplate . $current_page . "&direction=asc"; // Fetch last added videos first
        $response = wp_remote_get($videosUrl, $httpVideoOptions);    //change - created httpOptions for the videos
        // change - created check_vimeo_response
        if (check_vimeo_response($response, 'video')) {
            break;
        }

        $body = wp_remote_retrieve_body($response);
        $videoData = json_decode($body, true);
        if (empty($videoData['data'])) {
            break; // Exit the loop if no more videos are found
        }

        $videoDetails = array_map(function ($video) {
            return [
                'name' => $video['video']['name'],
                'player_embed_url' => $video['video']['player_embed_url']
            ];
        }, $videoData['data']);

        $allVideoDetails = array_merge($allVideoDetails, $videoDetails);
        $current_page++;
    } while (!empty($videoData['data'])); // Continue fetching while there are videos

    return $allVideoDetails;
}

function check_vimeo_response($response, $vimeo_item)
{
    if (is_wp_error($response)) {
        error_log('Error fetching Vimeo ' . $vimeo_item . ': ' . $response->get_error_message());
        return null; // Return null to indicate failure
    }
}

// Function to create lessons from Vimeo folder
function create_lessons_from_vimeo_folder()
{
    $directoryId = get_option('vimeo_directory_id');
    if (empty($directoryId)) {
        error_log('Error: Vimeo directory ID not set.');
        return;
    }
    $accessToken = VIMEO_ACCESS_TOKEN;

    $folderName = fetch_vimeo_folder_name($directoryId, $accessToken);
    $allVideoDetails = fetch_vimeo_videos($directoryId, $accessToken);

    if (empty($allVideoDetails)) {
        error_log('No videos found or error in fetching videos.');
        return;
    }
    // change - encpsualted getting the course id in a function
    $course_id = get_course_id_from_folder($folderName);
    if (!$course_id) {
        return;     //Stop if something goes wrong, well check logs to see what specifc
    }
    foreach ($allVideoDetails as $video) {
        process_video_for_lesson_creation_old($video, $course_id);
    }
}

function process_video_for_lesson_creation_old($video, $course_id)
{
    // change - made the condition into a function 
    if (validate_video_fields($video['name'],$video['player_embed_url'])) return;
    
    try {
        $lesson_exist = check_for_duplicate_lesson($course_id, $video['name']);
        if (!$lesson_exist) {
            $lesson_post = array(
                'post_title'    => sanitize_text_field($video['name']),
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1,  // Consider making this configurable
                'post_type'     => 'sfwd-lessons'
            );

            $lesson_id = wp_insert_post($lesson_post);

            if ($lesson_id) {
                $sfwd_lessons_meta = get_lesson_meta_config($video['player_embed_url'], $course_id);
                update_post_meta($lesson_id, '_sfwd-lessons', $sfwd_lessons_meta);
                learndash_update_setting($lesson_id, 'course', $course_id);
                error_log('Created New lesson: ' . $video['name'] . ' (ID: ' . $lesson_id . ')');
            } else {
                error_log('Failed to create lesson: ' . $video['name']);
            }
        }
    } catch (Exception $e) {
        error_log('Error in lesson creation process: ' . $e->getMessage());
    }
}

function validate_video_fields($videoName,$videoEmbedUrl){
    if (empty($videoName) || empty($videoEmbedUrl)) {
        error_log("Invalid video data provided.\n" . $videoName . " + " . $videoEmbedUrl);
        return true;
    }
}

// function check_for_duplicate_lesson($course_id, $lesson_name)
// {
//     $existing_lessons = learndash_get_course_lessons_list($course_id);
//     if (!empty($existing_lessons)) {
//         foreach ($existing_lessons as $lesson) {
//             if (isset($lesson['post']) && $lesson['post']->post_title === $lesson_name) {
//                 error_log('Duplicate lesson found, skipping: ' . $lesson_name);
//                 return true; // Duplicate found
//             }
//         }
//     } else {
//         error_log('No existing lessons found for course ID: ' . $course_id);
//     }
//     return false; // No duplicate
// }

// Version 1 - working for only live courses
// function extract_and_format_course_info($folderName)
// {
//     // Extract course path and date from folder name(at the start or at the end)
//     preg_match('/(AI|FULL\s?STACK|CYBER|QA|DIGITAL MARKETING DATA).*?(\d{1,2}[\/\\.\\-]\d{1,2}[\/\\.\\-]\d{2,4})/i', $folderName, $matches);
//     error_log('Preg Match Results: ' . print_r($matches, true)); // Log the matches

//     if (empty($matches)) {
//         error_log('Error: Course path or date not found in folder name.');
//         return null;
//     }

//     $coursePath = strtoupper($matches[1]) . ' LIVE';
//     $dateParts = preg_split('/[\/\\.\\-]/', $matches[2]);

//     if (count($dateParts) !== 3) {
//         error_log('Error: Invalid date format.');
//         return null;
//     }

//     // Add leading zeros to single-digit date components
//     $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
//     $month = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
//     $year = str_pad($dateParts[2], 2, '0', STR_PAD_LEFT);

//     // Use only the last two digits of the year
//     $year = substr($year, -2);

//     $courseDate = "$day/$month/$year";

//     // Log the extracted path and formatted date
//     error_log('Formatted Course Date: ' . $courseDate);
//     error_log('Extracted Course Path: ' . $coursePath);

//     return array(
//         'coursePath' => $coursePath,
//         'courseDate' => $courseDate,
//         'courseTitle' => "קורס $coursePath $courseDate"
//     );
// }

function extract_and_format_course_info($folderName)
{
    // Pattern for Live Users Courses (existing)
    $livePattern = '/(AI|FULL\s?STACK|CYBER|QA|DIGITAL MARKETING DATA).*?(\d{1,2}[\/\\.\\-]\d{1,2}[\/\\.\\-]\d{2,4})/i';
    
    // Pattern for DIGITAL [PATH] YEAR or DIGITAL [PATH] מתעדכן (new)
    $digitalPattern = '/DIGITAL\s+(AI|FULL\s?STACK|CYBER|QA|DATA)\s+(\d{4})?|DIGITAL\s+(AI|FULL\s?STACK|CYBER|QA|DATA)\s+מתעדכן/i';

    // Check for Live Users Courses pattern
    if (preg_match($livePattern, $folderName, $matches)) {
        error_log('Preg Match Results (Live): ' . print_r($matches, true)); // Log the matches

        $coursePath = strtoupper($matches[1]) . ' LIVE';
        $dateParts = preg_split('/[\/\\.\\-]/', $matches[2]);

        if (count($dateParts) !== 3) {
            error_log('Error: Invalid date format.');
            return null;
        }

        // Add leading zeros to single-digit date components
        $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
        $month = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
        $year = str_pad($dateParts[2], 2, '0', STR_PAD_LEFT);

        // Use only the last two digits of the year
        $year = substr($year, -2);

        $courseDate = "$day/$month/$year";
        $courseTitle = "קורס $coursePath $courseDate";

        return array(
            'coursePath' => $coursePath,
            'courseDate' => $courseDate,
            'courseTitle' => $courseTitle
        );
    }
    // Check for DIGITAL [PATH] YEAR or DIGITAL [PATH] מתעדכן pattern
    elseif (preg_match($digitalPattern, $folderName, $matches)) {
        error_log('Preg Match Results (Digital): ' . print_r($matches, true)); // Log the matches

        $coursePath = strtoupper($matches[1] ?? $matches[3]);

        // If a year is found, format the course title accordingly
        if (!empty($coursePath) && !empty($matches[2])) {
            $courseYear = $matches[2];
            $courseTitle = "קורס DIGITAL $coursePath $courseYear";
        } 
        // If it's מתעדכן, format the title with the correct path
        elseif (!empty($coursePath)) {
            $courseTitle = "קורס DIGITAL $coursePath מתעדכן";
        } else {
            error_log('Error: Course path not found in folder name.');
            return null;
        }

        return array(
            'coursePath' => $coursePath,
            'courseYear' => $courseYear ?? null,
            'courseTitle' => $courseTitle
        );
    }
    else {
        error_log('Error: Course pattern not found in folder name.');
        return null;
    }
}



function get_course_id_from_folder($folderName)
{
    // Include the file that contains the post_exists function
    if (!function_exists('post_exists')) {
        require_once(ABSPATH . 'wp-admin/includes/post.php');
    }
    // change - changed the return value for errors to null for a better check and
    $courseInfo = extract_and_format_course_info($folderName);
    if (!$courseInfo) {
        return null;
    }

    $courseTitle = $courseInfo['courseTitle'];

    // Find the course ID based on the course title
    error_log('CourseTitle: ' . $courseTitle);
    $existing_course_id = post_exists($courseTitle, '', '', 'sfwd-courses');
    if (!$existing_course_id) {
        error_log('Error: Course not found for title ' . $courseTitle);
        return null;
    }

    $course_id = $existing_course_id;
    error_log('Course Found: ' . $courseTitle . ' (ID: ' . $course_id . ')');
    return $course_id;
}


function get_lesson_meta_config($video_url, $course_id)
{
    return [
        'sfwd-lessons_lesson_materials_enabled' => '',
        'sfwd-lessons_lesson_materials' => '',
        'sfwd-lessons_lesson_video_enabled' => 'on',
        'sfwd-lessons_lesson_video_url' => $video_url,
        'sfwd-lessons_lesson_video_shown' => 'BEFORE',
        'sfwd-lessons_lesson_video_auto_start' => 'on',
        'sfwd-lessons_lesson_video_show_controls' => 'on',
        'sfwd-lessons_lesson_video_focus_pause' => 'on',
        'sfwd-lessons_lesson_video_track_time' => 'on',
        'sfwd-lessons_lesson_video_auto_complete' => '',
        'sfwd-lessons_lesson_video_auto_complete_delay' => 0,
        'sfwd-lessons_lesson_video_show_complete_button' => '',
        'sfwd-lessons_lesson_assignment_upload' => '',
        'sfwd-lessons_assignment_upload_limit_extensions' => '',
        'sfwd-lessons_assignment_upload_limit_size' => '',
        'sfwd-lessons_lesson_assignment_points_enabled' => '',
        'sfwd-lessons_lesson_assignment_points_amount' => '',
        'sfwd-lessons_assignment_upload_limit_count' => '',
        'sfwd-lessons_lesson_assignment_deletion_enabled' => '',
        'sfwd-lessons_auto_approve_assignment' => '',
        'sfwd-lessons_forced_lesson_time_enabled' => '',
        'sfwd-lessons_forced_lesson_time' => '',
        'sfwd-lessons_lesson_video_hide_complete_button' => 'on',
        'sfwd-lessons_lesson_schedule' => '',
        'sfwd-lessons_course' => $course_id,
        'sfwd-lessons_sample_lesson' => '',
        'sfwd-lessons_visible_after' => '',
        'sfwd-lessons_visible_after_specific_date' => '',
        'sfwd-lessons_external' => '',
        'sfwd-lessons_external_type' => 'virtual',
        'sfwd-lessons_external_require_attendance' => '',
    ];
}
?>