<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

require_once('login.php.inc');

$username = $_SESSION['username'];
$animeId = (int)($_POST['anime_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$enabled = (int)($_POST['enabled'] ?? 1);

if ($animeId <= 0 || $title === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing anime info']);
    exit;
}

$db = new loginDB();

if ($enabled === 1) {
    $ok = $db->addNotification($username, $animeId, $title);
} else {
    $ok = $db->removeNotification($username, $animeId);
}

echo json_encode(['ok' => (bool)$ok]);
exit;
?>
