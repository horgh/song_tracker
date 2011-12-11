<?php
/*
 * Work with the songs table
 */

require_once("Database.php");
require_once("Logger.php");
require_once("Model.php");
require_once("util.DateFormat.php");

class Song extends Model {
  protected $fields = array(
                          'id',
                          'title',
                          'artist',
                          'album',
                          'length',
                         );

  /*
   * Overload parent method
   */
  public function query_by_id($id) {
    if (!$this->query_by_id($id)) {
      return false;
    }
    $this->time_since = DateFormat::timeSince($this->create_time);
  }
}
?>
