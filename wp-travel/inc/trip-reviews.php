<?php
/**
 * WP Travel – Trip Reviews Shortcode with star filter + popular trips.
 *
 * Shortcode: [wp_travel_trip_reviews]
 *
 * Paste into your child theme's functions.php (or a Code Snippets plugin).
 *
 * Usage:
 *   [wp_travel_trip_reviews]
 *   [wp_travel_trip_reviews per_page="10"]
 *   [wp_travel_trip_reviews trip_id="123"]      <- reviews for one trip only
 *   [wp_travel_trip_reviews show_popular="no"]  <- hide the popular trips list
 *   [wp_travel_trip_reviews popular_count="3" popular_title="Most loved trips"]
 *   [wp_travel_trip_reviews post_type="itineraries" rating_key="_wp_travel_rating"]
 *
 * Defaults match WP Travel:
 *   - post type:  itineraries
 *   - rating key: _wp_travel_rating (same meta read in wp-travel/review.php)
 *
 * Layout: a summary panel (average + one bar per star that doubles as the
 * filter + popular trips ranked by 5-star reviews) beside a list of review
 * cards. Stacks on mobile.
 *
 * This file is self-contained. It does not need popular-trips-shortcode.php
 * and its function names do not clash with it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a clean star filter value (0 = all, 1-5 = specific star).
 *
 * @return int
 */
function wp_travel_trip_reviews_get_star_filter() {
	$star = isset( $_GET['wp_travel_star'] ) ? absint( $_GET['wp_travel_star'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	return ( $star >= 1 && $star <= 5 ) ? $star : 0;
}

/**
 * Render star markup for a rating.
 *
 * @param float  $rating      Rating 0-5.
 * @param string $extra_class Optional extra class.
 * @return string
 */
function wp_travel_trip_reviews_render_stars( $rating, $extra_class = '' ) {
	$rating  = max( 0, min( 5, (float) $rating ) );
	$percent = ( $rating / 5 ) * 100;

	/* translators: %s: rating */
	$label = sprintf( __( 'Rated %s out of 5', 'wp-travel' ), rtrim( rtrim( number_format( $rating, 1 ), '0' ), '.' ) );

	return '<span class="wp-travel-trip-reviews-stars ' . esc_attr( $extra_class ) . '" role="img" aria-label="' . esc_attr( $label ) . '">'
		. '<span class="wp-travel-trip-reviews-stars-fill" style="width:' . esc_attr( $percent ) . '%">&#9733;&#9733;&#9733;&#9733;&#9733;</span>'
		. '</span>';
}

/**
 * Get trips ordered by number of approved 5-star reviews (cached).
 *
 * @param string $post_type  Trip post type.
 * @param string $rating_key Rating comment meta key.
 * @param int    $limit      Max trips.
 * @return array[] Each item: array( 'trip_id' => int, 'five_star' => int ).
 */
function wp_travel_trip_reviews_get_popular_trips( $post_type, $rating_key, $limit ) {
	global $wpdb;

	$version   = (int) get_option( 'wp_travel_trip_reviews_popular_version', 1 );
	$cache_key = 'wp_travel_trp_' . md5( $version . '|' . $post_type . '|' . $rating_key . '|' . $limit );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c.comment_post_ID AS trip_id, COUNT(*) AS five_star
			FROM {$wpdb->comments} c
			INNER JOIN {$wpdb->commentmeta} cm ON cm.comment_id = c.comment_ID
			INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
			WHERE c.comment_approved = '1'
				AND c.comment_parent = 0
				AND p.post_type = %s
				AND p.post_status = 'publish'
				AND cm.meta_key = %s
				AND cm.meta_value = %s
			GROUP BY c.comment_post_ID
			ORDER BY five_star DESC, MAX(c.comment_date_gmt) DESC
			LIMIT %d",
			$post_type,
			$rating_key,
			'5',
			$limit
		),
		ARRAY_A
	);
	// phpcs:enable

	$rows = is_array( $rows ) ? $rows : array();

	set_transient( $cache_key, $rows, 6 * HOUR_IN_SECONDS );

	return $rows;
}

/**
 * Clear the popular trips cache whenever reviews change.
 */
