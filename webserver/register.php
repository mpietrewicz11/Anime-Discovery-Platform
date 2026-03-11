<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.html");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$email = trim($_POST['email'] ?? '');
$emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;

if ($username === ''  $password === ''  $email === '') {
    header("Location: register.html?error=fields_required");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.html?error=invalid_email");
    exit;
}

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

$request = [
    'type' => 'register',
    'username' => $username,
    'password' => $password,
    'email' => $email,
    'email_notifications' => $emailNotifications
];

$response = $client->send_request($request);

error_log("Register response: " . json_encode($response));

$success = false;

if (is_array($response) && isset($response['ok']) && $response['ok'] === true) {
    $success = true;
}

if ($success) {
    header("Location: login.html?registered=1");
    exit;
}

header("Location: register.html?error=register_failed");
exit;
?>
