-- This is deprecated and probably will no longer work, or won't soon
-- or at least is untested now

CREATE TABLE songs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  artist VARCHAR(100) NOT NULL,
  album VARCHAR(100),
  length VARCHAR(10),
  PRIMARY KEY (id),
  CONSTRAINT UNIQUE (title, artist, album)
);

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name CHAR(20) NOT NULL,
  pass CHAR(60) NOT NULL,
  email VARCHAR(60) NOT NULL,
  create_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT UNIQUE (name),
  CONSTRAINT UNIQUE (email)
);

CREATE TABLE plays (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  song_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  create_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (song_id) REFERENCES songs (id),
  FOREIGN KEY (user_id) REFERENCES users (id)
);
