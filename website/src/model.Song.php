<?php
/*
 * Work with the songs table
 *
 * XXX Unfinished and currently not used
 */

require_once("Database.php");
require_once("Logger.php");

class SongModel {
  private $id = NULL;
  private $title = NULL;
  private $artist = NULL;
  private $album = NULL;
  private $length = NULL;

  private $fields = array(
                          'id',
                          'title',
                          'artist',
                          'album',
                          'length',
                         );

  /*
   * Throws exception if insufficient data
   */
  function __construct(array $row) {
    $this->fill_fields($row);
  }

  /*
   * Throws exception if field not found
   */
  private function fill_fields(array $row) {
    foreach ($fields as $field) {
      if (!array_key_exists($field, $row)) {
        throw new Exception("did not find expected field $field");
      }
      $this->$field = $row[$field];
    }
  }

  /*
   * @return SongModel object
   *
   * Throws exception on db error (including if song with id does not exist)
   */
  function getById($id) {
    if (empty($id) || !is_numeric($id)) {
      throw new Exception("invalid id given: $id");
    }
    $dbh = Database::instance();
    $sql = "SELECT * FROM songs WHERE id = ?";
  }
}
?>
