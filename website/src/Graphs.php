<?
require_once("Database.php");

class Graphs {
  private $artists;
  private $songs;
  private $user_id;
  private $count;

  function __construct($user_id, $count) {
    $this->user_id = $user_id;
    $this->count = $count;
    $sql_artists = "SELECT COUNT(s.id) AS count, s.artist AS label"
                 . " FROM plays p JOIN songs s ON p.song_id = s.id"
                 . " WHERE p.user_id = ? AND s.artist != 'N/A'"
                 . " GROUP BY s.artist"
                 . " ORDER BY count DESC"
                 . " LIMIT ?";
    $sql_songs = "SELECT COUNT(1) AS count,"
               . "   CONCAT(s.artist, ' - ', s.title) AS label"
               . " FROM plays p JOIN songs s ON p.song_id = s.id"
               . " WHERE p.user_id = ?"
               . " GROUP BY label"
               . " ORDER BY count DESC"
               . " LIMIT ?";
    $this->artists = self::build_graph($sql_artists);
    $this->songs = self::build_graph($sql_songs);
  }

  public function get_artists() {
    return $this->artists;
  }

  public function get_songs() {
    return $this->songs;
  }

  private function build_graph($sql) {
    $db = Database::instance();
    $params = array($this->user_id, $this->count);
    $rows = $db->select($sql, $params);

    $entries = array();
    foreach ($rows as $row) {
      $entries[] = array(
                          'count' => $row['count'],
                          'label' => $row['label'],
                        );
    }
    return $entries;
  }
}
?>
