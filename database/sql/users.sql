CREATE TABLE users (
  id int NOT NULL AUTO_INCREMENT, -- this is the unique user identifier
  username varchar(50) NOT NULL, -- the ability to have a unique username for the login
  password varchar(255) DEFAULT NULL,
  email varchar(255) NOT NULL, -- this is the users email address
  email_notifications tinyint(1) DEFAULT '1', -- the 1 is enabled for notifications while as 0 is disabled
  bio text,
  PRIMARY KEY (id),
  UNIQUE KEY username (username) -- this actually prevents multiple or duplicate usernames
);
