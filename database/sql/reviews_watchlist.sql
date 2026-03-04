--user reviews table
CREATE TABLE user_reviews (
  review_id int NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  anime_id int NOT NULL,
  title varchar(255) NOT NULL,
  score tinyint NOT NULL,
  review_text text,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (review_id),
  UNIQUE KEY user_id (user_id,anime_id)
);
--watch list table
CREATE TABLE watch_list (
  list_id int NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  anime_id int NOT NULL,
  title varchar(255) NOT NULL,
  status enum('plan_of_watch','watching','done','hold','dropped') DEFAULT 'plan_of_watch',
  added_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (list_id),
  UNIQUE KEY user_id (user_id,anime_id)
);
