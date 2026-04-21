<?php
$params = [
	'host' => 'localhost',
	'port' => 5672,
	'login' => 'guest',
	'password' => 'guest',
	'vhost' => '/'
];
$conn = new AMQPConnection($params);
$conn->connect();
$channel = new AMQPChannel($conn);

$exchange = new AMQPExchange($channel);
$exchange->setName("logs.exchange");
$exchange->setType("fanout");
$exchange->declareExchange();

$message = json_encode([
	"lvl" => "INFO",
	"message" => "Able to test log from the anime website",
	"service" => "ADEMService",
	"user_id" => "user_123"
]);
$exchange->publish($message);
echo "Log was sent.\n";
?>
