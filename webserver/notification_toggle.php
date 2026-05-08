<?php
session_start();
header('Content-Type: application/json');
//endpoint for notifications 
if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$animeId = (int)($_POST['anime_id'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$enabled = (int)($_POST['enabled'] ?? 1);

if ($animeId <= 0 || $title === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing anime info']);
    exit;
}

require_once('rabbitMQLib.inc');

$client   = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request([
    "type"      => "toggle_notification",
    "username"  => $_SESSION['username'],
    "anime_id"  => $animeId,
    "title"     => $title,
    "enabled"   => $enabled
]);

echo json_encode($response ?? ['ok' => false, 'error' => 'No response from backend']);
