<?php 

/**
 * Show Coming Soon page if enabled
 */
function wptravel_show_coming_soon_page() {

    // Admins can access site normally
    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    // Cache settings once
    $settings = wptravel_get_settings();

    // Coming soon disabled
    if ( empty( $settings['enable_coming_soon'] ) || $settings['enable_coming_soon'] !== 'yes' ) {
        return;
    }

    // Allow REST API (OAuth, AJAX, headless)
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    // Allow wp-login.php
    if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ) {
        return;
    }

    // Developer bypass filter
    if ( apply_filters( 'wptravel_bypass_coming_soon', false ) ) {
        return;
    }

    /*
     * -----------------------------------------
     * Case 1: Default Coming Soon Template
     * -----------------------------------------
     */
    if ( ! empty( $settings['default_coming_soon'] ) && $settings['default_coming_soon'] === 'yes' ) {
        wptravel_render_default_coming_soon( $settings );
        exit;
    }

    /*
     * -----------------------------------------
     * Case 2: Custom Coming Soon Page
     * -----------------------------------------
     */
    if ( ! empty( $settings['coming_soon_page_id'] ) ) {

        $page_id = absint( $settings['coming_soon_page_id'] );
        $page    = get_post( $page_id );

        if ( $page && $page->post_status === 'publish' && !wp_is_block_theme() ) {

            status_header( 200 );
            nocache_headers();

            echo '<!DOCTYPE html><html ' . get_language_attributes() . '>';
            echo '<head>';
            echo '<meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';
            wp_head();
            echo '</head><body>';

            echo apply_filters( 'the_content', $page->post_content );

            wp_footer();
            echo '
            <style> .wp-travel-trip-details{
            display: none;
            }</style>
            </body></html>';

            
        }else{
            if ( $page && $page->post_status === 'publish' ) {

                global $wp_query, $post;

                // Fake the main query as this page
                $wp_query = new WP_Query([
                    'page_id' => absint( $settings['coming_soon_page_id'] ),
                ]);

                if ( have_posts() ) {
                    the_post();

                    status_header( 200 );
                    nocache_headers();

                    // Load the theme's page template
                    include get_page_template();
                    exit;
                }
            }

        }
        exit;
    }
    

    // Otherwise, load site normally
}
add_action( 'template_redirect', 'wptravel_show_coming_soon_page', 1 );

