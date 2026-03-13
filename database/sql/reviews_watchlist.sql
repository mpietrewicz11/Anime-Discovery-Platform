--user reviews table
CREATE TABLE user_reviews (
  review_id int NOT NULL AUTO_INCREMENT, -- a unique identifier for each of the review
  user_id int NOT NULL, -- goes back to who wrote a review or comment
  anime_id int NOT NULL, -- the specific anime that is being reviewed
  title varchar(255) NOT NULL, -- this is the short title of the review
  score tinyint NOT NULL, -- this is the rating from the user, allowing from 1-10 of a rating
  review_text text, -- allowing a written review by the person/user
  created_at datetime DEFAULT CURRENT_TIMESTAMP, -- this is when it was made
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- this auto updated whenever its been edited
  PRIMARY KEY (review_id),
  UNIQUE KEY user_id (user_id,anime_id)
);
--watch list table
CREATE TABLE watch_list (
  id int NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  anime_id int NOT NULL, -- the anime that is added on the watch list
  title varchar(255) NOT NULL,
  status enum('plan_of_watch','watching','done','hold','dropped') DEFAULT 'plan_of_watch', -- this is the current watch status of the anime
  added_at datetime DEFAULT CURRENT_TIMESTAMP, -- whenever its added on the list
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- the last time the status was updated
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id,anime_id)
);
