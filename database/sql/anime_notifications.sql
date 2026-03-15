CREATE TABLE anime_notifications (
  id int NOT NULL AUTO_INCREMENT, -- this adds a unique identifier within the notification setting
  user_id int NOT NULL, -- the notification to who it belongs to such as a user
  anime_id int NOT NULL, -- the specific anime the user want notis for
  anime_title varchar(255) NOT NULL,
  enabled tinyint(1) DEFAULT '1',
  last_sent_episode int DEFAULT '0', -- this is the last episode variable of number that the user was notified about
  PRIMARY KEY (id),
  UNIQUE KEY unique_user_anime (user_id,anime_id)
);
