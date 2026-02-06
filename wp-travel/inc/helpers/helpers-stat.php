<?php
/**
 * Helper Functions.
 *
 * @package WP_Travel
 */


function wptravel_get_date_filter_clause( $interval, $alias = 'p' ) {
    $submission_request = isset( $_REQUEST ) ? wptravel_sanitize_array( wp_unslash( $_REQUEST ) ) : array();

    // Default to no date filtering
    $start = $end = '';

    if ( $interval === 'custom' && ! empty( $submission_request['booking_stat_from'] ) && ! empty( $submission_request['booking_stat_to'] ) ) {
        // Convert format from m/d/Y to Y-m-d
        $from_obj = DateTime::createFromFormat( 'm/d/Y', $submission_request['booking_stat_from'] );
        $to_obj   = DateTime::createFromFormat( 'm/d/Y', $submission_request['booking_stat_to'] );

        if ( $from_obj && $to_obj ) {
            $start = $from_obj->format( 'Y-m-d' );
            $end   = $to_obj->format( 'Y-m-d' );
        }
    }

    switch ( $interval ) {
        case 'last-week':
            return "AND {$alias}.post_date >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 6 DAY)
                    AND {$alias}.post_date < DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY)";
        case 'last-month':
            return "AND {$alias}.post_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
                    AND {$alias}.post_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        case 'last-three-months':
            return "AND {$alias}.post_date >= DATE_FORMAT(CURDATE() - INTERVAL 3 MONTH, '%Y-%m-01')
                    AND {$alias}.post_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        case 'last-six-months':
            return "AND {$alias}.post_date >= DATE_FORMAT(CURDATE() - INTERVAL 6 MONTH, '%Y-%m-01')
                    AND {$alias}.post_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        case 'last-year':
            return "AND {$alias}.post_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 YEAR, '%Y-01-01')
                    AND {$alias}.post_date < DATE_FORMAT(CURDATE(), '%Y-01-01')";
        case 'custom':
            if ( $start && $end ) {
                return "AND {$alias}.post_date >= '{$start}' AND {$alias}.post_date <= '{$end}'";
            }
            return ""; // skip filter if invalid
        default:
            return ""; // all-time
    }
}

function wptravel_get_daily_booking_stats( $interval ) {
    global $wpdb;
    $date_filter = wptravel_get_date_filter_clause( $interval );

    return $wpdb->get_results("
        SELECT DATE(p.post_date) as booking_date, COUNT(DISTINCT p.ID) as num_bookings
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'wp_travel_booking_status'
        AND pm.meta_value = 'booked'
        AND p.post_status = 'publish'
        {$date_filter}
        GROUP BY DATE(p.post_date)
        ORDER BY booking_date ASC
    ", ARRAY_A);
}

function wptravel_get_trip_stats( $interval ) {
    global $wpdb;
    $date_filter = wptravel_get_date_filter_clause( $interval, 'booking_post' );

	$performing_trips = $wpdb->get_results("
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
		{$date_filter}
	", ARRAY_A);

	$booking_data = [];

	foreach ( $performing_trips as $booking ) {
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
			$booking_data[ $trip_id ]['trip_id'] = $trip_id;
			$booking_data[ $trip_id ]['total_revenue']  += floatval( $order_totals['total'] );
		}
	}

	uasort( $booking_data, function ( $a, $b ) {
		return $b['total_bookings'] <=> $a['total_bookings'];
	});

	return  $booking_data;
}

function wptravel_get_destination_stats( $interval ) {
    global $wpdb;
    $date_filter = wptravel_get_date_filter_clause( $interval, 'booking_post' );

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
		{$date_filter}
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
					'name'           => $term['name'],
					'total_revenue'  => 0,
					'total_bookings' => 0, // ✅ Add this line
					'term_id'        => $term['term_id'],
				];
			}

			$destination_data[ $term['term_id'] ]['total_revenue']  += $data['total_revenue'];
			$destination_data[ $term['term_id'] ]['total_bookings'] += $data['total_bookings'];
		}
	}


	uasort($destination_data, function ($a, $b) {
		return $b['total_bookings'] <=> $a['total_bookings'];
	});

	return $destination_data;
}

add_action( 'wp_ajax_wptravel_get_booking_data_stat', 'wptravel_ajax_get_booking_data' );

