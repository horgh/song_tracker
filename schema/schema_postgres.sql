-- Postgresql schema

-- each song played will have one row here.
CREATE TABLE song (
  id SERIAL,
  title VARCHAR NOT NULL,
  artist VARCHAR NOT NULL,
  album VARCHAR,
  length INTEGER,
  create_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

-- make song unique & case insensitive.
CREATE UNIQUE INDEX lower_song_idx
ON songs (LOWER(title), LOWER(artist), LOWER(album));

-- NOTE: we cannot name this table as 'user' as it is reserved.
CREATE TABLE users (
  id SERIAL,
  name VARCHAR NOT NULL,
  pass VARCHAR NOT NULL,
  email VARCHAR NOT NULL,
  create_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (name),
  UNIQUE (email),
  PRIMARY KEY (id)
);

-- a single song play has one row.
CREATE TABLE play (
  id SERIAL,
  song_id INT NOT NULL REFERENCES songs(id) ON UPDATE CASCADE ON DELETE CASCADE,
  user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  create_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
