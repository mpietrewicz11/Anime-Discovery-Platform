CREATE TABLE anime (
  anime_id int NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  score decimal(3,1) NOT NULL,
  episodes int DEFAULT NULL,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (anime_id)
);
