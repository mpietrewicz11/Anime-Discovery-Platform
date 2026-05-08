<?php
session_start();
header("Content-Type: application/json");
//endpoint for posts in the feed 
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$body = trim($_POST['body'] ?? '');
$isRepost = isset($_POST['repost_of']) && ctype_digit((string)$_POST['repost_of']);
if ($body === '' && !$isRepost) {
    echo json_encode(['ok' => false, 'error' => 'Post cannot be empty']);
    exit;
}

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$request = [
    'type'     => 'create_post',
    'username' => $_SESSION['username'],
    'body'     => $body
];

if (isset($_POST['repost_of']) && ctype_digit((string)$_POST['repost_of'])) {
    $request['repost_of'] = (int)$_POST['repost_of'];
}

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request($request);

echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend']);
exit;
