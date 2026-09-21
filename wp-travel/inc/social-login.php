<?php

// define( 'GOOGLE_CLIENT_ID', wptravel_get_settings()['google_client_id'] ?? '' );
// define( 'GOOGLE_CLIENT_SECRET', wptravel_get_settings()['google_client_secret'] ?? '' );
// define( 'GOOGLE_REDIRECT_URI', site_url( '/google-login-callback/' ) );


// function wp_travel_unique_username( $username ) {

// 	$username = sanitize_user( $username, true );

// 	if ( empty( $username ) ) {
// 		$username = 'wp_travel_user';
// 	}

// 	$username_base = $username;
// 	$count         = 1;

// 	while ( username_exists( $username ) ) {
// 		$username = $username_base . $count;
// 		$count++;
// 	}

// 	return $username;
// }


// /**
//  * Google login button.
//  */
// function wp_travel_google_login_button() {

// 	if ( empty( GOOGLE_CLIENT_ID ) || empty( GOOGLE_CLIENT_SECRET ) ) {
// 		return '';
// 	}

// 	$return_to = home_url( add_query_arg( array(), '' ) );

// 	/*
// 	 * OAuth state protection.
// 	 *
// 	 * This value must come back unchanged from Google.
// 	 */
// 	$state = wp_generate_password( 64, false, false );

// 	setcookie(
// 		'wp_travel_google_oauth_state',
// 		$state,
// 		array(
// 			'expires'  => time() + 600,
// 			'path'     => COOKIEPATH ?: '/',
// 			'domain'   => COOKIE_DOMAIN,
// 			'secure'   => is_ssl(),
// 			'httponly' => true,
// 			'samesite' => 'Lax',
// 		)
// 	);

// 	/*
// 	 * Store return URL separately.
// 	 */
// 	setcookie(
// 		'wp_travel_login_redirect',
// 		esc_url_raw( $return_to ),
// 		array(
// 			'expires'  => time() + 600,
// 			'path'     => COOKIEPATH ?: '/',
// 			'domain'   => COOKIE_DOMAIN,
// 			'secure'   => is_ssl(),
// 			'httponly' => true,
// 			'samesite' => 'Lax',
// 		)
// 	);

// 	$google_auth_url = add_query_arg(
// 		array(
// 			'client_id'     => GOOGLE_CLIENT_ID,
// 			'redirect_uri'  => GOOGLE_REDIRECT_URI,
// 			'response_type' => 'code',
// 			'scope'         => 'openid email profile',
// 			'access_type'   => 'online',
// 			'prompt'        => 'select_account',
// 			'state'         => $state,
// 		),
// 		'https://accounts.google.com/o/oauth2/v2/auth'
// 	);

// 	return sprintf(
// 		'<a class="google-login-btn" href="%s">%s</a>',
// 		esc_url( $google_auth_url ),
// 		esc_html__( 'Login with Google', 'wp-travel' )
// 	);
// }

// add_shortcode( 'google_login', 'wp_travel_google_login_button' );


// /**
//  * Handle Google OAuth callback.
//  */
// function wp_travel_google_login_callback() {

// 	if ( is_admin() ) {
// 		return;
// 	}

// 	$request_path = isset( $_SERVER['REQUEST_URI'] )
// 		? wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH )
// 		: '';

// 	$callback_path = wp_parse_url( GOOGLE_REDIRECT_URI, PHP_URL_PATH );

// 	if ( untrailingslashit( $request_path ) !== untrailingslashit( $callback_path ) ) {
// 		return;
// 	}

// 	/*
// 	 * Google may return an OAuth error.
// 	 */
// 	if ( isset( $_GET['error'] ) ) {
// 		wp_safe_redirect( home_url( '/' ) );
// 		exit;
// 	}

// 	if ( empty( $_GET['code'] ) || empty( $_GET['state'] ) ) {
// 		wp_die(
// 			esc_html__( 'Invalid Google login request.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 400 )
// 		);
// 	}

// 	$code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );
// 	$state = sanitize_text_field( wp_unslash( $_GET['state'] ) );

// 	$stored_state = isset( $_COOKIE['wp_travel_google_oauth_state'] )
// 		? sanitize_text_field( wp_unslash( $_COOKIE['wp_travel_google_oauth_state'] ) )
// 		: '';

// 	/*
// 	 * Critical OAuth CSRF protection.
// 	 */
// 	if (
// 		empty( $stored_state ) ||
// 		! hash_equals( $stored_state, $state )
// 	) {
// 		wp_die(
// 			esc_html__( 'Invalid Google authentication state.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 403 )
// 		);
// 	}

// 	/*
// 	 * State is one-time-use.
// 	 */
// 	setcookie(
// 		'wp_travel_google_oauth_state',
// 		'',
// 		array(
// 			'expires'  => time() - 3600,
// 			'path'     => COOKIEPATH ?: '/',
// 			'domain'   => COOKIE_DOMAIN,
// 			'secure'   => is_ssl(),
// 			'httponly' => true,
// 			'samesite' => 'Lax',
// 		)
// 	);

// 	/*
// 	 * Exchange authorization code for access token.
// 	 */
// 	$response = wp_remote_post(
// 		'https://oauth2.googleapis.com/token',
// 		array(
// 			'timeout' => 15,
// 			'body'    => array(
// 				'code'          => $code,
// 				'client_id'     => GOOGLE_CLIENT_ID,
// 				'client_secret' => GOOGLE_CLIENT_SECRET,
// 				'redirect_uri'  => GOOGLE_REDIRECT_URI,
// 				'grant_type'    => 'authorization_code',
// 			),
// 		)
// 	);