function wptravel_render_default_coming_soon( $settings ) {

    $site_live_date = ! empty( $settings['site_live_date'] ) ? $settings['site_live_date'] : '';
    $logo_url = esc_url($settings['coming_soon_logo']);
    $title = esc_html($settings['coming_soon_title']);
    $description = esc_html($settings['coming_soon_description']);
    $fb = esc_url($settings['fb_social_url']);
    $x = esc_url($settings['x_social_url']);
    $insta = esc_url($settings['insta_social_url']);
    $linkedin = esc_url($settings['linkedin_social_url']);


    if( $settings['coming_soon_design'] == 'design-one' ){
        echo '<!DOCTYPE html>
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

                <div class="container" style="background:'.esc_attr(wptravel_get_settings()['coming_soon_bg_color'] ).'">
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
                        <a href="'.esc_url(wptravel_get_settings()['fb_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'" ><i class="fab fa-facebook" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                        <a href="'.esc_url(wptravel_get_settings()['x_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-x-twitter" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                        <a href="'.esc_url(wptravel_get_settings()['insta_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-instagram" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                        <a href="'.esc_url(wptravel_get_settings()['linkedin_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-linkedin" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
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
    }elseif( $settings['coming_soon_design'] == 'design-two' ){ 
        echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>Coming Soon</title>

                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

                <style>
                    :root {
                        --bg: #0f172a;
                        --panel: #020617;
                        --primary: #38bdf8;
                        --text-main: #f8fafc;
                        --text-muted: #94a3b8;
                        --border: rgba(255,255,255,0.08);
                    }

                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }

                    body {
                        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
                        background: radial-gradient(circle at top, #1e293b, var(--bg));
                        min-height: 100vh;
                        color: var(--text-main);
                    }

                    .layout {
                        min-height: 100vh;
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                    }

                    @media (max-width: 900px) {
                        .layout {
                            grid-template-columns: 1fr;
                        }
                    }

                    .left {
                        padding: 4rem 10rem;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }

                    .logo {
                        width: 220px;
                        height: 220px;
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

                    .logo img {
                        width: 120px;
                    }
                    
                    @keyframes pulse {
                        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
                        50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(255, 255, 255, 0); }
                    }

                    h1 {
                        font-size: 3rem;
                        margin-bottom: 1rem;
                    }

                    .subtitle {
                        color: var(--text-muted);
                        font-size: 1.2rem;
                        max-width: 480px;
                        line-height: 1.6;
                        margin-bottom: 3rem;
                    }

                    .social-links {
                        display: flex;
                        gap: 1rem;
                    }

                    .social-link {
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        background: var(--panel);
                        border: 1px solid var(--border);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: var(--text-main);
                        text-decoration: none;
                        transition: all 0.3s ease;
                    }

                    .social-link:hover {
                        border-color: var(--primary);
                        color: var(--primary);
                        transform: translateY(-3px);
                    }

                    .social-links a:not([href]),
                    .social-links a[href=""] {
                        display: none;
                    }

                    .right {
                        padding: 4rem;
                        background: var(--panel);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .timer {
                        display: grid;
                        grid-template-columns: repeat(2, 160px);
                        gap: 2rem;
                    }

                    .unit {
                        background: rgba(255,255,255,0.05);
                        border: 1px solid var(--border);
                        border-radius: 20px;
                        padding: 2rem 1rem;
                        text-align: center;
                    }

                    .number {
                        font-size: 3.5rem;
                        font-weight: 700;
                        color: var(--primary);
                    }

                    .label {
                        margin-top: 0.5rem;
                        font-size: 0.9rem;
                        color: var(--text-muted);
                        letter-spacing: 1px;
                        text-transform: uppercase;
                    }
                </style>
            </head>

            <body>

                <div class="layout">

                    <div class="left" style="background:'.esc_attr(wptravel_get_settings()['coming_soon_bg_color'] ).'">
                        <div class="logo">
                            <img src="' . esc_url( $settings['coming_soon_logo'] ?? '' ) . '" />
                        </div>

                        <h1>' . esc_html( $settings['coming_soon_title'] ?? '' ) . '</h1>

                        <p class="subtitle">
                            ' . esc_html( $settings['coming_soon_description'] ?? '' ) . '
                        </p>

                        <div class="social-links">
                            <a href="'.esc_url(wptravel_get_settings()['fb_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'" ><i class="fab fa-facebook" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                            <a href="'.esc_url(wptravel_get_settings()['x_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-x-twitter" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                            <a href="'.esc_url(wptravel_get_settings()['insta_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-instagram" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                            <a href="'.esc_url(wptravel_get_settings()['linkedin_social_url'] ).'" class="social-link" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-linkedin" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                        </div>
                    </div>

                    <div class="right" style="background:'.esc_attr(wptravel_get_settings()['coming_soon_bg_color_two'] ).'">
                        <div class="timer" data-date="' . esc_attr( $site_live_date ) . '">
                            <div class="unit">
                                <div class="number" id="days">00</div>
                                <div class="label">Days</div>
                            </div>
                            <div class="unit">
                                <div class="number" id="hours">00</div>
                                <div class="label">Hours</div>
                            </div>
                            <div class="unit">
                                <div class="number" id="minutes">00</div>
                                <div class="label">Minutes</div>
                            </div>
                            <div class="unit">
                                <div class="number" id="seconds">00</div>
                                <div class="label">Seconds</div>
                            </div>
                        </div>
                    </div>

                </div>

                <script>
                    const timer = document.querySelector(".timer");
                    const dateStr = timer.getAttribute("data-date");
                    const launchDate = new Date(dateStr + "T00:00:00").getTime();

                    function updateTimer() {
                        const now = new Date().getTime();
                        const distance = launchDate - now;

                        if (distance <= 0) {
                            timer.innerHTML = "<h2 style=\'color:#38bdf8\'>We\'re Live!</h2>";
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

                    setInterval(updateTimer, 1000);
                    updateTimer();
                </script>

            </body>
            </html>';
    }elseif($settings['coming_soon_design'] == 'design-three'){
        echo '<!DOCTYPE html>
            <html lang="en">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Coming Soon</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
            <style>
                /* Reset */
                * { margin:0; padding:0; box-sizing:border-box; font-family: "Poppins", sans-serif; }
                html, body { height:100%; }

                /* Background */
                body {
                    display:flex;
                    justify-content:center;
                    align-items:center;
                    text-align:center;
                    overflow:hidden;
                    background: linear-gradient(135deg,#ffecd2,#fcb69f);
                    position:relative;
                }
                body::before {
                    content:"";
                    position:absolute;
                    width:200%; height:200%;
                    background: radial-gradient(circle, rgba(255,255,255,0.15) 1px, transparent 1px);
                    background-size:50px 50px;
                    animation: moveBg 20s linear infinite;
                    z-index:0;
                }
                @keyframes moveBg { 0%{transform:translate(0,0);} 100%{transform:translate(-50px,-50px);} }

                .container {
                    position:relative;
                    z-index:10;
                    display:flex;
                    flex-direction:column;
                    align-items:center;
                    justify-content:center;
                    max-width:500px;
                    padding:2rem;
                    background: rgba(255,255,255,0.1);
                    border-radius:15px;
                    backdrop-filter: blur(15px);
                    box-shadow:0 10px 25px rgba(0,0,0,0.2);
                }

                .logo img { max-width:150px; margin-bottom:1rem; border-radius:15px; }
                h1 { font-size:2.5rem; color:#fff; margin-bottom:0.5rem; }
                p.subtitle { font-size:1.1rem; color:#fff; opacity:0.9; margin-bottom:2rem; }

                .countdown {
                    display:flex;
                    gap:15px;
                    justify-content:center;
                    margin-bottom:2rem;
                }
                .unit {
                    background: rgba(255,255,255,0.2);
                    padding:1rem 1.2rem;
                    border-radius:10px;
                    min-width:70px;
                }
                .unit span.num { font-size:1.5rem; font-weight:bold; display:block; color:#fff; }
                .unit span.label { font-size:0.7rem; text-transform:uppercase; color:#fff; opacity:0.8; margin-top:2px; }

                .social-links {
                    display:flex;
                    gap:15px;
                    justify-content:center;
                }
                .social-links a {
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    width:40px;
                    height:40px;
                    border-radius:50%;
                    background: rgba(255,255,255,0.2);
                    color:#fff;
                    font-size:1.2rem;
                    transition:0.3s;
                    text-decoration:none;
                }
                .social-links a:hover { transform:scale(1.2); background: rgba(255,255,255,0.3); }
                .social-links a:not([href]), .social-links a[href=""] {
                    display: none;
                }

                @media(max-width:480px){
                    h1{font-size:2rem;}
                    .unit span.num{font-size:1.2rem;}
                    .unit{padding:0.8rem; min-width:55px;}
                }
            </style>
            </head>
            <body style="background:'.esc_attr(wptravel_get_settings()['coming_soon_bg_color'] ).'">

            <div class="container" >
                <div class="logo"><img src="'.$logo_url.'" alt="Logo"></div>
                <h1>'.$title.'</h1>
                <p class="subtitle">'.$description.'</p>

                <div class="countdown" data-date="'.$site_live_date.'">
                    <div class="unit"><span class="num" id="days">00</span><span class="label">Days</span></div>
                    <div class="unit"><span class="num" id="hours">00</span><span class="label">Hours</span></div>
                    <div class="unit"><span class="num" id="minutes">00</span><span class="label">Minutes</span></div>
                    <div class="unit"><span class="num" id="seconds">00</span><span class="label">Seconds</span></div>
                </div>

                <div class="social-links">
                    <a href="'.$fb.'" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-facebook" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                    <a href="'.$x.'" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-x-twitter" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                    <a href="'.$insta.'" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-instagram" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                    <a href="'.$linkedin.'" style="background:'.esc_attr(wptravel_get_settings()['social_icon_bg_color'] ).'"><i class="fab fa-linkedin" style="color:'.esc_attr(wptravel_get_settings()['social_icon_color'] ).'"></i></a>
                </div>
            </div>

            <script>
                const countdown = document.querySelector(".countdown");
                const launchDate = new Date(countdown.getAttribute("data-date") + "T00:00:00");

                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = launchDate - now;

                    if(distance < 0){
                        countdown.innerHTML = "<h2 style=\'color:white;\'>We\'re Live!</h2>";
                        return;
                    }

                    const days = Math.floor(distance/(1000*60*60*24));
                    const hours = Math.floor((distance%(1000*60*60*24))/(1000*60*60));
                    const minutes = Math.floor((distance%(1000*60*60))/(1000*60));
                    const seconds = Math.floor((distance%(1000*60))/1000);

                    document.getElementById("days").textContent = days.toString().padStart(2,"0");
                    document.getElementById("hours").textContent = hours.toString().padStart(2,"0");
                    document.getElementById("minutes").textContent = minutes.toString().padStart(2,"0");
                    document.getElementById("seconds").textContent = seconds.toString().padStart(2,"0");
                }

                setInterval(updateCountdown,1000);
                updateCountdown();
            </script>

            </body>
            </html>';
    }

    

    exit;
}
