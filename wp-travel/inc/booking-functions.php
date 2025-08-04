<?php
/**
 * Booking Functions.
 *
 * @package WP_Travel
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

	if( class_exists( 'WooCommerce' ) && $settings['enable_woo_checkout'] == 'yes' ){
		update_post_meta( $booking_id, 'refrence_woo_order_details', ( (int) $booking_id -1 ) );
		update_post_meta( $booking_id, 'booking_invoice_key', 'incoice'.( $booking_id - 5 ) );
	}
	
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

	$wt_cart->clear();

	if( get_option( 'wptravel_reserve_date' ) == 'yes' ){
		$reserved_booking_dates = array();

		$booking_args = array(
			'post_type'      => 'itinerary-booking',
			'posts_per_page' => 50,
		);
		
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
	}

	if( apply_filters( 'wp_travel_disable_default_thankyoupage', false ) == false ){
		if( apply_filters( 'wp_travel_woo_enable_onapage', false ) == false ){
			$thankyou_page_url = add_query_arg( 'booked', true, $thankyou_page_url );
			$thankyou_page_url = add_query_arg( '_nonce', WP_Travel::create_nonce(), $thankyou_page_url );
			$thankyou_page_url = add_query_arg( 'order_id', $booking_id, $thankyou_page_url );
			header( 'Location: ' . $thankyou_page_url );

			exit;
		}
	}

}

function wptravel_get_booking_chart() {

	global $wpdb;

	$total_booking = $wpdb->get_var("
		SELECT COUNT(DISTINCT pm.post_id)
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'wp_travel_booking_status'
		AND pm.meta_value = 'booked'
		AND p.post_status = 'publish'
	");

	$total_booking_last_month = $wpdb->get_var("
		SELECT COUNT(DISTINCT pm.post_id)
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'wp_travel_booking_status'
		AND pm.meta_value = 'booked'
		AND p.post_status = 'publish'
		AND p.post_date >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
		AND p.post_date < DATE_FORMAT(NOW(), '%Y-%m-01')
	");

	$total_booking_current_month = $wpdb->get_var("
		SELECT COUNT(DISTINCT pm.post_id)
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'wp_travel_booking_status'
		AND pm.meta_value = 'booked'
		AND p.post_status = 'publish'
		AND p.post_date >= DATE_FORMAT(NOW(), '%Y-%m-01')
		AND p.post_date < DATE_FORMAT(NOW() + INTERVAL 1 MONTH, '%Y-%m-01')
	");

	$booking_growth = 0;

	if ( $total_booking_last_month > 0 ) {
		$booking_growth = ( ( $total_booking_current_month - $total_booking_last_month ) / $total_booking_last_month ) * 100;
	}

	$total_canceled_booking = $wpdb->get_var("
		SELECT COUNT(DISTINCT pm.post_id)
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'wp_travel_booking_status'
		AND pm.meta_value = 'canceled'
		AND p.post_status = 'publish'
	");

 	$all_performing_trips = $wpdb->get_results("
		SELECT 
			trip_meta.meta_value AS trip_id,
			order_totals.meta_value AS order_total
		FROM {$wpdb->posts} AS booking_post
		INNER JOIN {$wpdb->postmeta} AS booking_status 
			ON booking_status.post_id = booking_post.ID 
			AND booking_status.meta_key = 'wp_travel_booking_status' 
			AND booking_status.meta_value = 'booked'
		INNER JOIN {$wpdb->postmeta} AS payment_status 
			ON payment_status.post_id = booking_post.ID 
			AND payment_status.meta_key = 'wp_travel_payment_status' 
			AND payment_status.meta_value = 'paid'
		INNER JOIN {$wpdb->postmeta} AS trip_meta 
			ON trip_meta.post_id = booking_post.ID 
			AND trip_meta.meta_key = 'wp_travel_post_id'
		INNER JOIN {$wpdb->postmeta} AS order_totals 
			ON order_totals.post_id = booking_post.ID 
			AND order_totals.meta_key = 'order_totals'
		WHERE booking_post.post_status = 'publish'
	", ARRAY_A);

	$booking_data = [];

	foreach ( $all_performing_trips as $booking ) {
		$trip_id = (int) $booking['trip_id'];
		$order_totals = maybe_unserialize( $booking['order_total'] );

		if ( isset( $order_totals['total'] ) ) {
			if ( ! isset( $booking_data[ $trip_id ] ) ) {
				$booking_data[ $trip_id ] = [
					'total_bookings' => 0,
					'total_revenue'  => 0,
				];
			}

			$booking_data[ $trip_id ]['total_bookings'] += 1;
			$booking_data[ $trip_id ]['total_revenue']  += floatval( $order_totals['total'] );
		}
	}

	uasort( $booking_data, function ( $a, $b ) {
		return $b['total_bookings'] <=> $a['total_bookings'];
	});

	

	$all_total_revenue = 0;

	foreach ( $booking_data as $trip ) {
		$all_total_revenue += $trip['total_revenue'];
	}

	$top_trip_id   = null;
	$top_trip_data = null;

	if ( ! empty( $booking_data ) ) {
		// Find the trip with the maximum number of bookings
		$top_trip_id = array_key_first(
			array_filter(
				$booking_data,
				fn($trip) => $trip['total_bookings'] === max(array_column($booking_data, 'total_bookings'))
			)
		);
		$top_trip_data = $booking_data[ $top_trip_id ];
		$top_trip_data['trip_id'] = $top_trip_id;
	}

	$best_selling_trip = $top_trip_data;

	$top_revenue_trip_id   = null;
	$top_revenue_trip_data = null;

	if ( ! empty( $booking_data ) ) {
		// Find the trip with the maximum number of bookings
		$top_revenue_trip_id = array_key_first(
			array_filter(
				$booking_data,
				fn($trip) => $trip['total_revenue'] === max(array_column($booking_data, 'total_revenue'))
			)
		);
		$top_revenue_trip_data = $booking_data[ $top_revenue_trip_id ];
		$top_revenue_trip_data['trip_id'] = $top_revenue_trip_id;
	}

	$top_revenue_trip_data = $top_revenue_trip_data;


	$start_date = date('Y-m-01', strtotime('first day of last month'));
	$end_date   = date('Y-m-t', strtotime('last day of last month'));

	$last_month_performing_trips = $wpdb->get_results("
		SELECT 
			trip_meta.meta_value AS trip_id,
			order_totals.meta_value AS order_total
		FROM {$wpdb->posts} AS booking_post
		INNER JOIN {$wpdb->postmeta} AS booking_status 
			ON booking_status.post_id = booking_post.ID 
			AND booking_status.meta_key = 'wp_travel_booking_status' 
			AND booking_status.meta_value = 'booked'
		INNER JOIN {$wpdb->postmeta} AS payment_status 
			ON payment_status.post_id = booking_post.ID 
			AND payment_status.meta_key = 'wp_travel_payment_status' 
			AND payment_status.meta_value = 'paid'
		INNER JOIN {$wpdb->postmeta} AS trip_meta 
			ON trip_meta.post_id = booking_post.ID 
			AND trip_meta.meta_key = 'wp_travel_post_id'
		INNER JOIN {$wpdb->postmeta} AS order_totals 
			ON order_totals.post_id = booking_post.ID 
			AND order_totals.meta_key = 'order_totals'
		WHERE booking_post.post_status = 'publish'
		AND booking_post.post_date BETWEEN '$start_date' AND '$end_date'
	", ARRAY_A);

	$booking_data = [];

	foreach ( $last_month_performing_trips as $booking ) {
		$trip_id = (int) $booking['trip_id'];
		$order_totals = maybe_unserialize( $booking['order_total'] );

		if ( isset( $order_totals['total'] ) ) {
			if ( ! isset( $booking_data[ $trip_id ] ) ) {
				$booking_data[ $trip_id ] = [
					'total_bookings' => 0,
					'total_revenue'  => 0,
				];
			}

			$booking_data[ $trip_id ]['total_bookings'] += 1;
			$booking_data[ $trip_id ]['total_revenue']  += floatval( $order_totals['total'] );
		}
	}

	uasort( $booking_data, function ( $a, $b ) {
		return $b['total_bookings'] <=> $a['total_bookings'];
	});


	$last_month_revenue = 0;

	foreach ( $booking_data as $trip ) {
		$last_month_revenue += $trip['total_revenue'];
	}

	$start_date = date('Y-m-01'); // First day of current month
	$end_date   = date('Y-m-t');  // Last day of current month


	$current_month_performing_trips = $wpdb->get_results("
		SELECT 
			trip_meta.meta_value AS trip_id,
			order_totals.meta_value AS order_total
		FROM {$wpdb->posts} AS booking_post
		INNER JOIN {$wpdb->postmeta} AS booking_status 
			ON booking_status.post_id = booking_post.ID 
			AND booking_status.meta_key = 'wp_travel_booking_status' 
			AND booking_status.meta_value = 'booked'
		INNER JOIN {$wpdb->postmeta} AS payment_status 
			ON payment_status.post_id = booking_post.ID 
			AND payment_status.meta_key = 'wp_travel_payment_status' 
			AND payment_status.meta_value = 'paid'
		INNER JOIN {$wpdb->postmeta} AS trip_meta 
			ON trip_meta.post_id = booking_post.ID 
			AND trip_meta.meta_key = 'wp_travel_post_id'
		INNER JOIN {$wpdb->postmeta} AS order_totals 
			ON order_totals.post_id = booking_post.ID 
			AND order_totals.meta_key = 'order_totals'
		WHERE booking_post.post_status = 'publish'
		AND booking_post.post_date BETWEEN '$start_date' AND '$end_date'
	", ARRAY_A);

	$booking_data = [];

	foreach ( $current_month_performing_trips as $booking ) {
		$trip_id = (int) $booking['trip_id'];
		$order_totals = maybe_unserialize( $booking['order_total'] );

		if ( isset( $order_totals['total'] ) ) {
			if ( ! isset( $booking_data[ $trip_id ] ) ) {
				$booking_data[ $trip_id ] = [
					'total_bookings' => 0,
					'total_revenue'  => 0,
				];
			}

			$booking_data[ $trip_id ]['total_bookings'] += 1;
			$booking_data[ $trip_id ]['total_revenue']  += floatval( $order_totals['total'] );
		}
	}

	uasort( $booking_data, function ( $a, $b ) {
		return $b['total_bookings'] <=> $a['total_bookings'];
	});

	$current_month_revenue = 0;

	foreach ( $booking_data as $trip ) {
		$current_month_revenue += $trip['total_revenue'];
	}

	$earning_growth = 0;

	if ( $last_month_revenue > 0 ) {
		$earning_growth = round( ( ( $current_month_revenue - $last_month_revenue ) / $last_month_revenue ) * 100, 2 );
	} elseif ( $current_month_revenue > 0 ) {
		$earning_growth = 'N/A'; // cannot compute growth from zero base
	} else {
		$earning_growth = 0;
	}

	$best_destination_results = $wpdb->get_results("
		SELECT 
			terms.term_id AS term_id,
			terms.name AS destination,
			COUNT(*) AS total_bookings,
			SUM(
				CAST(
					SUBSTRING_INDEX(
						SUBSTRING_INDEX(order_totals.meta_value, 'total\";d:', -1), 
						';', 
						1
					) AS UNSIGNED
				)
			) AS total_revenue
		FROM {$wpdb->posts} AS booking_post
		INNER JOIN {$wpdb->postmeta} AS booking_status 
			ON booking_status.post_id = booking_post.ID 
			AND booking_status.meta_key = 'wp_travel_booking_status' 
			AND booking_status.meta_value = 'booked'
		INNER JOIN {$wpdb->postmeta} AS payment_status 
			ON payment_status.post_id = booking_post.ID 
			AND payment_status.meta_key = 'wp_travel_payment_status' 
			AND payment_status.meta_value = 'paid'
		INNER JOIN {$wpdb->postmeta} AS trip_meta 
			ON trip_meta.post_id = booking_post.ID 
			AND trip_meta.meta_key = 'wp_travel_post_id'
		INNER JOIN {$wpdb->postmeta} AS order_totals 
			ON order_totals.post_id = booking_post.ID 
			AND order_totals.meta_key = 'order_totals'

		-- Taxonomy Join for destination
		INNER JOIN {$wpdb->term_relationships} AS rel 
			ON rel.object_id = trip_meta.meta_value
		INNER JOIN {$wpdb->term_taxonomy} AS tax 
			ON tax.term_taxonomy_id = rel.term_taxonomy_id 
			AND tax.taxonomy = 'travel_locations'
		INNER JOIN {$wpdb->terms} AS terms 
			ON terms.term_id = tax.term_id

		WHERE booking_post.post_status = 'publish'
			AND booking_post.post_date BETWEEN '$start_date' AND '$end_date'

		GROUP BY terms.term_id
		ORDER BY total_bookings DESC
		LIMIT 1
	", ARRAY_A);

	$top_revenue_destination = $wpdb->get_results("
		SELECT 
			trip_meta.meta_value AS trip_id,
			order_totals.meta_value AS order_total
		FROM {$wpdb->posts} AS booking_post
		INNER JOIN {$wpdb->postmeta} AS booking_status 
			ON booking_status.post_id = booking_post.ID 
			AND booking_status.meta_key = 'wp_travel_booking_status' 
			AND booking_status.meta_value = 'booked'
		INNER JOIN {$wpdb->postmeta} AS payment_status 
			ON payment_status.post_id = booking_post.ID 
			AND payment_status.meta_key = 'wp_travel_payment_status' 
			AND payment_status.meta_value = 'paid'
		INNER JOIN {$wpdb->postmeta} AS trip_meta 
			ON trip_meta.post_id = booking_post.ID 
			AND trip_meta.meta_key = 'wp_travel_post_id'
		INNER JOIN {$wpdb->postmeta} AS order_totals 
			ON order_totals.post_id = booking_post.ID 
			AND order_totals.meta_key = 'order_totals'
		WHERE booking_post.post_status = 'publish'
	", ARRAY_A);

	// Step 2: Group by trip_id
	$booking_data = [];

	foreach ( $top_revenue_destination as $booking ) {
		$trip_id = (int) $booking['trip_id'];
		$order_totals = maybe_unserialize( $booking['order_total'] );

		if ( isset( $order_totals['total'] ) ) {
			if ( ! isset( $booking_data[ $trip_id ] ) ) {
				$booking_data[ $trip_id ] = [
					'total_bookings' => 0,
					'total_revenue'  => 0,
				];
			}

			$booking_data[ $trip_id ]['total_bookings'] += 1;
			$booking_data[ $trip_id ]['total_revenue']  += floatval( $order_totals['total'] );
		}
	}

	// Step 3: Get destinations for each trip
	$trip_ids = array_keys( $booking_data );
	$trip_destinations = [];

	foreach ( $trip_ids as $trip_id ) {
		$terms = get_the_terms( $trip_id, 'travel_locations' );
		if ( is_array( $terms ) ) {
			$trip_destinations[ $trip_id ] = array_map( fn($term) => [
				'term_id' => $term->term_id,
				'name'    => $term->name,
			], $terms );
		}
	}

	// Step 4: Aggregate revenue per destination
	$destination_data = [];

	foreach ( $booking_data as $trip_id => $data ) {
		if ( ! isset( $trip_destinations[ $trip_id ] ) ) {
			continue;
		}

		foreach ( $trip_destinations[ $trip_id ] as $term ) {
			if ( ! isset( $destination_data[ $term['term_id'] ] ) ) {
				$destination_data[ $term['term_id'] ] = [
					'name'          => $term['name'],
					'total_revenue' => 0,
				];
			}

			$destination_data[ $term['term_id'] ]['total_revenue'] += $data['total_revenue'];
		}
	}

	// Step 5: Find the top revenue destination
	$top_destination_term_id = null;
	$top_destination_data = null;

	if ( ! empty( $destination_data ) ) {
		$top_destination_term_id = array_key_first(
			array_filter(
				$destination_data,
				fn($dest) => $dest['total_revenue'] === max(array_column($destination_data, 'total_revenue'))
			)
		);

		$top_destination_data = $destination_data[ $top_destination_term_id ];
		$top_destination_data['term_id'] = $top_destination_term_id;
	}

	// Final result
	$top_revenue_destination = $top_destination_data;


	$total_customers = $wpdb->get_var("
		SELECT COUNT(*)
		FROM {$wpdb->usermeta}
		WHERE meta_key = '{$wpdb->prefix}capabilities'
		AND meta_value LIKE '%wp-travel-customer%'
	");

	$total_customers_last_month = $wpdb->get_var("
		SELECT COUNT(*)
		FROM {$wpdb->users} u
		INNER JOIN {$wpdb->usermeta} um ON um.user_id = u.ID
		WHERE um.meta_key = '{$wpdb->prefix}capabilities'
		AND um.meta_value LIKE '%wp-travel-customer%'
		AND u.user_registered >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
		AND u.user_registered < DATE_FORMAT(NOW(), '%Y-%m-01')
	");

	$total_customers_current_month = $wpdb->get_var("
		SELECT COUNT(*)
		FROM {$wpdb->users} u
		INNER JOIN {$wpdb->usermeta} um ON um.user_id = u.ID
		WHERE um.meta_key = '{$wpdb->prefix}capabilities'
		AND um.meta_value LIKE '%wp-travel-customer%'
		AND u.user_registered >= DATE_FORMAT(NOW(), '%Y-%m-01')
		AND u.user_registered < DATE_FORMAT(NOW() + INTERVAL 1 MONTH, '%Y-%m-01')
	");

	$customer_growth = 0;

	if ( $total_customers_last_month > 0 ) {
		$customer_growth = ( ( $total_customers_current_month - $total_customers_last_month ) / $total_customers_last_month ) * 100;
	}

	?>
	<div class="wrap wptravel-report-page">
		<div class="page-header">
			<h3 class="wp-heading-inline" style="font-size: 2em;"><?php esc_html_e( 'Statistics Overview', 'wp-travel' ); ?></h3>
			<p><?php esc_html_e( "Welcome back! Here's what's happening with your bookings.", 'wp-travel' ); ?></p>
		</div>
		
		<div class="grid-container quick-status">
			<div class="grid-item">
				<h3><?php esc_html_e( "Total Bookings", 'wp-travel' ); ?></h3>
				<p class="count-number"><?php echo esc_html( $total_booking ); ?></p>
				<p> 
					<?php if( $booking_growth > 0 ): ?>

							<svg fill="#16C47F" width="20px" height="20px" viewBox="0 0 24 24" id="up-trend-round" data-name="Flat Line" class="icon flat-line"><path id="primary" d="M21,7l-6.79,6.79a1,1,0,0,1-1.42,0l-2.58-2.58a1,1,0,0,0-1.42,0L3,17" style="fill: none; stroke: #16C47F; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><polyline id="primary-2" data-name="primary" points="21 11 21 7 17 7" style="fill: none; stroke: #16C47F; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polyline></svg>
							<span style="color:#16C47F"> <?php echo abs( round( $booking_growth, 2 ) ) . ' % ' ?></span>
						<?php elseif ( $booking_growth < 0 ): ?>

							<svg fill="#E14434" width="20px" height="20px" viewBox="0 0 24 24" id="down-trend" class="icon line"><polyline id="primary" points="3 6 11 14 14 11 21 18" style="fill: none; stroke: #E14434; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5;"></polyline><polyline id="primary-2" data-name="primary" points="17 18 21 18 21 14" style="fill: none; stroke: #E14434; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5;"></polyline></svg>
							<span style="color:#E14434"> <?php echo abs( round( $booking_growth, 2 ) ) . ' % ' ?></span>
						<?php else: ?>
							<span style="color:#151515"> <?php echo '0 % ' ?></span>
					<?php endif; ?>
					<?php esc_html_e( "form last month", 'wp-travel' ); ?>
				</p>
			</div>
			<div class="grid-item">
				<h3><?php esc_html_e( "Total Earnings", 'wp-travel' ); ?></h3>
				<p class="count-number"><?php echo wptravel_get_formated_price_currency( $all_total_revenue, true ); ?></p>
				<p> 
					<?php if( $earning_growth > 0 ): ?>

							<svg fill="#16C47F" width="20px" height="20px" viewBox="0 0 24 24" id="up-trend-round" data-name="Flat Line" class="icon flat-line"><path id="primary" d="M21,7l-6.79,6.79a1,1,0,0,1-1.42,0l-2.58-2.58a1,1,0,0,0-1.42,0L3,17" style="fill: none; stroke: #16C47F; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><polyline id="primary-2" data-name="primary" points="21 11 21 7 17 7" style="fill: none; stroke: #16C47F; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polyline></svg>
							<span style="color:#16C47F"> <?php echo $earning_growth . ' % ' ?></span>
						<?php elseif ( $earning_growth < 0 ): ?>

							<svg fill="#E14434" width="20px" height="20px" viewBox="0 0 24 24" id="down-trend" class="icon line"><polyline id="primary" points="3 6 11 14 14 11 21 18" style="fill: none; stroke: #E14434; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5;"></polyline><polyline id="primary-2" data-name="primary" points="17 18 21 18 21 14" style="fill: none; stroke: #E14434; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5;"></polyline></svg>
							<span style="color:#E14434"> <?php echo $earning_growth . ' % ' ?></span>
						<?php else: ?>
							<span style="color:#151515"> <?php echo '0 % ' ?></span>
					<?php endif; ?>
					<?php esc_html_e( "form last month", 'wp-travel' ); ?>
				</p>
			</div>
			<div class="grid-item">
				<h3><?php esc_html_e( "Total Customers", 'wp-travel' ); ?></h3>
				
				<p class="count-number"><?php echo esc_html( $total_customers ); ?></p>
				<p> 
					<?php if( $customer_growth > 0 ): ?>

						<svg fill="#16C47F" width="20px" height="20px" viewBox="0 0 24 24" id="up-trend-round" data-name="Flat Line" class="icon flat-line"><path id="primary" d="M21,7l-6.79,6.79a1,1,0,0,1-1.42,0l-2.58-2.58a1,1,0,0,0-1.42,0L3,17" style="fill: none; stroke: #16C47F; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><polyline id="primary-2" data-name="primary" points="21 11 21 7 17 7" style="fill: none; stroke: #16C47F; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polyline></svg>
						<span style="color:#16C47F"> <?php echo abs( round( $customer_growth, 2 ) ) . ' % ' ?></span>
						<?php elseif ( $customer_growth < 0 ): ?>

						<svg fill="#E14434" width="20px" height="20px" viewBox="0 0 24 24" id="down-trend" class="icon line"><polyline id="primary" points="3 6 11 14 14 11 21 18" style="fill: none; stroke: #E14434; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5;"></polyline><polyline id="primary-2" data-name="primary" points="17 18 21 18 21 14" style="fill: none; stroke: #E14434; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5;"></polyline></svg>
						<span style="color:#E14434"> <?php echo abs( round( $customer_growth, 2 ) ) . ' % ' ?></span>
					<?php else: ?>
						<span style="color:#151515"> <?php echo '0 % ' ?></span>
					<?php endif; ?>
					<?php esc_html_e( "form last month", 'wp-travel' ); ?>
				</p>
				
			</div>
			<div class="grid-item">
				<h3><?php esc_html_e( "Total Cancelled Bookings", 'wp-travel' ); ?></h3>
				<p class="count-number"><?php echo esc_html( $total_canceled_booking ); ?></p>
			</div>
			
		</div>

		<div class="grid-container second-quick-status">
			<div class="grid-item">
				<h3>
					<?php 
						$itinerary_count = wp_count_posts( 'itineraries' )->publish;
						echo esc_html__( "Best Selling Trip", 'wp-travel' );
					?>
				</h3>

				<h4>
					<?php if($best_selling_trip['trip_id']): ?>
						<a href="<?php echo esc_url( get_the_permalink( $best_selling_trip['trip_id'] ) );?>" target="_blank"><?php echo esc_html( get_the_title( $best_selling_trip['trip_id'] ) );?></a>
						<?php else: ?>
						-
					<?php endif; ?>
					 
				</h4>
				<p>( <?php echo esc_html__( 'Total Bookings ' ) . $best_selling_trip['total_bookings']; ?> )</p>
			</div>
			<div class="grid-item">
				<h3><?php esc_html_e( "Highest Revenue Trip", 'wp-travel' ); ?></h3>
				<h4>
					<?php if($top_revenue_trip_data['trip_id']): ?>
						<a href="<?php echo esc_url( get_the_permalink( $top_revenue_trip_data['trip_id'] ) );?>" target="_blank"><?php echo esc_html( get_the_title( $top_revenue_trip_data['trip_id'] ) );?></a>
						<?php else: ?>
						-
					<?php endif; ?>
					  
				</h4>
				<p>( <?php echo esc_html__( 'Total Earnings ' ) . wptravel_get_formated_price_currency( $top_revenue_trip_data['total_revenue'] ); ?> )</p>
			</div>
			<div class="grid-item">
				<h3><?php esc_html_e( "Best Destination", 'wp-travel' ); ?></h3>
				<h4>
					<?php 
					if( $best_destination_results &&  $best_destination_results[0]['term_id']): ?>
						<a href="<?php echo esc_attr( get_term_link( (int)$best_destination_results[0]['term_id'] ) ); ?>" class="wp-travel-top-itineraries" target="_blank"><?php echo esc_html( $best_destination_results[0]['destination'] );?> </a>
						<?php else: ?>
						-
					<?php endif; ?>
				</h4>
				<?php if( $best_destination_results ): ?>
					<p>
						( <?php echo esc_html__( 'Total Bookings ' ) . $best_destination_results[0]['total_bookings']; ?> )
					</p>
				<?php endif; ?>
			</div>


			<div class="grid-item">
				<h3><?php esc_html_e( "Top Revenue Destination", 'wp-travel' ); ?></h3>
				<h4>
					<?php if($top_revenue_destination && $top_revenue_destination['term_id']): ?>
						<a href="<?php echo esc_attr( get_term_link( (int)$top_revenue_destination['term_id']) ); ?>" class="wp-travel-top-itineraries" target="_blank"><?php echo esc_html( $top_revenue_destination['name'] );?></a>
						<?php else: ?>
						-
					<?php endif; ?>
					
				</h4>
				<?php if( $top_revenue_destination ): ?>
				<p>( <?php echo esc_html__( 'Total Earnings ' ) . wptravel_get_formated_price_currency( $top_revenue_destination['total_revenue'] ); ?> )</p>
				<?php endif; ?>
			</div>
		</div>

		<?php if( (int)$total_booking > 0 ): ?>
			<div class="custom-data">
				
				<div class="main-chart">
					<div class="stat-toolbar">
						<form name="stat_toolbar" class="stat-toolbar-form" action="" method="get" >
							<input type="hidden" name="_nonce" value="<?php echo esc_attr( WP_Travel::create_nonce() ); ?>" />
							<input type="hidden" name="post_type" value="itinerary-booking" >
							<input type="hidden" name="page" value="booking_chart">
							
							<?php
							// @since 1.0.6 // Hook since
							do_action( 'wp_travel_before_stat_toolbar_fields' ); // phpcs:ignore
							do_action( 'wptravel_before_stat_toolbar_fields' );
							?>

							<div class="form-compare-stat clearfix ">
								<!-- Field groups -->
								<p class="field-group field-group-stat date-picker">
									<span class="field-label"><?php esc_html_e( 'From', 'wp-travel' ); ?>:</span>
									<input type="text" name="booking_stat_from" class="datepicker-from" class="form-control" id="fromdate1" />
									<label class="input-group-addon btn" for="fromdate1">
									<span class="dashicons dashicons-calendar-alt"></span>
									</label>
								</p>
								<p class="field-group field-group-stat date-picker">
									<span class="field-label"><?php esc_html_e( 'To', 'wp-travel' ); ?>:</span>
									<input type="text" name="booking_stat_to" class="datepicker-to" class="form-control"  id="fromdate2" />
									<label class="input-group-addon btn" for="fromdate2">
									<span class="dashicons dashicons-calendar-alt"></span>
									</label>
								</p>
							
								<?php
									$selected_interval = isset($_REQUEST['booking_intervals']) ? $_REQUEST['booking_intervals'] : 'all-time';
								?>
								
								<p class="field-group field-group-stat">
									<span class="field-label"><?php echo esc_html( 'Time Intervals:', 'wp-travel' ); ?></span>
									<select id="interval-selection" class="selectpicker form-control" name="booking_intervals">
										<option value="all-time" <?php selected($selected_interval, 'all-time'); ?>>
											<?php esc_html_e('All Time', 'wp-travel'); ?>
										</option>
										<option value="last-week" <?php selected($selected_interval, 'last-week'); ?>>
											<?php esc_html_e('Last Week', 'wp-travel'); ?>
										</option>
										<option value="last-month" <?php selected($selected_interval, 'last-month'); ?>>
											<?php esc_html_e('Last Month', 'wp-travel'); ?>
										</option>
										<option value="last-three-months" <?php selected($selected_interval, 'last-three-months'); ?>>
											<?php esc_html_e('Last Three Months', 'wp-travel'); ?>
										</option>
										<option value="last-six-months" <?php selected($selected_interval, 'last-six-months'); ?>>
											<?php esc_html_e('Last Six Months', 'wp-travel'); ?>
										</option>
										<option value="last-year" <?php selected($selected_interval, 'last-year'); ?>>
											<?php esc_html_e('Last Year', 'wp-travel'); ?>
										</option>
										<option value="custom" <?php selected($selected_interval, 'custom'); ?>>
											<?php esc_html_e('Custom Date', 'wp-travel'); ?>
										</option>
									</select>
								</p>

								<?php
								// @since 1.0.6 // Hook since
								do_action( 'wp_travel_after_stat_toolbar_fields' ); // phpcs:ignore
								do_action( 'wptravel_after_stat_toolbar_fields' );
								?>
								<div class="show-all btn-show-all" >
									<?php submit_button( esc_attr__( 'Show All', 'wp-travel' ), 'primary', 'submit' ); ?>
								</div>

							</div>

						</form>
					</div>
					<div class="loader-wrapper">
						<div class="loader"></div>
					</div>
					
					<div class="data-wrapper">
						<div class="left-block">
							<canvas id="wp-travel-booking-canvas"></canvas>
						</div>
						<div class="right-block">

							<div class="wp-travel-stat-info">

								<div class="right-block-single total-sales">
									<div>
										<strong><big class="wp-travel-total-sales"><?php echo wptravel_get_formated_price_currency( $all_total_revenue, true ); ?></big></strong><br />
									</div>
									
									<p><?php esc_html_e( 'Total Sales', 'wp-travel' ); ?></p>
								</div>

								<div class="right-block-single total-bookings">
									<div>
										<strong><big class="wp-travel-max-bookings"></big></strong><br />
									</div>
									
									<p><?php esc_html_e( 'Bookings', 'wp-travel' ); ?></p>

								</div>
								<div class="right-block-single top-destination">
									<div>
										<strong class="wp-travel-top-countries wp-travel-more">								
										</strong>
									</div>
									
									<p><?php esc_html_e( 'Top Destination', 'wp-travel' ); ?></p>
								</div>
								<div class="right-block-single top-trip">
									<div>
									<strong></strong>
									</div>
									
									<p><?php esc_html_e( 'Top itinerary', 'wp-travel' ); ?></p>
								</div>
							</div>

						</div>
						<div class="top-trips" style="display: flex; width: 96%; gap: 20px;">
							<div class="table-wrapper" style="width: 50%;">
								<h3><?php echo esc_html__( 'Top Performing Trips', 'wp-travel' ); ?></h3>
								<table class="top-bottom" style="width: 100%;" border="1" cellpadding="6" cellspacing="0">
									<thead>
										<tr>
											<th><?php echo esc_html__( 'SN', 'wp-travel' ); ?></th>
											<th><?php echo esc_html__( 'Trip Name', 'wp-travel' ); ?></th>
											<th><?php echo esc_html__( 'Booking Count', 'wp-travel' ); ?></th>
											<th><?php echo esc_html__( 'Total Revenue', 'wp-travel' ); ?></th>
										</tr>
									</thead>
									<tbody>
										
									</tbody>
								</table>
							</div>

							<div style="width: 50%;">
								<h3><?php echo esc_html__( 'Low Performing Trips', 'wp-travel' ); ?></h3>

								<table class="bottom-top" style="width: 100%;" border="1" cellpadding="6" cellspacing="0">
								<thead>
									<tr>
									<th><?php echo esc_html__( 'SN', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Trip Name', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Booking Count', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Total Revenue', 'wp-travel' ); ?></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								</table>
							</div>

						</div>

						<div class="top-destinations" style="display: flex; width: 96%; gap: 20px;">

							<div class="table-wrapper" style="width: 50%;">
								<h3><?php echo esc_html__( 'Top Performing Destinations', 'wp-travel' ); ?></h3>
								<table class="top-bottom" style="width: 100%;" border="1" cellpadding="6" cellspacing="0">
								<thead>
									<tr>
									<th><?php echo esc_html__( 'SN', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Destination Name', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Booking Count', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Total Revenue', 'wp-travel' ); ?></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								</table>
							</div>

							<div style="width: 50%;">
								<h3><?php echo esc_html__( 'Low Performing Destinations', 'wp-travel' ); ?></h3>

								<table class="bottom-top" style="width: 100%;" border="1" cellpadding="6" cellspacing="0">
								<thead>
									<tr>
									<th><?php echo esc_html__( 'SN', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Trip Name', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Booking Count', 'wp-travel' ); ?></th>
									<th><?php echo esc_html__( 'Total Revenue', 'wp-travel' ); ?></th>
									</tr>
								</thead>
								<tbody>
								
								</tbody>
								</table>
							</div>

						</div>
					</div>
					
				</div>			

				
			</div>
			
		<?php endif; ?>		

	</div>
	<?php
}

/**
 * Add to cart system
 * @since 7.1.0
 */
function wp_travel_add_to_cart_system() { 
	return apply_filters( 'wp_travel_add_to_cart_system', false );
}