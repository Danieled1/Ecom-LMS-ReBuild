<?php
/**
 * Template part for displaying header aside.
 *
 * @package BuddyBoss_Theme
 */
$show_search        = buddyboss_theme_get_option( 'desktop_component_opt_multi_checkbox', 'desktop_header_search' );
$show_messages      = buddyboss_theme_get_option( 'desktop_component_opt_multi_checkbox', 'desktop_messages' ) && is_user_logged_in();
$show_notifications = buddyboss_theme_get_option( 'desktop_component_opt_multi_checkbox', 'desktop_notifications' ) && is_user_logged_in();
$show_shopping_cart = buddyboss_theme_get_option( 'desktop_component_opt_multi_checkbox', 'desktop_shopping_cart' );
$header_style       = (int) buddyboss_theme_get_option( 'buddyboss_header' );
$profile_dropdown   = buddyboss_theme_get_option( 'profile_dropdown' );
$is_lms_inner       = (
	( class_exists( 'SFWD_LMS' ) && buddyboss_is_learndash_inner() ) ||
	( class_exists( 'LifterLMS' ) && buddypanel_is_lifterlms_inner() ) ||
	( function_exists( 'tutor' ) && buddyboss_is_tutorlms_inner() )
);
?>
<style>
	/* NOTICE - data-no-dropdown attribute disables the user profile(for now - we need)
		<a class="user-link" href=" echo esc_url( $user_link );"  echo bb_elementor_pro_disable_page_transition();  data-no-dropdown>
	*/
	/* TODO BEFORE NOTICE - to apply it first add the missing menu items to our sidebar menu so we can not use it and still have everything */
	/* Custom header-aside Styles */
	.avatar-100{
        width: 50px !important;      
        height: 50px !important;        
        border-radius: 10px !important;
        max-width: none !important;
		box-shadow: 0px 4px 20px 0px rgba(0, 0, 0, 0.15);
    }
    .user-name{
        font-size: 16px;
        margin-left: 0;
        margin-right: 10px;
    }
    #header-aside > div > div.user-wrap.user-wrap-container.menu-item-has-children > a{
        flex-direction: row-reverse;
    }
    /* Adjusting the order of elements */
    .header-search-link { order: 2; } /* The search icon comes fourth 2*/
    .bb-separator { order: 3; } /* The separator comes right before the search icon 3*/
    #header-messages-dropdown-elem { order: 4; } /* Messages come second 4*/
    #header-notifications-dropdown-elem { order: 5; } /* Notifications come first 5*/
    .user-wrap { order: 1;} /* User info comes last 1*/


    .header-aside-inner {
        display: flex;
        align-items: center;  /* Align items vertically */
        justify-content: center;
        width: 100%; /* Adjust width as necessary */
    }
    .header-aside-inner > * {
        margin-right: 8px; 
    }
    .header-aside-inner a {
        display: flex;
        align-items: center; /* Ensure that the icon within each link is also centered */
    }
    .header-aside-inner i {
        margin-right: 5px; /* Space between icon and text if any */
        vertical-align: middle; /* Helps further in aligning icons with text vertically */
    }

    /* Specific adjustments for ::before content */
    .header-aside-inner a::before {
        display: inline-block;
        vertical-align: middle; /* Aligns the pseudo-element with the text */
        /* You may need additional positioning here depending on your specific layout */
    }
