<?
require_once("Database.php");
require_once("Statements.php");

class Userlist {
	private $userlist;

	function __construct() {
		$this->userlist = self::build_userlist();
	}

	public function get_userlist() {
		return $this->userlist;
	}

	private static function build_userlist() {
		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_GET_USERS);
		$stmt->execute();
		$stmt->bind_result($username);
		$userlist = array();
		while($stmt->fetch()) {
			$userlist[] = $username;
		}
		return $userlist;
	}
}
