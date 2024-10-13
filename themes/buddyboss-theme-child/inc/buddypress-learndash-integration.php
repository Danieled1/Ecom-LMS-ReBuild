<?php

// BUDDYPRESS
function add_certificate_tab()
{
    bp_core_new_nav_item([
        'name' => __('תעודות', 'text-domain'),
        'slug' => 'certificates',
        'position' => 30,
        'screen_function' => 'show_certificate_screen',
        'default_subnav_slug' => 'certificates',
        'item_css_id' => 'certificates-personal-li'
    ]);
        // Remove 'Forums' tab
        bp_core_remove_nav_item('forums');
        
        // Remove 'Groups' tab
        bp_core_remove_nav_item('groups');
}
// BUDDYPRESS
function show_certificate_screen()
{
    add_action('bp_template_content', 'certificate_screen_content');
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}
// BUDDYPRESS
function certificate_screen_content()
{
    $user_id = bp_displayed_user_id();
    $certificates = get_user_certificates($user_id);
    if (!empty($certificates)) {
        echo '<ul id="certificate_list" class="bb-grid">';
        foreach ($certificates as $certificate) {
            $date_earned_formatted = date('F j, Y', $certificate['date_earned']);
            echo '<li class="sm-grid-1-1">';
            echo '<div class="bb-certificate-wrap">';
            echo '<div class="bb-certificate-content">';
            echo '<h3 class="bb-certificate-title"><span>Certificate in </span> <a href="' . esc_url($certificate['course_url']) . '">' . esc_html($certificate['course_name']) . '</a></h3>';
            echo '<div class="bb-certificate-date"><span>Earned on</span> ' . esc_html($date_earned_formatted) . '</div>';
            echo '<p class="bb-certificate-download"><a href="' . esc_url($certificate['download_url']) . '"><i class="bb-icon-rl bb-icon-arrow-down" aria-hidden="true"></i>Download PDF</a></p>';
            echo '</div>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<aside class="bp-feedback bp-messages info"><span class="bp-icon" aria-hidden="true"></span><p>Sorry, no certificates were found.</p></aside>';
    }
}
// LEARNDASH -  Import from learndash-functions.php 
function get_user_certificates($user_id)
{
    $certificates = [];
    $course_ids = learndash_user_get_enrolled_courses($user_id, array('status' => 'complete'));

    foreach ($course_ids as $course_id) {
        $completion_date = learndash_user_get_course_completed_date($user_id, $course_id);
        if ($completion_date) {
            $certificate_link = learndash_get_course_certificate_link($course_id, $user_id);
            if ($certificate_link) {
                $certificates[] = [
                    'course_name' => get_the_title($course_id),
                    'course_url' => get_permalink($course_id),
                    'download_url' => $certificate_link,
                    'date_earned' => $completion_date,
                ];
            }
        } else {
            error_log('Course not completed or date not available');
        }
    }
    return $certificates;
}
add_action('bp_setup_nav', 'add_certificate_tab', 99);
// BUDDYPRESS
function my_profile_nav_item_classes($classes, $item)
{
    error_log("MY_PROFILE_NAV_ITEM - " . print_r($item, true));
    if (bp_is_user() && $item->slug === 'certificates' && bp_is_current_action('certificates')) {
        $classes[] = 'current selected';
    }
    return $classes;
}
add_filter('bp_get_options_nav_classes', 'my_profile_nav_item_classes', 10, 2);


// LEARNDASH
function update_main_course_path($user_id, $course_id)
{
    // Fetch course title
    $course_title = get_the_title($course_id);

    // Define main courses keywords
    $main_courses_keywords = ['Full Stack', 'QA', 'AI', 'Data Analyst', 'Digital Marketing', 'Cyber'];

    // Check if the course title contains the specific keywords and the word "קורס"
    if (strpos($course_title, 'קורס') !== false) {
        foreach ($main_courses_keywords as $keyword) {
            if (strpos($course_title, $keyword) !== false) {
                // If found, update the user meta with the course path
                update_user_meta($user_id, 'path', $course_title);
                break; // Exit the loop once the main course is found and saved
            }
        }
    }
}
// Hook into LearnDash course enrollment
add_action('ld_added_course_access', 'update_main_course_path', 10, 2);
function add_dynamic_group_tabs() {
    $user_id = bp_displayed_user_id();

    if (!is_user_logged_in() || !bp_is_user()) {
        return; // Early return if user is not logged in or not on a user profile
    }

    $current_group_ids = learndash_get_users_group_ids($user_id);
    $main_group_slug = process_groups($current_group_ids); // Process groups and find main group

    // if (!empty($main_group_slug)) {
    //     add_action('bp_template_redirect', function() use ($main_group_slug) {
    //         bp_core_new_nav_default([
    //             'parent_slug' => 'courses',
    //             'subnav_slug' => $main_group_slug,
    //             'screen_function' => function () use ($main_group_slug) {
    //                 show_group_courses_screen_by_slug($main_group_slug);
    //             }
    //         ]);
    //     });
    // }
}

// LEARNDASH/util ? Not sure if its util global or need to be in learndash-functions own util?
function process_groups($group_ids) {
    $main_group_slug = '';
    foreach ($group_ids as $group_id) {
        $group = get_post($group_id);
        if ($group) {
            $is_main_group = is_main_group($group->post_title);
            $group_slug = add_group_tab($group, $is_main_group);
            if ($is_main_group) {
                $main_group_slug = $group_slug;  // Capture slug of the main group
            }
        }
    }
    return $main_group_slug;
}
// LEARNDASH ? not sure
function add_group_tab($group, $is_main_group = false) {
    $group_name = $group->post_title;
    $group_slug = sanitize_title($group_name);
    $position = determine_position($group_name);

    if ($position !== null) {
        bp_core_new_subnav_item([
            'name' =>  __($group_name , 'text-domain'),
            'slug' => $group_slug,
            'parent_url' => trailingslashit(bp_displayed_user_domain() . 'courses'),
            'parent_slug' => 'courses',
            'screen_function' => function () use ($group) {
                show_group_courses_screen($group->ID);
            },
            'position' => $position,
            'item_css_id' => $group_slug . '-personal-li'
        ]);
        if ($is_main_group) {
            return $group_slug;
        }
    }
    return '';
}

function show_group_courses_screen_by_slug($slug) {
    $group_id = find_group_id_by_slug($slug); // You will need to implement this function
    if ($group_id) {
        show_group_courses_screen($group_id);
    } else {
        error_log("No group found for slug: $slug");
    }
}
// LEARDASH util ? - not sure
function find_group_id_by_slug($slug) {
    // Implement lookup logic here
    $args = [
        'name' => $slug,
        'post_type' => 'group', // Adjust according to your post type
        'numberposts' => 1
    ];
    $posts = get_posts($args);
    return $posts ? $posts[0]->ID : null;
}
// global util ? - not sure

function is_main_group($group_name) {
    return strpos($group_name, "מחזור לימוד קורס") !== false; // Detecting the main group by title
}
// learndash ? - not sure
function determine_position($group_name) {
    // Define positions based on group names
    if (strpos($group_name, "מחזור לימוד קורס") !== false) {
        return 10;
    } elseif (strpos($group_name, "General Courses Extra") !== false) {
        return 20;
    } elseif (strpos($group_name, "Full Stack Extra") !== false) {
        return 30;
    } elseif (strpos($group_name, "QA Extra") !== false) {
        return 40;
    } elseif (strpos($group_name, "AI Extra") !== false) {
        return 50;
    } elseif (strpos($group_name, "Data & Digital Extra") !== false) {
        return 60;
    } elseif (strpos($group_name, "Cyber Extra") !== false) {
        return 70;
    }
    return null;
}
// learndash
function show_group_courses_screen($group_id) {
    add_action('bp_template_content', function () use ($group_id) {
        display_group_courses($group_id);
    });
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}
// learndash ? - mabe save the html template in the learndash/util? 
function display_group_courses($group_id) {
    $courses = learndash_group_enrolled_courses($group_id);
    if (!empty($courses)) {
        echo '<div class="related-courses">';
        echo '<div class="related-courses-content">';
        echo '<ul class="bb-course-list bb-course-items grid-view bb-grid" aria-live="assertive" aria-relevant="all">';
        foreach ($courses as $course_id) {
            display_course_item($course_id);
        }
        echo '</ul>';
        echo '</div>'; // Close related-courses-content
        echo '</div>'; // Close related-courses
    } else {
        echo 'No courses found in this group.';
    }
}
// learndash ? - mabe save the html template in the learndash/util? 

function display_course_item($course_id) {
    $course_title = get_the_title($course_id);
    $course_url = get_permalink($course_id);
    $steps_count = sizeof(learndash_get_course_steps($course_id));
    $course_progress = learndash_course_progress([
        'user_id' => bp_displayed_user_id(),
        'course_id' => $course_id,
        'array' => true
    ]);
    $progress_percentage = isset($course_progress['percentage']) ? intval($course_progress['percentage']) : 0;
    $button_text = $progress_percentage > 0 ? "בתהליך" : "התחל קורס";
    $course_args     = array(
        'course_id'     => $course_id,
        'user_id'       => bp_displayed_user_id(),
        'post_id'       => $course_id,
        'activity_type' => 'course',
    );
    $course_activity = learndash_get_user_activity( $course_args );


    echo '<li class="bb-course-item-wrap">';
    echo '<div class="card-course-image-container bb-cover-list-item">';
    echo '<div class="bb-course-cover">';
    echo '<a title="' . esc_attr($course_title) . '" href="' . esc_url($course_url) . '" class="bb-cover-wrap">';
    echo '<div class="card-course-status ld-status ld-status-progress ld-primary-background">' . $button_text . '</div>';
    echo '</a></div>';
    echo '<div class="bb-card-course-details bb-card-course-details--hasAccess">';
    echo '<div class="card-course-lessons-steps course-lesson-count">' . $steps_count . ' Lessons</div>';
    echo '<h2 class="bb-course-title">';
    echo '<a class="card-course-header truncate-title" title="' . esc_attr($course_title) . '" href="' . esc_url($course_url) . '">' . esc_html($course_title) . '</a>';
    echo '</h2>';
    echo '<div class="course-progress-wrap">';
    echo '<div class="ld-progress ld-progress-inline">';
    echo '<div class="ld-progress-bar">';
    echo '<div class="bar-styles ld-progress-bar-percentage" style="width:' . $progress_percentage . '%;"></div>';
    echo '</div>';
    echo '<div class="ld-progress-stats">';
    echo '<div class="ld-progress-percentage ld-secondary-color course-completion-rate">';
    echo '<div class="card-course-last-activity ld-progress-steps">';

    if (!empty($course_activity->activity_updated)) {
        echo sprintf(
            /* translators: Last activity date in infobar. */
            esc_html_x('פעילות אחרונה ב-%s', 'Last activity date in infobar', 'buddyboss-theme'),
            esc_html(learndash_adjust_date_time_display($course_activity->activity_updated))
        );
    } else {
        echo sprintf(
            /* translators: placeholders: completed steps, total steps. */
            esc_html_x('%1$d/%2$d Steps', 'placeholders: completed steps, total steps', 'buddyboss-theme'),
            esc_html($course_progress['completed']),
            esc_html($course_progress['total'])
        );
    }

    echo '</div>'; // Close card-course-last-activity
    echo '</div>'; // Close ld-progress
    echo '</div>'; // Close bb-card-course-details
    echo '</div>'; // Close bb-cover-list-item
    echo '</li>';
}

add_action('learndash_added_user_group', 'clear_user_group_cache', 10, 2);
add_action('learndash_removed_user_group', 'clear_user_group_cache', 10, 2);

add_action('bp_setup_nav', 'add_dynamic_group_tabs', 100);