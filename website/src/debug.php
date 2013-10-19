<?php
require_once("Database.php");

$db = Database::instance();
$sql = "SELECT id, title, artist, album, length FROM songs WHERE length = ?";
$len = "02:29";
$params = array($len);
$rows = $db->select($sql, $params);
if (count($rows) !== 1) {
  print "Failed to find row.";
} else {
  $row = $rows[0];
  $id = $row['id'];
  $title = $row['title'];
  $artist = $row['artist'];
  $album = $row['album'];
  $length = $row['length'];
  print("Got id $id, title $title, artist $artist, album $album, length $length");
  print("Artist: _" . $artist . "_ Title: _" . $title . "_");
}
