<?php
/*
Template Name: Reviews
*/

acf_form_head();
get_header();

// Set up the email recipient
$recipient_email = 'support@ecomschool.co.il'; // Replace with your desired email address
$errors = [];
if (is_user_logged_in()) {
    $current_user_id = get_current_user_id();
    $already_submitted = get_user_meta($current_user_id, 'has_submitted_feedback', true);

    if ($already_submitted) {
        // Set error message instead of redirecting
        $errors[] = 'כבר מילאתם את הטופס, בשביל למלא שוב יש לצור קשר';
    }
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $already_submitted = get_user_meta($current_user_id, 'has_submitted_feedback', true);

        if ($already_submitted) {
            $errors[] = 'כבר מילאתם את הטופס, בשביל למלא שוב יש לצור קשר';
        } else {
            // Sanitize and fetch form data with `isset()` checks
            $student_name = isset($_POST['student_name']) ? sanitize_text_field($_POST['student_name']) : '';
            $satisfaction_teaching = isset($_POST['satisfaction_teaching']) ? sanitize_text_field($_POST['satisfaction_teaching']) : '';
            $satisfaction_answers = isset($_POST['satisfaction_answers']) ? sanitize_text_field($_POST['satisfaction_answers']) : '';
            $satisfaction_concepts = isset($_POST['satisfaction_concepts']) ? sanitize_text_field($_POST['satisfaction_concepts']) : '';
            $improvement_teaching = isset($_POST['improvement_teaching']) ? sanitize_textarea_field($_POST['improvement_teaching']) : '';
            $satisfaction_broadcast = isset($_POST['satisfaction_broadcast']) ? sanitize_text_field($_POST['satisfaction_broadcast']) : '';
            $satisfaction_questions = isset($_POST['satisfaction_questions']) ? sanitize_text_field($_POST['satisfaction_questions']) : '';
            $satisfaction_flow = isset($_POST['satisfaction_flow']) ? sanitize_text_field($_POST['satisfaction_flow']) : '';
            $improvement_learning = isset($_POST['improvement_learning']) ? sanitize_textarea_field($_POST['improvement_learning']) : '';
            $satisfaction_professionalism = isset($_POST['satisfaction_professionalism']) ? sanitize_text_field($_POST['satisfaction_professionalism']) : '';
            $satisfaction_progress = isset($_POST['satisfaction_progress']) ? sanitize_text_field($_POST['satisfaction_progress']) : '';
            $improvement_material = isset($_POST['improvement_material']) ? sanitize_textarea_field($_POST['improvement_material']) : '';
            $watch_recordings = isset($_POST['watch_recordings']) ? sanitize_text_field($_POST['watch_recordings']) : '';
            $improvement_general = isset($_POST['improvement_general']) ? sanitize_textarea_field($_POST['improvement_general']) : '';

            // Prepare the email content
            $subject = "New Student Feedback from $student_name";
            $message = "
                Student Name: $student_name\n\n
                ---- מרצה ----\n
                מהי מידת שביעות רצונך מהעברת החומר: $satisfaction_teaching\n
                מהי מידת שביעות רצונך מתשובות המרצים: $satisfaction_answers\n
                מהי מידת שביעות רצונך מהבנת המושגים: $satisfaction_concepts\n
                נקודות לשיפור בנושא מרצה: $improvement_teaching\n\n
                ---- אופן הלמידה ----\n
                מידת שביעות רצונך מאיכות השידור: $satisfaction_broadcast\n
                מידת שביעות רצונך ממהלך השאלות: $satisfaction_questions\n
                מידת שביעות רצונך מזרימת השידור: $satisfaction_flow\n
                נקודות לשיפור בנושא למידה: $improvement_learning\n\n
                ---- החומר המקצועי בקורס ----\n
                מידת שביעות רצונך מרמה מקצועית: $satisfaction_professionalism\n
                מידת שביעות רצונך מקצב ההתקדמות: $satisfaction_progress\n
                נקודות לשיפור בנושא חומר מקצועי: $improvement_material\n\n
                ---- נושאים נוספים ----\n
                האם צופה בהקלטות קודמות: $watch_recordings\n
                נקודות לשיפור כלליות: $improvement_general
            ";
            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            // Send the email
            wp_mail($recipient_email, $subject, $message, $headers);

            // Mark feedback as submitted for the user
            update_user_meta($current_user_id, 'has_submitted_feedback', true);

            // Redirect to avoid duplicate submissions on refresh
            // another option = wp_redirect(site_url('/thank-you')); // Change '/thank-you' to your thank you page URL
            wp_redirect(esc_url(bp_core_get_user_domain($current_user_id)));
            exit;
        }
    } else {
        $errors[] = 'Please log in to submit feedback.';
    }
}
?>

