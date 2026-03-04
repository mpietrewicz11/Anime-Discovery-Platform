<?php
session_start();
header("Content-Type: application/json");

require_once("rabbitMQLib.inc");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Not logged in"]);
    exit;
}

try {
    $client = new rabbitMQClient("watchlistRabbitMQ.ini", "watchlistServer");

    $request = [
        "type"    => "watchlist_get",
        "user_id" => (int)$_SESSION["user_id"]
    ];

    $response = $client->send_request($request);

    if ($response === null) {
        http_response_code(500);
        echo json_encode(["ok" => false, "error" => "No response from backend"]);
        exit;
    }

    // If backend returns a string, try decode
    if (is_string($response)) {
        $decoded = json_decode($response, true);
        if (is_array($decoded)) $response = $decoded;
    }

    if (!is_array($response)) {
        http_response_code(500);
        echo json_encode(["ok" => false, "error" => "Invalid backend response"]);
        exit;
    }

    // Expect backend to return:
    // { ok: true, data: {anime_id, title, status, added_at, updated_at}
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
