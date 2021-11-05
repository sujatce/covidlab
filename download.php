<?php
include 'main.php';
check_loggedin($con);
//If user is only logged in, following code will be executed
	$file="confidential.pdf";
	if (file_exists($file)) {
		$Message="";
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}