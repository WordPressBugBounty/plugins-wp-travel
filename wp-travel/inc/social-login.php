<?php 

// ====== CONFIGURATION ======
define('GOOGLE_CLIENT_ID', wptravel_get_settings()['google_client_id']);
define('GOOGLE_CLIENT_SECRET', wptravel_get_settings()['google_client_secret']);
define('GOOGLE_REDIRECT_URI', site_url('/google-login-callback/'));

function wp_travel_unique_username($username) {
    $username_base = $username;
    $count = 1;
    while (username_exists($username)) {
        $username = $username_base . $count;
        $count++;
    }
    return $username;
}

// ====== ADD LOGIN BUTTON ======
function wp_travel_google_login_button() {
     // Capture the current page (e.g. /wp-travel-dashboard/) and store it in a cookie
	$return_to = home_url(add_query_arg(null, null));
	$return_to = esc_url_raw($return_to);

	setcookie(
		'wp_travel_login_redirect',
		$return_to,
		[
			'expires'  => time() + 300, // 5 minutes is plenty for an OAuth round trip
			'path'     => '/',
			'domain'   => '',           // current domain
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',        // 'Lax' survives the top-level redirect back from Google
		]
	);
    $google_auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',     
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);
    return '<a class="google-login-btn" href="' . esc_url($google_auth_url) . '">Login with Google</a>';
}
add_shortcode('google_login', 'wp_travel_google_login_button');

// ====== HANDLE CALLBACK ======
function wp_travel_google_login_callback() {
    if ( !is_admin() && strpos($_SERVER['REQUEST_URI'], '/google-login-callback') !== false && isset($_GET['code'])) {

        $fallback_url = 'https://dev.wensolutions.com/';

        // 1. Exchange code for access token
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'code' => $_GET['code'],
                'client_id' => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'grant_type' => 'authorization_code',
            ]
        ]);

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($data['access_token'])) {
            // 2. Get user info
            $userinfo = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
                'headers' => ['Authorization' => 'Bearer ' . $data['access_token']]
            ]);
            $userinfo = json_decode(wp_remote_retrieve_body($userinfo), true);

            if (!empty($userinfo['email'])) {
                $user_email = sanitize_email($userinfo['email']);
                $user_name  = sanitize_text_field($userinfo['name']);
                $user_id = email_exists($user_email);

                if (!$user_id) {
                    // Create new user with custom role and username
                    $random_password = wp_generate_password(12, false);

                    // Use Google name as username (fallback to email prefix if name missing)
                    $username = !empty($userinfo['name']) 
                        ? sanitize_user(str_replace(' ', '_', strtolower($userinfo['name'])))
                        : sanitize_user(current(explode('@', $user_email)));

                    // Ensure username is unique
                    $username = wp_travel_unique_username($username);

                    // Create user
                    $user_id = wp_create_user($username, $random_password, $user_email);

                    // Assign role
                    wp_update_user([
                        'ID'           => $user_id,
                        'role'         => 'wp-travel-customer',
                        'display_name' => $userinfo['name'],
                    ]);
                }

                // Log the user in
                wp_set_auth_cookie($user_id, true);

                // 3. Redirect back to the page stored in the cookie, if present and valid
                $redirect_to = $fallback_url;

                if (!empty($_COOKIE['wp_travel_login_redirect'])) {
                    $stored_url = $_COOKIE['wp_travel_login_redirect'];
                    // wp_validate_redirect ensures the URL is local to this site,
                    // preventing an open-redirect exploit via a tampered cookie value.
                    $redirect_to = wp_validate_redirect($stored_url, $fallback_url);
                }

                // Clean up the cookie now that it's been used
                setcookie('wp_travel_login_redirect', '', [
                    'expires'  => time() - 3600,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => is_ssl(),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                wp_redirect($redirect_to);
                exit;
            }
        }
    }
}
add_action('init', 'wp_travel_google_login_callback');