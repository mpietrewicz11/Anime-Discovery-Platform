
<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode("Invalid request");
    exit;
}

if (!isset($_POST["type"])) {
    echo json_encode("No request type provided");
    exit;
}

$requestType = $_POST["type"];

if ($requestType == "login") {

    if (!isset($_POST["username"]) || !isset($_POST["password"])) {
        echo json_encode("Missing credentials");
        exit;
    }

    $username = $_POST["username"];
    $password = $_POST["password"];

    // rabbitmq will go here later

    echo json_encode("Login request received for " . $username);
    exit;
}

echo json_encode("Unsupported request type");
exit;

