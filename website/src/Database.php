<?
/*
 * Database code similar to some of that in MyQuote by fedex
 */

require_once("User.php");
require_once("Song.php");

class Database {
	private static $DB_HOST = "localhost";
	private static $DB_LOGIN = "songs";
	private static $DB_PASS = "songs";
	private static $DB_DB = "songs";

	private static $instance;
	private $mysqli;
	private $statement;

	private static $_INSERT_SONG = "INSERT INTO songs (artist, album, title, length) VALUES(?, ?, ?, ?)";
	private static $_INSERT_SONG_TYPE = "ssss";
	private static $_LAST_PLAYS = "SELECT p.id, p.date, s.artist, s.album, s.title, s.length FROM plays p, songs s WHERE p.songid = s.id AND p.userid = ? ORDER BY p.id DESC LIMIT ?";
	private static $_LAST_PLAYS_TYPE = "ii";
	private static $_GET_SONGID = "SELECT s.id FROM songs s WHERE title = ? AND artist = ? AND album = ?";
	private static $_GET_SONGID_TYPE = "sss";
	private static $_INSERT_PLAY = "INSERT INTO plays (songid, userid) VALUES(?, ?)";
	private static $_INSERT_PLAY_TYPE = "ii";

	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

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

	public function get_statement() {
		return $this->statement;
	}

	// return id of song matching given data
	private function add_song($artist, $album, $title, $length) {
		// first attempt to insert new row for song
		$this->statement->prepare(self::$_INSERT_SONG);
		$this->statement->bind_param(self::$_INSERT_SONG_TYPE, $artist, $album, $title, $length);

		// if insertion failed, need to fetch already existing row id
		if (!$this->statement->execute()) {
			$this->statement->prepare(self::$_GET_SONGID);
			$this->statement->bind_param(self::$_GET_SONGID_TYPE, $title, $artist, $album);
			$this->statement->execute();
			$this->statement->store_result();

			// if somehow failed to find, indicate with -1
			if ($this->statement->num_rows != 1) {
				return -1;
			}

			$this->statement->bind_result($id);
			$this->statement->fetch();
			return $id;
		}
		return $this->statement->insert_id;
	}

	public function add_play($user, $artist, $album, $title, $length) {
		$album = stripslashes($album);
		$artist = stripslashes($artist);
		$title = stripslashes($title);
		$length = $this->fix_length($length);
		// do not add if last song for user is identical
		if ($this->repeat($user->get_id(), $artist, $album, $title, $length)) {
			return false;
		}

		if (!$user->is_valid()) {
			return false;
		}
		$songid = $this->add_song($artist, $album, $title, $length);
		if ($songid == -1) {
			return false;
		}

		$this->statement->prepare(self::$_INSERT_PLAY);
		$this->statement->bind_param(self::$_INSERT_PLAY_TYPE, $songid, $user->get_id());
		return $this->statement->execute();
	}

	public function get_songs($user, $count) {
		$this->statement->prepare(self::$_LAST_PLAYS);
		$this->statement->bind_param(self::$_LAST_PLAYS_TYPE, $user, $count);
		$this->statement->execute();
		$this->statement->bind_result($id, $date, $artist, $album, $title, $length);
		$songs = array();
		while ($this->statement->fetch()) {
			$songs[] = new Song($id, $date, $artist, $album, $title, $length);
		}
		return $songs;
	}

	// True if last played song is identical to given song's data
	private function repeat($user, $artist, $album, $title, $length) {
		$last = $this->get_songs($user, 1);
		// No plays yet: no repeat
		if (count($last) == 0) {
			return false;
		}
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
