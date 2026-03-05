CREATE TABLE anime_cache (
  mal_id int NOT NULL,
  title varchar(255) DEFAULT NULL,
  poster text,
  score float DEFAULT NULL,
  year int DEFAULT NULL,
  genre varchar(100) DEFAULT NULL,
  updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (mal_id)
);
