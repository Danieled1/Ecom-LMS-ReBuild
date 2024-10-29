<?php
/**
 * LearnDash LD30 Displays a course
 *
 * Available Variables:
 * $course_id                   : (int) ID of the course
 * $course                      : (object) Post object of the course
 * $course_settings             : (array) Settings specific to current course
 *
 * $courses_options             : Options/Settings as configured on Course Options page
 * $lessons_options             : Options/Settings as configured on Lessons Options page
 * $quizzes_options             : Options/Settings as configured on Quiz Options page
 *
 * $user_id                     : Current User ID
 * $logged_in                   : User is logged in
 * $current_user                : (object) Currently logged in user object
 *
 * $course_status               : Course Status
 * $has_access                  : User has access to course or is enrolled.
 * $materials                   : Course Materials
 * $has_course_content          : Course has course content
 * $lessons                     : Lessons Array
 * $quizzes                     : Quizzes Array
 * $lesson_progression_enabled  : (true/false)
 * $has_topics                  : (true/false)
 * $lesson_topics               : (array) lessons topics
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if (!defined('ABSPATH')) {
    exit;
}

$materials = (isset($materials)) ? $materials : '';
$lessons = (isset($lessons)) ? $lessons : array();
$quizzes = (isset($quizzes)) ? $quizzes : array();
$lesson_topics = (isset($lesson_topics)) ? $lesson_topics : array();
$course_certficate_link = (isset($course_certficate_link)) ? $course_certficate_link : '';

$template_args = array(
    'course_id' => $course_id,
    'course' => $course,
    'course_settings' => $course_settings,
    'courses_options' => $courses_options,
    'lessons_options' => $lessons_options,
    'quizzes_options' => $quizzes_options,
    'user_id' => $user_id,
    'logged_in' => $logged_in,
    'current_user' => $current_user,
    'course_status' => $course_status,
    'has_access' => $has_access,
    'materials' => $materials,
    'has_course_content' => $has_course_content,
    'lessons' => $lessons,
    'quizzes' => $quizzes,
    'lesson_progression_enabled' => $lesson_progression_enabled,
    'has_topics' => $has_topics,
    'lesson_topics' => $lesson_topics,
    'post' => $post,
);
$course_title = get_the_title($course_id);

$has_lesson_quizzes = learndash_30_has_lesson_quizzes($course_id, $lessons); ?>

<div class="<?php echo esc_attr(learndash_the_wrapper_class()); ?>">
    <div class="header-image-wrapper">
        <div class="header-image">
            <img src="https://dev.digitalschool.co.il/wp-content/uploads/2024/10/content.png" alt="Header Image" />
            <div class="header-gradient-overlay"></div>
            <div class="header-text">
                <div class="header-title"><?php echo esc_html($course_title); ?></div>
                <div class="header-subtitle">
                    <div class="bb-course-points">
                        <a class="anchor-course-points" href="#learndash-course-content">
                            <?php echo sprintf(esc_html_x('צפו בפרטי ה%s', 'link: View Course details', 'buddyboss-theme'), LearnDash_Custom_Label::get_label('course')); ?>
                            <i class="bb-icon-l bb-icon-angle-down"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bb-grid">

        <div class="bb-learndash-content-wrap">

            <?php
            /**
             * Fires before the course certificate link.
             *
             * @since 3.0.0
             *
             * @param int $course_id Course ID.
             * @param int $user_id   User ID.
             */
            do_action('learndash-course-certificate-link-before', $course_id, $user_id);

            /**
             * Certificate link
             */

            if (
                (defined('LEARNDASH_TEMPLATE_CONTENT_METHOD')) &&
                ('shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD)
            ) {
                $shown_content_key = 'learndash-shortcode-wrap-ld_certificate-' . absint($course_id) . '_' . absint($user_id);
                if (false === strstr($content, $shown_content_key)) {
                    $shortcode_out = do_shortcode('[ld_certificate course_id="' . $course_id . '" user_id="' . $user_id . '" display_as="banner"]');
                    if (!empty($shortcode_out)) {
                        echo $shortcode_out;
                    }
                }
            } else {
                if (!empty($course_certficate_link)):
                    learndash_get_template_part(
                        'modules/alert.php',
                        array(
                            'type' => 'success ld-alert-certificate',
                            'icon' => 'certificate',
                            'message' => __('You\'ve earned a certificate!', 'buddyboss-theme'),
                            'button' => array(
                                'url' => $course_certficate_link,
                                'icon' => 'download',
                                'label' => __('Download Certificate', 'buddyboss-theme'),
                                'target' => '_new',
                            ),
                        ),
                        true
                    );
                endif;
            }

            /**
             * Fires after the course certificate link.
             *
             * @since 3.0.0
             *
             * @param int $course_id Course ID.
             * @param int $user_id   User ID.
             */
            do_action('learndash-course-certificate-link-after', $course_id, $user_id);


            if (
                (defined('LEARNDASH_TEMPLATE_CONTENT_METHOD')) &&
                ('shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD)
            ) {
                $shown_content_key = 'learndash-shortcode-wrap-ld_infobar-' . absint($course_id) . '_' . (int) get_the_ID() . '_' . absint($user_id);
                if (false === strstr($content, $shown_content_key)) {
                    $shortcode_out = do_shortcode('[ld_infobar course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]');
                    if (!empty($shortcode_out)) {
                        echo $shortcode_out;
                    }
                }
            } else {
                /**
                 * Course info bar
                 */
                learndash_get_template_part(
                    'modules/infobar.php',
                    array(
                        'context' => 'course',
                        'course_id' => $course_id,
                        'user_id' => $user_id,
                        'has_access' => $has_access,
                        'course_status' => $course_status,
                        'post' => $post,
                    ),
                    true
                );
            }

            /** This filter is documented in themes/legacy/templates/course.php */
            echo apply_filters('ld_after_course_status_template_container', '', learndash_course_status_idx($course_status), $course_id, $user_id);

            /**
             * Content tabs
             */

            echo '<div class="course-content-container bb-ld-tabs">';
            echo '<div id="learndash-course-content"></div>';
            learndash_get_template_part(
                'modules/tabs.php',
                array(
                    'course_id' => $course_id,
                    'post_id' => get_the_ID(),
                    'user_id' => $user_id,
                    'content' => $content,
                    'materials' => $materials,
                    'context' => 'course',
                ),
                true
            );
            // Now, include the course content shortcode inside this same container
            if (defined('LEARNDASH_TEMPLATE_CONTENT_METHOD') && 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD) {
                $shown_content_key = 'learndash-shortcode-wrap-course_content-' . absint($course_id) . '_' . (int) get_the_ID() . '_' . absint($user_id);
                if (false === strstr($content, $shown_content_key)) {
                    $shortcode_out = do_shortcode('[course_content course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]');
                    if (!empty($shortcode_out)) {
                        echo $shortcode_out;
                    }
                }
            }
            learndash_get_template_part(
                'template-course-author-details.php',
                array(
                    'context' => 'course',
                    'course_id' => $course_id,
                    'user_id' => $user_id,
                ),
                true
            );
            echo '</div>';

  

            ?>

        </div>

        <?php
        // Single course sidebar.
        learndash_get_template_part('template-single-course-sidebar.php', $template_args, true);
        ?>
    </div>
</div>