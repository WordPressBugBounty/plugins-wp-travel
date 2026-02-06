<?php
/**
 * Adds dashboard Widgets for WP Travel.
 *
 * @package WP_Travel
 * @since 1.5.4
 */

class WP_Travel_Admin_Dashboard_Widgets {
	/**
	 * Assets path.
	 */
	var $assets_path;

	public function __construct() {
		$this->assets_path = plugin_dir_url( WP_TRAVEL_PLUGIN_FILE );
		add_action( 'wp_dashboard_setup', array( $this, 'add_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_widgets() {

		$bookings = wp_count_posts( 'itinerary-booking' );
		add_meta_box( 'wp-travel-quick-overview', __( 'WP Travel: Quick Overview', 'wp-travel' ), array( $this, 'wp_travel_dashboard_overview' ), 'dashboard', 'side', 'high' );

		// latest Bookings Widget.
		if ( 0 !== $bookings->publish && current_user_can( 'administrator' ) ) {
			add_meta_box( 'wp-travel-recent-bookings', __( 'WP Travel: Recent Bookings', 'wp-travel' ), array( $this, 'new_booking_callback' ), 'dashboard', 'side', 'high' );
		}

		$enquiry = wp_count_posts( 'itinerary-enquiries' );
		if ( 0 !== $bookings->publish && current_user_can( 'administrator' ) ) {
			add_meta_box( 'wp-travel-recent-enquiries', __( 'WP Travel: Recent Enquiries', 'wp-travel' ), array( $this, 'new_enquiries_callback' ), 'dashboard', 'side', 'high' );
		}

	}

	public function enqueue_scripts() {

		$screen = get_current_screen();

		if ( 'dashboard' === $screen->id ) {
			wp_enqueue_style( 'wp-travel-dashboard-widget-styles', $this->assets_path . 'app/assets/css/wp-travel-dashboard-widget.css', array(), WP_TRAVEL_VERSION );
		}

	}

	public function wp_travel_dashboard_overview() { 
		// Later replace these with dynamic values
		$args = array(
			'post_type'      => 'itinerary-booking',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'wp_travel_booking_status',
					'value' => 'booked',
				)
			),
		);

		$booked_query = new WP_Query( $args );
		$total_bookings = $booked_query->found_posts;

		global $wpdb;
		$all_performing_trips = $wpdb->get_results("
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
		", ARRAY_A);
		$booking_data = [];
		foreach ( $all_performing_trips as $booking ) {
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
		uasort( $booking_data, function ( $a, $b ) {
			return $b['total_bookings'] <=> $a['total_bookings'];
		});
		$all_total_revenue = 0;
		foreach ( $booking_data as $trip ) {
			$all_total_revenue += $trip['total_revenue'];
		}
		$total_earnings = $all_total_revenue;

		$trip_counts = wp_count_posts( 'itineraries' );
		$total_trips = isset( $trip_counts->publish ) ? $trip_counts->publish : 0;

		$enquiry_counts = wp_count_posts( 'itinerary-enquiries' );
		$total_enquiry  = isset( $enquiry_counts->publish ) ? $enquiry_counts->publish : 0;

		$customer_query = new WP_User_Query( array(
			'role'   => 'wp-travel-customer',
			'fields' => 'ID',
		) );
		$total_customer = $customer_query->get_total();

		?>

		<style>
			.wptravel-dashboard-box { 
				padding: 8px 0;
				margin-bottom: 6px;
				border-bottom: 1px solid #e1e1e1;
			}
			.wptravel-dashboard-box:last-child {
				border-bottom: none;
			}
			.wptravel-dashboard-label {
				font-weight: 600;
			}
			.wptravel-dashboard-value {
				float: right;
				font-weight: 700;
				color: #2271b1;
			}
		</style>

		<div class="wptravel-dashboard-widget">
			<div class="wptravel-dashboard-box">
				<a href="<?php echo admin_url( 'edit.php?post_type=itinerary-booking' ); ?>" class="wptravel-dashboard-label">
					<?php _e( 'Total Confirmed Bookings', 'wp-travel' ); ?>
				</a>
				<span class="wptravel-dashboard-value">
					<?php echo esc_html( $total_bookings ); ?>
				</span>
			</div>

			<div class="wptravel-dashboard-box">
				<span class="wptravel-dashboard-label"><?php _e( 'Total Earnings', 'wp-travel' ); ?></span>
				<span class="wptravel-dashboard-value"><?php echo wptravel_get_formated_price_currency( $total_earnings, true ); ?></span>
			</div>

			<div class="wptravel-dashboard-box">
				<span class="wptravel-dashboard-label"><?php _e( 'Total Customers', 'wp-travel' ); ?></span>
				<span class="wptravel-dashboard-value"><?php echo esc_html( $total_customer); ?></span>
			</div>

			<div class="wptravel-dashboard-box">
				<a href="<?php echo admin_url( 'edit.php?post_type=itineraries' ); ?>" 
				class="wptravel-dashboard-label">
					<?php _e( 'Published Trips', 'wp-travel' ); ?>
				</a>
				<span class="wptravel-dashboard-value">
					<?php echo esc_html( $total_trips ); ?>
				</span>
			</div>

			<div class="wptravel-dashboard-box">
				<a href="<?php echo admin_url( 'edit.php?post_type=itinerary-booking&page=wp-travel-enquiry-settings' ); ?>" 
				class="wptravel-dashboard-label">
					<?php _e( 'Total Enquiries', 'wp-travel' ); ?>
				</a>
				<span class="wptravel-dashboard-value"><?php echo esc_html( $total_enquiry ); ?></span>
			</div>

		</div>

		<?php
	}

	public function new_booking_callback() {

		$args = array(
			'numberposts' => apply_filters( 'wp_travel_dashboard_widget_bookings', 5 ),
			'post_type'   => 'itinerary-booking',
		);

		$bookings = get_posts( $args );
		if ( ! empty( $bookings ) && is_array( $bookings ) ) : ?>
			<table class="wp_travel_booking_dashboard_widget">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'wp-travel' ); ?></th>
						<!-- <th><?php esc_html_e( 'Trip Code', 'wp-travel' ); ?></th> -->
						<th><?php esc_html_e( 'Contact Name', 'wp-travel' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wp-travel' ); ?></th>
						<!-- <th><?php esc_html_e( 'Payment', 'wp-travel' ); ?></th> -->
						<th><?php esc_html_e( 'Date', 'wp-travel' ); ?></th>
					</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $bookings as $k => $booking ) :
					// Set Vars.
					$id         = $booking->ID;
					$booking_id = $booking->post_title;

					$trip_id   = get_post_meta( $id, 'wp_travel_post_id', true );
					$trip_code = wptravel_get_trip_code( $trip_id );

					$first_name = get_post_meta( $id, 'wp_travel_fname_traveller', true );
					if ( ! $first_name ) {
						// Legacy version less than 1.7.5 [ retriving value from old meta once. update post will update into new meta ].
						$first_name = get_post_meta( $id, 'wp_travel_fname', true );
					}
					$middle_name = get_post_meta( $id, 'wp_travel_mname_traveller', true );
					if ( ! $middle_name ) {
						$middle_name = get_post_meta( $id, 'wp_travel_mname', true );
					}
					$last_name = get_post_meta( $id, 'wp_travel_lname_traveller', true );
					if ( ! $last_name ) {
						$last_name = get_post_meta( $id, 'wp_travel_mname', true );
					}

					if ( is_array( $first_name ) ) { // Multiple Travelers.

						reset( $first_name );
						$first_key = key( $first_name );

						$name = '';
						if ( isset( $first_name[ $first_key ] ) && isset( $first_name[ $first_key ][0] ) ) {
							$name .= $first_name[ $first_key ][0];
						}
						if ( isset( $middle_name[ $first_key ] ) && isset( $middle_name[ $first_key ][0] ) ) {
							$name .= ' ' . $middle_name[ $first_key ][0];
						}
						if ( isset( $last_name[ $first_key ] ) && isset( $last_name[ $first_key ][0] ) ) {
							$name .= ' ' . $last_name[ $first_key ][0];
						}
					} else {
						$name  = $first_name;
						$name .= ' ' . $middle_name;
						$name .= ' ' . $last_name;
					}

					$date = wptravel_format_date( $booking->post_date, true, 'Y-m-d' );

					// Booking Status.
					$status    = wptravel_get_booking_status();
					$label_key = get_post_meta( $id, 'wp_travel_booking_status', true );
					if ( '' === $label_key ) {
						$label_key = 'pending';
						update_post_meta( $id, 'wp_travel_booking_status', $label_key );
					}
  
					// Payment.
					$payment_id = get_post_meta( $id, 'wp_travel_payment_id', true );

					$pmt_label_key = get_post_meta( $payment_id, 'wp_travel_payment_status', true );
					if ( ! $pmt_label_key ) {
						$pmt_label_key = 'N/A';
						update_post_meta( $payment_id, 'wp_travel_payment_status', $pmt_label_key );
					}
					$Pmt_status = wptravel_get_payment_status();
					
					if( $label_key !== 'N/A' ){
						$label_key = strtolower( $label_key );
					}

					?>

					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><?php echo esc_html( $booking_id ); ?></a></td>
						<!-- <td><?php echo esc_html( $trip_code ); ?></td> -->
						<td><?php echo esc_html( $name ); ?></td>
						<td><?php echo '<span class="wp-travel-status wp-travel-booking-status" style="color:#fff;padding:2px 5px;background: ' . esc_attr( $status[ $label_key ]['color'] ) . ' ">' . esc_attr( $status[ $label_key ]['text'] ) . '</span>'; ?></td>
						<!-- <td><?php echo '<span class="wp-travel-status wp-travel-payment-status" style="color:#fff;padding:2px 5px;background: ' . esc_attr( $Pmt_status[ $pmt_label_key ]['color'], 'wp-travel' ) . ' ">' . esc_attr( $Pmt_status[ $pmt_label_key ]['text'], 'wp-travel' ) . '</span>'; ?></td> -->
						<td><?php echo esc_html( $date ); ?></td>
					</tr>

					<?php
					endforeach;
				?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="6"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=itinerary-booking' ) ); ?>" class="button button-primary"><?php esc_html_e( 'View All Bookings', 'wp-travel' ); ?></a></td>
				</tr>
			<tfoot>
			</table>
			<?php
		endif;

	}

	public function new_enquiries_callback() {

		$args = array(
			'numberposts' => apply_filters( 'wp_travel_dashboard_widget_enquiries', 5 ),
			'post_type'   => 'itinerary-enquiries',
			'post_status'    => 'publish',
		);

		$query = new WP_Query( $args );
	 	?>
			<table class="wp_travel_booking_dashboard_widget">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'wp-travel' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wp-travel' ); ?></th>
						<th><?php esc_html_e( 'Message', 'wp-travel' ); ?></th>
					</tr>
			</thead>
			<tbody>
				<?php 
					while ( $query->have_posts() ) {
						$query->the_post();
						$post_id = get_the_ID();
						?>
							<tr>
								<td><?php echo esc_html( get_post_meta( $post_id, 'wp_travel_enquiry_name', true ) ); ?></td>
								<td><?php echo esc_html( get_post_meta( $post_id, 'wp_travel_enquiry_email', true ) ); ?></td>
								<td><?php echo esc_html( get_post_meta( $post_id, 'wp_travel_enquiry_query', true ) ); ?></td>
							</tr>
						<?php
					}
					wp_reset_postdata();
				?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="6"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=itinerary-booking&page=wp-travel-enquiry-settings' ) ); ?>" class="button button-primary"><?php esc_html_e( 'View All Enquiries', 'wp-travel' ); ?></a></td>
				</tr>
			<tfoot>
			</table>
		<?php
	}
}

new WP_Travel_Admin_Dashboard_Widgets();
