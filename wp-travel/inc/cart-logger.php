<?php

function wt_get_device_type() {

	$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

	if (preg_match('/ipad|tablet/i', $ua)) {
		return 'Tablet';
	}

	if (preg_match('/mobile|iphone|android/i', $ua)) {
		return 'Mobile';
	}

	return 'Desktop';
}

function wt_get_browser_details() {

	$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

	$browser = 'Unknown';

	if (stripos($ua, 'Edg') !== false) {
		$browser = 'Edge';
	} elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Safari') !== false) {
		$browser = 'Chrome';
	} elseif (stripos($ua, 'Firefox') !== false) {
		$browser = 'Firefox';
	} elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
		$browser = 'Safari';
	} elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) {
		$browser = 'Opera';
	}

	return array(
		'browser' => $browser,
		'user_agent' => $ua,
	);
}

function wt_cart_log( $message, $data = array(), $include_device_info = false ) {

	$upload_dir = wp_upload_dir();
	$log_dir    = $upload_dir['basedir'] . '/wp-travel-logs';

	if ( ! file_exists( $log_dir ) ) {
		wp_mkdir_p( $log_dir );
	}

	$device_info = '';

	if ( $include_device_info ) {

		$user_id    = get_current_user_id();
		$user_email = '';

		if ( $user_id ) {
			$user = get_userdata( $user_id );
			$user_email = $user ? $user->user_email : '';
		}

		$device_info = print_r(
			array(
				'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'device_type' => wt_get_device_type(),
				'browser'     => wt_get_browser_details(),
				'user_id'     => $user_id,
				'user_email'  => $user_email,
			),
			true
		);
	}

	$log_file = $log_dir . '/cart-' . date( 'Y-m-d' ) . '.log';

	$log = sprintf(
		"[%s]\nMESSAGE: %s\n%sDATA INFO: %s\n\n",
		current_time( 'mysql' ),
		$message,
		$include_device_info ? "DEVICE & USER INFO: {$device_info}\n" : '',
		print_r( $data, true )
	);

	file_put_contents( $log_file, $log, FILE_APPEND | LOCK_EX );
}
