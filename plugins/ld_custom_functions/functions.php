<?php
/*
Plugin Name: LD Custom Functions2
Description: Custom functions for managing LearnDash courses and groups.
Version: 1.0
Author: Your Name
*/


// Register the menu
add_action('admin_menu', 'ld_custom_functions_menu');
function ld_custom_functions_menu()
{
    add_menu_page(
        'LD Custom Functions',      // Page title
        'LD Custom Functions',      // Menu title
        'manage_options',           // Capability
        'ld_custom_functions',      // Menu slug
        'ld_custom_functions_page'  // Function to display the page
    );
}

function ld_custom_functions_enqueue_styles()
{
    $screen = get_current_screen();
    if ($screen->id === "toplevel_page_ld_custom_functions") {
        wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@^2.0/dist/tailwind.min.css');
        $plugin_url = plugin_dir_url(__FILE__);

        // Enqueue your custom JavaScript file
        wp_enqueue_script('ld-custom-functions-script', $plugin_url . 'assets/js/ld-custom-functions.js', array(), '1.0.0', true);

        wp_localize_script('ld-custom-functions-script', 'adminAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));
    }
}
add_action('admin_enqueue_scripts', 'ld_custom_functions_enqueue_styles');



require_once('course_management.php');
require_once('update_course_by_vimeo_folder_id.php');
// Display the admin page

function ld_custom_functions_page()
{

    ?>
    <div class="wrap">
        <h1 class="text-3xl font-bold underline mb-8">LearnDash Custom Functions - NOT CHECKED FOR EDGE CASES YET!</h1>

        <div class="grid grid-cols-3 gap-8">
            <!-- Left Column for Actions -->
            <div class="col-span-1 space-y-8">

                <!-- Create Course and Group Form -->
                <div class="p-4 border border-gray-300 rounded-lg shadow">
                    <h2 class="text-2xl font-semibold">Create Course and Group</h2>
                    <form action="" method="post" class="space-y-4">
                        <input type="text" name="course_gen_name" placeholder="Enter generation name"
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500">
                        <input type="date" name="course_date"
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500">
                        <button type="submit" name="create_course_group"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create</button>
                    </form>
                </div>

                <!-- Search Courses Form -->
                <div class="p-4 border border-gray-300 rounded-lg shadow">
                    <h2 class="text-2xl font-semibold">Search Courses</h2>
                    <form method="post" class="space-y-4">
                        <input type="text" id="search_input" name="course_name" placeholder="Enter Course Name"
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500">
                        <button type="submit" id="search_button" name="search_course"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Search
                            Courses</button>
                    </form>
                </div>

                <!-- Update Course Lessons from Vimeo Form -->
                <div class="p-4 border border-gray-300 rounded-lg shadow">
                    <h2 class="text-2xl font-semibold">Update Course Lessons from Vimeo</h2>
                    <form method="post" class="space-y-4">
                        <?php echo display_course_dropdown(); // Display the courses dropdown ?>
                        <!-- still working  <input type="text" name="vimeo_directory_id" placeholder="Enter Vimeo Folder ID"
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500">
                            -->
                        <?php echo display_vimeo_folders_dropdown(); // Display the Vimeo folders dropdown ?>

                        <button type="submit" name="update_lessons"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update
                            Lessons</button>
                    </form>
                </div>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_lessons'])) {
                    update_course_from_vimeo(); // Call your function to process the form
                }
                ?>
            </div>

            <!-- Right Column for Results and Messages -->
            <div class="col-span-2 p-4 border border-gray-300 rounded-lg shadow space-y-4">
                <h2 class="text-2xl font-semibold">Activity Log</h2>
                <!-- Dynamic result or message area -->
                <button id="backButton"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mt-4"
                    style="display: none;">Back to Courses</button>

                <div id="results">
                    <?php
                    // Dynamically loaded content via AJAX or PHP post submission
                    if (!isset($_POST['search_course'])) {
                        list_recent_courses();  // Function to list the most recent courses
                    } elseif (!empty(trim($_POST['course_name']))) {
                        $course_name = sanitize_text_field($_POST['course_name']);
                        list_courses($course_name);

                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Process form submissions
    if (isset($_POST['create_course_group'])) {
        $course = [
            'generationName' => sanitize_text_field($_POST['course_gen_name']),
            'startDate' => sanitize_text_field($_POST['course_date'])
        ];
        create_ld_course($course);
    }

    if (isset($_POST['delete_course'])) {
        $course_name = sanitize_text_field($_POST['course_name_to_delete']);
        delete_course_and_group($course_name);

    }
}





function fetch_ld_course_lessons()
{
    $course_id = intval($_POST['course_id']);
    $lessons = learndash_get_course_lessons_list($course_id);
    $html = '';

    if (!empty($lessons)) {
        $html .= '<div class="mt-4 overflow-auto" style="max-height: 400px;">'; // Ensure scroll effect
        $html .= '<ul class="divide-y divide-gray-300">';
        foreach ($lessons as $lesson) {
            $html .= '<li class="p-3 hover:bg-gray-100">' . esc_html($lesson['post']->post_title) . '</li>';
        }
        $html .= '</ul></div>';
    } else {
        $html = '<div class="p-3 text-gray-600">No lessons found for this course.</div>';
    }
    echo json_encode(['html' => $html]);
    wp_die(); // Properly end AJAX call
}
add_action('wp_ajax_fetch_ld_course_lessons', 'fetch_ld_course_lessons');

add_action('wp_ajax_delete_lessons', 'delete_lessons_handler');
function delete_lessons_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized access']);
        wp_die();
    }

    $course_id = intval($_POST['course_id']);

    // We'll retrieve all lessons using pagination
    $query_args = [
        'num'   => 20,   // Number of lessons per page
        'paged' => 1     // Start at the first page
    ];

    $all_lessons = [];

    do {
        $lessons = learndash_get_course_lessons_list($course_id, null, $query_args);

        if (!empty($lessons)) {
            $all_lessons = array_merge($all_lessons, $lessons);
            $query_args['paged']++;
        } else {
            break; // No more lessons
        }
    } while (!empty($lessons));

    // Now we have all lessons in $all_lessons
    // Let's delete them
    foreach ($all_lessons as $lesson) {
        if (isset($lesson['post']) && isset($lesson['post']->ID)) {
            $lesson_id = $lesson['post']->ID;
            error_log('Deleting lesson ID: ' . $lesson_id);
            wp_delete_post($lesson_id, true); // Force deletion (not sending to trash)
        }
    }

    wp_send_json(['success' => true, 'message' => 'All lessons deleted successfully']);
    wp_die();
}

