CREATE TABLE bundles (
  id int NOT NULL AUTO_INCREMENT,
  bundle_name varchar(255) DEFAULT NULL,
  version varchar(50) DEFAULT NULL,
  filename varchar(255) DEFAULT NULL,
  status varchar(20) DEFAULT 'new',
  target varchar(50) DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  deployed_at datetime DEFAULT NULL,
  PRIMARY KEY (id)
);
