<?php
session_start();
header('Content-Type: application/json');

// user must be logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

require_once('login.php.inc');

$username = $_SESSION['username'];
$animeId = (int)($_POST['anime_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$enabled = (int)($_POST['enabled'] ?? 1);

// basic validation so we don't send bad data to DB
if ($animeId <= 0 || $title === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing anime info']);
    exit;
}

try {
    $db = new loginDB();

    // toggle notification on/off
    if ($enabled === 1) {
        $ok = $db->addNotification($username, $animeId, $title);
    } else {
        $ok = $db->removeNotification($username, $animeId);
    }

    echo json_encode(['ok' => (bool)$ok]);
} catch (Throwable $e) {
    // log real error, return generic message to client
    error_log("notification_toggle.php error: " . $e->getMessage());

    echo json_encode(['ok' => false, 'error' => 'Server error']);
}

exit;
?>