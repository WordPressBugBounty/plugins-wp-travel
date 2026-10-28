<?php
/**
 * Single Guide Page
 *
 * @see     http://docs.wensolutions.com/document/template-structure/
 * @author  WenSolutions
 * @package WP_Travel_travel_guide
 * @since   1.0.0
 */



if( !wp_is_block_theme() ){
	get_header();

	?>
	<div class="wrapper-tg wp-travel-single-guide">
		<?php do_action( 'wp_travel_travel_guide_single_guide_template' ); ?>
	</div> 


	<?php
	/**
	 * Calling Single Guide Template Hook
	 */
	do_action( 'wp_travel_travel_guide_single_guide_featured_trip' );

	?>

	<script>
		document.querySelectorAll('a[href^="#"]').forEach(anchor => {
			anchor.addEventListener('click', function(e) {
				e.preventDefault();

				document.querySelector(this.getAttribute('href')).scrollIntoView({
					behavior: 'smooth'
				});
			});
		});
	</script>

	<?php
	get_footer();
	}else{
	?>

	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo('charset'); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<?php
			$title = get_the_title();
			$site_name = get_bloginfo('name');
			$page_title = $title . ' - ' . $site_name;
			?>
			<title><?php echo esc_html( $page_title ); ?></title>
			<?php
			/*
			You have to run the do_blocks() between the <head></head> tags in order
			for WordPress to load the corresponding CSS.
			*/
			ob_start();
			block_header_area();
			$str = ob_get_clean();
			$block_header = do_blocks($str);
			// Spacer block.
			$str = '<div 
						style="height:32px" 
						aria-hidden="true" 
						class="wp-block-spacer"
					></div>';
			$block_spacer = do_blocks($str);
			// Content block.
			$block_content = do_blocks(
				'<!-- wp:group {"layout":{"type":"constrained"}} -->
				<div class="wp-block-group">
				<!-- wp:post-content /-->
				</div>
				<!-- /wp:group -->'
			);
			ob_start();
			block_footer_area();
			$str = ob_get_clean();
			$block_footer = do_blocks($str);
			wp_head();
			?>
		</head>
		<body <?php body_class('block-theme-active'); ?>>
			<?php wp_body_open(); ?>
			<div class="wp-site-blocks">
				<header class="wp-block-template-part">
					<?php echo wp_kses_post( $block_header ); ?>
				</header>
				<main class="wptravel-content-wrapper is-layout-constrained" style="padding: 20px 0px 0px 0px;">
				<div class="wp-block-group alignfull">
					<div class="wrapper-tg wp-travel-single-guide">
						<?php do_action( 'wp_travel_travel_guide_single_guide_template' ); ?>
					</div> 


					<?php
					/**
					 * Calling Single Guide Template Hook
					 */
					do_action( 'wp_travel_travel_guide_single_guide_featured_trip' );

					?>

					<script>
						document.querySelectorAll('a[href^="#"]').forEach(anchor => {
							anchor.addEventListener('click', function(e) {
								e.preventDefault();

								document.querySelector(this.getAttribute('href')).scrollIntoView({
									behavior: 'smooth'
								});
							});
						});
					</script>
				</div>
				</main>
				<footer class="wp-block-template-part site-footer">
					<?php echo wp_kses_post( $block_footer ); ?>
				</footer>
			</div>
			<?php wp_footer(); ?>
		</body>
	</html>
	<?php 
	}