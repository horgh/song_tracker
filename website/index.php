<?
/*
 * 20/08/2010
 * by horgh
 *
 * Receive a POST which consists of parameters: artist, album, title, length
 * Add this data to a MySQL database
 *
 * You must set the values for database access in Database.php
 * The database schema is something similar to schema.sql
 *
 */

static $title = "Horgh's music";
static $password = "a110e6b9a361653a042e3f5dfbac4c6105693789";

require_once("Database.php");
require_once("Song.php");

$db = new Database();
header("Content-type: text/html; charset=utf-8");

// Update sent
if (isset($_POST['artist']) && isset($_POST['album']) && isset($_POST['title']) && isset($_POST['length']) && isset($_POST['pass'])) {
	$pass = urldecode($_POST['pass']);
	if (strcmp($pass, $password) != 0) {
		print("Invalid password.");
		exit();
	}
	/*
	$debug = "a " . $_POST['artist'] . " al " . $_POST['album'] . " ti " . $_POST['title'];
	print($debug . "\n");
	print(urldecode($debug) . "\n");
	*/
	$db->add_play($_POST['artist'], $_POST['album'], $_POST['title'], $_POST['length']);

// Last song played in plain text for script (for now playing)
} elseif (isset($_GET['last'])) {
	$songs = $db->get_songs(1);
	if (count($songs) == 1) {
		$song = $songs[0];
		print($song->get_artist() . " - " . $song->get_album() . " - " . $song->get_title() . " (" . $song->get_length() . ")");
	} else {
		print("Error fetching song");
	}

// Otherwise display website
} else {
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
	$songs = $db->get_songs(20);

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
