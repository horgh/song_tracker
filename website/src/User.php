<?
require_once("include/phpass/PasswordHash.php");
require_once("Database.php");
require_once("Logger.php");
require_once("Model.php");

class User extends Model {
  private $id = NULL;
  private $name = NULL;
  private $pass = NULL;
  private $email = NULL;
  private $create_time = NULL;

  private $fields = array(
                          'id',
                          'name',
                          'pass',
                          'email',
                          'create_time',
                          );

  function __construct($name) {
    $this->name = $name;
    $this->password = $password;
    $this->valid = $this->validate();
  }

  public function is_valid() {
    return $this->valid;
  }

  // must check validity of this with is_valid() prior to use
  public function get_id() {
    return $this->id;
  }

  /*
   * @return bool Whether user is valid & valid password given
   */
  private function validate($name, $password) {
    $hasher = new PasswordHash(12, FALSE);

    $db = Database::instance();
    $sql = "SELECT id, pass FROM users WHERE name = ?";
    $params = array($this->name);
    $rows = $db->select($sql, $params);
    if (count($rows) !== 1) {
      Logger::log("validate: user not found in database");
      return false;
    }

    $this->id = $rows[0]['id'];
    $hash = $rows[0]['pass'];

    // false if password doesn't match hash
    return $hasher->CheckPassword($this->password, $hash);
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
