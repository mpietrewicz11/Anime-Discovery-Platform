CREATE TABLE anime_cache (
  mal_id int NOT NULL, -- this is the MyAnimeList id an external API 
  title varchar(255) DEFAULT NULL,
  poster text, -- this is the photo/poster link to the image
  score float DEFAULT NULL, -- this is the rating score from the API
  year int DEFAULT NULL, -- the year the anime released
  genre varchar(100) DEFAULT NULL, -- the genre of the anime
  updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (mal_id)
);
