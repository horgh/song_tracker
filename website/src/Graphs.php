<?
require_once("Statements.php");
require_once("Database.php");

class Graphs {
	private $artists;
	private $songs;
	private $userid,
		$count;

	function __construct($userid, $count) {
		$this->userid = $userid;
		$this->count = $count;
		$this->artists = self::build_graph(Statements::_TOP_ARTISTS, Statements::_TOP_ARTISTS_TYPE);
		$this->songs = self::build_graph(Statements::_TOP_SONGS, Statements::_TOP_SONGS_TYPE);
	}

	public function get_artists() {
		return $this->artists;
	}

	public function get_songs() {
		return $this->songs;
	}

	private function build_graph($prepare_stmt, $bind_type) {
		$stmt = Database::instance()->get_statement();
		$stmt->prepare($prepare_stmt);
		$stmt->bind_param($bind_type, $this->userid, $this->count);
		$stmt->execute();
		$stmt->bind_result($count, $label);
		$entries = array();
		while ($stmt->fetch()) {
			$entries[] = array("count" => $count,
				"label" => $label);
		}
		return $entries;
	}
}
?>
