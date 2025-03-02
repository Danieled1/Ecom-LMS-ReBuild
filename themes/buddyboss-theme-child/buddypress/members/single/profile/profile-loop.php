<?php
/**
 * BuddyBoss - Members Profile Loop
 *
 * @since   BuddyPress 3.0.0
 * @version 3.1.0
 */
error_log("Test TEST TEST TEST ");
$edit_profile_link = trailingslashit(bp_displayed_user_domain() . bp_get_profile_slug() . '/edit/group/');
$GENERAL_INFO_GROUP_ID = 1;
function preload_lcp_image() {
    ?>
    <link rel="preload" as="image" href="https://dev.digitalschool.co.il/wp-content/uploads/buddypress/members/6238/cover-image/679a7aabbb410-bp-cover-image.png" type="image/webp">
    <?php
}
add_action('wp_head', 'preload_lcp_image');

bp_nouveau_xprofile_hook('before', 'loop_content');
?>                            

<?php

if (bp_has_profile(array('profile_group_id' => $GENERAL_INFO_GROUP_ID))) { 

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
                                            <h3 class="card-title">פרויקט 1</h3>
                                            <p class="card-sub-text">פרויקט במיוחד למסלול שלך!</p>
                                        </div>
                                    </div>
                                    <div class="card-logo">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/mini-logo.svg" alt="Logo" loading="lazy">
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
                                            <h3 class="card-title">פרויקט 2</h3>
                                            <p class="card-sub-text">פרויקט במיוחד למסלול שלך!</p>
                                        </div>
                                    </div>
                                    <div class="card-logo">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/mini-logo.svg" alt="Logo" loading="lazy">
                                    </div>
                                    <a href="#" class="card-button">התחל לפתח</a>
                                    <div class="card-footer">
                                        <p>7 ימי ניסיון חינם</p>
                                    </div>
                                </div>
                                <div class="card-campaign">
                                    <div class="card-header-box">
                                        <div class="card-side-color"></div>
                                        <div class="card-headers">
                                            <h3 class="card-title">פרויקט 3</h3>
                                            <p class="card-sub-text">פרויקט במיוחד למסלול שלך!</p>
                                        </div>
                                    </div>
                                    <div class="card-logo">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/mini-logo.svg" alt="Logo" loading="lazy">
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
                                            <h3 class="card-title">פרויקט 4</h3>
                                            <p class="card-sub-text">פרויקט במיוחד למסלול שלך!</p>
                                        </div>
                                    </div>
                                    <div class="card-logo">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/mini-logo.svg" alt="Logo" loading="lazy">
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