function wp_travel_trip_reviews_bust_popular_cache() {
	update_option( 'wp_travel_trip_reviews_popular_version', (int) get_option( 'wp_travel_trip_reviews_popular_version', 1 ) + 1, false );
}
add_action( 'comment_post', 'wp_travel_trip_reviews_bust_popular_cache' );
add_action( 'edit_comment', 'wp_travel_trip_reviews_bust_popular_cache' );
add_action( 'deleted_comment', 'wp_travel_trip_reviews_bust_popular_cache' );
add_action( 'wp_set_comment_status', 'wp_travel_trip_reviews_bust_popular_cache' );
add_action( 'added_comment_meta', 'wp_travel_trip_reviews_bust_popular_cache' );
add_action( 'updated_comment_meta', 'wp_travel_trip_reviews_bust_popular_cache' );

/**
 * Shortcode callback.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function wp_travel_trip_reviews_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'post_type'     => 'itineraries',
			'rating_key'    => '_wp_travel_rating',
			'per_page'      => 10,
			'trip_id'       => 0,
			'show_popular'  => 'yes',
			'popular_count' => 4,
			'popular_title' => __( 'Popular trips', 'wp-travel' ),
		),
		$atts,
		'wp_travel_trip_reviews'
	);

	$per_page   = max( 1, absint( $atts['per_page'] ) );
	$rating_key = sanitize_key( $atts['rating_key'] );
	$post_type  = sanitize_key( $atts['post_type'] );
	$paged      = isset( $_GET['wp_travel_cpage'] ) ? max( 1, absint( $_GET['wp_travel_cpage'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
	$filter     = wp_travel_trip_reviews_get_star_filter();

	// Base args shared by every query. parent=0 skips replies (they have no rating).
	$base = array(
		'status'    => 'approve',
		'post_type' => $post_type,
		'parent'    => 0,
	);
	if ( absint( $atts['trip_id'] ) ) {
		$base['post_id'] = absint( $atts['trip_id'] );
	}

	// Count per star, rated total and average (for the summary panel).
	$counts = array();
	$rated  = 0;
	$sum    = 0;
	for ( $i = 1; $i <= 5; $i++ ) {
		$counts[ $i ] = (int) get_comments(
			array_merge(
				$base,
				array(
					'count'      => true,
					'meta_key'   => $rating_key, // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value' => $i,          // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			)
		);
		$rated += $counts[ $i ];
		$sum   += $i * $counts[ $i ];
	}
	$average = $rated ? $sum / $rated : 0;

	// Popular trips (ranked by number of 5-star reviews).
	$popular = array();
	if ( 'no' !== strtolower( $atts['show_popular'] ) ) {
		$popular = wp_travel_trip_reviews_get_popular_trips( $post_type, $rating_key, max( 1, absint( $atts['popular_count'] ) ) );
	}

	// Main (filtered) query.
	$query_args = $base;
	if ( $filter ) {
		$query_args['meta_key']   = $rating_key; // phpcs:ignore WordPress.DB.SlowDBQuery
		$query_args['meta_value'] = $filter;     // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$total       = (int) get_comments( array_merge( $query_args, array( 'count' => true ) ) );
	$total_pages = (int) ceil( $total / $per_page );
	$comments    = get_comments(
		array_merge(
			$query_args,
			array(
				'number'  => $per_page,
				'offset'  => ( $paged - 1 ) * $per_page,
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		)
	);

	$anchor      = '#wp-travel-trip-reviews';
	$clean_url   = remove_query_arg( array( 'wp_travel_star', 'wp_travel_cpage' ) );
	$current_url = remove_query_arg( 'wp_travel_cpage' );

	ob_start();
	?>
	<div class="wp-travel-trip-reviews" id="wp-travel-trip-reviews">

		<!-- Summary panel: the bars are the filter. -->
		<aside class="wp-travel-trip-reviews-summary" aria-label="<?php esc_attr_e( 'Review summary', 'wp-travel' ); ?>">
			<div class="wp-travel-trip-reviews-score">
				<span class="wp-travel-trip-reviews-score-number"><?php echo esc_html( number_format_i18n( $average, 1 ) ); ?></span>
				<div>
					<?php echo wp_travel_trip_reviews_render_stars( $average, 'is-large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="wp-travel-trip-reviews-score-count">
						<?php
						/* translators: %s: number of reviews */
						echo esc_html( sprintf( _n( 'Based on %s review', 'Based on %s reviews', $rated, 'wp-travel' ), number_format_i18n( $rated ) ) );
						?>
					</span>
				</div>
			</div>

			<div class="wp-travel-trip-reviews-bars">
				<?php
				for ( $i = 5; $i >= 1; $i-- ) :
					$pct = $rated ? round( ( $counts[ $i ] / $rated ) * 100 ) : 0;
					?>
					<a class="wp-travel-trip-reviews-bar <?php echo $filter === $i ? 'is-active' : ''; ?> <?php echo 0 === $counts[ $i ] ? 'is-empty' : ''; ?>"
					   href="<?php echo esc_url( add_query_arg( 'wp_travel_star', $i, $current_url ) . $anchor ); ?>"
					   <?php echo $filter === $i ? 'aria-current="true"' : ''; ?>>
						<span class="wp-travel-trip-reviews-bar-label"><?php echo esc_html( $i ); ?> <span aria-hidden="true">&#9733;</span></span>
						<span class="wp-travel-trip-reviews-bar-track"><span class="wp-travel-trip-reviews-bar-fill" style="width:<?php echo esc_attr( $pct ); ?>%"></span></span>
						<span class="wp-travel-trip-reviews-bar-count"><?php echo esc_html( $counts[ $i ] ); ?></span>
					</a>
				<?php endfor; ?>
			</div>

			<?php if ( $filter ) : ?>
				<a class="wp-travel-trip-reviews-clear" href="<?php echo esc_url( $clean_url . $anchor ); ?>">
					<?php esc_html_e( 'Show all reviews', 'wp-travel' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $popular ) : ?>
				<!-- Popular trips: ranked by number of 5-star reviews. -->
				<section class="wp-travel-trip-reviews-popular">
					<?php if ( '' !== trim( $atts['popular_title'] ) ) : ?>
						<h3 class="wp-travel-trip-reviews-popular-title"><?php echo esc_html( $atts['popular_title'] ); ?></h3>
					<?php endif; ?>

					<ul class="wp-travel-trip-reviews-popular-list">
						<?php
						foreach ( $popular as $trip ) :
							$trip_id    = (int) $trip['trip_id'];
							$five_stars = (int) $trip['five_star'];
							?>
							<li>
								<a class="wp-travel-trip-reviews-popular-link" href="<?php echo esc_url( get_permalink( $trip_id ) ); ?>">
									<span class="wp-travel-trip-reviews-popular-thumb">
										<?php echo has_post_thumbnail( $trip_id ) ? get_the_post_thumbnail( $trip_id, 'thumbnail', array( 'loading' => 'lazy' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</span>
									<span class="wp-travel-trip-reviews-popular-text">
										<span class="wp-travel-trip-reviews-popular-name"><?php echo esc_html( get_the_title( $trip_id ) ); ?></span>
										<span class="wp-travel-trip-reviews-popular-meta">
											<b aria-hidden="true">&#9733;</b>
											<?php
											/* translators: %s: number of 5-star reviews */
											echo esc_html( sprintf( _n( '%s five-star review', '%s five-star reviews', $five_stars, 'wp-travel' ), number_format_i18n( $five_stars ) ) );
											?>
										</span>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</aside>

		<!-- Review list. -->
		<div class="wp-travel-trip-reviews-main">

			<?php if ( $filter ) : ?>
				<p class="wp-travel-trip-reviews-status">
					<?php
					/* translators: 1: number of reviews, 2: star rating */
					echo esc_html( sprintf( _n( '%1$s review with %2$d stars', '%1$s reviews with %2$d stars', $total, 'wp-travel' ), number_format_i18n( $total ), $filter ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( $comments ) : ?>

				<ul class="wp-travel-trip-reviews-list">
					<?php
					foreach ( $comments as $review ) :
						$rating = (int) get_comment_meta( $review->comment_ID, $rating_key, true );
						?>
						<li class="wp-travel-trip-reviews-item" id="wp-travel-review-<?php echo esc_attr( $review->comment_ID ); ?>">

							<header class="wp-travel-trip-reviews-head">
								<?php echo get_avatar( $review, 48 ); ?>
								<div class="wp-travel-trip-reviews-who">
									<strong class="wp-travel-trip-reviews-author"><?php echo esc_html( $review->comment_author ); ?></strong>
									<time class="wp-travel-trip-reviews-date" datetime="<?php echo esc_attr( get_comment_date( 'c', $review ) ); ?>">
										<?php echo esc_html( get_comment_date( get_option( 'date_format' ), $review ) ); ?>
									</time>
								</div>
								<?php echo wp_travel_trip_reviews_render_stars( $rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</header>

							<div class="wp-travel-trip-reviews-body">
								<?php echo wp_kses_post( wpautop( $review->comment_content ) ); ?>
							</div>

							<a class="wp-travel-trip-reviews-trip" href="<?php echo esc_url( get_permalink( $review->comment_post_ID ) ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
								<?php echo esc_html( get_the_title( $review->comment_post_ID ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( $total_pages > 1 ) : ?>
					<nav class="wp-travel-trip-reviews-pagination" aria-label="<?php esc_attr_e( 'Reviews pagination', 'wp-travel' ); ?>">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'base'      => add_query_arg( 'wp_travel_cpage', '%#%', $current_url ) . $anchor,
									'format'    => '',
									'current'   => $paged,
									'total'     => $total_pages,
									'prev_text' => '&lsaquo;',
									'next_text' => '&rsaquo;',
								)
							)
						);
						?>
					</nav>
				<?php endif; ?>

			<?php else : ?>
				<div class="wp-travel-trip-reviews-empty">
					<p><?php esc_html_e( 'No reviews match this rating yet.', 'wp-travel' ); ?></p>
					<?php if ( $filter ) : ?>
						<a href="<?php echo esc_url( $clean_url . $anchor ); ?>"><?php esc_html_e( 'Show all reviews', 'wp-travel' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'wp_travel_trip_reviews', 'wp_travel_trip_reviews_shortcode' );

/**
 * Enqueue inline styles only on pages that use the shortcode.
 */
function wp_travel_trip_reviews_enqueue_styles() {
	global $post;

	if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'wp_travel_trip_reviews' ) ) {
		return;
	}

	$css = '
	.wp-travel-trip-reviews{
		--wt-ink:#14232b; --wt-muted:#5f7078; --wt-line:#dde6e8; --wt-mist:#f1f6f7;
		--wt-accent:#0b6e75; --wt-accent-soft:#e1f1f2; --wt-star:#f2a900; --wt-star-off:#d5dee1;
		display:grid; grid-template-columns:320px minmax(0,1fr); gap:32px; align-items:start;
		color:var(--wt-ink); box-sizing:border-box;
	}
	.wp-travel-trip-reviews *{box-sizing:border-box}

	/* Summary panel */
	.wp-travel-trip-reviews-summary{
		position:sticky; top:24px; padding:24px; background:var(--wt-mist);
		border:1px solid var(--wt-line);
	}
	.wp-travel-trip-reviews-score{display:flex; align-items:center; gap:16px; margin-bottom:20px}
	.wp-travel-trip-reviews-score-number{font-size:56px; line-height:1; font-weight:700; letter-spacing:-.03em}
	.wp-travel-trip-reviews-score-count{display:block; margin-top:6px; font-size:14px; color:var(--wt-muted)}

	/* Stars (partial fill via width) */
	.wp-travel-trip-reviews-stars{
		position:relative; display:inline-block; font-size:16px; line-height:1; letter-spacing:2px;
		color:var(--wt-star-off); white-space:nowrap;
	}
	.wp-travel-trip-reviews-stars::before{content:"\2605\2605\2605\2605\2605"}
	.wp-travel-trip-reviews-stars-fill{position:absolute; left:0; top:0; overflow:hidden; color:var(--wt-star); white-space:nowrap}
	.wp-travel-trip-reviews-stars.is-large{font-size:22px}

	/* Bars = filter */
	.wp-travel-trip-reviews-bars{display:flex; flex-direction:column; gap:4px}
	.wp-travel-trip-reviews-bar{
		display:grid; grid-template-columns:36px 1fr 28px; align-items:center; gap:10px;
		padding:7px 10px; border-radius:10px; text-decoration:none; color:var(--wt-ink); font-size:14px;
	}
	.wp-travel-trip-reviews-bar:hover{background:#fff}
	.wp-travel-trip-reviews-bar.is-active{background:#fff; box-shadow:inset 0 0 0 2px var(--wt-accent)}
	.wp-travel-trip-reviews-bar.is-empty{color:var(--wt-muted)}
	.wp-travel-trip-reviews-bar-label span{color:var(--wt-star)}
	.wp-travel-trip-reviews-bar-track{height:8px; background:#dbe5e7; border-radius:999px; overflow:hidden}
	.wp-travel-trip-reviews-bar-fill{display:block; height:100%; background:var(--wt-accent); border-radius:999px}
	.wp-travel-trip-reviews-bar-count{text-align:right; color:var(--wt-muted); font-variant-numeric:tabular-nums}
	.wp-travel-trip-reviews-clear{
		display:inline-block; margin-top:14px; font-size:14px; font-weight:600;
		color:var(--wt-accent); text-decoration:underline; text-underline-offset:3px;
	}

	/* Popular trips (inside the summary panel) */
	.wp-travel-trip-reviews-popular{margin-top:24px; padding-top:20px; border-top:1px solid var(--wt-line)}
	.wp-travel-trip-reviews-popular-title{margin:0 0 12px; font-size:17px; line-height:1.3; color:var(--wt-ink)}
	.wp-travel-trip-reviews-popular-list{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:4px}
	.wp-travel-trip-reviews-popular-link{
		display:flex; align-items:center; gap:12px; padding:8px; border-radius:12px;
		text-decoration:none; color:var(--wt-ink);
	}
	.wp-travel-trip-reviews-popular-link:hover{background:#fff}
	.wp-travel-trip-reviews-popular-thumb{flex:none; width:56px; height:56px; border-radius:10px; overflow:hidden; background:#dbe5e7}
	.wp-travel-trip-reviews-popular-thumb img{display:block; width:100%; height:100%; object-fit:cover}
	.wp-travel-trip-reviews-popular-text{min-width:0; display:flex; flex-direction:column; gap:3px}
	.wp-travel-trip-reviews-popular-name{
		font-size:14px; font-weight:600; line-height:1.35;
		display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
	}
	.wp-travel-trip-reviews-popular-meta{font-size:13px; color:var(--wt-muted)}
	.wp-travel-trip-reviews-popular-meta b{color:var(--wt-star); font-weight:400}

	/* Review cards */
	.wp-travel-trip-reviews-status{margin:0 0 14px; font-size:14px; color:var(--wt-muted)}
	.wp-travel-trip-reviews-list{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:16px}
	.wp-travel-trip-reviews-item{
		padding:22px 24px; background:#fff; border:1px solid var(--wt-line);
	}
	.wp-travel-trip-reviews-head{display:flex; align-items:center; gap:14px; margin-bottom:12px}
	.wp-travel-trip-reviews-head img{width:48px; height:48px; border-radius:50%; object-fit:cover; flex:none}
	.wp-travel-trip-reviews-who{display:flex; flex-direction:column; min-width:0; margin-right:auto}
	.wp-travel-trip-reviews-author{font-size:16px; line-height:1.3}
	.wp-travel-trip-reviews-date{font-size:13px; color:var(--wt-muted)}
	.wp-travel-trip-reviews-body{max-width:70ch; font-size:15px; line-height:1.7}
	.wp-travel-trip-reviews-body p{margin:0 0 .8em}
	.wp-travel-trip-reviews-body p:last-child{margin-bottom:0}
	.wp-travel-trip-reviews-trip{
		display:inline-flex; align-items:center; gap:6px; margin-top:14px; padding:6px 12px;
		background:var(--wt-accent-soft); color:var(--wt-accent); border-radius:999px;
		font-size:13px; font-weight:600; text-decoration:none;
	}
	.wp-travel-trip-reviews-trip:hover{background:var(--wt-accent); color:#fff}

	/* Pagination */
	.wp-travel-trip-reviews-pagination{display:flex; flex-wrap:wrap; gap:6px; margin-top:24px}
	.wp-travel-trip-reviews-pagination .page-numbers{
		min-width:40px; height:40px; padding:0 12px; display:inline-flex; align-items:center; justify-content:center;
		border:1px solid var(--wt-line); border-radius:10px; color:var(--wt-ink); text-decoration:none; font-size:14px;
	}
	.wp-travel-trip-reviews-pagination a.page-numbers:hover{border-color:var(--wt-accent); color:var(--wt-accent)}
	.wp-travel-trip-reviews-pagination .current{background:var(--wt-accent); border-color:var(--wt-accent); color:#fff}

	/* Empty state */
	.wp-travel-trip-reviews-empty{padding:40px 24px; text-align:center; border:1px dashed var(--wt-line); border-radius:16px; color:var(--wt-muted)}
	.wp-travel-trip-reviews-empty a{color:var(--wt-accent); font-weight:600}

	/* Keyboard focus */
	.wp-travel-trip-reviews a:focus-visible{outline:3px solid var(--wt-accent); outline-offset:2px}

	/* Responsive */
	@media (max-width:860px){
		.wp-travel-trip-reviews{grid-template-columns:1fr; gap:20px}
		.wp-travel-trip-reviews-summary{position:static}
	}
	@media (max-width:520px){
		.wp-travel-trip-reviews-item{padding:18px}
		.wp-travel-trip-reviews-head{flex-wrap:wrap}
		.wp-travel-trip-reviews-score-number{font-size:48px}
	}
	';

	wp_register_style( 'wp-travel-trip-reviews', false, array(), '2.1.0' );
	wp_enqueue_style( 'wp-travel-trip-reviews' );
	wp_add_inline_style( 'wp-travel-trip-reviews', $css );
}
add_action( 'wp_enqueue_scripts', 'wp_travel_trip_reviews_enqueue_styles' );