#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');
//takes all the important stuff for registration such as username, password, email and email notifications, throws it to the database and creates account
function doRegister($username, $password, $email, $emailNotifications)
{
    $db = new loginDB();
    return $db->registerUser($username, $password, $email, $emailNotifications);
}
//sees everything like username and password for the specific user and the credentials into creating a session
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

function requestProcessor($request)
{
    if (!isset($request['type'])) {
        return ['ok' => false, 'error' => 'Invalid request'];
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

            return doRegister(
                $request['username'],
                $request['password'],
                $request['email'],
                $emailNotifications
            );

        case "login":
            return doLogin($request['username'], $request['password']);

        case "validate_session":
            return doValidate($request['sessionId']);

        default:
            return ['ok' => false, 'error' => 'Unknown request type'];
    }
}

$server = new rabbitMQServer("testRabbitMQ_register.ini", "testServer");
$server->process_requests('requestProcessor');
exit();
?>


