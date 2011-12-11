<?
/*
 * Graphs object extracts some data corresponding to various
 * statistics for a user
 */

require_once("Database.php");
require_once("Logger.php");

class Graphs {
  private $artists;
  private $songs;

  /*
   * @param int $user_id
   * @param int $count
   *
   * @return bool  Whether successful
   */
  function __construct($user_id, $count) {
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
    return true;
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
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("build_graphs: Database failure: " . $e->getMessage());
      return array();
    }

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
