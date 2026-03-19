
<?php
session_start();
header("Content-Type: application/json");

require_once("rabbitMQLib.inc");

//make sure user is logged in if not stop here
if (!isset($_SESSION["username"])) { echo json_encode(["ok" => false]); exit; }

// wiring to rabbitmq
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
//send request to backend to fetch
$response = $client->send_request([
	"type" => "get_anime_list",
	"genre" => $_GET["genre"] ?? "Top"]);

echo json_encode($response ?? ["ok" => false]);
