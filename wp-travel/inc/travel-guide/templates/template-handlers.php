<?php

if ( ! function_exists( 'wptravel_get_all_elementor_templates' ) ) {

	function wptravel_get_all_elementor_templates() {
		$templates = get_posts(array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => '_elementor_conditions',
					'compare' => 'EXISTS',
				),
			),
		));

		$all_conditions = [];

		foreach ($templates as $template) {
			$conditions = get_post_meta($template->ID, '_elementor_conditions', true);
			if (is_serialized($conditions)) {
				$conditions = unserialize($conditions);
			}

			if (is_array($conditions)) {
				$all_conditions = array_merge($all_conditions, $conditions);
			}
		}

		return $all_conditions;
	}

}

if ( ! function_exists( 'wp_travel_travel_guide_page_template' ) ) {

	/**
	 * Defining Guide Templates
	 *
	 *  @return template
	 */
	function wp_travel_travel_guide_page_template( $template ) {

		$guide_archive = false;
		$guide_single = false;
		$single_template = array();
		if( is_singular('travel-guide')){
			$single_template = get_post_meta( get_the_id(), '_wp_page_template' );
			add_filter( 'comments_template', 'wp_travel_travel_guide_review_template' );
		}

		if( wp_is_block_theme() ){
			
			foreach( get_block_templates() as $data ){
				if( $data->slug == 'archive-travel-guide' ){
					$guide_archive = true;
				}

				if( $data->slug == 'single-travel-guide' ){
					$guide_single = true;
				}
			}
		}
	
		if ( !$guide_single && is_singular( 'travel-guide' ) ) {

			if( class_exists('\ElementorPro\Plugin') && in_array('include/singular/travel-guide', wptravel_get_all_elementor_templates(), true) ){
				return $template;
			}else{
				if( file_exists( get_template_directory() . '/wp-travel/single-guide.php' ) ){
					return get_template_directory() . '/wp-travel/single-guide.php';
					
				}else{
					return load_template( dirname( __FILE__ ) . '/single-guide.php' );
				}
			}
			
		}
		

		if ( !$guide_archive && is_post_type_archive( 'travel-guide' ) ) {
			if( class_exists('\ElementorPro\Plugin') && in_array('include/archive/travel-guide_archive', wptravel_get_all_elementor_templates(), true) ){
				return $template;
			}else{
				if( file_exists( get_template_directory() . '/wp-travel/guide-archive.php' ) ){
					return get_template_directory() . '/wp-travel/guide-archive.php';
				}else{
					return load_template( dirname( __FILE__ ) . '/guide-archive.php' );
				}
			}
		}
		return $template;
	}

	add_filter( 'template_include', 'wp_travel_travel_guide_page_template' );

}


if ( ! function_exists( 'wp_travel_travel_guide_single_guide_header' ) ) {

	/**
	 * Defininig single guide header
	 *
	 * @return void
	 */
	function wp_travel_travel_guide_single_guide_header() {
		$migrate_travel_guide = get_option( 'wptravel_migrate_travel_guide', false );

		$is_pro_active = class_exists( 'WP_Travel_Pro' ) ? true : false;

		if( !$is_pro_active ){
		
			$migrate_travel_guide = true;
		}

		if ( $migrate_travel_guide ) {
			$guide_name = get_the_title();
			?>
				<div class="wptravel-travel-guide-heading wptravel-travel-guide-columns">
					<h1 class="wptravel-underline"> <?php echo esc_html__( 'Meet Our Guide ', 'wp-travel' ) . esc_html( $guide_name ); ?></h1>
				</div>
			<?php
		}else{
			$guide_data = get_user_by( 'login', get_the_title() )->data;
			?>
				<div class="wptravel-travel-guide-heading wptravel-travel-guide-columns">
					<h1 class="wptravel-underline"> <?php echo esc_html__( 'Meet Our Guide ', 'wp-travel' ) . esc_html( $guide_data->display_name ); ?></h1>
				</div>
			<?php
		}
	}

	add_action( 'wp_travel_travel_guide_single_guide_template', 'wp_travel_travel_guide_single_guide_header', 10 );
}


