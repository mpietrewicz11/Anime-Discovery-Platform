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
$exchange->setName("logs_exchange");
$exchange->setType("fanout");

$queue = new AMQPQueue($channel);
$queue->setName("");
$queue->setFlags(AMQP_EXCLUSIVE);
$queue->declare();
$queue->bind("logs_exchange");

echo "Listening for any logs...\n";

$queue->consume(function($msg) {
	file_put_contents(
		"logs.txt"
		$msg->getBody() . PHP_EOL,
		FILE_APPEND
	);
});
