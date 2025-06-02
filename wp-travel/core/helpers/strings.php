<?php
/**
 * Helpers cache.
 *
 * @package WP_Travel
 */

defined( 'ABSPATH' ) || exit;
/**
 * WpTravel_Helpers_Strings class.
 *
 * @since 4.6.4
 */
class WpTravel_Helpers_Strings {
 // @phpcs:ignore

	/**
	 * Get all strings used in WP Travel.
	 *
	 * @since 4.6.4
	 *
	 * @return array
	 */
	public static function get() {

		$price_per = array(
			array(
				'label' => 'Person',
				'value' => 'person',
			),
			array(
				'label' => 'Group',
				'value' => 'group',
			)
		);


		$localized_strings = array(
			'trip_extras_content_limit' => apply_filters( 'wp_travel_trip_extras_content_limit', 250 ),
			'activities'                => 'Activities',
			'add_date'                  => 'Please add date.',
			'alert'                     => self::alert_strings(),
			'book_n_pay'                => 'Book and Pay',
			'book_now'                  => 'Book Now',
			'booking_tab_content_label' => apply_filters( 'wp_travel_booking_section_main_title', 'Select Date and Pricing Options for this trip in the Trip Options setting.' ),
			'bookings'                  => self::booking_strings(),
			'category'                  => 'Category',
			'close'                     => 'Close',
			'confirm'                   => 'Are you sure you want to remove?',
			'custom_min_payout'         => 'Custom Min. Payout %',
			'custom_partial_payout'     => 'Custom Partial Payout',
			'custom_trip_title'         => 'Custom Trip Title',
			'dates'                     => 'Dates',
			'default_pax'               => 'Default Pax',
			'display'                   => 'Display',
			'empty_results'             => self::empty_results_strings(),
			'enable_sale'               => 'Enable Sale',
			'enter_location'            => 'Enter Location',
			'fact'                      => 'Fact',
			'featured_book_now'         => 'Book Now', // Book Now at the featured section.
			'featured_trip_enquiry'     => 'Trip Enquiry', // Trip Enquiry at the featured section.
			'featured_trip_video'		=> 'Featured Video',	// Featured Video Heading at Trip Options
			'filter_by'                 => 'Filter By',
			'fixed_departure'           => 'Fixed Departure',
			'from'                      => 'From',
			'gallery_images'     		=> 'Images',	// Images Heading at Trip Options
			'global_partial_payout'     => 'Global Partial Payout',
			'global_trip_enquiry'       => 'Global Trip Enquiry Option',
			'global_trip_title'         => 'Global Trip Title',
			'group'                     => 'Group',
			'group_size'                => 'Group Size',
			'keyword'                   => 'Keyword',
			'latitude'                  => 'Latitude',
			'loading'                   => 'Loading..',
			'load_more'                 => 'Load more..',
			'location'                  => 'Location',
			'locations'                 => 'Locations',
			'longitude'                 => 'Longitude',
			'max_pax'                   => 'Max Pax.',
			'min_pax'                   => 'Min Pax.',
			'minimum_payout'            => 'Minimum Payout',
			'next'                      => 'Next',
			'notice_button_text'        => array( 'get_pro' => 'Get WP Travel Pro' ),
			'previous'                  => 'Previous',
			'prices'                    => 'Prices',
			'price_category'            => 'Price Category',
			'price_per'                 => 'Price Per',
			'person'                    => 'Person',
			'price'                     => 'Price',
			'price_range'               => 'Price Range',
			'pricing_name'              => 'Pricing Name',
			'highest_price'             => 'Show Highest Price',
			'highest_price_description' => 'This option will display the highest price.',
			'enable_pax_individual'     => 'Enable Pax Individually',
			'enable_pax_individual_description' => 'This option will enable pax limit for individual pricing.',
			'reviews'                   => 'Reviews',
			'sale_price'                => 'Sale Price',
			'search'                    => 'Search',
			'search_placeholder'        => 'Ex: Trekking',
			'select'                    => 'Select',
			'save'                      => 'Save',
			'off'                       => 'Off',
			'save_settings'             => 'Save Settings',
			'show'                      => 'Show',
			'system_information'        => 'System Information',
			'view_system_information'   => 'View system information',
			'general_setting'           => 'General Settings',
			'to'                        => 'To',
			'video_url'                 => 'Video URL',
			'trip_code'                 => 'Trip code',
			'trip_date'                 => 'Trip date',
			'trip_duration'             => 'Trip Duration',
			'trip_enquiry'              => 'Trip Enquiry',
			'enquiry'					=> apply_filters( 'wp_travel_trip_enquiry_label', 'Enquiry' ),
			'trip_name'                 => 'Trip Name',
			'trip_type'                 => 'Trip Type',
			'unit'                      => 'Unit',
			'use_global_payout'         => 'Use Global Payout',
			'use_global_tabs_layout'    => 'Use Global Tabs Layout',
			//for duration select option translate 
			'duration_select_label'		=> array(
				'hour'			=> 'Hour',
				'day'			=> 'Day',
				'night'			=> 'Night',
				'day_night'		=> 'Day and Night',
				'day_hour'		=> 'Day and Hour',
				'hour_minute'	=> 'Hour and Minute',
			),
			// Admin related data.
			'admin_tabs'                => self::admin_tabs_strings(),
			'notices'                   => self::admin_notices(),
			'messages'                  => array(
				'add_fact'        => 'Please add new fact here.',
				'add_new_fact'    => 'Please add fact from the settings',  // add new fact in settings.
				'add_new_faq'     => 'Please add new FAQ here.',  // add new fact in settings.
				'no_gallery'      => 'There are no gallery images.',
				'pricing_message' => 'Before making any changes in date, please make sure pricing is saved.',
				'save_changes'    => '* Please save the changes',
				'total_payout'    => 'Error: Total payout percent is not equals to 100%. Please update the trip once else global partial percent will be used as default.',
				'trip_saved'      => 'Trip Saved!',
				'upload_desc'     => 'Drop files here to upload.',
			),
			'update'                    => 'Update',
			'upload'                    => 'Upload',
			'media_library'             => 'Media Library',
			'save_changes'              => 'Save Changes',
			'add'                       => '+ Add',
			'edit'                      => 'Edit',
			'remove'                    => '-Remove',
			'add_date'                  => '+ Add Date',
			'remove_date'               => '-Remove Date',
			'add_category'              => '+ Add Category',
			'remove_category'           => '-Remove Category',
			'add_extras'                => '+ Add Extras',
			'remove_extras'             => '-Remove Extras',
			'add_fact'                  => '+ Add Fact',
			'remove_fact'               => '-Remove Fact',
			'add_faq'                   => '+ Add Faq',
			'remove_faq'                => '-Remove Faq',
			'add_price'                 => '+ Add Price',
			'remove_price'              => '-Remove Price',
			'add_itinerary'             => '+ Add Itinerary',
			'remove_itinerary'          => '-Remove Itinerary',
			'date_label'                => 'Date Label',
			'select_pricing'            => 'Select pricing options',
			'select_all'                => 'Select All',
			'select_type'               => 'Select Type',
			'start_date'                => 'Start Date',
			'end_date'                  => 'End Date',
			'date_time'                 => 'Date & time',
			'enable_fixed_departure'    => 'Enable Fixed Departure',
			'nights'                    => 'Night(s)',
			'days'                      => 'Day(s)',
			'hour'                     	=> 'Hour(s)',
			'booking_start_date_info'   => apply_filters( 'booking_start_date_info', 'Booking will start from ' ),
			'booking_offset'            => apply_filters( 'wptravel_booking_offset', 0 ),
			'exclude_date'            	=> apply_filters( 'wptravel_exclude_booking_dates', array() ),
			'current_year'            	=> gmdate("Y"),
			'booking_start_date_label'  => 'Booking Start',
			'duration_start_date'       => 'Duration Start Date',
			'duration_end_date'         => 'Duration End Date',
			'minutes'                   => 'Minute(s)',
			'value'                     => 'Value',
			'faq_questions'             => 'FAQ Questions ?',
			'enter_question'            => 'Enter your question',
			'faq_answer'                => 'Your Answer',
			'trip_includes'             => 'Trip Includes',
			'trip_excludes'             => 'Trip Excludes',

			'itinerary'                 => 'Itinerary',
			'day_x'                     => 'Day X',
			'your_plan'                 => 'Your Plan',
			'trip_outline'              => 'Trip Outline',
			'overview'                  => 'Overview',
			'itinerary_label'           => 'Itinerary Label',
			'itinerary_title'           => 'Itinerary Title',
			'itinerary_date'            => 'Itinerary Date',
			'itinerary_time'            => 'Itinerary Time',
			'hours'                     => 'Hours',
			'minute'                    => 'Minute',
			'description'               => 'Description',
			'map'                       => 'Map',

			'help_text'                 => array(
				'date_pricing'       => 'Type Pricing option and enter',
				'enable_location'    => 'Enable/Disable latitude-longitude option',
				'use_global_payout'  => 'Note: In case of multiple cart items checkout, global payout will be used.',
				'show_highest_price' => 'This option will display the highest price..',
				'show_highest_price' => 'This option will display the highest price..',
			),
			'full_name'                 => 'Full Name',
			'enter_your_name'           => 'Enter your name',
			'email'                     => 'Email',
			'enter_your_email'          => 'Enter your email',
			'enquiry_message'           => apply_filters( 'wp_travel_enquiry_message_label', 'Enquiry Message' ),
			'enter_your_enquiry'        => 'Enter your enquiry...',
			'arrival_departure'			=> apply_filters( 'wp_travel_trip_duration_arrival_time', false ),
			'arrival_time'				=> apply_filters( 'wp_travel_arrival_time', 'Arrival Time' ),
			'departure_time'			=> apply_filters( 'wp_travel_departure_time', 'Departure Time' ),
			'conditional_payment_text'	=> 'Using the Conditional payment module, you can apply for conditional payment on the checkout page according to the billing address or the trip locations.',
			'single_archive'			=> self::wp_travel_single_archive_strings(),
			'set_cart_error'			=> 'You have already applied a coupon.',
			'set_coupon_empty'			=> 'Please enter your coupon code',
			'set_invalid_coupon_error'	=> 'Invalid Coupon code. Please re-enter your coupon code',
			'set_coupon_apply'			=> 'Coupon applied.',
			'set_enter_coupon_message'	=> 'Enter you coupon code',
			'set_coupon_btn'			=> 'Apply Coupon',
			'privacy_label'				=> 'Privacy Policy',
			'strip_card'				=> 'Stripe Card',
			'set_ideal_bank'			=> 'iDEAL Bank',
			'payupay'					=> 'Pay with PayU',
			'payupaylatam'					=> 'Pay with PayU Latam',
			'payupayrazorpay'					=> 'Pay with Razorpay',
			'set_book_now_btn'			=> 'Book Now',
			'set_cart_updated'			=> 'Cart updated successfully.',
			'set_cart_updated_error'	=> "Cart failed to update due to server error.",
			'set_cart_updated_server_responce' => "Cart failed to update due to server response error.",
			'set_cart_server_error'		=> 'Cart failed to update due to some server error.',
			'set_close_cart'			=> 'Close Cart',
			'set_view_cart'				=> 'View Cart',
			'set_updated_cart_btn'		=> 'Update Cart',
			'set_cart_total_price'		=> 'Trip Price' ,
			'set_cart_discount'			=> 'Discount',
			'set_cart_tax'				=> 'Tax' ,
			'set_payment_price'			=> 'Total Trip Price',
			'set_cart_partial_payment'	=>  'Partial Payment Price',
			'set_require_message'		=> ' is required',
			'set_require_empty'			=> 'Required field is empty',
			'set_go_back'				=> 'Go Back' ,
			'set_next_btn'				=> 'Next',
			'set_added_cart'			=> 'has been added to cart.',
			'set_gateway_select'		=> 'Plese select you payment gateway',
			'set_book_now'				=> "Book Now",
			'set_invalid_email'			=> 'Invalid Email',
			'set_load_traveler'			=> "Lead Traveler",
			'set_traveler'				=> 'Traveler ',
			'set_time_out'				=> '[X] Request Timeout!',
			'set_traveler_details'		=> 'Traveler Details',
			'set_booking_details'		=> 'Billing Details',
			'set_booking_with'			=> 'Booking / Payments',
			'set_booking_only'			=>  'Booking',
			'set_bank_detail'			=> 'Bank Details',
			'set_account_name'			=> 'Account Name',
			'set_account_number'		=> 'Account Number',
			'set_bank_name'				=> 'Bank Name',
			'set_sort_code'				=> 'Sort Code',
			'set_ibam'					=> 'IBAN',
			'set_swift'					=> 'Swift',
			'set_routing_number'		=> 'Routing Number',
			'set_add_to_cart'			=> 'Add to Cart',
			'trip_price_per'			=> apply_filters( 'wp_travel_trip_price_per', $price_per )
		);

		$localized_strings['price_per_labels'] = array(
			'group'  => $localized_strings['group'],
			'person' => self::booking_strings()['person'],
		);
		
		$localized_strings['reserved_booking_dates_all_trips'] = apply_filters( 'wp_travel_enable_booking_reserve_date_all_trips', true );

		$localized_strings['add_to_cart_notice'] = apply_filters( 'wp_travel_add_to_cart_notice_delay_time', 3000 );


		if( apply_filters( 'wp_travel_enable_booking_reserve_date', false ) == true && class_exists( 'WP_Travel_Pro' ) ){
			$localized_strings['reserved_booking_dates'] = get_option('wp_travel_reserve_date');
		}else{
			$localized_strings['reserved_booking_dates'] = array();
		}
	
		return apply_filters( 'wp_travel_strings', $localized_strings ); // @phpcs:ignore

	}

