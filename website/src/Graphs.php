<?php
/*
 * Graphs object extracts some data corresponding to various
 * statistics for a user
 */

require_once("Database.php");
require_once("Logger.php");

class Graphs {
  /*
   * @param int $user_id
   * @param int $count
   *
   * @return bool Whether successful
   */
  function __construct($user_id, $count) {
    $this->user_id = $user_id;
    $this->count = $count;

    $sql_songs_all_time = '
SELECT
COUNT(1) AS count,
CONCAT(s.artist, \' - \', s.title) AS label
FROM play p
JOIN song s
ON p.song_id = s.id
WHERE
p.user_id = ?
GROUP BY label
ORDER BY count DESC
LIMIT ?
';
    $params = array($this->user_id, $this->count);
    $this->top_songs_all_time = self::build_graph($sql_songs_all_time,
      $params);

    $this->top_songs_year = self::top_songs_past_interval('1 year');
    $this->top_songs_6_months = self::top_songs_past_interval('6 month');
    $this->top_songs_3_months = self::top_songs_past_interval('3 month');
    $this->top_songs_1_month = self::top_songs_past_interval('1 month');
    $this->top_songs_week = self::top_songs_past_interval('1 week');
    $this->top_songs_day = self::top_songs_past_interval('1 day');

    return true;
  }

  /*
   * @param string $interval e.g. 1 month, 1 week, 1 day, etc
   *
   * @return array Graph array
   */
  private function top_songs_past_interval($interval) {
    $sql = '
SELECT
COUNT(1) AS count,
CONCAT(s.artist, \' - \', s.title) AS label
FROM play p
JOIN song s
ON p.song_id = s.id
WHERE
p.user_id = ?
AND p.create_time > current_timestamp - CAST(? AS INTERVAL)
GROUP BY label
ORDER BY count DESC
LIMIT ?
';
    $params = array($this->user_id, $interval, $this->count);
    return self::build_graph($sql, $params);
  }

  private function build_graph($sql, array $params) {
    $db = Database::instance();
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
