<?php
header("Content-Type: application/json");
session_start();
//another endpoint to get a bigger cache
// user has to be logged in to request this
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

// anime_id is required
if (!isset($_GET["anime_id"]) || !is_numeric($_GET["anime_id"])) {
  http_response_code(400);
  echo json_encode(["ok" => false, "error" => "Missing or invalid anime_id"]);
  exit;
}

$anime_id = (int) $_GET["anime_id"];

$request = [
  "type"     => "get_anime_detail",
  "anime_id" => $anime_id
];

$response = rpc_request($request);

if (!isset($response["ok"]) || $response["ok"] !== true) {
  http_response_code(502);
}

echo json_encode($response);
exit;