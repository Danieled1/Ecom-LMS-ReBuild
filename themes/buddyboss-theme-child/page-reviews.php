<?php
/*
Template Name: Reviews
*/

acf_form_head();
get_header();

$recipient_email = 'support@ecomschool.co.il'; // Replace with your needed email address
$errors = [];
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_user_id = get_current_user_id();
    // Sanitize and fetch form data with `isset()` checks - in later refactor create a function that checks each input
    $student_name = isset($_POST['student_name']) ? sanitize_text_field($_POST['student_name']) : '';
    $satisfaction_teaching = isset($_POST['satisfaction_teaching']) ? sanitize_text_field($_POST['satisfaction_teaching']) : '';
    $improvement_teaching = isset($_POST['improvement_teaching']) ? sanitize_textarea_field($_POST['improvement_teaching']) : '';
    $subject = "New Student Feedback from $student_name";
    $message = "
                Student Name: $student_name\n\n
                ---- מרצה ----\n
                מהי מידת שביעות רצונך מהעברת החומר: $satisfaction_teaching\n
                נקודות לשיפור בנושא מרצה: $improvement_teaching\n\n
            ";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    wp_mail($recipient_email, $subject, $message, $headers);
    wp_redirect(esc_url(bp_core_get_user_domain($current_user_id)));
    exit;
}
?>


<div class="reviews-page-content">
    <div class="header-image-wrapper">
        <div class="header-image">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/review-banner-1.png"
                alt="Header Image" />
            <div class="header-gradient-overlay"></div>
            <div class="header-text">
                <div class="header-placement-title" style="margin-bottom: 20px;">נשמח לשמוע את המשוב שלך</div>
                <div class="header-placement-subtitle">
                    דעתך חשובה לנו! נשמח לשמוע מה אהבת ומה אפשר לשפר.<br>
                    המשוב שלך יעזור לנו לשפר את השירותים שלנו ולעזור לאחרים.
                </div>
            </div>
        </div>
    </div>
    <div class="main-reviews-container">
        <div class="form-container">
            <!-- Multi-Step Form -->
            <form method="POST" class="multi-step-form" style="width: 100%;">
                <div class="student-info active">
                    <label for="student_name" class="input-text-style">שם מלא של התלמיד</label>
                    <div class="form-input-container">
                        <input type="text" id="student_name" class="input-text-style" name="student_name"
                            placeholder="שם מלא" required>
                    </div>
                </div>
                <h4 class="form-section-header">מרצה</h4>
                <div class="form-section active" id="section-1">
                    <label for="satisfaction_teaching" class="input-text-style">מהי מידת שביעות רצונך מהעברת החומר הנלמד
                        על ידי
                        המרצים?</label>
                    <div class="form-input-container">
                        <select name="satisfaction_teaching" id="satisfaction_teaching" class="input-text-style"
                            required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>
                    </div>
                    <label for="improvement_teaching" class="input-text-style">איזה נקודות לשיפור היית רוצה להוסיף
                        בנושאים הקשורים
                        למרצים?</label>
                    <div class="form-input-container">
                        <textarea name="improvement_teaching" id="improvement_teaching" class="input-text-style"
                            placeholder="רשום כאן את תשובתך"></textarea>
                    </div>

                </div>

                <button type="submit" id="form-submit-button">שליחה</button>
            </form>
        </div>

        <div class="extra-container">
            <div class="card-campaign">
                <div class="card-header-box">
                    <div class="card-side-color"></div>
                    <div class="card-headers">
                        <h3 class="card-title">Join full stack Challange</h3>
                        <p class="card-sub-text">במיוחד למסלול שלך!</p>
                    </div>
                </div>
                <div class="card-logo">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/mini-logo.svg" alt="Logo">
                </div>
                <a href="#" class="card-button">התחל ללמוד</a>
                <div class="card-footer">
                    <p>7 ימי ניסיון חינם</p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();
?>