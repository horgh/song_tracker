<?
/*
 * 20/08/2010
 * by horgh
 *
 * Front-end to song/plays database
 *
 */

require_once("src/Database.php");
require_once("src/Song.php");
require_once("src/Template.php");
require_once("src/util.Query.php");

$db = Database::instance();

if (isset($_GET['user'])) {
	$userid = Query::get_id_by_name($_GET['user']);
	if ($userid != -1) {
		Template::build_header($_GET['user'] . "'s music");
		print("<h1>" . $_GET['user'] . "'s music</h1>");
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
}
Template::build_footer();
?>