if ( ! function_exists( 'wp_travel_travel_guide_single_guide_featured_trip' ) ) {

	/**
	 * Defininig single guide featured trip
	 *
	 * @return void
	 */
	function wp_travel_travel_guide_single_guide_featured_trip() {
		$migrate_travel_guide = get_option( 'wptravel_migrate_travel_guide', false );

		$is_pro_active = class_exists( 'WP_Travel_Pro' ) ? true : false;

		if( !$is_pro_active ){
			$migrate_travel_guide = true;
		}

		if ( $migrate_travel_guide ) {
			$post_id    = get_the_ID();
			$guide_name = get_the_title( $post_id );
			$meta       = wptravel_guide_meta_keys();
			$trip_list  = get_post_meta( $post_id, $meta['trip_list'], true );
			?>
				<div class="wptravel-tourguide-all-trip" id="wptravel-tourguide-trips">
					<div class="wrapper-tg">
						<div class="wptravel-tourguide-trip-list">
							<h3><?php echo esc_html__( 'TOURS BY ', 'wp-travel' ) . esc_html( $guide_name ); ?></h3>
						</div>
						<div class="wp-travel-itinerary-items">
							<div class="wp-travel-itinerary-items wptravel-archive-wrapper  grid-view">
								<?php

								if ( empty( $trip_list ) ) {
									echo esc_html__( 'No Guide Found..', 'wp-travel' );
								} else {
									$args = array(
										'post_type' => 'itineraries',
										'post__in'  => $trip_list,
									);

									$query = new WP_Query( $args );

									if ( $query->have_posts() ) :
										while ( $query->have_posts() ) :
											$query->the_post();

											global $wp_travel_itinerary;

											$enable_sale = WP_Travel_Helpers_Trips::is_sale_enabled( array( 'trip_id' => get_the_id() ) );
											$group_size  = wptravel_get_group_size( get_the_id() );
											$start_date  = get_post_meta( get_the_id(), 'wp_travel_start_date', true );
											$end_date    = get_post_meta( get_the_id(), 'wp_travel_end_date', true );

											$args                             = $args_regular = array( 'trip_id' => get_the_id() ); // phpcs:ignore
											$args_regular['is_regular_price'] = true;
											$trip_price                       = WP_Travel_Helpers_Pricings::get_price( $args );
											$regular_price                    = WP_Travel_Helpers_Pricings::get_price( $args_regular );

											$locations     = get_the_terms( get_the_id(), 'travel_locations' );
											$location_name = '';
											$location_link = '';
											if ( $locations && is_array( $locations ) ) {
												$first_location = array_shift( $locations );
												$location_name  = $first_location->name;
												$location_link  = get_term_link( $first_location->term_id, 'travel_locations' );
											}
											?>
											<div class="view-box">
												<div class="view-image">
													<a href="<?php the_permalink(); ?>" class="image-thumb">
														<div class="image-overlay"></div>
													<?php echo wp_kses_post( wptravel_get_post_thumbnail( get_the_id() ) ); ?>
													</a>
													<div class="offer">
														<span>#<?php echo esc_html( $wp_travel_itinerary->get_trip_code() ); ?></span>
													</div>
												</div>

												<div class="view-content">
													<div class="left-content">
														<header>
														<?php do_action( 'wp_travel_before_archive_content_title', get_the_id() ); ?>
															<h2 class="entry-title">
																<a class="heading-link" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to: ', 'wp-travel' ) ) ); ?>">
																<?php the_title(); ?>
																</a>
															</h2>
														<?php do_action( 'wp_travel_after_archive_title', get_the_id() ); ?>
														</header>
														<div class="trip-icons">
														<?php wptravel_get_trip_duration( get_the_id() ); ?>
															<div class="trip-location">
																<i class="fas fa-map-marker-alt"></i>
																<span>
																<?php if ( $location_name ) : ?>
																		<a href="<?php echo esc_url( $location_link ); ?>" ><?php echo esc_html( $location_name ); ?></a>
																		<?php
																	else :
																		esc_html_e( 'N/A', 'wp-travel' );
																	endif;
																	?>
																</span>
															</div>
															<div class="group-size">
																<i class="fas fa-users"></i>
																<span><?php echo esc_html( wptravel_get_group_size( get_the_id() ) ); ?></span>
															</div>
														</div>
														<div class="trip-desc">
															<?php the_excerpt(); ?>
														</div>
													</div>
													<div class="right-content">
														<div class="footer-wrapper">
															<div class="trip-price">
																<?php wptravel_save_offer( get_the_id() ); ?>
																<?php if ( $trip_price > 0 ) : ?>
																	<span class="price-here">
																		<?php echo wptravel_get_formated_price_currency( $trip_price ); //phpcs:ignore ?>
																	</span>
																<?php endif; ?>
																<?php if ( $enable_sale ) : ?>
																	<del><?php echo wptravel_get_formated_price_currency( $regular_price, true ); //phpcs:ignore ?></del>
																<?php endif; ?>

															</div>
															<div class="trip-rating">
																<?php if ( wptravel_tab_show_in_menu( 'reviews' ) ) : ?>
																	<div class="wp-travel-average-review">
																		<?php wptravel_trip_rating( get_the_id() ); ?>
																		<?php $count = (int) wptravel_get_review_count(); ?>
																	</div>
																	<span class="wp-travel-review-text"> (<?php echo esc_html( $count ) . esc_html__( ' Reviews', 'wp-travel' ); ?>)</span>
																<?php endif; ?>
															</div>
														</div>

														<a class="wp-block-button__link explore-btn" href="<?php the_permalink(); ?>"><span><?php esc_html_e( 'Explore', 'wp-travel' ); ?></span></a>
													</div>
												</div>
											</div>
											<?php
											endwhile;
										endif;
									wp_reset_postdata();
								}
								?>
							
							</div>
						</div>

					</div>

				</div>
			<?php
		}else{
			$guide_data = get_user_by( 'login', get_the_title() )->data;
			?>
				<div class="wptravel-tourguide-all-trip" id="wptravel-tourguide-trips">
					<div class="wrapper-tg">
						<div class="wptravel-tourguide-trip-list">
							<h3><?php echo esc_html__( 'TOURS BY ', 'wp-travel' ) . esc_html( $guide_data->display_name ); ?></h3>
						</div>
						<div class="wp-travel-itinerary-items">
							<div class="wp-travel-itinerary-items wptravel-archive-wrapper  grid-view">
								<?php

								if ( empty( get_user_meta( $guide_data->ID, 'trip_list', true ) ) ) {
									echo esc_html__( 'No Guide Found..', 'wp-travel' );
								} else {
									$args = array(
										'post_type' => 'itineraries',
										'post__in'  => get_user_meta( $guide_data->ID, 'trip_list', true ),
									);

									$query = new WP_Query( $args );

									if ( $query->have_posts() ) :
										while ( $query->have_posts() ) :
											$query->the_post();

											global $wp_travel_itinerary;

											$enable_sale = WP_Travel_Helpers_Trips::is_sale_enabled( array( 'trip_id' => get_the_id() ) );
											$group_size  = wptravel_get_group_size( get_the_id() );
											$start_date  = get_post_meta( get_the_id(), 'wp_travel_start_date', true );
											$end_date    = get_post_meta( get_the_id(), 'wp_travel_end_date', true );

											$args                             = $args_regular = array( 'trip_id' => get_the_id() ); // phpcs:ignore
											$args_regular['is_regular_price'] = true;
											$trip_price                       = WP_Travel_Helpers_Pricings::get_price( $args );
											$regular_price                    = WP_Travel_Helpers_Pricings::get_price( $args_regular );

											$locations     = get_the_terms( get_the_id(), 'travel_locations' );
											$location_name = '';
											$location_link = '';
											if ( $locations && is_array( $locations ) ) {
												$first_location = array_shift( $locations );
												$location_name  = $first_location->name;
												$location_link  = get_term_link( $first_location->term_id, 'travel_locations' );
											}
											?>
											<div class="view-box">
												<div class="view-image">
													<a href="<?php the_permalink(); ?>" class="image-thumb">
														<div class="image-overlay"></div>
													<?php echo wp_kses_post( wptravel_get_post_thumbnail( get_the_id() ) ); ?>
													</a>
													<div class="offer">
														<span>#<?php echo esc_html( $wp_travel_itinerary->get_trip_code() ); ?></span>
													</div>
												</div>

												<div class="view-content">
													<div class="left-content">
														<header>
														<?php do_action( 'wp_travel_before_archive_content_title', get_the_id() ); ?>
															<h2 class="entry-title">
																<a class="heading-link" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to: ', 'wp-travel' ) ) ); ?>">
																<?php the_title(); ?>
																</a>
															</h2>
														<?php do_action( 'wp_travel_after_archive_title', get_the_id() ); ?>
														</header>
														<div class="trip-icons">
														<?php wptravel_get_trip_duration( get_the_id() ); ?>
															<div class="trip-location">
																<i class="fas fa-map-marker-alt"></i>
																<span>
																<?php if ( $location_name ) : ?>
																		<a href="<?php echo esc_url( $location_link ); ?>" ><?php echo esc_html( $location_name ); ?></a>
																		<?php
																	else :
																		esc_html_e( 'N/A', 'wp-travel' );
																	endif;
																	?>
																</span>
															</div>
															<div class="group-size">
																<i class="fas fa-users"></i>
																<span><?php echo esc_html( wptravel_get_group_size( get_the_id() ) ); ?></span>
															</div>
														</div>
														<div class="trip-desc">
															<?php the_excerpt(); ?>
														</div>
													</div>
													<div class="right-content">
														<div class="footer-wrapper">
															<div class="trip-price">
																<?php wptravel_save_offer( get_the_id() ); ?>
																<?php if ( $trip_price > 0 ) : ?>
																	<span class="price-here">
																		<?php echo wptravel_get_formated_price_currency( $trip_price ); //phpcs:ignore ?>
																	</span>
																<?php endif; ?>
																<?php if ( $enable_sale ) : ?>
																	<del><?php echo wptravel_get_formated_price_currency( $regular_price, true ); //phpcs:ignore ?></del>
																<?php endif; ?>

															</div>
															<div class="trip-rating">
																<?php if ( wptravel_tab_show_in_menu( 'reviews' ) ) : ?>
																	<div class="wp-travel-average-review">
																		<?php wptravel_trip_rating( get_the_id() ); ?>
																		<?php $count = (int) wptravel_get_review_count(); ?>
																	</div>
																	<span class="wp-travel-review-text"> (<?php echo esc_html( $count ) . esc_html__( ' Reviews', 'wp-travel' ); ?>)</span>
																<?php endif; ?>
															</div>
														</div>

														<a class="wp-block-button__link explore-btn" href="<?php the_permalink(); ?>"><span><?php esc_html_e( 'Explore', 'wp-travel' ); ?></span></a>
													</div>
												</div>
											</div>
											<?php
											endwhile;
										endif;
									wp_reset_postdata();
								}
								?>
							
							</div>
						</div>

					</div>

				</div>
			<?php
		}
	}

	add_action( 'wp_travel_travel_guide_single_guide_featured_trip', 'wp_travel_travel_guide_single_guide_featured_trip' );
}


