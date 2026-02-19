<?php

function db() {
	$cfg = require __DIR__ . "/db_config.php";
	$dsn = "mysql:host={$cfg['host']};dbname={$cfg['db']};charset=utf8mb4";
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass']);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $pdo;
}

function handleRequest($req) {
	if (!is_array($req) || empty($req["type"]))
		return ["returnCode" => 1];
}

	$type = $req["type"];
	$u = $req["username"] ?? "";
	$p = $req["password"] ?? "";

	if ($type !== "register" && $type !== "login") {
		return ["returnCode" => 9];
	}

	if ($u == "" || $p == "") {
		return ["returnCode" => 2];
}

	$pdo = db();

	if ($type === "register") {
		try {
			$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
			$stmt->execute([$u, $p]);
			return ["returnCode" => 0, "success" => true];
}
	catch (Exception $e) {
		return ["returnCode" => 3];
}
}
//login
	$stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
	$stmt->execute([$u]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$row) return ["returnCode" => 4];
	if ($row["password"] !== $p) return ["returnCode" => 5];

	return ["returnCode" => 0, "success" => true];

}
