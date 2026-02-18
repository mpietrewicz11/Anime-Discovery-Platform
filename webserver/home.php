<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

<h1>Home Page</h1>

<p>Hi <?php echo $username; ?></p>

<br>
<a href="logout.php">Logout</a>

</body>
</html>