function wptravel_ajax_get_booking_data() {

	$permission = WP_Travel::verify_nonce();

	if ( ! $permission || is_wp_error( $permission ) ) {
		WP_Travel_Helpers_REST_API::response( $permission );
		exit;
	}

	// Permission check
	if ( isset($submission_request['booking_intervals']) && !is_super_admin() ) {
		wp_send_json_error(['message' => 'Permission denied']);
		exit;
	}

	
	$stat_data = array();

	$submission_request = isset($_REQUEST) ? wptravel_sanitize_array(wp_unslash($_REQUEST)) : array();

	$interval = $submission_request["booking_intervals"] ?? 'all-time';

	// Run Queries
	$results           = wptravel_get_daily_booking_stats($interval);
	$trip_stats        = wptravel_get_trip_stats($interval);
	$destination_stats = wptravel_get_destination_stats($interval);

	// Sort trip and destination stats
	$top_performing_trips = array_slice($trip_stats, 0, 10);
	$low_performing_trips = array_slice(array_reverse($trip_stats), 0, 10);
	
	$bottom_destinations = array_slice(array_reverse($destination_stats), 0, 10);
	$top_destinations = array_slice($destination_stats, 0, 10);

	// Prepare booking data arrays
	$booking_data = array_map(function($row) {
		return [
			'date' => $row['booking_date'],
			'num_bookings' => (int) $row['num_bookings'],
		];
	}, $results);

	$booking_dates = array_column($booking_data, 'date');
	$booking_number = array_column($booking_data, 'num_bookings');

	
	// Assemble response
	$stat_data['stat_data']['stat_label'] = array_map(function($date) {
		return date('Y/m/d', strtotime($date));
	}, $booking_dates);

	$stat_data['stat_data']['data'] = $booking_number;
	$stat_data['max_bookings'] = array_sum($booking_number);

	$stat_data['top_performing_trips'] = $top_performing_trips;
	$stat_data['low_performing_trips'] = $low_performing_trips;

	$stat_data['top_destinations'] = $top_destinations;
	$stat_data['bottom_destinations'] = $bottom_destinations;

	$stat_data['booking_stat_from'] = $submission_request['booking_stat_from'] ?? '';
	$stat_data['booking_stat_to'] = $submission_request['booking_stat_to'] ?? '';

	$stat_data['total_revenue'] = wptravel_get_formated_price_currency(array_reduce( $top_performing_trips, function( $carry, $item ) {
		return $carry + floatval( $item['total_revenue'] );
	}, 0 ) );


	$term_link = '';
	$term_name = '';

	if($top_destinations){
		$term_link = esc_url( get_term_link( (int)$top_destinations[0]['term_id'] ) );
		$term_name = esc_html( $top_destinations[0]['name'] );
	}
	

	$stat_data['top_destination'] = '<a href="' . $term_link . '" class="wp-travel-top-itineraries" target="_blank">'
								. $term_name .
								'</a>';

	$trip_id    = (int) $top_performing_trips[0]['trip_id'];
	$trip_link  = esc_url( get_the_permalink( $trip_id ) );
	$trip_title = esc_html( get_the_title( $trip_id ) );

	$stat_data['top_trip'] = '<a href="' . $trip_link . '" class="wp-travel-top-itineraries" target="_blank">'
						. $trip_title .
						'</a>';

	$sn = 1;
	$top_10_trip_html = '';
	foreach ( $top_performing_trips as $trip ) {
		if ( $sn > 10 ) break;

		$trip_id = (int) $trip['trip_id'];
		$trip_link = esc_url( get_the_permalink( $trip_id ) );
		$trip_title = esc_html( get_the_title( $trip_id ) );
		$booking_count = intval( $trip['total_bookings'] );
		$revenue = wptravel_get_formated_price_currency( $trip['total_revenue'] );

		$top_10_trip_html .= '<tr>';
		$top_10_trip_html .= '<td>' . $sn++ . '</td>';
		$top_10_trip_html .= '<td><a href="' . $trip_link . '" class="wp-travel-top-itineraries" target="_blank">' . $trip_title . '</a></td>';
		$top_10_trip_html .= '<td>' . $booking_count . '</td>';
		$top_10_trip_html .= '<td>' . $revenue . '</td>';
		$top_10_trip_html .= '</tr>';
	}

	$stat_data['top_10_trips'] = $top_10_trip_html;

	$sn = 1;
	$low_10_trip_html = '';
	foreach ( $low_performing_trips as $trip ) {
		if ( $sn > 10 ) break;

		$trip_id = (int) $trip['trip_id'];
		$trip_link = esc_url( get_the_permalink( $trip_id ) );
		$trip_title = esc_html( get_the_title( $trip_id ) );
		$booking_count = intval( $trip['total_bookings'] );
		$revenue = wptravel_get_formated_price_currency( $trip['total_revenue'] );

		$low_10_trip_html .= '<tr>';
		$low_10_trip_html .= '<td>' . $sn++ . '</td>';
		$low_10_trip_html .= '<td><a href="' . $trip_link . '" class="wp-travel-top-itineraries" target="_blank">' . $trip_title . '</a></td>';
		$low_10_trip_html .= '<td>' . $booking_count . '</td>';
		$low_10_trip_html .= '<td>' . $revenue . '</td>';
		$low_10_trip_html .= '</tr>';
	}

	$stat_data['low_10_trips'] = $low_10_trip_html;


	$sn = 1;
	$top_10_dest_html = '';

	if($top_destinations){
		foreach ( $top_destinations as $destination ) {
			if ( $sn > 10 ) break;

			$term_id = (int) $destination['term_id'];
			$term_link = esc_url( get_term_link( $term_id ) );
			$term_name = esc_html( $destination['name'] );
			$booking_count = intval( $destination['total_bookings'] );
			$revenue = wptravel_get_formated_price_currency( $destination['total_revenue'] );

			$top_10_dest_html .= '<tr>';
			$top_10_dest_html .= '<td>' . $sn++ . '</td>';
			$top_10_dest_html .= '<td><a href="' . $term_link . '" target="_blank">' . $term_name . '</a></td>';
			$top_10_dest_html .= '<td>' . $booking_count . '</td>';
			$top_10_dest_html .= '<td>' . $revenue . '</td>';
			$top_10_dest_html .= '</tr>';
		}
	}
	

	$stat_data['top_10_destinations'] = $top_10_dest_html;

	$sn = 1;
	$low_10_dest_html = '';

	if($top_destinations){

		foreach ( $bottom_destinations as $destination ) {
			if ( $sn > 10 ) break;

			$term_id = (int) $destination['term_id'];
			$term_link = esc_url( get_term_link( $term_id ) );
			$term_name = esc_html( $destination['name'] );
			$booking_count = intval( $destination['total_bookings'] );
			$revenue = wptravel_get_formated_price_currency( $destination['total_revenue'] );

			$low_10_dest_html .= '<tr>';
			$low_10_dest_html .= '<td>' . $sn++ . '</td>';
			$low_10_dest_html .= '<td><a href="' . $term_link . '" target="_blank">' . $term_name . '</a></td>';
			$low_10_dest_html .= '<td>' . $booking_count . '</td>';
			$low_10_dest_html .= '<td>' . $revenue . '</td>';
			$low_10_dest_html .= '</tr>';
		}
	}

	$stat_data['low_10_destinations'] = $low_10_dest_html;

	wp_send_json_success($stat_data);
}


