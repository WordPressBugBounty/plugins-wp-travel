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

	if ( isset( $_GET['wp_travel_listener'] )
		&& $_GET['wp_travel_listener'] == 'IPN'
		|| isset( $_GET['test'] )
		&& $_GET['test'] == true ) {
        do_action( 'wp_travel_verify_paypal_ipn' );
    }

}
add_action( 'init', 'wptravel_listen_paypal_ipn' );


/**
 * When a payment is made PayPal will send us a response and this function is
 * called. From here we will confirm arguments that we sent to PayPal which
 * the ones PayPal is sending back to us.
 * This is the Pink Lilly of the whole operation.
 */
function wptravel_paypal_ipn_process() {


	include dirname( __FILE__ ) . '/php-paypal-ipn/IPNListener.php';

	$listener = new IPNListener();

	$settings = wptravel_get_settings();

	$listener->use_sandbox = ( $settings['wt_test_mode'] ) ? true : false;

	/**
	 * Check if IPN was successfully processed.
	 */
	if ( $verified = $listener->processIpn() ) {


		$custom = json_decode( wp_unslash( $_POST['custom'] ?? '' ), true );

		$booking_id   = absint( $custom['booking_id'] ?? 0 );
		$payment_mode = sanitize_text_field( $custom['payment_mode'] ?? '' );
		$payment_type = sanitize_text_field( $custom['payment_type'] ?? '' );
		$user_id      = absint( $custom['user_id'] ?? 0 );

		error_log(
			'WP Travel PayPal IPN: Parsed data. ' .
			'Booking ID: ' . $booking_id .
			' | Payment Mode: ' . $payment_mode .
			' | Payment Type: ' . $payment_type .
			' | User ID: ' . $user_id
		);

		// ... your existing code ...

	} else {

		error_log(
			'WP Travel PayPal IPN: IPN verification FAILED. ' .
			'Txn ID: ' . sanitize_text_field( $_POST['txn_id'] ?? '' ) .
			' | Payment Status: ' . sanitize_text_field( $_POST['payment_status'] ?? '' )
		);
	}
}
