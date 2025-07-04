<?php
/**
 * EmailTemplate Functions.
 *
 * @package WP_Travel
 */

/**
 * Booking Admin Email Default template
 *
 * @return HTML
 */
function wptravel_booking_admin_default_email_content() {

	ob_start();

	?>
		<table class="wp-travel-wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;">
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {sitename} <?php echo 'Admin'; ?>,</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'You have received bookings from'; ?> {customer_name}:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: <a href="{booking_edit_link}" target="_blank" style="color: #5a418b;text-decoration: none;">#{booking_id}</a> ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{booking_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{traveler_details}</td>
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Note'; ?></b></td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_note}</td>
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="center">				
					<a href="{booking_edit_link}" class="wp-travel-veiw-more" target="_blank" style="color: #fcfffd;text-decoration: none;background: #dd402e;border-radius: 3px;display: block;font-size: 14px;margin: 20px auto;padding: 10px 20px;text-align: center;height: 30px;line-height: 30px;width: 200px;"><?php echo 'View details on site'; ?></a>
				</td>
			</tr>
		</table>	
	<?php

	$content = ob_get_contents();
	ob_end_clean();
	return $content;

}
/**
 * Booking Client Default Content.
 */
function wptravel_booking_client_default_email_content() {

	ob_start();

	?>

	<table class="wp-travel-wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;">
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {customer_name},</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Your booking has been received and is now being processed. Your order details are shown below for your reference'; ?>:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: <a href="{booking_edit_link}" target="_blank" style="color: #5a418b;text-decoration: none;">#{booking_id}</a> ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{booking_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{traveler_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Note'; ?></b></td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					<b><?php echo 'Customer Note'; ?></b>
				</td>
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_note}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{bank_deposit_table}</td>
			</tr>
	</table>

	<?php

	$content = ob_get_contents();

	ob_end_clean();

	return $content;

}

/**
 * Enqueries Admin Default Email.
 */
function wptravel_enquiries_admin_default_email_content() {
	$strings = array();
	if ( class_exists( 'WpTravel_Helpers_Strings' ) ) {
		$string = WpTravel_Helpers_Strings::get(); 
	}
	$strings = isset( $string['enquiry'] ) ? $string['enquiry'] : apply_filters( 'wp_travel_trip_enquiry_label', 'Enquiry' ) ;

	ob_start();

	?>
	<table class="wp-travel-wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;">
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {sitename} <?php echo 'Admin'; ?>,</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'You have received trip ' . esc_html( strtolower( $strings ) ) . ' from'; ?> {customer_name}:</p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo esc_html( $strings ) . ' Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					<b><?php echo 'Itinerary'; ?></b>

				</td>
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					<a href="{itinerary_link}" target="_blank" style="color: #5a418b;text-decoration: none;">{itinerary_title}</a>
				</td>	
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					<b><?php echo 'Name'; ?></b>

				</td>
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					{customer_name}
				</td>	
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					<b><?php echo 'E-mail'; ?></b>

				</td>
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					{customer_email}
				</td>	
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					<b><?php echo  esc_html( $strings ) . ' Message'; ?></b>

				</td>
				<td align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
					{customer_note}
				</td>	
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="center">			
					<a href="{enquery_edit_link}" class="wp-travel-veiw-more" target="_blank" style="color: #fcfffd;text-decoration: none;background: #dd402e;border-radius: 3px;display: block;font-size: 14px;margin: 20px auto;padding: 10px 20px;text-align: center;height: 30px;line-height: 30px;width: 200px;"><?php echo 'View details on site'; ?>
				</td>
			</tr>
		</table>

	<?php

	$content = ob_get_contents();

	ob_end_clean();

	return $content;

}
/**
 * Payment Default Email Admin Content.
 *
 * @return HTML
 */
