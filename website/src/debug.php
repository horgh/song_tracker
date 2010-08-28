<?
require_once("Database.php");
require_once("Statements.php");

$stmt = Database::instance()->get_statement();
$stmt->prepare(Statements::_DEBUG_SONG_BY_LENGTH);
$len = "02:29";
$stmt->bind_param(Statements::_DEBUG_SONG_BY_LENGTH_TYPE, $len);
$stmt->execute();
$stmt->bind_result($id, $title, $artist, $album, $length);
$stmt->fetch();
print("Got id $id, title $title, artist $artist, album $album, length $length");
print("Artist: _" . $artist . "_ Title: _" . $title . "_");
?>
