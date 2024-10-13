<?php 
function create_ticket_post_type()
{
    register_post_type('ticket', [
        'labels' => [
            'name' => __('Ticket'),
            'singular_name' => __('Ticket')
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'custom-fields'],
        'show_in_rest' => true, // Enable the Gutenberg editor
        'menu_icon' => 'dashicons-tickets-alt', // Choose an appropriate dashicon
        'show_ui' => true, // Do not display in the admin menu
    ]);
}
add_action('init', 'create_ticket_post_type');

function create_grades_cpt_and_taxonomy() {
    // Create the 'Grades' Custom Post Type
    register_post_type('grades', [
        'labels' => [
            'name' => __('Grades'),
            'singular_name' => __('Grades')
        ],
        'public' => true,
        'has_archive' => false,
        'supports' => ['title', 'editor', 'custom-fields'],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-awards',
        'show_ui' => true,
    ]);

    // Register a taxonomy for Educational Paths
    register_taxonomy('path', 'grades', [
        'labels' => [
            'name' => __('Paths'),
            'singular_name' => __('Path')
        ],
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'query_var' => true,
    ]);

    // Insert educational paths as terms - do this once, then remove or comment out
    // $paths = [
    //     'Data&Digital'
    // ];

    // foreach ($paths as $path) {
    //     if (!term_exists($path, 'path')) {
    //         wp_insert_term($path, 'path');
    //     }
    // }
}

add_action('init', 'create_grades_cpt_and_taxonomy');

function add_path_sub_terms($path_name, $tests_and_projects) {
    $path_term = term_exists($path_name, 'path'); // Check if the main path term exists

    if (!$path_term) {
        // If the path does not exist, create it
        $path_term = wp_insert_term($path_name, 'path');
    }

    if (!is_wp_error($path_term)) {
        foreach ($tests_and_projects as $name) {
            if (!term_exists($name, 'path')) {
                wp_insert_term($name, 'path', array('parent' => $path_term['term_id']));
            }
        }
    }
}
/**
 *  Adding paths to the grades system
 */
add_action('init', function() {
    // $full_stack_items = ['JavaScript Work', 'JavaScript Exam', 'JavaScript Test', 'JS Tic Tac Toe Project', 'JS Memory Game Project', 'Java Exam', 'Springboot Project', 'Full Stack Final Project'];
    // add_path_sub_terms('Full Stack', $full_stack_items);

    // $qa_items = ['Jira Project: Function Decomposition 1', ' SQL Average Test', 'Practitest Project: Mobile', 'Qase Project: Web', 'Open Final Exam', ' Automation Final Exam'];
    // add_path_sub_terms('QA', $qa_items);

    // $ai_items = ['AI Basics Test', 'Machine Learning Test', 'Neural Networks Test', 'AI Chatbot Project', 'Image Recognition Project', 'AI Data Analysis Project'];
    // add_path_sub_terms('AI', $ai_items);

    // $cyber_items = ['Networks Test', 'Linux Test', 'Python Project', 'Final Project'];
    // add_path_sub_terms('Cyber', $cyber_items);

    // $data_and_digital_items = ['Facebook Project','Instagram Project + Facebook Campaign','Google Project','WordPress Project','Data&Digital Final Project'];
    // add_path_sub_terms('Data&Digital', $data_and_digital_items);

});
function check_taxonomy_structure_to_log() {
    $parent_terms = get_terms(array(
        'taxonomy' => 'path',
        'hide_empty' => false,
        'parent' => 0  // Get only top-level terms
    ));

    if (!is_wp_error($parent_terms)) {
        foreach ($parent_terms as $parent) {
            // Fetch all child terms
            $child_terms = get_terms(array(
                'taxonomy' => 'path',
                'hide_empty' => false,
                'parent' => $parent->term_id
            ));

            $log_message = 'Parent Term: ' . $parent->name;
            error_log($log_message);

            foreach ($child_terms as $child) {
                $log_message = ' - Child Term: ' . $child->name;
                error_log($log_message);
            }
            error_log(""); // Add a line break for readability in the log
        }
    } else {
        error_log('Error retrieving terms: ' . $parent_terms->get_error_message());
    }
}
?>