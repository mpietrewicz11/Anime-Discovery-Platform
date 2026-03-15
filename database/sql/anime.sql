CREATE TABLE anime (
  anime_id int NOT NULL AUTO_INCREMENT, -- provides a unique id to each anime within the database
  title varchar(255) NOT NULL, -- the title of the anime
  score decimal(3,1) NOT NULL, -- the score/rating for example 6.8
  episodes int DEFAULT NULL, -- the amount of episodes the anime has
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (anime_id)
);
