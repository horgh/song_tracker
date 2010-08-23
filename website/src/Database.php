<?
/*
 * Database code similar to some of that in MyQuote by fedex
 *
 * Must set the $DB_ vars below
 */

class Database {
	private static $DB_HOST = "localhost";
	private static $DB_LOGIN = "songs";
	private static $DB_PASS = "songs";
	private static $DB_DB = "songs";

	private static $instance;
	private $mysqli;
	private $statement;

	function __construct() {
		$this->mysqli = new mysqli(self::$DB_HOST, self::$DB_LOGIN, self::$DB_PASS, self::$DB_DB);
		if (mysqli_connect_errno()) {
			print("MySQL connection error!");
			exit();
		}
		if (!$this->mysqli->set_charset("utf8")) {
			printf("Error setting charset utf8: %s\n", $this->mysqli->error);
		}
		$this->statement = $this->mysqli->stmt_init();
	}

	function __destruct() {
		$this->mysqli->close();
	}

	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_statement() {
		return $this->statement;
	}
}
?>
