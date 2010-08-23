<?
require_once("include/phpass/PasswordHash.php");
require_once("Database.php");
require_once("Statements.php");

class User {
	private $username;
	private $password;
	private $id;
	private $valid = false;

	function __construct($username, $password) {
		$this->username = $username;
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

	// false if registration fails
	public static function add_user($user, $email, $password) {
		$hasher = new PasswordHash(12, FALSE);
		$hash = $hasher->HashPassword($password);

		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_INSERT_USER);
		$stmt->bind_param(Statements::_INSERT_USER_TYPE, $user, $hash, $email);
		return $stmt->execute();
	}

	// get userid by name
	// -1 if not found
	public static function get_id_by_name($user) {
		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_USER_ID);
		$stmt->bind_param(Statements::_USER_ID_TYPE, $user);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows != 1) {
			return -1;
		}
		$stmt->bind_result($id);
		$stmt->fetch();
		return $id;
	}

	// Check if user is valid & valid password given
	private function validate() {
		$hasher = new PasswordHash(12, FALSE);

		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_USER_DATA);
		$stmt->bind_param(Statements::_USER_DATA_TYPE, $this->username);
		$stmt->execute();
		$stmt->store_result();

		// No rows; username not in table
		if ($stmt->num_rows != 1) {
			return false;
		}

		$stmt->bind_result($id, $hash);
		$stmt->fetch();
		$this->id = $id;
		// false if password doesn't match hash
		return $hasher->CheckPassword($this->password, $hash);
	}
}
