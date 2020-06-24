<?php

/*****
 * Setup
 */

$except = true;
require_once('panl.init.php');
require_once('../_system/password.php');

$var_list = array('email', 'new_password', 's');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

$view = new GrlxView_Login;
$view->page_title('Reset password: step two');
$view->headline('Reset password: step two');
$view->main_id('reset');

$form = new GrlxForm;
$form->send_to($_SERVER['SCRIPT_NAME']);


/*****
 * Updates
 */

// When the user submits a form
if ( $new_password && $s ) {

	// Give the user a new serial number.
	for($i=0;$i<16;$i++){
		$new_serial .= rand(0,9);
	}

	$new_hash = password_hash( $new_password, PASSWORD_BCRYPT );
	if ( password_verify($new_password, $new_hash) ) {
		$data = array(
			'serial' => $new_serial,
			'password' => $new_hash,
			'date_modified' => $db -> now(),
		);
		$db -> where('serial', $s);
		$db -> update('user', $data);

		$success = true;
	}
	else {
		$view->alert_msg('Password reset failed.');
	}
}


/*****
 * Display logic
 */

if ( $success ) {
	$form_output = '<p>Your password has been reset.</p>';
	$view->action('<div><a class="btn primary login" href="panl.login.php"><i></i>Login</a></div>');
	$form_output .= $view->format_actions();
}
else {
	$form_output = $form->open_form();

	$form->input_hidden('s');
	$form->value($s);
	$form_output .= $form->paint();

	$form_output .= $form->new_password('new_password');

	$view->action('<div><button class="btn primary save" name="submit" type="submit" value="Update"><i></i>Update</button></div>');

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
