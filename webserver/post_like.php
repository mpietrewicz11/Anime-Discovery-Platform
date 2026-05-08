<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
if ($postId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid post_id']);
    exit;
}

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    'type'     => 'like_post',
    'username' => $_SESSION['username'],
    'post_id'  => $postId
]);
//added debugger here 
echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend']);
exit;
