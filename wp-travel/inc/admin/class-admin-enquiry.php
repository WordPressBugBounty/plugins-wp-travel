<?php 

class WP_Travel_Admin_Enquiry {

	// private static $post_type = 'itinerary-enquiries';

	public function __construct() {

		// Hook into the submenu filter
		add_filter( 'wp_travel_submenus', [ $this, 'add_enquiry_settings_submenu' ] );

		add_action( 'admin_init', [ $this, 'conditionally_register_admin_ajax' ] );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_get_itinerary_enquiries', [ $this, 'get_itinerary_enquiries_callback' ] );
			add_action( 'wp_ajax_delete_itinerary_enquiry', [ $this, 'handle_delete_itinerary_enquiry' ] );
			add_action( 'wp_ajax_wptravel_mark_enquiry_read', [ $this, 'handle_mark_enquiry_read' ] );
		}
		
	}

	public function conditionally_register_admin_ajax() {
		if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'wp-travel-enquiry-settings' ) {
			add_action( 'wp_ajax_get_itinerary_enquiries', [ $this, 'get_itinerary_enquiries_callback' ] );
			add_action( 'wp_ajax_delete_itinerary_enquiry', [ $this, 'handle_delete_itinerary_enquiry' ] );
		}
	}

	/**
	 * Adds a custom submenu item to WP Travel admin.
	 */
	public function add_enquiry_settings_submenu( $submenus ) {
		global $wpdb;

		$pending_count = (int) $wpdb->get_var("
			SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = 'wp_travel_enquiry_status'
			AND pm.meta_value = 'unread'
			AND p.post_type = 'itinerary-enquiries'
			AND p.post_status = 'publish'
		");

		$notification = $pending_count > 0
			? ' <span class="submenu-enquiry-notification">' . $pending_count . '</span>'
			: '';

		$submenus['bookings']['enquiry_settings'] = array(
			'priority'   => 135,
			'page_title' => 'Itinerary Enquiries',
			'menu_title' => 'Enquiries' . $notification,
			'menu_slug'  => 'wp-travel-enquiry-settings',
			'callback'   => [ __CLASS__, 'render_enquiry_page' ],
		);

		return $submenus;
	}


	/**
	 * Renders the submenu page content.
	 */
	public static function render_enquiry_page() {
		?>
		<div id="wptravel-admin-enquiry-page">
			
		</div>
		<?php
	}

	function handle_mark_enquiry_read() {

		$permission = WP_Travel::verify_nonce();

		if ( ! $permission || is_wp_error( $permission ) ) {
			WP_Travel_Helpers_REST_API::response( $permission );
			exit;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
			exit;
		}

		$enquiry_id = absint( $_POST['enquiry_id'] ?? 0 );
		if ( ! $enquiry_id || get_post_type( $enquiry_id ) !== 'itinerary-enquiries' ) {
			wp_send_json_error( 'Invalid enquiry ID', 400 );
		}

		// Update post meta to 'Read'
		update_post_meta( $enquiry_id, 'wp_travel_enquiry_status', 'Read' );

		wp_send_json_success();
	}

	public function get_itinerary_enquiries_callback() {

		$permission = WP_Travel::verify_nonce();

		if ( ! $permission || is_wp_error( $permission ) ) {
			WP_Travel_Helpers_REST_API::response( $permission );
			exit;
		}
		
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
			exit;
		}

		$args = [
			'post_type'      => 'itinerary-enquiries',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		$query = new WP_Query( $args );
		$enquiries = [];

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			$form_fields = get_option( 'wp_travel_forms_properties' )[str_replace( '-', '_', get_bloginfo( 'language' ))]['enquiry'];
			$dynamic_fields = [];

			if( $form_fields ){
				foreach ( $form_fields as $field ) {
					if ( isset( $field['name'] ) ) {
						// Optional: get post meta value using the field's name, if stored that way
						// $value = get_post_meta( $post_id, $field['name'], true );

						$dynamic_fields[ $field['name'] ] = [
							'label' => $field['label'],
							'value'	=> get_post_meta( $post_id, $field['name'], true ) ?: 'N/A',
						]; 
					}
				}
			}else{
				$dynamic_fields['wp_travel_enquiry_name'] = [
					'label' => esc_html__( 'Full Name', 'wp-travel' ),
					'value'	=> get_post_meta( $post_id, 'wp_travel_enquiry_name', true ) ?: 'N/A',
				];

				$dynamic_fields['wp_travel_enquiry_email'] = [
					'label' => esc_html__( 'Email', 'wp-travel' ),
					'value'	=> get_post_meta( $post_id, 'wp_travel_enquiry_email', true ) ?: 'N/A',
				];

				$dynamic_fields['wp_travel_enquiry_query'] = [
					'label' => esc_html__( 'Enquiry Message', 'wp-travel' ),
					'value'	=> get_post_meta( $post_id, 'wp_travel_enquiry_query', true ) ?: 'N/A',
				];
			}
			
			$enquiries[] = array_merge([
				'id'           => $post_id,
				'title'        => get_the_title( $post_id ),
				'trip_link'        => get_the_permalink( get_post_meta( $post_id, 'wp_travel_post_id', true ) ),
				'enquiry_date' => get_the_date( 'd M Y', $post_id ),
				'trip_name'      => get_the_title( get_post_meta( $post_id, 'wp_travel_post_id', true ) ) ?: 'N/A',
				'status'         => get_post_meta( $post_id, 'wp_travel_enquiry_status', true ) ?: 'Pending',
			], $dynamic_fields);
		}

		wp_reset_postdata();

		wp_send_json_success( $enquiries );
	}

	public function handle_delete_itinerary_enquiry() {
		$permission = WP_Travel::verify_nonce();

		if ( ! $permission || is_wp_error( $permission ) ) {
			WP_Travel_Helpers_REST_API::response( $permission );
			exit;
		}
		
		if ( ! is_super_admin()  ) {
			wp_send_json_error( __( 'Permission denied.', 'wp-travel' ) );
			exit;
		}

		$enquiry_id = absint( $_POST['enquiry_id'] ?? 0 );

		if ( ! $enquiry_id || get_post_type( $enquiry_id ) !== 'itinerary-enquiries' ) {
			wp_send_json_error( __( 'Invalid enquiry ID.', 'wp-travel' ) );
		}

		$deleted = wp_delete_post( $enquiry_id, true );

		if ( $deleted ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( __( 'Could not delete enquiry.', 'wp-travel' ) );
		}
	}
}

new WP_Travel_Admin_Enquiry();
