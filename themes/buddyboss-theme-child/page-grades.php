<?php
/*
Template Name: Grades1
*/

?>
<style>
    #gradesTable,
    #gradesTable thead,
    #gradesTable tbody,
    #gradesTable tr,
    #gradesTable th,
    #gradesTable td {
        all: unset;
    }
</style>
<?php
acf_form_head();

get_header();
$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();

// Logging timing for job_status
$start_time = microtime(true);
$job_status = wp_cache_get('job_status_' . $current_user_id, 'user_meta');
if ($job_status === false) {
    $job_status = get_field('job_status', 'user_' . $current_user_id);
    wp_cache_set('job_status_' . $current_user_id, $job_status, 'user_meta');
}
$end_time = microtime(true);
error_log('Time to fetch job_status: ' . ($end_time - $start_time) . ' seconds');

// Logging timing for avatar
$start_time = microtime(true);
$avatar = wp_cache_get('avatar_' . $current_user_id, 'user_meta');
if ($avatar === false) {
    $avatar = get_avatar($current_user_id, 100);
    wp_cache_set('avatar_' . $current_user_id, $avatar, 'user_meta');
}
$end_time = microtime(true);
error_log('Time to fetch avatar: ' . ($end_time - $start_time) . ' seconds');


?>
<div class="grades-page-content">
    <div class="header-image-wrapper">
        <div class="header-image">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/grades-banner-1.png"
                alt="Header Image" />
            <div class="header-gradient-overlay"></div>
            <div class="header-text">
                <div class="header-placement-title">ציונים</div>
                <div class="header-placement-subtitle">
                    כאן תוכלו לעקוב אחר הציונים שלכם עבור מבחנים, פרויקטים ועבודות שהוגשו
                </div>
            </div>
        </div>
    </div>
    <div class="grades-table-container">
        <div class="grades-info">
            <div class="user-info">
                <a class="user-link">
                    <?php echo $avatar; ?>
                    <span>
                        <?php if (function_exists('bp_is_active') && function_exists('bp_activity_get_user_mentionname')): ?>
                            <span
                                class="user-name-grades"><?php echo esc_html(bp_activity_get_user_mentionname($current_user->ID)); ?></span>
                        <?php else: ?>
                            <span class="user-name-grades"> <?php echo esc_html($current_user->user_login); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
            </div>
            <div class="status-container">
                <?php displayHeaderWithIcon('chart-simple', 'סטטוס השמה'); ?>
                <div class="status-label">
                    <h4 id="resume-label"><?php echo $job_status['label']; ?></h4>
                </div>
            </div>
            <div class="total-grades">
                <p id="grades-text">מבחנים</p>
                <p id="completed-text">1/2</p>
            </div>

        </div>
        <table id="gradesTable">

        </table>
    </div>
</div>

<?php
get_footer();
?>