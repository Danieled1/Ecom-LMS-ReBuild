<?php

$user_id = get_current_user_id();
$current_user = wp_get_current_user();
$user_link = function_exists('bp_core_get_user_domain') ? bp_core_get_user_domain($current_user->ID) : get_author_posts_url($current_user->ID);
$user_link_url = esc_url($user_link);
$display_name = function_exists('bp_core_get_user_displayname') ? bp_core_get_user_displayname($current_user->ID) : $current_user->display_name;
$is_admin = user_can($user_id, 'manage_options');

$courses_with_progress = array();

if (!$is_admin) {
	// Get enrolled courses for the user
	$enrolled_courses = learndash_user_get_enrolled_courses($user_id);

	// Loop through enrolled courses to find those with progress > 0%
	if (!empty($enrolled_courses)) {
		foreach ($enrolled_courses as $course_id) {
			// Get total steps in the course
			$total_steps = learndash_get_course_steps_count($course_id);

			// Get completed steps for the user in this course
			$completed_steps = learndash_course_get_completed_steps($user_id, $course_id);

			// Calculate the percentage
			if ($total_steps > 0) {
				$percentage = floor(($completed_steps / $total_steps) * 100);
			} else {
				$percentage = 0;
			}

			// Only include courses with progress > 0%
			if ($percentage > 0) {
				$courses_with_progress[] = array(
					'course_id' => $course_id,
					'title' => get_the_title($course_id),
					'percentage' => $percentage,
				);
			}
		}
	}
}
// Get the menu assigned to the 'buddypanel-loggedin' location
$locations = get_nav_menu_locations();
$menu_id = isset($locations['buddypanel-loggedin']) ? $locations['buddypanel-loggedin'] : 0;

// Fetch the menu items from the specified menu ID
$menu_items = $menu_id ? wp_get_nav_menu_items($menu_id) : [];
if (!is_array($menu_items)) {
	$menu_items = []; // Set to an empty array if not valid
}
foreach ($menu_items as $item) {
    if ($item->title === 'הפרופיל שלי') {
        $item->url = esc_url(bp_core_get_user_domain($current_user->ID)); // Force correct profile URL
    }
}
$available_icons = array(
	'bb-icon-l buddyboss bb-icon-book-open',
	'bb-icon-l buddyboss bb-icon-users',
	'bb-icon-l buddyboss bb-icon-tools',
	'bb-icon-l buddyboss bb-icon-briefcase',
	'bb-icon-l buddyboss bb-icon-article',
	'bb-icon-l buddyboss bb-icon-file-attach',
	'bb-icon-l buddyboss bb-icon-graduation-cap',
	'bb-icon-l buddyboss bb-icon-airplay',
);

$settings_icon_mapping = [
	'הפרופיל שלי' => 'bb-icon-user',
	'פניות ואישורים' => 'bb-icon-file-attach',
	'השמה' => 'bb-icon-briefcase',
	'קבוצות' => 'bb-icon-users',
	'בלוג איקום' => 'bb-icon-article',
	'משוב' => 'bb-icon-airplay',
	'ציונים' => 'bb-icon-book-open',
	'התנתק' => 'bb-icon-sign-out',
	'הקורס שלי' => 'bb-icon-graduation-cap',
	'תמיכה מקצועית' => 'bb-icon-tools',

];


