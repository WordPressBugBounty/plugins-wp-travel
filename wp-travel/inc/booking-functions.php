<?php
/**
 * Booking Functions.
 *
 * @package WP_Travel
 */

/**
 * Frontend booking and send Email after clicking Book Now.
 *
 * @since 1.7.5
 */
function wptravel_book_now() {

	global $wt_cart;
	$items = $wt_cart->getItems();

	$discount_coupon_data = $wt_cart->get_discounts();


	$settings = wptravel_get_settings();
	if( class_exists( 'WooCommerce' ) && $settings['enable_woo_checkout'] == 'yes' ){
		if( !isset( $_REQUEST['key']) ){
			return;
		}

		$order_data = wc_get_order(wc_get_order_id_by_order_key($_REQUEST['key']))->data;

	}else{
		
		if ( ! WP_Travel::verify_nonce( true ) || ! isset( $_POST['wp_travel_book_now'] ) ) {
			return;
		}
		
		if( $discount_coupon_data['type'] !== 'percentage' && $discount_coupon_data['value'] !== '100' ){
			if( $_POST['wp_travel_booking_option'] == 'booking_with_payment' && !isset( $_POST['wp_travel_payment_gateway'] ) ){
				return;
			}
		}
		
	}

	/**
	 * Trigger any action before Booking Process.
	 *
	 * @hooked array( 'WP_Travel_Coupon', 'process_update_count' )
	 * @since 4.4.2
	 */
	do_action( 'wp_travel_action_before_booking_process' ); // phpcs:ignore
	do_action( 'wptravel_action_before_booking_process' );	


	if ( ! count( $items ) ) {
		return;
	}

	$price_key            = false;
	$pax                  = 1;
	$total_pax            = 0; // Total booked pax. helps to display report.
	$allow_multiple_items = WP_Travel_Cart::allow_multiple_items();

	$trip_ids               = array();
	$pax_array              = array();
	$price_keys             = array();
	$arrival_date           = array();
	$departure_date         = array();
	$arrival_date_email_tag = array(); // quick fix to add arrival date along with time in email.
	$pricing_id             = array(); // @since v4.0
	$trip_time              = array(); // @since v4.0
	foreach ( $items as $key => $item ) {
		// @since 3.1.3
		$email_travel_date = apply_filters( 'wp_travel_email_travel_date', $item['arrival_date'], $item ); // phpcs:ignore
		$email_travel_date = apply_filters( 'wptravel_email_travel_date', $email_travel_date, $item );
		$trip_ids[]               = $item['trip_id'];
		$pax_array[]              = $item['pax'];
		$price_keys[]             = $item['price_key'];
		$arrival_date[]           = $item['arrival_date'];
		$departure_date[]         = $item['departure_date'];
		$arrival_date_email_tag[] = $email_travel_date;
		$pricing_id[]             = isset( $item['pricing_id'] ) ? $item['pricing_id'] : 0; // @since v4.0
		$trip_time[]              = isset( $item['trip_time'] ) ? $item['trip_time'] : ''; // @since v4.0
		$total_pax               += $item['pax'];
	}

	if ( ! $allow_multiple_items || ( 1 === count( $items ) ) ) {
		$pax            = isset( $pax_array[0] ) ? $pax_array[0] : $pax;
		$price_key      = isset( $price_keys[0] ) ? $price_keys[0] : '';
		$arrival_date   = $arrival_date[0];
		$departure_date = $departure_date[0];
		$arrival_date_email_tag = wptravel_format_date( $arrival_date_email_tag[0], true, 'Y-m-d' );
	}
	$trip_id     = isset( $trip_ids[0] ) ? $trip_ids[0] : 0;
	$new_trip_id = $trip_ids;

	if ( empty( $trip_id ) ) {
		return;
	}
	
	$thankyou_page_url = wptravel_thankyou_page_url( $trip_id );

	// Insert Booking.
	$post_array = array(
		'post_title'   => '',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_slug'    => uniqid(),
		'post_type'    => 'itinerary-booking',
	);
	$booking_id = wp_insert_post( $post_array );
	// Update Booking Title.
	$update_data_array = array(
		'ID'         => $booking_id,
		'post_title' => 'Booking - # ' . $booking_id,
	);
	wp_update_post( $update_data_array );
	$settings       = wptravel_get_settings();
	
	
	$sanitized_data = wptravel_sanitize_array( $_POST ); // @phpcs:ignore

	if( class_exists( 'WooCommerce' ) && $settings['enable_woo_checkout'] == 'yes' ){ 
		if( !empty( $order_data ) ){
			$sanitized_data = array();
	
			$sanitized_data['wp_travel_fname_traveller'] = array(
				strtotime("now") => array(
					'0' =>	$order_data['billing']['first_name'] 
				)
			);
		
			$sanitized_data['wp_travel_lname_traveller'] = array(
				strtotime("now") => array(
					'0' =>	$order_data['billing']['last_name'] 
				)
			);
		
			$sanitized_data['wp_travel_country_traveller'] = array(
				strtotime("now") => array(
					'0' =>	$order_data['billing']['country'] 
				)
			);
		
			$sanitized_data['wp_travel_phone_traveller'] = array(
				strtotime("now") => array(
					'0' =>	$order_data['billing']['phone'] 
				)
			);
		
			$sanitized_data['wp_travel_email_traveller'] = array(
				strtotime("now") => array(
					'0' =>	$order_data['billing']['email'] 
				)
			);
		
			$sanitized_data['wp_travel_address'] = $order_data['billing']['address_1'];
		
			$sanitized_data['billing_city'] = $order_data['billing']['city'];
		
			$sanitized_data['wp_travel_country'] = $order_data['billing']['country'];
		
			$sanitized_data['billing_postal'] = $order_data['billing']['postcode'];
		
			if( $order_data['payment_method'] == 'cod' ){
				$sanitized_data['wp_travel_booking_option'] = 'booking_only';
			}else{
				$sanitized_data['wp_travel_booking_option'] = 'booking_with_payment';
			}
		}
	}

	if( isset( $_POST['wp_travel_checkout_gdpr_msg'] ) ){
		$sanitized_data['privacy_policy'] = true;
	}
	

	if( class_exists( 'WP_Travel_Pro' ) && isset( $settings['selected_booking_option'] ) && count( $settings['selected_booking_option'] ) == 1 && $settings['selected_booking_option'][0] = 'booking-with-payment' ){
		$sanitized_data['wp_travel_booking_option'] = 'booking_with_payment';
	}
	do_action( 'wpcrm_post_booking_user', $sanitized_data );
	// Updating Booking Metas.
	update_post_meta( $booking_id, 'order_data', $sanitized_data );
	update_post_meta( $booking_id, 'order_items_data', $items ); // @since 1.8.3
	update_post_meta( $booking_id, 'order_totals', $wt_cart->get_total() );
	update_post_meta( $booking_id, 'wp_travel_pax', $total_pax );

	


	$checkout_default_country = apply_filters( 'checkout_default_country', '' ); //@since 8.1.0

	if( !empty( $checkout_default_country ) ){
		update_post_meta( $booking_id, 'wp_travel_country', $checkout_default_country );
	}

	update_post_meta( $booking_id, 'wp_travel_booking_status', 'pending' );

	update_post_meta( $booking_id, 'wp_travel_trip_code', wptravel_get_trip_code( $trip_id ) );

	/**
	 * Update Arrival and Departure dates metas.
	 */

	update_post_meta( $booking_id, 'wp_travel_arrival_date', sanitize_text_field( $arrival_date ) );
	update_post_meta( $booking_id, 'wp_travel_departure_date', sanitize_text_field( $departure_date ) );
	update_post_meta( $booking_id, 'wp_travel_post_id', absint( $trip_id ) ); // quick fix [booking not listing in user dashboard].
	update_post_meta( $booking_id, 'wp_travel_arrival_date_email_tag', sanitize_text_field( $arrival_date_email_tag ) ); // quick fix arrival date with time.


	if( apply_filters( 'wptravel_checkout_enable_media_input', false ) == true ){
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		if( $_FILES ){

		// you can add some kind of validation here
			if( empty( $_FILES[ 'wptravel_checkout_media_field' ] ) ) {
				wp_die( 'No files selected.' );
			}

			$upload = wp_handle_upload( 
				$_FILES[ 'wptravel_checkout_media_field' ], 
				array( 'test_form' => false ) 
			);

			if( ! empty( $upload[ 'error' ] ) ) {
				wp_die( esc_html( $upload[ 'error' ] ) );
			}

			// it is time to add our uploaded image into WordPress media library
			$attachment_id = wp_insert_attachment(
				array(
					'guid'           => $upload[ 'url' ],
					'post_mime_type' => $upload[ 'type' ],
					'post_title'     => basename( $upload[ 'file' ] ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$upload[ 'file' ]
			);

			if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
				wp_die( 'Upload error.' );
			}

			// update medatata, regenerate image sizes
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			wp_update_attachment_metadata(
				$attachment_id,
				wp_generate_attachment_metadata( $attachment_id, $upload[ 'file' ] )
			);

			update_post_meta( $booking_id, 'wp_travel_checkout_media', sanitize_url( wp_get_attachment_url( $attachment_id ) ) );
		}
	}
	
	

	// Insert $_POST as Booking Meta.
	$post_ignore = array( '_wp_http_referer', 'wp_travel_security', '_nonce', 'wptravel_book_now', 'wp_travel_payment_amount' );

	
	foreach ( $sanitized_data as $meta_name => $meta_val ) {
		if ( in_array( $meta_name, $post_ignore, true ) ) {
			continue;
		}
		if ( is_array( $meta_val ) ) {
			$new_meta_value = array();
			foreach ( $meta_val as $key => $value ) {
				if ( is_array( $value ) ) {
					$new_meta_value[ $key ] = array_map( 'sanitize_text_field', $value );
					/**
					 * Quick fix for the field editor checkbox issue for the data save.
					 *
					 * @since 2.1.0
					 */
					if ( isset( $value[0] ) && is_array( $value[0] ) ) {
						$new_value = array();
						foreach ( $value as $nested_value ) {
							$new_value[] = implode( ', ', $nested_value );
						}
						$new_meta_value[ $key ] = array_map( 'sanitize_text_field', $new_value );
					}
				} else {
					$new_meta_value[ $key ] = sanitize_text_field( $value );
				}
			}
			update_post_meta( $booking_id, $meta_name, $new_meta_value );
		} else {
			update_post_meta( $booking_id, $meta_name, sanitize_text_field( $meta_val ) );
		}
	}

	$customer_email = isset( $_POST['wp_travel_email_traveller'] ) ? wptravel_sanitize_array( wp_unslash( $_POST['wp_travel_email_traveller'] ) ) : array(); // @phpcs:ignore
	reset( $customer_email );
	$first_key      = key( $customer_email );
	$customer_email = isset( $customer_email[ $first_key ][0] ) ? $customer_email[ $first_key ][0] : '';

	// Update single trip vals. // Need Enhancement. lots of loop with this $items in this functions.
	$i = 0; // need this to catch respective pricing_id and trip_id of the item in items loop
	foreach ( $items as $item_key => $trip ) {

		$trip_id      = $trip['trip_id'];
		$pax          = $trip['pax'];
		$price_key    = isset( $trip['price_key'] ) && ! empty( $trip['price_key'] ) ? $trip['price_key'] : false;
		$arrival_date = isset( $trip['arrival_date'] ) && ! empty( $trip['arrival_date'] ) ? $trip['arrival_date'] : '';

		$booking_count     = get_post_meta( $trip_id, 'wp_travel_booking_count', true );
		$booking_count     = ( isset( $booking_count ) && '' !== $booking_count ) ? $booking_count : 0;
		$new_booking_count = $booking_count + 1;
		update_post_meta( $trip_id, 'wp_travel_booking_count', sanitize_text_field( $new_booking_count ) );
		/**
		 * Coupon code and value store in booking
		 *
		 * @since 6.7.0
		 */
		update_post_meta( $booking_id, 'wp_travel_applied_coupon_data', $discount_coupon_data );
		/**
		 * Add Support for invertory addon options.
		 */
		wptravel_do_deprecated_action( 'wp_travel_update_trip_inventory_values', array( $trip_id, $pax, $price_key, $arrival_date, $booking_id ), '4.4.0', 'wp_travel_trip_inventory' );

		$args = array(
			'trip_id'       => $trip_id,
			'booking_id'    => $booking_id,
			'pricing_id'    => $pricing_id[ $i ],
			'pax'           => $pax,
			'selected_date' => $arrival_date, // [used in inventory].
			'time'          => $trip_time[ $i ],
			'price_key'     => $price_key, // Just for legacy. Note: Not used for inventory [For Email].
		);
		/**
		 * Trigger Update inventory values action.
		 *
		 * @hooked array( 'WP_Travel_Util_Inventory', 'update_inventory' )
		 * @since 4.0.0
		 */
		$inventory_args = apply_filters( 'wp_travel_inventory_args', $args ); // phpcs:ignore
		$inventory_args = apply_filters( 'wptravel_inventory_args', $inventory_args );

		do_action( 'wp_travel_trip_inventory', $inventory_args ); // phpcs:ignore
		do_action( 'wptravel_trip_inventory', $inventory_args );
		// End of Inventory.

		$i++;
	}
	
	/**
	 * Trigger Email functions. Sends Booking email to admin and client.
	 *
	 * @hooked array( 'WP_Travel_Email', 'send_booking_email' );
	 * @since 5.0.0
	 */

	do_action( 'wptravel_action_send_booking_email', $booking_id, wptravel_sanitize_array( $_POST ), $new_trip_id );
	/**
	 * Hook used to add payment and its info.
	 *
	 * @since 1.0.5 // For Payment.
	 */
	
	do_action( 'wp_travel_after_frontend_booking_save', $booking_id, $first_key ); // phpcs:ignore
	do_action( 'wptravel_after_frontend_booking_save', $booking_id, $first_key );
	
	if( $_POST['wp_travel_payment_gateway'] == 'paypal' ){
	
		do_action( 'wp_travel_standard_paypal_payment_process', $booking_id, $_POST['complete_partial_payment'] );
	}
	
	// Temp fixes [add payment id in case of booking only].

	$payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );
	if ( ! $payment_id ) {
		$title      = 'Payment - #' . $booking_id;
		$post_array = array(
			'post_title'   => $title,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_slug'    => uniqid(),
			'post_type'    => 'wp-travel-payment',
		);
		$payment_id = wp_insert_post( $post_array );
		update_post_meta( $booking_id, 'wp_travel_payment_id', $payment_id );
	}


	$require_login_to_checkout = isset( $settings['enable_checkout_customer_registration'] ) ? $settings['enable_checkout_customer_registration'] : 'no'; // if required login then there is registration option as well. so we continue if this is no.
	$create_user_while_booking = isset( $settings['create_user_while_booking'] ) ? $settings['create_user_while_booking'] : 'no';

	if ( is_user_logged_in() ) {
		$user    = wp_get_current_user();
		$user_id = $user->ID;
	} elseif ( 'no' === $require_login_to_checkout && 'yes' === $create_user_while_booking && ! is_user_logged_in() ) {
		$user_id = wptravel_create_new_customer( $customer_email );
	} else {
		$user_id = null;
	}
	if ( $user_id && ! is_wp_error( $user_id ) ) {
		$saved_booking_ids = get_user_meta( $user_id, 'wp_travel_user_bookings', true );
		$saved_booking_ids = ! $saved_booking_ids ? array() : $saved_booking_ids;
		array_push( $saved_booking_ids, $booking_id );
		update_user_meta( $user_id, 'wp_travel_user_bookings', $saved_booking_ids );
	}
	// Clear Transient To update booking Count.

	delete_post_meta( $trip_id, 'wp_travel_booking_count' );

	// Inc case of 100% discount.
	$cart_total = $wt_cart->get_total();
	if ( $cart_total['discount'] > 0 && ! $cart_total['total'] ) {
		update_post_meta( $booking_id, 'wp_travel_booking_status', 'booked' );
		update_post_meta( $payment_id, 'wp_travel_payment_status', 'paid' );

	}
	/**
	* Change payment mode N/A to full while payment full.
	 *
	* @since 6.6.0
	*/
	$payment_data   = wptravel_booking_data( $booking_id );
	$total_price    = isset( $payment_data['total'] ) ? $payment_data['total'] : 0;
	$payment_paid   = get_post_meta( $payment_id, 'wp_travel_payment_status', true );
	$booking_paid   = get_post_meta( $booking_id, 'wp_travel_payment_status', true );
	$partial_enable = get_post_meta( $payment_id, 'wp_travel_is_partial_payment', true );

	if( $discount_coupon_data['type'] == 'percentage' && $discount_coupon_data['value'] == '100' ){
		update_post_meta( $payment_id, 'wp_travel_payment_status', 'full_discount' );
		update_post_meta( $payment_id, 'wp_travel_payment_mode', 'full' );
	}

	if ( $booking_paid == 'paid' && $payment_paid == 'paid' && $partial_enable == 'no' && $total_price > 0 ) {
		update_post_meta( $payment_id, 'wp_travel_payment_mode', 'full' );
		update_post_meta( $payment_id, 'wp_travel_payment_amount', $total_price );
	}

	$affiliate = apply_filters( 'wp_travel_all_booking_data_list_for_slicewp', $booking_id, $user_id );

	// Clear Cart After process is complete.
	$wt_cart->clear();


	$reserved_booking_dates = array();

	$booking_args = array(
		'post_type'      => 'itinerary-booking', // Specify the custom post type
		'posts_per_page' => 50, // Get all posts
	);
	
	// Get the posts
	$booking_posts = get_posts( $booking_args );

	if ( !empty( $booking_posts ) ) {

		$i = 0;
		foreach ( $booking_posts as $post ) {

			$oreder_items = get_post_meta( $post->ID, 'order_items_data', true );

			foreach( $oreder_items as $item ){
				$reserved_booking_dates[$i]['id'] = $item['trip_id'];
				$reserved_booking_dates[$i]['date'] = $item['trip_start_date'];
				$i++;
			}
			
		}
		// Reset the global post object
		wp_reset_postdata();
	}
	update_option('wp_travel_reserve_date', $reserved_booking_dates);

	if( apply_filters( 'wp_travel_disable_default_thankyoupage', false ) == false ){
		$thankyou_page_url = add_query_arg( 'booked', true, $thankyou_page_url );
		$thankyou_page_url = add_query_arg( '_nonce', WP_Travel::create_nonce(), $thankyou_page_url );
		$thankyou_page_url = add_query_arg( 'order_id', $booking_id, $thankyou_page_url );
		header( 'Location: ' . $thankyou_page_url );
		exit;
	}

}



/**
 * Add to cart system
 * @since 7.1.0
 */
function wp_travel_add_to_cart_system() { 
	return apply_filters( 'wp_travel_add_to_cart_system', false );
}