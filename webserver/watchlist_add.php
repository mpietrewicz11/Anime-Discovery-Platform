<?php
session_start();
header("Content-Type: application/json");

require_once("rabbitMQLib.inc");

if (!isset($_SESSION["username"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Not logged in"]);
    exit;
}

$anime_id = isset($_POST["anime_id"]) ? (int)$_POST["anime_id"] : 0;
$title    = isset($_POST["title"]) ? trim($_POST["title"]) : "";

if ($anime_id <= 0 || $title === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing anime_id or title"]);
    exit;
}

$client = new rabbitMQClient("watchlistRabbitMQ.ini", "watchlistServer");

$request = [
    "type" => "add_watchlist",
    "username" => $_SESSION["username"],
    "anime_id" => $anime_id,
    "anime_title" => $title
];

$response = $client->send_request($request);

if ($response === null) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "No response from backend"]);
    exit;
}

echo json_encode($response);
