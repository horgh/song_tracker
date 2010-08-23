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
