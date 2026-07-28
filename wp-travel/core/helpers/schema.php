<?php
/**
 * Helpers class to do Add Schema for WP Travel Pages.
 *
 * @package WP_Travel
 */

/**
 * Schema Helper.
 */
class WpTravel_Helpers_Schema {

	/**
	 * This variable include trip data which is used in schema.
	 *
	 * @var array $trip
	 */
	public static $trip;

	/**
	 * Initialize schema in WP Travel.
	 *
	 * @since 5.0.0
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'run' ) );
	}

	/**
	 * Display all available schema for WP Travel.
	 *
	 * @since 5.0.0
	 */
	public static function run() {
		$use_schema = apply_filters( 'wptravel_use_schema', true );
		if ( ! $use_schema ) {
			return;
		}
		if ( WP_Travel::is_page( 'single' ) ) {
			global $post;
			$trip_id   = $post->ID;
			$trip_data = WpTravel_Helpers_Trips::get_trip( $trip_id );
			$trip      = array();
			if ( is_array( $trip_data ) && ! is_wp_error( $trip_data ) && isset( $trip_data['code'] ) && 'WP_TRAVEL_TRIP_INFO' === $trip_data['code'] ) {
				$trip = $trip_data['trip'];
			}
			self::$trip = $trip;
		}
		self::get_trip_schema();

		if ( WP_Travel::is_page( 'single' ) ) {
			self::get_faq_schema( $trip_id );
		}
	}

	public static function get_trip_schema() {

		if ( ! self::$trip ) {
			return;
		}

		$trip    = self::$trip;
		$trip_id = $trip['id'];

		/**
		 * Base Schema
		 */
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'TouristTrip',

			'name' => isset( $trip['title'] )
				? ucwords( $trip['title'] )
				: '',

			'description' => isset( $trip['trip_overview'] )
				? wp_strip_all_tags( $trip['trip_overview'] )
				: '',

			'url' => isset( $trip['url'] )
				? $trip['url']
				: '',

			'identifier' => array(
				'@type' => 'PropertyValue',
				'name'  => 'Trip ID',
				'value' => $trip_id,
			),
		);

		/**
		 * Tourist Types
		 */
		$terms = wp_get_post_terms( $trip_id, 'itinerary_types' );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

			$schema['touristType'] = array_map(
				function ( $term ) {
					return $term->name;
				},
				$terms
			);

		} else {

			$schema['touristType'] = array( 'General Tourism' );

		}

		/**
		 * Itinerary
		 */
		if ( ! empty( $trip['itineraries'] ) && is_array( $trip['itineraries'] ) ) {

			$schema['itinerary'] = array(
				'@type'           => 'ItemList',
				'numberOfItems'   => count( $trip['itineraries'] ),
				'itemListElement' => array(),
			);

			$i = 1;

			foreach ( $trip['itineraries'] as $itinerary ) {

				$schema['itinerary']['itemListElement'][] = array(
					'@type'    => 'ListItem',
					'position' => $i++,
					'item'     => array(
						'@type'       => 'TouristAttraction',
						'name'        => trim(
							( $itinerary['label'] ?? '' ) . ' - ' . ( $itinerary['title'] ?? '' )
						),
						'description' => wp_strip_all_tags( $itinerary['desc'] ?? '' ),
					),
				);
			}
		}

		/**
		 * Featured Image
		 */
		$image_id = get_post_thumbnail_id( $trip_id );

		if ( $image_id ) {

			$meta = wp_get_attachment_metadata( $image_id );

			$schema['image'] = array_filter(
				array(
					'@type'  => 'ImageObject',
					'url'    => wp_get_attachment_image_url( $image_id, 'full' ),
					'width'  => isset( $meta['width'] ) ? (int) $meta['width'] : null,
					'height' => isset( $meta['height'] ) ? (int) $meta['height'] : null,
				)
			);
		}

		/**
		 * Offer (Price)
		 */
		$args = array(
			'trip_id' => $trip_id,
		);

		$args_regular = $args;
		$args_regular['is_regular_price'] = true;

		$trip_price    = WP_Travel_Helpers_Pricings::get_price( $args );
		$regular_price = WP_Travel_Helpers_Pricings::get_price( $args_regular );

		$enable_sale = WP_Travel_Helpers_Trips::is_sale_enabled(
			array(
				'trip_id'                => $trip_id,
				'from_price_sale_enable' => true,
			)
		);

		$settings = wptravel_get_settings();
		$currency = isset( $settings['currency'] ) ? $settings['currency'] : 'USD';

		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $enable_sale ? $trip_price : $regular_price,
			'priceCurrency' => $currency,
			'availability'  => 'https://schema.org/InStock',
			'eligibleQuantity' => array(
				'@type'    => 'QuantitativeValue',
				'minValue' => get_post_meta( $trip_id, 'wp_travel_group_min_size', true ),
				'maxValue' => get_post_meta( $trip_id, 'wp_travel_group_size', true ),
			),
		);

		/**
		 * Filter
		 */
		$schema = apply_filters(
			'wptravel_trip_schema',
			$schema,
			$trip_id,
			$trip
		);

		self::generate_schema( $schema );
	}

	/**
	 * Generate FAQ Schema.
	 *
	 * @param int $trip_id Trip ID.
	 */
	public static function get_faq_schema( $trip_id ) {

		$faq_data = get_post_meta( $trip_id, 'wptravel_trip_faqs', true );

		if ( empty( $faq_data ) || ! is_array( $faq_data ) ) {
			return;
		}

		$faq_items = array();

		foreach ( $faq_data as $faq ) {

			if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) {
				continue;
			}

			$faq_items[] = array(
				'@type' => 'Question',
				'name'  => wp_strip_all_tags( $faq['question'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $faq['answer'] ),
				),
			);
		}

		if ( empty( $faq_items ) ) {
			return;
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'FAQPage',
			'mainEntity' => $faq_items,
		);

		self::generate_schema( $schema );
	}

	/**
	 * Generate schema as per $schema array.
	 *
	 * @since 5.0.0
	 * @param array $schema Schema structure array.
	 * @return string
	 */
	public static function generate_schema( $schema = array() ) {
		if ( ! $schema ) {
			return;
		}

		$schema_structure = '';

		if ( $schema ) {
			$schema_structure .= "\n\n";
			$schema_structure .= '<!-- This schema is generated by WP Travel v' . WP_TRAVEL_VERSION . ' -->';
			$schema_structure .= "\n";
			$schema_structure .= '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>';
			$schema_structure .= "\n\n";
		}
		echo $schema_structure; // @phpcs:ignore
	}

}
WpTravel_Helpers_Schema::init();
