<?php

/*****
 * Setup
 */

$except = true;
require_once('panl.init.php');

$var_list = array('email', 'new_password', 's');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

$view = new GrlxView_Login;
$view->page_title('Reset password: step one');
$view->headline('Reset password: step one');
$view->main_id('forgot');

$form = new GrlxForm;
$form->send_to($_SERVER['SCRIPT_NAME']);


/*****
 * Updates
 */

// When the user submits a form
if ( $email && !$s ) {

	// Check if valid user
	$user = $db
		-> where('email', $email)
		-> getOne('user', 'count(*) AS count');

	if ( $user['count'] == 1 ) {

		// Give the user a new serial number.
		for($i=0;$i<16;$i++){
			$new_serial .= rand(0,9);
		}

		$data = array(
			'serial' => $new_serial,
			'date_modified' => $db -> now(),
		);
		$db -> where('email', $email);
		if ( $db -> update('user', $data) ) {
			$email_message = 'Tap this to reset your Grawlix password: http://'.$_SERVER['HTTP_HOST'].'/_admin/panl.password-reset.php?s='.$new_serial;
			$headers = 'X-Mailer: PHP/'.phpversion();
			mail($email,'Grawlix password reset',$email_message,$headers);

			$success = true;
		}
		else {
			$view->alert_msg('Database error.');
		}
	}
	else {
		$view->alert_msg('Email not found.');
		unset( $_POST );
	}
}


/*****
 * Display logic
 */

if ( $success ) {
	$form_output = '<p>Email sent to <b>'.$email.'</b>.</p><p>Please check your inbox for instructions and a link to reset your password.</p>';
}
else {
	$form_output = $form->open_form();

	$form->input_email('email');
	$form->label('Enter the email associated with your panel username');
	$form->autofocus(true);
	$form_output .= $form->paint();

	$view->action('<div><button class="btn primary send" name="submit" type="submit" value="Send"><i></i>Send reset</button></div>');

	$form_output .= $view->format_actions();
	$form_output .= $form->close_form();
}


/*****
 * Display
 */

$output  = $view->open_view();
$output .= $form_output;
$output .= $view->close_view();
print($output);