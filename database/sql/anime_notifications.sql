CREATE TABLE anime_notifications (
  id int NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  anime_id int NOT NULL,
  anime_title varchar(255) NOT NULL,
  enabled tinyint(1) DEFAULT '1',
  last_sent_episode int DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY unique_user_anime (user_id,anime_id)
);
