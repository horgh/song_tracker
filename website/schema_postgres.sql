-- Postgresql schema

-- Each song that played will have one entry here
CREATE TABLE songs (
  id SERIAL,
  title VARCHAR NOT NULL,
  artist VARCHAR NOT NULL,
  album VARCHAR,
  length VARCHAR,
  UNIQUE (title, artist, album),
  PRIMARY KEY (id)
);
-- Make song unique & case insensitive
CREATE UNIQUE INDEX lower_song_idx ON songs (LOWER(title), LOWER(artist), LOWER(album));

-- Each user
CREATE TABLE users (
  id SERIAL,
  name VARCHAR NOT NULL,
  pass VARCHAR NOT NULL,
  email VARCHAR NOT NULL,
  create_time TIMESTAMP DEFAULT current_timestamp,
  UNIQUE (name),
  UNIQUE (email),
  PRIMARY KEY (id)
);

-- A single song play has one row
CREATE TABLE plays (
  id SERIAL,
  song_id INT NOT NULL REFERENCES songs(id) ON UPDATE CASCADE ON DELETE CASCADE,
  user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  create_time TIMESTAMP DEFAULT current_timestamp,
  PRIMARY KEY (id)
);
