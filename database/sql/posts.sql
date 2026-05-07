CREATE TABLE IF NOT EXISTS posts (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT  NOT NULL,
  body      TEXT NOT NULL,
  repost_of INT  DEFAULT NULL,
  created_at DATETIME DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS post_likes (
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  UNIQUE KEY unique_like (user_id, post_id)
);
