<?
/*
 * 20/08/2010
 * by horgh
 *
 * Front-end to song/plays database
 */

require_once("src/Database.php");
require_once("src/Song.php");
require_once("src/Template.php");
require_once("src/util.Query.php");
require_once("src/Userlist.php");
require_once("src/Graphs.php");

$db = Database::instance();

if (isset($_GET['user'])) {
	$userid = Query::get_id_by_name($_GET['user']);
	if ($userid != -1) {
		Template::build_header($_GET['user'] . "'s music");
		print("<h1>" . $_GET['user'] . "'s music</h1>\n");
		$total_plays = Query::user_count_plays($userid);
		print("<h3>Total plays: " . $total_plays . "</h3>\n");
?>
<table id="just_played">
<th>Artist</th>
<th>Album</th>
<th>Title</th>
<th>Length</th>
<th>Played</th>
<?
		$songs = Query::get_songs($userid, 20);
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
<br>
<table id="top_artists">
<th>Top Artists</th>
<th>Plays</th>
<?
	$graphs = new Graphs($userid, 10);
	foreach ($graphs->get_artists() as $artist) {
		print("<tr>");
		print("<td class=\"label\">" . $artist["label"] . "</td>");
		print("<td class=\"count\">" . $artist["count"] . "</td>");
		print("</tr>\n");
	}
?>
</table>
<table id="top_songs">
<th>Top Songs</th>
<th>Plays</th>
<?
	foreach ($graphs->get_songs() as $topsong) {
		print("<tr>");
		print("<td class=\"label\">" . $topsong["label"] . "</td>");
		print("<td class=\"count\">" . $topsong["count"] . "</td>");
		print("</tr>\n");
	}
?>
</table>
<?
	// Invalid user given
	} else {
		Template::build_header("Invalid user");
		print("User not found.");
	}
// No user set
} else {
	Template::build_header("Welcome");
	print("Welcome to the song tracker.");
?>
<table>
<th>Username</th>
<?
	$userlist = new Userlist();
	foreach ($userlist->get_userlist() as $user) {
		print("<tr>");
		print("<td><a href=\"index.php?user=" . $user . "\">" . $user . "</a></td>");
		print("</tr");
	}
}
?>
</table>
<?
Template::build_footer();
?>
