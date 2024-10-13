<?php
/**
 * Handles course operations in LearnDash, including creating, listing, and deleting courses and groups.
 */


/**
 * Creates a LearnDash course and associated group if they do not already exist, logging the process.
 * @param array $course Contains 'generationName' and 'startDate' to form the course name.
 * @return void
 */
function create_ld_course($course)
{
    $course_gen_name = "קורס " . $course['generationName'] . " " . $course['startDate'];
    $existing_course_id = post_exists($course_gen_name, '', '', 'sfwd-courses');
    if (!$existing_course_id) {
        $course_id = insert_new_learndash_post($course_gen_name, 'sfwd-courses');
        if ($course_id) {
            update_post_meta($course_id, '_ld_course_price_type', 'open');
            error_log('Created new course: ' . $course_gen_name);

            create_ld_group($course_gen_name, $course_id);
            error_log("Course and group creation process completed for " . $course_gen_name);
        } else {
            error_log('Failed to create course: ' . $course_gen_name);
            return;
        }
    } else {
        $course_id = $existing_course_id;
        error_log('Course already exists: ' . $course_gen_name);
    }
}

/**
 * Creates a LearnDash group for a given course.
 * @param string $course_gen_name The generated name of the course.
 * @param int $course_id The ID of the course to which the group is linked.
 * @return void
 */
function create_ld_group($course_gen_name, $course_id)
{
    error_log('Starting creation of new group for course id: ' . $course_gen_name . " - ID: " . $course_id);
    $group_name = str_replace("קורס", "מחזור לימוד קורס", $course_gen_name);
    $existing_group_id = post_exists($group_name, '', '', 'groups');
    if (!$existing_group_id) {
        $group_post_content = 'Group for ' . $course_gen_name;
        $group_id = insert_new_learndash_post($group_name, 'groups', $group_post_content);
        if ($group_id) {
            error_log('Created new group: ' . $group_name);
            // Link the course to the group
            learndash_set_group_enrolled_courses($group_id, array($course_id), true);
            error_log('Linked course to group: ' . $group_name);
        } else {
            error_log('Failed to create group for course: ' . $course_gen_name);
            return;
        }
    } else {
        $group_id = $existing_group_id;
        error_log('Group already exists: ' . $group_name);
    }
}

/**
 * Inserts a new LearnDash post, sanitizing input and setting post status to 'publish', with error logging.
 * @param string $title The title of the post.
 * @param string $type The type of post (e.g., 'sfwd-courses', 'groups').
 * @param string $content Optional content of the post.
 * @return mixed Returns the post ID on success, or false on failure.
 */
function insert_new_learndash_post($title, $type, $content = '')
{
    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($title),
        'post_content' => sanitize_textarea_field($content),
        'post_status'  => 'publish',
        'post_author'  => 1,
        'post_type'    => $type
    ]);

    if (is_wp_error($post_id)) {
        error_log('Failed to insert post: ' . $post_id->get_error_message());
        return false;
    }

    return $post_id;
}

/**
 * Deletes a specified LearnDash course and its associated group, logging each step.
 * @param string $course_name The name of the course to delete.
 * @return void
 */
function delete_course_and_group($course_name) {

    error_log("course: " . $course_name);
    // Step 1: Delete the Course
    $course_id = post_exists($course_name, '', '', 'sfwd-courses');
    if ($course_id) {
        wp_delete_post($course_id, true); // true to force delete
        error_log("Deleted course: " . $course_name);
    } else {
        error_log("Failed to find or delete course: " . $course_name);
    }

    // Step 2: Delete the Group
    $group_name = str_replace("קורס", "מחזור לימוד קורס", $course_name);
    $group_id = post_exists($group_name, '', '', 'groups');
    if ($group_id) {
        wp_delete_post($group_id, true); // true to force delete
        error_log("Deleted group: " . $group_name);
    } else {
        error_log("Failed to find or delete group: " . $group_name);
    }
}

// /**
//  * Lists courses filtered by a given name and displays them in an HTML structure.
//  * @param string $course_name The partial or full name of the course to filter by.
//  * @return void Outputs HTML content directly.
//  */
// function list_courses($course_name)
// {
//     $args = array(
//         'post_type' => 'sfwd-courses',
//         's' => $course_name,
//         'posts_per_page' => -1
//     );
//     $query = new WP_Query($args);

