<?php
/**
 * Admin Notices.
 *
 * @package WP_Travel
 */

 /**
  * Display critical admin notices.
  */
function wptravel_display_critical_admin_notices() {
	$show_notices = apply_filters( 'wp_travel_display_critical_admin_notices', false );
	if ( ! $show_notices ) {
		return;
	}
	?>
	<div class="wp-travel-notification notification-warning notice notice-error"> 
		<div class="notification-title">
			<h3><?php echo esc_html__( 'WP Travel Alert', 'wp-travel' ); ?></h3>
		</div>
		<div class="notification-content">
			<ul>
				<?php do_action( 'wp_travel_critical_admin_notice' ); ?>
			</ul>
		</div>
	</div>
	<?php

}
if ( ! is_multisite() ) {
	add_action( 'admin_notices', 'wptravel_display_critical_admin_notices' );

} else {
	add_action( 'network_admin_notices', 'wptravel_display_critical_admin_notices' );
	if ( is_main_site() ) {
		add_action( 'admin_notices', 'wptravel_display_critical_admin_notices' );
	}
}

 /**
  * Display General admin notices.
  */
function wptravel_display_general_admin_notices() {
	$screen       = get_current_screen();
	$screen_id    = $screen->id;
	$notice_pages = array(
		'itinerary-booking_page_settings',
		'itineraries_page_booking_chart', // may be not reqd
		'itinerary-booking_page_booking_chart',
		'edit-itinerary-booking',
		'edit-travel_keywords',
		'edit-travel_locations',
		'edit-itinerary_types',
		'edit-itineraries',
		'itineraries',
		'itinerary-booking',
		'edit-activity',
		'edit-wp-travel-coupons',
		'edit-itinerary-enquiries',
		'edit-tour-extras',
		'edit-wp_travel_downloads',
		'itinerary-booking_page_wp-travel-marketplace',
		'itinerary-booking_page_wp_travel_custom_filters_page',
	);
	$notice_pages = apply_filters( 'wp_travel_admin_general_notice_page_screen_ids', $notice_pages );
	if ( ! in_array( $screen_id, $notice_pages ) ) { // Only display general notice on WP Travel pages.
		  return false;
	}

	$show_notices = apply_filters( 'wp_travel_display_general_admin_notices', false );
	if ( ! $show_notices ) {
		return;
	}
	?>
	<div class="wp-travel-notification notification-warning notice notice-info is-dismissible"> 
		<div class="notification-title">
			<h3><?php echo esc_html__( 'WP Travel Notifications', 'wp-travel' ); ?></h3>
		</div>
		<div class="notification-content">
			<ul>
			  <?php do_action( 'wp_travel_general_admin_notice' ); ?>
			</ul>
		</div>
	</div>
	<?php

}

add_action( 'admin_notices', 'wptravel_display_general_admin_notices' );

// Deprecated notice.
function wptravel_display_deprecated_notice() {
	$notices = apply_filters( 'wp_travel_deprecated_admin_notice', array() );

	if ( count( $notices ) < 1 ) {
		return;
	}
	?>
	<div class="wp-travel-notification notification-warning notice notice-error"> 
		<div class="notification-title">
			<h3><?php echo esc_html__( 'WP Travel Deprecated Notices.', 'wp-travel' ); ?></h3>
		</div>
		<div class="notification-content">
			<ul>
				<?php foreach ( $notices as $notice ) : ?>
					<li><?php echo esc_html( $notice ); ?></li>
					<?php
				endforeach;
				?>
			</ul>
		</div>
	</div>
	<?php
}
add_action( 'admin_notices', 'wptravel_display_deprecated_notice' );


// Single Pricing deprecated notice.
function wptravel_display_single_pricing_deprecated_notice( $notices ) {

	if ( ! WP_Travel::verify_nonce( true ) ) {
		return $notices;
	}

	$screen  = get_current_screen();
	$post_id = get_the_ID();

	/**
	 * Already checking nonces above.
	 */
	if ( WP_TRAVEL_POST_TYPE === $screen->post_type && $screen->parent_base == 'edit' && ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) && $post_id ) {
		$pricing_option_type = wptravel_get_pricing_option_type( $post_id );
		if ( 'single-price' === $pricing_option_type ) {
			$notices[] = __( 'Single Pricing is deprecated and will be removed in future version of WP Travel. Please update your pricing to multiple pricing.', 'wp-travel' );
		}
	}
	return $notices;
}
add_filter( 'wp_travel_deprecated_admin_notice', 'wptravel_display_single_pricing_deprecated_notice' );

function wptravel_remove_v3_trips_notice() {
	$settings     = wptravel_get_settings();
	$user_since   = get_option( 'wp_travel_user_since', '3.0.0' );
	$switch_to_v4 = wptravel_is_react_version_enabled();
	if ( version_compare( $user_since, '4.0.0', '<' ) && ! $switch_to_v4 ) {
		?>
		<div class="wp-travel-notification notification-warning notice notice-info is-dismissible"> 
			<div class="notification-content">
				<ul>
					<div><p><strong><span style="color:#f00">Note : </span> <?php esc_html_e( "This is the notice for the users who still use previous layout (v3 layout) to migrate all the trips created using WP Travel versions before 4.0.0 through Migrate Pricing and Date option as we are going to remove 'Switch to V4' option in settings page from WP Travel v5.1.0.", 'wp-travel' ); ?> <a href="https://wptravel.io/removal-of-switch-to-v4-option/" target="_blank">More.</a></strong></p></div>
				</ul>
			</div>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'wptravel_remove_v3_trips_notice', 100 );

function wptravel_v3_notice_display( $show ) {
	$user_since   = get_option( 'wp_travel_user_since', '3.0.0' );
	$switch_to_v4 = wptravel_is_react_version_enabled();
	if ( version_compare( $user_since, '4.0.0', '<' ) && ! $switch_to_v4 ) {
		$show = true;
	}
	return $show;
}

add_filter( 'wp_travel_display_general_admin_notices', 'wptravel_v3_notice_display', 100 );
