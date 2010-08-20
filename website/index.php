<?
/*
 * 20/08/2010
 * by horgh
 *
 * Receive a POST which consists of parameters: artist, album, title, length
 * Add this data to a MySQL database
 *
 * You must set the values for database access in Database.php
 * The database schema is something similar to:
 *
 */

static $title = "Horgh's music";

require_once("Database.php");
require_once("Song.php");

$db = new Database();

if (isset($_POST['artist']) && isset($_POST['album']) && isset($_POST['title']) && isset($_POST['length'])) {
	$artist = urldecode($_POST['artist']);
	$album = urldecode($_POST['album']);
	$title = urldecode($_POST['title']);
	$length = urldecode($_POST['length']);

	$db->add_play($artist, $album, $title, $length);
} else {
	$songs = $db->get_songs();
?>
<html>
<head>
<?
print("<title>" . $title . "</title>");
?>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?
print("<h1>" . $title . "</h1>");
?>
<table id="just_played">
<th>Artist</th>
<th>Album</th>
<th>Title</th>
<th>Length</th>
<th>Played</th>
<?
	foreach ($songs as $song) {
		print("<tr>");
		print("<td>" . $song->get_artist() . "</td>");
		print("<td>" . $song->get_album() . "</td>");
		print("<td>" . $song->get_title() . "</td>");
		print("<td>" . $song->get_length() . "</td>");
		print("<td>" . $song->get_since() . "</td>");
		print("</tr>\n");
	}
?>
</table>
</body>
</html>
<?
}
?>
