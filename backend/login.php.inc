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
    public function updateBio($username, $bio)
    {
        $un = $this->logindb->real_escape_string($username);
        $b  = $this->logindb->real_escape_string($bio);
        $ok = $this->logindb->query("UPDATE users SET bio = '$b' WHERE username = '$un'");
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'DB error'];
    }

    public function getProfile($username)
    {
        $un = $this->logindb->real_escape_string($username);
        $userResult = $this->logindb->query(
            "SELECT id, username, bio FROM users WHERE username = '$un' LIMIT 1"
        );
        if (!$userResult) return ['ok' => false, 'error' => 'DB error'];
        $user = $userResult->fetch_assoc();
        if (!$user) return ['ok' => false, 'error' => 'User not found'];

        $userId = (int)$user['id'];
        $user['mfa_enabled'] = $this->getMfaEnabled($userId);

        $reviewResult = $this->logindb->query(
            "SELECT title, score, review_text, created_at
             FROM user_reviews
             WHERE user_id = $userId
             ORDER BY created_at DESC"
        );
        $reviews = [];
        if ($reviewResult) {
            while ($row = $reviewResult->fetch_assoc()) $reviews[] = $row;
        }

        $watchResult = $this->logindb->query(
            "SELECT title, status, added_at
             FROM watch_list
             WHERE user_id = $userId
             ORDER BY added_at DESC"
        );
        $watchlist = [];
        if ($watchResult) {
            while ($row = $watchResult->fetch_assoc()) $watchlist[] = $row;
        }

        return ['ok' => true, 'user' => $user, 'reviews' => $reviews, 'watchlist' => $watchlist];
    }

    public function getUserByUsername($username)
    {
        $un = $this->logindb->real_escape_string($username);
        $result = $this->logindb->query(
            "SELECT id, email FROM users WHERE username = '$un' LIMIT 1"
        );
        if (!$result) return null;
        return $result->fetch_assoc();
    }

    public function getMfaEnabled($userId)
    {
        $userId = (int)$userId;
        $result = $this->logindb->query(
            "SELECT mfa_enabled FROM users WHERE id = $userId LIMIT 1"
        );
        if (!$result) return 0;
        $row = $result->fetch_assoc();
        return $row ? (int)$row['mfa_enabled'] : 0;
    }

    public function setMfaEnabled($username, $enabled)
    {
        $un  = $this->logindb->real_escape_string($username);
        $val = $enabled ? 1 : 0;
        $ok  = $this->logindb->query("UPDATE users SET mfa_enabled = $val WHERE username = '$un'");
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'DB error'];
    }

    public function storeMfaCode($userId, $code)
    {
        $userId = (int)$userId;
        $code   = $this->logindb->real_escape_string($code);
        $sql = "REPLACE INTO mfa_codes (user_id, code, expires_at, used)
                VALUES ($userId, '$code', DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0)";
        return $this->logindb->query($sql);
    }

    public function verifyMfaCode($userId, $code)
    {
        $userId = (int)$userId;
        $code   = $this->logindb->real_escape_string($code);
        $result = $this->logindb->query(
            "SELECT id FROM mfa_codes
             WHERE user_id = $userId
               AND code = '$code'
               AND used = 0
               AND expires_at > NOW()
             LIMIT 1"
        );
        if (!$result || !$result->fetch_assoc()) {
            return false;
        }
        $this->logindb->query("UPDATE mfa_codes SET used = 1 WHERE user_id = $userId");
        return true;
    }

    public function getAnimeByGenre($genre, $limit = 12)
    {
        $g = $this->logindb->real_escape_string($genre);
        $limit = (int)$limit;
        if ($limit < 1)  $limit = 12;
        if ($limit > 50) $limit = 50;

        $sql = "SELECT mal_id, title, poster, score, year
                FROM anime_cache
                WHERE genre = '$g'
                ORDER BY score DESC
                LIMIT $limit";

        $result = $this->logindb->query($sql);
        if (!$result) {
            return ['ok' => false, 'error' => 'DB error: ' . $this->logindb->error];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return ['ok' => true, 'data' => $rows];
    }

    public function createPost($userId, $body, $repostOf = null)
    {
        $userId = (int)$userId;
        $body   = $this->logindb->real_escape_string($body);

        if ($repostOf !== null) {
            $repostOf = (int)$repostOf;
            $check = $this->logindb->query("SELECT id FROM posts WHERE id = $repostOf LIMIT 1");
            if (!$check || !$check->fetch_assoc()) {
                return ['ok' => false, 'error' => 'Original post not found'];
            }
            $sql = "INSERT INTO posts (user_id, body, repost_of, created_at)
                    VALUES ($userId, '$body', $repostOf, NOW())";
        } else {
            $sql = "INSERT INTO posts (user_id, body, created_at)
                    VALUES ($userId, '$body', NOW())";
        }

        $ok = $this->logindb->query($sql);
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'DB error'];
    }

    public function getFeed($currentUserId, $limit = 30)
    {
        $currentUserId = (int)$currentUserId;
        $limit = min((int)$limit, 50);

        $sql = "
            SELECT
                p.id,
                p.body,
                p.created_at,
                p.repost_of,
                u.username       AS author,
                (SELECT COUNT(*) FROM post_likes pl  WHERE pl.post_id  = p.id)                              AS like_count,
                (SELECT COUNT(*) FROM post_likes pl2 WHERE pl2.post_id = p.id AND pl2.user_id = $currentUserId) AS user_liked,
                op.body          AS original_body,
                ou.username      AS original_author
            FROM posts p
            JOIN users u  ON p.user_id   = u.id
            LEFT JOIN posts op ON p.repost_of = op.id
            LEFT JOIN users ou ON op.user_id  = ou.id
            ORDER BY p.created_at DESC
            LIMIT $limit
        ";

        $result = $this->logindb->query($sql);
        if (!$result) {
            return ['ok' => false, 'error' => 'DB error: ' . $this->logindb->error];
        }

        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = [
                'id'              => (int)$row['id'],
                'body'            => $row['body'],
                'created_at'      => $row['created_at'],
                'repost_of'       => $row['repost_of'] !== null ? (int)$row['repost_of'] : null,
                'author'          => $row['author'],
                'like_count'      => (int)$row['like_count'],
                'user_liked'      => (bool)(int)$row['user_liked'],
                'original_body'   => $row['original_body'],
                'original_author' => $row['original_author'],
            ];
        }

        return ['ok' => true, 'posts' => $posts];
    }

    public function likePost($userId, $postId)
    {
        $userId = (int)$userId;
        $postId = (int)$postId;

        $check = $this->logindb->query(
            "SELECT user_id FROM post_likes WHERE user_id = $userId AND post_id = $postId LIMIT 1"
        );

        if ($check && $check->fetch_assoc()) {
            $this->logindb->query("DELETE FROM post_likes WHERE user_id = $userId AND post_id = $postId");
            return ['ok' => true, 'liked' => false];
        }

        $this->logindb->query("INSERT INTO post_likes (user_id, post_id) VALUES ($userId, $postId)");
        return ['ok' => true, 'liked' => true];
    }

    public function getAnimeDetail($animeId)
    {
        $aid = (int)$animeId;

        $sql = "SELECT mal_id, title, poster, score, year, episodes, type, synopsis, genre, url
                FROM anime_cache
                WHERE mal_id = $aid
                LIMIT 1";

        $result = $this->logindb->query($sql);
        if (!$result) {
            return ['ok' => false, 'error' => 'DB error: ' . $this->logindb->error];
        }

        $row = $result->fetch_assoc();
        if (!$row) {
            return ['ok' => false, 'error' => 'Not found'];
        }

        return ['ok' => true, 'data' => $row];
    }
}
?>

