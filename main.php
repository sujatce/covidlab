<?php
// The main file contains the database connection, session initializing, and functions, other PHP files will depend on this file.
// Include thee configuration file
include_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';

// We need to use sessions, so you should always start sessions using the below function
session_start();
// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Update the charset
mysqli_set_charset($con, db_charset);
// The below function will check if the user is logged-in and also check the remember me cookie
function check_loggedin($con, $redirect_file = 'index.php') {
	// Check for remember me cookie variable and loggedin session variable
    if (isset($_COOKIE['rememberme']) && !empty($_COOKIE['rememberme']) && !isset($_SESSION['loggedin'])) {
    	// If the remember me cookie matches one in the database then we can update the session variables.
    	$stmt = $con->prepare('SELECT id, username, role FROM accounts WHERE rememberme = ?');
		$stmt->bind_param('s', $_COOKIE['rememberme']);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			// Found a match, update the session variables and keep the user logged-in
			$stmt->bind_result($id, $username, $role);
			$stmt->fetch();
            $stmt->close();
			session_regenerate_id();
			$_SESSION['loggedin'] = TRUE;
			$_SESSION['name'] = $username;
			$_SESSION['id'] = $id;
			$_SESSION['role'] = $role;
		} else {
			// If the user is not remembered redirect to the login page.
			header('Location: ' . $redirect_file);
			exit;
		}
    } else if (!isset($_SESSION['loggedin'])) {
    	// If the user is not logged in redirect to the login page.
    	header('Location: ' . $redirect_file);
    	exit;
    }
}


function insert_login_logs($username, $status)
{
	echo('insert');
	$ip = $_SERVER['REMOTE_ADDR'];
	$stmt = $con->prepare('INSERT INTO login_logs (username,ip_address, status,logindate) VALUES (?,?,?,now())');
				$stmt->bind_param('sss',  $username,$ip,$status);
				$stmt->execute();
				$stmt->close();
				echo('insert success');
}
// Send activation email function
function send_activation_email($email, $code) {
	$subject = 'Account Activation Required';
	$headers = 'From: ' . mail_from . "\r\n" . 'Reply-To: ' . mail_from . "\r\n" . 'Return-Path: ' . mail_from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
	$activate_link = activation_link . '?email=' . $email . '&code=' . $code;
	$email_template = str_replace('%link%', $activate_link, file_get_contents('activation-email-template.html'));
	send_email($email, $subject, $email_template, $headers);
}

function loginAttempts($con, $update = TRUE) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$now = date('Y-m-d H:i:s');
	if ($update) {
		$stmt = $con->prepare('INSERT INTO login_attempts (ip_address, `date`) VALUES (?,?) ON DUPLICATE KEY UPDATE attempts_left = attempts_left - 1, `date` = VALUES(`date`)');
		$stmt->bind_param('ss', $ip, $now);
		$stmt->execute();
		$stmt->close();
	}
	$stmt = $con->prepare('SELECT * FROM login_attempts WHERE ip_address = ?');
	$stmt->bind_param('s', $ip);
	$stmt->execute();
	$result = $stmt->get_result();
	$login_attempts = $result->fetch_array(MYSQLI_ASSOC);
	$stmt->close();
	if ($login_attempts) {
		// The user can try to login after 1 day... change the "+1 day" if you want increase/decrease this date.
		$expire = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($login_attempts['date'])));
		if ($now > $expire) {
			$stmt = $con->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
			$stmt->bind_param('s', $ip);
			$stmt->execute();
			$stmt->close();
			$login_attempts = array();
		}
	}
	return $login_attempts;
}

// Send activation email function
function send_email($email, $subject, $email_template, $headers) {


	// Replace sender@example.com with your "From" address.
	// This address must be verified with Amazon SES.
	$sender = mail_from;// 'noreply@sujatech.com';
	$senderName = 'COVIDLab';
	
	// Replace recipient@example.com with a "To" address. If your account
	// is still in the sandbox, this address must be verified.
	$recipient = $email;
	
	// Replace smtp_username with your Amazon SES SMTP user name.
	$usernameSmtp = 'AKIAXE6JT7DDW5BVAWDA';
	
	// Replace smtp_password with your Amazon SES SMTP password.
	$passwordSmtp = 'BEMrshf8H7V9AKfmgn+u2ak1+/3n6wF2zlwvoZi6BuJK';
	
	// Specify a configuration set. If you do not want to use a configuration
	// set, comment or remove the next line.
	//$configurationSet = 'ConfigSet';
	
	// If you're using Amazon SES in a region other than US West (Oregon),
	// replace email-smtp.us-west-2.amazonaws.com with the Amazon SES SMTP
	// endpoint in the appropriate region.
	$host = 'email-smtp.us-east-1.amazonaws.com';
	$port = 587;
	
	// The subject line of the email
	//$subject = '-- Put email subject here --';
	
	// The plain-text body of the email
	$bodyText =  $email_template;
	
	// The HTML-formatted body of the email
	$bodyHtml = $email_template;
	
	$mail = new PHPMailer(true);
	
	try {
		// Specify the SMTP settings.
		$mail->isSMTP();
		$mail->setFrom($sender, $senderName);
		$mail->Username   = $usernameSmtp;
		$mail->Password   = $passwordSmtp;
		$mail->Host       = $host;
		$mail->Port       = $port;
		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = 'tls';
		//$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
		//$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $headers);
	
		// Specify the message recipients.
		$mail->addAddress($recipient);
		// You can also add CC, BCC, and additional To recipients here.
	
		// Specify the content of the message.
		$mail->isHTML(true);
		$mail->Subject    = $subject;
		$mail->Body       = $bodyHtml;
		$mail->AltBody    = $bodyText;
		$mail->Send();
		echo "Email sent!" , PHP_EOL;
	} catch (phpmailerException $e) {
		echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
	} catch (Exception $e) {
		echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
	}
	}

?>
