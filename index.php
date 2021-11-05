<?php
include 'main.php';
// No need for the user to see the login form if they're logged-in so redirect them to the home page
if (isset($_SESSION['loggedin'])) {
	// If the user is not logged in redirect to the home page.
	header('Location: home.php');
	exit;
}
// Also check if they are "remembered"
if (isset($_COOKIE['rememberme']) && !empty($_COOKIE['rememberme'])) {
	// If the remember me cookie matches one in the database then we can update the session variables.
	$stmt = $con->prepare('SELECT id, username, role FROM accounts WHERE rememberme = ?');
	$stmt->bind_param('s', $_COOKIE['rememberme']);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		// Found a match
		$stmt->bind_result($id, $username, $role);
		$stmt->fetch();
		$stmt->close();
		session_regenerate_id();
		$_SESSION['loggedin'] = TRUE;
		$_SESSION['name'] = $username;
		$_SESSION['id'] = $id;
		$_SESSION['role'] = $role;
		header('Location: home.php');
		exit;
	}
}
$_SESSION['token'] = md5(uniqid(rand(), true));

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Login</title>
		<link href="style1.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>
		<div class="login">
			<h1>Login</h1>
			<div class="links">
				<a href="index.php" class="active">Login</a>
				<a href="register1.html">Register</a>
			</div>
			<form action="authenticate.php" method="post">
				<label for="username">
					<i class="fas fa-user"></i>
				</label>
				<input type="text" name="username" placeholder="Username" id="username" required>
				<label for="password">
					<i class="fas fa-lock"></i>
				</label>
				<input type="password" name="password" placeholder="Password" id="password" required>
				<input type="checkbox" name="rememberme">Remember me
				<a href="forgotpassword.php">Forgot Password?</a>
				<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
				<div class="msg"></div>
				<input type="submit" value="Login">
			</form>
		</div>
		<script>
		document.querySelector(".login form").onsubmit = function(event) {
			event.preventDefault();
			var form_data = new FormData(document.querySelector(".login form"));
			var xhr = new XMLHttpRequest();
			xhr.open("POST", document.querySelector(".login form").action, true);
			xhr.onload = function () {
				if (this.responseText.toLowerCase().indexOf("success") !== -1) {
					window.location.href = "home.php";
				} else if (this.responseText.indexOf("2FA") !== -1) {
    				window.location.href = this.responseText.replace("2FA: ", "");
				} else {
					document.querySelector(".msg").innerHTML = this.responseText;
				}
			};
			xhr.send(form_data);
		};
		</script>
	</body>
</html>
