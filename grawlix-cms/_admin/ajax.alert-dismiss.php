<?php
if ( isset($_GET['version']) && $_GET['version'] == true ) {
	session_start();
	unset($_SESSION['grawlix_version']);
	session_write_close();
}

if ( isset($_GET['cleanup']) && $_GET['cleanup'] == true ) {
	session_start();
	unset($_SESSION['install_cleanup']);
	session_write_close();
}
