<?php
/**
 * Itinerary Single Contnet Template
 *
 * This template can be overridden by copying it to yourtheme/wp-travel/content-single-itineraries.php.
 *
 * HOWEVER, on occasion wp-travel will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.wensolutions.com/document/template-structure/
 * @author  WenSolutions
 * @package WP_Travel
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $wp_travel_itinerary;
?>
<div class="<?php echo esc_attr(  wp_get_theme()->template ) ?>-wptravel-main-content-wrapper" >
<?php
do_action( 'wp_travel_before_single_itinerary', get_the_ID() );
$trip_id = get_the_ID();
$strings = WpTravel_Helpers_Strings::get();
$string = isset( $strings['single_archive'] ) ? $strings['single_archive'] : [];
$offers = isset( $string['offer'] ) ? $string['offer'] : apply_filters( 'wp_travel_singel_page_offer', 'Offer' );
$view_gallerys = isset( $string['view_gallery'] ) ? $string['view_gallery'] : apply_filters( 'wp_travel_singel_page_view_gallery', 'View Gallery' );
if ( post_password_required() ) {
	echo wp_kses_post( get_the_password_form() );
	return;
}
$wrapper_class = wptravel_get_theme_wrapper_class();
do_action( 'wp_travel_before_content_start' );
?>

<div id="itinerary-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="content entry-content">
		<div class="wp-travel trip-headline-wrapper clearfix <?php echo esc_attr( $wrapper_class ); ?>">
			<div class="wp-travel__trip-headline">
				<div id="wp-travel__add-to-cart_notice"></div>
				<div class="wp-travel-single-trip-add-to-cart">
					<?php wptravel_get_cart_icon(); ?>
				</div>
			</div>
				
			<div class="wp-travel-feature-slide-content featured-side-image left-plot">
				<div class="banner-image-wrapper" style="background-image: url(<?php echo esc_url( apply_filters( 'wp_travel_trip_single_page_thumbnail_background',  wptravel_get_post_thumbnail_url( get_the_ID(), 'large' ), get_the_ID() ) ); ?>)">
						<?php echo wp_kses_post( apply_filters( 'wp_travel_trip_single_page_thumbnail', wp_kses( wptravel_get_post_thumbnail( get_the_ID() ), wptravel_allowed_html( array( 'img' ) ) ), get_the_ID() ) ); ?>
						<?php if( WP_Travel_Helpers_Trips::get_trip(get_the_ID())['trip']['trip_video_code'] ): ?>
						<a class="trip-video" href="<?php echo esc_url(WP_Travel_Helpers_Trips::get_trip(get_the_ID())['trip']['trip_video_code']); ?>"><i class="fas fa-play-circle"></i></a>
						<?php endif; ?>
				</div>
				<?php if ( WP_Travel_Helpers_Trips::is_sale_enabled( array( 'trip_id' => get_the_ID() ) ) ) : ?>

					<div class="wp-travel-offer">
						<span><?php echo esc_html( $offers ); ?></span>
					</div>
					<?php endif; ?>
						<?php if ( $wp_travel_itinerary->has_multiple_images() ) : ?>
					<div class="wp-travel-view-gallery">
						<a class="top-view-gallery" href=""><?php echo esc_html( $view_gallerys ); ?></a>
					</div>
				<?php endif; ?>
			</div>
			<div class="wp-travel-feature-slide-content featured-detail-section right-plot">
				<div class="right-plot-inner-wrap">
					<?php $show_title = apply_filters( 'wp_travel_show_single_page_title', true ); ?>
					<?php if ( $show_title ) : ?>
						<header class="entry-header">
							<?php do_action( 'wp_travel_before_single_title', get_the_ID() ); ?>
							<?php wptravel_do_deprecated_action( 'wp_tarvel_before_single_title', array( get_the_ID() ), '2.0.4', 'wp_travel_before_single_title' ); ?>
							<?php apply_filters( 'wp_travel_single_archive_trip_tilte', the_title( '<h1 class="entry-title">', '</h1>' ),  $trip_id ); ?>
						</header>
					<?php endif; ?>					
					<?php wptravel_do_deprecated_action( 'wp_travel_after_single_title', array( get_the_ID() ), '2.0.4', 'wp_travel_single_trip_after_title' );  // @since 1.0.4 and deprecated in 2.0.4 ?>
					<?php do_action( 'wp_travel_single_trip_after_title', get_the_ID() ); ?>
					
				</div>
			</div>
		</div>
		<?php
			wptravel_do_deprecated_action( 'wp_travel_after_single_itinerary_header', array( get_the_ID() ), '2.0.4', 'wp_travel_single_trip_after_header' );  // @since 1.0.4 and deprecated in 2.0.4
			do_action( 'wp_travel_single_trip_after_header', get_the_ID() );
		?>
	</div><!-- .summary -->
</div><!-- #itinerary-<?php the_ID(); ?> -->

<?php do_action( 'wp_travel_after_single_itinerary', get_the_ID() ); ?>
</div>

<?php if( apply_filters( 'wp_travel_enable_quick_book', false ) == true ): ?>
	<div id="wp-travel-quick-book-modal" class="wp-travel-calendar-view">
		<div class="wp-travel-quick-view-modal-header">
			<button class="close-modal"><i class="fas fa-times"></i></button>
		</div>
		
		<div id="wp-travel-quick-book-modal-content">
			<svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="200" height="200" 
				style="shape-rendering: auto; display: block; background: transparent;">
				<g>
					<circle stroke-dasharray="61.26105674500097 22.420352248333657" r="13" stroke-width="5" 
						stroke="#0099e5" fill="none" cy="50" cx="50">
						<animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="1s" 
							repeatCount="indefinite" type="rotate" attributeName="transform">
						</animateTransform>
					</circle>
				</g>
			</svg>
		</div>
	</div>
<?php
endif; 
