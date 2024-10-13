<?php
/**
 * BuddyBoss - Members Home
 *
 * @since BuddyPress   1.0.0
 * @version 3.0.0
 */


$bp = buddypress();
$grid_class = '';
$user_full_template = '';
$bp_nouveau_appearance = bp_get_option('bp_nouveau_appearance');
$profile_cover_width = buddyboss_theme_get_option('buddyboss_profile_cover_width');

$bp_is_user_front = bp_is_user_front();
$bp_is_user_settings = bp_is_user_settings();
$bp_is_user_messages = bp_is_user_messages();
$bp_is_user_notifications = bp_is_user_notifications();
$bp_is_user_profile_edit = bp_is_user_profile_edit();
$bp_is_user_change_avatar = bp_is_user_change_avatar();
$bp_is_user_change_cover_image = bp_is_user_change_cover_image();

if (
    !$bp_is_user_front &&
    !empty($bp->template_message) &&
    !empty($bp->template_message_type) &&
    $bp->template_message_type == 'bp-sitewide-notice'
) {
    bp_nouveau_template_notices();
}

if (
    !$bp_is_user_settings &&
    !$bp_is_user_messages &&
    !$bp_is_user_notifications &&
    !$bp_is_user_profile_edit &&
    !$bp_is_user_change_avatar &&
    !$bp_is_user_change_cover_image
) {
    $grid_class = 'bb-grid';
}

if (
    $bp_is_user_messages ||
    $bp_is_user_settings ||
    $bp_is_user_notifications ||
    $bp_is_user_profile_edit ||
    $bp_is_user_change_avatar ||
    $bp_is_user_change_cover_image
) {
    $user_full_template = 'bp-fullwidth-wrap';
}

bp_nouveau_member_hook('before', 'home_content');
?>
<style>
.bb-template-v2 nav#object-nav>ul>.selected>a .bb-single-nav-item-point {
    border-bottom: 2px solid #FCB72B;
    
}
/* single nav items - my profile items /members/me   */
.bb-template-v2 nav#object-nav>ul>li>a .bb-single-nav-item-point{
    text-align: right;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
    color: var(--DARK, #333);

}
.bb-course-items .bb-cover-list-item {
    border-radius: 20px;
    border: none;
    box-shadow: 0px 4px 20px 0px rgba(0, 0, 0, 0.15);
    background: #FFF;
    
}

    /* Right Sidepanel  */
    /* .buddypanel {
        left: auto;
        right: 0;
    } */

    /* Main container */
    .container {
        max-width: 100%;
    }
    .site-content {
        padding: 0 0 !important;
    }

    body #buddypress #item-header-cover-image {
        display: flex;
        align-items: center;
        /* Vertical alignment */
        justify-content: center;
        /* Horizontal spacing */
        background: white;
        padding: 10px;
        border:1px solid #fff;
        border-radius: 20px 0 0 0;
        /* Adjust to reverse the round corners to the other side */
        height: 140px;
        width: 50%;
        float: right;
        direction: rtl;
        z-index: 2;
        flex-direction: row;
    }

    #item-header {
        border-radius: 0 0 20px 20px;

    }

    div#item-header-content {
        margin: 0 16px !important;
        padding: 0 0 0 0 !important;
        position: static;
        z-index: auto;
    }

    div#item-header-avatar {
        margin: 0 0 0 0 !important;
        padding: 0 0 0 0 !important;
        position: static;
        z-index: auto;
    }
    #buddypress #header-cover-image{
        overflow: visible;
    }

    #buddypress #header-cover-image.cover-small {
        height: 350px !important;
        border-radius: 0 0 20px 20px !important;
        flex-grow: 1 !important;
    }

    .bb-template-v2 #cover-image-container {
        position: relative !important;
        overflow: hidden !important;
        border-radius: 0 0 20px 20px !important;
        padding: 0 !important;
        border: none !important;
        background: none !important;


    }

    #item-header-avatar>img {
        border-radius: 0 !important;
        width: 80px;
    }

    #item-header-avatar>span.link-change-overlay {
        height: 80px !important;
        border-radius: 0 !important;
    }

    #item-header-content>div>div.bb-user-content-wrap {}

    #item-header-content>div>div.bb-user-content-wrap>div.flex.align-items-center.member-title-wrap {
        margin: 0;
    }

    #item-header-content>div>div.bb-user-content-wrap>div.flex.align-items-center.member-title-wrap>h2 {
        margin: 0 0 4px 0 !important;
    }

    #item-header-content>div>div.bb-user-content-wrap>div.item-meta {
        font-size: 16px;
    }
    
        nav#object-nav {
        direction: ltr; /* This will make the content flow from left to right */

    }

    .bp-profile-wrapper.need-separator .group-separator-block{
        padding: 0 20px;
    }

    /* table and campigns content container */
    .flexbox-container{
        display: flex;
    }
    /* table and campigns  container  */
    #item-body > div > div > div > div > div > div{
        width: 100%;
        
    }
    /* table component  */
    #item-body > div > div > div > div > div > div > div.bp-widget.general-info{
        width: max-content;
        flex:1;
        height: 100%;
        width: 100%;
        margin-bottom: 0;
    }
    .campigans-container{
        flex: 2;
        width: 100%; /* Ensure the container takes full width */
        overflow-x: hidden; /* Hide any horizontal overflow */
        position: relative;
        overflow: hidden; /* Ensure that the cards don't spill out of the container */

    }

    .profile-loop-header .entry-title.bb-profile-title{
        font-size: 24px;
        font-weight: 400;
        line-height: 28px;
    }
    #page > footer{
        padding: 0 250px !important;
    }
    @media screen and (max-width: 1400px) {
        #item-header-cover-image {
            width: 100% !important;
            /* Or 100% if you want it to stretch across the screen */

        }
        .flexbox-container{
            flex-direction: column;
        }
    }

    @media screen and (min-width: 800px) {
        .buddypanel-open:not(.register) .site {
            margin-left: 0 !important;
            /* Overrides any other margin-left settings */
        }

        .site-content {
            padding: 0 250px !important;
        }

        .bb-buddypanel:not(.activate) .site,
        .bb-buddypanel:not(.register) .site {
            margin-right: 230px !important;
            /* Ensures no left margin */
            -webkit-transition: margin-right .2s;
            /* Maintains a smooth transition for margin changes */
            transition: margin-right .2s;
        }

        .bb-buddypanel .bb-footer,
        .bb-buddypanel .header-search-wrap,
        .bb-buddypanel .site-content,
        .bb-buddypanel .site-header {
            padding: 0;
        }

    }
    @media screen and (min-width: 800px) {
        /* Ensure slider buttons are visible on desktop */
        .slider-button {
            display: inline-block;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: rgba(255, 255, 255, 0.8);
            border: none;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
        }
        .slider-button.left {
            left: 10px; /* Position left button */
        }
        .slider-button.right {
            right: 10px; /* Position right button */
        }
    }