if ( ! function_exists( 'wp_travel_travel_guide_single_guide_details' ) ) {

	 /**
	  * Defininig single guide details
	  *
	  * @return void
	  */

	function wp_travel_travel_guide_single_guide_details() {

		$migrate_travel_guide = get_option( 'wptravel_migrate_travel_guide', false );

		$is_pro_active = class_exists( 'WP_Travel_Pro' ) ? true : false;

		if( !$is_pro_active ){
			$migrate_travel_guide = true;
		}

		if ( $migrate_travel_guide ) {
			$post_id = get_the_ID();
			$meta    = wptravel_guide_meta_keys();

			$guide_name = get_the_title( $post_id );

			/*
			* Profile picture.
			* Prefer the attachment ID (current format); fall back to the
			* legacy stored URL for older/imported data.
			*/
			$guide_profile_picture = absint( get_post_meta( $post_id, $meta['profile_picture'], true ) );
			$guide_profile_img     = '';

			if ( $guide_profile_picture ) {
				$guide_profile_img = wp_get_attachment_image_url( $guide_profile_picture, 'full' );
			}

			if ( ! $guide_profile_img ) {
				$guide_profile_img = get_post_meta( $post_id, $meta['profile_picture_url'], true );
			}

			$guide_city        = get_post_meta( $post_id, $meta['city'], true );
			$guide_country     = get_post_meta( $post_id, $meta['country'], true );
			$guide_slogan      = get_post_meta( $post_id, $meta['user_slogan'], true );
			$guide_description = get_post_meta( $post_id, $meta['description'], true );
			$guide_biography   = get_post_meta( $post_id, $meta['biography'], true );
			$guide_language    = get_post_meta( $post_id, $meta['language'], true );
			$guide_ID          = get_post_meta( $post_id, $meta['guide_unique_id'], true );
			$guide_join_year   = get_post_meta( $post_id, $meta['join_year'], true );
			$guide_age         = get_post_meta( $post_id, $meta['age'], true );
			?>
			<div class="wptravel-travel-guide-row wptravel-travel-guide-columns">
				<!-- guide details -->
				<div class="wptravel-tourguide-name-details">
					<div class="wptravel-travel-guide-image">
						<img class="wptravel-travel-guide-img" height="250" width="250" src="<?php echo esc_url( $guide_profile_img ); ?>">
					</div>
					<div class="wptravel-travel-guide-name">
						<header class="entry-header">
							<h2 class="wptravel-tourguide-guidename entry-title" ><?php echo esc_html( $guide_name ); ?></h2>
						</header>

						<p class="wptravel-tourguide-addresss">
							<?php
							if ( ! empty( $guide_age ) ) {
								echo esc_html( ucfirst( $guide_age ) ) . esc_html__( ' Years Old - ', 'wp-travel' );
							}
							if ( ! empty( $guide_city ) ) {
								echo esc_html( ucfirst( $guide_city ) );
							}
							if ( ! empty( $guide_city ) ) {
								echo ', ' . esc_html( ucfirst( $guide_country ) );
							}
							?>

						</p>
						<?php if ( ! empty( $guide_slogan ) ) : ?>
							<p class="wptravel-tourguide-quote"><?php echo esc_html( $guide_slogan ); ?></p>
						<?php endif ?>
						<a class="wptravel-callto-buttion" href="#wptravel-tourguide-trips"><?php echo esc_html__( 'View All Tours', 'wp-travel' ); ?></a>
					</div>
				</div>
				<?php if ( ! empty( $guide_description ) ) : ?>
					<div class="wptravel-tourguide-guide-desc">
						<p>
							<?php echo esc_html( $guide_description ); ?>
						</p>
					</div>
				<?php endif ?>


				<?php if ( ! empty( $guide_biography ) ) : ?>
					<!-- credentials section -->
					<div class="wptravel-travel-guide-credentials">
						<h3 class="section-title"><?php echo esc_html__( 'BIOGRAPHY', 'wp-travel' ); ?></h3>
						<div>
							<?php echo wp_kses_post( $guide_biography ); ?>
						</div>
					</div>
				<?php endif ?>

				<!-- Review section -->
				<div class="wptravel-travel-guide-Review" id="wptravel-travel-guide-Review">
					<h3 class="section-title"> <?php echo esc_html__( 'RECENT REVIEW', 'wp-travel' ); ?></h3>
					<?php comments_template(); ?>
				</div>

			</div>
			<?php
			
		}else{
								
			$guide_data        = get_user_by( 'login', get_the_title() )->data;
			$guide_profile_img = ! empty( get_user_meta( $guide_data->ID, 'profile_picture', true ) ) ? get_user_meta( $guide_data->ID, 'profile_picture', true )['url'] : esc_url_raw( plugins_url( '/wp-travel/assets/images/wp-travel-placeholder.png' ) );	
			$guide_city        = get_user_meta( $guide_data->ID, 'city', true );
			$guide_country     = get_user_meta( $guide_data->ID, 'country', true );
			$guide_slogan      = get_user_meta( $guide_data->ID, 'user_slogan', true );
			$guide_description = get_user_meta( $guide_data->ID, 'description', true );
			$guide_biography   = get_user_meta( $guide_data->ID, 'biography', true );
			$guide_language    = get_user_meta( $guide_data->ID, 'language', true );
			$guide_ID          = get_user_meta( $guide_data->ID, 'guide_unique_id', true );
			$guide_join_year   = get_user_meta( $guide_data->ID, 'join_year', true );
			$guide_age         = get_user_meta( $guide_data->ID, 'age', true );
			?>
			<div class="wptravel-travel-guide-row wptravel-travel-guide-columns">
				<!-- guide details -->
				<div class="wptravel-tourguide-name-details">
					<div class="wptravel-travel-guide-image">
						<img class="wptravel-travel-guide-img" height="250" width="250" src="<?php echo esc_url( $guide_profile_img ); ?>">
					</div>
					<div class="wptravel-travel-guide-name">
						<header class="entry-header">
							<h2 class="wptravel-tourguide-guidename entry-title" ><?php echo esc_html( $guide_data->display_name ); ?></h2>
						</header>
						
						<p class="wptravel-tourguide-addresss">
							<?php
							if ( ! empty( $guide_age ) ) {
								echo esc_html( ucfirst( $guide_age ) ) . esc_html__( ' Years Old - ', 'wp-travel' );
							}
							if ( ! empty( $guide_city ) ) {
								echo esc_html( ucfirst( $guide_city ) );
							}
							if ( ! empty( $guide_city ) ) {
								echo ', ' . esc_html( ucfirst( $guide_country ) );
							}

							?>
													
						</p>
						<?php if ( ! empty( $guide_slogan ) ) : ?>
							<p class="wptravel-tourguide-quote"><?php echo esc_html( $guide_slogan ); ?></p>
						<?php endif ?>
						<a class="wptravel-callto-buttion" href="#wptravel-tourguide-trips"><?php echo esc_html__( 'View All Tours', 'wp-travel' ); ?></a>
					</div>
				</div>
				<?php if ( ! empty( $guide_description ) ) : ?>
					<div class="wptravel-tourguide-guide-desc">
						<p>
							<?php echo esc_html( $guide_description ); ?>
						</p>
					</div>
				<?php endif ?>
				

				<?php if ( ! empty( $guide_biography ) ) : ?>
					<!-- credentials section -->
					<div class="wptravel-travel-guide-credentials">
						<h3 class="section-title"><?php echo esc_html__( 'BIOGRAPHY', 'wp-travel' ); ?></h3>
						<p>
							<?php echo esc_html( $guide_biography ); ?>
						</p>
					</div>
				<?php endif ?>

				<!-- Review section -->
				<div class="wptravel-travel-guide-Review" id="wptravel-travel-guide-Review">
					<h3 class="section-title"> <?php echo esc_html__( 'RECENT REVIEW', 'wp-travel' ); ?></h3>
					<?php comments_template(); ?>
				</div>
				
			</div>
			<?php
		}
	}

	add_action( 'wp_travel_travel_guide_single_guide_template', 'wp_travel_travel_guide_single_guide_details', 20 );
}


