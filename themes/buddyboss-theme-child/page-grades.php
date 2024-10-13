<?php
/*
Template Name: Grades1
*/
acf_form_head();

get_header();
$current_user = wp_get_current_user();


?>
<div id="primary" class="content-area bb-grid-cell">
    <main id="main" class="site-main">
        <!-- Header Section with User Info and General Instructions -->
        <div class="section page-grades-header">
            <div class="user-info">
                <?php echo get_avatar($current_user->ID); ?>
                <h1 class="bb-profile-title"><?php echo $current_user->display_name; ?></h1>
            </div>
            <div class="vertical-line"></div> <!-- separator -->
            <div class="instructions" direction="rtl">
                <h1 class="bb-section-title">דף ציונים</h1>
                
                <p class="bb-section-description">כאן תוכלו לעקוב אחר הציונים שלכם עבור מבחנים, פרויקטים ועבודות שהוגשו</p>
            </div>
        </div>
        <div class="section">
        <div id="ticketHistory" class="ticket-history hidden">
                <table id="gradesTable" class="grades-table">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name">Name</th>
                            <th scope="col" class="manage-column column-type">Type</th>
                            <th scope="col" class="manage-column">Score</th>
                            <th scope="col" class="manage-column">Status</th>
                            <th scope="col" class="manage-column">Deadline</th>
                            <th scope="col" class="manage-column">Feedback</th>
                            <th scope="col" id="modified_time" class="manage-column">Last Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- Grades will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php
get_footer();
?>