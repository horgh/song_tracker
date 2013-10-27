<?php
/*
 * Work with the play table
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
    $sql = "SELECT COUNT(1) FROM play WHERE user_id = ?";
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

  //! store a new song play for a user.
  /*!
   * @param User $user
   * @param string $artist
   * @param string $album
   * @param string $title
   * @param mixed $length
   *
   * @return bool whether successful
   *
   * @pre The user must already be authenticated
   *
   * the artist or album may be blank. if so, they are stored as 'N/A'.
   *
   * the title is required.
   *
   * the length will be converted to milliseconds if possible.
   */
  public static function add_play(User $user, $artist, $album, $title,
    $length)
  {
    // ensure we have the length in milliseconds.
    $length = Format::fix_length($length);
    if ($length === -1) {
      Logger::log("add_play: failed to convert length to milliseconds");
      return false;
    }

    // unknown artist/album ("") becomes "N/A"
    if ($artist === "") {
      $artist = "N/A";
    }
    if ($album === "") {
      $album = "N/A";
    }

    // we do not allow a blank song title.
    if ($title === "") {
      Logger::log("add_play: invalid title");
      return false;
    }

    $db = Database::instance();
    $sql = 'SELECT add_play(?, ?, ?, ?, ?) AS success';
    $params = array($user->id, $artist, $album, $title, $length);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("add_play: database failure: " . $e->getMessage());
      return false;
    }

    return count($rows) === 1 && $rows[0]['success'] === true;
  }
}
