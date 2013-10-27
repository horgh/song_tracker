-- @param VARCHAR p_artist The artist name
-- @param VARCHAR p_album The album name
-- @param VARCHAR p_title The song title
-- @param INTEGER p_length The song length
--
-- @return INTEGER The song id. NULL if failure.
--
-- see if the song exists. add it if not.
CREATE OR REPLACE FUNCTION
get_song(VARCHAR, VARCHAR, VARCHAR, INTEGER)
RETURNS INTEGER
AS $get_song$
DECLARE
  p_artist ALIAS FOR $1;
  p_album ALIAS FOR $2;
  p_title ALIAS FOR $3;
  p_length ALIAS FOR $4;
  l_song_id INTEGER;
BEGIN
  -- we require that artist, album, title are non-zero length.
  IF LENGTH(p_artist) = 0 OR LENGTH(p_album) = 0
    OR LENGTH(p_title) = 0
  THEN
    RAISE NOTICE 'Artist, album, and title may not be blank.';
    RETURN NULL;
  END IF;

  -- check we have a length > 0.
  IF p_length <= 0
  THEN
    RAISE NOTICE 'Length must be > 0.';
    RETURN NULL;
  END IF;

  -- try to find the song in the table already.
  SELECT id INTO l_song_id FROM song
  WHERE artist ILIKE p_artist AND album ILIKE p_album AND title ILIKE p_title;
  IF FOUND
  THEN
    RETURN l_song_id;
  END IF;

  -- we need to add the song.
  SELECT NEXTVAL('songs_id_seq') INTO l_song_id;
  IF NOT FOUND
  THEN
    RAISE NOTICE 'Failed to retrieve next song id.';
    RETURN FALSE;
  END IF;

  INSERT INTO song (id, artist, album, title, length)
  VALUES(l_song_id, p_artist, p_album, p_title, p_length);
  IF NOT FOUND
  THEN
    RAISE NOTICE 'Failed to insert song.';
    RETURN FALSE;
  END IF;

  RETURN l_song_id;
END;
$get_song$
LANGUAGE plpgsql;

-- @param INTEGER p_user_id The user id that will have the play
-- @param VARCHAR p_artist The artist name
-- @param VARCHAR p_album The album name
-- @param VARCHAR p_title The song title
-- @param INTEGER p_length The song length
--
-- @return BOOLEAN Whether we were able to add the play
--
-- we first ensure that we have the song in the database. record it if
-- necessary.
--
-- we then add a play for the user.
--
-- we do not need to be in a transaction - if we add a song but cannot add
-- a play for some reason, that is not really a problem.
CREATE OR REPLACE FUNCTION
add_play(INTEGER, VARCHAR, VARCHAR, VARCHAR, INTEGER)
RETURNS BOOLEAN
AS $add_play$
DECLARE
  p_user_id ALIAS FOR $1;
  p_artist ALIAS FOR $2;
  p_album ALIAS FOR $3;
  p_title ALIAS FOR $4;
  p_length ALIAS FOR $5;
  l_song_id INTEGER;
BEGIN
  -- ensure we have a valid user.
  PERFORM id FROM users WHERE id = p_user_id;
  IF NOT FOUND
  THEN
    RAISE NOTICE 'User id not found.';
    RETURN FALSE;
  END IF;

  -- NOTE: we don't need to validate the song information - that gets handled
  --   when we try to find/add the song.

  -- find the song (add if necessary).
  SELECT get_song(p_artist, p_album, p_title, p_length)
  INTO STRICT l_song_id;
  IF NOT FOUND OR l_song_id IS NULL
  THEN
    RAISE NOTICE 'Failed to retrieve song.';
    RETURN FALSE;
  END IF;

  -- add the play.
  INSERT INTO play (user_id, song_id)
  VALUES(p_user_id, l_song_id);
  IF NOT FOUND
  THEN
    RAISE NOTICE 'Failed to insert play.';
    RETURN FALSE;
  END IF;
  RETURN TRUE;
END;
$add_play$
LANGUAGE plpgsql;
