<?php
$current_user = $args['current_user'];
?>

  <div class="account-setting">
	<div class="title">
	  <h3><?php esc_html_e( 'My Account', 'wp-travel' ); ?></h3>
	</div>
	<?php
	
	$allow_html =  wp_kses_allowed_html();
	$allow_html[ 'form' ] = array(
		'class' => true,
		'action' => true,
		'method' => true
	);
	$allow_html[ 'input' ] = array(
		'type' => true,
		'class' => true,
		'name' => true,
		'id' => true,
		'value' => true,
	);
	$allow_html[ 'label' ] = array(
		'class' => true,
		'for' => true
	);
	$allow_html[ 'span' ] = array(
		'class' => true
	);
	$allow_html[ 'div' ] = array(
		'class' => true,
		'id' => true,
		'style' => true
	);

	echo wp_kses( wptravel_get_template_html(
		'account/form-edit-account.php',
		array(
			'user' => $current_user,
		)
	), $allow_html);
	
	?>
  </div>
