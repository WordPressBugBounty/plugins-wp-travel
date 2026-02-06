<?php 


function wptravel_show_coming_soon_page() {
	if ( current_user_can( 'manage_options' )  ) {
		return;
	}

    $site_live_date = wptravel_get_settings()['site_live_date'];

	$coming_soon_page_id = wptravel_get_settings()['coming_soon_page_id'];
	if ( wptravel_get_settings()['default_coming_soon'] == 'yes' ) {
		echo
            '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <meta name="description" content="Exciting things are coming soon. Stay tuned!" />
                <title>Coming Soon</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
                <style>
                    :root {
                        --main-bg: linear-gradient(135deg, #c8d2ff 0%, #764ba2 100%);
                        --white-rgba: rgba(255, 255, 255, 0.1);
                        --text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
                    }

                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    html, body {
                        margin: 0;
                        padding: 0;
                        width: 100%;
                    }

                    body {
                        font-family: Arial, sans-serif;
                        background: var(--main-bg);
                        min-height: 100%;
                        max-width: 100%;
                        margin-top: 0px !important;
                        position: relative;
                    }

                    #error-page .wp-die-message {
                        margin: 0px !important;
                    }

                    @keyframes float {
                        0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.3; }
                        50% { transform: translateY(-20px) rotate(180deg); opacity: 0.8; }
                    }

                    .container {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        text-align: center;
                        padding: 2rem;
                        position: relative;
                        z-index: 10;
                    }

                    .logo {
                        // width: 120px;
                        // height: 120px;
                        background: rgba(255, 255, 255, 0.15);
                        border-radius: 50%;
                        padding: 40px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 2rem;
                        backdrop-filter: blur(10px);
                        border: 2px solid rgba(255, 255, 255, 0.2);
                        animation: pulse 2s ease-in-out infinite;
                    }

                    .logo img{
                        width: 80px;
                    }

                    @keyframes pulse {
                        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
                        50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(255, 255, 255, 0); }
                    }

                    h1 {
                        font-size: 4rem;
                        font-weight: 700;
                        background: linear-gradient(45deg, #fff, #fff);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                        text-shadow: var(--text-shadow);
                        margin-bottom: 1rem;
                        animation: slideInUp 1s ease-out;
                    }

                    .subtitle {
                        font-size: 1.5rem;
                        color: rgba(255, 255, 255, 0.9);
                        margin-bottom: 3rem;
                        max-width: 600px;
                        line-height: 1.6;
                        animation: slideInUp 1s ease-out 0.2s both;
                    }

                    .countdown {
                        display: flex;
                        gap: 2rem;
                        margin-bottom: 3rem;
                        flex-wrap: wrap;
                        justify-content: center;
                        animation: slideInUp 1s ease-out 0.4s both;
                    }

                    .countdown[data-date=""] {
                        display: none;
                    }

                    .time-unit {
                        background: var(--white-rgba);
                        backdrop-filter: blur(10px);
                        padding: 1.5rem;
                        border-radius: 15px;
                        border: 1px solid rgba(255, 255, 255, 0.2);
                        min-width: 100px;
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                    }

                    .time-unit:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                    }

                    .time-number {
                        font-size: 2.5rem;
                        font-weight: bold;
                        color: white;
                        text-shadow: var(--text-shadow);
                    }

                    .time-label {
                        font-size: 0.9rem;
                        color: rgba(255, 255, 255, 0.8);
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        margin-top: 0.5rem;
                    }

                    .email-signup {
                        display: flex;
                        gap: 1rem;
                        flex-wrap: wrap;
                        justify-content: center;
                        max-width: 400px;
                        width: 100%;
                        animation: slideInUp 1s ease-out 0.6s both;
                    }

                    .email-input {
                        flex: 1;
                        padding: 1rem 1.5rem;
                        border: 2px solid rgba(255, 255, 255, 0.2);
                        border-radius: 50px;
                        background: rgba(255, 255, 255, 0.15);
                        color: white;
                        font-size: 1rem;
                        backdrop-filter: blur(10px);
                        min-width: 250px;
                        transition: all 0.3s ease;
                    }

                    .email-input::placeholder {
                        color: rgba(255, 255, 255, 0.7);
                    }

                    .email-input:focus {
                        outline: none;
                        border-color: rgba(255, 255, 255, 0.5);
                        background: rgba(255, 255, 255, 0.2);
                        transform: scale(1.02);
                    }

                    .notify-btn {
                        padding: 1rem 2rem;
                        border: none;
                        border-radius: 50px;
                        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
                        color: white;
                        font-weight: 600;
                        font-size: 1rem;
                        cursor: pointer;
                        letter-spacing: 1px;
                        transition: all 0.3s ease;
                        text-transform: uppercase;
                    }

                    .notify-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 10px 25px rgba(238, 90, 36, 0.4);
                    }

                    .social-links {
                        display: flex;
                        gap: 1.5rem;
                        margin-top: 3rem;
                        animation: slideInUp 1s ease-out 0.8s both;
                    }

                    .social-link {
                        width: 50px;
                        height: 50px;
                        background: var(--white-rgba);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        text-decoration: none;
                        font-size: 1.5rem;
                        transition: all 0.3s ease;
                        backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.2);
                    }

                    .social-link:hover {
                        background: rgba(255, 255, 255, 0.2);
                        transform: translateY(-3px) scale(1.1);
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                    }

                    .social-links a.social-link:not([href]),
                    .social-links a.social-link[href=""] {
                        display: none;
                    }

                    @keyframes slideInUp {
                        from { opacity: 0; transform: translateY(30px); }
                        to { opacity: 1; transform: translateY(0); }
                    }

                    @media (max-width: 768px) {
                        h1 { font-size: 2.5rem; }
                        .subtitle { font-size: 1.2rem; }
                        .countdown { gap: 1rem; }
                        .time-unit { padding: 1rem; min-width: 80px; }
                        .time-number { font-size: 2rem; }
                        .email-input { min-width: 280px; }
                    }

                    @media (max-width: 480px) {
                        h1 { font-size: 2rem; }
                        .time-unit { padding: 0.8rem; min-width: 70px; }
                        .time-number { font-size: 1.5rem; }
                    }
                </style>
            </head>
            <body>

                <div class="container">
                    <div class="logo">
                    <img src="'.esc_url(wptravel_get_settings()['coming_soon_logo'] ).'" />
                    </div>
                    <h1>'.esc_html( wptravel_get_settings()['coming_soon_title'] ).'</h1>
                    <p class="subtitle">'.esc_html(wptravel_get_settings()['coming_soon_description'] ).'</p>
                    
                    <div class="countdown" data-date="'.$site_live_date.'">
                        <div class="time-unit"><span class="time-number" id="days">00</span><span class="time-label">Days</span></div>
                        <div class="time-unit"><span class="time-number" id="hours">00</span><span class="time-label">Hours</span></div>
                        <div class="time-unit"><span class="time-number" id="minutes">00</span><span class="time-label">Minutes</span></div>
                        <div class="time-unit"><span class="time-number" id="seconds">00</span><span class="time-label">Seconds</span></div>
                    </div>

                    <div class="social-links">
                        <a href="'.esc_url(wptravel_get_settings()['fb_social_url'] ).'" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="'.esc_url(wptravel_get_settings()['x_social_url'] ).'" class="social-link"><i class="fab fa-x-twitter"></i></a>
                        <a href="'.esc_url(wptravel_get_settings()['insta_social_url'] ).'" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="'.esc_url(wptravel_get_settings()['linkedin_social_url'] ).'" class="social-link"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                
                <script>
                    const countdown = document.querySelector(".countdown");

                    const launchDateStr = countdown.getAttribute("data-date");
                    const launchDate = new Date(launchDateStr + "T00:00:00");

                    function updateCountdown() {
                        const now = new Date().getTime();
                        const distance = launchDate - now;

                        if (distance < 0) {
                            document.querySelector(".countdown").innerHTML = "<h2 style=\'color: white;\'>We\'re Live!</h2>";
                            return;
                        }

                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        document.getElementById("days").textContent = days.toString().padStart(2, "0");
                        document.getElementById("hours").textContent = hours.toString().padStart(2, "0");
                        document.getElementById("minutes").textContent = minutes.toString().padStart(2, "0");
                        document.getElementById("seconds").textContent = seconds.toString().padStart(2, "0");
                    }

                    setInterval(updateCountdown, 1000);
                    updateCountdown();

                    
                </script>
            </body>
            </html>';
            exit;

	}

	if ( ! is_page( $coming_soon_page_id ) ) {

		// Load the coming soon page as the global post
		global $wp_query, $post;

		$post = get_post( $coming_soon_page_id );
		setup_postdata( $post );

		status_header( 503 ); // Send "Service Unavailable" for SEO
		nocache_headers();

		// Load the page template assigned to the post
		$template = get_page_template();

		if ( $template ) {
			include $template;
			exit;
		} else {
			// Fallback to page.php or singular.php
			include get_query_template( 'page' );
			exit;
		}
	}
}

if( wptravel_get_settings()['enable_coming_soon'] == "yes" ){
    add_action( 'template_redirect', 'wptravel_show_coming_soon_page' );
}