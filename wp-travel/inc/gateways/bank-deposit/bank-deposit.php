<?php

require_once dirname( __FILE__ ) . '/settings.php';

function wptravel_booking_bank_deposit( $booking_id ) {
	if ( ! $booking_id ) {
		return;
	}
	if (
		! WP_Travel::verify_nonce( true )
		|| ! isset( $_POST['wp_travel_book_now'] ) // @phpcs:ignore
		) {
		return;
	}

	$gateway = isset( $_POST['wp_travel_payment_gateway'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_travel_payment_gateway'] ) ) : '';
	if ( 'bank_deposit' === $gateway ) {

		$payment_id = wptravel_get_payment_id( $booking_id );

		$payment_mode = get_post_meta( $payment_id, 'wp_travel_payment_mode', true );
		update_post_meta( $booking_id, 'wp_travel_booking_status', 'booked' );
		update_post_meta( $payment_id, 'wp_travel_payment_status', 'waiting_voucher' );
	}

}

add_action( 'wp_travel_after_frontend_booking_save', 'wptravel_booking_bank_deposit' );

function wptravel_submit_bank_deposit_slip() {

	if ( isset( $_POST['complete_partial_payment'] ) && isset( $_POST['wp_travel_payment_gateway'] ) && $_POST['wp_travel_payment_gateway'] == 'bank_deposit' ) { 
		$payment_gateway = 'bank_deposit';

		$booking_id      = sanitize_text_field( wp_unslash( $_POST['wp_travel_booking_id'] ) );

		$payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );

		$new_payment_id = apply_filters( 'wptravel_before_insert_partial_payment', $payment_id, $booking_id, $payment_gateway );

		update_post_meta( $new_payment_id, '_bank_deposit_args', array() );
		update_post_meta( $new_payment_id, 'wp_travel_payment_slip_name', '' );
		update_post_meta( $new_payment_id, 'wp_travel_payment_status', 'waiting_voucher' );

	}

	if ( isset( $_POST['wp_travel_submit_slip'] ) ) {

		if ( ! isset( $_POST['booking_id'] ) ) {
			return;
		}

		if (
			! isset( $_POST['wp_travel_security'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_travel_security'] ) ), 'wp_travel_security_action' )
			) {
			return;
		}

		$settings = wptravel_get_settings();

		$allowed_files = apply_filters( 'wp_travel_bank_deposit_allowed_files', 'jpg, png, txt, pdf' );

		$allowed_files = str_replace( ' ', '', $allowed_files );
		$allowed_ext   = explode( ',', $allowed_files );
		$target_dir    = WP_CONTENT_DIR . '/' . WP_TRAVEL_SLIP_UPLOAD_DIR . '/';
		if ( ! file_exists( $target_dir ) ) {
			$created = mkdir( $target_dir, 0755, true );

			if ( ! $created ) {
				WPTravel()->notices->add( __( 'Unable to create directory "wp-travel-slip"', 'wp-travel' ), 'error' );
			}
		}
		$filename    = substr( md5( rand( 1, 1000000 ) ), 0, 10 ) . '-' . basename( $_FILES['wp_travel_bank_deposit_slip']['name'] );
		$target_file = $target_dir . $filename;
		$tmp_name    = '';
		if ( isset( $_FILES['wp_travel_bank_deposit_slip']['tmp_name'] ) ) {
			$tmp_name = sanitize_text_field( wp_unslash( $_FILES['wp_travel_bank_deposit_slip']['tmp_name'] ) );
		}

		$ext = strtolower( pathinfo( $target_file, PATHINFO_EXTENSION ) );

		$upload_ok = false;
		if ( in_array( $ext, $allowed_ext ) ) {
			if ( isset( $_FILES['wp_travel_bank_deposit_slip']['tmp_name'] ) ) {
				$move = move_uploaded_file( $_FILES['wp_travel_bank_deposit_slip']['tmp_name'], $target_file );
				if ( $move ) {
					$upload_ok = true;
				}
			}
		} else {

			WPTravel()->notices->add( __( 'Uploaded files are not allowed.', 'wp-travel' ), 'error' );
			$upload_ok = false;
		}

		// Update status if file is uploaded. and save image path to meta.
		if ( true === $upload_ok ) {

			
			$booking_id = absint( $_POST['booking_id'] );
			$txn_id     = isset( $_POST['wp_travel_bank_deposit_transaction_id'] ) ? sanitize_text_field( $_POST['wp_travel_bank_deposit_transaction_id'] ) : '';
			$data       = wptravel_booking_data( $booking_id );	
			
			$payment_id     = get_post_meta( $booking_id, 'wp_travel_payment_id', true );
			$payment_id = $payment_id[count(get_post_meta( $booking_id, 'wp_travel_payment_id', true ))-1];

			$total = $data['total'];

			$amount = $total;
			$amount = wptravel_get_formated_price( $amount );

			do_action( 'wt_before_payment_process', $booking_id );

			$detail['amount'] = $amount;
			$detail['txn_id'] = $txn_id;

			if ( isset( $_POST['wp_travel_payment_mode'] ) && 'partial' == $_POST['wp_travel_payment_mode'] ) {
				$detail['amount'] = get_post_meta( $payment_id, 'wp_travel_payment_amount', true );
			
			}


			if( isset( $_POST['wp_travel_payment_mode'] ) && 'full' == $_POST['wp_travel_payment_mode'] ){
				$previous_amount = get_post_meta( $payment_id-1, 'wp_travel_payment_amount', true );
				if( empty( $previous_amount ) ){
					$detail['amount'] = wptravel_get_formated_price( $data['total'] );
				}else{
					$detail['amount'] = wptravel_get_formated_price( $data['total'] - $previous_amount );
				}
				update_post_meta( $payment_id, 'wp_travel_payment_amount', sanitize_text_field( $detail['amount'] ) );
			}
			
			
			
			$payment_method = get_post_meta( $payment_id, 'wp_travel_payment_gateway', true );
			update_post_meta( $payment_id, 'wp_travel_payment_gateway', sanitize_text_field( $payment_method ) );
			update_post_meta( $payment_id, 'wp_travel_payment_slip_name', sanitize_text_field( $filename ) );

		
			wptravel_update_payment_status( $booking_id, $amount, 'voucher_submited', $detail, sprintf( '_%s_args', $payment_method ), $payment_id );

			update_post_meta( $payment_id, 'wp_travel_payment_amount', sanitize_text_field( $detail['amount'] ) );
			


			if ( function_exists( 'wptravel_get_settings' ) ) {
				$settings = wptravel_get_settings();
			} else {
				$settings = wp_travel_get_settings();
			}

			$send_email_to_admin = ( isset( $settings['send_booking_email_to_admin'] ) && '' !== $settings['send_booking_email_to_admin'] ) ? $settings['send_booking_email_to_admin'] : 'yes';

			$current_user = wp_get_current_user();

			$client_email = $current_user->user_email;
			$admin_email  = get_option( 'admin_email' );

			// Email Variables.
			if ( is_multisite() ) {
				$sitename = get_network()->site_name;
			} else {
				$sitename = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}
			
			$email_tags = array(
				'{sitename}'      => $sitename,
				'{booking_id}'    => $booking_id,
				'{customer_name}' => $current_user->display_name,
				// '{amount}'        => wptravel_get_currency_symbol() . ' ' . $amount,
				'{amount}'        => wptravel_get_formated_price_currency( $detail['amount'], false, '', $booking_id ), // @since 1.0.5,
			);

			$email = new WP_Travel_Emails();

			$admin_template = $email->wptravel_get_email_template( 'partial_payment', 'admin' );

			if( get_post_meta( $booking_id, 'wp_travel_bank_payment_mode' )[0] == 'full' ){
				$admin_template['mail_header'] = str_replace( 'Partial Payment', 'Full Payment', $admin_template['mail_header'] );
				$admin_template['mail_content'] = str_replace( 'partial payment', 'payment', $admin_template['mail_content'] );
				$admin_template['subject'] = 'Full Payment';
			}

			$admin_message_data  = $admin_template['mail_header'];
			$admin_message_data .= $admin_template['mail_content'];
			$admin_message_data .= $admin_template['mail_footer'];

			// Admin message.
			$admin_message = str_replace( array_keys( $email_tags ), $email_tags, $admin_message_data );
			// Admin Subject.
			$admin_subject = $admin_template['subject'];

			// Client Template.
			$client_template = $email->wptravel_get_email_template( 'partial_payment', 'client' );

			if( get_post_meta( $booking_id, 'wp_travel_bank_payment_mode' )[0] == 'full' ){
				$client_template['mail_header'] = str_replace( 'Partial Payment', 'Full Payment', $client_template['mail_header'] );
				$client_template['mail_content'] = str_replace( 'partial payment', 'payment', $client_template['mail_content'] );
				$client_template['subject'] = 'Full Payment Received';
			}

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

			if ( ! wp_mail( $client_email, $client_subject, $client_message, $headers ) ) {
				WP_Travel()->notices->add( '<strong>' . __( 'Error:', 'wp-travel-pro' ) . '</strong> ' . __( 'Email could not be sent.', 'wp-travel-pro' ), 'error' );
			}

			if( get_post_meta( $booking_id, 'wp_travel_bank_payment_mode' )[0] == 'full' ){ 
				WP_Travel()->notices->add( __( 'Full Payment Success.', 'wp-travel-pro' ), 'success' );
			}else{
				WP_Travel()->notices->add( __( 'Partial Payment Success.', 'wp-travel-pro' ), 'success' );
			}
			

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

			// global $wp;
			// $thankyou_page = home_url( $wp->request );

			// wp_redirect( $thankyou_page );
			// die;

			do_action( 'wp_travel_after_successful_payment', $booking_id );

			// if( is_array( $payment_id ) ){
				
				// foreach( $payment_id as $data ){

				// 	if( !get_post_meta( (int)$data, 'wp_travel_payment_slip_name', true ) ){
					
				// 		$amount = get_post_meta( (int)$data, 'wp_travel_payment_amount', true );
				// 		$detail['amount'] = $amount;

				// 		if ( isset( $_POST['wp_travel_payment_mode'] ) && 'full' == $_POST['wp_travel_payment_mode'] ) {
				// 			$detail['amount'] = $total_trip_price;
				// 			update_post_meta( (int)$data+1, 'wp_travel_payment_amount', $detail['amount'] );
				// 			update_post_meta( (int)$data+1, 'wp_travel_payment_status', 'voucher_submited' );
				// 		}

				// 		$payment_method = get_post_meta( (int)$data, 'wp_travel_payment_gateway', true );
				// 		update_post_meta( (int)$data, 'wp_travel_payment_gateway', sanitize_text_field( $payment_method ) );
				// 		update_post_meta( (int)$data, 'wp_travel_payment_slip_name', sanitize_text_field( $filename ) );

				// 		wptravel_update_payment_status( $booking_id, $amount, 'voucher_submited', $detail, sprintf( '_%s_args', $payment_method ), (int)$data );
				// 		do_action( 'wp_travel_after_successful_payment', $booking_id );
				// 	}
				// }
				// $payment_method = get_post_meta( $payment_id, 'wp_travel_payment_gateway', true );
				// update_post_meta( $payment_id, 'wp_travel_payment_gateway', sanitize_text_field( $payment_method ) );
				// update_post_meta( $payment_id, 'wp_travel_payment_slip_name', sanitize_text_field( $filename ) );
			// }else{
			// 	$payment_method = get_post_meta( $payment_id, 'wp_travel_payment_gateway', true );
			// 	update_post_meta( $payment_id, 'wp_travel_payment_gateway', sanitize_text_field( $payment_method ) );
			// 	update_post_meta( $payment_id, 'wp_travel_payment_slip_name', sanitize_text_field( $filename ) );

			// 	wptravel_update_payment_status( $booking_id, $amount, 'voucher_submited', $detail, sprintf( '_%s_args', $payment_method ), $payment_id );
			// 	do_action( 'wp_travel_after_successful_payment', $booking_id );
			// }

		}
	}
}

add_action( 'init', 'wptravel_submit_bank_deposit_slip' );


function wptravel_bank_deposite_button( $booking_id = null, $details = array() ) {

	if ( ! WP_Travel::verify_nonce( true ) ) {
		return $booking_id;
	}

	// In Case of partial payment activated.
	if ( ! $booking_id ) {
		$booking_id = isset( $_GET['detail_id'] ) ? absint( $_GET['detail_id'] ) : 0;
	}
	if ( ! $booking_id ) {
		return;
	}
	$enabled_payment_gateways = wptravel_enabled_payment_gateways();
	$details                  = wptravel_booking_data( $booking_id );
	if ( in_array( 'bank_deposit', $enabled_payment_gateways, true ) && in_array( $details['payment_status'], array( 'waiting_voucher' ), true ) ) :
		if ( ! class_exists( 'WP_Travel_Partial_Payment_Core' ) ) :
			$details['due_amount'] = apply_filters( 'wp_travel_partial_payment_due_amount', $details['due_amount'] );
			?>
			<div class="wp-travel-form-field  wp-travel-text-info">
				<label for="wp-travel-amount-info"><?php esc_html_e( 'Amount', 'wp-travel' ); ?></label>
				<div class="wp-travel-text-info"><?php echo wptravel_get_formated_price_currency( $details['due_amount'], false, '', $booking_id ); //phpcs:ignore ?></div>
			</div>
		<?php endif; ?>
		<div class="wp-travel-bank-deposit-wrap">
			<h3 class="my-order-single-title"><?php esc_html_e( 'Bank Payment', 'wp-travel' ); ?></h3>
			<a href="#wp-travel-bank-deposit-content" class="wp-travel-upload-slip wp-travel-magnific-popup button"><?php esc_html_e( 'Submit Payment Receipt', 'wp-travel' ); ?></a>
			<a href="#wp-travel-bank-details-content" class="wp-travel-magnific-popup view-bank-deposit-button" style="display:block; padding:5px 0" ><?php esc_html_e( 'View Bank Details', 'wp-travel' ); ?></a>
		</div>
		<?php
	endif;
}

add_action( 'wp_travel_dashboard_booking_after_detail', 'wptravel_bank_deposite_button', 20, 2 );

function wptravel_bank_deposite_content( $booking_id = null, $details = array() ) {
	if ( ! WP_Travel::verify_nonce( true ) ) {
		return $booking_id;
	}

	// In Case of partial payment activated.
	if ( ! $booking_id ) {
		$booking_id = isset( $_GET['detail_id'] ) ? absint( $_GET['detail_id'] ) : 0;
	}
	if ( ! $booking_id ) {
		return;
	}

	$details = wptravel_booking_data( $booking_id );

	// End of in Case of partial payment activated.
	if ( ! class_exists( 'WP_Travel_FW_Form' ) ) {
		include_once WP_TRAVEL_ABSPATH . 'inc/framework/form/class.form.php';
	}

	$form = new WP_Travel_FW_Form();

	$form_options                      = array(
		'id'            => 'wp-travel-submit-slip',
		'wrapper_class' => 'wp-travel-submit-slip-form-wrapper',
		'submit_button' => array(
			'name'  => 'wp_travel_submit_slip',
			'id'    => 'wp-travel-submit-slip',
			'value' => __( 'Submit', 'wp-travel' ),
		),
		// 'hook_prefix'   => 'wp_travel_partial_payment',
		'multipart'     => true,
		'nonce'         => array(
			'action' => 'wp_travel_security_action',
			'field'  => 'wp_travel_security',
		),
	);
	$bank_deposit_fields               = wptravel_get_bank_deposit_form_fields($details);
	$bank_deposit_fields['booking_id'] = array(
		'type'    => 'hidden',
		'name'    => 'booking_id',
		'id'      => 'wp-travel-booking_id',
		'default' => $booking_id,
	);
	?>
	<div class="wp-travel-bank-deposit-wrap">
		<div id="wp-travel-bank-deposit-content" class="wp-travel-popup" >
			<h3 class="popup-title"><?php esc_html_e( 'Submit Bank Payment Receipt', 'wp-travel' ); ?></h3>
			<?php $form->init( $form_options )->fields( $bank_deposit_fields )->template(); ?>
			<button title="Close (Esc)" type="button" class="mfp-close close-button">x</button>
		</div>
		<div id="wp-travel-bank-details-content" class="wp-travel-popup" >
			<h3 class="popup-title"><?php esc_html_e( 'Bank Details', 'wp-travel' ); ?></h3>
			<?php echo wptravel_get_bank_deposit_account_table(); //phpcs:ignore ?>
			<button title="Close (Esc)" type="button" class="mfp-close close-button">x</button>
		</div>
	</div>
	<?php

}

// Bank deposit Payment content.
add_action( 'wp_travel_dashboard_booking_after_detail', 'wptravel_bank_deposite_content', 9, 2 );