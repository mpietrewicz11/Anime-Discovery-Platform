<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Not logged in"]);
    exit;
}

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing username"]);
    exit;
}

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    "type"     => "get_profile",
    "username" => $username
]);

if (is_array($response) && !empty($response['ok'])) {
    $response['viewer'] = $_SESSION['username'];
}

echo json_encode($response ?? ["ok" => false, "error" => "No response from backend"]);
exit;
