<?php
/*
 * Work with the plays table
 */

require_once("Database.php");
require_once("Logger.php");
require_once("Model.php");
require_once("model.User.php");
require_once("util.Format.php");

class Play extends Model {
  protected $fields = array(
    'id',
    'song_id',
    'user_id',
    'create_time',
  );
  
  /*
   * Overload parent method
   */
  public function query_by_id($id) {
    if (!parent::query_by_id($id)) {
      return false;
    }
    $this->time_since = Format::timeSince($this->create_time);
    return true;
  }

  /*
   * @param array $row Row of data from db
   *
   * @return bool Whether successful
   *
   * Overload parent method
   */
  public function fill_fields(array $row) {
    if (!parent::fill_fields($row)) {
      Logger::log("fill_fields: failed to call parent's fill_fields()");
      return false;
    }
    $this->time_since = Format::timeSince($this->create_time);
    return true;
  }

  /*
   * @return int Play count of given user, or -1 if failure
   */
  public static function user_play_count(User $user) {
    if (!isset($user->id)) {
      Logger::log("user_play_count: invalid user id");
      return -1;
    }

    $db = Database::instance();
    $sql = "SELECT COUNT(1) FROM plays WHERE user_id = ?";
    $params = array($user->id);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("user_play_count: database failure: " . $e->getMessage());
      return -1;
    }
    if (count($rows) !== 1 || !array_key_exists('count', $rows[0])) {
      Logger::log("user_play_count: count not found in result");
      return -1;
    }
    return $rows[0]['count'];
  }

  /*
   * @return bool whether successful
   */
  public static function add_play(User $user, $artist, $album, $title,
    $length)
  {
    $length = Format::fix_length($length);

    // unknown artist/album ("") becomes "N/A"
    if ($artist == "") {
      $artist = "N/A";
    }
    if ($album == "") {
      $album = "N/A";
    }

    // do not allow blank title
    if ($title == "") {
      Logger::log("add_play: invalid title");
      return false;
    }

    // do not add if last song for user is identical
    //if (self::repeat($user->get_id(), $artist, $album, $title, $length)) {
    //  return false;
    //}

    if ($user->authenticate()) {
      Logger::log("add_play: invalid user");
      return false;
    }

    // May need a new song record before the play
    // XXX: insert song should check if it exists first !
    $song_id = Song::insert_song($artist, $album, $title, $length);
    if ($song_id == -1) {
      Logger::log("add_play: failed to insert song");
      return false;
    }

    $db = Database::instance();
    // Now we can record the play. However, if a play has occurred within
    // the past few seconds that indicates the previous play was not really
    // played (was switched away from 'instantaneously'), so remove that
    // false play prior to adding ours
    $db->beginTransaction();
    if (self::remove_invalid_previous_play($user) === -1) {
      $db->rollBack();
      Logger::log("add_play: failure removing any invalid previous play");
      return false;
    }

    // Record the play
    $sql = "INSERT INTO plays (song_id, user_id) VALUES(?, ?)";
    $params = array($song_id, $user->id);
    if ($db->manipulate($sql, $params, 1, true) !== 1) {
      $db->rollBack();
      Logger::log("add_play: unexpectedly did not have row affected");
      return false;
    }
    $db->commit();
    return true;
  }

  /*
   * @param User $user
   *
   * @return int Number of plays removed, or -1 if failure
   *
   * We do not have to remove a play in order to be a success, only not
   * encounter an error
   *
   * Remove a play if it is within some delta of the current time as we will
   * assume that it was not really played (next play was too close to the
   * previous)
   *
   * We require the song to be greater in length than the interval as well
   */
  private static function remove_invalid_previous_play(User $user) {
    // XXX: make this a config variable
    // 20 seconds!
    $secs = 20;
    // for margin of error, add 5 seconds to required song length
    $millisecs = ($secs + 5) * 1000;
    $sql = '
DELETE FROM plays p
WHERE
p.user_id = ?
AND p.create_time > current_timestamp - CAST(? AS INTERVAL)
AND p.song_id IN
  (SELECT s.id
   FROM songs s
   WHERE
   s.id = p.song_id
   AND s.length > ?
  )
';
    $params = array($user->id, "$secs seconds", $millisecs);
    $db = Database::instance();
    try {
      $count = $db->manipulate($sql, $params, NULL, true);
    } catch (Exception $e) {
      Logger::log("remove_invalid_previous_play: database failure: "
        . $e->getMessage());
      return -1;
    }
    Logger::log("remove_invalid_previous_play: Removed $count invalid"
      . " prior plays.");
    return $count;
  }

  /*
   * @return bool Whether last played song is identical to this given song data
   *
   * Used by add_play()
   */
  private static function repeat(User $user, $artist, $album, $title, $length) {
    $lastPlaySongs = User::get_users_latest_songs($user, 1);

    // No plays yet: no repeat
    if (count($lastPlaySongs) == 0) {
      return false;
    }

    $last = $lastPlaySongs[0];
    return $last->artist == $artist && $last->album == $album
      && $last->title == $title && $last->length == $length;
  }

}
