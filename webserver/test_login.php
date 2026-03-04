<?php
session_start();
$_SESSION['username'] = "Emi";
header("Location: home.php");
exit;
