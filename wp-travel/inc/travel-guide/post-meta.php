<?php
/**
 * Travel Guide CPT Meta Box.
 *
 * CPT: travel-guide
 *
 * Stores all guide information in post meta.
 *
 * @package Tour_Guide_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Travel Guide meta keys.
 *
 * @return array
 */
function wptravel_guide_meta_keys() {

	return array(
		'user_slogan'          => 'wp_travel_guide_user_slogan',
		'guide_unique_id'      => 'wp_travel_guide_unique_id',
		'user_phone'           => 'wp_travel_guide_phone',
		'user_email'           => 'wp_travel_guide_email',
		'city'                 => 'wp_travel_guide_city',
		'country'              => 'wp_travel_guide_country',
		'age'                  => 'wp_travel_guide_age',
		'join_year'            => 'wp_travel_guide_join_year',
		'facebook_link'        => 'wp_travel_guide_facebook_link',
		'instagram_link'       => 'wp_travel_guide_instagram_link',
		'twitter_link'         => 'wp_travel_guide_twitter_link',
		'linkedin_link'        => 'wp_travel_guide_linkedin_link',
		'youtube_link'         => 'wp_travel_guide_youtube_link',
		'tiktok_link'          => 'wp_travel_guide_tiktok_link',
		'profile_picture'      => 'wp_travel_guide_profile_picture',
		'profile_picture_url'  => 'wp_travel_guide_profile_picture_url',
		'language'             => 'wp_travel_guide_language',
		'gender'               => 'wp_travel_guide_gender',
		'description'          => 'wp_travel_guide_description',
		'biography'            => 'wp_travel_guide_biography',
		'trip_list'            => 'wp_travel_guide_trip_list',
		'added_by'             => 'wp_travel_guide_added_by',
	);
}


/**
 * Check whether Travel Guide Pro fields should be available.
 *
 * Reads the "WP Travel Travel Guide" module's enabled state from
 * the WP Travel settings option. Falls back to the plugin's own
 * pro-detection function if that module entry isn't present
 * (e.g. a different settings shape), and finally to a filterable
 * default of false.
 *
 * @return bool
 */
function wptravel_guide_is_pro_active() {

    if( !class_exists( 'WP_Travel_Pro' ) ){
        return false;
    }

	$settings = wptravel_get_settings();

	if ( isset( $settings['modules']['show_wp_travel_tour_guide']['value'] ) ) {

		$module = $settings['modules']['show_wp_travel_tour_guide'];
		

		if ( ! empty( $module['force_enable'] ) ) {
			return true;
		}

		return 'yes' === $module['value'];
	}


	/**
	 * Filters whether Travel Guide Pro-only fields are active.
	 *
	 * @param bool $is_pro_active Default false.
	 */
	return false;
}


/**
 * Get the list of Travel Guide meta fields reserved for Pro.
 *
 * These field keys correspond to the keys returned by
 * wptravel_guide_meta_keys(), plus 'social_links' as a group key
 * covering all social link fields.
 *
 * @return array
 */
function wptravel_guide_pro_fields() {

	return array(
		'biography',
		'age',
		'join_year',
		'user_email',
		'user_phone',
		// 'trip_list',
		'facebook_link',
		'instagram_link',
		'twitter_link',
		'linkedin_link',
		'youtube_link',
		'tiktok_link',
	);
}


/**
 * Check whether a given Travel Guide field is Pro-only and locked.
 *
 * Returns true only when the field is in the Pro list AND Pro is
 * not active, meaning the field should be hidden/blocked.
 *
 * @param string $field Field key, matching wptravel_guide_meta_keys().
 * @return bool
 */
function wptravel_guide_is_field_locked( $field ) {

	if ( wptravel_guide_is_pro_active() ) {
		return false;
	}

	return in_array( $field, wptravel_guide_pro_fields(), true );
}


/**
 * Remove default post type support for boxes not used on
 * Travel Guide: main content editor, featured image, excerpt,
 * and comments/discussion.
 *
 * Must run after the 'travel-guide' post type is registered,
 * so it hooks into 'init' at a later priority.
 */
function wptravel_guide_remove_default_supports() {

	remove_post_type_support( 'travel-guide', 'editor' );
	remove_post_type_support( 'travel-guide', 'thumbnail' );
	remove_post_type_support( 'travel-guide', 'excerpt' );
	add_post_type_support( 'travel-guide', 'comments' );
}
add_action( 'init', 'wptravel_guide_remove_default_supports', 20 );