?>
<style>
	.testtest {
		display: flex;
		width: 200px;
		padding: 20px 0px;
		flex-direction: column;

		gap: 8px;
	}


	/* user-link */
	#menu-item-4747>a {
		margin-bottom: 0 !important;
		align-items: center !important;
		padding: 0 !important;
		height: 72px !important;
		border-bottom: none !important;
		cursor: default;
	}
	#menu-item-4747>a:hover{
		color:none;
		background: none;
	}
	.bb-template-v2 ul.buddypanel-menu>li>a>img:first-child {
		margin: 0 10px !important;
	}

	.side-panel-inner {
		padding-top: 0 !important;
	}

	/* div - wrapper */
	body>aside>div>nav>div>div {
		/* border-bottom: 1px solid #d6d9dd; */
	}

	/* div sub-menu-inner */
	body>aside>div>nav>div>div>ul {
		margin: 0;
	}

	/* Default state: Set text color to gray */
	.buddypanel-menu .bb-menu-item {
		color: #717171 !important;
		font-weight: 400 !important;
		font-style: normal;
		line-height: normal;
		height: 35px !important;
		/* gap: 8px; */
	}

	/* Default state: Ensure icons inherit the gray color */
	.buddypanel-menu .bb-menu-item i {
		color: inherit !important;

	}

	/* Active state: Set text color to purple and background to #F2F2F2 */
	.buddypanel-menu .current-menu-item .bb-menu-item {
		color: var(--purple, #6836FF) !important;
		background-color: #F2F2F2 !important;
	}

	/* Active state: Ensure icons inherit the purple color */
	.buddypanel-menu .current-menu-item .bb-menu-item i {
		color: inherit !important;
	}

	#menu-item-last-courses {
		margin: 0 !important;
		cursor: pointer;
	}

	/* Open state: Set text color to purple */
	#menu-item-last-courses>a.bb-menu-item {
		color: var(--purple, #6836FF) !important;

	}

	.bb-template-v2 .buddypanel .side-panel-menu .sub-menu a {
		padding-right: 10px !important;
		height: 35px !important;

	}

	hr {
		margin: 4px 0;
		opacity: 0.6;
	}

	/* Active state for entire menu structure */
	.buddypanel-menu .current-menu-item,
	.buddypanel-menu .current-menu-ancestor {
		background-color: #F2F2F2 !important;
		color: var(--purple, #6836FF) !important;
	}

	/* Adjust submenu item text alignment */
	.buddypanel .side-panel-menu .sub-menu li a {
		display: flex;
		align-items: center;
		justify-content: space-between;
		/* Ensures the icon and text are aligned well */
		width: 91% !important;
		padding: 0 10px !important;
		height: 35px;
	}

	.buddypanel-open .buddypanel-menu .sub-menu.bb-open {
		padding-top: 38px;
	}

	.buddypanel .site-header-container {
		display: none;
	}

	#toggle-sidebar {
		position: fixed;
		z-index: 1234;
		width: 40px;
		height: 40px;
		display: flex;
		justify-content: center;
		align-items: center;
		border: 1px #FFF solid;
		border-radius: 10px;
		top: 116px;
		right: 220px;
		transition: right 0.3s ease-in-out;
		color: var(--Gray-3, #828282) ;
		background: var(--WHITE, #FFF) !important;
		text-align: center;
		font-size: 14px;
		font-weight: 900;
		
	}

	body {
		transition: margin 0.1s ease-in-out;
	}

	.buddypanel {
		transition: display 0.3s ease-in;
	}
</style>


<button id="toggle-sidebar" class="buddypanel" >&lt;</button>
<aside class="buddypanel buddypanel--toggle-off">

	<div class="side-panel-inner">
		<nav class="side-panel-menu-container">
			<div class="sub-menu">

				<!-- Courses Menu (No separate dropdown class, reusing existing menu classes) -->
				<ul id="buddypanel-menu" class="borders buddypanel-menu side-panel-menu">
					<!--Username part - uncomment to return it - 17/11/24  -->
				<!-- <li id="menu-item-<?php echo esc_attr($user_id); ?>" class="user-not-active">
						<a class="user-link">
							<?php echo get_avatar(get_current_user_id(), 100); ?>
							<span>
								<?php if (function_exists('bp_is_active') && function_exists('bp_activity_get_user_mentionname')): ?>
									<span
										class="user-mention"><?php echo esc_html(bp_activity_get_user_mentionname($current_user->ID)) . "@"; ?></span>
								<?php else: ?>
									<span
										class="user-mention"><?php echo esc_html($current_user->user_login) . "@"; ?></span>
								<?php endif; ?>
							</span>
						</a>
					</li> -->
					<hr>
					<?php foreach ($menu_items as $item): ?>
						<?php if (in_array($item->title, ['הקורס שלי', 'תמיכה מקצועית'])): ?>
							<li id="menu-item-<?php echo esc_attr($item->ID); ?>"
								class="menu-item menu-item-type-post_type menu-item-object-page">
								<a href="<?php echo esc_url($item->url); ?>" class="bb-menu-item" data-balloon-pos="right"
									data-balloon="<?php echo esc_attr($item->title); ?>">
									<i class="_mi _before <?php echo esc_attr($settings_icon_mapping[$item->title]); ?>"
										aria-hidden="true"></i>
									<span><?php echo esc_html($item->title); ?></span>
								</a>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
					<!-- Parent Menu Item for Last Courses -->
					<?php if (!$is_admin && !empty($courses_with_progress)): ?>
						<hr>
						<li id="menu-item-last-courses" class="menu-item menu-item-has-children">
							<a href="#" class="bb-menu-item dropdown-toggle " data-balloon-pos="right"
								data-balloon="קורסים אחרונים">
								<span>קורסים אחרונים</span>
							</a>
							<!-- Submenu with Courses -->
							<ul class="sub-menu bb-open">
								<?php foreach ($courses_with_progress as $course):
									// Randomly pick an icon class from the available icons
									$random_icon = $available_icons[array_rand($available_icons)];
									?>
									<li id="menu-item-<?php echo esc_attr($course['course_id']); ?>"
										class="menu-item menu-item-type-post_type menu-item-object-page">
										<a href="<?php echo get_permalink($course['course_id']); ?>" class="bb-menu-item"
											data-balloon-pos="right" data-balloon="<?php echo esc_attr($course['title']); ?>">
											<!-- Random Course Icon -->
											<i class="_mi _before <?php echo esc_attr($random_icon); ?>" aria-hidden="true"></i>
											<!-- Course Title -->
											<span><?php echo esc_html($course['title']); ?></span>
											<!-- Course Progress -->
											<span class="course-progress"><?php echo esc_html($course['percentage']); ?>%</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endif; ?>
					<hr>
					<li id="menu-item-last-courses" class="menu-item menu-item-has-children">
						<a href="#" class="bb-menu-item dropdown-toggle " data-balloon-pos="right"
							data-balloon="הגדרות">
							<span>הגדרות</span>
						</a>
						<ul class="sub-menu bb-open">
							<?php foreach ($menu_items as $item): ?>
								<?php if (in_array($item->title, ['הפרופיל שלי', 'פניות ואישורים', 'השמה', 'קבוצות'])): ?>			
									<li id="menu-item-<?php echo esc_attr($item->ID); ?>"
										class="menu-item menu-item-type-post_type menu-item-object-page">
										<a href="<?php echo esc_url($item->url); ?>" class="bb-menu-item"
											data-balloon-pos="right" data-balloon="<?php echo esc_attr($item->title); ?>">
											<i class="_mi _before <?php echo esc_attr($settings_icon_mapping[$item->title]); ?>"
												aria-hidden="true"></i>
											<span><?php echo esc_html($item->title); ?></span>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</li>
					<hr>
					<?php foreach ($menu_items as $item): ?>
						<?php if (in_array($item->title, ['בלוג איקום', 'משוב', 'ציונים', 'התנתק'])): ?>
							<li id="menu-item-<?php echo esc_attr($item->ID); ?>"
								class="menu-item menu-item-type-post_type menu-item-object-page">
								<a href="<?php echo esc_url($item->url); ?>" class="bb-menu-item" data-balloon-pos="right"
									data-balloon="<?php echo esc_attr($item->title); ?>">
									<i class="_mi _before <?php echo esc_attr($settings_icon_mapping[$item->title]); ?>"
										aria-hidden="true"></i>
									<span><?php echo esc_html($item->title); ?></span>
								</a>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>


				</ul>
			</div>

		</nav>
	</div>
</aside>

<script defer>
	document.addEventListener('DOMContentLoaded', function () {
		const menuItem = document.querySelector('#menu-item-last-courses');
		const dropdown = menuItem.querySelector('.sub-menu');
		const arrowIcon = menuItem.querySelector('.bb-icon-angle-down');
		const toggleButton = document.getElementById('toggle-sidebar');
		const buddypanel = document.querySelector('body > aside');
		const siteContent = document.querySelector('.bb-buddypanel:not(.activate) .site, .bb-buddypanel:not(.register) .site');
		const sidebarOpen = document.querySelector('.buddypanel-open:not(.register) .site');
		const sidebarNotOpen = document.querySelector('bb-buddypanel:not(.activate) .site, .bb-buddypanel:not(.register) .site');
		const originalMarginLeft = document.body.style.marginLeft;
		const originalMarginRight = document.body.style.marginRight;
		// const buddypanelMenu = document.querySelector('#buddypanel-menu');
		// buddypanelMenu.addEventListener('click', function (e) {
		// 	// Check if the clicked element is an anchor (<a>) or a menu item (<li>)
		// 	let clickedItem = e.target.closest('li');

		// 	// If no <li> was clicked, exit
		// 	if (!clickedItem) return;

		// 	// Handle parent and submenu items
		// 	if (clickedItem.classList.contains('menu-item')) {
		// 	// Remove 'current-menu-item' class from all items
		// 	buddypanelMenu.querySelectorAll('.menu-item').forEach(function (item) {
		// 		item.classList.remove('current-menu-item');
		// 	});

		// 	// Add 'current-menu-item' class to the clicked item or its parent
		// 	clickedItem.classList.add('current-menu-item');
		// 	}
			
		// });

		const buddypanelMenu = document.querySelector('#buddypanel-menu');
  
		// Function to mark the current menu item based on URL
		function highlightCurrentMenuItem() {
    const currentUrl = window.location.href; // Get current URL
    const buddypanelMenu = document.querySelector('#buddypanel-menu');

    buddypanelMenu.querySelectorAll('a').forEach(function (menuLink) {
        const menuItem = menuLink.closest('li');
        if (menuItem.classList.contains('user-not-active')) {
            return;
        }

        // Clear previous highlighting
        menuItem.classList.remove('current-menu-item');

        // Compare the exact URL match or use a stricter comparison
        if (menuLink.href === currentUrl) {
            menuItem.classList.add('current-menu-item');
        }
    });
	
}
		// Run the function on page load to highlight the correct menu item
		highlightCurrentMenuItem();
		function checkViewport() {
			if (window.innerWidth < 800) {
				toggleButton.style.display = 'none'; // Show button on mobile
			} else {
				toggleButton.style.display = 'flex'; // Hide button on larger screens
			}
		}
		checkViewport();
		window.addEventListener('resize', checkViewport);
	

		// Function to apply styles
		function applyStyles() {
			buddypanel.style.opacity = "0";
			buddypanel.style.visibility = "hidden";
			document.body.classList.remove('bb-buddypanel');
			document.body.classList.remove('buddypanel-open');

			// Force reflow to ensure styles are applied immediately
			void document.body.offsetWidth;

			// Use !important to override existing styles
			document.body.style.setProperty('margin-left', '0', 'important');
			document.body.style.setProperty('margin-right', '0', 'important');
			toggleButton.style.right = '0'; // Set the toggle button position to 0
			toggleButton.innerHTML = "&gt;";


		}
		function restoreStyles() {
			buddypanel.style.opacity = "1";
			buddypanel.style.visibility = "visible";
			document.body.style.setProperty('margin-left', originalMarginLeft, 'important');
			document.body.style.setProperty('margin-right', originalMarginRight, 'important');
			document.body.classList.add('bb-buddypanel');
			document.body.classList.add('buddypanel-open');
			toggleButton.style.right = '220px'; // Set the toggle button position to 0
			toggleButton.innerHTML = "&lt;";
		}

		// Add event listener to toggle button
		toggleButton.addEventListener('click', function () {
			const isOpen = buddypanel.style.visibility !== "hidden"; // Check if sidebar is open
			if (isOpen) {
				applyStyles(); // Hide sidebar
			} else {
				restoreStyles(); // Show sidebar and restore styles
			}
		});

		// Ensure the section starts open
		if (dropdown) {
			menuItem.classList.add('open');
			dropdown.classList.add('bb-open');
			arrowIcon.classList.add('bs-submenu-open');  // Initially set the arrow to the open state
		}

		// Toggle functionality when clicking the entire menu item
		menuItem.addEventListener('click', function (event) {
			event.preventDefault();
			const isOpen = menuItem.classList.toggle('open');
			dropdown.classList.toggle('bb-open', isOpen);
			arrowIcon.classList.toggle('bs-submenu-open', isOpen); // Rotate the arrow on toggle
		});
		const profileTab = document.querySelector('#user-xprofile > div');
		console.log("TEST TEST", profileTab);
    if (profileTab) {
        profileTab.innerHTML = 'פרופיל'; // Translated to Hebrew
    }

	});


</script>