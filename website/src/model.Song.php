<?php
/*
 * Work with the song table
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
    $sql = '
SELECT
p.id AS play_id,
p.create_time,
s.id,
s.artist,
s.album,
s.title,
s.length
FROM play p,
song s
WHERE
p.song_id = s.id
AND p.user_id = ?
ORDER BY p.create_time DESC
LIMIT ?
';
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
