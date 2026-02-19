<?php

class loginDB
{
private $logindb;

public function __construct()
{
	$this->logindb = new mysqli("100.102.151.59","it490","123","it490");

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

public function validateLogin($username,$password)
{
	$un = $this->logindb->real_escape_string($username);

	$statement = "select * from users where username = '$un'";
	$response = $this->logindb->query($statement);

	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $username".PHP_EOL;
		if (password_verify($password, $row["password"]))
		{
			echo "passwords match for $username".PHP_EOL;
			return 1;// password match
		}
		echo "passwords did not match for $username".PHP_EOL;
	}
	return 0;//no users matched username
}

	public function registerUser($username, $password)
{
    $un = $this->logindb->real_escape_string($username);
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $safeHash = $this->logindb->real_escape_string($hash);
    $statement = "INSERT INTO users (username, password) VALUES ('$un', '$safeHash')";
    return $this->logindb->query($statement);
}

public function createSession($username)
{
    $un = $this->logindb->real_escape_string($username);
    $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");
    $row = $result->fetch_assoc();
    if (!$row) return false;

    $sessionKey = bin2hex(random_bytes(32));
    $safeKey = $this->logindb->real_escape_string($sessionKey);
    $userId = $row['id'];

    $this->logindb->query(
        "INSERT INTO sessions (user_id, session_key, created_at) VALUES ($userId, '$safeKey', NOW())"
    );
    return $sessionKey;
}

public function validateSession($sessionId)
{
    $safeId = $this->logindb->real_escape_string($sessionId);
    $result = $this->logindb->query("SELECT id FROM sessions WHERE session_key = '$safeId'");
    return (bool)$result->fetch_assoc();
}

}
?>
