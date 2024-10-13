<?php

require_once('/home/devdigitalschool/public_html/wp-content/plugins/vimeo-lesson-sync/vimeo-lesson-sync.php');
define('ACCESS_TOKEN', VIMEO_ACCESS_TOKEN);


function update_course_from_vimeo() {
    error_log('POST Submission: ' . print_r($_POST, true)); // Log the entire POST array to debug

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_lessons'])) {
        $vimeo_directory_id = sanitize_text_field($_POST['vimeo_directory_id']);
        $course_id = sanitize_text_field($_POST['course_id']);

        if (empty($vimeo_directory_id) || empty($course_id)) {
            echo '<p>Error: Missing necessary data for updating lessons. Please provide both Vimeo directory ID and course ID.</p>';
            return;
        }

    
        update_option('vimeo_directory_id', $vimeo_directory_id);

        $all_video_details = fetch_vimeo_videos($vimeo_directory_id, ACCESS_TOKEN);
        $new_lessons_count = 0; 

        if (!empty($all_video_details )) {
            foreach ($all_video_details  as $video) {
                $lesson_added = process_video_for_lesson_creation($video, $course_id);
                if ($lesson_added) {
                    $new_lessons_count ++;
                }
            }
            echo '<p>Lessons updated successfully. New lessons added: ' . $new_lessons_count  . '</p>';
        } else {
            echo '<p>No videos found in the specified Vimeo folder.</p>';
        }
    }
}

function fetch_vimeo_folders() {
    $folders = []; 
    $page = 1; // Start from the first page
    $per_page = 50;

    do {
        $api_folders_url = 'https://api.vimeo.com/me/projects?page=' . $page . '&per_page=' . $per_page; 
        $response = wp_remote_get($api_folders_url, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . ACCESS_TOKEN,
                'Content-Type' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('Error retrieving Vimeo folders: ' . $response->get_error_message());
            break; 
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $folders = array_merge($folders, $data['data'] ?? []); // Append fetched folders to array

        // Check if there's more data to fetch
        $total = $data['total'] ?? 0;
        $fetched_items = count($data['data'] ?? []);
        $page++; // Increment page number for the next request
    } while ($fetched_items >= $per_page && ($page - 1) * $per_page < $total);

    return $folders;
}


function display_vimeo_folders_dropdown() {
    $folders = fetch_vimeo_folders();
    $html = '<select name="vimeo_directory_id" class="bg-gray-200 border-2 rounded w-full py-2 px-4 text-gray-700">';
    foreach ($folders as $folder) {
        // Extract the folder ID from the URI
        $folder_id = basename($folder['uri']);
        $html .= '<option value="' . esc_attr($folder_id) . '">' . esc_html($folder['name']) . '</option>';
        }
    $html .= '</select>';
    return $html;
}


function process_video_for_lesson_creation($video, $course_id) {
    $video_embed_url = $video['player_embed_url'];
    $video_name = $video['name'];
    if (validate_video_fields($video_name,$video_embed_url)) return false;

    try {
        $lesson_exist = check_for_duplicated_lesson($course_id, $video_embed_url);
    if (!$lesson_exist) {
        $lesson_post = [
            'post_title'    => sanitize_text_field($video_name),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => 1,  // Consider making this configurable
            'post_type'     => 'sfwd-lessons'
        ];

        $lesson_id = wp_insert_post($lesson_post);
        if ($lesson_id) {
            $sfwd_lessons_meta = get_lesson_meta_config($video_embed_url, $course_id);
            update_post_meta($lesson_id, '_sfwd-lessons', $sfwd_lessons_meta);
            learndash_update_setting($lesson_id, 'course', $course_id);
            error_log('Created new lesson: ' . $video_name . ' (ID: ' . $lesson_id . ')');
            return true;

        } else {
            error_log('Failed to create lesson: ' . $video_name);
            return false;  // Return false here if the lesson could not be created

        }
    }
    } catch (Exception $e) {
        error_log('Error in lesson creation process: ' . $e->getMessage());
        return false;  // Return false here if an exception occurred

    }
    return false; // Return false if lesson was not added

    
}
function check_for_duplicated_lesson($course_id, $video_url) {
    global $wpdb;
    $video_url_length = strlen($video_url);
    // Prepare the SQL query to fetch posts with matching course_id and video_url
    $query = $wpdb->prepare("
        SELECT pm.post_id, pm.meta_value 
        FROM {$wpdb->postmeta} pm 
        JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
        WHERE pm.meta_key = '_sfwd-lessons' 
        AND p.post_type = 'sfwd-lessons' 
        AND p.post_status = 'publish' 
        AND pm.meta_value LIKE %s 
        AND pm.meta_value LIKE %s
    ", 
    '%"sfwd-lessons_course";i:' . $course_id . ';%',
    '%"sfwd-lessons_lesson_video_url";s:' . $video_url_length . ':"' . $video_url . '"%'
    );

    $matching_posts = $wpdb->get_results($query);

    if (!empty($matching_posts)) {
        foreach ($matching_posts as $post) {
            $lesson_meta_data = maybe_unserialize($post->meta_value);
            if (is_array($lesson_meta_data) && isset($lesson_meta_data['sfwd-lessons_lesson_video_url'])) {
                if ($lesson_meta_data['sfwd-lessons_lesson_video_url'] === $video_url) {
                    // Duplicate URL found
                    error_log("Duplicate found for URL: {$lesson_meta_data['sfwd-lessons_lesson_video_url']} at post ID: {$post->post_id}");
                    return true;
                }
            }
        }
    }

    // No duplicate found
    return false;
}


