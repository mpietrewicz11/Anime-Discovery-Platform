CREATE TABLE users (
  id int NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
);
