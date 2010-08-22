<?
/*
 * See music.php & schema.sql
 *
 * Database code similar to that in MyQuote by fedex
 */

class Database {
	private static $DB_HOST = "localhost";
	private static $DB_LOGIN = "songs";
	private static $DB_PASS = "songs";
	private static $DB_DB = "songs";

	private static $instance;
	private $mysqli;
	private $statement;

	private static $_INSERT = "INSERT INTO plays (artist, album, title, length) VALUES(?, ?, ?, ?)";
	private static $_INSERT_TYPE = "ssss";
	private static $_PLAYS = "SELECT p.id, p.date, p.artist, p.album, p.title, p.length FROM plays p ORDER BY p.id DESC LIMIT ?";
	private static $_PLAYS_TYPE = "i";

	function __construct() {
		$this->mysqli = new mysqli(self::$DB_HOST, self::$DB_LOGIN, self::$DB_PASS, self::$DB_DB);
		if (mysqli_connect_errno()) {
			print("MySQL connection error!");
			exit();
		}
		$this->statement = $this->mysqli->stmt_init();
	}

	function __destruct() {
		$this->statement->close();
		$this->mysqli->close();
	}

	public function add_play($artist, $album, $title, $length) {
		$album = stripslashes($album);
		$artist = stripslashes($artist);
		$title = stripslashes($title);
		$length = $this->fix_length($length);
		if ($this->repeat($artist, $album, $title, $length)) {
			return false;
		}
		$this->statement->prepare(self::$_INSERT);
		$this->statement->bind_param(self::$_INSERT_TYPE, $artist, $album, $title, $length);
		$this->statement->execute();
		return true;
	}

	public function get_songs($count) {
		$this->statement->prepare(self::$_PLAYS);
		$this->statement->bind_param(self::$_PLAYS_TYPE, $count);
		$this->statement->execute();
		$this->statement->bind_result($id, $date, $artist, $album, $title, $length);
		$songs = array();
		while ($this->statement->fetch()) {
			$songs[] = new Song($id, $date, $artist, $album, $title, $length);
		}
		return $songs;
	}

	// True if last played song is identical to this one
	private function repeat($artist, $album, $title, $length) {
		$last = $this->get_songs(1);
		$last = $last[0];
		return $last->get_artist() == $artist && $last->get_album() == $album && $last->get_title() == $title && $last->get_length() == $length;
	}

	// Check if length is given in form mm:ss or milliseconds, return in form of
	// mm:ss
	private function fix_length($length) {
		// If no ":" found, assume time given in milliseconds
		if (strpos($length, ":") === false) {
			$minutes = round($length / 1000 / 60, 0, PHP_ROUND_HALF_DOWN);
			$seconds = $length / 1000 % 60;
			$length = sprintf("%02d:%02d", $minutes, $seconds);
		}
		return $length;
	}
}
?>
