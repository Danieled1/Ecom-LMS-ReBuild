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

?>

<div class="container">
	<div class="content">
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
		<div class="header-placement-title" style="color:#6836FF; font-weight: 700; line-height: 100%;">השמה לשוק העבודה
		</div>
		<div class="header-placement-subtitle" style="color: #333; font-size: 18px; line-height: 115.375%;">כל מה שאתם
			צריכים בשביל לנהל את חיפוש העבודה שלכם איתנו</div>

		<div class="placement-container">
			<div class="placement-sub-container">
				<div class="status-sub-items">
					<div class="status-container">
						<div class="status-header">
							<div class="chart-icon">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/chart-simple.svg"
									alt="Chart Simple" class="chart-simple-img" />
							</div>
							<h3 class="resume-status">סטטוס השמה</h3>
						</div>
						<div class="status-label">
							<h4 id="resume-label"><?php echo $job_status['label']; ?></h4>
						</div>
					</div>
					<div>
						<?php
						if ($job_status['value'] == 'hired') {
							echo '<p class="status-heading">מאחלים המון בהצלחה בעבודה החדשה.</p>';
						} elseif ($job_status['value'] == 'interview') {
							echo '<div class="interview-status">';
							displayInterviewDetail($interview_details, 'interview_date', 'תאריך');
							displayInterviewDetail($interview_details, 'interview_time', 'שעה');
							displayInterviewDetail($interview_details, 'interview_location', 'מיקום');
							echo '</div>';
						} elseif ($job_status['value'] == 'published') {
							echo '<p class="status-heading">קורות החיים שלך נשלחו למעסיקים מחכים לתשובה..</p>';
							echo '<p class="status-heading">צפי לתשובות תוך 1-2 שבועות.</p>';
							echo '<p class="status-heading">בזמן ההמתנה, כדאי להכין את עצמך לראיונות ולוודא שפרופיל ה-LinkedIn שלך מעודכן.</p>';
						} elseif ($job_status['value'] !== 'hired') { // Allow re-upload of resume unless the user is employed
							$custom_label = 'Resume File (PDF)';
							// Display the ACF form to allow users to upload or update their resume
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
									'dir' => 'rtl', // Enable right-to-left text direction
									'lang' => 'he'  // Set the language to Hebrew
								),
								'label_placement' => 'top',  // Places labels above fields
								'instruction_placement' => 'label'  // Places instructions below labels
							));

						}
						?>
					</div>
				</div>
			</div>
			<div class="placement-sub-container">
				<div class="status-sub-items flex-items">
					<div id="company-header" class="status-container hidden">
						<div class="status-header">
							<?php
							if ($employment_info) {
								?>
								<div class="chart-icon">
									<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/chart-simple.svg"
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

					<div class="status-container">
						<div class="status-header">
							<div class="chart-icon">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/chart-simple.svg"
									alt="Chart Simple" class="chart-simple-img" />
							</div>
							<h3 class="resume-status">הערות:</h3>
						</div>
						<div class="placement-notes">
							<?php
							if ($placement_notes_file) {
								$file_name = basename($placement_notes_file);
								echo '<a class="placement-notes-link" href="' . esc_url($placement_notes_file) . '" download>' . esc_html($file_name) . '</a>';

								if ($placement_notes_updated_at) {
									echo '<p class="placement-notes-update"> עלה בתאריך:  ' . esc_html($placement_notes_updated_at) . '</p>';
								}
							} else {
								echo '<p class="placement-notes-update">אין הערות כרגע. </p>';
							}
							?>
						</div>
					</div>
				</div>

			</div>
		</div>
		<!-- Resume Upload Form -->
		<div class="resume-status-section">
			<?php
			echo '<h2>סטטוס השמה<br> <h4>' . $job_status['label'] . '</h4> </h2>';
			?>

			<div class="status-section">
				<?php
				// Display the current status of the job 
				if ($job_status['value'] == 'interview') {
					echo '<div class="interview-status">';
					echo '<p class="status-heading">Interview Scheduled</p>';
					displayInterviewDetail($interview_details, 'interview_date', label: 'תאריך');
					displayInterviewDetail($interview_details, 'interview_time', 'שעה');
					displayInterviewDetail($interview_details, 'interview_location', 'מיקום');
					echo '</div>';
				} elseif ($job_status['value'] == 'hired') {
					echo '<div class="hired-status">';
					echo '<p class="status-heading">Congratulations on Your Employment!</p>';
					if ($employment_info) {
						displayEmploymentInfo($employment_info);
					} else {
						echo '<p class="no-info">Employment details are not available at the moment.</p>';
					}
					echo '</div>';
				}
				// Allow re-upload of resume unless the user is employed
				if ($job_status['value'] !== 'hired') {
					$custom_label = 'Resume File (PDF)';
					// Display the ACF form to allow users to upload or update their resume
					acf_form(array(
						'post_id' => 'user_' . $current_user_id,
						'post_title' => false,
						'post_content' => false,
						'fields' => array('resume_file'),
						'submit_value' => $resume_file ? 'עדכון קורות חיים' : 'העלאת קורות חיים',
						'updated_message' => __("קורות חיים עודכנו", 'acf'),
						'form_attributes' => array(
							'dir' => 'rtl', // Enable right-to-left text direction
							'lang' => 'he'  // Set the language to Hebrew
						),
						'label_placement' => 'top',  // Places labels above fields
						'instruction_placement' => 'label'  // Places instructions below labels
					));

				}
				// Function to display interview details
				function displayInterviewDetail($interview_details, $key, $label)
				{
					if (!empty($interview_details[$key])) {
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
					}
				}
				// Function to display employment information
				function displayEmploymentInfo($employment_info)
				{
					if (!empty($employment_info['company_name'])) {
						echo '<p class="status-heading" style="margin: 0;"> ' . esc_html($employment_info['company_name']) . '</p>';
					}
					if (!empty($employment_info['company_logo'])) {
						echo '<img src="' . esc_url($employment_info['company_logo']['url']) . '" alt="' . esc_attr($employment_info['company_name']) . ' Logo">';
					}
				}
				?>
			</div>

			<div class="placement-notes-section">
				<h2>הערות</h2>
				<?php
				if ($placement_notes_file) {
					// Extract the file name from the file path
					$file_name = basename($placement_notes_file);

					// Output the download link for the file with the file name
					echo '<p><a href="' . esc_url($placement_notes_file) . '" download>' . esc_html($file_name) . '</a></p>';

					// Display the last updated date if available
					if ($placement_notes_updated_at) {
						echo '<p> Uploaded on: ' . esc_html($placement_notes_updated_at) . '</p>';
					}
				} else {
					// Display a message if no placement notes are available
					echo '<p>No placement notes available.</p>';
				}
				?>
			</div>
		</div>
		<!-- Display 4 Related Courses -->
		<div class="related-courses">
			<h2 id="header">Related Find-Job Courses</h2>
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
					echo '<ul class="bb-course-list bb-course-items grid-view bb-grid" aria-live="assertive" aria-relevant="all">';
					while ($the_query->have_posts()) {
						$the_query->the_post();
						$steps_count = sizeof(learndash_get_course_steps(get_the_ID()));
						// $course_progress = learndash_user_get_course_progress(get_current_user_id(),get_the_ID());
						$course_id = get_the_ID();
						$user_id = get_current_user_id();
						$course_progress = learndash_course_progress(array(
							'user_id' => $user_id,
							'course_id' => $course_id,
							'array' => true
						));

						// Get progress percentage and last activity
						$progress_percentage = isset($course_progress['percentage']) ? intval($course_progress['percentage']) : 0;
						$button_text = $progress_percentage > 0 ? "In Progress" : "Start Course"; // Change button text based on progress
				
						$course_author_id = get_post_field('post_author', $course_id);
						$course_author = get_userdata($course_author_id);
						$author_display_name = $course_author->display_name;
						$author_profile_url = bp_core_get_user_domain($course_author_id);
						$author_avatar = get_avatar($course_author_id, 80);
						?>
						<li class="bb-course-item-wrap">
							<div class="bb-cover-list-item">
								<div class="bb-course-cover">
									<a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>" class="bb-cover-wrap">
										<div class="ld-status ld-status-progress ld-primary-background">
											<?php echo $button_text; ?>
										</div>
									</a>
								</div>
								<div class="bb-card-course-details bb-card-course-details--hasAccess">
									<div class="course-lesson-count"><?php echo $steps_count . ' Lessons'; ?> Lessons</div>
									<h2 class="bb-course-title">
										<a class="truncate-title" title="<?php the_title(); ?>"
											href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h2>
									<div class="bb-course-meta">
										<a class="item-avatar" href="<?php echo esc_url($author_profile_url); ?>">
											<?php echo $author_avatar; ?>
										</a>
										<strong>
											<a href="<?php echo esc_url($author_profile_url); ?>">
												<?php echo esc_html($author_display_name); ?>
											</a>
										</strong>
									</div>
									<div class="course-progress-wrap">
										<div class="ld-progress ld-progress-inline">
											<div class="ld-progress-bar">
												<div class="ld-progress-bar-percentage ld-secondary-background"
													style="width:<?php echo $progress_percentage; ?>%"></div>
											</div>
											<div class="ld-progress-stats">
												<div class="ld-progress-percentage ld-secondary-color course-completion-rate">
													<?php echo $progress_percentage; ?>% Complete
												</div>

											</div> <!--/.ld-prog  ess-stats-->
										</div> <!--/.ld-progress-->
									</div>
								</div>
							</div>
						</li>
						<?php
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