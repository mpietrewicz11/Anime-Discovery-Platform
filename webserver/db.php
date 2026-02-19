<?php

$host = "127.0.0.1";
$user = "it490app";
$pass = "123";
$db = "it490";


$conn =  new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
	die("Connection failed");
}

?>
