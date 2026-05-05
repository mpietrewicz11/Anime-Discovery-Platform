<?php

// Database connection
$host = "127.0.0.1";
$user = "it490app";
$pass = "123";
$db   = "it490";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

echo "Starting anime collection...\n";

// how many pages to fetch per genre (25 per page = 200 anime per genre)
define("PAGES_PER_GENRE", 8);
define("PER_PAGE", 25);
// seconds to wait between every API call so we dont hit rate limits
define("SLEEP_BETWEEN", 2);

function fetchAnime($url) {
    echo "  GET $url\n";
    $json = @file_get_contents($url);
    if (!$json) {
        echo "  Failed to fetch.\n";
        return [];
    }

    $data = json_decode($json, true);
    return $data['data'] ?? [];
}

function hasNextPage($url) {
    $json = @file_get_contents($url);
    if (!$json) return false;
    $data = json_decode($json, true);
    return !empty($data['pagination']['has_next_page']);
}

function storeAnime($conn, $anime, $genreTag) {
    $id     = (int)$anime['mal_id'];
    $title  = $conn->real_escape_string($anime['title'] ?? '');
    $poster = $conn->real_escape_string($anime['images']['jpg']['image_url'] ?? '');
    $score  = isset($anime['score']) && $anime['score'] ? (float)$anime['score'] : 0;
    $type   = $conn->real_escape_string($anime['type'] ?? '');
    $episodes = isset($anime['episodes']) ? (int)$anime['episodes'] : null;
    $url    = $conn->real_escape_string($anime['url'] ?? '');

    // store synopsis now
    $synopsis = $conn->real_escape_string($anime['synopsis'] ?? '');

    $year = null;
    if (!empty($anime['year'])) {
        $year = (int)$anime['year'];
    } elseif (!empty($anime['aired']['from'])) {
        $year = (int)date("Y", strtotime($anime['aired']['from']));
    }

    $episodesVal = $episodes !== null ? "'$episodes'" : "NULL";
    $yearVal     = $year     !== null ? "'$year'"     : "NULL";

    // using REPLACE INTO so if we run the cron again it updates stale data
    $sql = "
        REPLACE INTO anime_cache
        (mal_id, title, poster, score, year, genre, synopsis, type, episodes, url)
        VALUES
        ('$id', '$title', '$poster', '$score', $yearVal, '$genreTag', '$synopsis', '$type', $episodesVal, '$url')
    ";

    if (!$conn->query($sql)) {
        echo "  DB error for mal_id $id: " . $conn->error . "\n";
    }
}

function fetchPages($conn, $baseUrl, $genreTag, $maxPages = PAGES_PER_GENRE) {
    for ($page = 1; $page <= $maxPages; $page++) {
        $url = $baseUrl . "&page=$page";
        $list = fetchAnime($url);

        if (empty($list)) {
            echo "  No data on page $page, stopping.\n";
            break;
        }

        foreach ($list as $anime) {
            storeAnime($conn, $anime, $genreTag);
        }

        echo "  Stored " . count($list) . " anime (page $page) for '$genreTag'\n";

        // check if there is another page before sleeping
        $data = json_decode(@file_get_contents($url), true);
        $hasNext = !empty($data['pagination']['has_next_page']);

        sleep(SLEEP_BETWEEN);

        if (!$hasNext) {
            echo "  No more pages for '$genreTag'.\n";
            break;
        }
    }
}

// TOP ANIME 
echo "\nFetching Top Anime...\n";
fetchPages(
    $conn,
    "https://api.jikan.moe/v4/top/anime?limit=" . PER_PAGE . "&sfw=true",
    "Top"
);

// GENRES, getting all the genres showing in home
$genres = [
    "Romance" => ["type" => "genre",       "id" => 22],
    "Comedy"  => ["type" => "genre",       "id" => 4 ],
    "Fantasy" => ["type" => "genre",       "id" => 10],
    "Sports"  => ["type" => "genre",       "id" => 30],
    "Mystery" => ["type" => "genre",       "id" => 7 ],
    "Action"  => ["type" => "genre",       "id" => 1 ],
    "Drama"   => ["type" => "genre",       "id" => 8 ],
    "SciFi"   => ["type" => "genre",       "id" => 24],
    "Horror"  => ["type" => "genre",       "id" => 14],
    "Shounen" => ["type" => "demographic", "id" => 27],
];

foreach ($genres as $name => $info) {
    echo "\nFetching $name...\n";

    $param = $info['type'] === "demographic" ? "demographics" : "genres";
    $baseUrl = "https://api.jikan.moe/v4/anime?{$param}={$info['id']}&order_by=score&sort=desc&limit=" . PER_PAGE . "&sfw=true";

    fetchPages($conn, $baseUrl, $name);
}

echo "\nAnime collection complete.\n";

$conn->close();
?>