<?php

class loginDB
{
    private $logindb;

    public function __construct()
    {
        $this->logindb = new mysqli("127.0.0.1", "it490", "123", "it490");

        if ($this->logindb->connect_errno != 0)
        {
            error_log("Error connecting to database: " . $this->logindb->connect_error);
            exit(1);
        }
    }

    public function validateLogin($username, $password)
    {
        $un = $this->logindb->real_escape_string($username);

        $statement = "SELECT * FROM users WHERE username = '$un'";
        $response = $this->logindb->query($statement);

        if (!$response) {
            return 0;
        }

        while ($row = $response->fetch_assoc())
        {
            if (password_verify($password, $row["password"]))
            {
                return 1;
            }
        }

        return 0;
    }

    public function registerUser($username, $password, $email, $emailNotifications = 0)
    {
        $un = $this->logindb->real_escape_string($username);
        $em = $this->logindb->real_escape_string($email);
        $emailNotifications = (int)$emailNotifications;

        $checkUser = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");
        if ($checkUser && $checkUser->fetch_assoc()) {
            return ['ok' => false, 'error' => 'Username already exists'];
        }

        $checkEmail = $this->logindb->query("SELECT id FROM users WHERE email = '$em'");
        if ($checkEmail && $checkEmail->fetch_assoc()) {
            return ['ok' => false, 'error' => 'Email already exists'];
        }

	// $password already is the hash, and we need to escape it for the SQL query.
        $safeHash = $this->logindb->real_escape_string($password);

        $statement = "INSERT INTO users (username, password, email, email_notifications)
                      VALUES ('$un', '$safeHash', '$em', $emailNotifications)";

        $ok = $this->logindb->query($statement);
	return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'Register failed'];
    }

    public function createSession($username)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return false;

        $row = $result->fetch_assoc();
        if (!$row) return false;

        $sessionKey = bin2hex(random_bytes(32));
        $safeKey = $this->logindb->real_escape_string($sessionKey);
        $userId = (int)$row['id'];

        $ok = $this->logindb->query(
            "INSERT INTO sessions (user_id, session_key, created_at) VALUES ($userId, '$safeKey', NOW())"
        );

        if (!$ok) return false;

        return $sessionKey;
    }

    public function validateSession($sessionId)
    {
        $safeId = $this->logindb->real_escape_string($sessionId);
        $result = $this->logindb->query("SELECT id FROM sessions WHERE session_key = '$safeId'");

        if (!$result) return false;

        return (bool)$result->fetch_assoc();
    }

    public function addToWatchlist($username, $animeId, $animeTitle)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return false;

        $row = $result->fetch_assoc();
        if (!$row) return false;

        $userId = (int)$row['id'];
        $aid = (int)$animeId;
        $title = $this->logindb->real_escape_string($animeTitle);

        $check = $this->logindb->query("SELECT id FROM watch_list WHERE user_id = $userId AND anime_id = $aid");
        if ($check && $check->fetch_assoc()) return false;

        $statement = "INSERT INTO watch_list (user_id, anime_id, title, added_at)
                      VALUES ($userId, $aid, '$title', NOW())";

        return $this->logindb->query($statement);
    }

    public function getWatchlist($username)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return false;

        $row = $result->fetch_assoc();
        if (!$row) return false;

        $userId = (int)$row['id'];

        $response = $this->logindb->query("SELECT anime_id, title, added_at FROM watch_list WHERE user_id = $userId");
        if (!$response) return [];

        $list = [];
        while ($row = $response->fetch_assoc())
        {
            $list[] = $row;
        }

        return $list;
    }

    public function addReview($username, $animeId, $animeTitle, $rating, $reviewText)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return false;

        $row = $result->fetch_assoc();
        if (!$row) return false;

        $userId = (int)$row['id'];
        $aid = (int)$animeId;
        $title = $this->logindb->real_escape_string($animeTitle);
        $rating = (int)$rating;
        $text = $this->logindb->real_escape_string($reviewText);

        $statement = "REPLACE INTO user_reviews (user_id, anime_id, title, score, review_text, created_at)
                      VALUES ($userId, $aid, '$title', $rating, '$text', NOW())";

        return $this->logindb->query($statement);
    }

    public function getReviews($animeId)
    {
        $aid = (int)$animeId;
        $statement = "SELECT u.username, r.score, r.review_text, r.created_at
                      FROM user_reviews r
                      JOIN users u ON r.user_id = u.id
                      WHERE r.anime_id = $aid";

        $response = $this->logindb->query($statement);
        if (!$response) return [];

        $list = [];
        while ($row = $response->fetch_assoc())
        {
            $list[] = $row;
        }

        return $list;
    }

    public function getTopAnime($limit = 12)
    {
        $limit = (int)$limit;
        if ($limit < 1) $limit = 12;
        if ($limit > 50) $limit = 50;

        $sql = "SELECT mal_id, title, score, episodes, poster, year
                FROM anime_cache
                WHERE genre = 'Top'
                ORDER BY score DESC
                LIMIT $limit";

        $result = $this->logindb->query($sql);
        if (!$result) {
            return ["ok" => false, "error" => "DB error: " . $this->logindb->error];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return ["ok" => true, "data" => $rows];
    }

    public function logindb_escape($str)
    {
        return $this->logindb->real_escape_string($str);
    }

    public function upsertAnime($animeId, $title, $score, $episodes)
    {
        $title = $this->logindb->real_escape_string($title);
        $score = is_numeric($score) ? $score : 0;
        $episodes = (int)$episodes;
        $animeId = (int)$animeId;

        $statement = "INSERT INTO anime (anime_id, title, score, episodes, updated_at)
                      VALUES ($animeId, '$title', $score, $episodes, NOW())
                      ON DUPLICATE KEY UPDATE
                      title = '$title', score = $score, episodes = $episodes, updated_at = NOW()";

        return $this->logindb->query($statement);
    }

    public function addNotification($username, $animeId, $animeTitle)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return false;

        $row = $result->fetch_assoc();
        if (!$row) return false;

        $userId = (int)$row['id'];
        $aid = (int)$animeId;
        $title = $this->logindb->real_escape_string($animeTitle);

        $check = $this->logindb->query("SELECT id FROM anime_notifications WHERE user_id = $userId AND anime_id = $aid");
        if ($check && $check->fetch_assoc()) {
            return $this->logindb->query("
                UPDATE anime_notifications
                SET enabled = 1, anime_title = '$title'
                WHERE user_id = $userId AND anime_id = $aid
            ");
        }

        $statement = "
            INSERT INTO anime_notifications (user_id, anime_id, anime_title, enabled, last_sent_episode)
            VALUES ($userId, $aid, '$title', 1, 0)
        ";

        return $this->logindb->query($statement);
    }

    public function removeNotification($username, $animeId)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return false;

        $row = $result->fetch_assoc();
        if (!$row) return false;

        $userId = (int)$row['id'];
        $aid = (int)$animeId;

        return $this->logindb->query("
            UPDATE anime_notifications
            SET enabled = 0
            WHERE user_id = $userId AND anime_id = $aid
        ");
    }

    public function getNotifications($username)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query("SELECT id FROM users WHERE username = '$un'");

        if (!$result) return [];

        $row = $result->fetch_assoc();
        if (!$row) return [];

        $userId = (int)$row['id'];

        $response = $this->logindb->query("
            SELECT anime_id, anime_title, enabled, last_sent_episode
            FROM anime_notifications
            WHERE user_id = $userId
        ");

        if (!$response) return [];

        $list = [];
        while ($row = $response->fetch_assoc()) {
            $list[] = $row;
        }

        return $list;
    }

    public function getAnimeList($genre)
    {
        $g = $this->logindb->real_escape_string($genre);
        $statement = "SELECT mal_id, title, poster, score, year
                      FROM anime_cache
                      WHERE genre = '$g'
                      ORDER BY score DESC
                      LIMIT 25";

        $response = $this->logindb->query($statement);
        if (!$response) {
            return ['ok' => false, 'error' => 'DB query failed'];
        }

        $list = [];
        while ($row = $response->fetch_assoc())
        {
            $list[] = $row;
        }

        return ['ok' => true, 'data' => $list];
    }
}
?>

