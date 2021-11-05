<?php
include 'main.php';
check_loggedin($con);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Home Page</title>
		<link href="style1.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>Welcome to COVID Lab Testing</h1>
				<a href="home.php"><i class="fas fa-home"></i>Home</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<?php if ($_SESSION['role'] == 'Admin'): ?>
				<a href="admin/index.php" target="_blank"><i class="fas fa-user-cog"></i>Admin</a>
				<?php endif; ?>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
			<h2>Start testing for COVID-19. Book your appointments today!</h2>
			<p class="block">Welcome back <?=$_SESSION['firstname']?>  <?=$_SESSION['lastname']?>!</p>
			<p class="block">You have logged in <?=$_SESSION['counter']?> times and last login date is <?=$_SESSION['lastlogindate']?>!</p>
			<p class="block">Please download company confidential description here.!<a href='download.php'>Download Confidential File</a></p>
		</div>
	</body>
</html>
