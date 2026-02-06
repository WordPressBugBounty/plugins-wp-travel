<?php

/**
 * Retrieve the correct PayPal Redirect based on http/s
 * and "live" or "test" mode, i.e., sandbox.
 *
 * @return PayPal URI
 */
function wptravel_get_paypal_redirect_url( $ssl_check = false ) {

	if ( is_ssl() || ! $ssl_check ) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	if ( wptravel_test_mode() ) {
		$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
	} else {
		$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
	}

	return $paypal_uri;
}


/**
 * Listen for a $_GET request from our PayPal IPN.
 * This would also do the "set-up" for an "alternate purchase verification"
 */
function wptravel_listen_paypal_ipn() {


	// if ( isset( $_POST['payer_id'] ) && isset( $_POST['txn_id'] ) ) {
	// 	do_action( 'wp_travel_verify_paypal_ipn' );
    // }

	if ( isset( $_POST['payer_id'] ) && isset( $_POST['txn_id'] ) ) {
        // Get current user's email
        $current_user_email = '';

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $current_user_email = $current_user->user_email;
        }

        // Pass the current user's email to the action
        do_action( 'wp_travel_verify_paypal_ipn', $current_user_email );
    }

}
add_action( 'init', 'wptravel_listen_paypal_ipn' );


/**
 * When a payment is made PayPal will send us a response and this function is
 * called. From here we will confirm arguments that we sent to PayPal which
 * the ones PayPal is sending back to us.
 * This is the Pink Lilly of the whole operation.
 */
