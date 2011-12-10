<?php
/*
 * Work with the users table
 */

require_once("include/phpass/PasswordHash.php");
require_once("Database.php");
require_once("Logger.php");
require_once("Model.php");

class User extends Model {
  private $fields = array(
                          'id',
                          'name',
                          'pass',
                          'email',
                          'create-time',
                         );

  /*
   * @param string $name
   * @param string $password   Password given by user
   *
   * @return bool Whether user authenticates successfully
   */
  public function authenticate($name, $password) {
    // Object may already have data from database
    if (!isset($this->id) || !$this->query_by_field('name', $name)) {
      Logger::log("authenticate: failed to find user record");
      return false;
    }

    $hasher = new PasswordHash(12, FALSE);
    // $this->pass has the hashed password from the database
    return $hasher->CheckPassword($password, $this->pass);
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
      Logger::log("get_user_names: Failed to retrieve names: " . $e->getMessage());
      return array();
    }

    $userlist = array();
    foreach ($rows as $row) {
      $userlist[] = $row['name'];
    }
    return $userlist;
  }
}
?>