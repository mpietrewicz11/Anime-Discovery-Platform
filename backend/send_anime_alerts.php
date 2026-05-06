<?php
require __DIR__ . '/vendor/autoload.php';
require_once('login.php.inc');

use PHPMailer\PHPMailer\PHPMailer;

$SMTP_HOST       = 'smtp.gmail.com';
$SMTP_PORT       = 587;
$SMTP_SECURE     = PHPMailer::ENCRYPTION_STARTTLS;
$SMTP_USERNAME   = 'it490.adem@gmail.com';
$SMTP_PASSWORD   = 'fuouqkalaznkgebp';
$SMTP_FROM       = 'it490.adem@gmail.com';
$SMTP_FROM_NAME  = 'ADEM Anime Alerts';

function smtp_mail($to, $subject, $body) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_SECURE, $SMTP_USERNAME, $SMTP_PASSWORD, $SMTP_FROM, $SMTP_FROM_NAME;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->Port       = $SMTP_PORT;
        $mail->SMTPSecure = $SMTP_SECURE;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USERNAME;
        $mail->Password   = $SMTP_PASSWORD;

        $mail->setFrom($SMTP_FROM, $SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body;

        return $mail->send();
    } catch (\Throwable $e) {
        error_log("SMTP error to $to: " . $e->getMessage());
        return false;
    }
}

function fetch_latest_episode($animeId) {
    $url = "https://api.jikan.moe/v4/anime/" . urlencode($animeId) . "/episodes";
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$resp) {
        return null;
    }

    $json = json_decode($resp, true);
    if (!isset($json['data']) || !is_array($json['data']) || count($json['data']) === 0) {
        return null;
    }

    $latest = end($json['data']);

    return [
        'episode' => (int)($latest['mal_id'] ?? 0),
        'title'   => $latest['title'] ?? 'Untitled Episode'
    ];
}

$db = new loginDB();
$conn = new mysqli("100.102.151.59", "it490", "123", "it490");

if ($conn->connect_errno != 0) {
    die("DB connection failed\n");
}

$sql = "
    SELECT 
        u.id AS user_id,
        u.email,
        u.email_notifications,
        an.anime_id,
        an.anime_title,
        an.last_sent_episode,
        an.enabled
    FROM users u
    JOIN anime_notifications an ON u.id = an.user_id
    WHERE u.email IS NOT NULL
      AND u.email <> ''
      AND u.email_notifications = 1
      AND an.enabled = 1
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed\n");
}

while ($row = $result->fetch_assoc()) {
    $userId = (int)$row['user_id'];
    $email = trim($row['email']);
    $animeId = (int)$row['anime_id'];
    $animeTitle = $row['anime_title'];
    $lastSentEpisode = (int)$row['last_sent_episode'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        continue;
    }

    $latest = fetch_latest_episode($animeId);
    if (!$latest) {
        continue;
    }

    if ($latest['episode'] > $lastSentEpisode) {
        $subject = "New episode alert: " . $animeTitle;

        $body = "Hi!\n\n";
        $body .= "A new episode of {$animeTitle} is now available.\n\n";
        $body .= "Episode: {$latest['episode']}\n";
        $body .= "Title: {$latest['title']}\n\n";
        $body .= "Go check it out on ADEM Project!\n";

        $ok = smtp_mail($email, $subject, $body);

        if ($ok) {
            $newEpisode = (int)$latest['episode'];
            $updateSql = "
                UPDATE anime_notifications
                SET last_sent_episode = $newEpisode
                WHERE user_id = $userId AND anime_id = $animeId
            ";
            $conn->query($updateSql);
        }
    }
}
?>