if ( ! function_exists( 'wp_travel_travel_guide_single_guide_sidebar' ) ) {

	 /**
	  * Defininig single guide sidebar
	  *
	  *  @return void
	  */

	function wp_travel_travel_guide_single_guide_sidebar() {
		$migrate_travel_guide = get_option( 'wptravel_migrate_travel_guide', false );

		$is_pro_active = class_exists( 'WP_Travel_Pro' ) ? true : false;

		if( !$is_pro_active ){
			$migrate_travel_guide = true;
		}

		if ( $migrate_travel_guide ) {
			$post_id         = get_the_ID();
			$meta            = wptravel_guide_meta_keys();
			$guide_language  = get_post_meta( $post_id, $meta['language'], true );
			$guide_ID        = get_post_meta( $post_id, $meta['guide_unique_id'], true );
			$guide_join_year = get_post_meta( $post_id, $meta['join_year'], true );

			$facebook_link  = get_post_meta( $post_id, $meta['facebook_link'], true );
			$twitter_link   = get_post_meta( $post_id, $meta['twitter_link'], true );
			$instagram_link = get_post_meta( $post_id, $meta['instagram_link'], true );
			$linkedin_link  = get_post_meta( $post_id, $meta['linkedin_link'], true );
			$tiktok_link    = get_post_meta( $post_id, $meta['tiktok_link'], true );
			$youtube_link   = get_post_meta( $post_id, $meta['youtube_link'], true );
			?>
			<aside class="wptravel-travel-guide-details wptravel-travel-guide-columns">
				<div class="wptravel-sidebar-wrapper">
					<h3 class="section-title"><?php echo esc_html__( 'Get to know me', 'wp-travel' ); ?></h3>
					<?php if ( ! empty( $guide_join_year ) ) : ?>
						<div class="guide-register-since">
							<h3><?php echo esc_html__( 'Since', 'wp-travel' ); ?></h3>
							<P><?php echo esc_html( $guide_join_year ); ?></P>
						</div>
					<?php endif ?>


					<div class="wptravel-tourguide-aside-review">
					<h3 class="rating"><?php echo esc_html__( 'Guide Rating', 'wp-travel' ); ?></h3>
					<?php if ( wp_travel_travel_guide_rating()['review_count'] < 1 ) : ?>

							<p class="wp-travel-noreviews"><?php echo esc_html__( 'No review yet. Be first to give review', 'wp-travel' ); ?></p>
							<a class="wptravel-tourguide-aside-readmore" href="#wptravel-travel-guide-Review"><?php echo esc_html__( 'Review', 'wp-travel' ); ?></a>
						<?php
						else :

							$avg_rating = wp_travel_travel_guide_rating()['avg_rating'];
							for ( $i = 1; $i <= 5; $i++ ) {
								$star_checked = '';
								if ( $i <= $avg_rating ) {
									$star_checked = 'checked';
								}
								?>
								<span class="fa fa-star <?php echo esc_attr( $star_checked ); ?>"></span>
							<?php } ?>

							<span><?php echo esc_html( wp_travel_travel_guide_rating()['review_count'] ); ?><?php echo esc_html__( ' reviews', 'wp-travel' ); ?></span>
							<a class="wptravel-tourguide-aside-readmore" href="#wptravel-travel-guide-Review">Read Reviews</a>
					<?php endif ?>
					</div>


					<?php if ( ! empty( $guide_language ) ) : ?>
						<div class="language">
							<h3><?php echo esc_html__( 'language(s)', 'wp-travel' ); ?></h3>
							<p><?php echo esc_html( $guide_language ); ?></p>
						</div>
					<?php endif ?>

					<div class="guide-number">
						<h3><?php echo esc_html__( 'Guide Id: #', 'wp-travel' ) . esc_html( $guide_ID ); ?></h3>
					</div>
					<div class="wptravel-tg-social">
						<?php if ( ! empty( $facebook_link ) ) : ?>
							<span>
								<a href="<?php echo esc_url( $facebook_link ); ?>" alt="fb-link">
									<svg width="25" height="25">
										<path d="M12,2C6.477,2,2,6.477,2,12c0,5.013,3.693,9.153,8.505,9.876V14.65H8.031v-2.629h2.474v-1.749 c0-2.896,1.411-4.167,3.818-4.167c1.153,0,1.762,0.085,2.051,0.124v2.294h-1.642c-1.022,0-1.379,0.969-1.379,2.061v1.437h2.995 l-0.406,2.629h-2.588v7.247C18.235,21.236,22,17.062,22,12C22,6.477,17.523,2,12,2z" />
									</svg>
								</a>
							</span>

						<?php endif ?>

						<?php if ( ! empty( $twitter_link ) ) : ?>
							<span>
								<a href="<?php echo esc_url( $twitter_link ); ?>" alt="twitter-link">
								<svg viewBox="0 0 48 48" width="25px" height="25px"><path fill="#03A9F4" d="M42,12.429c-1.323,0.586-2.746,0.977-4.247,1.162c1.526-0.906,2.7-2.351,3.251-4.058c-1.428,0.837-3.01,1.452-4.693,1.776C34.967,9.884,33.05,9,30.926,9c-4.08,0-7.387,3.278-7.387,7.32c0,0.572,0.067,1.129,0.193,1.67c-6.138-0.308-11.582-3.226-15.224-7.654c-0.64,1.082-1,2.349-1,3.686c0,2.541,1.301,4.778,3.285,6.096c-1.211-0.037-2.351-0.374-3.349-0.914c0,0.022,0,0.055,0,0.086c0,3.551,2.547,6.508,5.923,7.181c-0.617,0.169-1.269,0.263-1.941,0.263c-0.477,0-0.942-0.054-1.392-0.135c0.94,2.902,3.667,5.023,6.898,5.086c-2.528,1.96-5.712,3.134-9.174,3.134c-0.598,0-1.183-0.034-1.761-0.104C9.268,36.786,13.152,38,17.321,38c13.585,0,21.017-11.156,21.017-20.834c0-0.317-0.01-0.633-0.025-0.945C39.763,15.197,41.013,13.905,42,12.429"/></svg>

								</a>
							</span>

						<?php endif ?>

						<?php if ( ! empty( $instagram_link ) ) : ?>
							<span>
								<a href="<?php echo esc_url( $instagram_link ); ?>" alt="insta-link">
									<svg width="25" height="25">
									<path d="M 8 3 C 5.239 3 3 5.239 3 8 L 3 16 C 3 18.761 5.239 21 8 21 L 16 21 C 18.761 21 21 18.761 21 16 L 21 8 C 21 5.239 18.761 3 16 3 L 8 3 z M 18 5 C 18.552 5 19 5.448 19 6 C 19 6.552 18.552 7 18 7 C 17.448 7 17 6.552 17 6 C 17 5.448 17.448 5 18 5 z M 12 7 C 14.761 7 17 9.239 17 12 C 17 14.761 14.761 17 12 17 C 9.239 17 7 14.761 7 12 C 7 9.239 9.239 7 12 7 z M 12 9 A 3 3 0 0 0 9 12 A 3 3 0 0 0 12 15 A 3 3 0 0 0 15 12 A 3 3 0 0 0 12 9 z"/>
									</svg>
								</a>
							</span>

						<?php endif ?>

						<?php if ( ! empty( $linkedin_link ) ) : ?>
							<span>
								<a href="<?php echo esc_url( $linkedin_link ); ?>" alt="linkedin-link">
									<svg width="25" height="25">
									<path d="M19,3H5C3.895,3,3,3.895,3,5v14c0,1.105,0.895,2,2,2h14c1.105,0,2-0.895,2-2V5C21,3.895,20.105,3,19,3z M9,17H6.477v-7H9 V17z M7.694,8.717c-0.771,0-1.286-0.514-1.286-1.2s0.514-1.2,1.371-1.2c0.771,0,1.286,0.514,1.286,1.2S8.551,8.717,7.694,8.717z M18,17h-2.442v-3.826c0-1.058-0.651-1.302-0.895-1.302s-1.058,0.163-1.058,1.302c0,0.163,0,3.826,0,3.826h-2.523v-7h2.523v0.977 C13.93,10.407,14.581,10,15.802,10C17.023,10,18,10.977,18,13.174V17z"/>
									</svg>
								</a>
							</span>

						<?php endif ?>

						<?php if ( ! empty( $tiktok_link ) ) : ?>
							<span>
								<a href="<?php echo esc_url( $tiktok_link ); ?>" alt="tiktok-link">
									<svg width="25" height="25">
									<path d="M 6 3 C 4.3550302 3 3 4.3550302 3 6 L 3 18 C 3 19.64497 4.3550302 21 6 21 L 18 21 C 19.64497 21 21 19.64497 21 18 L 21 6 C 21 4.3550302 19.64497 3 18 3 L 6 3 z M 12 7 L 14 7 C 14 8.005 15.471 9 16 9 L 16 11 C 15.395 11 14.668 10.734156 14 10.285156 L 14 14 C 14 15.654 12.654 17 11 17 C 9.346 17 8 15.654 8 14 C 8 12.346 9.346 11 11 11 L 11 13 C 10.448 13 10 13.449 10 14 C 10 14.551 10.448 15 11 15 C 11.552 15 12 14.551 12 14 L 12 7 z"/>
									</svg>
								</a>
							</span>

						<?php endif ?>

						<?php if ( ! empty( $youtube_link ) ) : ?>
							<span>
								<a href="<?php echo esc_url( $youtube_link ); ?>" alt="youtube-link">
								<svg viewBox="0 0 48 48" width="25px" height="25px"><path fill="#FF3D00" d="M43.2,33.9c-0.4,2.1-2.1,3.7-4.2,4c-3.3,0.5-8.8,1.1-15,1.1c-6.1,0-11.6-0.6-15-1.1c-2.1-0.3-3.8-1.9-4.2-4C4.4,31.6,4,28.2,4,24c0-4.2,0.4-7.6,0.8-9.9c0.4-2.1,2.1-3.7,4.2-4C12.3,9.6,17.8,9,24,9c6.2,0,11.6,0.6,15,1.1c2.1,0.3,3.8,1.9,4.2,4c0.4,2.3,0.9,5.7,0.9,9.9C44,28.2,43.6,31.6,43.2,33.9z"/><path fill="#FFF" d="M20 31L20 17 32 24z"/></svg>

								</a>
							</span>

						<?php endif ?>

					</div>
				</div>
			</aside>
			<?php
		}else{
			$guide_data      = get_user_by( 'login', get_the_title() )->data;
			$guide_language  = get_user_meta( $guide_data->ID, 'language', true );
			$guide_ID        = get_user_meta( $guide_data->ID, 'guide_unique_id', true );
			$guide_join_year = get_user_meta( $guide_data->ID, 'join_year', true );
			?>
			<aside class="wptravel-travel-guide-details wptravel-travel-guide-columns">
				<div class="wptravel-sidebar-wrapper">
					<h3 class="section-title"><?php echo esc_html__( 'Get to know me', 'wp-travel' ); ?></h3>
					<?php if ( ! empty( $guide_join_year ) ) : ?>
						<div class="guide-register-since">
							<h3><?php echo esc_html__( 'Since', 'wp-travel' ); ?></h3>
							<P><?php echo esc_html( $guide_join_year ); ?></P>
						</div>
					<?php endif ?>
					
					
					<div class="wptravel-tourguide-aside-review">
					<h3 class="rating"><?php echo esc_html__( 'Guide Rating', 'wp-travel' ); ?></h3> 
					<?php if ( wp_travel_travel_guide_rating()['review_count'] < 1 ) : ?>
										
							<p class="wp-travel-noreviews"><?php echo esc_html__( 'No review yet. Be first to give review', 'wp-travel' ); ?></p>
							<a class="wptravel-tourguide-aside-readmore" href="#wptravel-travel-guide-Review"><?php echo esc_html__( 'Review', 'wp-travel' ); ?></a>
						<?php
						else :

							$avg_rating = wp_travel_travel_guide_rating()['avg_rating'];
							for ( $i = 1; $i <= 5; $i++ ) {
								$star_checked = '';
								if ( $i <= $avg_rating ) {
									$star_checked = 'checked';
								}
								?>
								<span class="fa fa-star <?php echo esc_attr( $star_checked ); ?>"></span>
							<?php } ?>

							<span><?php echo esc_html( wp_travel_travel_guide_rating()['review_count'] ); ?><?php echo esc_html__( ' reviews', 'wp-travel' ); ?></span>
							<a class="wptravel-tourguide-aside-readmore" href="#wptravel-travel-guide-Review">Read Reviews</a>
					<?php endif ?>
					</div>
				

					<?php if ( ! empty( $guide_language ) ) : ?>
						<div class="language">
							<h3><?php echo esc_html__( 'language(s)', 'wp-travel' ); ?></h3>
							<p><?php echo esc_html( $guide_language ); ?></p>
						</div>
					<?php endif ?>
					
					<div class="guide-number">
						<h3><?php echo esc_html__( 'Guide Id: #', 'wp-travel' ) . esc_html( $guide_ID ); ?></h3>
					</div>
					<div class="wptravel-tg-social">
						<?php if ( ! empty( get_user_meta( $guide_data->ID, 'facebook_link', true ) ) ) : ?>
							<span>
								<a href="<?php echo esc_url( get_user_meta( $guide_data->ID, 'facebook_link', true ) ); ?>" alt="fb-link">
									<svg width="25" height="25">
										<path d="M12,2C6.477,2,2,6.477,2,12c0,5.013,3.693,9.153,8.505,9.876V14.65H8.031v-2.629h2.474v-1.749 c0-2.896,1.411-4.167,3.818-4.167c1.153,0,1.762,0.085,2.051,0.124v2.294h-1.642c-1.022,0-1.379,0.969-1.379,2.061v1.437h2.995 l-0.406,2.629h-2.588v7.247C18.235,21.236,22,17.062,22,12C22,6.477,17.523,2,12,2z" />
									</svg>
								</a>
							</span>
							
						<?php endif ?>
						
						<?php if ( ! empty( get_user_meta( $guide_data->ID, 'twitter_link', true ) ) ) : ?>
							<span>
								<a href="<?php echo esc_url( get_user_meta( $guide_data->ID, 'twitter_link', true ) ); ?>" alt="twitter-link">
								<svg viewBox="0 0 48 48" width="25px" height="25px"><path fill="#03A9F4" d="M42,12.429c-1.323,0.586-2.746,0.977-4.247,1.162c1.526-0.906,2.7-2.351,3.251-4.058c-1.428,0.837-3.01,1.452-4.693,1.776C34.967,9.884,33.05,9,30.926,9c-4.08,0-7.387,3.278-7.387,7.32c0,0.572,0.067,1.129,0.193,1.67c-6.138-0.308-11.582-3.226-15.224-7.654c-0.64,1.082-1,2.349-1,3.686c0,2.541,1.301,4.778,3.285,6.096c-1.211-0.037-2.351-0.374-3.349-0.914c0,0.022,0,0.055,0,0.086c0,3.551,2.547,6.508,5.923,7.181c-0.617,0.169-1.269,0.263-1.941,0.263c-0.477,0-0.942-0.054-1.392-0.135c0.94,2.902,3.667,5.023,6.898,5.086c-2.528,1.96-5.712,3.134-9.174,3.134c-0.598,0-1.183-0.034-1.761-0.104C9.268,36.786,13.152,38,17.321,38c13.585,0,21.017-11.156,21.017-20.834c0-0.317-0.01-0.633-0.025-0.945C39.763,15.197,41.013,13.905,42,12.429"/></svg>
																			
								</a>
							</span>
							
						<?php endif ?>
						
						<?php if ( ! empty( get_user_meta( $guide_data->ID, 'instagram_link', true ) ) ) : ?>
							<span>
								<a href="<?php echo esc_url( get_user_meta( $guide_data->ID, 'instagram_link', true ) ); ?>" alt="insta-link">
									<svg width="25" height="25">
									<path d="M 8 3 C 5.239 3 3 5.239 3 8 L 3 16 C 3 18.761 5.239 21 8 21 L 16 21 C 18.761 21 21 18.761 21 16 L 21 8 C 21 5.239 18.761 3 16 3 L 8 3 z M 18 5 C 18.552 5 19 5.448 19 6 C 19 6.552 18.552 7 18 7 C 17.448 7 17 6.552 17 6 C 17 5.448 17.448 5 18 5 z M 12 7 C 14.761 7 17 9.239 17 12 C 17 14.761 14.761 17 12 17 C 9.239 17 7 14.761 7 12 C 7 9.239 9.239 7 12 7 z M 12 9 A 3 3 0 0 0 9 12 A 3 3 0 0 0 12 15 A 3 3 0 0 0 15 12 A 3 3 0 0 0 12 9 z"/>
									</svg>
								</a>
							</span>
							
						<?php endif ?>
						
						<?php if ( ! empty( get_user_meta( $guide_data->ID, 'linkedin_link', true ) ) ) : ?>
							<span>
								<a href="<?php echo esc_url( get_user_meta( $guide_data->ID, 'linkedin_link', true ) ); ?>" alt="linkedin-link">
									<svg width="25" height="25">
									<path d="M19,3H5C3.895,3,3,3.895,3,5v14c0,1.105,0.895,2,2,2h14c1.105,0,2-0.895,2-2V5C21,3.895,20.105,3,19,3z M9,17H6.477v-7H9 V17z M7.694,8.717c-0.771,0-1.286-0.514-1.286-1.2s0.514-1.2,1.371-1.2c0.771,0,1.286,0.514,1.286,1.2S8.551,8.717,7.694,8.717z M18,17h-2.442v-3.826c0-1.058-0.651-1.302-0.895-1.302s-1.058,0.163-1.058,1.302c0,0.163,0,3.826,0,3.826h-2.523v-7h2.523v0.977 C13.93,10.407,14.581,10,15.802,10C17.023,10,18,10.977,18,13.174V17z"/>
									</svg>
								</a>
							</span>
							
						<?php endif ?>
						
						<?php if ( ! empty( get_user_meta( $guide_data->ID, 'tiktok_link', true ) ) ) : ?>
							<span>
								<a href="<?php echo esc_url( get_user_meta( $guide_data->ID, 'tiktok_link', true ) ); ?>" alt="tiktok-link">
									<svg width="25" height="25">
									<path d="M 6 3 C 4.3550302 3 3 4.3550302 3 6 L 3 18 C 3 19.64497 4.3550302 21 6 21 L 18 21 C 19.64497 21 21 19.64497 21 18 L 21 6 C 21 4.3550302 19.64497 3 18 3 L 6 3 z M 12 7 L 14 7 C 14 8.005 15.471 9 16 9 L 16 11 C 15.395 11 14.668 10.734156 14 10.285156 L 14 14 C 14 15.654 12.654 17 11 17 C 9.346 17 8 15.654 8 14 C 8 12.346 9.346 11 11 11 L 11 13 C 10.448 13 10 13.449 10 14 C 10 14.551 10.448 15 11 15 C 11.552 15 12 14.551 12 14 L 12 7 z"/>
									</svg>
								</a>
							</span>
							
						<?php endif ?>
					
						<?php if ( ! empty( get_user_meta( $guide_data->ID, 'youtube_link', true ) ) ) : ?>
							<span>
								<a href="<?php echo esc_url( get_user_meta( $guide_data->ID, 'youtube_link', true ) ); ?>" alt="youtube-link">
								<svg viewBox="0 0 48 48" width="25px" height="25px"><path fill="#FF3D00" d="M43.2,33.9c-0.4,2.1-2.1,3.7-4.2,4c-3.3,0.5-8.8,1.1-15,1.1c-6.1,0-11.6-0.6-15-1.1c-2.1-0.3-3.8-1.9-4.2-4C4.4,31.6,4,28.2,4,24c0-4.2,0.4-7.6,0.8-9.9c0.4-2.1,2.1-3.7,4.2-4C12.3,9.6,17.8,9,24,9c6.2,0,11.6,0.6,15,1.1c2.1,0.3,3.8,1.9,4.2,4c0.4,2.3,0.9,5.7,0.9,9.9C44,28.2,43.6,31.6,43.2,33.9z"/><path fill="#FFF" d="M20 31L20 17 32 24z"/></svg>
																												
								</a>
							</span>
							
						<?php endif ?>
						
					</div>
				</div>
			</aside>
			<?php
		}
	}

	add_action( 'wp_travel_travel_guide_single_guide_template', 'wp_travel_travel_guide_single_guide_sidebar', 30 );
}


