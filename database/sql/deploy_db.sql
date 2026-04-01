create database deploy_db;
use deploy_db;
create user 'deploy'@'localhost' identified by '123';
grant all on deploy_db.* to 'deploy'@'localhost';
create table bundles (
		id int primary key auto_increment,
		bundle_name varchar(255),
		version varchar(255),
		filename varchar(255),
		status varchar(20) default 'new',
		target varchar(50),
		created_at timestamp default current_timestamp,
		deployed_at datetime );




