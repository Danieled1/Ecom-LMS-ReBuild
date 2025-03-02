<?php
/*
Template Name: Job Placement
*/

acf_form_head();
get_header();

// Get current user data
$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();
$job_status = get_field('job_status', 'user_' . $current_user_id);
$resume_file = get_field('resume_file', 'user_' . $current_user_id);
$interview_details = get_field('interview_details', 'user_' . $current_user_id);
$employment_info = get_field('employment_info', 'user_' . $current_user_id);
$placement_notes_file = get_field('placement_notes', 'user_' . $current_user_id);
$placement_notes_updated_at = $placement_notes_file ? get_last_updated_at($current_user_id, 'placement_notes_last_updated') : 'Never updated';
$section_data = array(
	'job_status' => $job_status,
	'interview_details' => $interview_details,
	'current_user_id' => $current_user_id,
	'resume_file' => $resume_file
);

// TO DO: User interface to update interview details - there is also ajax and js commented \/ \/

// function displayInterviewDetail($interview_details, $key, $label) {
// 	$current_user_id = get_current_user_id();
//     $detail = isset($interview_details[$key]) ? $interview_details[$key] : '';
//     echo '<div class="interview-detail" id="detail-' . $key . '">';
//     echo '<span class="label status-heading">' . $label . ':</span>';

//     if ($detail) {
//         echo '<span class="value status-heading">' . esc_html($detail) . '</span>';
//     } else {
//         echo '<span class="value status-heading missing-details" id="value-' . $key . '">חסרים פרטים על ראיון זה</span>';
//     }

//     echo '<button id="edit-button-' . $key . '" class="edit-button" onclick="editDetail(\'' . $key . '\')">Edit</button>';
//     echo '<input data-user="' . $current_user_id . '"type="text" class="edit-input hidden" id="input-' . $key . '" value="' . esc_attr($detail) . '">';
//     echo '<button class="save-button hidden" onclick="saveDetails()">Save</button>';
//     echo '</div>';
// }


function displayInterviewDetail($interview_details, $key, $label)
{
	if (!empty($interview_details[$key])) {
		error_log("INSIDE displayInterviewDetails" . $interview_details[$key]);
		echo '<div class="interview-detail">';
		if ($key === 'interview_date') {
			echo '<span class="icon calendar">&#128197;</span>';
		}
		if ($key === 'interview_time') {
			echo '<span class="icon clock">&#128340;</span>';
		}
		if ($key === 'interview_location') {
			echo '<span class="icon ">&#127970;</span>';
		}
		echo '<span class="label status-heading"> ' . $label . ' :</span>';



		echo '<span class="value status-heading"> ' . esc_html($interview_details[$key]) . ' </span>';
		echo '</div>';
	} else {
		echo '<div class="interview-detail">';
        echo '<span class="label status-heading">' . $label . ':</span>';
        echo '<span class="value status-heading">חסרים פרטים על ראיון זה</span>'; // Hebrew text for "Missing details about this interview"
        echo '</div>';
	}
}
function displayEmploymentInfo($employment_info)
{
	if (!empty($employment_info['company_name'])) {
		echo '<p class="status-heading" style="margin: 0;"> ' . esc_html($employment_info['company_name']) . '</p>';
	}
	if (!empty($employment_info['company_logo'])) {
		echo '<img src="' . esc_url($employment_info['company_logo']['url']) . '" alt="' . esc_attr($employment_info['company_name']) . ' Logo">';
	}
}
function displayPlacementNotes($placement_notes_file, $placement_notes_updated_at)
{
	if ($placement_notes_file) {
		$file_name = basename($placement_notes_file);
		echo '<a class="placement-notes-link" href="' . esc_url($placement_notes_file) . '" download>' . esc_html($file_name) . '</a>';

		if ($placement_notes_updated_at) {
			echo '<p class="placement-notes-update"> עלה בתאריך:  ' . esc_html($placement_notes_updated_at) . '</p>';
		}
	} else {
		echo '<p class="placement-notes-update">אין הערות כרגע. </p>';
	}
}
// function displayHeaderWithIcon($svg_name, $header_text)
// {
// 	echo '<div class="status-header">';
// 	echo '<div class="chart-icon">';
// 	echo '<img src=' . get_stylesheet_directory_uri() . '/assets/vectors/' . $svg_name . '.svg"	alt="' . $svg_name . '" class="chart-simple-img"/>';
// 	echo '</div>';
// 	echo '<h3 class="resume-status">' . $header_text . '</h3>';
// 	echo '</div>';
// }
function displayJobStatusSection($section_data)
{
	$job_status = $section_data['job_status'];
	$interview_details = $section_data['interview_details'];
	$current_user_id = $section_data['current_user_id'];
	$resume_file = $section_data['resume_file'];
	error_log("CURRENT_JOB_STATUS = " . print_r($job_status,true));

	if ($job_status['value'] == 'hired') {
		echo '<p class="status-heading">מאחלים המון בהצלחה בעבודה החדשה.</p>';
	} elseif ($job_status['value'] == 'interview') {
		echo '<div class="status-container">';
		displayHeaderWithIcon('note-sticky','פרטי ראיון');
		echo '<div class="interview-status">';
		displayInterviewDetail($interview_details, 'interview_date', 'תאריך');
		displayInterviewDetail($interview_details, 'interview_time', 'שעה');
		displayInterviewDetail($interview_details, 'interview_location', 'מיקום');
		echo '</div>';
		echo '</div>';
	} elseif ($job_status['value'] == 'published') {
		echo '<div class="status-container">';
		displayHeaderWithIcon('note-sticky','עדכון');
		echo '<p class="status-heading" style="margin-top:0;">קורות החיים שלך נשלחו למעסיקים מחכים לתשובה..</p>';
		echo '<p class="status-heading">צפי לתשובות תוך 1-2 שבועות.</p>';
		echo '<p class="status-heading">בזמן ההמתנה, כדאי להכין את עצמך לראיונות ולוודא שפרופיל ה-LinkedIn שלך מעודכן.</p>';
		echo '</div>';
	} elseif ($job_status['value'] !== 'hired') {
		// Display the ACF form for resume upload
		acf_form(array(
			'post_id' => 'user_' . $current_user_id,
			'post_title' => false,
			'post_content' => false,
			'fields' => array('resume_file'),
			'submit_value' => $resume_file ? 'עדכון קורות חיים' : 'העלאת קורות חיים',
			'updated_message' => __("קורות חיים עודכנו", 'acf'),
			'html_updated_message' => '<div id="message" class="updated"><p>%s</p></div>',
			'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
			'html_submit_spinner' => '<span class="acf-spinner"></span>',
			'form_attributes' => array(
				'dir' => 'rtl',
				'lang' => 'he'
			),
			'label_placement' => 'top',
			'instruction_placement' => 'label'
		));
	}

}

