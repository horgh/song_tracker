<?
require_once("include/phpass/PasswordHash.php");
require_once("Database.php");
require_once("Logger.php");

class User {
  private $name;
  private $password;
  private $id;
  private $valid = false;

  function __construct($name, $password) {
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
  private function validate() {
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
}
