<?php
session_start();

/*
  DEV ONLY:
  This sets a test session so you can preview pages without backend.
  Delete this file before final submission.
*/

$_SESSION["username"] = "emi_test";
header("Location: home.php");
exit;
