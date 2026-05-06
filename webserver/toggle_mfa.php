<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    'type'     => 'toggle_mfa',
    'username' => $_SESSION['username'],
    'enabled'  => $enabled
]);

echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend']);
exit;
