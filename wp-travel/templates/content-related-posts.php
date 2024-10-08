<?php
/**
 * Related Posts Template.
 *
 * @package WP_Travel
 */
$post_id                = $args;
$settings               = wptravel_get_settings();
$hide_related_itinerary = ( isset( $settings['hide_related_itinerary'] ) && '' !== $settings['hide_related_itinerary'] ) ? $settings['hide_related_itinerary'] : 'no';
$layout_version         = wptravel_layout_version();
if ( 'yes' === $hide_related_itinerary ) {
	return;
}
	$currency_code   = ( isset( $settings['currency'] ) ) ? $settings['currency'] : '';
	$currency_symbol = wptravel_get_currency_symbol( $currency_code );

	// For use in the loop, list 5 post titles related to first tag on current post.
	$terms = wp_get_object_terms( $post_id, 'itinerary_types' );

	$no_related_post_message = '<p class="wp-travel-no-detail-found-msg">' . esc_html__( 'Related trip not found.', 'wp-travel' ) . '</p>';
	$wrapper_class           = wptravel_get_theme_wrapper_class();
?>
	<div class="wp-travel-related-posts wp-travel-container-wrap <?php echo esc_attr( $wrapper_class ); ?>">
		<h2><?php echo esc_html( apply_filters( 'wp_travel_related_post_title', __( 'Related Trips', 'wp-travel' ) ) ); ?></h2>
		<div class="wp-travel-itinerary-items"> 
			<?php
				if ( ! empty( $terms ) ) {
					$term_ids    = wp_list_pluck( $terms, 'term_id' );
					$col_per_row = apply_filters( 'wp_travel_related_itineraries_col_per_row', '3' );
					$args        = array(
						'post_type'      => WP_TRAVEL_POST_TYPE,
						'post__not_in'   => array( $post_id ),
						'posts_per_page' => $col_per_row,
						'tax_query'      => array(
							array(
								'taxonomy' => 'itinerary_types',
								'field'    => 'id',
								'terms'    => $term_ids,
							),
						),
					);
					$query       = new WP_Query( $args );
					if ( $query->have_posts() ) {
						?>
						<ul class="wp-travel-itinerary-list">
							<?php
							while ( $query->have_posts() ) :
								$query->the_post();
									wptravel_get_template_part( 'shortcode/itinerary', 'item' );
							endwhile; ?>
						</ul>
						<?php
					} else {
						wptravel_get_template_part( 'shortcode/itinerary', 'item-none' );
					}
					wp_reset_query();
				} else {
					wptravel_get_template_part( 'shortcode/itinerary', 'item-none' );
				}
				?>
	 </div>
</div>