	/**
	 * Get all booking related strings.
	 *
	 * @since 4.6.4
	 *
	 * @return array
	 */
	public static function booking_strings() {
		return array(
			'pricing_name'                  => 'Pricing Name',
			'start_date'                    => 'Start',
			'end_date'                      => 'End',
			'action'                        => 'Action',
			'recurring'                     => 'Recurring:',
			'group_size'                    => 'Group (Min-Max)',
			'seats_left'                    => 'Seats left',
			'min_pax'                       => 'Min',
			'max_pax'                       => 'Max',
			'pax'                           => 'Pax',
			'price_tax'                     => 'Tax',
			'select_pax'                    => 'Select Pax',
			'price'                         => 'Price',
			'arrival_date'                  => 'Arrival date',
			'departure_date'                => 'Departure date',
			'sold_out'                      => 'Sold Out',
			'select'                        => 'Select',
			'close'                         => 'Close',
			'book_now'                      => 'Book Now',
			'combined_pricing'              => 'Pricing', // Added for combined pricing label for categorized pricing @since 3.0.0.
			'pricing_not_available'         => 'The pricing is not available on the selected Date. Please choose another date or pricing.',
			'max_pax_exceeded'              => 'Max. Exceeded.',
			'date_select'                   => apply_filters( 'wp_travel_select_date_label', 'Select a Date' ),
			'date_select_to_view_options'   => apply_filters( 'wp_travel_select_date_notice_label', 'Select a Date to view available pricings and other options.' ),
			'booking_tab_clear_all'         => 'Clear All',
			'min_booking_amount'        => 'Total:',
			'booking_tab_booking_btn_label' => 'Book Now',
			'booking_tab_pax_selector'      => 'Pax Selector',
			'group_discount_tooltip'        => 'Group Discounts',
			'view_group_discount'           => 'Discounts',
			'pricings_list_label'           => 'Pricings',
			'pricings_not_found'           => 'Sorry!! Pricing not found for selected date. Please select another date.',
			'person'                        =>  'Person',
			'departure_custom_label'        =>  'Bookings',
			'departure_custom_links'		=> apply_filters( 'wp_travel_enable_custom_links_departure', false ),
			'departure_custom_links_label'  => 'Book Now',
			'date'                          => 'Date',
			'trip_extras'                   => 'Trip Extras',
			'trip_extras_list_label'        => 'Trip Extras',
			'trip_extras_link_label'        => 'Learn More',
			'available_trip_times'          => 'Available times',
			'booking_option'                => 'Booking Options',
			'booking_with_payment'          => 'Booking with payment',
			'booking_only'                  => 'Booking only',
			'payment_price_detail'			=> [
				'payment_detail'		=> 'Payment Details',
				'date'					=> 'Date',
				'payment_id'			=> 'Payment ID / Txn ID',
				'payment_methode'		=> 'Payment Method',
				'payment_amount'		=> 'Payment Amount',
			]
		);
	}

