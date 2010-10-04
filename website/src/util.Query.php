<?
/*
 * Misc database interactions
 */

require_once("Database.php");
require_once("Song.php");
require_once("User.php");

class Query {
	public static function user_count_plays($userid) {
		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_PLAYS_USER);
		$stmt->bind_param(Statements::_PLAYS_USER_TYPE, $userid);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		return $count;
	}

	// boolean whether succeed
	public static function add_play($user, $artist, $album, $title, $length) {
		// XXX why stripslashes?
		//$album = stripslashes($album);
		//$artist = stripslashes($artist);
		//$title = stripslashes($title);
		$length = self::fix_length($length);

		if ($title == "" || $artist == "") {
			return false;
		}

		// do not add if last song for user is identical
		if (self::repeat($user->get_id(), $artist, $album, $title, $length)) {
			return false;
		}

		if (!$user->is_valid()) {
			return false;
		}

		$songid = self::insert_song($artist, $album, $title, $length);
		if ($songid == -1) {
			return false;
		}

		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_INSERT_PLAY);
		$stmt->bind_param(Statements::_INSERT_PLAY_TYPE, $songid, $user->get_id());
		return $stmt->execute();
	}

	// return song id, or -1 if not found
	// used by insert_song()
	private static function get_song_by_names($title, $artist, $album) {
		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_GET_SONGID);
		$stmt->bind_param(Statements::_GET_SONGID_TYPE, $title, $artist, $album);
		$stmt->execute();
		$stmt->store_result();

		// if somehow failed to find, indicate with -1
		if ($stmt->num_rows != 1) {
			return -1;
		}

		$stmt->bind_result($id);
		$stmt->fetch();
		return $id;
	}

	// returns id of song matching given data
	// used by add_play()
	private static function insert_song($artist, $album, $title, $length) {
		$stmt = Database::instance()->get_statement();
		// first attempt to insert new row for song
		$stmt->prepare(Statements::_INSERT_SONG);
		$stmt->bind_param(Statements::_INSERT_SONG_TYPE, $artist, $album, $title, $length);

		// if insertion failed, need to fetch already existing row id
		if (!$stmt->execute()) {
			return self::get_song_by_names($title, $artist, $album);
		}
		return $stmt->insert_id;
	}

	// True if last played song is identical to given song's data
	// used by add_play()
	private static function repeat($user, $artist, $album, $title, $length) {
		$last = self::get_songs($user, 1);
		// No plays yet: no repeat
		if (count($last) == 0) {
			return false;
		}
		$last = $last[0];
		return $last->get_artist() == $artist && $last->get_album() == $album && $last->get_title() == $title && $last->get_length() == $length;
	}

	// Length given in form mm:ss or milliseconds, return in form of mm:ss
	// used by add_play()
	private static function fix_length($length) {
		// If no ":" found, assume time given in milliseconds
		if (strpos($length, ":") === false) {
			$length = $length / 1000;
			$minutes = round($length / 60, 0, PHP_ROUND_HALF_DOWN);
			$seconds = $length % 60;
			$length = sprintf("%02d:%02d", $minutes, $seconds);
		}
		return $length;
	}

	// returns array of $count songs for $user
	public static function get_songs($user, $count) {
		$stmt = Database::instance()->get_statement();
		$stmt->prepare(Statements::_LAST_PLAYS);
		$stmt->bind_param(Statements::_LAST_PLAYS_TYPE, $user, $count);
		$stmt->execute();
		$stmt->bind_result($id, $date, $artist, $album, $title, $length);
		$songs = array();
		while ($stmt->fetch()) {
			$songs[] = new Song($id, $date, $artist, $album, $title, $length);
		}
		return $songs;
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

	// get userid by name. -1 if not found
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
}
?>
