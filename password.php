<?php 
$password = "admin";
$adminpass = password_hash($password, PASSWORD_DEFAULT);

echo $adminpass;
?>