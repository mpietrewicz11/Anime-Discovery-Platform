<?php
$params = [
	'host' => 'localhost',
	'port' => 5672,
	'login' => 'guest',
	'password' => 'guest',
	'vhost' => '/'
];
try {
$conn = new AMQPConnection($params);
$conn->connect();
$channel = new AMQPChannel($conn);
$exchange = new AMQPExchange($channel);
$exchange->setName("logs.exchange");
$exchange->setType("fanout");
$exchange->declareExchange();

$queue = new AMQPQueue($channel);
$queue->setName("logs_queue");
$queue->setFlags(AMQP_EXCLUSIVE);
$queue->declareQueue();
$queue->bind("logs.exchange");

echo "Listening for any logs...\n";

$queue->consume(function($msg) {
	$payload = [
		"time" => date("c"),
		"lvl" => "INFO",
		"message" => $msg->getBody(),
		"service" => "ServiceName",
		"host" => gethostname(),
		"user_id" => "user_id_any"
	];
	$logEntry = json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL;

	try {
		file_put_contents("loggings.txt", $logEntry, FILE_APPEND);
	} catch (Exception $e) {
		echo "Error of writing to a logging file: " . $e->getMessage() . "\n";
	} 
	
});
} catch (Exception $e) {
	echo "Error message: " . $e->getMessage() . "\n";
}
?>
