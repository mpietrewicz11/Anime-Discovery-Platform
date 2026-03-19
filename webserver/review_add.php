<?php
session_start();
header("Content-Type: application/json");

require_once("rabbitMQLib.inc");

// user must be logged in to post a review
if (!isset($_SESSION["username"])) {
    echo json_encode(["ok" => false]);
    exit;
}

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// send review data to backend through rqbbitmq
$response = $client->send_request([
    "type" => "add_review",
    "username" => $_SESSION["username"],
    "anime_id" => (int)($_POST["anime_id"] ?? 0),
    "anime_title" => trim($_POST["title"] ?? ""),
    "rating" => (int)($_POST["rating"] ?? 0),
    "review_text" => trim($_POST["review_text"] ?? "")
]);

// return response (fallback just in case something fails)
echo json_encode($response ?? ["ok" => false]);