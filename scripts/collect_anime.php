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


function fetchAnime($url) {
    $json = file_get_contents($url);
    if (!$json) {
        echo "Failed to fetch API\n";
        return [];
    }

    $data = json_decode($json, true);

    if (!isset($data['data'])) {
        return [];
    }

    return $data['data'];
}


function storeAnime($conn, $anime, $genreTag) {

    $id = $anime['mal_id'];
    $title = $conn->real_escape_string($anime['title']);
    $poster = $conn->real_escape_string($anime['images']['jpg']['image_url']);
    $score = $anime['score'] ?? 0;

    $year = null;
    if (isset($anime['aired']['from'])) {
        $year = date("Y", strtotime($anime['aired']['from']));
    }

    $sql = "
    REPLACE INTO anime_cache
    (mal_id, title, poster, score, year, genre)
    VALUES
    ('$id', '$title', '$poster', '$score', '$year', '$genreTag')
    ";

    $conn->query($sql);
}


// TOP ANIME
echo "Fetching Top Anime...\n";

$topAnime = fetchAnime("https://api.jikan.moe/v4/top/anime?limit=25&sfw=true");

foreach ($topAnime as $anime) {
    storeAnime($conn, $anime, "Top");
}

sleep(2);


// GENRES TO COLLECT
$genres = [
    "Romance" => 22,
    "Comedy" => 4,
    "Fantasy" => 10,
    "Sports" => 30,
    "Mystery" => 7
];

foreach ($genres as $name => $id) {

    echo "Fetching $name...\n";

    $url = "https://api.jikan.moe/v4/anime?genres=$id&order_by=score&sort=desc&limit=20&sfw=true";

    $animeList = fetchAnime($url);

    foreach ($animeList as $anime) {
        storeAnime($conn, $anime, $name);
    }

    sleep(2); // avoid rate limit
}


echo "Anime collection complete.\n";

$conn->close();

?>
