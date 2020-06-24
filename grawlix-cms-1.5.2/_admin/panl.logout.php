<?php
session_start();
unset($_SESSION['admin']);
session_destroy();

require_once('lib/GrlxView.php');
require_once('lib/GrlxView_Login.php');

$view = new GrlxView_Login;
$view->page_title('Logged out');
$view->headline('See you next time');
$view->main_id('logout');
$view->action('<div><a class="btn primary login" href="panl.login.php"><i></i>Login again</a></div>');


/*****
 * Display
 */
$output  = $view->open_view();
$output .= '<p>You are now logged out of the Grawlix Panel.</p>';
$output .= $view->format_actions();
$output .= $view->close_view();
print($output);

