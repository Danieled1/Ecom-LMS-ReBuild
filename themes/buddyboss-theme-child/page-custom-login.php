<?php
/* Template Name: Custom Login */

// Simple debug message to check accessibility
if (!is_user_logged_in()) {
    error_log('Custom Login Page Accessed by Non-Logged-In User');
} else {
    error_log('Custom Login Page Accessed by Logged-In User');
}
$error_message = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST = " .print_r($_POST, true));
    $creds = array(
        'user_login'    => $_POST['log'],
        'user_password' => $_POST['pwd'],
        'remember'      => isset($_POST['rememberme'])
    );
    error_log( "CREDS = ".print_r($creds, true));
    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        // Translate common error messages to Hebrew
        switch ($user->get_error_code()) {
            case 'invalid_username':
                $error_message = 'שם המשתמש שגוי. נא לנסות שוב.';
                break;
            case 'incorrect_password':
                $error_message = 'הסיסמה שגויה. נא לנסות שוב.';
                break;
            case 'empty_username':
                $error_message = 'שדה שם המשתמש ריק. נא למלא את כל השדות הנדרשים.';
                break;
            case 'empty_password':
                $error_message = 'שדה הסיסמה ריק. נא למלא את כל השדות הנדרשים.';
                break;
            default:
                error_log(" User error code: " .$user->get_error_code());
                $error_message = 'אירעה שגיאה לא ידועה. נא לנסות שוב.';
                break;
        }
    } else {
        $slug = $user->user_nicename;   // Member profile page URL construct out of the user's nicename

        error_log('User Slug: ' . $user->user_nicename);
        $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/members/' . $slug . '/');
        
        wp_redirect($redirect_to);

        exit;
    }
}
//  
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error_message)) : ?>
    <script>
        console.log("<?php echo $error_message; ?>");
        document.addEventListener("DOMContentLoaded", function() {
            const errorContainer = document.querySelector(".login-error");
            errorContainer.innerHTML = "<?php echo $error_message; ?>";
            errorContainer.style.display = "block";
        });
    </script>
<?php endif; 

?>

<?php wp_head(); ?>
<div class="login-page">
    <div class="right-container">
        <div class="login-container flex-column-center ">
            <img class="logo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/logo.svg" alt="">
            <form method="post" action="">
                <div class="inputs-container flex-column-center ">
                <div class="signup-frame">
                    <img class="signup-frame-bg" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/rectangle_52_x2.svg" alt="Description">
                    <span class="signup-frame-text">התחברות</span>
                </div>
                    <div class="input-container">
                        
                        <div class="email-icon"></div>
                        <input type="text" name="log" id="email" placeholder="כתובת דואר אלקטרוני">
                    </div>
                    <div class="input-container">
                        <div class="password-icon"></div>
                        <input type="password" name="pwd" id="password" placeholder="סיסמה">
                    </div>
                    <button class="custom-button primary icon-login">התחבר</button>
                    <div class="terms-container">
                        <a href="#privacy-modal">תנאי השימוש</a> -ו
                        <a href="#terms-modal">תקנון אתר</a>
                    </div>
                </div>
                <div class="login-error" style="display: none;"></div>

            </form>
            <div class="help-container">
                <h1>צריכים עזרה ?</h1>
                <p>שלחו הודעה ונענה בהקדם</p>
                <div class="input-container">
                    <button class="custom-button secondary icon-help"><span>לחצו לעזרה</span></button>
                </div>
            </div>
        </div>

    </div>
    <div class="left-container">
        
    </div>

</div>
