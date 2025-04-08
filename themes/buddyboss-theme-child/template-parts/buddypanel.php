<?php
/**
 * BuddyPanel & Instructor Dashboard Integration
 */


$user_id     = get_current_user_id();
$current_user = wp_get_current_user();
$current_url = $_SERVER['REQUEST_URI'];
$instructor_dashboard_slug = '/instructor-dashboard';
$is_instructor_dashboard = strpos($current_url, $instructor_dashboard_slug);
if ($is_instructor_dashboard !== false) {
    return; 
}
$user_link   = function_exists('bp_core_get_user_domain') 
    ? bp_core_get_user_domain($current_user->ID) 
    : get_author_posts_url($current_user->ID);
$user_link_url  = esc_url($user_link);
$is_admin       = user_can($user_id, 'manage_options');

$display_name   = function_exists('bp_core_get_user_displayname') 
    ? bp_core_get_user_displayname($current_user->ID) 
    : $current_user->display_name;


$instructor_dashboard_url = site_url('/instructor-dashboard');
// $extra_courses_tab_url = 
$allowed_roles = ['wdm_instructor', 'instructor', 'manage_options']; // Extend as needed
$has_instructor_access = false;
foreach ($allowed_roles as $role) {
    if (user_can($user_id, $role)) {
        $has_instructor_access = true;
        error_log("User has access due to role: " . $role);
        break;
    }
}


$instructor_menu_item = $has_instructor_access ? (object) [
    'ID'      => 'instructor-dashboard',
    'title'   => 'ניהול מרצה',
    'url'     => $instructor_dashboard_url,
    'classes' => 'bb-menu-item instructor-dashboard'
] : null;

// 3. Gather "Courses With Progress" data (for non-admin users) = 
$courses_with_progress = [];
if (!$is_admin) {
    $enrolled_courses = learndash_user_get_enrolled_courses($user_id);
    if (!empty($enrolled_courses)) {
        foreach ($enrolled_courses as $course_id) {
            $total_steps     = learndash_get_course_steps_count($course_id);
            $completed_steps = learndash_course_get_completed_steps($user_id, $course_id);
            $percentage      = ($total_steps > 0) 
                ? floor(($completed_steps / $total_steps) * 100) 
                : 0;
            if ($percentage > 0) {
                $courses_with_progress[] = [
                    'course_id'  => $course_id,
                    'title'      => get_the_title($course_id),
                    'percentage' => $percentage,
                ];
            }
        }
    }
}

// 4. Fetch BuddyPanel Menu Items
$locations = get_nav_menu_locations();
$menu_id   = isset($locations['buddypanel-loggedin']) ? $locations['buddypanel-loggedin'] : 0;
$menu_items = $menu_id ? wp_get_nav_menu_items($menu_id) : [];

if (!is_array($menu_items)) {
    $menu_items = [];
}
if ($instructor_menu_item) {
    $menu_items[] = $instructor_menu_item;
}
// 5. Filter & Adjust Menu Items in One Pass
//    We'll group them into categories to reduce repeated loops later
$menu_groups = [
    'main'     => [], // "הקורס שלי", "תמיכה מקצועית", "ניהול מרצה" 
    'settings' => [], // "הפרופיל שלי", "פניות ואישורים", "השמה", "קבוצות"
    'footer'   => [], // "בלוג איקום", "משוב", "ציונים", "התנתק"
];

// Adjust the URL for "הפרופיל שלי" and categorize
foreach ($menu_items as $item) {
	if (empty($item) || !isset($item->title)) {
        error_log("Skipping invalid menu item.");
        continue; // Skip invalid items
    }
    if ($item->title === 'הפרופיל שלי') {
        $item->url = esc_url(bp_core_get_user_domain($current_user->ID));
    }
    // Categorize by title - Removed 'בלוג איקום' and , 'קבוצות' - temp
    if (in_array($item->title, ['הפרופיל שלי','ניהול מרצה','הקורס שלי'])) {
        $menu_groups['main'][] = $item;
    } elseif (in_array($item->title, [ 'תמיכה מקצועית','פניות ואישורים', 'השמה', 'ציונים'])) {
        $menu_groups['settings'][] = $item;
    } elseif (in_array($item->title, ['משוב' , 'התנתק'])) {
        $menu_groups['footer'][] = $item;
    }
}
function clean_and_truncate_course_title($title, $maxWords = 3) {
    $wordsToRemove = ['קורס', 'digital', 'live']; // Words to remove
    $words = explode(' ', strtolower($title)); // Convert title to lowercase and split into words

    $filteredWords = array_filter($words, function($word) use ($wordsToRemove) {
        return !in_array($word, $wordsToRemove); // Remove word if it's in the removal list
    });

    $result = array_slice($filteredWords, 0, $maxWords); // Get the first $maxWords words
    $final_title = implode(' ', $result);
    return strtoupper($final_title); // Combine into a string

}

// error_log("Processing menu items. main=" . count($menu_groups['main']) . 
//           ", settings=" . count($menu_groups['settings']) . 
//           ", footer=" . count($menu_groups['footer']));

/**
 * $available_icons and $settings_icon_mapping from your existing code
 */
