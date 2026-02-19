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

if ($username === '' || $password === '') {
    header("Location: register.html?error=fields_required");
    exit;
}

$client = new rabbitMQClient("testRabbitMQ_register.ini", "testServer");

$request = [
    'type' => 'register',
    'username' => $username,
    'password' => $password
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

