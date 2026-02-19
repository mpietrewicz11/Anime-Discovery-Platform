#!/usr/bin/php
<?php
require_once('backend/path.inc');
require_once('backend/get_host_info.inc');
require_once('backend/rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");


$request = array();
$request['type'] = "login";
$request['username'] = $argv[1];
$request['password'] = $argv[2];
$request['message'] = "HI";
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

