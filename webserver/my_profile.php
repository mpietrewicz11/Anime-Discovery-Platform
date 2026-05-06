<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

header("Location: anime_profile.html?username=" . urlencode($_SESSION['username']));
exit;
