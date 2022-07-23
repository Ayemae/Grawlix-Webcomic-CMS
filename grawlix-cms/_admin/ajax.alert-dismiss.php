<?php
if ( $_GET['version'] == true ) {
	session_start();
	unset($_SESSION['grawlix_version']);
	session_write_close();
}

if ( $_GET['cleanup'] == true ) {
	session_start();
	unset($_SESSION['install_cleanup']);
	session_write_close();
}
