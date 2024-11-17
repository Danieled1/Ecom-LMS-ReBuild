<?php
/*
Template Name: Coming Soon
*/

// Check if the current user is logged in and allowed access
$allowed_users = ['support@ecomschool.co.il', 'goxik48771'];
$current_user = wp_get_current_user();

// If the user is not logged in or not in the allowed list, show the coming soon page
if (!is_user_logged_in() || !in_array($current_user->user_login, $allowed_users)) {
    ?>

    <style>
        /* Coming Soon Page Styles */
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Heebo', sans-serif;
            background:#fff;
            color: #333;
            direction: rtl;
        }

        .coming-soon-container {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.00) 76.04%, #FFF 100%), radial-gradient(58.89% 35.32% at 2.24% 50%, rgba(105, 57, 255, 0.20) 0%, rgba(196, 196, 196, 0.00) 100%), radial-gradient(39% 26.79% at 13.62% 46.87%, rgba(104, 228, 255, 0.30) 0%, rgba(196, 196, 196, 0.00) 100%), radial-gradient(53.08% 30.01% at 83.1% 56.51%, rgba(17, 192, 233, 0.20) 0%, rgba(196, 196, 196, 0.00) 100%), radial-gradient(61.55% 40.72% at 87.42% 75.1%, rgba(252, 183, 43, 0.20) 0%, rgba(196, 196, 196, 0.00) 100%), radial-gradient(19.38% 23.86% at 9.43% 64.11%, rgba(252, 183, 43, 0.30) 0%, rgba(196, 196, 196, 0.00) 100%);
            box-shadow: var(--card-shadow, 0px 4px 12px rgba(0, 0, 0, 0.1));
            border-radius: var(--border-radius-large, 20px);
            max-width: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .coming-soon-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--purple, #6836FF);
            margin-bottom: var(--spacing-medium, 16px);
        }

        .coming-soon-message {
            font-size: 1.25rem;
            font-weight: 400;
            color: #666;
            margin-bottom: var(--spacing-large, 24px);
        }

        .coming-soon-footer {
            font-size: 1rem;
            color: #999;
        }
        .coming-soon-logo {
            height: auto;
            margin-bottom: 1.5rem;
            animation: logoBounce 3s ease-in-out infinite;
        }

        @keyframes logoBounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>

    <div class="coming-soon-container">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/logo.svg" alt="Logo" class="coming-soon-logo" />
        <h1 class="coming-soon-title">תכף זה קורה...</h1>
        <p class="coming-soon-message">אנחנו עובדים קשה כדי להביא לכם את החווית הלמידה הטובה ביותר.<br> אנא בדקו שוב בקרוב!</p>
        <p class="coming-soon-footer">תודה על הסבלנות!</p>
    </div>

    <?php
    exit;
}

// If the user is allowed, redirect them to the homepage
wp_redirect(home_url());
exit;

