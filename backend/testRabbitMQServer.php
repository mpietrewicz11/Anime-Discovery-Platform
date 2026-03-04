#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function doRegister($username, $password)
{
    $db = new loginDB();
    return $db->registerUser($username, $password); // you must implement this
}

function doLogin($username, $password)
{
    $db = new loginDB();

    $ok = $db->validateLogin($username, $password);
    if (!$ok) {
        return ['ok' => false, 'error' => 'Invalid credentials'];
    }

    $sessionId = $db->createSession($username); // you must implement this
    if (!$sessionId) {
        return ['ok' => false, 'error' => 'Could not create session'];
    }

    return ['ok' => true, 'session_id' => $sessionId];
}

 function doValidate($sessionId)
{
    $db = new loginDB();
    $ok = $db->validateSession($sessionId); // optional, implement if needed
    return ['ok' => (bool)$ok];
}


// handlers/listeners for watchlist and reviews/rating

function doAddWatchlist($username, $animeId, $animeTitle)
{
	$db = new loginDB();
	return $db->addToWatchlist($username, $animeId, $animeTitle);
}

function doGetWatchlist($username)
{
	$db = new loginDB();
	return $db->getWatchlist($username);
}

function doAddReview($username, $animeId, $animeTitle, $rating, $reviewText)
{
	$db = new loginDB();
	return $db->addReview($username, $animeID, $animeTitle, $rating, $reviewText)
}
	
function doGetReviews($animeId)
{
	$db = new loginDB();
	return $db->getReviews($animeId);
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
            if (!isset($request['username']) || !isset($request['password'])) {
                return ['ok' => false, 'error' => 'Missing registration fields'];
            }
            $ok = doRegister($request['username'], $request['password']);
            return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'Register failed'];

        case "login":
            if (!isset($request['username']) || !isset($request['password'])) {
                return ['ok' => false, 'error' => 'Missing login fields'];
            }
            return doLogin($request['username'], $request['password']);

        case "validate_session":
            if (!isset($request['sessionId'])) {
                return ['ok' => false, 'error' => 'Missing sessionId'];
            }
            return doValidate($request['sessionId']);
    }

    return ['ok' => false, 'error' => 'Unsupported type']; 
}


$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('requestProcessor');
exit();
?>