// function wptravel_get_booking_data() {

	
// 	global $wpdb;
// 	$stat_data = array();
	
// 	if( isset( $_GET['post_type'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'booking_chart' ){
// 		$submission_request = isset($_REQUEST) ? wptravel_sanitize_array(wp_unslash($_REQUEST)) : array();

// 		$interval = $submission_request["booking_intervals"] ?? 'all-time';

// 		// Permission check only if booking_intervals is manually submitted (e.g., via dashboard filter)
// 		if (isset($submission_request['booking_intervals']) && !current_user_can('manage_options')) {
// 			return;
// 		}

// 		// 🟢 Run Queries Only Once
// 		$results           = wptravel_get_daily_booking_stats($interval);
// 		$trip_stats        = wptravel_get_trip_stats($interval);
// 		$destination_stats = wptravel_get_destination_stats($interval);

// 		// Sort trip and destination stats
// 		$top_performing_trips = array_slice(array_reverse($trip_stats), 0, 15);
// 		$low_performing_trips = array_slice($trip_stats, 0, 15);

// 		$top_destinations = array_slice(array_reverse($destination_stats), 0, 15);
// 		$bottom_destinations = array_slice($destination_stats, 0, 15);

// 		// Prepare booking data arrays
// 		$booking_data = array_map(function($row) {
// 			return [
// 				'date' => $row['booking_date'],
// 				'num_bookings' => (int) $row['num_bookings'],
// 			];
// 		}, $results);