</style>
<div id="item-header" role="complementary" data-bp-item-id="<?php echo esc_attr(bp_displayed_user_id()); ?>"
    data-bp-item-component="members" class="users-header single-headers">
    <?php bp_nouveau_member_header_template_part(); ?>
</div><!-- #item-header -->
<?php
if (
    isset($bp_nouveau_appearance['user_nav_display']) &&
    $bp_nouveau_appearance['user_nav_display'] &&
    is_active_sidebar('profile') &&
    !$bp_is_user_settings &&
    !$bp_is_user_messages &&
    !$bp_is_user_notifications &&
    !$bp_is_user_profile_edit &&
    !$bp_is_user_change_avatar &&
    !$bp_is_user_change_cover_image &&
    $profile_cover_width != 'default'
) {
    $grid_class = '';
    ?>
    <div class="bb-grid bb-user-nav-display-wrap">
        <div class="bp-wrap-outer">
        <?php } ?>

        <div class="bp-wrap <?php echo $user_full_template; ?>">
            <?php
            if (
                !bp_nouveau_is_object_nav_in_sidebar() &&
                !$bp_is_user_messages &&
                !$bp_is_user_settings &&
                !$bp_is_user_notifications &&
                !$bp_is_user_profile_edit &&
                !$bp_is_user_change_avatar &&
                !$bp_is_user_change_cover_image
            ) {
                bp_get_template_part('members/single/parts/item-nav');
            }
            ?>

            <div class="bb-profile-grid <?php echo $grid_class; ?>">
                <div id="item-body" class="item-body">
                    <div class="item-body-inner">
                        <?php bp_nouveau_member_template_part(); ?>
                    </div>
                </div><!-- #item-body -->

                <?php
                if (
                    (
                        !isset($bp_nouveau_appearance['user_nav_display']) ||
                        !$bp_nouveau_appearance['user_nav_display']
                    ) &&
                    is_active_sidebar('user_activity') &&
                    bp_is_user_activity()
                ) {
                    ob_start();
                    dynamic_sidebar('user_activity');
                    $sidebar = ob_get_clean();  // get the contents of the buffer and turn it off.
                    if (trim($sidebar)) { ?>
                        <div id="user-activity" class="widget-area" role="complementary">
                            <div class="bb-sticky-sidebar">
                                <?php dynamic_sidebar('user_activity'); ?>
                            </div>
                        </div>
                        <?php
                    }
                }

                if (
                    (
                        !isset($bp_nouveau_appearance['user_nav_display']) ||
                        !$bp_nouveau_appearance['user_nav_display']
                    ) &&
                    is_active_sidebar('profile') &&
                    !$bp_is_user_settings &&
                    !$bp_is_user_messages &&
                    !$bp_is_user_notifications &&
                    !$bp_is_user_profile_edit &&
                    !$bp_is_user_change_avatar &&
                    !$bp_is_user_change_cover_image &&
                    !$bp_is_user_front &&
                    $profile_cover_width == 'full'
                ) {
                    ob_start();
                    dynamic_sidebar('profile');
                    $sidebar = ob_get_clean();  // get the contents of the buffer and turn it off.
                    if (trim($sidebar)) {
                        ?>
                        <div id="secondary" class="widget-area sm-grid-1-1 no-padding-top" role="complementary">
                            <div class="bb-sticky-sidebar">
                                <?php dynamic_sidebar('profile'); ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

        </div><!-- // .bp-wrap -->

        <?php
        if (
            isset($bp_nouveau_appearance['user_nav_display']) &&
            $bp_nouveau_appearance['user_nav_display'] &&
            is_active_sidebar('profile') &&
            !$bp_is_user_settings &&
            !$bp_is_user_messages &&
            !$bp_is_user_notifications &&
            !$bp_is_user_profile_edit &&
            !$bp_is_user_change_avatar &&
            !$bp_is_user_change_cover_image &&
            !$bp_is_user_front &&
            $profile_cover_width != 'default'
        ) { ?>
        </div>

        <?php
        ob_start();
        dynamic_sidebar('profile');
        $sidebar = ob_get_clean();  // get the contents of the buffer and turn it off.
        if (trim($sidebar)) {
            ?>
            <div id="secondary" class="widget-area sm-grid-1-1 no-padding-top" role="complementary">
                <div class="bb-sticky-sidebar">
                    <?php dynamic_sidebar('profile'); ?>
                </div>
            </div>
            <?php
        }
        ?>

    </div>

    <?php
        }

        bp_nouveau_member_hook('after', 'home_content');
