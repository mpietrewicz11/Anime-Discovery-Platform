<?php
session_start();
header("Content-Type: text/plain");

echo "Session ID: " . session_id() . "\n";
echo "Username: " . ($_SESSION['username'] ?? "(not set)") . "\n";
echo "All session data:\n";
print_r($_SESSION);
