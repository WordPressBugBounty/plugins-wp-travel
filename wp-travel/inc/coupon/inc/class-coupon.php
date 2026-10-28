<?php
/**
 * WP_Travel_Coupon Main Class
 */
class WP_Travel_Coupon {

	/**
	 * Coupons Data array, with defaults.
	 *
	 * @since 1.4.0
	 * @var array
	 */
	protected $data = array(
		'coupon_code'        => '',
		'coupon_value'       => 0,
		'coupon_expiry_date' => null,
		'coupon_type'        => 'fixed',
		'usage_count'        => 0,
		'restricted_trips'   => array(),
		'usage_limit'        => 0,
	);

	public function __construct() {
		add_action( 'wp_travel_action_before_booking_process', array( $this, 'process_update_count' ) );
	}

	/**
	 * Update usese count action.
	 */
	public function process_update_count() {
		global $wt_cart;
		if ( ! isset( $wt_cart ) ) {
			return;
		}

		$discounts = $wt_cart->get_discounts();
		if ( ! is_array( $discounts ) || empty( $discounts['coupon_id'] ) ) {
			return; // No coupon in cart.
		}

		// Re-validate the cached coupon and atomically claim one use.
		// A stale cart can no longer carry a discount past the usage limit.
		$result = $this->claim_for_checkout( $discounts );
		if ( is_wp_error( $result ) ) {
			$wt_cart->clear_discount_values();
			WPTravel()->notices->add( $result->get_error_message(), 'error' );
			wp_safe_redirect( wptravel_get_checkout_url() );
			exit;
		}
	}
	/**
	 * Get coupon id.
	 */
	public function get_coupon_id_by_code( $string ) {


		global $wpdb;
		$string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);

		$meta_key = 'wp_travel_coupon_code';

		$sql = $wpdb->prepare(
			"
			SELECT post_id
			FROM $wpdb->postmeta
			WHERE meta_key = %s
			AND meta_value = %s
		",
			$meta_key,
			$string
		);

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {

			return false;
		}

