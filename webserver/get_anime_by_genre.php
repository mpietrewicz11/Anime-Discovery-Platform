<?php
//end 
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Not logged in"]);
    exit;
}

require_once(__DIR__ . "/path.inc");
require_once(__DIR__ . "/get_host_info.inc");
require_once(__DIR__ . "/rabbitMQLib.inc");

function rpc_request($request) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    try {
        $response = $client->send_request($request);
        if ($response === null) {
            return ["ok" => false, "error" => "No response from backend"];
        }
        return $response;
    } catch (Exception $e) {
        return ["ok" => false, "error" => "RPC error: " . $e->getMessage()];
    }
}

$genre = isset($_GET["genre"]) ? trim($_GET["genre"]) : "";
$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 12;

if ($limit < 1)  $limit = 12;
if ($limit > 50) $limit = 50;

if (empty($genre)) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing genre"]);
    exit;
}

$request = [
    "type"  => "get_anime_by_genre",
    "genre" => $genre,
    "limit" => $limit
];

$response = rpc_request($request);

if (!isset($response["ok"]) || $response["ok"] !== true) {
    http_response_code(502);
}

echo json_encode($response);
exit;