//     if ($query->have_posts()) {
//         echo '<div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">';
//         echo '<div class="p-4 border-b border-gray-200">';
//         echo '<h3 class="text-lg font-semibold text-gray-700">Results:</h3>';
//         echo '</div>';
//         echo '<ul class="divide-y divide-gray-200 overflow-auto" style="max-height:400px">';
//         while ($query->have_posts()) {
//             $query->the_post();
//             $course_id = get_the_ID();
//             echo '<li class="flex justify-between items-center p-4 hover:bg-gray-50">';
//             echo '<span class="text-gray-600">' . get_the_title() . '</span>';
//             echo '<div>';
//             echo '<button onclick="showLessons(' . $course_id . ')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">View Lessons</button>';
//             echo '<form method="post" class="inline">';
//             echo '<input type="hidden" name="course_id_to_delete" value="' . $course_id . '">';
//             echo '<button type="submit" name="delete_course" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">Delete Course and Group</button>';
//             echo '</form>';
//             echo '</div>';
//             echo '</li>';
//         }
//         echo '</ul>';
//         echo '</div>';
//     } else {
//         echo '<p class="mt-4 text-gray-600">No courses found with that name.</p>';
//     }

//     wp_reset_postdata();
// }
add_action('wp_ajax_relist_courses', 'relist_courses');
function relist_courses() {
    if (!empty($_POST['course_name'])) {
        $course_name = sanitize_text_field($_POST['course_name']);
        list_courses($course_name);
    }
    wp_die(); // Ensures a proper AJAX response
}

function list_courses($course_name) {
    $args = [
        'post_type' => 'sfwd-courses',
        's' => $course_name,
        'posts_per_page' => -1
    ];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        echo '<div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">';
        echo '<div class="p-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-700">Results:</h3></div>';
        echo '<ul class="divide-y divide-gray-200 overflow-auto" style="max-height:400px">';
        while ($query->have_posts()) {
            $query->the_post();
            $course_id = get_the_ID();
            echo '<li class="flex justify-between items-center p-4 hover:bg-gray-50">';
            echo '<span class="text-gray-600">' . get_the_title() . '</span>';
            echo '<div>';
            echo '<button onclick="showLessons(' . $course_id . ')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">View Lessons</button>';
            echo '<form method="post" class="inline">';
            echo '<input type="hidden" name="course_name_to_delete" value="' .  get_the_title() . '">';
            echo '<button type="submit" name="delete_course" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">Delete Course and Group</button>';
            echo '<button onclick="cleanLessons(' . $course_id . ')" class="bg-red-900 hover:bg-red-800 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">Delete Lessons</button>';
            echo '</form>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<p class="mt-4 text-gray-600">No courses found with that name.</p>';
    }
    wp_reset_postdata();
}

add_action('wp_ajax_list_recent_courses', 'list_recent_courses');
function list_recent_courses() {
    $args = array(
        'post_type' => 'sfwd-courses',
        'posts_per_page' => 20, // Number of recent courses to fetch
        'orderby' => 'date', // Sort by date
        'order' => 'DESC' // Newest first
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">';
        echo '<div class="p-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-700">Recently Created Courses:</h3></div>';
        echo '<ul class="divide-y divide-gray-200 overflow-auto" style="max-height:400px">';
        while ($query->have_posts()) {
            $query->the_post();
            $course_id = get_the_ID();
            echo '<li class="flex justify-between items-center p-4 hover:bg-gray-50">';
            echo '<span class="text-gray-600">' . get_the_title() . '</span>';
            echo '<div>';
            echo '<button onclick="showLessons(' . $course_id . ')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">View Lessons</button>';
            echo '<form method="post" class="inline">';
            echo '<input type="hidden" name="course_name_to_delete" value="' . get_the_title() . '">';
            echo '<button type="submit" name="delete_course" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">Delete Course and Group</button>';
            echo '<button onclick="cleanLessons(' . $course_id . ')" class="bg-red-900 hover:bg-red-800 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">Delete Lessons</button>';

            echo '</form>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<p class="mt-4 text-gray-600">No recent courses found.</p>';
    }

    wp_reset_postdata();
}

// Function to fetch and display courses
function get_courses_dropdown() {
    $args = [
        'post_type'      => 'sfwd-courses', // Ensure this matches your course post type
        'posts_per_page' => -1,             // Retrieve all courses
        'post_status'    => 'publish'       // Only fetch published courses
    ];

    $courses = new WP_Query($args);
    $options = '';

    if ($courses->have_posts()) {
        while ($courses->have_posts()) {
            $courses->the_post();
            $course_id = get_the_ID();
            $course_title = get_the_title();
            $options .= "<option value='{$course_id}'>{$course_title}</option>";
        }
        wp_reset_postdata(); // Reset the global post object so that the rest of the page works correctly
    }

    return $options;
}
function display_course_dropdown() {
    $args = array(
        'post_type'      => 'sfwd-courses',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    );
    $courses = get_posts($args);
    $html = '<select name="course_id" class="bg-gray-200 border-2 rounded w-full py-2 px-4 text-gray-700 leading-tight">';
    foreach ($courses as $course) {
        $html .= '<option value="' . esc_attr($course->ID) . '">' . esc_html($course->post_title) . '</option>';
    }
    $html .= '</select>';
    return $html;
}
