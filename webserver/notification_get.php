<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'data' => []]);
    exit;
}

require_once('login.php.inc');

$username = $_SESSION['username'];
$db = new loginDB();
$list = $db->getNotifications($username);

echo json_encode([
    'ok' => true,
    'data' => $list
]);
exit;
?>
