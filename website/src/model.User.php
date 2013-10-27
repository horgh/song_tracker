<?php
/*
 * Work with the users table
 */

require_once("include/phpass/PasswordHash.php");
require_once("Database.php");
require_once("Logger.php");
require_once("Model.php");
require_once("model.Play.php");

class User extends Model {
  protected $fields = array(
    'id',
    'name',
    'pass',
    'email',
    'create_time',
  );

  /*
   * @param string $name Name of user to find
   *
   * @return bool whether successful
   */
  public function query_by_name($name) {
    if (strlen($name) === 0) {
      Logger::log("query_by_name: invalid name");
      return false;
    }
    return $this->query_by_field('name', $name);
  }

  /*
   * @return bool Whether successful
   */
  public function register($user, $email, $password) {
    if (strlen($user) === 0 || strlen($email) === 0
      || strlen($password) === 0)
    {
      Logger::log("register: invalid user or email or password");
      return false;
    }

    $hasher = new PasswordHash(12, FALSE);
    $hash = $hasher->HashPassword($password);

    $this->name = $user;
    $this->email = $email;
    $this->pass = $hash;

    return $this->store();
  }

  /*
   * @return array of strings: names of users from db
   */
  public static function get_users_names() {
    $db = Database::instance();
    $sql = "SELECT name FROM users ORDER BY name ASC";
    $params = array();

    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("get_user_names: Failed to retrieve names: "
        . $e->getMessage());
      return array();
    }

    $userlist = array();
    foreach ($rows as $row) {
      $userlist[] = $row['name'];
    }
    return $userlist;
  }

  /*
   * @return bool Whether object has been initialised with the data
   *   of a user
   */
  private function initialised() {
    return isset($this->id);
  }

  /*
   * @return int Number of plays total, or -1 if failure
   */
  public function get_play_count() {
    if (!$this->initialised()) {
      Logger::log("get_play_count: user not initialised");
      return -1;
    }
    return Play::user_play_count($this);
  }

  /*
   * @param int $count
   *
   * @return array of latest played songs
   *
   * Get the $count latest songs played by this user
   */
  public function get_latest_songs($count) {
    if (!is_numeric($count)) {
      Logger::log("get_latest_songs: invalid count value");
      return array();
    }
    if (!$this->initialised()) {
      Logger::log("get_latest_songs: user not initialised");
      return array();
    }
    return Song::get_users_latest_songs($this, $count);
  }
}