function displayCourseItem($course_id, $user_id)
{
	global $post;
	$course_id = get_the_ID();
	$is_enrolled = sfwd_lms_has_access($course_id, $user_id);
	$class = $is_enrolled ? 'bb-card-course-details--hasAccess' : 'bb-card-course-details--noAccess';
	$course_price = learndash_get_course_meta_setting($course_id, 'course_price');
	$course_price_type = learndash_get_course_meta_setting($course_id, 'course_price_type');
	$course_pricing = learndash_get_course_price($course_id);
	$progress = learndash_course_progress(array('user_id' => $user_id, 'course_id' => $course_id, 'array' => true));
	$progress_percentage = isset($progress['percentage']) ? intval($progress['percentage']) : 0;
	$button_text = $progress_percentage > 0 ? "בתהליך" : "התחל קורס";
	$steps_count = sizeof(learndash_get_course_steps($course_id));
	$author_id = get_post_field('post_author', $course_id);
	$author_name = get_userdata($author_id)->display_name;
	$author_url = bp_core_get_user_domain($author_id);
	$author_avatar = get_avatar($author_id, 80);
	$user_course_has_access = sfwd_lms_has_access($course_id, $user_id);


	if ($user_course_has_access) {
		$is_enrolled = true;
	} else {
		$is_enrolled = false;
	}
	?>

	<li class="bb-course-item-wrap">
		<div class="card-course-image-container bb-cover-list-item <?php echo esc_attr($class); ?>">
			<div class="bb-course-cover">
				<a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>" class="bb-cover-wrap">
					<div class="ld-status ld-status-progress ld-primary-background">
						<?php echo $button_text; ?>
					</div>
				</a>
			</div>
			<div class="bb-card-course-details <?php echo esc_attr($class); ?>">
				<div class="card-course-lessons-steps course-lesson-count"><?php echo $steps_count . ' Lessons'; ?></div>
				<h2 class="bb-course-title">
					<a class="card-course-header truncate-title" title="<?php the_title(); ?>"
						href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php
				if (is_user_logged_in() && isset($user_course_has_access) && $user_course_has_access) {
					?>

					<div class="course-progress-wrap">

						<?php
						learndash_get_template_part(
							'modules/progress.php',
							array(
								'context' => 'course',
								'user_id' => $user_id,
								'course_id' => $course_id,
							),
							true
						);
						?>

					</div>

				<?php } ?>
			</div>
		</div>
	</li>

	<?php
}