function wptravel_payment_admin_default_email_content() {

	ob_start();

	?>
	<table class="wp-travel-wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;">
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {sitename} <?php echo 'Admin'; ?>,</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'You have received payment from'; ?> {customer_name}:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: <a href="{booking_edit_link}" target="_blank" style="color: #5a418b;text-decoration: none;">#{booking_id}</a> ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{booking_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{traveler_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_note}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{payment_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="center">				
					<a href="{booking_edit_link}" class="wp-travel-veiw-more" target="_blank" style="color: #fcfffd;text-decoration: none;background: #dd402e;border-radius: 3px;display: block;font-size: 14px;margin: 20px auto;padding: 10px 20px;text-align: center;height: 30px;line-height: 30px;width: 200px;"><?php echo 'View details on site'; ?></a>
				</td>
			</tr>
		</table>

	<?php

	$content = ob_get_contents();

	ob_end_clean();

	return $content;

}

/**
 * Payment default client email template.
 *
 * @return HTML
 */
function wptravel_payment_client_default_email_content() {

	ob_start();
	?>
	<table class="wp-travel-wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;">
			<tr class="wp-travel-content" style="background: #fff;">
				<td colspan="2" align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
				<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {customer_name},</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Your payment has been received.'; ?></p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: <a href="{booking_edit_link}" target="_blank" style="color: #5a418b;text-decoration: none;">#{booking_id}</a> ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{booking_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{traveler_details}</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td style="font-size: 14px; background: #fff; margin: 0; padding: 0px 0px 8px 25px;" colspan="2" align="left">{payment_details}</td>
			</tr>

		</table>

	<?php

	$content = ob_get_contents();

	ob_end_clean();

	return $content;

}


/**
 * Will return admin email template.
 *
 * @return HTML
 */
