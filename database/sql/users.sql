CREATE TABLE users (
  id int NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(255) DEFAULT NULL,
  email varchar(255) NOT NULL,
  email_notifications tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
);