	/**
	 * Get all tabs related strings.
	 *
	 * @since 4.6.4
	 *
	 * @return array
	 */
	public static function admin_tabs_strings() {
		return array(
			'itinerary'         => 'Itinerary',
			'price_n_dates'     => 'Prices & Dates',
			'includes_excludes' => 'Includes/Excludes',
			'facts'             => 'Facts',
			'gallery'           => 'Gallery',
			'locations'         => 'Locations',
			'checkout'          => 'Checkout',
			'inventory_options' => 'Inventory Options',
			'faqs'              => 'FAQs',
			'downloads'         => 'Downloads',
			'misc'              => 'Misc',
			'tabs'              => 'Tabs',
			'guides'            => 'Guides',
			'checkout_field_editor'            => 'Checkout Field Editor',
		);
	}

	/**
	 * Get all alert strings.
	 *
	 * @since 4.6.4
	 *
	 * @return array
	 */
	public static function alert_strings() {
		return array(
			'atleast_min_pax_alert' => 'Please select at least minimum pax.',
			'both_pax_alert'        => 'Pax should be between {min_pax} and {max_pax}.',
			'max_pax_alert'         => 'Pax should be lower than or equal to {max_pax}.',
			'min_pax_alert'         => 'Pax should be greater than or equal to {min_pax}.',
			'remove_category'       => 'Are you sure to delete category?', // admin alert.
			'remove_date'           => 'Are you sure to delete this date?', // admin alert.
			'remove_fact'           => 'Are you sure to delete remove fact?', // admin alert.
			'remove_faq'            => 'Are you sure to delete FAQ?', // admin alert.
			'remove_gallery'        => 'Are you sure, want to remove the image from Gallery?', // admin alert.
			'remove_itinerary'      => 'Are you sure to delete this itinerary?', // admin alert.
			'remove_price'          => 'Are you sure to delete this price?', // admin alert.
			'required_pax_alert'    => 'Pax is required.',
		);
	}

