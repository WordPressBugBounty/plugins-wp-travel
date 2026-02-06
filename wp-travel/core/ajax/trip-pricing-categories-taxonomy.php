<?php
class WP_Travel_Ajax_Trip_Pricing_Categories_Taxonomy {
	public static function init() {
		// Remove item from trip
		add_action( 'wp_ajax_wp_travel_get_trip_pricing_categories_terms', array( __CLASS__, 'get_trip_pricing_categories_terms' ) );
		add_action( 'wp_ajax_nopriv_wp_travel_get_trip_pricing_categories_terms', array( __CLASS__, 'get_trip_pricing_categories_terms' ) );

		add_action( 'wp_ajax_wp_travel_get_trip_pricing_categories_term', array( __CLASS__, 'get_trip_pricing_categories_term' ) );
		add_action( 'wp_ajax_nopriv_wp_travel_get_trip_pricing_categories_term', array( __CLASS__, 'get_trip_pricing_categories_term' ) );
	}

	public static function get_trip_pricing_categories_terms() {
		
		$permission = WP_Travel::verify_nonce();

		if ( ! $permission || is_wp_error( $permission ) ) {
			WP_Travel_Helpers_REST_API::response( $permission );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			WP_Travel_Helpers_REST_API::response(
				new WP_Error(
					'forbidden',
					__( 'You are not allowed to get trip pricing categories terms.', 'wp-travel' ),
					array( 'status' => 403 )
				)
			);
			exit;
		}

		$response = WP_Travel_Helpers_Trip_Pricing_Categories_Taxonomy::get_trip_pricing_categories_terms();
		WP_Travel_Helpers_REST_API::response( $response );
	}

	public static function get_trip_pricing_categories_term() {

		
		$permission = WP_Travel::verify_nonce();

		if ( ! $permission || is_wp_error( $permission ) ) {
			WP_Travel_Helpers_REST_API::response( $permission );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			WP_Travel_Helpers_REST_API::response(
				new WP_Error(
					'forbidden',
					__( 'You are not allowed to get trip categories term.', 'wp-travel' ),
					array( 'status' => 403 )
				)
			);
			exit;
		}

		WP_Travel::verify_nonce();
		$category_id = ! empty( $_GET['pricing_category_id'] ) ? absint( $_GET['pricing_category_id'] ) : 0;
		$response    = WP_Travel_Helpers_Trip_Pricing_Categories_Taxonomy::get_trip_pricing_categories_term( $category_id );
		WP_Travel_Helpers_REST_API::response( $response );
	}
}

WP_Travel_Ajax_Trip_Pricing_Categories_Taxonomy::init();