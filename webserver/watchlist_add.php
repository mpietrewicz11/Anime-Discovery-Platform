<?php
session_start();
header("Content-Type: application/json");

require_once("rabbitMQLib.inc");

// Must be logged in (watch_list table needs user_id)
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Not logged in"]);
    exit;
}

$anime_id = isset($_POST["anime_id"]) ? (int)$_POST["anime_id"] : 0;
$title    = isset($_POST["title"]) ? trim($_POST["title"]) : "";
$status   = isset($_POST["status"]) ? trim($_POST["status"]) : "plan_of_watch"; // matches your enum

if ($anime_id <= 0) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Invalid anime_id"]);
    exit;
}

if ($title === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing title"]);
    exit;
}

//  only allow known status values from enum
$allowed = ["plan_of_watch", "watching", "done", "hold", "dropped"];
if (!in_array($status, $allowed, true)) {
    $status = "plan_of_watch";
}

try {
    $client = new rabbitMQClient("watchlistRabbitMQ.ini", "watchlistServer");

    $request = [
        "type"     => "watchlist_add",
        "user_id"  => (int)$_SESSION["user_id"],
        "anime_id" => $anime_id,
        "title"    => $title,
        "status"   => $status
    ];

    $response = $client->send_request($request);

    if ($response === null) {
        http_response_code(500);
        echo json_encode(["ok" => false, "error" => "No response from backend"]);
        exit;
    }

    // If backend returns a string, try to decode it
    if (is_string($response)) {
        $decoded = json_decode($response, true);
        if (is_array($decoded)) $response = $decoded;
    }

    // Ensure we always return JSON
    if (!is_array($response)) {
        http_response_code(500);
        echo json_encode(["ok" => false, "error" => "Invalid backend response"]);
        exit;
    }

    // If backend says ok=false, surface it
    if (isset($response["ok"]) && $response["ok"] !== true) {
        http_response_code(500);
    }

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Server error"]);
    exit;
}
