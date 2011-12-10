<?
require_once("Database.php");

class Userlist {
  private $userlist;

  function __construct() {
    $this->userlist = self::build_userlist();
  }

  public function get_userlist() {
    return $this->userlist;
  }

  private static function build_userlist() {
    // Get users
    $db = Database::instance();
    $sql = "SELECT name FROM users ORDER BY name ASC";
    $params = array();
    $rows = $db->select($sql, $params);
    $userlist = array();
    foreach ($rows as $row) {
      $userlist[] = $row['name'];
    }
    return $userlist;
  }
}
