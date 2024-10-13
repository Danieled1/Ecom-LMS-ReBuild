<?php
/*
Template Name: Technical Support Guide
*/
$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();

acf_form_head();
get_header();
?>

<div class="container">
    <div class="content">
        <div class="header-image-wrapper">
            <div class="header-image">
                <img src="https://via.placeholder.com/1180x350" alt="Header Image" />
                <div class="header-gradient-overlay"></div>
                <div class="header-text">
                    <div class="header-title">טופס תמיכה מקצועית</div>
                    <div class="header-subtitle">נקודות מפתח למילוי הטופס בצורה נכונה</div>
                </div>
            </div>
        </div>

        <div class="content-text">
            <span>לפני שתמלאו את הטופס, אנא קראו את הנקודות המפתח הבאות:</span>
            <ul>
                <li>תמיכה טכנית שדורשת צירוף קבצים או תמונות ולא תכלול כאלה תאט את זמן המענה.</li>
                <li>מלאו כל פרט בקפידה וודאו את נושא התמיכה והפרט שבו אתם זקוקים לעזרה.</li>
                <li>לא כל תמיכה דורשת שיתוף מסך; אנו מבקשים את הפרטים מראש כדי לאפשר לנו להחליט אם יש צורך בכך.</li>
                <li>התמיכה אינה שיעור פרטי; עליכם להתמקד בנושא אחד בכל פנייה.</li>
            </ul>
        </div>


        <div class="important-notes">הערות חשובות:</div>

        <div class="notes">
            תשאלו את עצמכם את השאלות הבאות לפני שאתם פונים לתמיכה: באיזה נושא אני זקוק לעזרה? האם אני יכול לתאר
            את הבעיה בצורה ברורה וממוקדת?<br />לאחר קריאת ההנחיות וההערות החשובות, אתם מוזמנים למלא את טופס
            התמיכה הטכנית.
        </div>

        <div class="support-button">
            <div class="button-text">טופס תמיכה טכנית</div>
            <div class="arrow-icon">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/arrow-right-solid.svg"
                    alt="Arrow Right" class="arrow-icon-img" />
            </div>
        </div>

    </div>
</div>

<?php
// Include any necessary PHP code for your guide here


get_footer();
?>