<?php
session_start();
header("Content-Type: application/json");

require_once("rabbitMQLib.inc");

if (!isset($_SESSION["username"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Not logged in"]);
    exit;
}

$client = new rabbitMQClient("watchlistRabbitMQ.ini", "watchlistServer");

$request = [
    "type" => "get_watchlist",
    "username" => $_SESSION["username"]
];

$response = $client->send_request($request);

if ($response === null) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "No response from backend"]);
    exit;
}

echo json_encode($response);
