<?php
/*
 * Work with the songs table
 */

require_once("Database.php");
require_once("Logger.php");
require_once("Model.php");

class Song extends Model {
  protected $fields = array(
                          'id',
                          'title',
                          'artist',
                          'album',
                          'length',
                         );

  /*
   * @return int id of song matching given data
   *
   * Used by add_play()
   */
  public static function insert_song($artist, $album, $title, $length) {
    $db = Database::instance();

    // First attempt to insert new song row
    $sql = "INSERT INTO songs (artist, album, title, length) VALUES(?, ?, ?, ?)";
    $params = array($artist, $album, $title, $length);
    // We can expect to fail (if song is already in db)
    try {
      $count = $db->manipulate($sql, $params, 1);
    } catch (Exception $e) {
      Logger::log("insert_song: failure inserting song. Is it already in the"
                . " database? Error: " . $e->getMessage()
                . " SQL: $sql Params: " . print_r($params, 1));
    }

    // Regardless, we need to now get the id of the song from db
    return self::get_song_id_by_names($title, $artist, $album);
  }

  /*
   * @return int song id, or -1 if not found
   *
   * Used by insert_song()
   */
  public static function get_song_id_by_names($title, $artist, $album) {
    $db = Database::instance();
    $sql = "SELECT id FROM songs"
         . " WHERE LOWER(title) = LOWER(?)"
         . "  AND LOWER(artist) = LOWER(?)"
         . "  AND LOWER(album) = LOWER(?)";
    $params = array($title, $artist, $album);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("get_song_id_by_names: failed to retrieve song: " . $e->getMessage());
      return -1;
    }

    // if somehow failed to find, indicate with -1
    if (count($rows) !== 1 || !array_key_exists('id', $rows[0])) {
      Logger::log("get_song_id_by_names: failed to find song");
      return -1;
    }
    return $rows[0]['id'];
  }

  /*
   * @return array of $count songs for $user
   *
   * Return in order from most recently played back
   */
  public static function get_users_latest_songs(User $user, $count) {
    if (!is_numeric($count)) {
      Logger::log("get_users_latest_songs: invalid count value");
      return array();
    }

    $db = Database::instance();
    $sql = "SELECT p.id AS play_id, p.create_time, s.id, s.artist, s.album, s.title, s.length"
         . " FROM plays p, songs s"
         . " WHERE p.song_id = s.id AND p.user_id = ?"
         . " ORDER BY p.create_time DESC"
         . " LIMIT ?";
    $params = array($user->id, $count);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("get_users_latest_songs: database failure: " . $e->getMessage());
      return array();
    }

    $songs = array();
    foreach ($rows as $row) {
      $song = new self();
      if (!$song->fill_fields($row)) {
        Logger::log("get_users_latest_songs: failed to build song object");
        return array();
      }

      $play = new Play();
      if (!$play->fill_fields(array(
                                    'id' => $row['play_id'],
                                    'song_id' => $row['id'],
                                    'user_id' => $user->id,
                                    'create_time' => $row['create_time'],
                                   )))
      {
        Logger::log("get_users_latest_songs: failed to build play object");
        return array();
      }
      $play->song = $song;
      $song->play = $play;

      $songs[] = $song;
    }
    return $songs;
  }
}
?>
