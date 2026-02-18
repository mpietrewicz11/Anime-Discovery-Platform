<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header("Location: login.html?error=fields_required");
    exit;
}

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

$request = [
    'type' => 'login',
    'username' => $username,
    'password' => $password
];

$response = $client->send_request($request);

$success = false;

if (is_array($response)) {
    if (isset($response['ok']) && $response['ok'] === true) {
        $success = true;
    }
    if (isset($response['session_id'])) {
        $success = true;
    }
}

if ($success) {
    $_SESSION['username'] = $username;

    if (isset($response['session_id'])) {
        $_SESSION['session_id'] = $response['session_id'];
    }

    header("Location: home.php");
    exit;
}

header("Location: login.html?error=login_failed");
exit;
?>

