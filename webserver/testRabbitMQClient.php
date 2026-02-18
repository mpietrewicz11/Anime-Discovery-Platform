#!/usr/bin/php
<?php

$params = [
    'host'     => '100.67.69.11',
    'port'     => 5672,
    'login'    => 'it490app',
    'password' => '123456',
    'vhost'    => '/it490'
];

$conn = new AMQPConnection($params);
$conn->connect();
$ch = new AMQPChannel($conn);

$requestQueue  = 'auth.login.request';
$responseQueue = 'auth.login.response';

$username = $argv[1] ?? 'steve';
$password = $argv[2] ?? 'password';

$payload = [
    'type' => 'login',
    'username' => $username,
    'password' => $password
];

$corrId = uniqid('', true);

$ex = new AMQPExchange($ch);
$ex->setName(''); 

$ex->publish(
    json_encode($payload),
    $requestQueue,
    AMQP_NOPARAM,
    [
        'correlation_id' => $corrId,
        'reply_to' => $responseQueue,
        'content_type' => 'application/json'
    ]
);

// wait for matching response
$q = new AMQPQueue($ch);
$q->setName($responseQueue);

$start = time();
$timeout = 5;
$response = null;

while ((time() - $start) < $timeout) {
    $msg = $q->get(AMQP_AUTOACK);
    if ($msg) {
        if ($msg->getCorrelationId() === $corrId) {
            $response = $msg->getBody();
            break;
        }
    }
    usleep(200000);
}

$conn->disconnect();

if ($response === null) {
    echo "No response (timeout)\n";

    exit(1);
}

echo "client received response:\n";
echo $response . "\n";