// 	if ( is_wp_error( $response ) ) {
// 		wp_die(
// 			esc_html__( 'Unable to contact Google.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 500 )
// 		);
// 	}

// 	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
// 		wp_die(
// 			esc_html__( 'Google authentication failed.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 401 )
// 		);
// 	}

// 	$data = json_decode(
// 		wp_remote_retrieve_body( $response ),
// 		true
// 	);

// 	if ( empty( $data['access_token'] ) ) {
// 		wp_die(
// 			esc_html__( 'Google authentication failed.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 401 )
// 		);
// 	}

// 	/*
// 	 * Retrieve authenticated Google user.
// 	 */
// 	$userinfo_response = wp_remote_get(
// 		'https://www.googleapis.com/oauth2/v2/userinfo',
// 		array(
// 			'timeout' => 15,
// 			'headers' => array(
// 				'Authorization' => 'Bearer ' . $data['access_token'],
// 			),
// 		)
// 	);

// 	if ( is_wp_error( $userinfo_response ) ) {
// 		wp_die(
// 			esc_html__( 'Unable to retrieve Google account information.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 500 )
// 		);
// 	}

// 	if ( 200 !== wp_remote_retrieve_response_code( $userinfo_response ) ) {
// 		wp_die(
// 			esc_html__( 'Unable to verify Google account.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 401 )
// 		);
// 	}

// 	$userinfo = json_decode(
// 		wp_remote_retrieve_body( $userinfo_response ),
// 		true
// 	);

// 	/*
// 	 * DO NOT authenticate merely because an email exists.
// 	 *
// 	 * Google must explicitly say that the email is verified.
// 	 */
// 	if (
// 		empty( $userinfo['email'] ) ||
// 		empty( $userinfo['verified_email'] )
// 	) {
// 		wp_die(
// 			esc_html__( 'Google could not verify your email address.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 403 )
// 		);
// 	}

// 	$user_email = sanitize_email( $userinfo['email'] );

// 	if ( empty( $user_email ) || ! is_email( $user_email ) ) {
// 		wp_die(
// 			esc_html__( 'Invalid Google email address.', 'wp-travel' ),
// 			esc_html__( 'Authentication Error', 'wp-travel' ),
// 			array( 'response' => 400 )
// 		);
// 	}

// 	$user_id = email_exists( $user_email );

// 	/*
// 	 * Create WP Travel customer when account doesn't exist.
// 	 */
// 	if ( ! $user_id ) {

// 		$name = ! empty( $userinfo['name'] )
// 			? sanitize_text_field( $userinfo['name'] )
// 			: '';

// 		$email_parts = explode( '@', $user_email );

// 		$username_base = ! empty( $name )
// 			? str_replace( ' ', '_', strtolower( $name ) )
// 			: $email_parts[0];

// 		$username = wp_travel_unique_username( $username_base );

// 		$password = wp_generate_password( 32, true, true );

// 		$user_id = wp_create_user(
// 			$username,
// 			$password,
// 			$user_email
// 		);

// 		if ( is_wp_error( $user_id ) ) {
// 			wp_die(
// 				esc_html__( 'Unable to create the user account.', 'wp-travel' ),
// 				esc_html__( 'Authentication Error', 'wp-travel' ),
// 				array( 'response' => 500 )
// 			);
// 		}

// 		$update_result = wp_update_user(
// 			array(
// 				'ID'           => $user_id,
// 				'role'         => 'wp-travel-customer',
// 				'display_name' => $name,
// 			)
// 		);

// 		if ( is_wp_error( $update_result ) ) {
// 			wp_delete_user( $user_id );

// 			wp_die(
// 				esc_html__( 'Unable to configure the user account.', 'wp-travel' ),
// 				esc_html__( 'Authentication Error', 'wp-travel' ),
// 				array( 'response' => 500 )
// 			);
// 		}
// 	}

// 	/*
// 	 * At this point:
// 	 *
// 	 * - Google authorization code was exchanged by our application.
// 	 * - OAuth state was verified.
// 	 * - Google's user-info endpoint authenticated the user.
// 	 * - Google's verified_email flag was checked.
// 	 *
// 	 * Now it is safe to authenticate the corresponding WordPress account.
// 	 */
// 	wp_set_current_user( $user_id );
// 	wp_set_auth_cookie( $user_id, true );

// 	do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

// 	$fallback_url = home_url( '/' );
// 	$redirect_to  = $fallback_url;

// 	if ( ! empty( $_COOKIE['wp_travel_login_redirect'] ) ) {

// 		$stored_url = esc_url_raw(
// 			wp_unslash( $_COOKIE['wp_travel_login_redirect'] )
// 		);

// 		$redirect_to = wp_validate_redirect(
// 			$stored_url,
// 			$fallback_url
// 		);
// 	}

// 	setcookie(
// 		'wp_travel_login_redirect',
// 		'',
// 		array(
// 			'expires'  => time() - 3600,
// 			'path'     => COOKIEPATH ?: '/',
// 			'domain'   => COOKIE_DOMAIN,
// 			'secure'   => is_ssl(),
// 			'httponly' => true,
// 			'samesite' => 'Lax',
// 		)
// 	);

// 	wp_safe_redirect( $redirect_to );
// 	exit;
// }

// add_action( 'init', 'wp_travel_google_login_callback' );