/**
 * Explicitly remove the Discussion and Comments meta boxes.
 *
 * The Comments meta box ("commentsdiv") can still be added by
 * WordPress core even after 'comments' support is removed if the
 * post already has comments, so it is removed explicitly here as
 * a safety net alongside the support removal above.
 */
function wptravel_guide_remove_meta_boxes() {

	// remove_meta_box( 'commentstatusdiv', 'travel-guide', 'normal' );
	// remove_meta_box( 'commentsdiv', 'travel-guide', 'normal' );
	remove_meta_box( 'postimagediv', 'travel-guide', 'side' );
}
add_action( 'add_meta_boxes', 'wptravel_guide_remove_meta_boxes', 20 );


/**
 * Add Travel Guide meta box.
 */
function wptravel_add_guide_meta_box() {

	add_meta_box(
		'wptravel_guide_details',
		__( 'Travel Guide Details', 'wp-travel-pro' ),
		'wptravel_guide_meta_box_callback',
		'travel-guide',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_travel-guide', 'wptravel_add_guide_meta_box' );


/**
 * Enqueue media uploader on Travel Guide edit screen.
 *
 * @param string $hook Current admin page.
 */
function wptravel_guide_admin_scripts( $hook ) {

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'travel-guide' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'jquery' );
}
add_action( 'admin_enqueue_scripts', 'wptravel_guide_admin_scripts' );


/**
 * Render Travel Guide meta box.
 *
 * @param WP_Post $post Current post.
 */
function wptravel_guide_meta_box_callback( $post ) {

	$meta = wptravel_guide_meta_keys();

	/*
	 * Nonce.
	 */
	wp_nonce_field(
		'wptravel_save_guide_details',
		'wptravel_guide_details_nonce'
	);

	/*
	 * Retrieve meta.
	 */
	$user_slogan     = get_post_meta( $post->ID, $meta['user_slogan'], true );
	$guide_unique_id = get_post_meta( $post->ID, $meta['guide_unique_id'], true );
	$user_phone      = get_post_meta( $post->ID, $meta['user_phone'], true );
	$user_email      = get_post_meta( $post->ID, $meta['user_email'], true );
	$city            = get_post_meta( $post->ID, $meta['city'], true );
	$country         = get_post_meta( $post->ID, $meta['country'], true );
	$age             = get_post_meta( $post->ID, $meta['age'], true );
	$join_year       = get_post_meta( $post->ID, $meta['join_year'], true );

	$facebook_link  = get_post_meta( $post->ID, $meta['facebook_link'], true );
	$instagram_link = get_post_meta( $post->ID, $meta['instagram_link'], true );
	$twitter_link   = get_post_meta( $post->ID, $meta['twitter_link'], true );
	$linkedin_link  = get_post_meta( $post->ID, $meta['linkedin_link'], true );
	$youtube_link   = get_post_meta( $post->ID, $meta['youtube_link'], true );
	$tiktok_link    = get_post_meta( $post->ID, $meta['tiktok_link'], true );

	$profile_picture = absint(
		get_post_meta(
			$post->ID,
			$meta['profile_picture'],
			true
		)
	);

	/*
	 * Keep profile_picture_url for backward compatibility.
	 * It should no longer be trusted as an input value.
	 */
	$profile_picture_url = '';

	if ( $profile_picture ) {
		$profile_picture_url = wp_get_attachment_image_url(
			$profile_picture,
			'full'
		);
	}

	if ( ! $profile_picture_url ) {
		$profile_picture_url = esc_url(
			get_post_meta(
				$post->ID,
				$meta['profile_picture_url'],
				true
			)
		);
	}

	$language    = get_post_meta( $post->ID, $meta['language'], true );
	$gender      = get_post_meta( $post->ID, $meta['gender'], true );
	$description = get_post_meta( $post->ID, $meta['description'], true );
	$biography   = get_post_meta( $post->ID, $meta['biography'], true );

	$trip_list = get_post_meta(
		$post->ID,
		$meta['trip_list'],
		true
	);

	$trip_list = is_array( $trip_list )
		? array_values( array_filter( array_map( 'absint', $trip_list ) ) )
		: array();

	$added_by = absint(
		get_post_meta(
			$post->ID,
			$meta['added_by'],
			true
		)
	);

	?>

	<div class="wptravel-guide-meta-box">

		<h3>
			<?php esc_html_e( 'Basic Information', 'wp-travel-pro' ); ?>
		</h3>

		<table class="form-table">

			<tr>
				<th>
					<label for="wptravel_guide_user_slogan">
						<?php esc_html_e( 'Slogan', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="text"
						id="wptravel_guide_user_slogan"
						name="wptravel_guide_user_slogan"
						value="<?php echo esc_attr( $user_slogan ); ?>"
						class="widefat"
					/>
				</td>
			</tr>

			<tr>
				<th>
					<label for="wptravel_guide_description">
						<?php esc_html_e( 'Description', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<textarea
						id="wptravel_guide_description"
						name="wptravel_guide_description"
						rows="5"
						class="widefat"
					><?php echo esc_textarea( $description ); ?></textarea>
				</td>
			</tr>

			<?php if ( ! wptravel_guide_is_field_locked( 'biography' ) ) : ?>
			<tr>
				<th>
					<label for="wptravel_guide_biography">
						<?php esc_html_e( 'Biography', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<?php
					wp_editor(
						$biography,
						'wptravel_guide_biography',
						array(
							'textarea_name' => 'wptravel_guide_biography',
							'media_buttons' => true,
							'textarea_rows' => 10,
							'teeny'         => false,
						)
					);
					?>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th>
					<?php esc_html_e( 'Biography', 'wp-travel-pro' ); ?>
				</th>
				<td>
					<p class="description">
						<?php esc_html_e( 'Available in WP Travel Pro.', 'wp-travel-pro' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>

			<tr>
				<th>
					<label for="wptravel_guide_city">
						<?php esc_html_e( 'City', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="text"
						id="wptravel_guide_city"
						name="wptravel_guide_city"
						value="<?php echo esc_attr( $city ); ?>"
						class="widefat"
					/>
				</td>
			</tr>

			<tr>
				<th>
					<label for="wptravel_guide_country">
						<?php esc_html_e( 'Country', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="text"
						id="wptravel_guide_country"
						name="wptravel_guide_country"
						value="<?php echo esc_attr( $country ); ?>"
						class="widefat"
					/>
				</td>
			</tr>

			<tr>
				<th>
					<label for="wptravel_guide_language">
						<?php esc_html_e( 'Language', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="text"
						id="wptravel_guide_language"
						name="wptravel_guide_language"
						value="<?php echo esc_attr( $language ); ?>"
						class="widefat"
					/>
				</td>
			</tr>

			<tr>
				<th>
					<label for="wptravel_guide_gender">
						<?php esc_html_e( 'Gender', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<select
						id="wptravel_guide_gender"
						name="wptravel_guide_gender"
						class="widefat"
					>
						<option value="">
							<?php esc_html_e( 'Select Gender', 'wp-travel-pro' ); ?>
						</option>

						<option
							value="male"
							<?php selected( $gender, 'male' ); ?>
						>
							<?php esc_html_e( 'Male', 'wp-travel-pro' ); ?>
						</option>

						<option
							value="female"
							<?php selected( $gender, 'female' ); ?>
						>
							<?php esc_html_e( 'Female', 'wp-travel-pro' ); ?>
						</option>

						<option
							value="other"
							<?php selected( $gender, 'other' ); ?>
						>
							<?php esc_html_e( 'Other', 'wp-travel-pro' ); ?>
						</option>
					</select>
				</td>
			</tr>

			<?php if ( ! wptravel_guide_is_field_locked( 'age' ) ) : ?>
			<tr>
				<th>
					<label for="wptravel_guide_age">
						<?php esc_html_e( 'Age', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="number"
						id="wptravel_guide_age"
						name="wptravel_guide_age"
						value="<?php echo esc_attr( $age ); ?>"
						min="0"
						max="150"
						class="widefat"
					/>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th>
					<?php esc_html_e( 'Age', 'wp-travel-pro' ); ?>
				</th>
				<td>
					<p class="description">
						<?php esc_html_e( 'Available in WP Travel Pro.', 'wp-travel-pro' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>

			<?php if ( ! wptravel_guide_is_field_locked( 'join_year' ) ) : ?>
			<tr>
				<th>
					<label for="wptravel_guide_join_year">
						<?php esc_html_e( 'Join Year', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="number"
						id="wptravel_guide_join_year"
						name="wptravel_guide_join_year"
						value="<?php echo esc_attr( $join_year ); ?>"
						min="1900"
						max="<?php echo esc_attr( gmdate( 'Y' ) ); ?>"
						class="widefat"
					/>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th>
					<?php esc_html_e( 'Join Year', 'wp-travel-pro' ); ?>
				</th>
				<td>
					<p class="description">
						<?php esc_html_e( 'Available in WP Travel Pro.', 'wp-travel-pro' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( ! wptravel_guide_is_field_locked( 'user_phone' ) ) : ?>
			<tr>
				<th>
					<label for="wptravel_guide_user_phone">
						<?php esc_html_e( 'Phone', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="text"
						id="wptravel_guide_user_phone"
						name="wptravel_guide_user_phone"
						value="<?php echo esc_attr( $user_phone ); ?>"
						class="widefat"
					/>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th>
					<?php esc_html_e( 'Phone', 'wp-travel-pro' ); ?>
				</th>
				<td>
					<p class="description">
						<?php esc_html_e( 'Available in WP Travel Pro.', 'wp-travel-pro' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( ! wptravel_guide_is_field_locked( 'user_email' ) ) : ?>
			<tr>
				<th>
					<label for="wptravel_guide_user_email">
						<?php esc_html_e( 'Email', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="email"
						id="wptravel_guide_user_email"
						name="wptravel_guide_user_email"
						value="<?php echo esc_attr( $user_email ); ?>"
						class="widefat"
					/>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th>
					<?php esc_html_e( 'Email', 'wp-travel-pro' ); ?>
				</th>
				<td>
					<p class="description">
						<?php esc_html_e( 'Available in WP Travel Pro.', 'wp-travel-pro' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>

		</table>

		<hr />

		<h3>
			<?php esc_html_e( 'Profile Picture', 'wp-travel-pro' ); ?>
		</h3>

		<table class="form-table">

			<tr>
				<th>
					<label>
						<?php esc_html_e( 'Profile Image', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>

					<div
						id="wptravel-guide-image-preview"
						style="margin-bottom:10px;"
					>
						<?php
						if ( $profile_picture ) {

							echo wp_get_attachment_image(
								$profile_picture,
								'thumbnail',
								false,
								array(
									'style' => 'max-width:150px;height:auto;',
									'alt'   => '',
								)
							);

						} elseif ( $profile_picture_url ) {
							?>
							<img
								src="<?php echo esc_url( $profile_picture_url ); ?>"
								alt=""
								style="max-width:150px;height:auto;"
							/>
							<?php
						}
						?>
					</div>

					<input
						type="hidden"
						id="wptravel_guide_profile_picture"
						name="wptravel_guide_profile_picture"
						value="<?php echo esc_attr( $profile_picture ); ?>"
					/>

					<button
						type="button"
						class="button"
						id="wptravel-guide-upload-image"
					>
						<?php esc_html_e( 'Select Image', 'wp-travel-pro' ); ?>
					</button>

					<button
						type="button"
						class="button"
						id="wptravel-guide-remove-image"
					>
						<?php esc_html_e( 'Remove Image', 'wp-travel-pro' ); ?>
					</button>

				</td>
			</tr>

		</table>

		<hr />

		<h3>
			<?php esc_html_e( 'Social Links', 'wp-travel-pro' ); ?>
		</h3>

		<?php if ( wptravel_guide_is_pro_active() ) : ?>

		<table class="form-table">

			<?php
			$social_fields = array(
				'facebook_link'  => __( 'Facebook', 'wp-travel-pro' ),
				'instagram_link' => __( 'Instagram', 'wp-travel-pro' ),
				'twitter_link'   => __( 'Twitter', 'wp-travel-pro' ),
				'linkedin_link'  => __( 'LinkedIn', 'wp-travel-pro' ),
				'youtube_link'   => __( 'YouTube', 'wp-travel-pro' ),
				'tiktok_link'    => __( 'TikTok', 'wp-travel-pro' ),
			);

			foreach ( $social_fields as $field => $label ) {

				$value = get_post_meta(
					$post->ID,
					$meta[ $field ],
					true
				);
				?>

				<tr>
					<th>
						<label for="wptravel_guide_<?php echo esc_attr( $field ); ?>">
							<?php echo esc_html( $label ); ?>
						</label>
					</th>

					<td>
						<input
							type="url"
							id="wptravel_guide_<?php echo esc_attr( $field ); ?>"
							name="wptravel_guide_<?php echo esc_attr( $field ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							class="widefat"
							placeholder="https://"
						/>
					</td>
				</tr>

				<?php
			}
			?>

		</table>

		<?php else : ?>

			<p class="description">
				<?php esc_html_e( 'Social links are available in WP Travel Pro.', 'wp-travel-pro' ); ?>
			</p>

		<?php endif; ?>

		<hr />

		<h3>
			<?php esc_html_e( 'Featured Trips', 'wp-travel-pro' ); ?>
		</h3>

		<?php if ( ! wptravel_guide_is_field_locked( 'trip_list' ) ) : ?>

		<table class="form-table">

			<tr>
				<th>
					<label for="wptravel_guide_trip_list">
						<?php esc_html_e( 'Select Trips', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>

					<?php
					$trips = get_posts(
						array(
							'post_type'      => 'itineraries',
							'post_status'    => array( 'publish', 'draft' ),
							'posts_per_page' => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'no_found_rows'  => true,
						)
					);

					/*
					 * Backward compatibility for installations
					 * using "itinerary".
					 */
					if ( empty( $trips ) ) {

						$trips = get_posts(
							array(
								'post_type'      => 'itinerary',
								'post_status'    => array( 'publish', 'draft' ),
								'posts_per_page' => -1,
								'orderby'        => 'title',
								'order'          => 'ASC',
								'no_found_rows'  => true,
							)
						);
					}
					?>

					<select
						id="wptravel_guide_trip_list"
						name="wptravel_guide_trip_list[]"
						multiple
						size="8"
						style="width:100%;max-width:500px;"
					>

						<?php foreach ( $trips as $trip ) : ?>

							<option
								value="<?php echo esc_attr( $trip->ID ); ?>"
								<?php selected( in_array( $trip->ID, $trip_list, true ), true ); ?>
							>
								<?php echo esc_html( $trip->post_title ); ?>
							</option>

						<?php endforeach; ?>

					</select>

					<p class="description">
						<?php
						esc_html_e(
							'Hold Ctrl/Cmd to select multiple trips.',
							'wp-travel-pro'
						);
						?>
					</p>

				</td>
			</tr>

		</table>

		<?php else : ?>

			<p class="description">
				<?php esc_html_e( 'Featured trips are available in WP Travel Pro.', 'wp-travel-pro' ); ?>
			</p>

		<?php endif; ?>

		<hr />

		<h3>
			<?php esc_html_e( 'System Information', 'wp-travel-pro' ); ?>
		</h3>

		<table class="form-table">

			<tr>
				<th>
					<label for="wptravel_guide_unique_id">
						<?php esc_html_e( 'Guide Unique ID', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>
					<input
						type="text"
						id="wptravel_guide_unique_id"
						name="wptravel_guide_unique_id"
						value="<?php echo esc_attr( $guide_unique_id ); ?>"
						class="widefat"
					/>

					<p class="description">
						<?php
						esc_html_e(
							'Leave blank to generate automatically when saving.',
							'wp-travel-pro'
						);
						?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label>
						<?php esc_html_e( 'Added By', 'wp-travel-pro' ); ?>
					</label>
				</th>

				<td>

					<?php
					$added_by_user = $added_by
						? get_userdata( $added_by )
						: false;

					$added_by_name = $added_by_user
						? $added_by_user->display_name
						: '';
					?>

					<input
						type="text"
						value="<?php echo esc_attr( $added_by_name ); ?>"
						class="widefat"
						readonly
					/>

					<p class="description">
						<?php
						esc_html_e(
							'Automatically set to the user who creates the guide.',
							'wp-travel-pro'
						);
						?>
					</p>

				</td>
			</tr>

		</table>

	</div>

	<script>
	jQuery(document).ready(function($) {

		var mediaUploader;

		$('#wptravel-guide-upload-image').on('click', function(e) {

			e.preventDefault();

			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media({
				title: '<?php echo esc_js( __( 'Select Profile Image', 'wp-travel-pro' ) ); ?>',
				button: {
					text: '<?php echo esc_js( __( 'Use Image', 'wp-travel-pro' ) ); ?>'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			mediaUploader.on('select', function() {

				var attachment = mediaUploader.state()
					.get('selection')
					.first()
					.toJSON();

				if (!attachment || !attachment.id) {
					return;
				}

				var imageUrl = attachment.url;

				if (
					attachment.sizes &&
					attachment.sizes.thumbnail
				) {
					imageUrl = attachment.sizes.thumbnail.url;
				}

				$('#wptravel_guide_profile_picture')
					.val(parseInt(attachment.id, 10));

				$('#wptravel-guide-image-preview').html(
					$('<img>', {
						src: imageUrl,
						alt: '',
						css: {
							maxWidth: '150px',
							height: 'auto'
						}
					})
				);
			});

			mediaUploader.open();

		});


		$('#wptravel-guide-remove-image').on('click', function(e) {

			e.preventDefault();

			$('#wptravel_guide_profile_picture').val('');

			$('#wptravel-guide-image-preview').empty();

		});

	});
	</script>

	<?php
}


/**
 * Sanitize Travel Guide URL.
 *
 * Only HTTP and HTTPS URLs are allowed.
 *
 * @param string $url URL.
 * @return string
 */
function wptravel_sanitize_guide_url( $url ) {

	$url = esc_url_raw( $url );

	if ( empty( $url ) ) {
		return '';
	}

	$scheme = wp_parse_url(
		$url,
		PHP_URL_SCHEME
	);

	if (
		! in_array(
			strtolower( (string) $scheme ),
			array( 'http', 'https' ),
			true
		)
	) {
		return '';
	}

	return $url;
}


/**
 * Get supported Travel Guide trip post types.
 *
 * @return array
 */
function wptravel_get_guide_trip_post_types() {

	$post_types = array();

	if ( post_type_exists( 'itineraries' ) ) {
		$post_types[] = 'itineraries';
	}

	if ( post_type_exists( 'itinerary' ) ) {
		$post_types[] = 'itinerary';
	}

	return $post_types;
}


/**
 * Validate Travel Guide trip.
 *
 * @param int $trip_id Trip ID.
 * @return bool
 */
function wptravel_is_valid_guide_trip( $trip_id ) {

	$trip_id = absint( $trip_id );

	if ( ! $trip_id ) {
		return false;
	}

	$trip_post_type = get_post_type( $trip_id );

	if (
		! $trip_post_type ||
		! in_array(
			$trip_post_type,
			wptravel_get_guide_trip_post_types(),
			true
		)
	) {
		return false;
	}

	$status = get_post_status( $trip_id );

	if ( ! $status ) {
		return false;
	}

	/*
	 * Published trips can be selected by users who can
	 * read the post. Non-public trips require edit access.
	 */
	if ( 'publish' === $status ) {

		return true;
	}

	return current_user_can(
		'edit_post',
		$trip_id
	);
}


/**
 * Update or delete Travel Guide meta.
 *
 * @param int    $post_id Post ID.
 * @param string $meta_key Meta key.
 * @param mixed  $value Value.
 */
function wptravel_update_guide_meta(
	$post_id,
	$meta_key,
	$value
) {

	if (
		'' === $value ||
		null === $value ||
		( is_array( $value ) && empty( $value ) )
	) {

		delete_post_meta(
			$post_id,
			$meta_key
		);

		return;
	}

	update_post_meta(
		$post_id,
		$meta_key,
		$value
	);
}


/**
 * Save Travel Guide meta securely.
 *
 * @param int $post_id Post ID.
 */
function wptravel_save_guide_details( $post_id ) {

	$post_id = absint( $post_id );

	if ( ! $post_id ) {
		return;
	}

	/*
	 * Verify post type.
	 */
	if ( 'travel-guide' !== get_post_type( $post_id ) ) {
		return;
	}

	/*
	 * Ignore autosave.
	 */
	if (
		defined( 'DOING_AUTOSAVE' ) &&
		DOING_AUTOSAVE
	) {
		return;
	}

	/*
	 * Ignore revisions.
	 */
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	/*
	 * Ignore autosave revisions.
	 */
	if ( wp_is_post_autosave( $post_id ) ) {
		return;
	}

	/*
	 * Verify nonce exists and is a string.
	 */
	if (
		! isset( $_POST['wptravel_guide_details_nonce'] ) ||
		! is_string( $_POST['wptravel_guide_details_nonce'] )
	) {
		return;
	}

	$nonce = sanitize_text_field(
		wp_unslash(
			$_POST['wptravel_guide_details_nonce']
		)
	);

	/*
	 * Verify nonce.
	 */
	if (
		! wp_verify_nonce(
			$nonce,
			'wptravel_save_guide_details'
		)
	) {
		return;
	}

	/*
	 * Verify current user can edit this guide.
	 */
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$meta = wptravel_guide_meta_keys();


	/**
	 * ---------------------------------------------------------
	 * Text fields.
	 * ---------------------------------------------------------
	 */
	$text_fields = array(
		'user_slogan',
		'user_phone',
		'city',
		'country',
		'language',
	);

	foreach ( $text_fields as $field ) {

		$input_name = 'wptravel_guide_' . $field;

		if ( ! isset( $_POST[ $input_name ] ) ) {
			continue;
		}

		if ( ! is_string( $_POST[ $input_name ] ) ) {
			continue;
		}

		$value = sanitize_text_field(
			wp_unslash(
				$_POST[ $input_name ]
			)
		);

		wptravel_update_guide_meta(
			$post_id,
			$meta[ $field ],
			$value
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Email.
	 * ---------------------------------------------------------
	 */
	if ( isset( $_POST['wptravel_guide_user_email'] ) ) {

		if ( ! is_string( $_POST['wptravel_guide_user_email'] ) ) {
			return;
		}

		$user_email = sanitize_email(
			wp_unslash(
				$_POST['wptravel_guide_user_email']
			)
		);

		if ( '' !== $user_email && ! is_email( $user_email ) ) {
			$user_email = '';
		}

		wptravel_update_guide_meta(
			$post_id,
			$meta['user_email'],
			$user_email
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Gender.
	 * ---------------------------------------------------------
	 */
	if ( isset( $_POST['wptravel_guide_gender'] ) ) {

		$gender = sanitize_key(
			wp_unslash(
				$_POST['wptravel_guide_gender']
			)
		);

		$allowed_gender = array(
			'male',
			'female',
			'other',
		);

		if (
			! in_array(
				$gender,
				$allowed_gender,
				true
			)
		) {
			$gender = '';
		}

		wptravel_update_guide_meta(
			$post_id,
			$meta['gender'],
			$gender
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Age.
	 *
	 * Pro-only. Silently ignored on free installs so posted
	 * data cannot be used to set this field without Pro.
	 * ---------------------------------------------------------
	 */
	if (
		! wptravel_guide_is_field_locked( 'age' ) &&
		isset( $_POST['wptravel_guide_age'] )
	) {

		$age = absint(
			wp_unslash(
				$_POST['wptravel_guide_age']
			)
		);

		if ( $age > 150 ) {
			$age = '';
		}

		wptravel_update_guide_meta(
			$post_id,
			$meta['age'],
			$age
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Join year.
	 *
	 * Pro-only. Silently ignored on free installs.
	 * ---------------------------------------------------------
	 */
	if (
		! wptravel_guide_is_field_locked( 'join_year' ) &&
		isset( $_POST['wptravel_guide_join_year'] )
	) {

		$join_year = absint(
			wp_unslash(
				$_POST['wptravel_guide_join_year']
			)
		);

		$current_year = (int) gmdate( 'Y' );

		if (
			$join_year < 1900 ||
			$join_year > $current_year
		) {
			$join_year = '';
		}

		wptravel_update_guide_meta(
			$post_id,
			$meta['join_year'],
			$join_year
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Social links.
	 *
	 * Pro-only. Silently ignored on free installs so posted
	 * data cannot be used to set these fields without Pro.
	 * ---------------------------------------------------------
	 */
	if ( wptravel_guide_is_pro_active() ) {

		$social_fields = array(
			'facebook_link',
			'instagram_link',
			'twitter_link',
			'linkedin_link',
			'youtube_link',
			'tiktok_link',
		);

		foreach ( $social_fields as $field ) {

			$input_name = 'wptravel_guide_' . $field;

			if ( ! isset( $_POST[ $input_name ] ) ) {
				continue;
			}

			if ( ! is_string( $_POST[ $input_name ] ) ) {
				continue;
			}

			$value = wptravel_sanitize_guide_url(
				wp_unslash(
					$_POST[ $input_name ]
				)
			);

			wptravel_update_guide_meta(
				$post_id,
				$meta[ $field ],
				$value
			);
		}
	}


	/**
	 * ---------------------------------------------------------
	 * Profile picture.
	 *
	 * Never trust profile_picture_url from POST.
	 * The URL is generated from the validated attachment ID.
	 * ---------------------------------------------------------
	 */
	if ( isset( $_POST['wptravel_guide_profile_picture'] ) ) {

		$profile_picture = absint(
			wp_unslash(
				$_POST['wptravel_guide_profile_picture']
			)
		);

		if ( $profile_picture ) {

			$attachment = get_post(
				$profile_picture
			);

			$mime_type = get_post_mime_type(
				$profile_picture
			);

			$is_image = (
				$mime_type &&
				0 === strpos(
					$mime_type,
					'image/'
				)
			);

			$is_valid_attachment = (
				$attachment &&
				'attachment' === $attachment->post_type &&
				$is_image &&
				current_user_can(
					'edit_post',
					$profile_picture
				)
			);

			if ( $is_valid_attachment ) {

				update_post_meta(
					$post_id,
					$meta['profile_picture'],
					$profile_picture
				);

				/*
				 * Store the generated URL only for backward
				 * compatibility with existing code.
				 */
				$profile_picture_url = wp_get_attachment_image_url(
					$profile_picture,
					'full'
				);

				if ( $profile_picture_url ) {

					update_post_meta(
						$post_id,
						$meta['profile_picture_url'],
						esc_url_raw( $profile_picture_url )
					);

				} else {

					delete_post_meta(
						$post_id,
						$meta['profile_picture_url']
					);
				}

			} else {

				delete_post_meta(
					$post_id,
					$meta['profile_picture']
				);

				delete_post_meta(
					$post_id,
					$meta['profile_picture_url']
				);
			}

		} else {

			delete_post_meta(
				$post_id,
				$meta['profile_picture']
			);

			delete_post_meta(
				$post_id,
				$meta['profile_picture_url']
			);
		}
	}


	/**
	 * ---------------------------------------------------------
	 * Description.
	 * ---------------------------------------------------------
	 */
	if ( isset( $_POST['wptravel_guide_description'] ) ) {

		if ( ! is_string( $_POST['wptravel_guide_description'] ) ) {
			return;
		}

		$description = sanitize_textarea_field(
			wp_unslash(
				$_POST['wptravel_guide_description']
			)
		);

		wptravel_update_guide_meta(
			$post_id,
			$meta['description'],
			$description
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Biography.
	 *
	 * Pro-only. Rich HTML is allowed but sanitized through
	 * wp_kses_post(). Silently ignored on free installs.
	 * ---------------------------------------------------------
	 */
	if (
		! wptravel_guide_is_field_locked( 'biography' ) &&
		isset( $_POST['wptravel_guide_biography'] )
	) {

		if ( ! is_string( $_POST['wptravel_guide_biography'] ) ) {
			return;
		}

		$biography = wp_kses_post(
			wp_unslash(
				$_POST['wptravel_guide_biography']
			)
		);

		wptravel_update_guide_meta(
			$post_id,
			$meta['biography'],
			$biography
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Featured trips.
	 *
	 * Pro-only. Silently ignored (existing value left untouched)
	 * on free installs so posted data cannot be used to set this
	 * field without Pro.
	 * ---------------------------------------------------------
	 */
	if ( wptravel_guide_is_field_locked( 'trip_list' ) ) {

		// Do nothing: leave any existing trip_list meta untouched.

	} elseif ( isset( $_POST['wptravel_guide_trip_list'] ) ) {

		$submitted_trip_list = wp_unslash(
			$_POST['wptravel_guide_trip_list']
		);

		if ( ! is_array( $submitted_trip_list ) ) {
			$submitted_trip_list = array();
		}

		$submitted_trip_list = array_map(
			'absint',
			$submitted_trip_list
		);

		$submitted_trip_list = array_values(
			array_unique(
				array_filter(
					$submitted_trip_list
				)
			)
		);

		$valid_trip_list = array();

		foreach ( $submitted_trip_list as $trip_id ) {

			if (
				wptravel_is_valid_guide_trip(
					$trip_id
				)
			) {
				$valid_trip_list[] = $trip_id;
			}
		}

		$valid_trip_list = array_values(
			array_unique(
				$valid_trip_list
			)
		);

		wptravel_update_guide_meta(
			$post_id,
			$meta['trip_list'],
			$valid_trip_list
		);

	} else {

		/*
		 * If the multi-select was submitted without any value,
		 * clear existing trips.
		 */
		delete_post_meta(
			$post_id,
			$meta['trip_list']
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Guide unique ID.
	 * ---------------------------------------------------------
	 */
	if ( isset( $_POST['wptravel_guide_unique_id'] ) ) {

		if ( ! is_string( $_POST['wptravel_guide_unique_id'] ) ) {
			return;
		}

		$guide_unique_id = sanitize_text_field(
			wp_unslash(
				$_POST['wptravel_guide_unique_id']
			)
		);

		/*
		 * Generate a deterministic unique ID when empty.
		 */
		if ( '' === $guide_unique_id ) {
			$guide_unique_id = 'guide-' . $post_id;
		}

		update_post_meta(
			$post_id,
			$meta['guide_unique_id'],
			$guide_unique_id
		);
	}


	/**
	 * ---------------------------------------------------------
	 * Added By.
	 *
	 * NEVER accept this value from $_POST.
	 * ---------------------------------------------------------
	 */
	$existing_added_by = absint(
		get_post_meta(
			$post_id,
			$meta['added_by'],
			true
		)
	);

	if ( ! $existing_added_by ) {

		$current_user_id = get_current_user_id();

		if ( $current_user_id ) {

			update_post_meta(
				$post_id,
				$meta['added_by'],
				absint( $current_user_id )
			);
		}
	}
}

add_action(	'save_post_travel-guide', 'wptravel_save_guide_details', 10, 1 );