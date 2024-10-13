<?php
/**
 * BuddyBoss - Members Profile Loop
 *
 * @since   BuddyPress 3.0.0
 * @version 3.1.0
 */

$edit_profile_link = trailingslashit(bp_displayed_user_domain() . bp_get_profile_slug() . '/edit/group/');

bp_nouveau_xprofile_hook('before', 'loop_content');
?>                             <style>
.card-slider {
    display: flex;
    width: auto; /* Allow the width to adjust based on content */
    overflow-x: auto; /* Allow horizontal scrolling */
    padding-bottom: 15px; /* Space for smooth scroll */
    scroll-behavior: smooth; /* Smooth scrolling */
    position: relative; /* Ensure hover areas are placed correctly */
   
}
/* For WebKit browsers (Chrome, Safari) */
.card-slider::-webkit-scrollbar {
    height: 6px; /* Height of the scrollbar */
    opacity: 0; /* Hide scrollbar by default */
    transition: opacity 0.3s; /* Smooth transition for visibility */
}
.campigans-container:hover .card-slider::-webkit-scrollbar {
    opacity: 1; /* Show scrollbar on hover */
}

.card-slider::-webkit-scrollbar-thumb {
    background-color: darkgray; /* Color of the scrollbar thumb */
    border-radius: 10px; /* Rounded corners */
}

.card-slider::-webkit-scrollbar-track {
    background: transparent; /* Background of the scrollbar track */
}

.card-campaign {
    flex: 0 0 30%; /* Allow the cards to take 30% of the container’s width */
    margin-right: 15px; /* Add some space between the cards */
    min-width: 220px; /* Set minimum width to avoid shrinking too much */
    height: auto; /* Allow height to adjust based on content */
    border-radius: 20px;
    background: linear-gradient(17deg, #FCB72B 0.61%, #F2F2F2 37.17%, #6836FF 99.44%);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.card-header-box {
    width: 100%;
    height: 40%;
    background: #6836FF;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    border-bottom-left-radius: 100px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-side-color {
    width: 15%;
    height: 100%;
    background: #3DD88C;
    border-bottom-right-radius: 20px;
    border-top-right-radius: 20px;
}

.card-headers {
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin: 30px;
}

.card-title {
    color: #FFF;
    text-align: right;
    font-family: Rubik;
    font-size: 26px;
    font-style: normal;
    font-weight: 700;
    line-height: 90%;
    margin: 4px 0 !important;
}

.card-sub-text {
    color: #3DD88C;
    font-size: 14px;
    font-style: normal;
    font-weight: 300;
    line-height: 130%;
}

.card-logo {
    width: 70px;
    height: 70px;
    margin-top: -35px;
    margin-bottom: 20px;
    align-self: center;
}

.card-button {
    display: inline-flex;
    padding: 15px 30px;
    margin-bottom: 20px;
    justify-content: center;
    align-items: center;
    gap: 10px;
    border-radius: 10px;
    background: linear-gradient(90deg, #FCB72B 0%, #6836FF 100%);
    color: #FFF;
    text-align: right;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.card-footer {
    color: #6836FF;
    text-align: center;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 130%;
}

</style>


<?php
if (bp_has_profile()) {

    while (bp_profile_groups()):
        bp_the_profile_group();

        if (bp_profile_group_has_fields()) {
            bp_nouveau_xprofile_hook('before', 'field_content'); ?>
            <div class="group-separator-block">
                <header class="entry-header profile-loop-header profile-header flex align-items-center">
                    <h1 class="entry-title bb-profile-title ">מידע כללי</h1>

                    <?php
                    if (bp_is_my_profile()) {
                        ?>
                        <a href="<?php echo esc_url($edit_profile_link . bp_get_the_profile_group_id()); ?>"
                            class="push-right button outline small"><?php esc_attr_e('עריכה', 'buddyboss-theme'); ?></a>
                        <?php
                    }
                    ?>
                </header>
                <div class="flexbox-container">
                    <div class="bp-widget <?php bp_the_profile_group_slug(); ?>">
                        <table class="profile-fields bp-tables-user">

                            <?php
                            while (bp_profile_fields()):
                                bp_the_profile_field();

                                if (
                                    function_exists('bp_member_type_enable_disable') &&
                                    false === bp_member_type_enable_disable()
                                ) {
                                    if (
                                        function_exists('bp_get_xprofile_member_type_field_id') &&
                                        bp_get_the_profile_field_id() === bp_get_xprofile_member_type_field_id()
                                    ) {
                                        continue;
                                    }
                                }
                                bp_nouveau_xprofile_hook('before', 'field_item');

                                if (bp_field_has_data()):
                                    ?>
                                    <tr<?php bp_field_css_class(); ?>>
                                        <td class="label"><?php bp_the_profile_field_name(); ?></td>
                                        <td class="data"><?php bp_the_profile_field_value(); ?></td>
                                        </tr>
                                        <?php
                                endif;

                                bp_nouveau_xprofile_hook('', 'field_item');

                            endwhile;

                            bp_nouveau_xprofile_hook('after', 'field_items');
                            ?>

                        </table>
                    </div>
                    <div class="campigans-container bg-white mx-auto max-w-7xl">
                            <div class="card-slider">  
                                <div class="card-campaign">
                                    <div class="card-header-box">
                                        <div class="card-side-color"></div>
                                        <div class="card-headers">
                                            <h3 class="card-title">1Join full stack Challange</h3>
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
                                <div class="card-campaign">
                                    <div class="card-header-box">
                                        <div class="card-side-color"></div>
                                        <div class="card-headers">
                                            <h3 class="card-title">2Join Mobile Challange</h3>
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
                                <div class="card-campaign">
                                    <div class="card-header-box">
                                        <div class="card-side-color"></div>
                                        <div class="card-headers">
                                            <h3 class="card-title">3Join Mobile Challange</h3>
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
                                <div class="card-campaign">
                                    <div class="card-header-box">
                                        <div class="card-side-color"></div>
                                        <div class="card-headers">
                                            <h3 class="card-title">4Join Mobile Challange</h3>
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
                                <div class="card-campaign">
                                    <div class="card-header-box">
                                        <div class="card-side-color"></div>
                                        <div class="card-headers">
                                            <h3 class="card-title">Join Mobile Challange</h3>
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
            </div>
            
            <?php
            bp_nouveau_xprofile_hook('after', 'field_content');
        }

    endwhile;

    bp_nouveau_xprofile_hook('', 'field_buttons');
} else {
    ?>

    <div class="info bp-feedback">
        <span class="bp-icon" aria-hidden="true"></span>
        <p>
            <?php
            if (bp_is_my_profile()) {
                esc_html_e('You have not yet added details to your profile.', 'buddyboss-theme');
            } else {
                esc_html_e('This member has not yet added details to their profile.', 'buddyboss-theme');
            }
            ?>
        </p>
    </div>

    <?php
}
bp_nouveau_xprofile_hook('after', 'loop_content');
