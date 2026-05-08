<?php
session_start();
header("Content-Type: application/json");
//almost got a headache doing this!!! got it working tho :)
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$title = trim($_POST['title'] ?? '');
if ($title === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing anime title']);
    exit;
}

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    'type'     => 'test_notification',
    'username' => $_SESSION['username'],
    'title'    => $title
]);

echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend']);
exit;
