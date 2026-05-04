<?php
include 'db.php'

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
	echo json_encode(["error" => "No user was acknowledged"]);
	exit;
}
// Important info for the user
$stmt = $pdo->prepare("SELECT id, username, email, bio FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt2 = $pdo->prepare("SELECT title, score, review_text, created_at FROM user_reviews WHERE user_id = ? ORDER BY created_at DESC");
$stmt2->execute([$user_id]);
$reviews = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$stmt3 = $pdo->prepare("SELECT title, status, added_at FROM watch_list WHERE user_id = ? ORDER BY added_at DESC");
$stmt3->execute([$user_id]);
$watchlist = $stmt3-fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["user" => $user, "reviews" => $reviews, "watchlist" => $watchlist]);
?>

