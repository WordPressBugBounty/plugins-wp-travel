<?php
/**
 * Trip Reminder Emails (2 Days Before - WP Travel)
 */

/*
|--------------------------------------------------------------------------
| 1. Schedule Hourly Cron
|--------------------------------------------------------------------------
*/

add_action('init', function () {

    $all_settings = wptravel_get_settings();

    if( $all_settings['send_trip_reminder_email_to_client'] == 'yes' ){        
         if ( ! wp_next_scheduled( 'wp_travel_trip_reminder_cron_hook' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'wp_travel_trip_reminder_cron_hook' );
        }
    }

    if( $all_settings['send_trip_review_email_to_client'] == 'yes' ){        
        if ( ! wp_next_scheduled( 'wp_travel_trip_review_cron_hook' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'wp_travel_trip_review_cron_hook' );
        }
    }
    
});


//  $sent = wp_mail('asdas@asdas.com', 'test', 'asdas dasd asdad', 'dddddd');
/*
|--------------------------------------------------------------------------
| 2. Helper: Replace Tags
|--------------------------------------------------------------------------
*/

function trip_parse_tags($content, $tags) {
    return str_replace(array_keys($tags), array_values($tags), $content);
}


/*
|--------------------------------------------------------------------------
| 3. Main Reminder Function
|--------------------------------------------------------------------------
*/

add_action('wp_travel_trip_reminder_cron_hook', 'trip_reminder_send_emails');

