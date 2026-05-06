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
//connection with rabbitmq
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

if (is_array($response) && $response['ok'] === true) {
    if (!empty($response['mfa_required'])) {
        // credentials valid — hold username and wait for OTP
        $_SESSION['pending_mfa_user'] = $username;
        header("Location: verify_otp.html");
        exit;
    }

    // no MFA path (fallback, should not normally hit)
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