function wptravel_paypal_ipn_process( $current_user_email ) {


		$settings              = wptravel_get_settings();

		$message = null;
	
		if ( $_POST['mc_currency'] != $settings['currency'] ) { // @phpcs:ignore
			$message .= "\nCurrency does not match those assigned in settings\n";
		}

		/**
		 * Check if this payment was already processed
		 *
		 * PayPal transaction id (txn_id) is stored in the database, we check
		 * that against the txn_id returned.
		 */
		$booking_id = isset( $_POST['custom'] ) ? absint( $_POST['custom'] ) : 0;
		$txn_id     = get_post_meta( $booking_id, 'txn_id', true );
		if ( empty( $txn_id ) ) {
			update_post_meta( $booking_id, 'txn_id', sanitize_text_field( $_POST['txn_id'] ) );
		} else {
			$message .= "\nThis payment was already processed\n";
		}

		/**
		 * Verify the payment is set to "Completed".
		 *
		 * Create a new payment, send customer an email and empty the cart
		 */

		if ( ! empty( $_POST['payer_status'] ) && $_POST['payer_status'] == 'VERIFIED' && ! isset( $_GET['partial'] ) ) { // @phpcs:ignore
				
			// Fixed Paypal booking step 
			set_post_type( $booking_id, 'itinerary-booking' );
						
			$booking_form_data = get_post_meta( $booking_id, 'order_data', true );


			$customer_email = isset( $booking_form_data['wp_travel_email_traveller'] ) ? wptravel_sanitize_array( wp_unslash( $booking_form_data['wp_travel_email_traveller'] ) ) : array();
			reset( $customer_email );
			$first_key      = key( $customer_email );

			do_action( 'wptravel_action_send_booking_email', $booking_id, $booking_form_data, $booking_form_data['new_trip_id'] );
			
			do_action( 'wp_travel_after_frontend_booking_save', $booking_id, $first_key );
			do_action( 'wptravel_after_frontend_booking_save', $booking_id, $first_key );

			do_action( 'wptravel_save_bookings_data_google_sheet', $booking_id );

			// End of Fixed Paypal booking step 
			
			// Update booking status and Payment args.
				update_post_meta( $booking_id, 'wp_travel_booking_status', 'booked' );
				$payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );

				$payment_ids = array();
				// get previous payment ids.
				$payment_id  = get_post_meta( $booking_id, 'wp_travel_payment_id', true );
				$paypal_args = get_post_meta( $booking_id, '_paypal_args', true );

			if ( '' !== $paypal_args ) { // Partial Payment.
				if ( is_string( $payment_id ) && '' !== $payment_id ) {
					$payment_ids[] = $payment_id;
				} else {
					$payment_ids = $payment_id;
				}

				// insert new payment id and update meta.
				$title          = 'Payment - #' . $booking_id;
				$post_array     = array(
					'post_title'   => $title,
					'post_content' => '',
					'post_status'  => 'publish',
					'post_slug'    => uniqid(),
					'post_type'    => 'wp-travel-payment',
				);
				$new_payment_id = wp_insert_post( $post_array );
				$payment_ids[]  = $new_payment_id;
				update_post_meta( $booking_id, 'wp_travel_payment_id', $payment_ids );

				$payment_method = 'paypal';
				$amount         = sanitize_text_field( wp_unslash( $_POST['mc_gross'] ) );
				$detail         = wptravel_sanitize_array( $_POST );

				update_post_meta( $new_payment_id, 'wp_travel_payment_gateway', $payment_method );

				update_post_meta( $new_payment_id, 'wp_travel_payment_amount', $amount );
				
		
				if( $_POST['payment_status'] == 'Completed' ){
		
					update_post_meta( $new_payment_id, 'wp_travel_payment_status', 'paid' );
				}else{
	
					update_post_meta( $new_payment_id, 'wp_travel_payment_status', 'pending' );
				}
				
				update_post_meta( $new_payment_id, 'wp_travel_payment_mode', 'partial' );

				$json = sanitize_text_field( wp_unslash( $_POST['payment_details'] ) );
				wptravel_update_payment_status( $booking_id, $amount, 'paid', $detail, sprintf( '_%s_args', $payment_method ), $new_payment_id );

			} else { 
                
				
				update_post_meta( $payment_id, '_paypal_args', wptravel_sanitize_array( $_POST ) );
				if( $_GET['payment'] == 'partial' ){
					if( $_POST['payment_status'] == 'Completed' ){
						update_post_meta( $payment_id, 'wp_travel_payment_status', 'partially_paid' );
					}else{
						update_post_meta( $payment_id, 'wp_travel_payment_status', 'pending' );
					}
				}elseif( $_GET['payment'] == 'full' ){
				    
					if( $_POST['payment_status'] == 'Completed' ){
						update_post_meta( $payment_id, 'wp_travel_payment_status', 'paid' );
					}else{
					   
						update_post_meta( $payment_id, 'wp_travel_payment_status', 'pending' );
					}
				}else{
					if( $_POST['payment_status'] == 'Completed' ){
						update_post_meta( $payment_id, 'wp_travel_payment_status', 'paid' );
					}else{
						update_post_meta( $payment_id, 'wp_travel_payment_status', 'pending' );
					}
				}

				update_post_meta( $payment_id, 'wp_travel_payment_mode', 'full' );

				if( $_GET['payment'] == 'partial' ){ 
					update_post_meta( $payment_id, 'wp_travel_payment_mode', 'partial' );
				}
				
				update_post_meta( $payment_id, 'wp_travel_payment_amount', sanitize_text_field( $_POST['mc_gross'] ) );

				do_action( 'wp_travel_after_successful_payment', $booking_id );
			}
		} elseif( ! empty( $_POST['payer_status'] ) && $_POST['payer_status'] == 'VERIFIED' && isset( $_GET['partial'] ) ) {
			
				
				// // Fixed Paypal booking step 
				// set_post_type( $booking_id, 'itinerary-booking' );
							
				// $booking_form_data = get_post_meta( $booking_id, 'order_data', true );

				// $customer_email = isset( $booking_form_data['wp_travel_email_traveller'] ) ? wptravel_sanitize_array( wp_unslash( $booking_form_data['wp_travel_email_traveller'] ) ) : array();
				// reset( $customer_email );
				// $first_key      = key( $customer_email );

				// do_action( 'wptravel_action_send_booking_email', $booking_id, $booking_form_data, $booking_form_data['new_trip_id'] );
				
				// do_action( 'wp_travel_after_frontend_booking_save', $booking_id, $first_key );
				// do_action( 'wptravel_after_frontend_booking_save', $booking_id, $first_key );

				// do_action( 'wptravel_save_bookings_data_google_sheet', $booking_id );

				$payment_gateway = 'paypal';
				$booking_id      = (int)$_GET['booking_id'];
				
				$payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );

				
				$new_payment_id = apply_filters( 'wptravel_before_insert_partial_payment', $payment_id, $booking_id, $payment_gateway );

				$detail = wptravel_sanitize_array( $_POST );

				$amount = sanitize_text_field( $_POST['mc_gross'] ); // @since 1.0.7

				wptravel_update_payment_status( $booking_id, $amount, 'paid', $detail, sprintf( '_%s_args', $payment_gateway ), $new_payment_id );

				if ( function_exists( 'wptravel_get_settings' ) ) {
					$settings = wptravel_get_settings();
				} else {
					$settings = wp_travel_get_settings();
				}

				// if ( function_exists( 'wptravel_booking_data' ) ) {
				// 	$details     = wptravel_booking_data( $booking_id );
				// } else {
				// 	$details     = wp_travel_booking_data( $booking_id );
				// }
				// $booking_option = $details['booking_option'];

				// // Need to update payment meta here.
				// do_action( 'wp_travel_after_partial_payment_complete' );

				// Added since 4.3.4, if the trip is book only first, then if user pay it in next time, this divert to full payment mode and full pay email will be sent. ( This needs enhancement ).
				// if ( 'booking_only' === $booking_option ) {
				// 	wptravel_send_email_payment( $booking_id );
				// 	return;
				// }
				// Send Partial Payment Complete email here.
				$send_email_to_admin = ( isset( $settings['send_booking_email_to_admin'] ) && '' !== $settings['send_booking_email_to_admin'] ) ? $settings['send_booking_email_to_admin'] : 'yes';

				$current_user = wp_get_current_user();

				$client_email = $current_user->user_email;

				// If client email is empty get the email of main traveller
				if( !$client_email ){
					$client_email =  get_post_meta( $booking_id, 'wp_travel_email_traveller', true );
					$client_email = !empty($client_email) && is_array($client_email) ? array_shift($client_email) : null;
				}
				

				$admin_email  = get_option( 'admin_email' );

				// Email Variables.
				if ( is_multisite() ) {
					$sitename = get_network()->site_name;
				} else {
					$sitename = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
				}

				// $amount = isset( $_POST['amount'] ) ? sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : 0;

				$email_tags = array(
					'{sitename}'      => $sitename,
					'{booking_id}'    => $booking_id,
					'{customer_name}' => $current_user->display_name,
					// '{amount}'        => wptravel_get_currency_symbol() . ' ' . $amount,
					'{amount}'        => wptravel_get_formated_price_currency( $amount, false, '', $booking_id ), // @since 1.0.5,
				);

				$email = new WP_Travel_Emails();

				$admin_template = $email->wptravel_get_email_template( 'partial_payment', 'admin' );

				$admin_message_data  = $admin_template['mail_header'];
				$admin_message_data .= $admin_template['mail_content'];
				$admin_message_data .= $admin_template['mail_footer'];
				// Admin message.
				$admin_message = str_replace( array_keys( $email_tags ), $email_tags, $admin_message_data );
				// Admin Subject.
				$admin_subject = $admin_template['subject'];

				// Client Template.
				$client_template = $email->wptravel_get_email_template( 'partial_payment', 'client' );

				$client_message_data  = $client_template['mail_header'];
				$client_message_data .= $client_template['mail_content'];
				$client_message_data .= $client_template['mail_footer'];

				// Client message.
				$client_message = str_replace( array_keys( $email_tags ), $email_tags, $client_message_data );

				// Client Subject.
				$client_subject = $client_template['subject'];

				$reply_to_email = isset( $settings['wp_travel_from_email'] ) ? $settings['wp_travel_from_email'] : $admin_email;

				// Send mail to admin if booking email is set to yes.
				if ( 'yes' === $send_email_to_admin ) {

					// To send HTML mail, the Content-type header must be set.
					$headers = $email->email_headers( $reply_to_email, $client_email );

					if ( ! wp_mail( $admin_email, $admin_subject, $admin_message, $headers ) ) {
						WP_Travel()->notices->add( '<strong>' . __( 'Error:', 'wp-travel-pro' ) . '</strong> ' . __( 'Email could not be sent.', 'wp-travel-pro' ), 'error' );
					}
				}

				// Send email to client.
				// To send HTML mail, the Content-type header must be set.
				$headers = $email->email_headers( $reply_to_email, $reply_to_email );

				if ( ! wp_mail( $client_email , $client_subject, $client_message, $headers ) ) {
						WP_Travel()->notices->add( '<strong>' . __( 'Error:', 'wp-travel-pro' ) . '</strong> ' . __( 'Emailss could not be sent.', 'wp-travel-pro' ), 'error' );
				}


				WP_Travel()->notices->add( __( 'Partial Payment Success.', 'wp-travel-pro' ), 'success' );

				if ( function_exists(  'slicewp_get_setting' ) ) {
					$slicewp_settings = slicewp_get_setting( 'active_integrations' );
					if ( in_array( 'wptravel', $slicewp_settings ) ){
						global $wpdb;

						$reference_value = $booking_id;
						$new_status = 'pending';

						if( wptravel_booking_data( $booking_id )['payment_status'] == 'paid' ){
							$new_status = 'unpaid';
						}

						$wpdb->update(
							"{$wpdb->prefix}slicewp_commissions",
							array( 'status' => $new_status ),        
							array( 'reference' => $reference_value ),
							array( '%s' ),
							array( '%s' )
						);
					}
				}

				wp_redirect( $thankyou_page );
				die;

			
		}else {

			$message .= "\nPayment status not set to Completed\n";

		}    

}
add_action( 'wp_travel_verify_paypal_ipn', 'wptravel_paypal_ipn_process' );
