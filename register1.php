<?php
include 'main.php';

if(!empty($_POST['g-recaptcha-response']))
{
    $secret = '6LcnfgcdAAAAACeI2Cgbiv-NoG5jU3QIXZVdQ7cW';
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
    $responseData = json_decode($verifyResponse);
    if($responseData->success)
    {
        $msg = "g-recaptcha varified successfully";
        //echo 'Verified successfully';
    }
    else
    {
		$msg = "Error validating recaptcha";
        //echo 'Error validating recaptcha';
        exit('Error verifying g-recaptcha');
    }
}else
{
	$msg = "reCAPTCHA is not validated, please validate it";
	exit('reCAPTCHA is not validated, please validate it');
}

if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['fname'],$_POST['lname'],$_POST['username'],$_POST['password'],$_POST['cpassword'],$_POST['birthdate'], $_POST['email'], $_POST['sq1'],$_POST['ans1'],$_POST['sq2'],$_POST['ans2'], $_POST['sq3'], $_POST['ans3'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.

	if (empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['cpassword']) || empty($_POST['birthdate']) || empty( $_POST['email']) || empty($_POST['sq1']) || empty($_POST['sq2']) || empty($_POST['ans2']) || empty($_POST['sq3']) || empty( $_POST['ans3'])){
		// One or more values are empty.
   sexit('Please complete the registration form');
}
// Check to see if the email is valid.
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Email is not valid!');
}
// Username must contain only characters and numbers.
if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['username'])) {
    exit('Username is not valid!');
}
// Password must be between 5 and 20 characters long.
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
}
// Check if both the password and confirm password fields match
if ($_POST['cpassword'] != $_POST['password']) {
	exit('Passwords do not match!');
}
// We need to check if the account with that username exists.
$stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ? OR email = ?');
// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
$stmt->bind_param('ss', $_POST['username'], $_POST['email']);
$stmt->execute();
$stmt->store_result();
// Store the result so we can check if the account exists in the database.
if ($stmt->num_rows > 0) {
	// Username already exists
	echo 'Username and/or email exists!';
} else {
	$stmt->close();
	// Username doesnt exists, insert new account
	$stmt = $con->prepare('INSERT INTO accounts (username, password, email,activation_code, firstname,lastname, birthdate	,sq1,sa1,sq2,sa2,sq3, sa3,ip) VALUES (?, ?, ?, ?, ?,?, ?, ?, ?, ?,?, ?, ?,?)');
	// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
	//$stmt->store_result();
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$uniqid = account_activation ? uniqid() : 'activated';
	$ip = $_SERVER['REMOTE_ADDR'];
	//$stmt->bind_param('sssssssiissssss',$_POST['username'], $password, $_POST['email'], $uniqid,$_POST['fname'],$_POST['lname'],$_POST['birthdate'],$_POST[''],$_POST[''], $_POST['sq1'],$_POST['ans1'],$_POST['sq2'],$_POST['ans2'], $_POST['sq3'], $_POST['ans3']);
	//$password
	  $stmt->bind_param('ssssssssssssss',$_POST['username'], $password, $_POST['email'], $uniqid,$_POST['fname'],$_POST['lname'],$_POST['birthdate'], $_POST['sq1'],$_POST['ans1'],$_POST['sq2'],$_POST['ans2'], $_POST['sq3'], $_POST['ans3'],$ip);
	  if (false === $stmt) {
		// Log the error and handle it:
		error_log('Could not create a statement:' . $conn->error);
	  }
	$stmt->execute();
	$stmt->close();
	if (account_activation) {
		// Account activation required, send the user the activation email with the "send_activation_email" function from the "main.php" file
		send_activation_email($_POST['email'], $uniqid);
		echo 'You have successfully registered, Please check your email to activate your account before logging in! <a href="index.php">Login</a> ';
	} else {
		echo 'You have successfully registered, you can now login!';
	}
}
?>
