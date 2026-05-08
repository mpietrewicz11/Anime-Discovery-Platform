<?php
session_start();
header('Content-Type: application/json');
//endpoint for notifications
if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in', 'data' => []]);
    exit;
}

require_once('rabbitMQLib.inc');

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    "type"     => "get_notifications",
    "username" => $_SESSION['username']
]);

echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend', 'data' => []]);