$available_icons = [
    'bb-icon-l buddyboss bb-icon-book-open',
    'bb-icon-l buddyboss bb-icon-users',
    'bb-icon-l buddyboss bb-icon-tools',
    'bb-icon-l buddyboss bb-icon-briefcase',
    'bb-icon-l buddyboss bb-icon-article',
    'bb-icon-l buddyboss bb-icon-file-attach',
    'bb-icon-l buddyboss bb-icon-graduation-cap',
    'bb-icon-l buddyboss bb-icon-airplay',
    'bb-icon-l buddyboss bb-icon-l',
];

$settings_icon_mapping = [
    'הפרופיל שלי'  => 'bb-icon-user',
    'פניות ואישורים' => 'bb-icon-file-attach',
    'השמה'       => 'bb-icon-briefcase',
    'קבוצות'      => 'bb-icon-users',
    'בלוג איקום'   => 'bb-icon-article',
    'משוב'        => 'bb-icon-airplay',
    'ציונים'      => 'bb-icon-book-open',
    'התנתק'       => 'bb-icon-sign-out',
    'הקורס שלי'    => 'bb-icon-graduation-cap',
    'תמיכה מקצועית' => 'bb-icon-tools',
	'ניהול מרצה'    => 'bb-icon-briefcase',

];

?>


<!-- <button id="toggle-sidebar" class="buddypanel">&lt;</button> -->
<aside class="buddypanel buddypanel--toggle-off">
    <div class="side-panel-inner">
        <nav class="side-panel-menu-container">
            <div class="sub-menu">
                <ul id="buddypanel-menu" class="borders buddypanel-menu side-panel-menu">
                    
                    <!-- MAIN MENU ITEMS -->
                    <hr>
                    <?php foreach ($menu_groups['main'] as $item): ?>
                        <li id="menu-item-<?php echo esc_attr($item->ID); ?>"
                            class="menu-item menu-item-type-post_type menu-item-object-page">
                            <a href="<?php echo esc_url($item->url); ?>" class="bb-menu-item" data-balloon-pos="right"
                               data-balloon="<?php echo esc_attr($item->title); ?>">
                                <i class="_mi _before <?php echo esc_attr($settings_icon_mapping[$item->title] ?? ''); ?>"
                                   aria-hidden="true"></i>
                                <span><?php echo esc_html($item->title); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <!-- LAST COURSES for Non-Admins with progress -->
                    <?php if (!$is_admin && !empty($courses_with_progress)): ?>
                        <hr>
                        <li id="menu-item-last-courses" class="menu-item menu-item-has-children">
                            <a href="#" class="bb-menu-item dropdown-toggle" data-balloon-pos="right"
                               data-balloon="קורסים אחרונים">
                                <span>קורסים אחרונים</span>
                            </a>
                            <ul class="sub-menu bb-open">
                                <?php foreach ($courses_with_progress as $course):
                                    $random_icon = $available_icons[array_rand($available_icons)];
                                    $truncated_title = clean_and_truncate_course_title($course['title'])
                                ?>
                                    <li id="menu-item-<?php echo esc_attr($course['course_id']); ?>"
                                        class="menu-item menu-item-type-post_type menu-item-object-page">
                                        <a href="<?php echo get_permalink($course['course_id']); ?>" class="bb-menu-item"
                                           data-balloon-pos="right" data-balloon="<?php echo esc_attr($course['title']); ?>">
                                            <i class="_mi _before <?php echo esc_attr($random_icon); ?>" aria-hidden="true"></i>
                                            <span><?php echo esc_html($truncated_title); ?></span>
                                            <span class="course-progress"><?php echo esc_html($course['percentage']); ?>%</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- SETTINGS MENU ITEMS -->
                    <hr>
                    <li id="menu-item-last-courses" class="menu-item menu-item-has-children">
                        <a href="#" class="bb-menu-item dropdown-toggle" data-balloon-pos="right"
                           data-balloon="הגדרות">
                            <span>הגדרות</span>
                        </a>
                        <ul class="sub-menu bb-open">
                            <?php foreach ($menu_groups['settings'] as $item): ?>
                                <li id="menu-item-<?php echo esc_attr($item->ID); ?>"
                                    class="menu-item menu-item-type-post_type menu-item-object-page">
                                    <a href="<?php echo esc_url($item->url); ?>" class="bb-menu-item"
                                       data-balloon-pos="right" data-balloon="<?php echo esc_attr($item->title); ?>">
                                        <i class="_mi _before <?php echo esc_attr($settings_icon_mapping[$item->title] ?? ''); ?>"
                                           aria-hidden="true"></i>
                                        <span><?php echo esc_html($item->title); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <!-- FOOTER MENU ITEMS -->
                    <hr>
                    <?php foreach ($menu_groups['footer'] as $item): ?>
                        <li id="menu-item-<?php echo esc_attr($item->ID); ?>"
                            class="menu-item menu-item-type-post_type menu-item-object-page">
                            <a href="<?php echo esc_url($item->url); ?>" class="bb-menu-item" 
                               data-balloon-pos="right" data-balloon="<?php echo esc_attr($item->title); ?>">
                                <i class="_mi _before <?php echo esc_attr($settings_icon_mapping[$item->title] ?? ''); ?>" 
                                   aria-hidden="true"></i>
                                <span><?php echo esc_html($item->title); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>
        </nav>
    </div>
</aside>
