<?php

class WP_Travel_Ajax {

	public function validate_cart_access() {

		global $wt_cart;

		$items = $wt_cart->getItems();

		if ( is_user_logged_in() ) { 
			foreach($wt_cart->getItems() as $item){
				$cart_user_id = $item['user_id'];
			}

			if( $cart_user_id !== get_current_user_id() ){
				wp_send_json_error(
					array(
						'message' => __( 'You have no access to this cart.', 'wp-travel' ),
					),
					401
				);
			}
		}else{
			foreach($wt_cart->getItems() as $item){
				$cart_session_id = $item['session_id'];
			}

			if ( isset($_COOKIE['wp_travel_session']) ) {

				$cookie = wp_unslash($_COOKIE['wp_travel_session']);
				$parts  = explode('||', $cookie);

				$current_session_id = ! empty($parts[0])
					? sanitize_text_field($parts[0])
					: '';
			}

			if( $cart_session_id !== $current_session_id ){
				wp_send_json_error(
					array(
						'message' => __( 'You have no access to this cart.', 'wp-travel' ),
					),
					401
				);
			}
		}

	}

	public function add_to_cart() {
		$permission = WP_Travel::verify_nonce();

		if ( ! $permission || is_wp_error( $permission ) ) {
			WP_Travel_Helpers_REST_API::response( $permission );
			exit;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$http_post_data = wptravel_sanitize_array( $_POST ); // phpcs:ignore
		$post_data      = json_decode( file_get_contents( 'php://input' ) );
		$post_data      = wptravel_sanitize_array( $post_data );
		$post_data      = ! empty( $post_data ) ? (array) $post_data : $http_post_data;

		if ( ! isset( $post_data['trip_id'] ) ) {
			return;
		}

		$skip_cart = apply_filters( 'wptravel_skip_add_to_cart', false );
		if ( $skip_cart ) {
			return;
		}

		wptravel_nocache_headers();
		global $wt_cart;

		$allow_multiple_cart_items = WP_Travel_Cart::allow_multiple_items();
		if ( ! $allow_multiple_cart_items ) {
			$wt_cart->clear();
		}

		$trip_id   = absint( $post_data['trip_id'] );
		$price_key = isset( $post_data['price_key'] ) ? sanitize_text_field( $post_data['price_key'] ) : '';
		// Pricing id contains string for legacy version below WP Travel 3.0.0.
		$pricing_id     = isset( $post_data['pricing_id'] ) ? sanitize_text_field( $post_data['pricing_id'] ) : ''; // @since 3.0.0
		$arrival_date   = isset( $post_data['arrival_date'] ) ? sanitize_text_field( $post_data['arrival_date'] ) : '';
		$departure_date = isset( $post_data['departure_date'] ) ? sanitize_text_field( $post_data['departure_date'] ) : ''; // Need to remove. is't post value.
		$pax            = isset( $post_data['pax'] ) ? (array) wptravel_sanitize_array( $post_data['pax'] ) : 0;
		$trip_extras    = isset( $post_data['wp_travel_trip_extras'] ) ? $post_data['wp_travel_trip_extras'] : array();
		$trip_price     = 0;

		$args  = array(
			'trip_id'         => $trip_id,
			'pax'             => $pax,
			'price_key'       => $price_key,
			'pricing_id'      => $pricing_id,
			'trip_start_date' => $arrival_date,
			'return_price'    => false,
			'request_data'    => $post_data,
		);
		$attrs = wptravel_get_cart_attrs( $args ); // pricing_id && $trip_start_date @since 4.0.0.

		
		if ( isset( $post_data['trip_time'] ) ) {
			$attrs['trip_time'] = $post_data['trip_time'];
		}

		$pricing_option_type = wptravel_get_pricing_option_type( $trip_id );
		if ( ( is_object( $pax ) || is_array( $pax ) ) && 'multiple-price' === $pricing_option_type ) { // @since 3.0.0
			$total_pax          = array_sum( $pax );
			$pricings           = wptravel_get_trip_pricing_option( $trip_id ); // Get Pricing Options for the trip.
			$pricing_data       = isset( $pricings['pricing_data'] ) ? $pricings['pricing_data'] : array();
			$trip               = array();
			$trip_price_partial = 0;

			foreach ( $pax as $category_id => $pax_value ) {
				$args           = array(
					'trip_id'     => $trip_id,
					'pricing_id'  => $pricing_id,
					'category_id' => $category_id,
					'price_key'   => $price_key,
				);
				$category_price = WP_Travel_Helpers_Pricings::get_price( $args );
				if ( function_exists( 'wp_travel_group_discount_price' ) ) { // From Group Discount addons.
					$group_trip_price = wp_travel_group_discount_price( $trip_id, $pax_value, $pricing_id, $category_id );

					if ( $group_trip_price ) {
						$category_price = $group_trip_price;
					}
				}
				$category_price_partial = $category_price;

				if ( wptravel_is_partial_payment_enabled() ) {
					$percent                = WP_Travel_Helpers_Pricings::get_payout_percent( $trip_id );
					$category_price_partial = ( $category_price * $percent ) / 100;
				}

				$pricing_index = null;
				foreach ( $pricing_data as $index => $pricing ) {
					if ( wptravel_is_react_version_enabled() ) {
						if ( (int) $pricing_id === (int) $pricing['pricing_id'] ) {
							$pricing_index = $index;
							break;
						}
						continue;
					}
					if ( isset( $pricing['categories'] ) && is_array( $pricing['categories'] ) ) {
						if ( array_key_exists( $category_id, $pricing['categories'] ) ) {
							$pricing_index = $index;
							break;
						};
					}
				}
				$category = isset( $pricing_data[ $pricing_index ]['categories'][ $category_id ] ) ? $pricing_data[ $pricing_index ]['categories'][ $category_id ] : array();

				$catetory_type = isset( $category['type'] ) ? $category['type'] : ''; // Old Way to get type in WP Travel.
				if ( empty( $catetory_type ) && is_numeric( $category_id ) ) { // Set category type if category is taxonomy term.
					$pricing_category = get_term( $category_id, 'itinerary_pricing_category' );
					$catetory_type    = $pricing_category->name;
				}

				$trip[ $category_id ] = array(
					'pax'           => $pax_value,
					'price'         => wptravel_get_formated_price( $category_price ),
					'price_partial' => wptravel_get_formated_price( $category_price_partial ),
					'type'          => $catetory_type,
					'custom_label'  => isset( $category['custom_label'] ) ? $category['custom_label'] : __( 'Custom', 'wp-travel' ),
					'price_per'     => isset( $category['price_per'] ) ? $category['price_per'] : 'person',
				);

				// multiply category_price by pax to add in trip price if price per is person.
				if ( 'person' == $trip[ $category_id ]['price_per'] ) {
					$category_price         *= $pax_value;
					$category_price_partial *= $pax_value;
				}
				// add price.
				$trip_price         += $category_price;
				$trip_price_partial += $category_price_partial;
			}
			$attrs['trip'] = $trip;
		} else {
			$pax       = array_sum( (array) $pax );
			$price_per = get_post_meta( $trip_id, 'wp_travel_price_per', true );
			$price_per = ! empty( $price_per ) ? $price_per : 'person';
			// multiply category_price by pax to add in trip price if price per is person.
			$args       = array(
				'trip_id'   => $trip_id,
				'price_key' => $price_key,
			);
			$price      = WP_Travel_Helpers_Pricings::get_price( $args );
			$trip_price = $price;
			if ( wptravel_is_partial_payment_enabled() ) {
				$percent                = WP_Travel_Helpers_Pricings::get_payout_percent( $trip_id );
				$category_price_partial = ( $trip_price * $percent ) / 100;
			}
			if ( 'person' === $price_per ) {
				$trip_price = $price * $pax;
			}

			// Custom Trip Price.
			if ( isset( $post_data['trip_price'] ) && $post_data['trip_price'] > 0 ) {
				$trip_price = $post_data['trip_price'];
			}
			$attrs['trip'] = array(
				"category-{$trip_id}" => array( // assigned category for single pricing to match data structure @since 3.0.0
					'pax'           => $pax,
					'price'         => $price,
					'price_partial' => wptravel_get_formated_price( $category_price_partial ),
					'type'          => 'adult', // Not set yet.
					'custom_label'  => __( 'Custom', 'wp-travel' ),
					'price_per'     => $price_per,
					'trip_price'    => $trip_price,
				),
			);

			if ( function_exists( 'wp_travel_group_discount_price' ) && 'single-pricing-id' !== $pricing_id ) { // From Group Discount addons.
				$group_trip_price = wp_travel_group_discount_price( $trip_id, $pax, $pricing_id, $pricing_id ); // for old price pricing id is treated as category id.
				if ( $group_trip_price ) {
					$trip_price = $group_trip_price;
				}
			}
		}

		// Custom Trip Price.
		if ( isset( $post_data['trip_price'] ) && $post_data['trip_price'] > 0 ) {
			$trip_price = $post_data['trip_price'];
			if ( wptravel_is_partial_payment_enabled() ) {
				$percent            = WP_Travel_Helpers_Pricings::get_payout_percent( $trip_id );
				$trip_price_partial = $trip_price * $percent / 100;
			}
		}

		$attrs['enable_partial'] = wptravel_is_partial_payment_enabled();
		if ( $attrs['enable_partial'] ) {
			$trip_price_partial             = $trip_price;
			$payout_percent                 = WP_Travel_Helpers_Pricings::get_payout_percent( $trip_id );
			$attrs['partial_payout_figure'] = $payout_percent; // added in 1.8.4.

			if ( $payout_percent > 0 ) {
				$trip_price_partial = ( $trip_price * $payout_percent ) / 100;
				$trip_price_partial = wptravel_get_formated_price( $trip_price_partial );
			}
			$attrs['trip_price_partial'] = $trip_price_partial;
		}

		$attrs['pricing_id']     = $pricing_id;
		$attrs['arrival_date']   = $arrival_date;
		$attrs['date_id']   	= isset($post_data['date_id'][0]) ? (int)$post_data['date_id'][0] : '';
		$attrs['departure_date'] = $departure_date;
		$attrs['trip_extras']    = $trip_extras;
		$attrs['pickup_location']    = isset($post_data['pickup_location']) ? $post_data['pickup_location'] : '';
		$attrs['user_id']    = get_current_user_id();

		$attrs['session_id'] = '';

		if ( isset($_COOKIE['wp_travel_session']) ) {

			$cookie = wp_unslash($_COOKIE['wp_travel_session']);
			$parts  = explode('||', $cookie);

			$attrs['session_id'] = ! empty($parts[0])
				? sanitize_text_field($parts[0])
				: '';
		}

		$attrs = apply_filters( 'wp_travel_cart_attributes', $attrs, $post_data ); // @phpcs:ignore
		
		$item_id_args = array(
			'trip_id'    => $trip_id,
			'price_key'  => $price_key,
			'start_date' => $arrival_date,
			'pricing_id' => $pricing_id,
		);
		$cart_item_id = $wt_cart->get_cart_item_id( $item_id_args );

		$update_cart_on_add = apply_filters( 'wp_travel_filter_update_cart_on_add', true ); // @phpcs:ignore

		$add_to_cart_args = array(
			'trip_id'            => $trip_id,
			'trip_price'         => $trip_price,
			'trip_price_partial' => $trip_price_partial,
			'pax'                => $total_pax,
			'price_key'          => $price_key,
			'attrs'              => $attrs,
		);
		
		if ( true === $update_cart_on_add ) {
			$items = $wt_cart->getItems();

			if ( isset( $items[ $cart_item_id ] ) ) {
				if ( is_array( $pax ) ) {
					$trip_extras = isset( $post_data['wp_travel_trip_extras'] ) ? (array) $post_data['wp_travel_trip_extras'] : array();
					$wt_cart->update( $cart_item_id, $pax, $trip_extras, $post_data );
				} else {
					$pax += $items[ $cart_item_id ]['pax'];
					$wt_cart->update( $cart_item_id, $pax );
				}
			} else {
				$wt_cart->add( $add_to_cart_args );
			}
		} else {
			$wt_cart->add( $add_to_cart_args );
		}

		echo true;

	}

}