if ( ! function_exists( 'wp_travel_travel_guide_review_template' ) ) {

	/**
	 * Defining Review Template
	 *
	 * @return template
	 */
	function wp_travel_travel_guide_review_template() {
		
		$guide_review_template = wptravel_get_template( 'single-wp-travel-reviews.php' );
		if ( $guide_review_template ) {
			return $guide_review_template;
		}

	}

}


if ( ! function_exists( 'wp_travel_travel_guide_add_review_rate_meta' ) ) {

	 /**
	  * Defining Action to add guide review rate
	  *
	  * @param int    $comment_id Attributes.
	  * @param boolen $approve Attributes.
	  * @param array  $comment_data Attributes.
	  */

	function wp_travel_travel_guide_add_review_rate_meta( $comment_id, $approve, $comment_data ) {
		if ( isset( $_POST['wp_travel_rate_val'] ) && 'travel-guide' === get_post_type( $comment_data['comment_post_ID'] ) ) {
			if ( absint( $_POST['wp_travel_rate_val'] ) > 5 || absint( $_POST['wp_travel_rate_val'] ) < 0 ) {
				return;
			}
			add_comment_meta( $comment_id, '_wp_travel_rating', absint( $_POST['wp_travel_rate_val'] ), true );
		}
	}

	add_action( 'comment_post', 'wp_travel_travel_guide_add_review_rate_meta', 10, 3 );

}


if ( ! function_exists( 'wp_travel_travel_guide_rating' ) ) {

	 /**
	  * Getting guide review rate
	  *
	  * @return array
	  */

	function wp_travel_travel_guide_rating( $id = '' ) {
		$guide_id     = ! empty( $id ) ? $id : get_the_id();
		$get_reviews  = get_comments( array( 'post_id' => $guide_id ) );
		$review_count = count( get_comments( array( 'post_id' => $guide_id ) ) );
		$get_rating   = 0;

		foreach ( $get_reviews as $review ) {
			$get_rating = $get_rating + (int) get_comment_meta( (int) $review->comment_ID, '_wp_travel_rating' )[0];
		}

		return array(
			'review_count' => $review_count,
			'avg_rating'   => ( $review_count < 1 ) ? 0 : intval( $get_rating / $review_count ),
		);

	}
}
