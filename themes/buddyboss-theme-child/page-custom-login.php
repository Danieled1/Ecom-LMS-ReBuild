<?php
/* Template Name: Custom Login */

$error_message = ''; // Initialize error message variable

if (is_user_logged_in()) {
    $user = wp_get_current_user();
    
    if (in_array('administrator', $user->roles, true)) {
        wp_redirect(admin_url());
        exit;
    }

    if (in_array('instructor', $user->roles, true) || in_array('wdm_instructor', $user->roles, true)) {
        wp_redirect(site_url('/instructor-dashboard'));
        exit;
    }

    // Default redirect for students
    wp_redirect(home_url('/members/' . $user->user_nicename . '/'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $creds = array(
        'user_login'    => sanitize_text_field($_POST['log']),
        'user_password' => $_POST['pwd'],
        'remember'      => isset($_POST['rememberme'])
    );
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

         // Admin users need full authentication before being redirected
         if (in_array('administrator', $user->roles, true)) {
            wp_set_auth_cookie($user->ID, true);
            wp_redirect(admin_url()); 
            exit;
        }

        // Instructors should go to the instructor dashboard
        if (in_array('instructor', $user->roles, true) || in_array('wdm_instructor', $user->roles, true)) {
            wp_set_auth_cookie($user->ID, true);
            wp_redirect(site_url('/instructor-dashboard'));
            exit;
        }

        // Default: Send students to their profile page
        $redirect_to = home_url('/members/' . $user->user_nicename . '/');
        wp_redirect($redirect_to);
        exit;
        // v0.2
        // $user_roles = $user->roles; // Get user's roles
        // error_log('User Roles: ' . print_r($user_roles, true));

        // $redirect_to = home_url(); // Default fallback redirect

        // if (in_array('administrator', $user_roles)) {
        //     $redirect_to = admin_url(); // Redirect admin to WP dashboard
        // } elseif (in_array('wdm_instructor', $user_roles) || in_array('instructor', $user_roles)) {
        //     $redirect_to = site_url('/instructor-dashboard'); // Redirect instructors to their panel
        // } elseif (in_array('subscriber', $user_roles) || in_array('student', $user_roles)) {
        //     $redirect_to = site_url('/members/' . $user->user_nicename . '/'); // Redirect students to their profile
        // } 

        // error_log('Redirecting user to: ' . $redirect_to);
        // wp_redirect($redirect_to);
        
        
        // v0.1
        // $slug = $user->user_nicename;   // Member profile page URL construct out of the user's nicename

        // error_log('User Slug: ' . $user->user_nicename);
        // $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/members/' . $slug . '/');
        
        // wp_redirect($redirect_to);

        exit;
    }
}
//  
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error_message)) : ?>
    <script>
        console.log("<?php echo esc_js($error_message); ?>");
        document.addEventListener("DOMContentLoaded", function() {
            const errorContainer = document.querySelector(".login-error");
            errorContainer.innerHTML = "<?php echo esc_js($error_message); ?>";
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
                <?php wp_nonce_field('custom_login_action', 'custom_login_nonce'); ?>
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
        </div>

    </div>
    <div class="left-container">
        
    </div>

</div>
