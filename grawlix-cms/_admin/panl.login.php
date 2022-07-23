<?php

/* ! Setup * * * * * * * */

$except = true;
require_once('panl.init.php');
require_once('../_system/password.php');

// Send admin to specific page
$ref = $_GET['ref'];
$ref ? $ref : $ref = $_POST['ref'];
$ref ? $ref : $ref = 'book.view.php';

$view = new GrlxView_Login;
$view->page_title('Login');
$view->headline('Login to your Grawlix Panel');
$view->main_id('password');


// Set a cookie good for the entire domain
// Show the admin bar on the front-end
function grlx_cookie() {
	$expiry = 86400 * 30; // 86400 = 1 day
	setcookie('grlx_bar',true,time() + $expiry,'/');
	return;
}

if ( $_POST['submit'] == 'Login' ) {

	$var_list = array('username','extra');
	if ( $var_list ) {
		foreach ( $var_list as $key => $val ) {
			$$val = register_variable($val);
		}
	}

	if ( $username && $extra ) {
		$cols = array('id', 'password');
		$result = $db
			-> where('username', $username)
			-> getOne('user', $cols);
		$count = $db -> count;

		// Check password hash
		if ( password_verify($extra, $result['password']) && is_numeric($result['id']) ) {
			// Successful login
			$characters1 = range('a','z');
			$characters2 = range(0,9);
			$characters3 = array('-','!','@','#','$','%','^','&','*','(',')','_');
			$characters = array_merge($characters1,$characters2,$characters3);
			for($i=0;$i<32;$i++)
			{
				$x = array_rand($characters);
				$new_serial .= $characters[$x];
			}
			$data = array('serial' => $new_serial);
			$db -> where('id', $result['id']);
			$db -> update('user', $data);
			$_SESSION['admin'] = $new_serial;
			$_SESSION['grawlix_version'] = 'run_check'; // software update check
			$_SESSION['install_cleanup'] = 'run_check'; // check if firstrun is still present
			grlx_cookie();
			header('location:'.$ref);
			die();
		}
		else {
			$view->alert_msg('Login failed.');
		}
	}

	if ( ( $username && $extra && !$result ) || ( $count == 0 ) ) {
		$view->alert_msg('Login failed.');
	}
}


/* ! Build * * * * * * * */

$form = new GrlxForm;
$form->error_check(false);
$form->no_div_wrap();
$form->hide_error();
$form->send_to($_SERVER['SCRIPT_NAME']);

$form_output = $form->open_form();

$form->input_hidden('ref');
$form->value($ref);
$form_output .= $form->paint();

$form->input_text('username');
$form->placeholder('username');
$form->autofocus(true);
$form_output .= $form->paint();

$form->input_password('extra');
$form->placeholder('password');
$form_output .= $form->paint();

$view->action('<div><a class="lnk" href="panl.password-forgot.php">Forgot password?</a></div>');
$view->action('<div><button class="btn primary login" name="submit" type="submit" value="Login"><i></i>Login</button></div>');

$form_output .= $view->format_actions();
$form_output .= $form->close_form();


/* ! Display * * * * * * * */

$output  = $view->open_view();
$output .= $form_output;
$output .= $view->close_view();
print($output);
