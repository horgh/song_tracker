--
-- this is an update to the original postgres schema.
--
-- NOTE: the base schema file is already updated to reflect these
--   changes as well.
--

-- add create_timestamp to all songs table.
ALTER TABLE songs
ADD COLUMN
create_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;

UPDATE songs
SET create_time = NULL;

-- change create_time columns from
-- TIMESTAMP WITHOUT TIME ZONE to
-- TIMESTAMP WITH TIME ZONE.

-- users.
ALTER TABLE users
ADD COLUMN create_time_temp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;

UPDATE users
SET create_time_temp = create_time;

ALTER TABLE users
DROP COLUMN create_time;

ALTER TABLE users
RENAME COLUMN create_time_temp TO create_time;

-- plays.
ALTER TABLE plays
ADD COLUMN create_time_temp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;

UPDATE plays
SET create_time_temp = create_time;

ALTER TABLE plays
DROP COLUMN create_time;

ALTER TABLE plays
RENAME COLUMN create_time_temp TO create_time;

-- rename the tables.
ALTER TABLE songs RENAME TO song;
-- we cannot rename users table to user. it is reserved.
--ALTER TABLE users RENAME TO user;
ALTER TABLE plays RENAME TO play;

-- drop a duplicate constraint.
ALTER TABLE song
DROP CONSTRAINT songs_title_artist_album_key;
