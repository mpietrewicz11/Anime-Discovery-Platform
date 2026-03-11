<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in', 'data' => []]);
    exit;
}

require_once('login.php.inc');

$username = $_SESSION['username'];

try {
    $db = new loginDB();
    $list = $db->getNotifications($username);

    echo json_encode([
        'ok' => true,
        'data' => is_array($list) ? $list : []
    ]);
} catch (Throwable $e) {
    error_log("notification_get.php error: " . $e->getMessage());
    echo json_encode([
        'ok' => false,
        'error' => 'Server error',
        'data' => []
    ]);
}
exit;
?>
