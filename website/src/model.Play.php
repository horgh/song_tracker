<?php
/*
 * Work with the play table
 */

require_once('Database.php');
require_once('Logger.php');
require_once('Model.php');
require_once('model.User.php');
require_once('util.Format.php');

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
}