</style>
<div id="header-aside" class="header-aside <?php echo esc_attr( $profile_dropdown ); ?>">
	<div class="header-aside-inner">

		<?php
		if ( $is_lms_inner ) :
			?>
			<a href="#" id="bb-toggle-theme">
				<span class="sfwd-dark-mode" data-balloon-pos="down" data-balloon="<?php esc_html_e( 'Dark Mode', 'buddyboss-theme' ); ?>"><i class="bb-icon-rl bb-icon-moon"></i></span>
				<span class="sfwd-light-mode" data-balloon-pos="down" data-balloon="<?php esc_html_e( 'Light Mode', 'buddyboss-theme' ); ?>"><i class="bb-icon-l bb-icon-sun"></i></span>
			</a>
			<a href="#" class="header-maximize-link course-toggle-view" data-balloon-pos="down" data-balloon="<?php esc_html_e( 'Maximize', 'buddyboss-theme' ); ?>"><i class="bb-icon-l bb-icon-expand"></i></a>
			<a href="#" class="header-minimize-link course-toggle-view" data-balloon-pos="down" data-balloon="<?php esc_html_e( 'Minimize', 'buddyboss-theme' ); ?>"><i class="bb-icon-l bb-icon-merge"></i></a>

			<?php
		elseif ( is_user_logged_in() ) :
			if ( $show_search && 4 !== $header_style ) :
				?>
				<a href="#" class="header-search-link" data-balloon-pos="down" data-balloon="<?php esc_html_e( 'Search', 'buddyboss-theme' ); ?>"><i class="bb-icon-l bb-icon-search"></i></a>
				<span class="bb-separator"></span>
				<?php
			endif;

			if ( $show_messages && function_exists( 'bp_is_active' ) && bp_is_active( 'messages' ) ) :
				get_template_part( 'template-parts/messages-dropdown' );
			endif;

			if ( $show_notifications && function_exists( 'bp_is_active' ) && bp_is_active( 'notifications' ) ) :
				get_template_part( 'template-parts/notification-dropdown' );
			endif;

			if ( $show_shopping_cart && class_exists( 'WooCommerce' ) ) :
				get_template_part( 'template-parts/cart-dropdown' );
			endif;
		endif;

		if ( 'off' !== $profile_dropdown ) {
			if ( is_user_logged_in() ) :
				?>
				<div class="user-wrap user-wrap-container menu-item-has-children">
					<?php
					$current_user = wp_get_current_user();
					$user_link    = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( $current_user->ID ) : get_author_posts_url( $current_user->ID );
					$display_name = function_exists( 'bp_core_get_user_displayname' ) ? bp_core_get_user_displayname( $current_user->ID ) : $current_user->display_name;
					?>

					<a class="user-link" href="<?php echo esc_url( $user_link ); ?>" <?php echo bb_elementor_pro_disable_page_transition(); ?>>
						<?php
						if ( 'name_and_avatar' === $profile_dropdown ) {
							?>
							<span class="user-name"><?php echo esc_html( $display_name ); ?></span><i class="bb-icon-l bb-icon-angle-down"></i>
							<?php
						}
						echo get_avatar( get_current_user_id(), 100 );
						?>
					</a>

					<div class="sub-menu">
						<div class="wrapper">
							<ul class="sub-menu-inner">
								<li>
									<a class="user-link" href="<?php echo esc_url( $user_link ); ?>" <?php echo bb_elementor_pro_disable_page_transition(); ?>>
										<?php echo get_avatar( get_current_user_id(), 100 ); ?>
										<span>
											<span class="user-name"><?php echo esc_html( $display_name ); ?></span>
											<?php if ( function_exists( 'bp_is_active' ) && function_exists( 'bp_activity_get_user_mentionname' ) ) : ?>
												<span class="user-mention"><?php echo '@' . esc_html( bp_activity_get_user_mentionname( $current_user->ID ) ); ?></span>
											<?php else : ?>
												<span class="user-mention"><?php echo '@' . esc_html( $current_user->user_login ); ?></span>
											<?php endif; ?>
										</span>
									</a>
								</li>
								<?php
								$theme_menu_locations = get_nav_menu_locations();
								
								if ( class_exists( 'SFWD_LMS' ) && ld_30_focus_mode_enable() && isset( $theme_menu_locations['ld30_focus_mode'] ) ) {
									wp_nav_menu(
										array(
											'theme_location' => 'ld30_focus_mode',
											'container' => false,
											'fallback_cb' => false,
											'walker'  => new BuddyBoss_SubMenuWrap(),
											'menu_class' => 'bb-my-account-menu',
										)
									);
								} elseif ( function_exists( 'bp_is_active' ) ) {
									$header_menu = wp_nav_menu(
										array(
											'theme_location' => 'header-my-account',
											'echo'        => false,
											'fallback_cb' => '__return_false',
										)
									);
									if ( ! empty( $header_menu ) ) {
										wp_nav_menu(
											array(
												'theme_location' => 'header-my-account',
												'menu_id' => 'header-my-account-menu',
												'container' => false,
												'fallback_cb' => '',
												'walker'  => new BuddyBoss_SubMenuWrap(),
												'menu_class' => 'bb-my-account-menu',
											)
										);
									} else {
										do_action( THEME_HOOK_PREFIX . 'header_user_menu_items' );
									}
								} else {
									do_action( THEME_HOOK_PREFIX . 'header_user_menu_items' );
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<?php
			endif;
		}

		if ( ! is_user_logged_in() ) :
			?>

			<?php if ( $show_search && 4 !== $header_style && !$is_lms_inner ) : ?>
				<a href="#" class="header-search-link" data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Search', 'buddyboss-theme' ); ?>"><i class="bb-icon-l bb-icon-search"></i></a>
				<span class="search-separator bb-separator"></span>
				<?php
			endif;

			if ( $show_shopping_cart && class_exists( 'WooCommerce' ) && !$is_lms_inner ) :
				get_template_part( 'template-parts/cart-dropdown' );
			endif;
			?>
				<div class="bb-header-buttons">
					<a href="<?php echo esc_url( wp_login_url() ); ?>" class="button small outline signin-button link"><?php esc_html_e( 'Sign in', 'buddyboss-theme' ); ?></a>

					<?php if ( get_option( 'users_can_register' ) ) : ?>
						<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="button small signup"><?php esc_html_e( 'Sign up', 'buddyboss-theme' ); ?></a>
					<?php endif; ?>
				</div>
			<?php

			endif;

			if (
				3 === $header_style || $is_lms_inner
			) :
			    echo buddypanel_position_right();
			endif;
		?>

	</div><!-- .header-aside-inner -->
</div><!-- #header-aside -->
