<?php 

add_action( 'woocommerce_email_after_order_table', 'wp_travel_booking_info_after_customer_order_table', 10, 4 );

function wp_travel_booking_info_after_customer_order_table( $order, $sent_to_admin, $plain_text, $email ) {
	
		$order_id = (int) $order->get_id();
        global $wt_cart;

		$items = $wt_cart->getItems();

        foreach ( $items as $item ) {

			$total_pax = '';
            foreach ( $item['trip'] as $data ) {
                $total_pax .= '(' . $data['custom_label'] . ' * ' . $data['pax'] . ')';
				
            }

            $trip_id     = $item['trip_id'];
            $trip_name   = get_the_title( $trip_id );
            $travel_date = $item['trip_start_date'];
			$travel_time = isset( $item['trip_time'] ) ? $item['trip_time'] : '';
			$pickup_location = isset( $item['pickup_location'] ) ? $item['pickup_location'] : '';
        }

        ob_start();
		if ( count( $items ) > 0 ) {
        ?>
	
        <h2 style="color: #7f54b3; font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;">
            <?php echo esc_html__( 'Trip Info', 'wp-travel' ); ?>
        </h2>

        <div style="margin-top: 20px; margin-bottom: 20px; padding: 12px; color: #636363; border: 1px solid #e5e5e5;">
            <p><strong><?php echo esc_html__( 'Trip Name: ', 'wp-travel' ); ?></strong> 
                <a href="<?php echo esc_url( get_permalink( $trip_id ) ); ?>" target="_blank">
                    <?php echo esc_html( $trip_name ); ?>
                </a>
            </p>
            <p><strong><?php echo esc_html__( 'Total Pax: ', 'wp-travel' ); ?></strong> <?php echo esc_html( $total_pax ); ?></p>
            <p><strong><?php echo esc_html__( 'Travel Date: ', 'wp-travel' ); ?></strong> <?php echo esc_html( $travel_date ); ?></p>

			<?php if($travel_time): ?>
				<p><strong><?php echo esc_html__( 'Travel Time: ', 'wp-travel' ); ?></strong> <?php echo esc_html( $travel_time ); ?></p>
			<?php endif; ?>

			<?php if($pickup_location): ?>
				<p><strong><?php echo esc_html__( 'Pickup Location: ', 'wp-travel' ); ?></strong> <?php echo esc_html( $pickup_location ); ?></p>
			<?php endif; ?>
        </div>

        <?php
		
        echo ob_get_clean();
    }
}

