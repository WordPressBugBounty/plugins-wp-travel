<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!.
 *
 * This template can be overridden by copying it to yourtheme/wp-travel/review.php.
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
	exit;
}

global $comment;
$settings = wptravel_get_settings();


$rating = intval( get_comment_meta( $comment->comment_ID, '_wp_travel_rating', true ) ); ?>

<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container">

		<?php echo get_avatar( $comment, apply_filters( 'wp_travel_review_gravatar_size', '60' ), '' ); ?>

		<div class="comment-text">
			<!-- since 6.2 -->
			<?php
			if ( $settings['disable_admin_review'] == 'yes' ) :

				if ( get_user_by( 'login', $comment->comment_author ) ) {
					if ( in_array( get_user_by( 'login', $comment->comment_author )->roles[0], array( 'administrator', 'editor', 'author' ) ) ) {
						?>
						<div class="wp-travel-admin-review">
							<?php esc_html_e( 'Admin', 'wp-travel' ); ?>
						</div>
						<?php
					} else {
						?>
						<div class="wp-travel-average-review" title="<?php echo esc_html__( 'Rated ', 'wp-travel' ).esc_html($rating).esc_html__( ' out of 5', 'wp-travel' ); ?>">
							<a>
							 <span style="width:<?php echo esc_attr( ( $rating / 5 ) * 100 ); ?>%"><strong><?php echo esc_html( $rating ); ?></strong> <?php esc_html_e( 'out of 5', 'wp-travel' ); ?></span>
							</a>
						</div>
						<?php
					}
				} else {
					?>
					<div class="wp-travel-average-review" title="<?php echo esc_html__( 'Rated ', 'wp-travel' ).esc_html($rating).esc_html__( ' out of 5', 'wp-travel' ); ?>">
						<a>
						 <span style="width:<?php echo esc_attr( ( $rating / 5 ) * 100 ); ?>%"><strong><?php echo esc_html( $rating ); ?></strong> <?php esc_html_e( 'out of 5', 'wp-travel' ); ?></span>
						</a>
					</div>
				<?php	} ?>
				
				<?php else : ?>
					<div class="wp-travel-average-review" title="<?php echo esc_html__( 'Rated ', 'wp-travel' ).esc_html($rating).esc_html__( ' out of 5', 'wp-travel' ); ?>">
						<a>
						 <span style="width:<?php echo esc_attr( ( $rating / 5 ) * 100 ); ?>%"><strong><?php echo esc_html( $rating ); ?></strong> <?php esc_html_e( 'out of 5', 'wp-travel' ); ?></span>
						</a>
					</div>
			<?php endif ?>

			<?php do_action( 'wp_travel_review_before_comment_meta', $comment ); ?>

			<?php if ( $comment->comment_approved == '0' ) : ?>

				<p class="meta"><em><?php echo esc_html( apply_filters( 'wp_travel_single_archive_comment_approve_message', __( 'Your comment is awaiting approval', 'wp-travel' ) ) ); ?></em></p>

			<?php else : ?>

				<p class="meta">
					<strong><?php echo esc_html( apply_Filters( 'wp_travel_single_archive_comment_author', comment_author() ) ); ?></strong>&ndash; <time datetime="<?php echo esc_html( apply_filters( 'wp_travel_single_archive_comment_date', get_comment_date( 'c' ) ) ); ?>"><?php echo esc_html( apply_filters( 'wp_travel_single_archive_comment_date_format', get_comment_date( get_option( 'date_format' ) ) ) ); ?></time>:
				</p>

			<?php endif; ?>

			<?php do_action( 'wp_travel_review_before_comment_text', $comment ); ?>

			<div class="description"><?php apply_filters( 'wp_travel_single_archive_comment', comment_text() ); ?></div>
			<?php if( get_option( 'thread_comments', true ) == '1' ): ?>
			<div class="reply">
			<?php
			do_action( 'wp_travel_single_archive_after_comment_text', $comment, $rating );
			// Reply Link.
			$post_id = get_the_ID();
			if ( ! comments_open( get_the_ID() ) ) {
				return;
			}
			global $user_ID;
			$login_text = __( 'please login to review', 'wp-travel' );
			$link       = '';
			if ( get_option( 'comment_registration' ) && ! $user_ID ) {
				$link = '<a rel="nofollow" href="' . wp_login_url( get_permalink() ) . '">' . $login_text . '</a>';
			} else {

				$link = "<a class='comment-reply-link' href='" . esc_url( add_query_arg( 'replytocom', $comment->comment_ID ) ) . '#respond' . "' onclick='return addComment.moveForm(\"comment-$comment->comment_ID\", \"$comment->comment_ID\", \"respond\", \"$post_id\")'>" . esc_html__( 'Reply', 'wp-travel' ) . '</a>';
			}
			echo wp_kses_post( apply_filters( 'wp_travel_comment_reply_link', $link ) );
			?>
			</div>
			<?php endif; ?>
			<?php do_action( 'wp_travel_review_after_comment_text', $comment ); ?>

		</div>
	</div>