<div class="page-container">
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
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- Progress Indicator -->
                <div class="progress-indicator">
                    <span id="step-counter">1</span> / 4
                </div>

                <!-- Multi-Step Form -->
                <form method="POST" class="multi-step-form">
                    <div class="student-info active" >
                        <label for="student_name">שם מלא של התלמיד:</label>
                        <div class="form-input-container">
                            <input type="text" id="student_name" name="student_name" placeholder="שם מלא" required>
                        </div>
                        
                    </div>
                    <!-- Section 1: מרצה -->
                    <div class="form-section active" id="section-1">
                        <label for="satisfaction_teaching">מהי מידת שביעות רצונך מהעברת החומר הנלמד על ידי
                            המרצים?</label>
                        <select name="satisfaction_teaching" id="satisfaction_teaching" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="satisfaction_answers">מהי מידת שביעות רצונך מהתשובות של המרצים על השאלות
                            בשיעור?</label>
                        <select name="satisfaction_answers" id="satisfaction_answers" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="satisfaction_concepts">מהי מידת שביעות רצונך מהבנת המושגים הנלמדים על ידי המרצים
                            בשיעור?</label>
                        <select name="satisfaction_concepts" id="satisfaction_concepts" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="improvement_teaching">איזה נקודות לשיפור היית רוצה להוסיף בנושאים הקשורים
                            למרצים?</label>
                        <textarea name="improvement_teaching" id="improvement_teaching"
                            placeholder="רשום כאן את תשובתך"></textarea>

                        <button type="button" class="next-button">Next</button>
                    </div>

                    <!-- Section 2: אופן הלמידה -->
                    <div class="form-section" id="section-2">
                        <label for="satisfaction_broadcast">מהי מידת שביעות רצונך מאיכות השידור?</label>
                        <select name="satisfaction_broadcast" id="satisfaction_broadcast" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="satisfaction_questions">מהי מידת שביעות רצונך מהאופן בו מתנהל השאלות מול
                            המרצים?</label>
                        <select name="satisfaction_questions" id="satisfaction_questions" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="satisfaction_flow">מהי מידת שביעות רצונך מהאופן שבו מולקט השידור?</label>
                        <select name="satisfaction_flow" id="satisfaction_flow" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="improvement_learning">האם אתה מעוניין להוסיף נקודות לשיפור בנושאים הקשורים
                            לשידור?</label>
                        <textarea name="improvement_learning" id="improvement_learning"
                            placeholder="רשום כאן את תשובתך"></textarea>

                        <button type="button" class="prev-button">Previous</button>
                        <button type="button" class="next-button">Next</button>
                    </div>

                    <!-- Section 3: החומר המקצועי בקורס -->
                    <div class="form-section" id="section-3">
                        <label for="satisfaction_professionalism">מהי מידת שביעות רצונך מרמה המקצועית של השיעור?</label>
                        <select name="satisfaction_professionalism" id="satisfaction_professionalism" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="satisfaction_progress">מהי מידת שביעות רצונך מקצב ההתקדמות של החומר?</label>
                        <select name="satisfaction_progress" id="satisfaction_progress" required>
                            <option value="במידה רבה מאוד">במידה רבה מאוד</option>
                            <option value="במידה רבה">במידה רבה</option>
                            <option value="במידה בינונית">במידה בינונית</option>
                            <option value="במידה מעטה">במידה מעטה</option>
                            <option value="כלל לא">כלל לא</option>
                        </select>

                        <label for="improvement_material">האם אתה מעוניין להוסיף נקודות לשיפור בנושאים הקשורים לחומר
                            המקצועי?</label>
                        <textarea name="improvement_material" id="improvement_material"
                            placeholder="רשום כאן את תשובתך"></textarea>

                        <button type="button" class="prev-button">Previous</button>
                        <button type="button" class="next-button">Next</button>
                    </div>

                    <!-- Section 4: נושאים נוספים -->
                    <div class="form-section" id="section-4">
                        <label for="watch_recordings">במידה ויש, האם אתה צופה בהקלטות של השיעורים הקודמים בקורס
                            שלך?</label>
                        <select name="watch_recordings" id="watch_recordings" required>
                            <option value="כן">כן</option>
                            <option value="לא">לא</option>
                        </select>

                        <label for="improvement_general">האם אתה מעוניין להוסיף נקודות לשיפור בנושאים כללים?</label>
                        <textarea name="improvement_general" id="improvement_general"
                            placeholder="רשום כאן את תשובתך"></textarea>

                        <button type="button" class="prev-button">Previous</button>
                        <button type="submit">שליחה</button>
                    </div>
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
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/mini-logo.svg"
                            alt="Logo">
                    </div>
                    <a href="#" class="card-button">התחל ללמוד</a>
                    <div class="card-footer">
                        <p>7 ימי ניסיון חינם</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();
?>