// 		$booking_dates = array_column($booking_data, 'date');
// 		$booking_number = array_column($booking_data, 'num_bookings');

// 		// Assemble response
// 		$stat_data['stat_data']['stat_label'] = array_map(function($date) {
// 			return date('Y/m/d', strtotime($date));
// 		}, $booking_dates);

// 		$stat_data['stat_data']['data'][] = $booking_number;
// 		$stat_data['max_bookings'] = array_sum($booking_number);

// 		$stat_data['top_performing_trips'] = $top_performing_trips;
// 		$stat_data['low_performing_trips'] = $low_performing_trips;

// 		$stat_data['top_destinations'] = $top_destinations;
// 		$stat_data['bottom_destinations'] = $bottom_destinations;

// 		$stat_data['booking_stat_from'] = $submission_request['booking_stat_from'] ?? '';
// 		$stat_data['booking_stat_to'] = $submission_request['booking_stat_to'] ?? '';
// 	}
	

// 	return $stat_data;
// }


/**
 * Get Booking Status List.
 *
 * @since 1.0.5
 */
function wptravel_get_booking_status() {
	$status = array(
		'pending'  => array(
			'color' => '#FF9800',
			'text'  => __( 'Pending', 'wp-travel' ),
		),
		'booked'   => array(
			'color' => '#008600',
			'text'  => __( 'Booked', 'wp-travel' ),
		),
		'canceled' => array(
			'color' => '#FE450E',
			'text'  => __( 'Canceled', 'wp-travel' ),
		),
		'N/A'      => array(
			'color' => '#892E2C',
			'text'  => __( 'N/A', 'wp-travel' ),
		),
	);

	return apply_filters( 'wp_travel_booking_status_list', $status );
}

function wptravel_make_stat_data( $stat_datas, $show_empty = false ) {
	if ( ! $stat_datas ) {
		return;
	}
	// Split stat data.
	if ( is_array( $stat_datas ) && count( $stat_datas ) > 0 ) {
		$data              = array();
		$data_label        = array();
		$data_bg_color     = array();
		$data_border_color = array();
		foreach ( $stat_datas as $stat_data ) {
			$data[]              = isset( $stat_data['data'] ) ? $stat_data['data'] : array();
			$data_label[]        = isset( $stat_data['data_label'] ) ? $stat_data['data_label'] : array();
			$data_bg_color[]     = isset( $stat_data['data_bg_color'] ) ? $stat_data['data_bg_color'] : array();
			$data_border_color[] = isset( $stat_data['data_border_color'] ) ? $stat_data['data_border_color'] : array();
		}
	}

	if ( is_array( $data ) ) {
		if ( count( $data ) == 1 ) {
			$default_array_key = array_keys( $data[0] );
			$new_data[]        = array_values( $data[0] );

		} elseif ( count( $data ) > 1 ) {
			if ( count( $data ) > 1 ) {
				$array_with_all_keys = $data[0];
				for ( $i = 0; $i < count( $data ) - 1; $i++ ) {
					$next_array_key         = array_keys( $data[ $i + 1 ] );
					$next_array_default_val = array_fill_keys( $next_array_key, 0 );

					$array_with_all_keys = array_merge( $next_array_default_val, $array_with_all_keys );
					uksort(
						$array_with_all_keys,
						function( $a, $b ) {
							return strtotime( $a ) > strtotime( $b );
						}
					);
				}
				$default_array_key = array_keys( $array_with_all_keys );
				$default_stat_val  = null;
				if ( $show_empty ) {
					$default_stat_val = 0;
				}
				$array_key_default_val = array_fill_keys( $default_array_key, $default_stat_val );

				$new_data = array();
				for ( $i = 0; $i < count( $data ); $i++ ) {
					$new_array = array_merge( $array_key_default_val, $data[ $i ] );
					uksort(
						$new_array,
						function( $a, $b ) {
							return strtotime( $a ) > strtotime( $b );
						}
					);
					$new_data[] = array_values( $new_array );
				}
			}
		}
		$new_return_data['stat_label']        = $default_array_key;
		$new_return_data['data']              = $new_data;
		$new_return_data['data_label']        = $data_label;
		$new_return_data['data_bg_color']     = $data_bg_color;
		$new_return_data['data_border_color'] = $data_border_color;

		return $new_return_data;
	}
}