?>
<div class="container">
	<div class="placement-page-content">
		<div class="header-image-wrapper">
			<div class="header-image">
				<img src="https://dev.digitalschool.co.il/wp-content/uploads/2024/10/placement-bg.png"
					alt="Header Image" />
				<div class="header-gradient-overlay"></div>
				<div class="header-text">
					<div class="header-placement-title" style="margin-bottom: 20px;">השמה לשוק העבודה</div>
					<div class="header-placement-subtitle">מחפשים את הצעד הבא בקריירה שלכם? אצלנו תמצאו מגוון רחב של
						הזדמנויות עבודה שמתאימות לכישורים ולשאיפות שלכם<br> אנחנו כאן כדי לעזור לכם למצוא את המשרה הבאה
						שלכם</div>
				</div>
			</div>
		</div>
		<div class="header-placement-subtitle" style="color: #333; font-size: 18px; line-height: 115.375%;">כל מה שאתם
			צריכים בשביל לנהל את חיפוש העבודה שלכם איתנו</div>

		<div class="placement-container">
			<div class="placement-sub-container">
				<div class="status-sub-items">
				<div id="company-header" class="status-container hidden">
						<div class="status-header">
							<?php
							if ($employment_info) {
								?>
								<div class="chart-icon">
									<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/BUILDING.svg"
										alt="Chart Simple" id="chart-img" class="chart-simple-img hidden" />
								</div>
								<h3 id="company-name" class="resume-status hidden">שם החברה:</h3>
							</div>
							<?php
							displayEmploymentInfo($employment_info);
							} else {
								echo '<p class="status-heading no-info">פרטי תעסוקה יעודכנו בהמשך :)</p>';
							}
							?>
					</div>
					<div id="company-logo" class="hidden">company logo</div>
					<div><?php displayJobStatusSection($section_data); ?></div>
				</div>
			</div>
			<div class="placement-sub-container">
				<div class="status-sub-items flex-items">
					<div class="status-container">
						<?php displayHeaderWithIcon('chart-simple', 'סטטוס השמה'); ?>
						<div class="status-label">
							<h4 id="resume-label"><?php echo $job_status['label']; ?></h4>
						</div>
					</div>
					<div class="status-container">
						<?php displayHeaderWithIcon('note-sticky','הערות'); ?>
	
						<div class="placement-notes">
							<?php displayPlacementNotes($placement_notes_file, $placement_notes_updated_at); ?>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- Display 4 Related Courses -->
		<div class="related-courses">
			<h2 id="related-courses-header">קורסים לחיפוש עבודה</h2>
			<p class="header-placement-subtitle" style="color: #333; font-size: 18px; line-height: 115.375%;">
				כדי להצליח בתהליך חיפוש העבודה, חשוב להשלים את הקורסים האלו. הם יעזרו לכם לבנות את היכולות והידע
				הנדרשים,
				כך שתהיו יותר מוכנים להצלחה בראיונות העבודה ובקבלה למשרה שתתאים לכם.
			</p>
			<div class="related-courses-content">

				<?php
				// Define the query arguments
				$args = array(
					'post_type' => 'sfwd-courses', // This should be the custom post type for the courses
					'posts_per_page' => 4, // Number of courses to display
					'tax_query' => array(
						array(
							'taxonomy' => 'ld_course_category', // This should be the taxonomy name for the course category
							'field' => 'slug',
							'terms' => 'job-prep', // The category slug
						),
					),
				);

				// The Query
				$the_query = new WP_Query($args);

				// The Loop
				if ($the_query->have_posts()) {
					echo '<ul id="courses-placement" class="bb-course-list bb-course-items grid-view bb-grid" aria-live="assertive" aria-relevant="all">';
					while ($the_query->have_posts()) {
						$the_query->the_post();

						displayCourseItem(get_the_ID(), get_current_user_id());
					}
					echo '</ul>';
				} else {
					// No posts found
					echo 'No courses found in this category.';
				}
				// Restore original Post Data
				wp_reset_postdata();
				?>
			</div>
		
		</div>

	</div>
</div><!-- #primary -->
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function () {
		const companyLogo = document.getElementById("company-logo");
		const imageChart = document.getElementById("chart-img");
		const companyName = document.getElementById("company-name");
		const companyHeader = document.getElementById("company-header");
		<?php if ($employment_info && !empty($employment_info['company_name'])): ?>
			// If employment info with a company logo is available, remove the hidden class
			console.log("Entered the condtion");

			companyLogo.classList.remove("hidden");
			imageChart.classList.remove("hidden");
			companyName.classList.remove("hidden");
			companyHeader.classList.remove("hidden");
		<?php endif; ?>
	});
</script>
<?php
get_footer();
?>