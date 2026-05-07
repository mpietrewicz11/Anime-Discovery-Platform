#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function smtp_mail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAuth   = true;
        $mail->Username   = 'it490.adem@gmail.com';
        $mail->Password   = 'fuouqkalaznkgebp';
        $mail->setFrom('it490.adem@gmail.com', 'ADEM Project');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body;
        return $mail->send();
    } catch (\Throwable $e) {
        $entry = date("Y-m-d H:i:s") . " [SMTP ERROR] to=$to error=" . $e->getMessage() . " info=" . $mail->ErrorInfo . PHP_EOL;
        file_put_contents("/var/log/it490.log", $entry, FILE_APPEND);
        error_log("SMTP error to $to: " . $e->getMessage() . " | " . $mail->ErrorInfo);
        return false;
    }
}

function logEvent($msg) {
    try {
        $conn = new AMQPConnection(['host'=>'100.64.95.105','port'=>5672,'login'=>'deploy','password'=>'123','vhost'=>'deploy']);
        $conn->connect();
        $ex = new AMQPExchange(new AMQPChannel($conn));
        $ex->setName("logs.exchange");
        $ex->setType("fanout");
        $ex->declareExchange();
        $ex->publish($msg);
    } catch (Exception $e) {}
}

function doRegister($username, $password, $email, $emailNotifications)
{
    $db = new loginDB();
    return $db->registerUser($username, $password, $email, $emailNotifications);
}

