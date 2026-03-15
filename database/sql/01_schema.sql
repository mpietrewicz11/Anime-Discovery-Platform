CREATE DATABASE IF NOT EXISTS it490;
USE it490;
CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY, -- a unique identifier for each of the users
	username VARCHAR(50) NOT NULL UNIQUE, -- username that is going to be used for the login
	password VARCHAR(50) NOT NULL );
