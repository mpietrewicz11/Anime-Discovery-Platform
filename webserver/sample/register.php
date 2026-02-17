
<?php

require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config/rabbitmq.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode("Invalid request");
    exit;
}

if (!isset($_POST["type"])) {
    echo json_encode("No request type provided");
    exit;
}

$requestType = $_POST["type"];

if ($requestType == "register") {

    if (!isset($_POST["username"]) || !isset($_POST["password"])) {
        echo json_encode("Missing credentials");
        exit;
    }

    $username = $_POST["username"];
    $password = $_POST["password"];

    // RabbitMQ register logic will go here later
 $request_id = bin2hex(random_bytes(16));
	
	$payload = [
	     "request_id" => $request_id,
	      "type" => "register",
	     "username" => $username,
	     "password" => $password,
	     "timestamp" => time()

];

	$body = json_encode($payload);
        
 	$conn = new AMQPStreamConnection(
		$config["host"],
		$config["port"],
		$config["user"],
		$config["pass"],
		$config["vhost"]
);

	$ch = $conn->channel();

	$ch-> exchange_declare(
		$config["exchange"],
		$config["exchange_type"],
		false,
		true,
		false
);

	$msg = new AMQPMessage($body, ["delivery_mode" => 2]);
	
	$ch->basic_publish($msg, $config["exchange"], "auth.register.request");

	$ch->close();
	$conn->close();

	echo json_encode([
		"status" => "queued",
		"request_id" => $request_id
//    echo json_encode("Register request received for " . $username);
]);   

 exit;
}

echo json_encode("Unsupported request type");
exit;

?>

