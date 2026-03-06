<?php
session_start();
header("Content-Type: application/json");
require_once("rabbitMQLib.inc");

if (!isset($_SESSION["username"])) { echo json_encode(["ok" => false]); exit; }

	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
	$response = $client->send_request([
		"type" => "get_reviews",
		"anime_id" => (int)$_GET["anime_id"]]);

	echo json_encode($response ?? ["ok" => false]);
