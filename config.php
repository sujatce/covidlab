<?php
// database hostname, you don't usually need to change this
define('db_host', 'localhost:4306');
// database username
define('db_user', 'root');
// database password
define('db_pass', '');
// database name
define('db_name', 'phplogin');
// database charset, change this only if utf8 is not supported by your language
define('db_charset', 'utf8');
// Email activation variables
// account activation required?
define('account_activation', true);
// Change "Your Company Name" and "yourdomain.com", do not remove the < and >
define('mail_from', 'noreply@sujatech.com');
// Link to activation file, update this
define('activation_link', 'http://localhost:8080/covidlab/activate.php');
define('resetpassword_link', 'http://localhost:8080/covidlab/resetpassword.php?email=');
// Replace smtp_username with your Amazon SES SMTP user name.
define('usernameSmtp','AKIAXE6JT7DDW5BVAWDA');
// Replace smtp_password with your Amazon SES SMTP password.
define('passwordSmtp','BEMrshf8H7V9AKfmgn+u2ak1+/3n6wF2zlwvoZi6BuJK');
define('smtp_host','email-smtp.us-east-1.amazonaws.com');
define('smtp_port',587);

?>
