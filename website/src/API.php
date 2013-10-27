<?php

require_once('Database.php');
require_once('Logger.php');
require_once('util.Format.php');

class API {
  //! record a user's song play.
  /*!
   * @param string $username
   * @param string $password
   * @param string $artist
   * @param string $album
   * @param string $title
   * @param mixed $length
   *
   * @return bool whether successful
   */
  public static function add_user_play($username, $password,
    $artist, $album, $title, $length)
  {
    // ensure we have the length in milliseconds.
    $length = Format::fix_length($length);
    if ($length === -1) {
      Logger::log("add_play: failed to convert length to milliseconds");
      return false;
    }

    // unknown artist/album ("") becomes "N/A"
    if ($artist === "") {
      $artist = "N/A";
    }
    if ($album === "") {
      $album = "N/A";
    }

    $db = Database::instance();
    $sql = 'SELECT api_add_user_play(?, ?, ?, ?, ?, ?) AS success';
    $params = array($username, $password, $artist, $album, $title, $length);
    try {
      $rows = $db->select($sql, $params);
    } catch (Exception $e) {
      Logger::log("add_user_play: database failure: " . $e->getMessage());
      return false;
    }
    return count($rows) === 1 && $rows[0]['success'] === true;
  }
}