function doLogin($username, $password)
{
    $db = new loginDB();

    if (!$db->validateLogin($username, $password)) {
        logEvent("login failed - invalid credentials: $username");
        return ['ok' => false, 'error' => 'Invalid credentials'];
    }

    $user = $db->getUserByUsername($username);
    if (!$user) {
        return ['ok' => false, 'error' => 'User not found'];
    }

    // only trigger MFA if the user has opted in
    if ($db->getMfaEnabled($user['id'])) {
        if (empty($user['email'])) {
            return ['ok' => false, 'error' => 'No email on file for this account'];
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $db->storeMfaCode($user['id'], $code);

        $sent = smtp_mail(
            $user['email'],
            'Your ADEM login code',
            "Hi {$username},\n\nYour verification code is: {$code}\n\nIt expires in 10 minutes. Do not share it with anyone.\n\n— ADEM Project"
        );

         if (!$sent) {
            logEvent("mfa email failed: $username");
            return ['ok' => false, 'error' => 'mfa_email_failed'];
        }

        return ['ok' => true, 'mfa_required' => true];
    }

    // MFA not enabled — create session immediately
    $sessionId = $db->createSession($username);
    if (!$sessionId) {
        return ['ok' => false, 'error' => 'Could not create session'];
    }

    return ['ok' => true, 'session_id' => $sessionId];
}

function doToggleMfa($username, $enabled)
{
    $db = new loginDB();
    return $db->setMfaEnabled($username, $enabled);
}

function doVerifyOtp($username, $code)
{
    $db = new loginDB();

    $user = $db->getUserByUsername($username);
    if (!$user) {
        return ['ok' => false, 'error' => 'User not found'];
    }

    if (!$db->verifyMfaCode($user['id'], $code)) {
        return ['ok' => false, 'error' => 'Invalid or expired code'];
    }

    $sessionId = $db->createSession($username);
    if (!$sessionId) {
        return ['ok' => false, 'error' => 'Could not create session'];
    }

    return ['ok' => true, 'session_id' => $sessionId];
}

function doValidate($sessionId)
{
    $db = new loginDB();
    $ok = $db->validateSession($sessionId);
    return ['ok' => (bool)$ok];
}

function doAddWatchlist($username, $animeId, $animeTitle)
{
    $db = new loginDB();
    $ok = $db->addToWatchlist($username, $animeId, $animeTitle);
    return ['ok' => (bool)$ok];
}

function doGetWatchlist($username)
{
    $db = new loginDB();
    $list = $db->getWatchlist($username);
    return ['ok' => true, 'data' => $list];
}

function doAddReview($username, $animeId, $animeTitle, $rating, $reviewText)
{
    $db = new loginDB();
    $ok = $db->addReview($username, $animeId, $animeTitle, $rating, $reviewText);
    return ['ok' => (bool)$ok];
}

function doGetReviews($animeId)
{
    $db = new loginDB();
    $list = $db->getReviews($animeId);
    return ['ok' => true, 'reviews' => $list];
}

function doGetAnimeList($genre)
{
    $db = new loginDB();
    return $db->getAnimeList($genre);
}

function doGetTopAnime($limit = 12)
{
    $db = new loginDB();
    return $db->getTopAnime($limit);
}

//  this is the new cache additon: fetch a single anime by mal_id from anime_cache
function doUpdateBio($username, $bio)
{
    $db = new loginDB();
    return $db->updateBio($username, $bio);
}

function doGetProfile($username)
{
    $db = new loginDB();
    return $db->getProfile($username);
}

function doGetNotifications($username)
{
    $db = new loginDB();
    $list = $db->getNotifications($username);
    return ['ok' => true, 'data' => is_array($list) ? $list : []];
}

function doTestNotification($username, $animeTitle)
{
    $db   = new loginDB();
    $user = $db->getUserByUsername($username);

    if (!$user || empty($user['email'])) {
        return ['ok' => false, 'error' => 'No email on file for this account'];
    }

    $sent = smtp_mail(
        $user['email'],
        "You're subscribed to {$animeTitle}!",
        "Hi {$username},\n\nThanks for subscribing to {$animeTitle} on ADEM Project!\n\nYou'll receive an email alert whenever a new episode drops.\n\n— ADEM Project"
    );

    return $sent
        ? ['ok' => true]
        : ['ok' => false, 'error' => 'Failed to send email'];
}

function doToggleNotification($username, $animeId, $title, $enabled)
{
    $db = new loginDB();
    if ($enabled === 1) {
        $ok = $db->addNotification($username, $animeId, $title);
    } else {
        $ok = $db->removeNotification($username, $animeId);
    }
    return ['ok' => (bool)$ok];
}

function doGetAnimeByGenre($genre, $limit = 12)
{
    $db = new loginDB();
    return $db->getAnimeByGenre($genre, $limit);
}

function doCreatePost($username, $body, $repostOf = null)
{
    $db   = new loginDB();
    $user = $db->getUserByUsername($username);
    if (!$user) return ['ok' => false, 'error' => 'User not found'];
    return $db->createPost((int)$user['id'], $body, $repostOf);
}

function doGetFeed($username)
{
    $db   = new loginDB();
    $user = $db->getUserByUsername($username);
    $uid  = $user ? (int)$user['id'] : 0;
    return $db->getFeed($uid);
}

function doLikePost($username, $postId)
{
    $db   = new loginDB();
    $user = $db->getUserByUsername($username);
    if (!$user) return ['ok' => false, 'error' => 'User not found'];
    return $db->likePost((int)$user['id'], $postId);
}

function doGetAnimeDetail($animeId)
{
    $db = new loginDB();
    return $db->getAnimeDetail($animeId);
}

function requestProcessor($request)
{
    echo "routing key: " . print_r($request, true) . PHP_EOL;
    echo "received request" . PHP_EOL;
    var_dump($request);

    if (!isset($request['type'])) {
        return ['ok' => false, 'error' => 'Missing type'];
    }

    switch ($request['type']) {
        case "register":
            if (
                !isset($request['username']) ||
                !isset($request['password']) ||
                !isset($request['email'])
            ) {
                logEvent("register failed - missing fields");
                return ['ok' => false, 'error' => 'Missing registration fields'];
            }
            $emailNotifications = isset($request['email_notifications'])
                ? (int)$request['email_notifications']
                : 0;
            $result = doRegister(
                $request['username'],
                $request['password'],
                $request['email'],
                $emailNotifications
            );
            logEvent("register attempt: " . $request['username'] . " result: " . ($result['ok'] ? 'success' : 'failed'));
            return $result;

          case "login":
            if (!isset($request['username']) || !isset($request['password'])) {
                logEvent("login failed - missing fields");
                return ['ok' => false, 'error' => 'Missing login fields'];
            }
            $result = doLogin($request['username'], $request['password']);
            logEvent("login attempt: " . $request['username'] . " result: " . ($result['ok'] ? 'success' : 'failed'));
            return $result;

        case "toggle_mfa":
            if (!isset($request['username']) || !isset($request['enabled'])) {
                return ['ok' => false, 'error' => 'Missing fields'];
            }
            return doToggleMfa($request['username'], (int)$request['enabled']);

        case "verify_otp":
            if (!isset($request['username']) || !isset($request['code'])) {
                return ['ok' => false, 'error' => 'Missing username or code'];
            }
            $result = doVerifyOtp($request['username'], trim($request['code']));
            logEvent("verify_otp: " . $request['username'] . " result: " . ($result['ok'] ? 'success' : 'failed'));
            return $result;

        case "validate_session":
            if (!isset($request['sessionId'])) {
                return ['ok' => false, 'error' => 'Missing sessionId'];
            }
            return doValidate($request['sessionId']);

        case "add_watchlist":
            return doAddWatchlist(
                $request['username'],
                $request['anime_id'],
                $request['anime_title']
            );

        case "get_watchlist":
            return doGetWatchlist($request['username']);

        case "add_review":
            return doAddReview(
                $request['username'],
                $request['anime_id'],
                $request['anime_title'],
                $request['rating'],
                $request['review_text']
            );

        case "get_reviews":
            return doGetReviews($request['anime_id']);

        case "get_anime_list":
            $genre = $request['genre'] ?? 'Top';
            return doGetAnimeList($genre);

        case "get_top_anime":
            $limit = isset($request["limit"]) ? (int)$request["limit"] : 12;
            return doGetTopAnime($limit);

        case "update_bio":
            if (!isset($request['username'])) {
                return ['ok' => false, 'error' => 'Missing username'];
            }
            return doUpdateBio($request['username'], $request['bio'] ?? '');

        case "get_profile":
            if (!isset($request['username'])) {
                return ['ok' => false, 'error' => 'Missing username'];
            }
            return doGetProfile($request['username']);

        case "get_notifications":
            if (!isset($request['username'])) {
                return ['ok' => false, 'error' => 'Missing username'];
            }
            return doGetNotifications($request['username']);

        case "test_notification":
            if (!isset($request['username']) || !isset($request['title'])) {
                return ['ok' => false, 'error' => 'Missing fields'];
            }
            return doTestNotification($request['username'], $request['title']);

        case "toggle_notification":
            if (!isset($request['username']) || !isset($request['anime_id']) || !isset($request['title'])) {
                return ['ok' => false, 'error' => 'Missing notification fields'];
            }
            $enabled = isset($request['enabled']) ? (int)$request['enabled'] : 1;
            return doToggleNotification($request['username'], (int)$request['anime_id'], $request['title'], $enabled);

        case "get_anime_by_genre":
            if (!isset($request['genre'])) {
                return ['ok' => false, 'error' => 'Missing genre'];
            }
            $limit = isset($request['limit']) ? (int)$request['limit'] : 12;
            return doGetAnimeByGenre($request['genre'], $limit);

        case "get_anime_detail":
            if (!isset($request['anime_id'])) {
                return ['ok' => false, 'error' => 'Missing anime_id'];
            }
            return doGetAnimeDetail((int)$request['anime_id']);

        case "create_post":
            if (!isset($request['username']) || !isset($request['body'])) {
                return ['ok' => false, 'error' => 'Missing fields'];
            }
            $repostOf = isset($request['repost_of']) ? (int)$request['repost_of'] : null;
            return doCreatePost($request['username'], $request['body'], $repostOf);

        case "get_feed":
            if (!isset($request['username'])) {
                return ['ok' => false, 'error' => 'Missing username'];
            }
            return doGetFeed($request['username']);

        case "like_post":
            if (!isset($request['username']) || !isset($request['post_id'])) {
                return ['ok' => false, 'error' => 'Missing fields'];
            }
            return doLikePost($request['username'], (int)$request['post_id']);
    }

    return ['ok' => false, 'error' => 'Unsupported type'];
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('requestProcessor');
exit();
?>