	/**
	 * Get all empty results strings.
	 *
	 * @since 4.6.4
	 *
	 * @return array
	 */
	public static function empty_results_strings() {
		return array(
			'activities' => 'No Activities',
			'add_extras' => 'Please add extras first',
			'category'   => 'No category found.',
			'dates'      => 'No dates found',
			'extras'     => 'No extras found.',
			'group_size' => 'No size limit',
			'itinerary'  => 'No Itineraries found.',
			'pricing'    => 'No pricing found.',
			'trip_type'  => 'No Trip Type',
		);
	}

	/**
	 * Get all admin notices strings.
	 *
	 * @since 4.6.4
	 *
	 * @return array
	 */
	public static function admin_notices() {
		return array(
			'checkout_option'    => array(
				'title'       => 'Need to add your checkout options?',
				'description' => 'By upgrading to Pro, you can add your checkout options for all of your trips !',
			),
			'inventory_option'   => array(
				'title'       => 'Need to add your inventory options?',
				'description' => 'By upgrading to Pro, you can add your inventory options in all of your trips !',
			),
			'downloads_option'   => array(
				'title'       => 'Need to add your downloads?',
				'description' => 'By upgrading to Pro, you can add your downloads in all of your trips !',
			),
			'guide_option'   => array(
				'title'       => 'Need to add trip guides?',
				'description' => 'By upgrading to Pro, you can add trip guides in all of your trips !',
			),
			'need_more_option'   => array(
				'title'       => 'Need More Options ?',
				'description' => 'By upgrading to Pro, you can get additional trip specific features like Inventory Options, Custom Sold out action/message and Group size limits. !',
			),
			'need_extras_option' => array(
				'title'       => 'Need advance Trip Extras options?',
				'description' => '',
			),
			'global_faq_option'  => array(
				'title'       => 'Tired of updating repitative FAQs ?',
				'description' => 'By upgrading to Pro, you can create and use Global FAQs in all of your trips !',
			),
			'featured_trip_video_option'   => array(
				'description' => 'Insert the video link to embed.',
			),
			'trip_code_option'   => array(
				'description' => 'Need Custom Trip Code? Check',
			),
			'map_option'         => array(
				'title'       => 'Need alternative maps ?',
				'description' => 'If you need alternative to current map then you can get free or pro maps for WP Travel.',
			),
			'map_key_option'     => array(
				'description' => "You can add 'Google Map API Key' in the %1\$ssettings%2\$s to use additional features.",
			),
			'global_tab_option'  => array(
				'title'       => 'Need Additional Tabs ?',
				'description' => 'By upgrading to Pro, you can get trip specific custom tabs addition options with customized content and sorting !',
			),
		);
	}
	/**
	 * Wp trave trip single archive page strings
	 * @since 6.9
	 */
	public static function wp_travel_single_archive_strings() {
		$strings = [
			'offer'		=> 'Offer',
			'view_gallery'					=> 'View Gallery',
			'keywords'						=> 'Keywords',

		];

		return $strings;
	}
}