function wptravel_admin_email_template() {
	if ( class_exists( 'WpTravel_Helpers_Strings' ) ) {
		$strings = WpTravel_Helpers_Strings::get();
	}
	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo 'To admin'; ?></title>
		<style type="text/css">
			body{
				background: #fcfcfc ;
				color: #5d5d5d;
				margin: 0;
				padding: 0;
			}
			a{
				color: #5a418b;text-decoration: none;
			}
			.wp-travel-wrapper{
				color: #5d5d5d;
				font-family: Roboto, sans-serif;
				margin: auto;
			}
			.wp-travel-wrapper tr{background: #fff}
			.wp-travel-header td{
				background: #dd402e;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-header h2 {
				color: #fcfffd;
				font-size: 20px;
				margin: 0;
				padding: 0;
				text-align: center;
			}

			.wp-travel-content-top{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-content-top p{
				line-height: 1.55;
				font-size: 14px;
			}
			.wp-travel-content-title{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.wp-travel-content-title h3{font-size: 16px; line-height: 1; margin:0;margin-top: 30px}

			.wp-travel-content-head{width: 24%}
			.wp-travel-content-info{width: 76%}
			.wp-travel-content-head td,
			.wp-travel-content-info td{
				font-size: 14px;
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.full-width{width: 100%!important}

			.wp-travel-veiw-more{
				background: #dd402e;
				border-radius: 3px;
				color: #fcfffd;
				display:block;
				font-size: 14px;
				margin: 20px auto;			
				padding: 10px 20px;
				text-align: center;
				text-decoration: none;
				width: 130px;
			}

			.wp-travel-footer td{
				background: #eaebed;
				box-sizing: border-box;
				font-size: 14px;
				padding: 10px 25px;
			}

			@media screen and ( max-width:600px ){
				table[class="wp-travel-wrapper"] {width: 100%!important}
			}
			@media screen and ( max-width:480px ){
				table[class="wp-travel-content-head"],
				table[class="wp-travel-content-info"] {width: 100%!important;}
				table[class="wp-travel-content-info"]{margin-bottom: 10px}

			}
		</style>
	</head>
	<body style="background: #fcfcfc;color: #5d5d5d;margin: 0;padding: 0;">
		<!-- Wrapper -->
		<table class="wp-travel-wrapper" width="600" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;"> 
			<tr class="wp-travel-header" style="background: #fff;">			
				<td align="left" style="background: #dd402e;box-sizing: border-box;margin: 0;padding: 20px 25px;"> <!-- Header -->
					<h2 style="color: #fcfffd;font-size: 20px;margin: 0;padding: 0;text-align: center;"><?php echo 'New Bookings'; ?></h2>
				</td> <!-- /Header -->
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {sitename} <?php echo 'Admin'; ?>,</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'You have received bookings from'; ?> {customer_name}:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: <a href="{booking_edit_link}" target="_blank" style="color: #5a418b;text-decoration: none;">#{booking_id}</a> ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Booking Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Itinerary'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								<a href="{itinerary_link}" target="_blank" style="color: #5a418b;text-decoration: none;">{itinerary_title}</a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo $strings['bookings']['pax'] ? esc_html( $strings['bookings']['pax'] ) : 'Pax'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_no_of_pax}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Scheduled Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_scheduled_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Arrival Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_arrival_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Departure Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_departure_date}</td>
						</tr>
					</table>
				</td>
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Customer Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Name'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_name}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Country'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_country}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Address'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_address}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Phone'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_phone}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Email'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_email}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head full-width" align="left" cellspacing="0" cellpadding="0" style="width: 100%!important;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Note'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info full-width" align="left" cellspacing="0" cellpadding="0" style="width: 100%!important;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_note}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="center">				
					<p style="display:inline-block;margin:0 auto;width:100%"><a href="{booking_edit_link}" class="wp-travel-veiw-more" target="_blank" style="color: #fcfffd;text-decoration: none;background: #dd402e;border-radius: 3px;display: block;font-size: 14px;margin: 20px auto;padding: 10px 20px;text-align: center;width: 130px;"><?php echo 'View details on site'; ?></a></p>
				</td>
			</tr>
			<tr class="wp-travel-footer" style="background: #fff;">
				<td align="center" style="background: #eaebed;box-sizing: border-box;font-size: 14px;padding: 10px 25px;">
					<p>{sitename} - <?php echo 'Powered By'; ?>: <a href="http://wptravel.io/" target="_blank" style="color: #5a418b;text-decoration: none;"><?php echo 'WP Travel'; ?></a></p>
				</td>
			</tr>
		</table><!-- /Wrapper -->
	</body>
	</html>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

/**
 * Will return customer email template.
 *
 * @return HTML
 */
function wptravel_customer_email_template() {
	if ( class_exists( 'WpTravel_Helpers_Strings' ) ) {
		$strings = WpTravel_Helpers_Strings::get();
	}
	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo 'To admin'; ?></title>
		<style type="text/css">
			body{
				background: #fcfcfc ;
				color: #5d5d5d;
				margin: 0;
				padding: 0;
			}
			a{
				color: #5a418b;text-decoration: none;
			}
			.wp-travel-wrapper{
				color: #5d5d5d;
				font-family: Roboto, sans-serif;
				margin: auto;
			}
			.wp-travel-wrapper tr{background: #fff}
			.wp-travel-header td{
				background: #dd402e;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-header h2 {
				color: #fcfffd;
				font-size: 20px;
				margin: 0;
				padding: 0;
				text-align: center;
			}

			.wp-travel-content-top{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-content-top p{
				line-height: 1.55;
				font-size: 14px;
			}
			.wp-travel-content-title{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.wp-travel-content-title h3{font-size: 16px; line-height: 1; margin:0;margin-top: 30px}

			.wp-travel-content-head{width: 24%}
			.wp-travel-content-info{width: 76%}
			.wp-travel-content-head td,
			.wp-travel-content-info td{
				font-size: 14px;
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.full-width{width: 100%!important}

			.wp-travel-veiw-more{
				background: #dd402e;
				border-radius: 3px;
				color: #fcfffd;
				display:block;
				font-size: 14px;
				margin: 20px auto;			
				padding: 10px 20px;
				text-align: center;
				text-decoration: none;
				width: 130px;
			}

			.wp-travel-footer td{
				background: #eaebed;
				box-sizing: border-box;
				font-size: 14px;
				padding: 10px 25px;
			}

			@media screen and ( max-width:600px ){
				table[class="wp-travel-wrapper"] {width: 100%!important}
			}
			@media screen and ( max-width:480px ){
				table[class="wp-travel-content-head"],
				table[class="wp-travel-content-info"] {width: 100%!important;}
				table[class="wp-travel-content-info"]{margin-bottom: 10px}

			}
		</style>
	</head>
	<body style="background: #fcfcfc;color: #5d5d5d;margin: 0;padding: 0;">
		<!-- Wrapper -->
		<table class="wp-travel-wrapper" width="600" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;"> 
			<tr class="wp-travel-header" style="background: #fff;">			
				<td align="left" style="background: #dd402e;box-sizing: border-box;margin: 0;padding: 20px 25px;"> <!-- Header -->
					<h2 style="color: #fcfffd;font-size: 20px;margin: 0;padding: 0;text-align: center;"><?php echo 'Thank you for your booking.'; ?></h2>
				</td> <!-- /Header -->
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {customer_name},</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Your booking has been received and is now being processed. Your order details are shown below for your reference'; ?>:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: #{booking_id} ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Booking Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Itinerary'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								{itinerary_title}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo $strings['bookings']['pax'] ? esc_html( $strings['bookings']['pax'] ) : 'Pax'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_no_of_pax}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Scheduled Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_scheduled_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Arrival Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_arrival_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Departure Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_departure_date}</td>
						</tr>
					</table>
				</td>
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Your Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Name'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_name}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Country'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_country}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Address'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_address}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Phone'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_phone}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Email'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_email}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head full-width" align="left" cellspacing="0" cellpadding="0" style="width: 100%!important;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Note'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info full-width" align="left" cellspacing="0" cellpadding="0" style="width: 100%!important;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_note}</td>
						</tr>
					</table>
				</td>
			</tr>			
			<tr class="wp-travel-footer" style="background: #fff;">
				<td align="center" style="background: #eaebed;box-sizing: border-box;font-size: 14px;padding: 10px 25px;">
					<p>{sitename} - <?php echo 'Powered By'; ?>: <a href="http://wptravel.io/" target="_blank" style="color: #5a418b;text-decoration: none;"><?php echo 'WP Travel'; ?></a></p>
				</td>
			</tr>
		</table><!-- /Wrapper -->
	</body>
	</html>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

/**
 * Will return Enqueries admin email template.
 *
 * @return HTML
 */
function wptravel_enqueries_admin_email_template() {
	$strings = array();
	if ( class_exists( 'WpTravel_Helpers_Strings' ) ) {
		$string = WpTravel_Helpers_Strings::get(); 
	}
	$strings = isset( $string['enquiry'] ) ? $string['enquiry'] : apply_filters( 'wp_travel_trip_enquiry_label', 'Enquiry' ) ;

	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo 'To admin'; ?></title>
		<style type="text/css">
			body{
				background: #fcfcfc ;
				color: #5d5d5d;
				margin: 0;
				padding: 0;
			}
			a{
				color: #5a418b;text-decoration: none;
			}
			.wp-travel-wrapper{
				color: #5d5d5d;
				font-family: Roboto, sans-serif;
				margin: auto;
			}
			.wp-travel-wrapper tr{background: #fff}
			.wp-travel-header td{
				background: #dd402e;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-header h2 {
				color: #fcfffd;
				font-size: 20px;
				margin: 0;
				padding: 0;
				text-align: center;
			}

			.wp-travel-content-top{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-content-top p{
				line-height: 1.55;
				font-size: 14px;
			}
			.wp-travel-content-title{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.wp-travel-content-title h3{font-size: 16px; line-height: 1; margin:0;margin-top: 30px}

			.wp-travel-content-head{width: 24%}
			.wp-travel-content-info{width: 76%}
			.wp-travel-content-head td,
			.wp-travel-content-info td{
				font-size: 14px;
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.full-width{width: 100%!important}

			.wp-travel-veiw-more{
				background: #dd402e;
				border-radius: 3px;
				color: #fcfffd;
				display:block;
				font-size: 14px;
				margin: 20px auto;			
				padding: 10px 20px;
				text-align: center;
				text-decoration: none;
				width: 130px;
			}

			.wp-travel-footer td{
				background: #eaebed;
				box-sizing: border-box;
				font-size: 14px;
				padding: 10px 25px;
			}

			@media screen and ( max-width:600px ){
				table[class="wp-travel-wrapper"] {width: 100%!important}
			}
			@media screen and ( max-width:480px ){
				table[class="wp-travel-content-head"],
				table[class="wp-travel-content-info"] {width: 100%!important;}
				table[class="wp-travel-content-info"]{margin-bottom: 10px}

			}
		</style>
	</head>
	<body style="background: #fcfcfc;color: #5d5d5d;margin: 0;padding: 0;">
		<!-- Wrapper -->
		<table class="wp-travel-wrapper" width="600" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;"> 
			<tr class="wp-travel-header" style="background: #fff;">			
				<td align="left" style="background: #dd402e;box-sizing: border-box;margin: 0;padding: 20px 25px;"> <!-- Header -->
					<h2 style="color: #fcfffd;font-size: 20px;margin: 0;padding: 0;text-align: center;"><?php echo 'New Trip Enquiry'; ?></h2>
				</td> <!-- /Header -->
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {sitename} <?php echo 'Admin'; ?>,</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'You have received trip ' . esc_html( strtolower( $strings ) ). ' from'; ?> {customer_name}:</p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Booking Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Itinerary'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								<a href="{itinerary_link}" target="_blank" style="color: #5a418b;text-decoration: none;">{itinerary_title}</a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Customer Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Name'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_name}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Email'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_email}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head full-width" align="left" cellspacing="0" cellpadding="0" style="width: 100%!important;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo esc_html( $strings ) . ' Message'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info full-width" align="left" cellspacing="0" cellpadding="0" style="width: 100%!important;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{customer_note}</td>
						</tr>
					</table>
				</td>
			</tr>

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="center">				
					<a href="{enquery_edit_link}" class="wp-travel-veiw-more" target="_blank" style="color: #fcfffd;text-decoration: none;background: #dd402e;border-radius: 3px;display: block;font-size: 14px;margin: 20px auto;padding: 10px 20px;text-align: center;width: 130px;"><?php echo 'View details on site'; ?></a>
				</td>
			</tr>
			<tr class="wp-travel-footer" style="background: #fff;">
				<td align="center" style="background: #eaebed;box-sizing: border-box;font-size: 14px;padding: 10px 25px;">
					<p>{sitename} - <?php echo 'Powered By'; ?>: <a href="http://wptravel.io/" target="_blank" style="color: #5a418b;text-decoration: none;"><?php echo 'WP Travel'; ?></a></p>
				</td>
			</tr>
		</table><!-- /Wrapper -->
	</body>
	</html>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}


/**
 * Will return admin email template.
 *
 * @return HTML
 */
function wptravel_payment_email_template_admin() {
	if ( class_exists( 'WpTravel_Helpers_Strings' ) ) {
		$strings = WpTravel_Helpers_Strings::get();
	}
	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo 'To admin'; ?></title>
		<style type="text/css">
			body{
				background: #fcfcfc ;
				color: #5d5d5d;
				margin: 0;
				padding: 0;
			}
			a{
				color: #5a418b;text-decoration: none;
			}
			.wp-travel-wrapper{
				color: #5d5d5d;
				font-family: Roboto, sans-serif;
				margin: auto;
			}
			.wp-travel-wrapper tr{background: #fff}
			.wp-travel-header td{
				background: #dd402e;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-header h2 {
				color: #fcfffd;
				font-size: 20px;
				margin: 0;
				padding: 0;
				text-align: center;
			}

			.wp-travel-content-top{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-content-top p{
				line-height: 1.55;
				font-size: 14px;
			}
			.wp-travel-content-title{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.wp-travel-content-title h3{font-size: 16px; line-height: 1; margin:0;margin-top: 30px}

			.wp-travel-content-head{width: 24%}
			.wp-travel-content-info{width: 76%}
			.wp-travel-content-head td,
			.wp-travel-content-info td{
				font-size: 14px;
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.full-width{width: 100%!important}

			.wp-travel-veiw-more{
				background: #dd402e;
				border-radius: 3px;
				color: #fcfffd;
				display:block;
				font-size: 14px;
				margin: 20px auto;			
				padding: 10px 20px;
				text-align: center;
				text-decoration: none;
				width: 130px;
			}

			.wp-travel-footer td{
				background: #eaebed;
				box-sizing: border-box;
				font-size: 14px;
				padding: 10px 25px;
			}

			@media screen and ( max-width:600px ){
				table[class="wp-travel-wrapper"] {width: 100%!important}
			}
			@media screen and ( max-width:480px ){
				table[class="wp-travel-content-head"],
				table[class="wp-travel-content-info"] {width: 100%!important;}
				table[class="wp-travel-content-info"]{margin-bottom: 10px}

			}
		</style>
	</head>
	<body style="background: #fcfcfc;color: #5d5d5d;margin: 0;padding: 0;">
		<!-- Wrapper -->
		<table class="wp-travel-wrapper" width="600" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;"> 
			<tr class="wp-travel-header" style="background: #fff;">			
				<td align="left" style="background: #dd402e;box-sizing: border-box;margin: 0;padding: 20px 25px;"> <!-- Header -->
					<h2 style="color: #fcfffd;font-size: 20px;margin: 0;padding: 0;text-align: center;"><?php echo 'New Bookings Payment'; ?></h2>
				</td> <!-- /Header -->
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {sitename} <?php echo 'Admin'; ?>,</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'You have received payment from'; ?> {customer_name}:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: <a href="{booking_edit_link}" target="_blank" style="color: #5a418b;text-decoration: none;">#{booking_id}</a> ({booking_arrival_date})</b></p>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-title" style="background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">				
					<h3 style="font-size: 16px;line-height: 1;margin: 0;margin-top: 30px;"><b><?php echo 'Booking Details'; ?>:</b></h3>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Itinerary'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								<a href="{itinerary_link}" target="_blank" style="color: #5a418b;text-decoration: none;">{itinerary_title}</a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo $strings['bookings']['pax'] ? esc_html( $strings['bookings']['pax'] ) : 'Pax'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_no_of_pax}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Scheduled Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_scheduled_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Arrival Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_arrival_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Departure Date'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">{booking_departure_date}</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- /Payment Starts -->
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Payment Status'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
							{payment_status}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Payment Mode'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
							{payment_mode}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b> <?php echo 'Trip Price'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								{currency_symbol} {trip_price}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Payment Amount'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								{currency_symbol} {payment_amount}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- /Payment ends -->

			<tr class="wp-travel-content" style="background: #fff;">
				<td align="center">				
					<a href="{booking_edit_link}" class="wp-travel-veiw-more" target="_blank" style="color: #fcfffd;text-decoration: none;background: #dd402e;border-radius: 3px;display: block;font-size: 14px;margin: 20px auto;padding: 10px 20px;text-align: center;width: 130px;"><?php echo 'View details '; ?></a>
				</td>
			</tr>
			<tr class="wp-travel-footer" style="background: #fff;">
				<td align="center" style="background: #eaebed;box-sizing: border-box;font-size: 14px;padding: 10px 25px;">
					<p>{sitename} - <?php echo 'Powered By'; ?>: <a href="http://wptravel.io/" target="_blank" style="color: #5a418b;text-decoration: none;"><?php echo 'WP Travel'; ?></a></p>
				</td>
			</tr>
		</table><!-- /Wrapper -->
	</body>
	</html>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

/**
 * Will return customer email template.
 *
 * @return HTML
 */
function wptravel_payment_email_template_customer() {
	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo 'To customer'; ?></title>
		<style type="text/css">
			body{
				background: #fcfcfc;
				color: #5d5d5d;
				margin: 0;
				padding: 0;
			}
			a{
				color: #5a418b;text-decoration: none;
			}
			.wp-travel-wrapper{
				color: #5d5d5d;
				font-family: Roboto, sans-serif;
				margin: auto;
			}
			.wp-travel-wrapper tr{background: #fff}
			.wp-travel-header td{
				background: #dd402e;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-header h2 {
				color: #fcfffd;
				font-size: 20px;
				margin: 0;
				padding: 0;
				text-align: center;
			}

			.wp-travel-content-top{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 20px 25px;
			}
			.wp-travel-content-top p{
				line-height: 1.55;
				font-size: 14px;
			}
			.wp-travel-content-title{
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.wp-travel-content-title h3{font-size: 16px; line-height: 1; margin:0;margin-top: 30px}

			.wp-travel-content-head{width: 24%}
			.wp-travel-content-info{width: 76%}
			.wp-travel-content-head td,
			.wp-travel-content-info td{
				font-size: 14px;
				background: #fff;
				box-sizing: border-box;
				margin: 0;
				padding: 0px 0px 8px 25px;
			}
			.full-width{width: 100%!important}

			.wp-travel-veiw-more{
				background: #dd402e;
				border-radius: 3px;
				color: #fcfffd;
				display:block;
				font-size: 14px;
				margin: 20px auto;			
				padding: 10px 20px;
				text-align: center;
				text-decoration: none;
				width: 130px;
			}

			.wp-travel-footer td{
				background: #eaebed;
				box-sizing: border-box;
				font-size: 14px;
				padding: 10px 25px;
			}

			@media screen and ( max-width:600px ){
				table[class="wp-travel-wrapper"] {width: 100%!important}
			}
			@media screen and ( max-width:480px ){
				table[class="wp-travel-content-head"],
				table[class="wp-travel-content-info"] {width: 100%!important;}
				table[class="wp-travel-content-info"]{margin-bottom: 10px}

			}
		</style>
	</head>
	<body style="background: #fcfcfc;color: #5d5d5d;margin: 0;padding: 0;">
		<!-- Wrapper -->
		<table class="wp-travel-wrapper" width="600" cellpadding="0" cellspacing="0" style="color: #5d5d5d;font-family: Roboto, sans-serif;margin: auto;"> 
			<tr class="wp-travel-header" style="background: #fff;">			
				<td align="left" style="background: #dd402e;box-sizing: border-box;margin: 0;padding: 20px 25px;"> <!-- Header -->
					<h2 style="color: #fcfffd;font-size: 20px;margin: 0;padding: 0;text-align: center;"><?php echo 'Thank you for your Payment.'; ?></h2>
				</td> <!-- /Header -->
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left" class="wp-travel-content-top" style="background: #fff;box-sizing: border-box;margin: 0;padding: 20px 25px;">
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Hello'; ?> {customer_name},</p>
					<p style="line-height: 1.55;font-size: 14px;"><?php echo 'Your payment has been received.'; ?>:</p>
					<p style="line-height: 1.55;font-size: 14px;"><b><?php echo 'Booking ID'; ?>: #{booking_id} ({booking_arrival_date})</b></p>
				</td>
			</tr>			
			<!-- /Payment Starts -->
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Payment Status'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
							{payment_status}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Payment Mode'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
							{payment_mode}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b> <?php echo 'Trip Price'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								{currency_symbol} {trip_price}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="wp-travel-content" style="background: #fff;">
				<td align="left">
					<table class="wp-travel-content-head" align="left" cellspacing="0" cellpadding="0" style="width: 24%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;"><b><?php echo 'Payment Amount'; ?></b></td>
						</tr>
					</table>
					<table class="wp-travel-content-info" align="left" cellspacing="0" cellpadding="0" style="width: 76%;">
						<tr style="background: #fff;">
							<td style="font-size: 14px;background: #fff;box-sizing: border-box;margin: 0;padding: 0px 0px 8px 25px;">
								{currency_symbol} {payment_amount}
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- /Payment ends -->
			<tr class="wp-travel-footer" style="background: #fff;">
				<td align="center" style="background: #eaebed;box-sizing: border-box;font-size: 14px;padding: 10px 25px;">
					<p>{sitename} - <?php echo 'Powered By'; ?>: <a href="http://wptravel.io/" target="_blank" style="color: #5a418b;text-decoration: none;"><?php echo 'WP Travel'; ?></a></p>
				</td>
			</tr>
		</table><!-- /Wrapper -->
	</body>
	</html>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}