add_action('woocommerce_loaded' , function (){ 
	class My_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT implements WC_Object_Data_Store_Interface, WC_Product_Data_Store_Interface {


		/**
		 * Method to read a product from the database.
		 * @param WC_Product
		 */
		public function read( &$product ) {
			$product->set_defaults();
	
			if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || 'product' !== $post_object->post_type ) {
				//throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
			}
	
			$id = $product->get_id();
	
			$product->set_props( array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'status'            => $post_object->post_status,
				'description'       => $post_object->post_content,
				'short_description' => $post_object->post_excerpt,
				'parent_id'         => $post_object->post_parent,
				'menu_order'        => $post_object->menu_order,
				'reviews_allowed'   => 'open' === $post_object->comment_status,
			) );
	
			$this->read_attributes( $product );
			$this->read_downloads( $product );
			$this->read_visibility( $product );
			$this->read_product_data( $product );
			$this->read_extra_data( $product );
			$product->set_object_read( true );
		}
	
	
	}
	
	add_filter( 'woocommerce_data_stores', 'my_woocommerce_data_stores' );
	
	function my_woocommerce_data_stores( $stores ) {
	
		$stores['product'] = 'MY_Product_Data_Store_CPT';
	
		return $stores;
	}
	
	add_filter('woocommerce_product_get_price', 'my_woocommerce_product_get_price', 10, 2 );
	function my_woocommerce_product_get_price( $price, $product ) {
		global $wt_cart;
		$extras_price_total = 0;

		

		if ( get_post_type( $product->get_id() ) === 'itineraries' ) {
			if( isset( array_values($wt_cart->getItems())[0]['trip_extras'] ) && !empty( array_values($wt_cart->getItems())[0]['trip_extras'] ) ){
				foreach ( array_values($wt_cart->getItems())[0]['trip_extras']['id'] as $k => $extra_id ) :

					$trip_extras_data = get_post_meta( $extra_id, 'wp_travel_tour_extras_metas', true );

					$price      = isset( $trip_extras_data['extras_item_price'] ) && ! empty( $trip_extras_data['extras_item_price'] ) ? $trip_extras_data['extras_item_price'] : false;
					$sale_price = isset( $trip_extras_data['extras_item_sale_price'] ) && ! empty( $trip_extras_data['extras_item_sale_price'] ) ? $trip_extras_data['extras_item_sale_price'] : false;

					if ( $sale_price ) {
						$price = $sale_price;
					}
					$price = WpTravel_Helpers_Trip_Pricing_Categories::get_converted_price( $price );
					$qty   = isset( array_values($wt_cart->getItems())[0]['trip_extras']['qty'][ $k ] ) && array_values($wt_cart->getItems())[0]['trip_extras']['qty'][ $k ] ? array_values($wt_cart->getItems())[0]['trip_extras']['qty'][ $k ] : 1;

					$total = $price * $qty;

					$extras_price_total = $extras_price_total + ( $price * $qty );

				endforeach;
			}
			## Price calculation ##
			if( isset(  array_values($wt_cart->getItems())[0]  ) ){
				$price = (double)array_values($wt_cart->getItems())[0]['trip_price'] + $extras_price_total;
				
			}
			
			
		}

		if ( isset( $GLOBALS['WOOCS'] ) && is_object( $GLOBALS['WOOCS'] ) ) {
			global $WOOCS;

			$base_currency = $WOOCS->default_currency;
        	$current_currency = $WOOCS->current_currency;


			if( array_values($wt_cart->getItems())[0]['used_currency'] !== $base_currency ){
				$price = (double)array_values($wt_cart->getItems())[0]['trip_price'];
				$price = $WOOCS->convert_from_to_currency( $price, array_values($wt_cart->getItems())[0]['used_currency'], $base_currency );
				$price = $price + $extras_price_total;
			}
		}

		return $price;
	}

} );


add_filter('woocommerce_cart_item_removed_message', 'disable_specific_removed_notice', 10, 2);
function disable_specific_removed_notice($message, $cart_item_key) {
    // Return an empty string to suppress the notice
    return '';
}

if( apply_filters( 'wp_travel_woo_enable_onapage', false ) == true ){
	add_filter( 'body_class', 'wp_travel_add_class_woo_onepage' );
	function wp_travel_add_class_woo_onepage( $classes ) {
		if(is_page((int)wptravel_get_settings()['thank_you_page_id'])){
			$classes[] = 'woo-onpage-enable';
		}
		
		return $classes;
	}
}

add_action(
    'woocommerce_admin_order_data_after_order_details',
    'wp_travel_show_booking_link_below_order_details'
);

function wp_travel_show_booking_link_below_order_details( $order ) {
    if ( ! $order ) {
        return;
    }

    // Order ID
    $order_id = $order->get_id();

    // Booking ID = Order ID + 1
    $booking_id = $order_id + 1;

    // Booking edit link
    $booking_url = admin_url(
        'post.php?post=' . $booking_id . '&action=edit'
    );

    echo '<p class="form-field form-field-wide">';

    echo '<span  style="
        margin-top:12px;
    "><strong>Booking Link:</strong> ';
    echo '<a href="' . esc_url( $booking_url ) . '">';
    echo 'View Booking #' . esc_html( $booking_id );
    echo '</a></span>';

    echo '</p>';
}