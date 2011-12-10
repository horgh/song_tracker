<?
/*
 * Misc database interactions
 */

require_once("Database.php");
require_once("Song.php");
require_once("User.php");
require_once("Logger.php");

class Query {

  /*
   * @return int count of plays, or -1 if failure
   */
  public static function user_count_plays($user_id) {
    if (!is_numeric($user_id)) {
      Logger::log("user_count_plays: invalid user id $user_id");
      return -1;
    }

    $db = Database::instance();
    $sql = "SELECT COUNT(id) FROM plays WHERE user_id = ?";
    $params = array($user_id);
    $rows = $db->select($sql, $params);
    if (count($rows) !== 1 || !array_key_exists('count', $rows[0])) {
      Logger::log("user_count_plays: failed to find count");
      return -1;
    }
    return $rows[0]['count'];
  }

  /*
   * @return bool whether successful
   */
  public static function add_play($user, $artist, $album, $title, $length) {
    $length = self::fix_length($length);

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
    /*
    if (self::repeat($user->get_id(), $artist, $album, $title, $length)) {
      return false;
    }
    */

    if (!$user->is_valid()) {
      Logger::log("add_play: invalid user");
      return false;
    }

    // May need a new song record before the play
    $song_id = self::insert_song($artist, $album, $title, $length);
    if ($song_id == -1) {
      Logger::log("add_play: failed to insert song");
      return false;
    }

    $db = Database::instance();
    $sql = "INSERT INTO plays (song_id, user_id) VALUES(?, ?)";
    $params = array($song_id, $user->get_id());
    return $db->manipulate($sql, $params, 1) === 1;
  }

  /*
   * @return int song id, or -1 if not found
   *
   * Used by insert_song()
   */
  private static function get_song_by_names($title, $artist, $album) {
    $db = Database::instance();
    $sql = "SELECT id FROM songs WHERE title = ? AND artist = ? AND album = ?";
    $params = array($title, $artist, $album);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("get_song_by_names: failed to retrieve song: " . $e->getMessage());
      return -1;
    }

    // if somehow failed to find, indicate with -1
    if (count($rows) !== 1 || !array_key_exists('id', $rows[0])) {
      Logger::log("get_song_by_names: failed to find song");
      return -1;
    }
    return $rows[0]['id'];
  }

  /*
   * @return int id of song matching given data
   *
   * Used by add_play()
   */
  private static function insert_song($artist, $album, $title, $length) {
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
    return self::get_song_by_names($title, $artist, $album);
  }

  /*
   * @return bool Whether last played song is identical to this given song data
   *
   * Used by add_play()
   */
  private static function repeat($user, $artist, $album, $title, $length) {
    $last = self::get_songs($user, 1);
    // No plays yet: no repeat
    if (count($last) == 0) {
      return false;
    }
    $last = $last[0];
    return $last->get_artist() == $artist && $last->get_album() == $album && $last->get_title() == $title && $last->get_length() == $length;
  }

  /*
   * @return string Length in form mm:ss
   *
   * Length given in form mm:ss or milliseconds, return in form of mm:ss
   *
   * Used by add_play()
   */
  private static function fix_length($length) {
    // If no ":" found, assume time given in milliseconds
    if (strpos($length, ":") === false) {
      $length = $length / 1000;
      $minutes = floor($length / 60);
      $seconds = $length % 60;
      $length = sprintf("%02d:%02d", $minutes, $seconds);
    }
    return $length;
  }

  /*
   * @return array of $count songs for $user
   *
   * Return in order from most recently played back
   */
  public static function get_songs($user, $count) {
    $db = Database::instance();
    $sql = "SELECT p.id, p.create_time, s.artist, s.album, s.title, s.length FROM plays p, songs s WHERE p.song_id = s.id AND p.user_id = ? ORDER BY p.create_time DESC LIMIT ?";
    $params = array($user, $count);
    $rows = $db->select($sql, $params);
    $songs = array();
    foreach ($rows as $row) {
      $songs[] = new Song($row['id'], $row['create_time'], $row['artist'], $row['album'], $row['title'], $row['length']);
    }
    return $songs;
  }

  /*
   * @return bool Whether successful
   */
  public static function add_user($user, $email, $password) {
    $hasher = new PasswordHash(12, FALSE);
    $hash = $hasher->HashPassword($password);

    $db = Database::instance();
    $sql = "INSERT INTO users (name, pass, email) VALUES(?, ?, ?)";
    $params = array($user, $hash, $email);
    try {
      $db->manipulate($sql, $params, 1);
    } catch (Exception $e) {
      Logger::log("add_user: failed to insert user");
      return false;
    }
    return true;
  }

  /*
   * @return int user id, or -1 if not found
   */
  public static function get_id_by_name($user) {
    $db = Database::instance();
    $sql = "SELECT id FROM users WHERE name = ?";
    $params = array($user);
    $rows = $db->select($sql, $params);
    if (count($rows) !== 1 || !array_key_exists('id', $rows[0])) {
      Logger::log("get_id_by_name: failed to find user");
      return -1;
    }
    return $rows[0]['id'];
  }
}
?>