		return $results['0']->post_id;

	}
	/**
	 * Get Coupon Metas
	 *
	 * @param int    $coupon_id coupon id.
	 * @param string $tab tab key.
	 * @param string $key meta key.
	 * @return mixed meta value or false.
	 */
	public function get_coupon_meta( $coupon_id, $tab, $key ) {

		if ( empty( $coupon_id ) || empty( $key ) || empty( $tab ) ) {
			return false;
		}

		$coupon_metas = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );

		if ( ! $coupon_metas ) {
			return false;
		}

		if ( is_array( $coupon_metas ) && ! empty( $coupon_metas ) ) {

			return isset( $coupon_metas[ $tab ][ $key ] ) ? $coupon_metas[ $tab ][ $key ] : false;

		}

		return false;

	}
	/**
	 * get discount type
	 */
	public function get_discount_type( $coupon_id ) {

		// General Tab Data.
		$coupon_metas = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$general_tab  = isset( $coupon_metas['general'] ) ? $coupon_metas['general'] : array();
		$coupon_type  = isset( $general_tab['coupon_type'] ) ? $general_tab['coupon_type'] : 'fixed';

		return $coupon_type;

	}
	/**
	 * get discount type
	 * Note: [Deprecated due to not suitable naming. use get_discount_value of this class ]
	 */
	public function get_discount_amount( $coupon_id ) {

		// General Tab Data.
		$coupon_metas = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$general_tab  = isset( $coupon_metas['general'] ) ? $coupon_metas['general'] : array();
		$coupon_value = isset( $general_tab['coupon_value'] ) ? $general_tab['coupon_value'] : '';

		return $coupon_value;

	}

	/**
	 * Return the Discount value. Value may be either in flat amount or in percent depending upon the discount type.
	 *
	 * @param Number $coupon_id Current coupon/post id.
	 *
	 * @since 4.4.7
	 * @return Number Discount value
	 */
	public function get_discount_value( $coupon_id ) {
		// Meta.
		$coupon_metas = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		return isset( $coupon_metas['general'] ) && isset( $coupon_metas['general']['coupon_value'] ) && $coupon_metas['general']['coupon_value'] ? $coupon_metas['general']['coupon_value'] : 0;
	}

	/**
	 * get usage count
	 */
	public function get_usage_count( $coupon_id ) {

		$usage_count = get_post_meta( $coupon_id, 'wp_travel_coupon_uasge_count', true );

		return ! empty( $usage_count ) ? $usage_count : 0;
	}
	/**
	 * Update usage count.
	 */
	public function update_usage_count( $coupon_id ) {

		$old_value = $this->get_usage_count( $coupon_id );

		$value = $old_value + 1;

		$value = absint( $value );

		return update_post_meta( $coupon_id, 'wp_travel_coupon_uasge_count', $value );

	}
	/**
	 * Allowed trip_ids
	 *
	 * @todo Do not use. Need to deprecated this method. use is_discountable instead
	 */
	public function trip_ids_allowed( $coupon_id, $trip_ids ) {

		if ( empty( $coupon_id ) ) {

			return false;
		}

		if ( empty( $trip_ids ) || ! is_array( $trip_ids ) ) {

			return false;

		}

		$coupon_metas     = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$restrictions_tab = isset( $coupon_metas['restriction'] ) ? $coupon_metas['restriction'] : array();
		// Field Values.
		$restricted_trips = isset( $restrictions_tab['restricted_trips'] ) ? $restrictions_tab['restricted_trips'] : array();

		if ( empty( $restricted_trips ) ) {

			return true;
		}

		foreach ( $trip_ids as $key => $trip_id ) {

			if ( in_array( $trip_id, $restricted_trips ) ) {

				return true;
			}
		}

		return false;

	}

	/**
	 * Check whether trip has discount or not.
	 *
	 * @param Number $coupon_id ID of the coupon.
	 * @param Number $trip_id Trip id to check whether this trip has discount or not.
	 *
	 * @since 4.4.7
	 *
	 * @return Boolean
	 */
	public function is_discountable( $coupon_id, $trip_id ) {

		if ( empty( $coupon_id ) || empty( $trip_id ) ) {
			return false;
		}

		$coupon_metas     = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$restrictions_tab = isset( $coupon_metas['restriction'] ) ? $coupon_metas['restriction'] : array();
		// Field Values.
		$restricted_trips = isset( $restrictions_tab['restricted_trips'] ) ? $restrictions_tab['restricted_trips'] : array();

		if ( empty( $restricted_trips ) ) {
			return true;
		}
		if ( in_array( $trip_id, $restricted_trips ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check whether trip has uses count. if uses count set and cross the limit then this will treat as not usable.
	 *
	 * @param Number $coupon_id ID of the coupon.
	 *
	 * @since 4.4.7
	 *
	 * @return Boolean
	 */
	public function is_limit_exceed( $coupon_id ) {

		$coupon_metas        = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$restrictions_tab    = isset( $coupon_metas['restriction'] ) ? $coupon_metas['restriction'] : array();
		$coupon_limit_number = isset( $restrictions_tab['coupon_limit_number'] ) ? $restrictions_tab['coupon_limit_number'] : '';

		if ( ! empty( $coupon_limit_number ) ) {
			$usage_count = WPTravel()->coupon->get_usage_count( $coupon_id );
			if ( absint( $usage_count ) >= absint( $coupon_limit_number ) ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Check whether trip has for specific user or not.
	 *
	 * @param Number $coupon_id ID of the coupon.
	 *
	 * @since 5.0.0
	 *
	 * @return Boolean
	 */
	public function valid_for_user( $coupon_id ) {

		$coupon_metas     = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$restrictions_tab = isset( $coupon_metas['restriction'] ) ? $coupon_metas['restriction'] : array();
		$coupon_user_id   = isset( $restrictions_tab['coupon_user_id'] ) ? $restrictions_tab['coupon_user_id'] : '';

		if ( ! empty( $coupon_user_id ) ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			$current_user    = wp_get_current_user();
			$current_user_id = $current_user->data->ID;
			if ( absint( $current_user_id ) === absint( $coupon_user_id ) ) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Get Discount applicable total/partial total along with extras price.
	 *
	 * @param Number $coupon_id ID of the coupon.
	 * @param Bool   $partial_total Either return partial total or full total.
	 *
	 * @since 4.4.7
	 *
	 * @return Boolean
	 */
	public function get_discount_applicable_total( $coupon_id, $partial_total = false ) {
		if ( ! $coupon_id ) {
			return 0;
		}
		global $wt_cart;

		$items = $wt_cart->getItems();

		$discount_applicable_total = 0;

		// loop cart items here.
		if ( ! $partial_total ) {
			foreach ( $items as $cart_item_id => $item ) {
				$trip_id = $item['trip_id'];
				if ( WPTravel()->coupon->is_discountable( $coupon_id, $trip_id ) ) {
					$discount_applicable_total += $wt_cart->get_item_total( $cart_item_id );
				}
			}
		} else {
			// Implement Partial in Total amount. It treat as parital is also applied in extras items.
			foreach ( $items as $cart_item_id => $item ) {
				$trip_id = $item['trip_id'];
				if ( WPTravel()->coupon->is_discountable( $coupon_id, $trip_id ) ) {
					$item_total = $wt_cart->get_item_total( $cart_item_id );

					if ( wptravel_is_partial_payment_enabled() ) {
						$payout_percent = WP_Travel_Helpers_Pricings::get_payout_percent( $trip_id );

						$item_total                 = ( $item_total * $payout_percent ) / 100;
						$discount_applicable_total += $item_total;
					} else {
						$discount_applicable_total += $item_total;
					}
				}
			}
		}
		return wptravel_get_formated_price( $discount_applicable_total );
	}

	/**
	 * Is Valid Check Coupon Validity check
	 */
	public function is_coupon_valid( $coupon_id ) {

		if ( empty( $coupon_id ) ) {
			return false;
		}

		$coupon_metas       = get_post_meta( $coupon_id, 'wp_travel_coupon_metas', true );
		$general_tab        = isset( $coupon_metas['general'] ) ? $coupon_metas['general'] : array();
		$coupon_expiry_date = isset( $general_tab['coupon_expiry_date'] ) ? $general_tab['coupon_expiry_date'] : '';

		// Check Coupon Status.
		$coupon_status = get_post_status( $coupon_id );

		if ( 'publish' !== $coupon_status ) {
			return false;
		}
		if ( ! empty( $coupon_expiry_date ) ) {
			$date_now  = new DateTime();
			$test_date = new DateTime( $coupon_expiry_date );

			// Check Expiry Date.
			$date_now  = $date_now->format( 'Y-m-d' );
			$test_date = $test_date->format( 'Y-m-d' );

			if ( strtotime( $date_now ) > strtotime( $test_date ) ) {

				return false;

			}
		}

		return true;

	}

	/**
	 * Get Coupon Status.
	 */
	public function get_coupon_status( $coupon_id ) {

		if ( ! $coupon_id || empty( $coupon_id ) ) {

			return false;
		}
		if ( ! get_post_meta( $coupon_id, 'wp_travel_coupon_code', true ) ) {
			return;
		}

		if ( ! $this->is_coupon_valid( $coupon_id ) ) {

			return 'inactive';

		}

		// Activity by usage count.
		$usage_count = $this->get_usage_count( $coupon_id );
		$limit       = $this->get_coupon_meta( $coupon_id, 'restriction', 'coupon_limit_number' );

		if ( ! empty( $limit ) ) {

			return ( $limit <= $usage_count ) ? 'limit_exceed' : 'active';
		}

		return 'active';

	}

	/**
	 * Re-validate the coupon that is cached in the cart against current data.
	 *
	 * @param array $discounts Cart discounts array ( $wt_cart->get_discounts() ).
	 * @return true|WP_Error
	 */
	public function validate_for_checkout( $discounts ) {
		global $wt_cart;

		$coupon_id = isset( $discounts['coupon_id'] ) ? absint( $discounts['coupon_id'] ) : 0;
		if ( ! $coupon_id ) {
			return new WP_Error( 'WP_TRAVEL_INVALID_COUPON', __( 'The applied coupon is no longer valid. Please review your cart.', 'wp-travel' ) );
		}

		if ( ! $this->is_coupon_valid( $coupon_id ) || ! $this->valid_for_user( $coupon_id ) ) {
			return new WP_Error( 'WP_TRAVEL_INVALID_COUPON', __( 'The applied coupon is no longer valid. Please review your cart.', 'wp-travel' ) );
		}

		// Discount definition changed since it was applied.
		$cached_type  = isset( $discounts['type'] ) ? $discounts['type'] : '';
		$cached_value = isset( $discounts['value'] ) ? $discounts['value'] : 0;
		if ( $this->get_discount_type( $coupon_id ) !== $cached_type || (float) $this->get_discount_value( $coupon_id ) !== (float) $cached_value ) {
			return new WP_Error( 'WP_TRAVEL_INVALID_COUPON', __( 'The coupon has changed. Please review your cart and apply it again.', 'wp-travel' ) );
		}

		// At least one cart item must still be eligible.
		$eligible = false;
		if ( isset( $wt_cart ) ) {
			foreach ( $wt_cart->getItems() as $item ) {
				if ( isset( $item['trip_id'] ) && $this->is_discountable( $coupon_id, $item['trip_id'] ) ) {
					$eligible = true;
					break;
				}
			}
		}
		if ( ! $eligible ) {
			return new WP_Error( 'WP_TRAVEL_COUPON_NOT_ALLOWED_FOR_TRIP', __( 'The applied coupon is not valid for the trips in your cart.', 'wp-travel' ) );
		}

		return true;
	}

	/**
	 * Meta key used to store temporary coupon reservations ( one row per session ).
	 * Row value format: "<session token>|<expiry unix timestamp>".
	 */
	const RESERVATION_META = 'wp_travel_coupon_reservation';

	/**
	 * Reservation lifetime in seconds. A session that applies a coupon holds one use for this long.
	 */
	public function get_reservation_ttl() {
		return max( 60, (int) apply_filters( 'wp_travel_coupon_reservation_ttl', 15 * MINUTE_IN_SECONDS ) );
	}

	/**
	 * Per-visitor token, stored in the WP Travel session.
	 */
	protected function get_session_token() {
		$token = WPTravel()->session->get( 'wp_travel_coupon_res_token' );
		if ( empty( $token ) || ! is_string( $token ) ) {
			$token = wp_generate_password( 24, false );
			WPTravel()->session->set( 'wp_travel_coupon_res_token', $token );
		}
		return $token;
	}

	/**
	 * Named DB lock so capacity checks and writes for one coupon are serialized.
	 */
	protected function lock_coupon( $coupon_id ) {
		global $wpdb;
		$name = substr( $wpdb->prefix . 'wptc_' . absint( $coupon_id ), 0, 60 );
		return 1 === (int) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK( %s, 5 )', $name ) );
	}

	protected function unlock_coupon( $coupon_id ) {
		global $wpdb;
		$name = substr( $wpdb->prefix . 'wptc_' . absint( $coupon_id ), 0, 60 );
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', $name ) );
	}

	/**
	 * Read confirmed usage straight from the DB ( bypasses object cache ).
	 */
	protected function read_used( $coupon_id ) {
		global $wpdb;
		return absint(
			$wpdb->get_var(
				$wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s LIMIT 1", $coupon_id, 'wp_travel_coupon_uasge_count' )
			)
		);
	}

	/**
	 * Delete expired reservations and return the number of live reservations held by OTHER sessions.
	 */
	protected function count_other_reservations( $coupon_id, $token ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $coupon_id, self::RESERVATION_META )
		);
		$others  = 0;
		$expired = array();
		$now     = time();
		foreach ( (array) $rows as $row ) {
			$parts  = explode( '|', $row->meta_value );
			$expiry = isset( $parts[1] ) ? (int) $parts[1] : 0;
			if ( $expiry <= $now ) {
				$expired[] = (int) $row->meta_id;
			} elseif ( $parts[0] !== $token ) {
				$others++;
			}
		}
		if ( $expired ) {
			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ( " . implode( ',', $expired ) . ' )' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		return $others;
	}

	protected function delete_own_reservations( $coupon_id, $token ) {
		global $wpdb;
		return (int) $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s AND meta_value LIKE %s",
				$coupon_id,
				self::RESERVATION_META,
				$wpdb->esc_like( $token ) . '|%'
			)
		);
	}

	/**
	 * Reserve one use for the current session when the coupon is applied.
	 * While a reservation is live, other sessions cannot use that slot.
	 * Re-applying from the same session refreshes its own reservation.
	 *
	 * @param int $coupon_id Coupon ID.
	 * @return bool True when the session holds a reservation ( or the coupon has no limit ).
	 */
	public function reserve_usage( $coupon_id ) {
		global $wpdb;

		$coupon_id = absint( $coupon_id );
		$limit     = absint( $this->get_coupon_meta( $coupon_id, 'restriction', 'coupon_limit_number' ) );
		if ( ! $coupon_id ) {
			return false;
		}
		if ( 0 === $limit ) {
			return true; // Unlimited coupon, nothing to reserve.
		}

		$token = $this->get_session_token();
		if ( ! $this->lock_coupon( $coupon_id ) ) {
			return false; // Fail closed.
		}

		try {
			$used   = $this->read_used( $coupon_id );
			$others = $this->count_other_reservations( $coupon_id, $token );
			$this->delete_own_reservations( $coupon_id, $token ); // Refresh = delete + re-add.

			if ( ( $used + $others ) >= $limit ) {
				return false;
			}
			add_post_meta( $coupon_id, self::RESERVATION_META, $token . '|' . ( time() + $this->get_reservation_ttl() ) );
			return true;
		} finally {
			$this->unlock_coupon( $coupon_id );
		}
	}

	/**
	 * Release this session's reservation ( coupon removed, cart changed, ... ).
	 *
	 * @param int $coupon_id Coupon ID.
	 */
	public function release_reservation( $coupon_id ) {
		$coupon_id = absint( $coupon_id );
		if ( ! $coupon_id ) {
			return;
		}
		$this->delete_own_reservations( $coupon_id, $this->get_session_token() );
	}

	/**
	 * Atomically convert the session's reservation into a confirmed use.
	 * If the session has no live reservation ( expired / never applied ), it must fit in the remaining capacity.
	 * Capacity = limit - confirmed uses - live reservations held by other sessions.
	 *
	 * @param int $coupon_id Coupon ID.
	 * @return bool True if a use was claimed.
	 */
	public function claim_usage( $coupon_id ) {
		global $wpdb;

		$coupon_id = absint( $coupon_id );
		if ( ! $coupon_id ) {
			return false;
		}

		$meta_key = 'wp_travel_coupon_uasge_count';
		$limit    = absint( $this->get_coupon_meta( $coupon_id, 'restriction', 'coupon_limit_number' ) );
		$token    = $this->get_session_token();

		if ( ! $this->lock_coupon( $coupon_id ) ) {
			return false; // Fail closed.
		}

		try {
			$used   = $this->read_used( $coupon_id );
			$others = $this->count_other_reservations( $coupon_id, $token );
			$this->delete_own_reservations( $coupon_id, $token );

			if ( $limit > 0 && ( $used + $others ) >= $limit ) {
				return false;
			}

			add_post_meta( $coupon_id, $meta_key, 0, true ); // No-op if the row exists.
			$updated = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_value = CAST( meta_value AS UNSIGNED ) + 1 WHERE post_id = %d AND meta_key = %s",
					$coupon_id,
					$meta_key
				)
			);
			wp_cache_delete( $coupon_id, 'post_meta' );
			return 1 === (int) $updated;
		} finally {
			$this->unlock_coupon( $coupon_id );
		}
	}

	/**
	 * Release a previously claimed use ( e.g. booking creation failed ).
	 *
	 * @param int $coupon_id Coupon ID.
	 */
	public function release_usage( $coupon_id ) {
		global $wpdb;

		$coupon_id = absint( $coupon_id );
		if ( ! $coupon_id ) {
			return;
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = CAST( meta_value AS UNSIGNED ) - 1 WHERE post_id = %d AND meta_key = %s AND CAST( meta_value AS UNSIGNED ) > 0",
				$coupon_id,
				'wp_travel_coupon_uasge_count'
			)
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		wp_cache_delete( $coupon_id, 'post_meta' );
	}

	/**
	 * Revalidate the cart coupon and claim one use. Call right before creating the booking.
	 *
	 * @param array $discounts Cart discounts array.
	 * @return true|WP_Error
	 */
	public function claim_for_checkout( $discounts ) {
		if ( empty( $discounts ) || empty( $discounts['coupon_id'] ) ) {
			return true; // No coupon in cart.
		}

		$valid = $this->validate_for_checkout( $discounts );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $this->claim_usage( $discounts['coupon_id'] ) ) {
			return new WP_Error( 'WP_TRAVEL_COUPON_LIMIT_EXCEED', __( 'This coupon has reached its usage limit and was removed from your cart.', 'wp-travel' ) );
		}

		return true;
	}
}