<?php
session_start();
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: verify_otp.html");
    exit;
}

// pending_mfa_user is set by login.php after credentials pass
if (empty($_SESSION['pending_mfa_user'])) {
    header("Location: login.html?error=session_expired");
    exit;
}

$username = $_SESSION['pending_mfa_user'];
$code     = trim($_POST['code'] ?? '');

if ($code === '' || !ctype_digit($code) || strlen($code) !== 6) {
    header("Location: verify_otp.html?error=invalid_code");
    exit;
}

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    'type'     => 'verify_otp',
    'username' => $username,
    'code'     => $code
]);

if (is_array($response) && $response['ok'] === true) {
    unset($_SESSION['pending_mfa_user']);
    $_SESSION['username'] = $username;
    if (!empty($response['session_id'])) {
        $_SESSION['session_id'] = $response['session_id'];
    }
    header("Location: home.php");
    exit;
}

header("Location: verify_otp.html?error=invalid_code");
exit;
