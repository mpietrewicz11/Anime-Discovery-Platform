<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    'type'     => 'get_feed',
    'username' => $_SESSION['username']
]);

echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend']);
exit;
