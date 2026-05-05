#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function logEvent($msg) {
    exec("logger -t it490 " . escapeshellarg($msg));
    $entry = date("Y-m-d H:i:s") . " [" . gethostname() . "] " . $msg . PHP_EOL;
    file_put_contents("/var/log/it490.log", $entry, FILE_APPEND);
}

function doRegister($username, $password, $email, $emailNotifications)
{
    $db = new loginDB();
    return $db->registerUser($username, $password, $email, $emailNotifications);
}

function doLogin($username, $password)
{
    $db = new loginDB();
    $ok = $db->validateLogin($username, $password);
    if (!$ok) {
        return ['ok' => false, 'error' => 'Invalid credentials'];
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
function doGetNotifications($username)
{
    $db = new loginDB();
    $list = $db->getNotifications($username);
    return ['ok' => true, 'data' => is_array($list) ? $list : []];
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
                return ['ok' => false, 'error' => 'Missing login fields'];
            }
            $result = doLogin($request['username'], $request['password']);
            logEvent("login attempt: " . $request['username'] . " result: " . ($result['ok'] ? 'success' : 'failed'));
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

        case "get_notifications":
            if (!isset($request['username'])) {
                return ['ok' => false, 'error' => 'Missing username'];
            }
            return doGetNotifications($request['username']);

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
    }

    return ['ok' => false, 'error' => 'Unsupported type'];
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('requestProcessor');
exit();
?>