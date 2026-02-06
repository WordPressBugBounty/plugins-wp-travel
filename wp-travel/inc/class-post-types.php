<?php
/**
 * WP Travel Post Type class
 *
 * @package WP_Travel
 */

/**
 * WP Travel Post Type class
 */
class WP_Travel_Post_Types {
 // @phpcs:ignore

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function init() {
		self::register_bookings();
		self::register_trip();
		self::register_payment();
		self::register_travel_guide();

		WP_Travel_Post_Status::init();
	}
	/**
	 * Register Post Type Trip.
	 *
	 * @return void
	 */
	public static function register_trip() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$permalink = wptravel_get_permalink_structure();
		$labels    = array(
			'name'               => _x( 'Trips', 'post type general name', 'wp-travel' ),
			'singular_name'      => _x( 'Trip', 'post type singular name', 'wp-travel' ),
			'menu_name'          => _x( 'Trips', 'admin menu', 'wp-travel' ),
			'name_admin_bar'     => _x( 'Trip', 'add new on admin bar', 'wp-travel' ),
			'add_new'            => _x( 'New Trip', 'wp-travel', 'wp-travel' ),
			'add_new_item'       => __( 'Add New Trip', 'wp-travel' ),
			'new_item'           => __( 'New Trip', 'wp-travel' ),
			'edit_item'          => __( 'Edit Trip', 'wp-travel' ),
			'view_item'          => __( 'View Trip', 'wp-travel' ),
			'all_items'          => __( 'All Trips', 'wp-travel' ),
			'search_items'       => __( 'Search Trips', 'wp-travel' ),
			'parent_item_colon'  => __( 'Parent Trips:', 'wp-travel' ),
			'not_found'          => __( 'No Trips found.', 'wp-travel' ),
			'not_found_in_trash' => __( 'No Trips found in Trash.', 'wp-travel' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-travel' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => $permalink['wp_travel_trip_base'],
				'with_front' => true,
			),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'comments', 'excerpt', 'revisions' ),
			'menu_icon'          => 'dashicons-location',
			'menu_position'      => 30,
			'show_in_rest'       => true,
		);

		if( apply_filters( 'wp_travel_disable_block_editor_for_trip', false ) == false ){
			if( class_exists( 'WP_Travel_Blocks' ) || class_exists( 'WpTravelElementorExtended\Main' ) ){
				$args['supports'][] = 'editor';
			}
		}

		/**
		 * Register a itineraries post type.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		register_post_type( WP_TRAVEL_POST_TYPE, $args );

		$post_types = array( 'itineraries' );
		$fields     = array(
			'wp_travel_lat'                  => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_lng'                  => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_trip_map_use_lat_lng' => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_location'             => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_overview'             => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_outline'              => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_trip_include'         => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
			'wp_travel_trip_exclude'         => array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			),
		);
		/**
		 * Filter to add meta fields for itinerary.
		 *
		 * @since 5.0.8
		 */
		$fields = apply_filters( 'wptravel_itinerary_meta_fields', $fields ); // Need to change advanced galley key for advanced gallery.
		self::register_meta_fields( $post_types, $fields );
	}

	/**
	 * Register Post Type Bookings.
	 *
	 * @return void
	 */
	public static function register_bookings() {
		$labels = array(
			'name'               => _x( 'Bookings', 'post type general name', 'wp-travel' ),
			'singular_name'      => _x( 'Booking', 'post type singular name', 'wp-travel' ),
			'menu_name'          => _x( 'WP Travel', 'admin menu', 'wp-travel' ),
			'name_admin_bar'     => _x( 'Booking', 'add new on admin bar', 'wp-travel' ),
			'add_new'            => _x( 'Add New', 'wp-travel', 'wp-travel' ),
			'add_new_item'       => __( 'Add New booking', 'wp-travel' ),
			'new_item'           => __( 'New booking', 'wp-travel' ),
			'edit_item'          => __( 'View booking', 'wp-travel' ),
			'view_item'          => __( 'View booking', 'wp-travel' ),
			'all_items'          => __( 'Bookings', 'wp-travel' ),
			'search_items'       => __( 'Search bookings', 'wp-travel' ),
			'parent_item_colon'  => __( 'Parent bookings:', 'wp-travel' ),
			'not_found'          => __( 'No bookings found.', 'wp-travel' ),
			'not_found_in_trash' => __( 'No bookings found in Trash.', 'wp-travel' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-travel' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
			'menu_icon'          => 'dashicons-wp-travel',
			'with_front'         => true,
			'menu_position'      => 30,
			'show_in_rest'       => true,
		);
		/**
		 * Register a itinerary-booking post type.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		register_post_type( 'itinerary-booking', $args );
	}

	/**
	 * Register Post Type Enquiries.
	 *
	 * @return void
	 */
	public static function register_enquiries() {
		$labels = array(
			'name'               => _x( 'Enquiries', 'post type general name', 'wp-travel' ),
			'singular_name'      => _x( 'Enquiry', 'post type singular name', 'wp-travel' ),
			'menu_name'          => _x( 'Enquiries', 'admin menu', 'wp-travel' ),
			'name_admin_bar'     => _x( 'Enquiry', 'add new on admin bar', 'wp-travel' ),
			'add_new'            => _x( 'Add New', 'wp-travel', 'wp-travel' ),
			'add_new_item'       => __( 'Add New Enquiry', 'wp-travel' ),
			'new_item'           => __( 'New Enquiry', 'wp-travel' ),
			'edit_item'          => __( 'View Enquiry', 'wp-travel' ),
			'view_item'          => __( 'View Enquiry', 'wp-travel' ),
			'all_items'          => __( 'Enquiries', 'wp-travel' ),
			'search_items'       => __( 'Search Enquiries', 'wp-travel' ),
			'parent_item_colon'  => __( 'Parent Enquiries:', 'wp-travel' ),
			'not_found'          => __( 'No Enquiries found.', 'wp-travel' ),
			'not_found_in_trash' => __( 'No Enquiries found in Trash.', 'wp-travel' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-travel' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => 'edit.php?post_type=itinerary-booking',
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports' => [ 'title', 'custom-fields' ],
			'menu_icon'          => 'dashicons-help',
			'with_front'         => true,
			'show_in_rest'       => true,
		);
		/**
		 * Register a itinerary-booking post type.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		register_post_type( 'itinerary-enquiries', $args );
	}

	/**
	 * Register Post Type Payment.
	 *
	 * @return void
	 */
	public static function register_payment() {
		$labels = array(
			'name'               => _x( 'Payments', 'post type general name', 'wp-travel' ),
			'singular_name'      => _x( 'Payment', 'post type singular name', 'wp-travel' ),
			'menu_name'          => _x( 'Payments', 'admin menu', 'wp-travel' ),
			'name_admin_bar'     => _x( 'Payment', 'add new on admin bar', 'wp-travel' ),
			'add_new'            => _x( 'Add New', 'wp-travel', 'wp-travel' ),
			'add_new_item'       => __( 'Add New Payment', 'wp-travel' ),
			'new_item'           => __( 'New Payment', 'wp-travel' ),
			'edit_item'          => __( 'Edit Payment', 'wp-travel' ),
			'view_item'          => __( 'View Payment', 'wp-travel' ),
			'all_items'          => __( 'All Payments', 'wp-travel' ),
			'search_items'       => __( 'Search Payments', 'wp-travel' ),
			'parent_item_colon'  => __( 'Parent Payments:', 'wp-travel' ),
			'not_found'          => __( 'No Payments found.', 'wp-travel' ),
			'not_found_in_trash' => __( 'No Payments found in Trash.', 'wp-travel' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-travel' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'itinerary-payment' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'comments' ),
			'menu_icon'          => 'dashicons-cart',
		);
		/**
		 * Register a Payments post type.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		register_post_type( 'wp-travel-payment', $args );
	}

	/**
	 * Register Post Type WP Travel Tour Extras.
	 *
	 * @return void
	 */
	public static function register_tour_extras() {
		$labels = array(
			'name'               => _x( 'Trip Extras', 'post type general name', 'wp-travel' ),
			'singular_name'      => _x( 'Trip Extra', 'post type singular name', 'wp-travel' ),
			'menu_name'          => _x( 'Trip Extras', 'admin menu', 'wp-travel' ),
			'name_admin_bar'     => _x( 'Trip Extra', 'add new on admin bar', 'wp-travel' ),
			'add_new'            => _x( 'Add New', 'wp-travel', 'wp-travel' ),
			'add_new_item'       => __( 'Add New Trip Extra', 'wp-travel' ),
			'new_item'           => __( 'New Trip Extra', 'wp-travel' ),
			'edit_item'          => __( 'Edit Trip Extra', 'wp-travel' ),
			'view_item'          => __( 'View Trip Extra', 'wp-travel' ),
			'all_items'          => __( 'Trip Extras', 'wp-travel' ),
			'search_items'       => __( 'Search Trip Extras', 'wp-travel' ),
			'parent_item_colon'  => __( 'Parent Trip Extras:', 'wp-travel' ),
			'not_found'          => __( 'No Trip Extras found.', 'wp-travel' ),
			'not_found_in_trash' => __( 'No Trip Extras found in Trash.', 'wp-travel' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-travel' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=itinerary-booking',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'tour-extras' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'thumbnail' ),
			'menu_icon'          => 'dashicons-wp-travel',
			'show_in_rest'       => true,
		);

		$args = apply_filters( 'wp_travel_tour_extras_post_type_args', $args ); // @phpcs:ignore
		/**
		 * Register a WP Travel Tour Extras post type.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		register_post_type( 'tour-extras', $args );
	}

	public static function register_travel_guide() {
		$guide_labels = array(
			'name'               => _x( 'Travel Guide', 'Post type general name', 'wp-travel' ),
			'singular_name'      => _x( 'Travel Guide', 'Post type singular name', 'wp-travel' ),
			'menu_name'          => _x( 'Travel Guide', 'Admin Menu text', 'wp-travel' ),
			'name_admin_bar'     => _x( 'Travel Guide', 'Add New on Toolbar', 'wp-travel' ),
			'add_new'            => __( 'Add New', 'wp-travel' ),
			'add_new_item'       => __( 'Add New Travel Guide', 'wp-travel' ),
			'new_item'           => __( 'New Tour Travel Guide', 'wp-travel' ),
			'edit_item'          => __( 'Edit Travel Guide', 'wp-travel' ),
			'view_item'          => __( 'View Travel Guide', 'wp-travel' ),
			'all_items'          => __( 'All Travel Guide', 'wp-travel' ),
			'search_items'       => __( 'Search Travel Guide', 'wp-travel' ),
			'parent_item_colon'  => __( 'Parent Travel Guide:', 'wp-travel' ),
			'not_found'          => __( 'No guides found.', 'wp-travel' ),
			'not_found_in_trash' => __( 'No guides found in Trash.', 'wp-travel' ),
		);

		$guide_args = array(
			'labels'             => $guide_labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'travel-guide' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'travel-guide', $guide_args );
	}

	/**
	 * Register meta fields as per post types.
	 *
	 * @param array $post_types Collection of post type.
	 * @param array $fields     Meta fields.
	 *
	 * @since 5.0.8
	 */
	public static function register_meta_fields( $post_types, $fields ) {
		if ( ! $post_types || ! $fields ) {
			return;
		}

		if ( ! empty( $post_types ) && ! empty( $fields ) ) {
			foreach ( $post_types as $pt ) {
				foreach ( $fields as $meta_key => $field ) {
					register_post_meta( $pt, $meta_key, $field );
				}
			}
		}

	}
}


/**
 * Register Custom Post Type: Temporary Bookings
 * This Post Type is added to handle unpaid paypal bookings
 */
function register_temporary_bookings_post_type() {

	$labels = array(
		'name'                  => _x( 'Temporary Bookings', 'Post Type General Name', 'textdomain' ),
		'singular_name'         => _x( 'Temporary Booking', 'Post Type Singular Name', 'textdomain' ),
		'menu_name'             => __( 'Temp Bookings', 'textdomain' ),
		'name_admin_bar'        => __( 'Temporary Booking', 'textdomain' ),
		'add_new'               => __( 'Add New', 'textdomain' ),
		'add_new_item'          => __( 'Add New Temporary Booking', 'textdomain' ),
		'edit_item'             => __( 'Edit Temporary Booking', 'textdomain' ),
		'new_item'              => __( 'New Temporary Booking', 'textdomain' ),
		'view_item'             => __( 'View Temporary Booking', 'textdomain' ),
		'search_items'          => __( 'Search Temporary Bookings', 'textdomain' ),
		'not_found'             => __( 'No temporary bookings found', 'textdomain' ),
		'not_found_in_trash'    => __( 'No temporary bookings found in Trash', 'textdomain' ),
	);

	$args = array(
		'label'                 => __( 'Temporary Bookings', 'textdomain' ),
		'labels'                => $labels,
		'description'           => __( 'Stores temporary or pending booking records.', 'textdomain' ),
		'public'                => false,          // not publicly queryable
		'show_ui'               => true,           // visible in admin
		'show_in_menu'          => false,
		'menu_icon'             => 'dashicons-clock',
		'supports'              => array( 'title', 'custom-fields' ),
		'capability_type'       => 'post',
		'has_archive'           => false,
		'rewrite'               => false,          // no front-end URLs
		'publicly_queryable'    => false,
		'show_in_rest'          => false,          // optional: disable block editor
		'menu_position'         => 25,
	);

	register_post_type( 'pending-booking', $args );
}
add_action( 'init', 'register_temporary_bookings_post_type' );


// Add trip filter dropdown for itinerary bookings
add_action( 'restrict_manage_posts', 'add_trip_filter_to_itinerary_bookings' );
function add_trip_filter_to_itinerary_bookings() {

    global $typenow;

    if ( 'itinerary-booking' !== $typenow ) {
        return;
    }

    $trips = get_posts( array(
        'post_type'      => 'itineraries',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $selected_trip = isset( $_GET['booking_trip_id'] ) ? absint( $_GET['booking_trip_id'] ) : '';

    echo '<select name="booking_trip_id">';
    echo '<option value="">' . esc_html__( 'All Trips', 'wp-travel-pro' ) . '</option>';

    foreach ( $trips as $trip ) {
        printf(
            '<option value="%d"%s>%s</option>',
            esc_attr( $trip->ID ),
            selected( $selected_trip, $trip->ID, false ),
            esc_html( $trip->post_title )
        );
    }

    echo '</select>';
}

add_action( 'pre_get_posts', 'filter_itinerary_bookings_by_trip' );
function filter_itinerary_bookings_by_trip( $query ) {

    global $pagenow;

    if (
        ! is_admin() ||
        'edit.php' !== $pagenow ||
        ! $query->is_main_query() ||
        empty( $_GET['post_type'] ) ||
        'itinerary-booking' !== $_GET['post_type'] ||
        empty( $_GET['booking_trip_id'] )
    ) {
        return;
    }

    $trip_id = absint( $_GET['booking_trip_id'] );

    // Use REGEXP to match the trip_id inside serialized array
    $query->set( 'meta_query', array(
        array(
            'key'     => 'order_items_data',
            'value'   => 's:7:"trip_id";i:' . $trip_id . ';',
            'compare' => 'REGEXP',
        ),
    ) );
}

// Remove empty search parameter for itinerary bookings
add_action( 'admin_footer-edit.php', 'remove_empty_search_param_script_for_bookings' );
function remove_empty_search_param_script_for_bookings() {
    global $typenow;
    if ( $typenow !== 'itinerary-booking' ) return;
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('posts-filter');
            if (!form) return;

            form.addEventListener('submit', function () {
                const searchInput = form.querySelector('input[name="s"]');
                if (searchInput && searchInput.value.trim() === '') {
                    searchInput.parentNode.removeChild(searchInput);
                }
            });
        });
    </script>
    <?php
}