function trip_reminder_send_emails() {

    $all_settings = wptravel_get_settings();
    $settings     = isset($all_settings['trip_reminder_admin_template_settings'])
        ? $all_settings['trip_reminder_admin_template_settings']
        : [];

    // Prevent duplicate cron runs
    if (get_transient('trip_reminder_cron_running')) {
        return;
    }

    set_transient('trip_reminder_cron_running', 1, 10 * MINUTE_IN_SECONDS);

    /*
    |--------------------------------------------------------------------------
    | Target Date (2 days before arrival)
    |--------------------------------------------------------------------------
    */

    $today_timestamp = current_time('timestamp');
    $target_date     = date('Y-m-d', strtotime('+2 days', $today_timestamp));

    /*
    |--------------------------------------------------------------------------
    | Query Bookings
    |--------------------------------------------------------------------------
    */

    $args = [
        'post_type'      => 'itinerary-booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => 'wp_travel_arrival_date',
                'value'   => $target_date,
                'compare' => '=',
                'type'    => 'DATE'
            ],
            [
                'key'     => 'reminder_sent',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => 'reminder_lock',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ];

    $bookings = new WP_Query($args);

    if (empty($bookings->posts)) {
        delete_transient('trip_reminder_cron_running');
        return;
    }

    foreach ($bookings->posts as $booking_id) {

        update_post_meta($booking_id, 'reminder_lock', time());

        if (get_post_meta($booking_id, 'reminder_sent', true)) {
            delete_post_meta($booking_id, 'reminder_lock');
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Collect Emails
        |--------------------------------------------------------------------------
        */

        $email_data = get_post_meta($booking_id, 'wp_travel_email_traveller', true);
        $emails     = [];

        if (is_array($email_data)) {
            foreach ($email_data as $group) {
                if (is_array($group)) {
                    foreach ($group as $email) {
                        if (is_email($email)) {
                            $emails[] = $email;
                        }
                    }
                }
            }
        }

        $emails = array_unique($emails);

        if (empty($emails)) {
            delete_post_meta($booking_id, 'reminder_lock');
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Trip Data
        |--------------------------------------------------------------------------
        */

        $trip_id      = get_post_meta($booking_id, 'wp_travel_post_id', true);
        $trip_name    = $trip_id ? get_the_title($trip_id) : 'Your Tour';
        $arrival_date = get_post_meta($booking_id, 'wp_travel_arrival_date', true);

        /*
        |--------------------------------------------------------------------------
        | Tags
        |--------------------------------------------------------------------------
        */

        $tags = [
            '{trip_name}'  => $trip_name,
            '{start_date}' => date_i18n(get_option('date_format'), strtotime($arrival_date)),
            '{raw_date}'   => $arrival_date,
            '{booking_id}' => $booking_id,
            '{site_name}'  => get_bloginfo('name'),
            '{site_url}'   => home_url(),
        ];

        $tags = apply_filters('trip_reminder_email_tags', $tags, $booking_id, $trip_id);

        /*
        |--------------------------------------------------------------------------
        | Load Templates From Settings
        |--------------------------------------------------------------------------
        */

        $subject_template = ! empty($settings['email_subject'])
            ? $settings['email_subject']
            : 'Trip Reminder';

        $message_template = ! empty($settings['email_content'])
            ? $settings['email_content']
            : wptravel_trip_reminder_email_template();

        /*
        |--------------------------------------------------------------------------
        | Replace Tags
        |--------------------------------------------------------------------------
        */

        $subject = trip_parse_tags($subject_template, $tags);
        $message = trip_parse_tags($message_template, $tags);

        $subject = apply_filters('trip_reminder_email_subject', $subject, $booking_id, $trip_id);
        $message = apply_filters('trip_reminder_email_message', $message, $booking_id, $trip_id);

        /*
        |--------------------------------------------------------------------------
        | Headers (HTML)
        |--------------------------------------------------------------------------
        */

        $headers = apply_filters(
            'trip_reminder_email_headers',
            ['Content-Type: text/html; charset=UTF-8'],
            $booking_id,
            $trip_id
        );

        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        $sent = wp_mail($emails, $subject, $message, $headers);

        if ($sent) {
            update_post_meta($booking_id, 'reminder_sent', 'yes');
            update_post_meta($booking_id, 'reminder_sent_at', current_time('mysql'));
        }

        delete_post_meta($booking_id, 'reminder_lock');
    }

    delete_transient('trip_reminder_cron_running');
}





add_action('wp_travel_trip_review_cron_hook', 'trip_review_send_emails');

function trip_review_send_emails() {

    $all_settings = wptravel_get_settings();
    $settings     = isset($all_settings['trip_review_admin_template_settings'])
        ? $all_settings['trip_review_admin_template_settings']
        : [];

    // Prevent duplicate cron runs
    if (get_transient('trip_review_reminder_cron_running')) {
        return;
    }

    set_transient('trip_review_reminder_cron_running', 1, 10 * MINUTE_IN_SECONDS);

    /*
    |--------------------------------------------------------------------------
    | Target Date (2 days before arrival)
    |--------------------------------------------------------------------------
    */

    $today_timestamp = current_time('timestamp');
    $target_date     = date('Y-m-d', strtotime('-2 days', $today_timestamp));

    /*
    |--------------------------------------------------------------------------
    | Query Bookings
    |--------------------------------------------------------------------------
    */

    $args = [
        'post_type'      => 'itinerary-booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => 'wp_travel_departure_date',
                'value'   => $target_date,
                'compare' => '=',
                'type'    => 'DATE'
            ],
            [
                'key'     => 'reminder_sent',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => 'reminder_lock',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ];

    $bookings = new WP_Query($args);

    if (empty($bookings->posts)) {
        delete_transient('trip_review_reminder_cron_running');
        return;
    }

    foreach ($bookings->posts as $booking_id) {

        update_post_meta($booking_id, 'review_reminder_lock', time());

        if (get_post_meta($booking_id, 'review_reminder_sent', true)) {
            delete_post_meta($booking_id, 'review_reminder_lock');
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Collect Emails
        |--------------------------------------------------------------------------
        */

        $email_data = get_post_meta($booking_id, 'wp_travel_email_traveller', true);
        $emails     = [];

        if (is_array($email_data)) {
            foreach ($email_data as $group) {
                if (is_array($group)) {
                    foreach ($group as $email) {
                        if (is_email($email)) {
                            $emails[] = $email;
                        }
                    }
                }
            }
        }

        $emails = array_unique($emails);

        if (empty($emails)) {
            delete_post_meta($booking_id, 'review_reminder_lock');
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Trip Data
        |--------------------------------------------------------------------------
        */

        $trip_id      = get_post_meta($booking_id, 'wp_travel_post_id', true);
        $trip_name    = $trip_id ? get_the_title($trip_id) : 'Your Tour';
        $trip_link    = $trip_id ? get_the_permalink($trip_id) : '#';
        $trip_end_date = get_post_meta($booking_id, 'wp_travel_arrival_date', true);

        /*
        |--------------------------------------------------------------------------
        | Tags
        |--------------------------------------------------------------------------
        */

        $tags = [
            '{trip_name}'  => $trip_name,
            '{trip_end_date}'   => $trip_end_date,
            '{booking_id}' => $booking_id,
            '{review_link}' => $trip_link,
            '{site_name}'  => get_bloginfo('name'),
            '{site_url}'   => home_url(),
        ];

        $tags = apply_filters('trip_review_email_tags', $tags, $booking_id, $trip_id);

        /*
        |--------------------------------------------------------------------------
        | Load Templates From Settings
        |--------------------------------------------------------------------------
        */

        $subject_template = ! empty($settings['email_subject'])
            ? $settings['email_subject']
            : 'Trip Review';

        $message_template = ! empty($settings['email_content'])
            ? $settings['email_content']
            : wptravel_trip_review_email_template();

        /*
        |--------------------------------------------------------------------------
        | Replace Tags
        |--------------------------------------------------------------------------
        */

        $subject = trip_parse_tags($subject_template, $tags);
        $message = trip_parse_tags($message_template, $tags);

        $subject = apply_filters('trip_review_email_subject', $subject, $booking_id, $trip_id);
        $message = apply_filters('trip_review_email_message', $message, $booking_id, $trip_id);

        /*
        |--------------------------------------------------------------------------
        | Headers (HTML)
        |--------------------------------------------------------------------------
        */

        $headers = apply_filters(
            'trip_review_email_headers',
            ['Content-Type: text/html; charset=UTF-8'],
            $booking_id,
            $trip_id
        );

        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        $sent = wp_mail($emails, $subject, $message, $headers);

        if ($sent) {
            update_post_meta($booking_id, 'review_reminder_sent', 'yes');
            update_post_meta($booking_id, 'review_reminder_sent_at', current_time('mysql'));
        }

        delete_post_meta($booking_id, 'review_reminder_lock');
    }

   

    delete_transient('trip_review_reminder_cron_running');
}